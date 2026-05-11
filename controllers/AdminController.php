<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (class_exists('AdminController')) return;

class AdminController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function handle(string $action): void
    {
        match ($action) {
            'faces'       => $this->listUsersWithFaces(),
            'deleteFace'  => $this->deleteUserFace((int)($_GET['user_id'] ?? 0)),
            'viewFace'    => $this->viewUserFace((int)($_GET['user_id'] ?? 0)),
            default       => $this->listUsersWithFaces(),
        };
    }

    public function listUsersWithFaces(): void
    {
        $this->requireAdmin();

        $stmt  = $this->pdo->query("
            SELECT u.id_utilisateur AS id, u.nom, u.email, u.role,
                   v.id_visage,
                   v.created_at AS face_created_at,
                   v.updated_at AS face_updated_at,
                   CASE WHEN v.id_visage IS NULL THEN 0 ELSE 1 END AS has_face
            FROM utilisateurs u
            LEFT JOIN visages_utilisateurs v ON v.id_utilisateur = u.id_utilisateur
            ORDER BY u.id_utilisateur DESC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/../views/admin/faces.php';
        exit;
    }

    public function deleteUserFace(int $userId): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=faces');
            exit;
        }

        $stmt = $this->pdo->prepare('DELETE FROM visages_utilisateurs WHERE id_utilisateur = :uid');
        $stmt->execute([':uid' => $userId]);
        $_SESSION['flash_message'] = 'Face ID supprimé.';
        header('Location: index.php?controller=admin&action=faces');
        exit;
    }

    public function viewUserFace(int $userId): void
    {
        $this->requireAdmin();

        $stmt = $this->pdo->prepare("
            SELECT u.id_utilisateur AS id, u.nom, u.email, u.role,
                   v.face_encoding, v.created_at, v.updated_at
            FROM utilisateurs u
            LEFT JOIN visages_utilisateurs v ON v.id_utilisateur = u.id_utilisateur
            WHERE u.id_utilisateur = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $face = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$face) {
            $_SESSION['flash_message'] = 'Utilisateur introuvable.';
            header('Location: index.php?controller=admin&action=faces');
            exit;
        }

        include __DIR__ . '/../views/admin/view-face.php';
        exit;
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
    }
}
