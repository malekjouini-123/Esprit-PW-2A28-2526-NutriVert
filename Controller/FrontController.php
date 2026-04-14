<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/Model/Evenement.php';
require_once dirname(__DIR__) . '/Model/Inscription.php';
require_once dirname(__DIR__) . '/Model/Category.php';

/**
 * Contrôleur pour le Front Office NutriVert.
 */
class FrontController
{
    /**
     * Affiche les pages publiques.
     */
    public function render(): void
    {
        $evenements = Evenement::findAll();
        $all_participants = Inscription::findAll();
        $categories = Category::findAll();
        
        $subpage = $_GET['sub'] ?? 'events';
        $action = $_GET['action'] ?? 'list';

        // Gestion du participant connecté (Suivi)
        $participant = null;
        if (isset($_SESSION['participant_email'])) {
            $participant = Inscription::findOneByEmail($_SESSION['participant_email']);
        }
        
        if ($action === 'inscription') {
            $eventId = (int)($_GET['event_id'] ?? 0);
            $event = Evenement::findById($eventId);
            if (!$event) {
                header('Location: index.php');
                exit;
            }
            include dirname(__DIR__) . '/View/Front/evenement_inscription.php';
        } else {
            switch ($subpage) {
                case 'categories':
                    $email = $_GET['email'] ?? '';
                    $participant = $email ? Inscription::findOneByEmail($email) : $participant;
                    include dirname(__DIR__) . '/View/Front/view_categories.php';
                    break;
                case 'participants':
                    $email = $_GET['email'] ?? '';
                    $inscriptions = $email ? Inscription::findByEmail($email) : [];
                    include dirname(__DIR__) . '/View/Front/view_participants.php';
                    break;
                default: // 'events'
                    include dirname(__DIR__) . '/View/Front/view_evenements.php';
                    break;
            }
        }
    }

    /**
     * Gère la connexion utilisateur.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $email = (string)($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $participant = Inscription::findOneByEmail($email);
        
        if ($participant && $participant->mot_de_passe === $password) {
            $_SESSION['participant_email'] = $email;
            header('Location: index.php?sub=events&login=success');
        } else {
            header('Location: index.php?sub=events&error=auth_failed');
        }
        exit;
    }

    /**
     * Gère la déconnexion.
     */
    public function logout(): void
    {
        unset($_SESSION['participant_email']);
        header('Location: index.php');
        exit;
    }

    /**
     * Gère l'inscription d'un participant.
     */
    public function saveInscription(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'evenement_id' => (int)($_POST['evenement_id'] ?? 0),
            'nom' => (string)($_POST['nom'] ?? ''),
            'prenom' => (string)($_POST['prenom'] ?? ''),
            'email' => (string)($_POST['email'] ?? ''),
            'mot_de_passe' => (string)($_POST['mot_de_passe'] ?? ''),
            'telephone' => (string)($_POST['telephone'] ?? ''),
            'lieu' => (string)($_POST['lieu'] ?? ''),
            'date_naissance' => (string)($_POST['date_naissance'] ?? ''),
            'poids' => (float)($_POST['poids'] ?? 0),
            'taille' => (float)($_POST['taille'] ?? 0),
            'imc' => (float)($_POST['imc'] ?? 0),
            'categorie_preferee' => (string)($_POST['categorie_preferee'] ?? '')
        ];

        $inscription = new Inscription($data);
        if ($inscription->save()) {
            header('Location: index.php?sub=participants&success=inscribed&id=' . $inscription->id);
        } else {
            header('Location: index.php?sub=participants&error=failed');
        }
        exit;
    }
}
