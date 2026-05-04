<?php
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Database.php';

class AdminController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /** List all users and their Face ID registration status. */
    public function listUsersWithFaces() {
        $this->requireAdmin();
        $users = $this->userModel->getAllWithFaceStatus();
        include __DIR__ . '/../View/Backoffice/faces.php';
    }

    /** Delete a Face ID encoding for one user. */
    public function deleteUserFace($userId) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-faces');
            exit;
        }

        $this->userModel->deleteFaceEncoding((int)$userId);
        $_SESSION['success'] = 'Face ID supprime.';
        header('Location: index.php?action=admin-faces');
        exit;
    }

    /** Show Face ID metadata for one user. */
    public function viewUserFace($userId) {
        $this->requireAdmin();
        $face = $this->userModel->getUserFaceDetails((int)$userId);

        if (!$face) {
            $_SESSION['error'] = 'Utilisateur introuvable.';
            header('Location: index.php?action=admin-faces');
            exit;
        }

        include __DIR__ . '/../View/Backoffice/viewFace.php';
    }

    /** Allow only administrators to access Face ID backoffice routes. */
    private function requireAdmin() {
        if (!isAdmin()) {
            header('Location: index.php');
            exit;
        }
    }
}
