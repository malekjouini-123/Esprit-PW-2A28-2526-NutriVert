<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Coaching.php';
require_once __DIR__ . '/../models/Exercise.php';

class CoachingController
{
    private Coaching $coachingModel;
    private Exercise $exerciseModel;

    public function __construct()
    {
        $this->coachingModel = new Coaching();
        $this->exerciseModel = new Exercise();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'index':
            case 'search':
            case 'sort':
                $this->renderIndex();
                break;
            case 'create':
                $this->renderCreate();
                break;
            case 'store':
                $this->store();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'export':
                $this->exportPdf();
                break;
            default:
                $this->renderIndex();
                break;
        }
    }

    public function getAllForDashboard(): array
    {
        return $this->coachingModel->getAll();
    }

    public function getByIdForDashboard(int $id): ?array
    {
        return $this->coachingModel->getById($id);
    }

    private function renderIndex(): void
    {
        $search = trim((string)($_GET['search'] ?? ''));
        $sort   = trim((string)($_GET['sort'] ?? ''));

        // Map UI sort keys to [column, order] — avoids explode() breaking on multi-word columns.
        $sortMap = [
            'title_asc'           => ['title',          'asc'],
            'duration_weeks_asc'  => ['duration_weeks',  'asc'],
            'duration_weeks_desc' => ['duration_weeks',  'desc'],
        ];
        [$sortColumn, $sortOrder] = $sortMap[$sort] ?? ['', 'desc'];

        if ($search !== '' && $sortColumn !== '') {
            // Search first, then sort in-memory.
            $coachingPrograms = $this->coachingModel->search($search);
            $coachingPrograms = $this->sortRows($coachingPrograms, $sortColumn, $sortOrder);
        } elseif ($search !== '') {
            $coachingPrograms = $this->coachingModel->search($search);
        } elseif ($sortColumn !== '') {
            $coachingPrograms = $this->coachingModel->sort($sortColumn, $sortOrder);
        } else {
            $coachingPrograms = $this->coachingModel->getAll();
        }

        $exercisesByCoaching = [];
        foreach ($this->exerciseModel->getAll() as $exercise) {
            $coachingId = (int)$exercise['coaching_id'];
            if (!isset($exercisesByCoaching[$coachingId])) {
                $exercisesByCoaching[$coachingId] = [];
            }
            $exercisesByCoaching[$coachingId][] = $exercise;
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
        if ($id === null) {
            $this->setFlash('Invalid coaching ID.');
            $this->redirect('index.php?controller=coaching&action=index');
        }

        $editingProgram = $this->coachingModel->getById($id);
        if (!$editingProgram) {
            $this->setFlash('Coaching program not found.');
            $this->redirect('index.php?controller=coaching&action=index');
        }

        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../views/front/coaching_edit.php';
    }

    private function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=coaching&action=index');
        }

        [$data, $errors] = $this->validateCoaching($_POST);
        if ($errors !== []) {
            $this->setFlash(implode(' ', $errors));
            $this->redirectBackToCoaching();
        }

        $this->coachingModel->create($data);
        $this->setFlash('Coaching program created successfully.');
        $this->redirectBackToCoaching();
    }

    private function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=coaching&action=index');
        }

        $id = $this->getIdFromGet();
        if ($id === null || !$this->coachingModel->getById($id)) {
            $this->setFlash('Coaching program not found.');
            $this->redirectBackToCoaching();
        }

        [$data, $errors] = $this->validateCoaching($_POST);
        if ($errors !== []) {
            $this->setFlash(implode(' ', $errors));
            $this->redirectBackToCoaching($id);
        }

        $this->coachingModel->update($id, $data);
        $this->setFlash('Coaching program updated successfully.');
        $this->redirectBackToCoaching();
    }

    private function delete(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Invalid coaching ID.');
            $this->redirectBackToCoaching();
        }

        $exercises = $this->exerciseModel->getByCoaching($id);
        foreach ($exercises as $exercise) {
            if (!empty($exercise['image'])) {
                $absolutePath = __DIR__ . '/../' . $exercise['image'];
                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
            $this->exerciseModel->delete((int)$exercise['id']);
        }

        $this->coachingModel->delete($id);
        $this->setFlash('Coaching program deleted successfully.');
        $this->redirectBackToCoaching();
    }

    private function exportPdf(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Invalid coaching ID for PDF export.');
            $this->redirectBackToCoaching();
        }

        $program = $this->coachingModel->getById($id);
        if (!$program) {
            $this->setFlash('Coaching program not found.');
            $this->redirectBackToCoaching();
        }

        $exercises = $this->exerciseModel->getByCoaching($id);

        if (ob_get_length()) {
            ob_clean();
        }

        require_once __DIR__ . '/../vendor/autoload.php';
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        $e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: sans-serif; color: #2D3E2B; }
                h1 { color: #FF7E67; font-size: 24px; border-bottom: 2px solid #7DCFB6; padding-bottom: 10px; }
                p { font-size: 14px; line-height: 1.5; color: #4A5B4A; }
                .badges { margin: 15px 0; font-size: 12px; }
                .badge { display: inline-block; padding: 4px 10px; background: #ebf5df; border: 1px solid #7DCFB6; border-radius: 12px; margin-right: 10px; font-weight: bold; color: #2D3E2B; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #FEF7E8; color: #FF7E67; padding: 12px; text-align: left; font-size: 13px; text-transform: uppercase; border-bottom: 2px dashed rgba(255,126,103,0.3); }
                td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; }
            </style>
        </head>
        <body>
            <h1>' . $e($program['title']) . '</h1>
            <p>' . nl2br($e($program['description'] ?: 'Aucune description fournie.')) . '</p>
            <div class="badges">
                <span class="badge">Durée: ' . (int)$program['duration_weeks'] . ' semaines</span>
                <span class="badge">Niveau: ' . ucfirst($e($program['difficulty_level'])) . '</span>
            </div>
            
            <h2 style="margin-top: 30px; font-size: 18px; color: #4A6B4A;">Liste des Exercices</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom de l&apos;exercice</th>
                        <th>Séries x Rép.</th>
                        <th>Temps de repos</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>';

        if ($exercises === []) {
            $html .= '<tr><td colspan="4" style="text-align: center; color: #888; padding: 30px;">Aucun exercice lié à ce programme.</td></tr>';
        } else {
            foreach ($exercises as $ex) {
                $html .= '<tr>
                    <td style="font-weight: bold; color: #4A6B4A;">' . $e($ex['name']) . '</td>
                    <td><strong style="color: coral;">' . (int)$ex['sets'] . ' x ' . (int)$ex['reps'] . '</strong></td>
                    <td>' . $e($ex['rest_time']) . '</td>
                    <td>' . nl2br($e($ex['description'] ?: '-')) . '</td>
                </tr>';
            }
        }

        $html .= '</tbody></table></body></html>';

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Programme_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $program['title']) . '.pdf', ["Attachment" => true]);
        exit;
    }

    private function validateCoaching(array $input): array
    {
        $errors = [];
        $title = trim(strip_tags((string)($input['title'] ?? '')));
        $description = trim(strip_tags((string)($input['description'] ?? '')));
        $durationWeeks = filter_var($input['duration_weeks'] ?? null, FILTER_VALIDATE_INT);
        $difficulty = strtolower(trim((string)($input['difficulty_level'] ?? '')));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if ($durationWeeks === false || $durationWeeks <= 0) {
            $errors[] = 'Duration must be a positive number.';
        }

        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $errors[] = 'Difficulty must be easy, medium, or hard.';
        }

        return [[
            'title' => $title,
            'description' => $description,
            'duration_weeks' => $durationWeeks ?: 0,
            'difficulty_level' => $difficulty,
        ], $errors];
    }


    private function sortRows(array $rows, string $column, string $order): array
    {
        $safeOrder = strtolower($order) === 'asc' ? 1 : -1;
        $safeColumn = in_array($column, ['duration_weeks', 'difficulty_level', 'created_at', 'title'], true)
            ? $column
            : 'created_at';

        usort($rows, static function (array $a, array $b) use ($safeColumn, $safeOrder): int {
            if ($safeColumn === 'difficulty_level') {
                $rank = ['easy' => 1, 'medium' => 2, 'hard' => 3];
                $first = $rank[$a[$safeColumn]] ?? 999;
                $second = $rank[$b[$safeColumn]] ?? 999;
                return ($first <=> $second) * $safeOrder;
            }

            return (($a[$safeColumn] <=> $b[$safeColumn]) * $safeOrder);
        });

        return $rows;
    }

    private function getIdFromGet(): ?int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        return $id !== false && $id !== null ? $id : null;
    }

    private function setFlash(string $message): void
    {
        $_SESSION['flash_message'] = $message;
    }

    private function consumeFlash(): ?string
    {
        if (!isset($_SESSION['flash_message'])) {
            return null;
        }

        $message = (string)$_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    private function redirectBackToCoaching(?int $editId = null): void
    {
        if (($_GET['redirect'] ?? '') === 'dashboard') {
            $url = 'index.php?controller=dashboard&action=index';
            if ($editId !== null) {
                $url .= '&view=coaching_edit&id=' . $editId;
            }
            $this->redirect($url);
        }

        $url = 'index.php?controller=coaching&action=index';
        if ($editId !== null) {
            $url .= '&action=edit&id=' . $editId;
        }
        $this->redirect($url);
    }
}
