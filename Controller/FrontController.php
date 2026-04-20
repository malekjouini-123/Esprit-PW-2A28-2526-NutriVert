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
        $participation_count = 0;
        if (isset($_SESSION['participant_email'])) {
            $participant = Inscription::findOneByEmail($_SESSION['participant_email']);
            if ($participant) {
                $user_inscriptions = Inscription::findByEmail($participant->email);
                $participation_count = count($user_inscriptions);
            }
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
     * Gère la connexion pour le suivi.
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
            header('Location: index.php?sub=participants&login=success');
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
            'categorie_preferee' => (string)($_POST['categorie_preferee'] ?? ''),
            'objectif' => (string)($_POST['objectif'] ?? 'maintien'),
            'face_id' => (string)($_POST['face_id'] ?? '')
        ];

        $inscription = new Inscription($data);
        if ($data['id'] > 0) {
            $existing = Inscription::findById($data['id']);
            if ($existing && empty($data['mot_de_passe'])) {
                $inscription->mot_de_passe = $existing->mot_de_passe;
            }
            $res = $inscription->update();
        } else {
            $res = $inscription->save();
        }

        if ($res) {
            header('Location: index.php?sub=participants&success=inscribed&id=' . ($data['id'] > 0 ? $data['id'] : $inscription->id));
        } else {
            header('Location: index.php?sub=participants&error=failed');
        }
        exit;
    }

    /**
     * Gère la sauvegarde d'un événement depuis le Front.
     */
    public function saveEvent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'id' => $id,
            'titre' => (string)($_POST['titre'] ?? ''),
            'categorie' => (string)($_POST['categorie'] ?? ''),
            'description' => (string)($_POST['description'] ?? ''),
            'date_evenement' => (string)($_POST['date_evenement'] ?? ''),
            'lieu' => (string)($_POST['lieu'] ?? ''),
            'prix_participation' => (float)($_POST['prix'] ?? 0),
            'capacite_max' => (int)($_POST['capacite'] ?? 0),
            'statut' => (string)($_POST['statut'] ?? 'Actif'),
            'image_url' => (string)($_POST['image_url'] ?? '')
        ];

        $event = new Evenement($data);
        $res = ($id > 0) ? $event->update() : $event->save();

        if ($res) {
            header('Location: index.php?sub=events&success=saved&id=' . ($id > 0 ? $id : $event->id));
        } else {
            header('Location: index.php?sub=events&error=save_failed');
        }
        exit;
    }

    /**
     * Gère la sauvegarde d'une catégorie depuis le Front.
     */
    public function saveCategory(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'id' => $id > 0 ? $id : 0,
            'nom' => (string)($_POST['nom'] ?? ''),
            'description' => (string)($_POST['description'] ?? ''),
            'atelier' => (string)($_POST['atelier'] ?? ''),
            'images' => (array)($_POST['images'] ?? [])
        ];

        $category = new Category($data);
        if ($category->save()) {
            header('Location: index.php?sub=categories&success=saved&id=' . $category->id);
        } else {
            header('Location: index.php?sub=categories&error=save_failed');
        }
        exit;
    }
}
