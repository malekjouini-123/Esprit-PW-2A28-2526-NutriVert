<?php
require_once __DIR__ . '/../Database.php';

class ChatModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    /** Save one user message and the AI response. */
    public function saveChat($userId, $message, $response, $model) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ai_chats (id_utilisateur, user_message, ai_response, model, created_at)
            VALUES (:uid, :message, :response, :model, NOW())
        ");

        return $stmt->execute([
            ':uid' => (int)$userId,
            ':message' => $message,
            ':response' => $response,
            ':model' => $model
        ]);
    }

    /** Return the latest chats for one user. */
    public function getUserChats($userId, $limit = 30) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM ai_chats
            WHERE id_utilisateur = :uid
            ORDER BY id_chat DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute([':uid' => (int)$userId]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Return all chats with user details for backoffice review. */
    public function getAllChats() {
        $stmt = $this->pdo->query("
            SELECT c.*, u.nom, u.prenom, u.email
            FROM ai_chats c
            INNER JOIN utilisateurs u ON u.id_utilisateur = c.id_utilisateur
            ORDER BY c.id_chat DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
