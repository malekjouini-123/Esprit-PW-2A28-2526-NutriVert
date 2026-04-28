<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reaction.php';

class ReactionController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Add or update a reaction
    public function addReaction(Reaction $reaction) {
        // Check if reaction already exists
        $existing = $this->getReaction($reaction->getUserId(), $reaction->getPostId());
        if ($existing) {
            // Update existing reaction
            $stmt = $this->pdo->prepare("UPDATE Reaction SET type_reaction = ? WHERE user_id = ? AND post_id = ?");
            $stmt->execute([
                $reaction->getTypeReaction(),
                $reaction->getUserId(),
                $reaction->getPostId()
            ]);
            $existing->setTypeReaction($reaction->getTypeReaction());
            return $existing;
        } else {
            // Insert new reaction
            $stmt = $this->pdo->prepare("INSERT INTO Reaction (type_reaction, user_id, post_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $reaction->getTypeReaction(),
                $reaction->getUserId(),
                $reaction->getPostId()
            ]);
            $reaction->setIdReaction($this->pdo->lastInsertId());
            return $reaction;
        }
    }

    // Remove a reaction
    public function removeReaction($user_id, $post_id) {
        $stmt = $this->pdo->prepare("DELETE FROM Reaction WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
    }

    // Get reaction by user and post
    public function getReaction($user_id, $post_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reaction WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $row = $stmt->fetch();
        if ($row) {
            $reaction = new Reaction();
            $reaction->setIdReaction($row['id_reaction']);
            $reaction->setTypeReaction($row['type_reaction']);
            $reaction->setUserId($row['user_id']);
            $reaction->setPostId($row['post_id']);
            return $reaction;
        }
        return null;
    }

    // Get all reactions for a post
    public function getReactionsByPost($post_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Reaction WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $reactions = [];
        while ($row = $stmt->fetch()) {
            $reaction = new Reaction();
            $reaction->setIdReaction($row['id_reaction']);
            $reaction->setTypeReaction($row['type_reaction']);
            $reaction->setUserId($row['user_id']);
            $reaction->setPostId($row['post_id']);
            $reactions[] = $reaction;
        }
        return $reactions;
    }

    // Get reaction counts for a post (likes and dislikes)
    public function getReactionCounts($post_id) {
        $stmt = $this->pdo->prepare("SELECT type_reaction, COUNT(*) as count FROM Reaction WHERE post_id = ? GROUP BY type_reaction");
        $stmt->execute([$post_id]);
        $counts = ['Like' => 0, 'Dislike' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['type_reaction']] = $row['count'];
        }
        return $counts;
    }

    // Toggle reaction: if same type, remove; if different, update; if none, add
    public function toggleReaction($user_id, $post_id, $type) {
        $existing = $this->getReaction($user_id, $post_id);
        if ($existing) {
            if ($existing->getTypeReaction() == $type) {
                // Same type, remove
                $this->removeReaction($user_id, $post_id);
                return null;
            } else {
                // Different type, update
                $existing->setTypeReaction($type);
                return $this->addReaction($existing);
            }
        } else {
            // No reaction, add
            $reaction = new Reaction($type, $user_id, $post_id);
            return $this->addReaction($reaction);
        }
    }
}
?>