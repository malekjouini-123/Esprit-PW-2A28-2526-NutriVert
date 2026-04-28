<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Coaching.php';

if (class_exists('CoachingController')) return;

use Dompdf\Dompdf;

class CoachingController
{
    private PDO $pdo;
    private array $allowedSortColumns = ['duration_weeks', 'difficulty_level', 'created_at', 'title'];

    public function __construct() { $this->pdo = getDB(); }
    public function __destruct() {}

    public function handle(string $action): void
    {
        switch ($action) {
            case 'index': case 'search': case 'sort': $this->renderIndex(); break;
            case 'create':        $this->renderCreate(); break;
            case 'store':         $this->store();        break;
            case 'edit':          $this->edit();         break;
            case 'update':        $this->update();       break;
            case 'delete':        $this->delete();       break;
            case 'export':        $this->exportPdf();    break;
            case 'export_csv':    $this->exportCsv();    break;
            case 'generer_seance':$this->genererSeance();break;
            default:              $this->renderIndex();
        }
    }

    public function getAllForDashboard(): array    { return $this->dbGetAll(); }
    public function getByIdForDashboard(int $id): ?Coaching { return $this->dbGetById($id); }

    private function renderIndex(): void
    {
        $search = trim((string)($_GET['search'] ?? ''));
        $sort   = trim((string)($_GET['sort']   ?? ''));
        $sortMap = [
            'title_asc'           => ['title',         'asc'],
            'duration_weeks_asc'  => ['duration_weeks','asc'],
            'duration_weeks_desc' => ['duration_weeks','desc'],
        ];
        [$sortColumn, $sortOrder] = $sortMap[$sort] ?? ['', 'desc'];

        if ($search !== '' && $sortColumn !== '')      $coachingPrograms = $this->sortRows($this->dbSearch($search), $sortColumn, $sortOrder);
        elseif ($search !== '')                        $coachingPrograms = $this->dbSearch($search);
        elseif ($sortColumn !== '')                    $coachingPrograms = $this->dbSort($sortColumn, $sortOrder);
        else                                           $coachingPrograms = $this->dbGetAll();

        $exercisesByCoaching = [];
        $exerciseController  = new ExerciseController();
        foreach ($exerciseController->getAllForDashboard() as $exercise) {
            $exercisesByCoaching[(int)$exercise['coaching_id']][] = $exercise;
        }
        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../views/front/coaching.php';
    }

    private function renderCreate(): void
    {
        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../views/front/coaching_create.php';
    }

    private function edit(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Identifiant invalide.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $editingProgram = $this->dbGetById($id);
        if (!$editingProgram) { $this->setFlash('Programme introuvable.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../views/front/coaching_edit.php';
    }

    private function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('index.php?controller=coaching&action=index'); }
        $program = $this->buildFromPost($_POST);
        if ($program === null) { $this->redirectBackToCoaching(); }
        $this->dbCreate($program);
        $this->setFlash('Programme créé avec succès.');
        $this->redirectBackToCoaching();
    }

    private function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('index.php?controller=coaching&action=index'); }
        $id = $this->getIdFromGet();
        if ($id === null || !$this->dbGetById($id)) { $this->setFlash('Programme introuvable.'); $this->redirectBackToCoaching(); }
        $program = $this->buildFromPost($_POST);
        if ($program === null) { $this->redirectBackToCoaching($id); }
        $this->dbUpdate($id, $program);
        $this->setFlash('Programme mis à jour avec succès.');
        $this->redirectBackToCoaching();
    }

    private function delete(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Identifiant invalide.'); $this->redirectBackToCoaching(); }
        $exerciseController = new ExerciseController();
        foreach ($exerciseController->getByCoachingForController($id) as $exercise) {
            if (!empty($exercise['image'])) { $path = __DIR__ . '/../' . $exercise['image']; if (is_file($path)) @unlink($path); }
            $exerciseController->deleteById((int)$exercise['id']);
        }
        $this->dbDelete($id);
        $this->setFlash('Programme supprimé avec succès.');
        $this->redirectBackToCoaching();
    }

    private function exportCsv(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Identifiant invalide.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $program = $this->dbGetById($id);
        if (!$program) { $this->setFlash('Programme introuvable.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $exerciseController = new ExerciseController();
        $exercises = $exerciseController->getByCoachingForController($id);

        $e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $program->getTitle()) . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        // En-têtes
        fputcsv($output, ['Programme', $e($program->getTitle())], ';');
        fputcsv($output, ['Description', $e($program->getDescription() ?: 'Aucune description')], ';');
        fputcsv($output, ['Durée', $program->getDurationWeeks() . ' semaines'], ';');
        fputcsv($output, ['Niveau', ucfirst($e($program->getDifficultyLevel()))], ';');
        fputcsv($output, [], ';'); // Ligne vide
        fputcsv($output, ['Exercice', 'Séries × Répétitions', 'Temps de repos', 'Description'], ';');

        if (empty($exercises)) {
            fputcsv($output, ['Aucun exercice lié'], ';');
        } else {
            foreach ($exercises as $ex) {
                fputcsv($output, [
                    $e($ex['name']),
                    $ex['sets'] . ' × ' . $ex['reps'],
                    $e($ex['rest_time']),
                    $e($ex['description'] ?: '-')
                ], ';');
            }
        }

        fclose($output);
        exit;
    }

    private function genererSeance(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Programme introuvable.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $program = $this->dbGetById($id);
        if (!$program) { $this->setFlash('Programme introuvable.'); $this->redirect('index.php?controller=coaching&action=index'); }
        $niveau = $program->getDifficultyLevel();
        $bibliotheque = [
            ['nom' => 'Marche rapide',    'type' => 'cardio',    'series' => 3, 'reps' => 10, 'repos' => 30],
            ['nom' => 'Course',           'type' => 'cardio',    'series' => 4, 'reps' => 8,  'repos' => 45],
            ['nom' => 'Burpees',          'type' => 'cardio',    'series' => 3, 'reps' => 12, 'repos' => 60],
            ['nom' => 'Corde à sauter',   'type' => 'cardio',    'series' => 5, 'reps' => 20, 'repos' => 30],
            ['nom' => 'Gainage',          'type' => 'endurance', 'series' => 3, 'reps' => 30, 'repos' => 30],
            ['nom' => 'Squat',            'type' => 'endurance', 'series' => 3, 'reps' => 15, 'repos' => 40],
            ['nom' => 'Fentes',           'type' => 'endurance', 'series' => 3, 'reps' => 12, 'repos' => 35],
            ['nom' => 'Pompes',           'type' => 'force',     'series' => 4, 'reps' => 12, 'repos' => 45],
            ['nom' => 'Tractions',        'type' => 'force',     'series' => 3, 'reps' => 8,  'repos' => 60],
            ['nom' => 'Développé couché', 'type' => 'force',     'series' => 4, 'reps' => 10, 'repos' => 90],
        ];
        $regles = [
            'easy'   => ['cardio' => 3, 'endurance' => 1, 'force' => 0],
            'medium' => ['cardio' => 2, 'endurance' => 1, 'force' => 1],
            'hard'   => ['cardio' => 1, 'endurance' => 2, 'force' => 3],
        ];
        $regle = $regles[$niveau] ?? ['cardio' => 2, 'endurance' => 1, 'force' => 1];
        $seance = []; $compteurs = ['cardio' => 0, 'endurance' => 0, 'force' => 0];
        foreach ($bibliotheque as $exercice) {
            $type = $exercice['type'];
            if ($compteurs[$type] < $regle[$type]) { $seance[] = $exercice; $compteurs[$type]++; }
        }
        include __DIR__ . '/../views/front/seance_generee.php';
    }

    private function dbGetAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM coaching_programs ORDER BY created_at DESC');
        return $this->hydrateMany($stmt->fetchAll());
    }

    private function dbGetById(int $id): ?Coaching
    {
        $stmt = $this->pdo->prepare('SELECT * FROM coaching_programs WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Coaching($row) : null;
    }

    private function dbSearch(string $keyword): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM coaching_programs WHERE title LIKE :k1 OR description LIKE :k2 ORDER BY created_at DESC');
        $kw = '%' . $keyword . '%';
        $stmt->execute(['k1' => $kw, 'k2' => $kw]);
        return $this->hydrateMany($stmt->fetchAll());
    }

    private function dbSort(string $column, string $order): array
    {
        $col = in_array($column, $this->allowedSortColumns, true) ? $column : 'created_at';
        $dir = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        if ($col === 'difficulty_level') {
            $sql = "SELECT * FROM coaching_programs ORDER BY FIELD(difficulty_level,'easy','medium','hard') {$dir}, created_at DESC";
        } else {
            $sql = "SELECT * FROM coaching_programs ORDER BY {$col} {$dir}";
        }
        return $this->hydrateMany($this->pdo->query($sql)->fetchAll());
    }

    private function dbCreate(Coaching $program): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO coaching_programs (title, description, image, duration_weeks, difficulty_level) VALUES (:title, :description, :image, :duration_weeks, :difficulty_level)');
        return $stmt->execute($program->toArray());
    }

    private function dbUpdate(int $id, Coaching $program): bool
    {
        $stmt = $this->pdo->prepare('UPDATE coaching_programs SET title = :title, description = :description, image = :image, duration_weeks = :duration_weeks, difficulty_level = :difficulty_level WHERE id = :id');
        return $stmt->execute(array_merge($program->toArray(), ['id' => $id]));
    }

    private function dbDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM coaching_programs WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function hydrateMany(array $rows): array
    {
        return array_map(static fn(array $row): Coaching => new Coaching($row), $rows);
    }

    private function buildFromPost(array $input): ?Coaching
    {
        try {
            return new Coaching([
                'title'            => trim(strip_tags((string)($input['title']            ?? ''))),
                'description'      => trim(strip_tags((string)($input['description']      ?? ''))),
                'image'            => trim((string)($input['image'] ?? '')) ?: null,
                'duration_weeks'   => (int)($input['duration_weeks']   ?? 0),
                'difficulty_level' => strtolower(trim((string)($input['difficulty_level'] ?? ''))),
            ]);
        } catch (InvalidArgumentException $e) {
            $this->setFlash($e->getMessage());
            return null;
        }
    }

    private function sortRows(array $rows, string $column, string $order): array
    {
        $dir = strtolower($order) === 'asc' ? 1 : -1;
        usort($rows, static function (Coaching $a, Coaching $b) use ($column, $dir): int {
            return match ($column) {
                'difficulty_level' => ((['easy'=>1,'medium'=>2,'hard'=>3][$a->getDifficultyLevel()]??999)<=>(['easy'=>1,'medium'=>2,'hard'=>3][$b->getDifficultyLevel()]??999))*$dir,
                'duration_weeks'   => ($a->getDurationWeeks() <=> $b->getDurationWeeks()) * $dir,
                'title'            => strcmp($a->getTitle(), $b->getTitle()) * $dir,
                default            => (($a->getCreatedAt()??'')<=>($b->getCreatedAt()??''))*$dir,
            };
        });
        return $rows;
    }

    private function getIdFromGet(): ?int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        return ($id !== false && $id !== null) ? $id : null;
    }

    private function setFlash(string $message): void    { $_SESSION['flash_message'] = $message; }

    private function consumeFlash(): ?string
    {
        if (!isset($_SESSION['flash_message'])) return null;
        $msg = (string)$_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }

    private function redirect(string $url): never { header('Location: ' . $url); exit; }

    private function redirectBackToCoaching(?int $editId = null): never
    {
        if (($_GET['redirect'] ?? '') === 'dashboard') {
            $url = 'index.php?controller=dashboard&action=index';
            if ($editId !== null) $url .= '&view=coaching_edit&id=' . $editId;
            $this->redirect($url);
        }
        $url = 'index.php?controller=coaching&action=index';
        if ($editId !== null) $url .= '&action=edit&id=' . $editId;
        $this->redirect($url);
    }
}