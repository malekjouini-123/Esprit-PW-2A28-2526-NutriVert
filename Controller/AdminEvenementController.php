<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/Evenement.php';

/**
 * Contrôleur pour la gestion des événements par l'administrateur.
 */
class AdminEvenementController
{
    /**
     * Affiche la liste des événements.
     */
    public function index(): void
    {
        $evenements = Evenement::findAll();
        $this->render('evenement_list', ['evenements' => $evenements]);
    }

    /**
     * Affiche le formulaire de création ou d'édition d'un événement.
     */
    public function form(int $id = 0): void
    {
        $evenement = ($id > 0) ? Evenement::findById($id) : new Evenement();
        if ($id > 0 && !$evenement) {
            header('Location: admin_evenements.php?error=notfound');
            exit;
        }
        $this->render('evenement_form', ['evenement' => $evenement]);
    }

    /**
     * Enregistre un événement (création ou mise à jour).
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_evenements.php');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $evenement = ($id > 0) ? Evenement::findById($id) : new Evenement();
        
        if (!$evenement) {
            header('Location: admin_evenements.php?error=notfound');
            exit;
        }

        $evenement->titre = (string)($_POST['titre'] ?? '');
        $evenement->description = (string)($_POST['description'] ?? '');
        $evenement->date_evenement = (string)($_POST['date_evenement'] ?? '');
        $evenement->lieu = (string)($_POST['lieu'] ?? '');
        $evenement->image_url = (string)($_POST['image_url'] ?? '');

        if ($evenement->save()) {
            header('Location: admin_evenements.php?success=saved');
        } else {
            header('Location: admin_evenements.php?error=save_failed');
        }
        exit;
    }

    /**
     * Supprime un événement.
     */
    public function delete(int $id): void
    {
        if (Evenement::delete($id)) {
            header('Location: admin_evenements.php?success=deleted');
        } else {
            header('Location: admin_evenements.php?error=delete_failed');
        }
        exit;
    }

    /**
     * Fonction de rendu pour inclure le layout admin.
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include dirname(__DIR__) . "/view/admin/{$view}.php";
        $adminContent = ob_get_clean();
        include dirname(__DIR__) . '/view/layout/layout_admin.php';
    }
}
