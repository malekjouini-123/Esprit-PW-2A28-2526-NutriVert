<?php
declare(strict_types=1);

if (class_exists('ChatModel')) return;

require_once __DIR__ . '/../config/database.php';

class ChatModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function saveChat(int $userId, string $message, string $response, string $model): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO ai_chats (id_utilisateur, user_message, ai_response, model, created_at)
            VALUES (:uid, :message, :response, :model, NOW())
        ");

        return $stmt->execute([
            ':uid'      => $userId,
            ':message'  => $message,
            ':response' => $response,
            ':model'    => $model,
        ]);
    }

    public function getUserChats(int $userId, int $limit = 30): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM ai_chats
            WHERE id_utilisateur = :uid
            ORDER BY id_chat DESC
            LIMIT " . $limit
        );
        $stmt->execute([':uid' => $userId]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getAllChats(): array {
        $stmt = $this->pdo->query("
            SELECT c.*, u.nom, u.prenom, u.email
            FROM ai_chats c
            INNER JOIN utilisateurs u ON u.id_utilisateur = c.id_utilisateur
            ORDER BY c.id_chat DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
