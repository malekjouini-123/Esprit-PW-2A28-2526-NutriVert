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
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $sortColumn = trim((string)($_GET['sort_column'] ?? ''));
        $sortOrder = trim((string)($_GET['sort_order'] ?? 'desc'));

        if ($keyword !== '') {
            $coachingPrograms = $this->coachingModel->search($keyword);
        } elseif ($sortColumn !== '') {
            $coachingPrograms = $this->coachingModel->sort($sortColumn, $sortOrder);
        } else {
            $coachingPrograms = $this->coachingModel->getAll();
        }

        // If both are provided, keep filtering in SQL and sort in memory.
        if ($keyword !== '' && $sortColumn !== '') {
            $coachingPrograms = $this->sortRows($coachingPrograms, $sortColumn, $sortOrder);
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

        if (!$this->loadFpdf()) {
            $this->setFlash('FPDF not found. Install it in /vendor/fpdf/fpdf.php or /fpdf/fpdf.php.');
            $this->redirectBackToCoaching();
        }

        $exercises = $this->exerciseModel->getByCoaching($id);

        // Ensure no prior output corrupts PDF download.
        if (ob_get_length()) {
            ob_clean();
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(14, 14, 14);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetFillColor(245, 245, 245);

        $pdf->SetFont('Arial', 'B', 19);
        $pdf->Cell(0, 12, $this->pdfText((string)$program['title']), 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $this->pdfText('Description'), 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 7, $this->pdfText((string)($program['description'] ?: 'No description.')));
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, $this->pdfText('Duration: ' . (int)$program['duration_weeks'] . ' weeks'), 0, 1);
        $pdf->Cell(0, 7, $this->pdfText('Difficulty: ' . ucfirst((string)$program['difficulty_level'])), 0, 1);
        $pdf->Ln(3);
        $pdf->Line(14, $pdf->GetY(), 196, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 9, $this->pdfText('Exercises'), 0, 1);
        $pdf->Ln(1);

        if ($exercises === []) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 7, $this->pdfText('No exercises linked to this coaching program.'), 0, 1);
        } else {
            $index = 1;
            foreach ($exercises as $exercise) {
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                }

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 8, $this->pdfText('Exercise ' . $index . ': ' . (string)$exercise['name']), 1, 1, 'L', true);

                $pdf->SetFont('Arial', '', 11);
                $pdf->Cell(0, 7, $this->pdfText('- Sets: ' . (int)$exercise['sets']), 'LR', 1);
                $pdf->Cell(0, 7, $this->pdfText('- Reps: ' . (int)$exercise['reps']), 'LR', 1);
                $pdf->Cell(0, 7, $this->pdfText('- Rest time: ' . (string)$exercise['rest_time']), 'LR', 1);
                $pdf->MultiCell(0, 7, $this->pdfText('- Description: ' . (string)($exercise['description'] ?: 'No description.')), 'LRB');
                $pdf->Ln(3);
                $index++;
            }
        }

        $pdf->Output('D', 'coaching_program.pdf');
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

    private function loadFpdf(): bool
    {
        if (class_exists('FPDF')) {
            return true;
        }

        $paths = [
            __DIR__ . '/../vendor/fpdf/fpdf.php',
            __DIR__ . '/../vendor/setasign/fpdf/fpdf.php',
            __DIR__ . '/../fpdf/fpdf.php',
            'C:/xampp/htdocs/fpdf/fpdf.php',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                break;
            }
        }

        return class_exists('FPDF');
    }

    private function pdfText(string $text): string
    {
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        return $converted !== false ? $converted : $text;
    }

    private function sortRows(array $rows, string $column, string $order): array
    {
        $safeOrder = strtolower($order) === 'asc' ? 1 : -1;
        $safeColumn = in_array($column, ['duration_weeks', 'difficulty_level', 'created_at'], true)
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
