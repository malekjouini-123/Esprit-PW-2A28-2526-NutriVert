<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reply.php';

class ReplyController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Get all replies
    public function getAllReplies() {
        $stmt = $this->pdo->query("SELECT * FROM Reply ORDER BY date_reply DESC");
        $replies = [];
        while ($row = $stmt->fetch()) {
            $reply = new Reply();
            $reply->setIdReply($row['id_reply']);
            $reply->setCommentaire($row['commentaire']);
            $reply->setImageUrl($row['image_url']);
            $reply->setDateReply($row['date_reply']);
            $reply->setPostId($row['post_id']);
            $reply->setAuteurId($row['auteur_id']);
            $reply->setParentReplyId($row['parent_reply_id']);
            $replies[] = $reply;
        }
        return $replies;
    }

    // Get reply by ID
    public function getReplyById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reply WHERE id_reply = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $reply = new Reply();
            $reply->setIdReply($row['id_reply']);
            $reply->setCommentaire($row['commentaire']);
            $reply->setImageUrl($row['image_url']);
            $reply->setDateReply($row['date_reply']);
            $reply->setPostId($row['post_id']);
            $reply->setAuteurId($row['auteur_id']);
            $reply->setParentReplyId($row['parent_reply_id']);
            return $reply;
        }
        return null;
    }

    // Create a new reply
    public function createReply(Reply $reply) {
        // Filtrage des mots inappropriés
        $reply->setCommentaire(filterProfanity($reply->getCommentaire()));

        $stmt = $this->pdo->prepare("INSERT INTO Reply (commentaire, image_url, post_id, auteur_id, parent_reply_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $reply->getCommentaire(),
            $reply->getImageUrl(),
            $reply->getPostId(),
            $reply->getAuteurId(),
            $reply->getParentReplyId()
        ]);
        $reply->setIdReply($this->pdo->lastInsertId());
        return $reply;
    }

    // Update a reply
    public function updateReply(Reply $reply) {
        // Filtrage des mots inappropriés
        $reply->setCommentaire(filterProfanity($reply->getCommentaire()));

        $stmt = $this->pdo->prepare("UPDATE Reply SET commentaire = ?, image_url = ? WHERE id_reply = ?");
        $stmt->execute([
            $reply->getCommentaire(),
            $reply->getImageUrl(),
            $reply->getIdReply()
        ]);
    }

    // Delete a reply
    public function deleteReply($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Reply WHERE id_reply = ?");
        $stmt->execute([$id]);
    }

    // Get replies by post
    public function getRepliesByPost($post_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reply WHERE post_id = ? ORDER BY date_reply ASC");
        $stmt->execute([$post_id]);
        $replies = [];
        while ($row = $stmt->fetch()) {
            $reply = new Reply();
            $reply->setIdReply($row['id_reply']);
            $reply->setCommentaire($row['commentaire']);
            $reply->setImageUrl($row['image_url']);
            $reply->setDateReply($row['date_reply']);
            $reply->setPostId($row['post_id']);
            $reply->setAuteurId($row['auteur_id']);
            $reply->setParentReplyId($row['parent_reply_id']);
            $replies[] = $reply;
        }
        return $replies;
    }

    // Get replies by user
    public function getRepliesByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reply WHERE auteur_id = ? ORDER BY date_reply DESC");
        $stmt->execute([$user_id]);
        $replies = [];
        while ($row = $stmt->fetch()) {
            $reply = new Reply();
            $reply->setIdReply($row['id_reply']);
            $reply->setCommentaire($row['commentaire']);
            $reply->setImageUrl($row['image_url']);
            $reply->setDateReply($row['date_reply']);
            $reply->setPostId($row['post_id']);
            $reply->setAuteurId($row['auteur_id']);
            $reply->setParentReplyId($row['parent_reply_id']);
            $replies[] = $reply;
        }
        return $replies;
    }

    // Get child replies (replies to a reply)
    public function getChildReplies($parent_reply_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reply WHERE parent_reply_id = ? ORDER BY date_reply ASC");
        $stmt->execute([$parent_reply_id]);
        $replies = [];
        while ($row = $stmt->fetch()) {
            $reply = new Reply();
            $reply->setIdReply($row['id_reply']);
            $reply->setCommentaire($row['commentaire']);
            $reply->setImageUrl($row['image_url']);
            $reply->setDateReply($row['date_reply']);
            $reply->setPostId($row['post_id']);
            $reply->setAuteurId($row['auteur_id']);
            $reply->setParentReplyId($row['parent_reply_id']);
            $replies[] = $reply;
        }
        return $replies;
    }
}
?>