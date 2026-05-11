<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/Evenement.php';
require_once __DIR__ . '/../Model/Category.php';
require_once __DIR__ . '/../Model/Participant.php';
require_once __DIR__ . '/../Model/PersonalRecommendation.php';

class FrontController extends BaseController
{
    private function getEventFormData(): array
    {
        $imageUrl = null;

        if (!empty($_POST['selected_image_url'])) {
            $imageUrl = trim((string)$_POST['selected_image_url']);
        } elseif (!empty($_FILES['image_file']['name'])) {
            $imageUrl = save_uploaded_image($_FILES['image_file'] ?? [], 'events');
        } elseif (!empty($_POST['image_url'])) {
            $imageUrl = trim((string)$_POST['image_url']);
        }

        return [
            'titre' => trim((string)($_POST['titre'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'date_evenement' => trim((string)($_POST['date_evenement'] ?? '')),
            'lieu' => trim((string)($_POST['lieu'] ?? '')),
            'prix' => (float)($_POST['prix'] ?? 0),
            'capacite' => (int)($_POST['capacite'] ?? 0),
            'categorie_id' => !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
            'image_url' => $imageUrl,
            'is_published' => !empty($_POST['is_published']) ? 1 : 0,
        ];
    }

    private function getCategoryFormData(): array
    {
        $imageUrl = null;

        if (!empty($_POST['selected_category_image'])) {
            $imageUrl = trim((string)$_POST['selected_category_image']);
        } elseif (!empty($_FILES['image_file']['name'])) {
            $imageUrl = save_uploaded_image($_FILES['image_file'] ?? [], 'categories');
        }

        return [
            'nom' => trim((string)($_POST['nom'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'image_url' => $imageUrl,
            'is_published' => !empty($_POST['is_published']) ? 1 : 0,
        ];
    }

    private function getParticipantFormData(bool $withDefaultPassword = false): array
    {
        $motDePasse = trim((string)($_POST['mot_de_passe'] ?? ''));
        if ($withDefaultPassword && $motDePasse === '') {
            $motDePasse = 'pass123';
        }

        return [
            'nom' => trim((string)($_POST['nom'] ?? '')),
            'prenom' => trim((string)($_POST['prenom'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'mot_de_passe' => $motDePasse,
            'telephone' => !empty($_POST['telephone']) ? trim((string)$_POST['telephone']) : null,
            'poids' => (float)($_POST['poids'] ?? 0),
            'taille' => (float)($_POST['taille'] ?? 0),
            'imc' => (float)($_POST['imc'] ?? 0),
            'lieu' => trim((string)($_POST['lieu'] ?? '')),
            'objectif' => trim((string)($_POST['objectif'] ?? 'maintien')),
        ];
    }

    private function getRecommendationFormData(): array
    {
        return [
            'titre' => trim((string)($_POST['titre'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'categorie_preferee' => !empty($_POST['categorie_preferee']) ? trim((string)$_POST['categorie_preferee']) : null,
            'budget_max' => !empty($_POST['budget_max']) ? (float)$_POST['budget_max'] : null,
            'localisation' => !empty($_POST['localisation']) ? trim((string)$_POST['localisation']) : null,
            'ai_suggestion' => null,
            'evenements_suggeres' => null,
        ];
    }

    private function filterEvents(array $evenements): array
    {
        if (!empty($_GET['search_title'])) {
            $searchTitle = strtolower(trim((string)$_GET['search_title']));
            $evenements = array_filter($evenements, static function ($evenement) use ($searchTitle) {
                return strpos(strtolower((string)($evenement['titre'] ?? '')), $searchTitle) !== false;
            });
        }

        if (!empty($_GET['search_date'])) {
            $searchDate = (string)$_GET['search_date'];
            $evenements = array_filter($evenements, static function ($evenement) use ($searchDate) {
                return date('Y-m-d', strtotime((string)($evenement['date_evenement'] ?? ''))) === $searchDate;
            });
        }

        if (!empty($_GET['sort_price'])) {
            $sortPrice = (string)$_GET['sort_price'];
            usort($evenements, static function ($a, $b) use ($sortPrice) {
                $left = (float)($a['prix'] ?? 0);
                $right = (float)($b['prix'] ?? 0);
                return $sortPrice === 'asc' ? ($left <=> $right) : ($right <=> $left);
            });
        }

        return $evenements;
    }

    public function index()
    {
        $evenements = $this->eventGetAllPublished();
        $categories = $this->categoryGetAllPublished();
        $recommandations = $this->eventGetRecommendations();
        require_once __DIR__ . '/../View/Front/index.php';
    }

    public function showEvent(int $id)
    {
        $evenement = $this->eventGetById($id);
        if (!$evenement) {
            header("Location: index.php");
            exit;
        }
        require_once __DIR__ . '/../View/Front/event_details.php';
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                die("Token CSRF invalide.");
            }
            
            $evenement_id = (int)$_POST['evenement_id'];
            $data = $this->getParticipantFormData(true);

            // Validation basique
            if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
                $error = "Tous les champs requis doivent être remplis.";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = "Email invalide.";
            } else {
                $participant_id = $this->participantCreate($data);
                if ($participant_id > 0 && $this->participantRegisterToEvent($participant_id, $evenement_id)) {
                    $mailSent = 0;
                    $emailMessage = "";
                    $evenement = $this->eventGetById($evenement_id);
                    if ($evenement) {
                        $date = date('d/m/Y H:i', strtotime($evenement['date_evenement']));
                        $titre = htmlspecialchars((string)$evenement['titre']);
                        $lieu = htmlspecialchars((string)$evenement['lieu']);

                        $prenom = htmlspecialchars((string)($_POST['prenom'] ?? ''));
                        $nom = htmlspecialchars((string)($_POST['nom'] ?? ''));

                        $subject = "Confirmation d'inscription : " . (string)$evenement['titre'];
                        $body = "
                            <div style=\"font-family: Arial, sans-serif; line-height: 1.6;\">
                                <h2 style=\"color:#2D3E2B;\">Inscription confirmée</h2>
                                <p>Bonjour <strong>{$prenom} {$nom}</strong>,</p>
                                <p>Votre inscription à l'événement suivant a bien été enregistrée :</p>
                                <ul>
                                    <li><strong>Événement :</strong> {$titre}</li>
                                    <li><strong>Date :</strong> {$date}</li>
                                    <li><strong>Lieu :</strong> {$lieu}</li>
                                </ul>
                                <p>À bientôt.</p>
                            </div>
                        ";

                        [$mailOk, $emailMessage] = send_email((string)$_POST['email'], $subject, $body);
                        $mailSent = $mailOk ? 1 : 0;
                        if (!$mailOk) {
                            $_SESSION['flash_mail_error'] = $emailMessage;
                        }
                    }

                    header("Location: index.php?action=event&id=$evenement_id&success=1&mail_sent=$mailSent");
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
            
            // Si erreur, afficher le message
            if (isset($error)) {
                echo "<div style='color:red; padding:20px;'>Erreur : $error</div>";
            }
        }
        require_once __DIR__ . '/../View/Front/event_details.php';
    }

    public function addEvent()
    {
        $categories = $this->categoryGetAllPublished();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getEventFormData();
            if ($this->eventCreate($data)) {
                header("Location: index.php?success=event");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Front/add_event.php';
    }

    public function addCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getCategoryFormData();
            if ($this->categoryCreate($data['nom'], $data['description'], $data['image_url'], $data['is_published'])) {
                header("Location: index.php?success=category");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Front/add_category.php';
    }

    public function addParticipant()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getParticipantFormData(true);

            if ($this->participantCreate($data)) {
                header("Location: index.php?success=participant");
                exit;
            }
        }
        require_once __DIR__ . '/../View/Front/add_participant.php';
    }

    public function listParticipants()
    {
        $participants = $this->participantGetAll();
        require_once __DIR__ . '/../View/Front/participants_list.php';
    }

    public function listCategories()
    {
        $categories = $this->categoryGetAllPublished();
        require_once __DIR__ . '/../View/Front/categories_list.php';
    }

    public function listEvents()
    {
        $evenements = $this->eventGetAllPublished();
        $evenements = $this->filterEvents($evenements);
        require_once __DIR__ . '/../View/Front/events_list.php';
    }

    public function exportPDF()
    {
        $evenements = $this->eventGetAll();
        $evenements = $this->filterEvents($evenements);
        $this->generatePDF($evenements);
    }

    private function generatePDF(array $evenements)
    {
        // NOTE:
        // Le code précédent envoyait du HTML avec un header application/pdf -> fichier "PDF" invalide.
        // Ici on génère une page imprimable. L'utilisateur peut "Enregistrer en PDF" depuis la boîte d'impression.
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="evenements_' . date('Y-m-d_H-i-s') . '.html"');

        $generatedAt = date('d/m/Y à H:i:s');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Export des événements</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2D3E2B; text-align: center; margin-bottom: 6px; }
                .meta { text-align:center; color:#666; margin-bottom: 18px; }
                .hint { background:#E6F4EE; border-left: 6px solid #53B38C; padding: 10px 12px; border-radius: 8px; color:#2D3E2B; }
                table { width: 100%; border-collapse: collapse; margin-top: 16px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
                th { background-color: #E6F4EE; font-weight: bold; color: #2D3E2B; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .footer { margin-top: 22px; text-align: center; color: #999; font-size: 12px; }
                @media print {
                    .no-print { display:none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class="no-print hint">
                Astuce : dans la fenêtre d'impression, choisis <strong>“Enregistrer au format PDF”</strong>.
            </div>

            <h1>Rapport des Événements</h1>
            <div class="meta">Généré le <?= htmlspecialchars($generatedAt) ?></div>

            <?php if (empty($evenements)): ?>
                <p style="text-align: center; color: #999; margin-top: 40px;">Aucun événement à afficher.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Capacité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evenements as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['titre']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td>
                                <td><?= htmlspecialchars($e['lieu']) ?></td>
                                <td><?= htmlspecialchars($e['categorie_nom'] ?? 'N/A') ?></td>
                                <td><?= number_format((float)$e['prix'], 2) ?> €</td>
                                <td><?= (int)$e['capacite'] ?> places</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="footer">
                <p>Document généré automatiquement.</p>
            </div>

            <script>
                // Lance l'impression automatiquement (l'utilisateur peut enregistrer en PDF)
                window.print();
            </script>
        </body>
        </html>
        <?php

        echo ob_get_clean();
        exit;
    }

    public function recommendations()
    {
        $recommandations = $this->eventGetRecommendations();
        require_once __DIR__ . '/../View/Front/recommendations.php';
    }

    public function createPersonalRecommendation()
    {
        $categories = $this->categoryGetAll();
        $ai_response = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Si c'est une requête AJAX pour l'analyse
            if (isset($_POST['ajax_analyze'])) {
                $data = $this->getRecommendationFormData();

                // Obtenir les suggestions de l'IA
                $ai_response = $this->recommendationGenerateAISuggestions($data);

                // Retourner JSON pour AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'suggestions' => $ai_response['suggestions'],
                    'matching_events' => $ai_response['matching_events']
                ]);
                exit;
            }

            // Si c'est une création normale
            $data = $this->getRecommendationFormData();

            // Obtenir les suggestions de l'IA
            $ai_response = $this->recommendationGenerateAISuggestions($data);

            $data['ai_suggestion'] = json_encode($ai_response['suggestions']);
            $data['evenements_suggeres'] = json_encode(array_map(function($e) {
                return ['id' => $e['event']['id'], 'titre' => $e['event']['titre'], 'score' => $e['score']];
            }, $ai_response['matching_events']));

            $this->recommendationCreate($data);
            header("Location: index.php?action=my_recommendations&success=1");
            exit;
        }

        require_once __DIR__ . '/../View/Front/create_recommendation.php';
    }

    public function myRecommendations()
    {
        $recommendations = $this->recommendationGetAll();
        require_once __DIR__ . '/../View/Front/my_recommendations.php';
    }

    public function deleteRecommendation(int $id)
    {
        $this->recommendationDelete($id);
        header("Location: index.php?action=my_recommendations&deleted=1");
        exit;
    }
}
