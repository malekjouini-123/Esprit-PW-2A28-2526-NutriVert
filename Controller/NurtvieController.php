<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

/**
 * Contrôleur front — affiche la page publique NutriVert (MVC).
 */
class NurtvieController
{
    public function renderEvenements(): void
    {
        require_once dirname(__DIR__) . '/model/Evenement.php';
        require_once dirname(__DIR__) . '/model/Inscription.php';
        require_once dirname(__DIR__) . '/model/Category.php';
        
        $evenements = Evenement::findAll();
        $all_participants = Inscription::findAll();
        $categories = Category::findAll();
        
        $subpage = $_GET['sub'] ?? 'events';
        $view = $_GET['action'] ?? 'list';

        // Gestion du participant connecté (Suivi)
        $participant = null;
        if (isset($_SESSION['participant_email'])) {
            $participant = Inscription::findOneByEmail($_SESSION['participant_email']);
        }
        
        if ($view === 'inscription') {
            $eventId = (int)($_GET['event_id'] ?? 0);
            $event = Evenement::findById($eventId);
            if (!$event) {
                header('Location: index.php');
                exit;
            }
            include dirname(__DIR__) . '/view/evenement_inscription.php';
        } else {
            // Selon la sous-page demandée
            switch ($subpage) {
                case 'categories':
                    $email = $_GET['email'] ?? '';
                    $participant = $email ? Inscription::findOneByEmail($email) : $participant;
                    include dirname(__DIR__) . '/view/view_categories.php';
                    break;
                case 'participants':
                    $email = $_GET['email'] ?? '';
                    $inscriptions = $email ? Inscription::findByEmail($email) : [];
                    include dirname(__DIR__) . '/view/view_participants.php';
                    break;
                default: // 'events'
                    include dirname(__DIR__) . '/view/view_evenements.php';
                    break;
            }
        }
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        require_once dirname(__DIR__) . '/model/Inscription.php';
        $email = (string)($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $participant = Inscription::findOneByEmail($email);
        
        // Note: Simple check without hashing for now as per previous code style
        if ($participant && $participant->mot_de_passe === $password) {
            $_SESSION['participant_email'] = $email;
            header('Location: index.php?sub=events&login=success');
        } else {
            header('Location: index.php?sub=events&error=auth_failed');
        }
        exit;
    }

    public function handleLogout(): void
    {
        unset($_SESSION['participant_email']);
        header('Location: index.php');
        exit;
    }

    public function handleInscription(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        require_once dirname(__DIR__) . '/model/Inscription.php';

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

        $existing = null;
        if ($data['id'] > 0) {
            $existing = Inscription::findById($data['id']);
        }
        
        if ($existing && empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = $existing->mot_de_passe;
        }

        $inscription = new Inscription($data);
        if ($inscription->save()) {
            header('Location: index.php?sub=participants&success=inscribed&id=' . nv_pdo()->lastInsertId());
        } else {
            header('Location: index.php?sub=participants&error=failed');
        }
        exit;
    }

    public function handleParticipantUpdate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        require_once dirname(__DIR__) . '/model/Inscription.php';
        
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

        $existing = Inscription::findById($data['id']);
        if ($existing && empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = $existing->mot_de_passe;
        }

        $inscription = new Inscription($data);
        if ($inscription->update()) {
            header('Location: index.php?sub=participants&success=updated&id=' . $data['id']);
        } else {
            header('Location: index.php?sub=participants&error=update_failed');
        }
        exit;
    }

    public function handleParticipantDelete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        require_once dirname(__DIR__) . '/model/Inscription.php';
        if (Inscription::delete($id)) {
            header('Location: index.php?sub=participants&success=deleted');
        } else {
            header('Location: index.php?sub=participants&error=delete_failed');
        }
        exit;
    }

    public function handleEventSave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        require_once dirname(__DIR__) . '/model/Evenement.php';
        
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'id' => $id > 0 ? $id : 0,
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
        if ($event->save()) {
            header('Location: index.php?sub=events&success=saved&id=' . $event->id);
        } else {
            header('Location: index.php?sub=events&error=save_failed');
        }
        exit;
    }

    public function handleEventDelete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        require_once dirname(__DIR__) . '/model/Evenement.php';
        if (Evenement::delete($id)) {
            header('Location: index.php?sub=events&success=deleted');
        } else {
            header('Location: index.php?sub=events&error=delete_failed');
        }
        exit;
    }

    public function handleCategorySave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        require_once dirname(__DIR__) . '/model/Category.php';
        
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

    public function handleCategoryDelete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        require_once dirname(__DIR__) . '/model/Category.php';
        if (Category::delete($id)) {
            header('Location: index.php?sub=categories&success=deleted');
        } else {
            header('Location: index.php?sub=categories&error=delete_failed');
        }
        exit;
    }
}
