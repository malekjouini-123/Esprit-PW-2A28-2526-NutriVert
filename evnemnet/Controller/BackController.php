<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/Evenement.php';
require_once __DIR__ . '/../Model/Category.php';
require_once __DIR__ . '/../Model/Participant.php';
require_once __DIR__ . '/../Model/PersonalRecommendation.php';

class BackController extends BaseController
{
    private function getEventFormData(?array $existingEvent = null): array
    {
        $uploaded = save_uploaded_image($_FILES['image_file'] ?? [], 'events');

        return [
            'titre' => trim((string)($_POST['titre'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'date_evenement' => trim((string)($_POST['date_evenement'] ?? '')),
            'lieu' => trim((string)($_POST['lieu'] ?? '')),
            'prix' => (float)($_POST['prix'] ?? 0),
            'capacite' => (int)($_POST['capacite'] ?? 0),
            'categorie_id' => !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
            'image_url' => $uploaded ?: (!empty($_POST['image_url']) ? trim((string)$_POST['image_url']) : ($existingEvent['image_url'] ?? null)),
            'is_published' => !empty($_POST['is_published']) ? 1 : 0,
        ];
    }

    private function getCategoryFormData(?array $existingCategory = null): array
    {
        $uploaded = save_uploaded_image($_FILES['image_file'] ?? [], 'categories');

        return [
            'nom' => trim((string)($_POST['nom'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'image_url' => $uploaded ?: ($existingCategory['image_url'] ?? null),
            'is_published' => !empty($_POST['is_published']) ? 1 : 0,
        ];
    }

    private function getParticipantFormData(bool $allowEmptyPassword = true): array
    {
        $data = [
            'nom' => trim((string)($_POST['nom'] ?? '')),
            'prenom' => trim((string)($_POST['prenom'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telephone' => !empty($_POST['telephone']) ? trim((string)$_POST['telephone']) : null,
            'poids' => (float)($_POST['poids'] ?? 0),
            'taille' => (float)($_POST['taille'] ?? 0),
            'imc' => (float)($_POST['imc'] ?? 0),
            'lieu' => trim((string)($_POST['lieu'] ?? '')),
            'objectif' => trim((string)($_POST['objectif'] ?? 'maintien')),
        ];

        $password = trim((string)($_POST['mot_de_passe'] ?? ''));
        if ($password !== '' || !$allowEmptyPassword) {
            $data['mot_de_passe'] = $password;
        }

        return $data;
    }

    private function buildParticipantEmailBody(array $participant, string $message): string
    {
        $fullName = trim((string)($participant['prenom'] ?? '') . ' ' . (string)($participant['nom'] ?? ''));
        $safeName = htmlspecialchars($fullName !== '' ? $fullName : 'Participant', ENT_QUOTES, 'UTF-8');
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

        return '
            <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #2D3E2B;">
                <h2 style="color:#2D3E2B;">Message de l\'administration</h2>
                <p>Bonjour <strong>' . $safeName . '</strong>,</p>
                <div style="background:#F5F9F5; border-left:4px solid #53B38C; padding:14px 16px; border-radius:8px; margin:16px 0;">
                    ' . $safeMessage . '
                </div>
                <p>Cordialement,<br>Gestion des Événements</p>
            </div>
        ';
    }

    // --- EVENEMENTS ---
    public function listEvents()
    {
        $evenements = $this->eventGetAll();
        require_once __DIR__ . '/../View/Back/evenements_list.php';
    }

    public function showEvent(int $id)
    {
        $evenement = $this->eventGetById($id);
        if (!$evenement) {
            header("Location: admin.php?action=events");
            exit;
        }
        require_once __DIR__ . '/../View/Back/evenement_show.php';
    }

    public function addEvent()
    {
        $categories = $this->categoryGetAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getEventFormData();
            if ($this->eventCreate($data)) {
                header("Location: admin.php?action=events");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Back/evenement_form.php';
    }

    public function editEvent(int $id)
    {
        $evenement = $this->eventGetById($id);
        if (!$evenement) {
            header("Location: admin.php?action=events");
            exit;
        }
        $categories = $this->categoryGetAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getEventFormData($evenement);
            if ($this->eventUpdate($id, $data)) {
                header("Location: admin.php?action=events");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Back/evenement_form.php';
    }

    public function toggleEventPublish(int $id)
    {
        $evenement = $this->eventGetById($id);
        if (!$evenement) {
            header("Location: admin.php?action=events");
            exit;
        }
        $new = !empty($evenement['is_published']) ? 0 : 1;
        $this->eventUpdate($id, [
            'titre' => $evenement['titre'],
            'description' => $evenement['description'],
            'date_evenement' => $evenement['date_evenement'],
            'lieu' => $evenement['lieu'],
            'prix' => (float)$evenement['prix'],
            'capacite' => (int)$evenement['capacite'],
            'categorie_id' => (int)$evenement['categorie_id'],
            'image_url' => $evenement['image_url'] ?? null,
            'is_published' => $new,
        ]);
        header("Location: admin.php?action=events");
        exit;
    }

    public function deleteEvent(int $id)
    {
        $this->eventDelete($id);
        header("Location: admin.php?action=events");
        exit;
    }

    // --- CATEGORIES ---
    public function listCategories()
    {
        $categories = $this->categoryGetAll();
        require_once __DIR__ . '/../View/Back/categories_list.php';
    }

    public function addCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getCategoryFormData();
            if ($this->categoryCreate($data['nom'], $data['description'], $data['image_url'], $data['is_published'])) {
                header("Location: admin.php?action=categories");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Back/category_form.php';
    }

    public function showCategory(int $id)
    {
        $category = $this->categoryGetById($id);
        if (!$category) {
            header("Location: admin.php?action=categories");
            exit;
        }
        require_once __DIR__ . '/../View/Back/category_show.php';
    }

    public function editCategory(int $id)
    {
        $category = $this->categoryGetById($id);
        if (!$category) {
            header("Location: admin.php?action=categories");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getCategoryFormData($category);
            if ($this->categoryUpdate($id, $data['nom'], $data['description'], $data['image_url'], $data['is_published'])) {
                header("Location: admin.php?action=categories");
                exit;
            }
        }

        require_once __DIR__ . '/../View/Back/category_form.php';
    }

    public function toggleCategoryPublish(int $id)
    {
        $category = $this->categoryGetById($id);
        if (!$category) {
            header("Location: admin.php?action=categories");
            exit;
        }
        $new = !empty($category['is_published']) ? 0 : 1;
        $this->categoryUpdate($id, $category['nom'], $category['description'] ?? null, $category['image_url'] ?? null, $new);
        header("Location: admin.php?action=categories");
        exit;
    }

    public function deleteCategory(int $id)
    {
        $this->categoryDelete($id);
        header("Location: admin.php?action=categories");
        exit;
    }

    // --- PARTICIPANTS ---
    public function listParticipants()
    {
        $participants = $this->participantGetAll();
        require_once __DIR__ . '/../View/Back/participants_list.php';
    }

    public function showParticipant(int $id)
    {
        $participant = $this->participantGetById($id);
        if (!$participant) {
            header("Location: admin.php?action=participants");
            exit;
        }
        require_once __DIR__ . '/../View/Back/participant_show.php';
    }

    public function editParticipant(int $id)
    {
        $participant = $this->participantGetById($id);
        if (!$participant) {
            header("Location: admin.php?action=participants");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getParticipantFormData();
            if ($this->participantUpdate($id, $data)) {
                header("Location: admin.php?action=participants");
                exit;
            }
        }

        require_once __DIR__ . '/../View/Back/participant_form.php';
    }

    public function deleteParticipant(int $id)
    {
        $this->participantDelete($id);
        header("Location: admin.php?action=participants");
        exit;
    }

    public function emailParticipant(int $id)
    {
        $participant = $this->participantGetById($id);
        if (!$participant) {
            $_SESSION['flash_error'] = 'Participant introuvable.';
            header("Location: admin.php?action=participants");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash_error'] = 'Token CSRF invalide.';
                header("Location: admin.php?action=email_participant&id=$id");
                exit;
            }

            $subject = trim((string)($_POST['subject'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));

            if ($subject === '' || $message === '') {
                $_SESSION['flash_error'] = 'Sujet et message sont obligatoires.';
            } else {
                [$sent, $feedback] = send_email(
                    (string)$participant['email'],
                    $subject,
                    $this->buildParticipantEmailBody($participant, $message)
                );

                if ($sent) {
                    $_SESSION['flash_success'] = 'Email envoye au participant avec succes.';
                    header("Location: admin.php?action=show_participant&id=$id");
                    exit;
                }

                $_SESSION['flash_error'] = 'Echec de l\'envoi: ' . $feedback;
            }
        }

        require_once __DIR__ . '/../View/Back/participant_email.php';
    }

    // --- RECOMMENDATIONS PERSONNALISEES ---
    public function listRecommendations()
    {
        $recommendations = $this->recommendationGetAll();
        require_once __DIR__ . '/../View/Back/recommendations_list.php';
    }

    public function showRecommendation(int $id)
    {
        $recommendation = $this->recommendationGetById($id);
        if (!$recommendation) {
            header("Location: admin.php?action=recommendations");
            exit;
        }
        require_once __DIR__ . '/../View/Back/recommendation_show.php';
    }

    public function deleteRecommendation(int $id)
    {
        $this->recommendationDelete($id);
        header("Location: admin.php?action=recommendations");
        exit;
    }
}
