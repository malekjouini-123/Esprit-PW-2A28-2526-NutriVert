<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/Model/Evenement.php';
require_once dirname(__DIR__) . '/Model/Inscription.php';
require_once dirname(__DIR__) . '/Model/Category.php';

/**
 * Contrôleur pour le Back Office NutriVert.
 */
class BackController
{
    /**
     * Affiche la liste des éléments demandés (events, participants, categories).
     */
    public function render(): void
    {
        $subpage = $_GET['sub'] ?? 'events';
        
        switch ($subpage) {
            case 'categories':
                $categories = Category::findAll();
                include dirname(__DIR__) . '/View/Back/view_categories.php';
                break;
            case 'participants':
                $all_participants = Inscription::findAll();
                include dirname(__DIR__) . '/View/Back/view_participants.php';
                break;
            default: // 'events'
                $evenements = Evenement::findAll();
                include dirname(__DIR__) . '/View/Back/view_evenements.php';
                break;
        }
    }

    /**
     * Enregistre un événement.
     */
    public function saveEvent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        
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
            header('Location: admin.php?sub=events&success=saved&id=' . $event->id);
        } else {
            header('Location: admin.php?sub=events&error=save_failed');
        }
        exit;
    }

    /**
     * Supprime un événement.
     */
    public function deleteEvent(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (Evenement::delete($id)) {
            header('Location: admin.php?sub=events&success=deleted');
        } else {
            header('Location: admin.php?sub=events&error=delete_failed');
        }
        exit;
    }

    /**
     * Enregistre une catégorie.
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
            header('Location: admin.php?sub=categories&success=saved&id=' . $category->id);
        } else {
            header('Location: admin.php?sub=categories&error=save_failed');
        }
        exit;
    }

    /**
     * Supprime une catégorie.
     */
    public function deleteCategory(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (Category::delete($id)) {
            header('Location: admin.php?sub=categories&success=deleted');
        } else {
            header('Location: admin.php?sub=categories&error=delete_failed');
        }
        exit;
    }

    /**
     * Enregistre ou met à jour un participant.
     */
    public function saveParticipant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        
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
            header('Location: admin.php?sub=participants&success=saved&id=' . ($data['id'] > 0 ? $data['id'] : $inscription->id));
        } else {
            header('Location: admin.php?sub=participants&error=save_failed');
        }
        exit;
    }

    /**
     * Supprime un participant.
     */
    public function deleteParticipant(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (Inscription::delete($id)) {
            header('Location: admin.php?sub=participants&success=deleted');
        } else {
            header('Location: admin.php?sub=participants&error=delete_failed');
        }
        exit;
    }
}
