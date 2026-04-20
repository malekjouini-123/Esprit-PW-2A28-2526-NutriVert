<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reactionreply.php';

class ReactionReplyController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function addReaction(ReactionReply $reaction) {
        $existing = $this->getReaction($reaction->getUserId(), $reaction->getReplyId());
        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE ReactionReply SET type_reaction = ? WHERE user_id = ? AND reply_id = ?");
            $stmt->execute([
                $reaction->getTypeReaction(),
                $reaction->getUserId(),
                $reaction->getReplyId()
            ]);
            $existing->setTypeReaction($reaction->getTypeReaction());
            return $existing;
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO ReactionReply (type_reaction, user_id, reply_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $reaction->getTypeReaction(),
                $reaction->getUserId(),
                $reaction->getReplyId()
            ]);
            $reaction->setIdReactionReply($this->pdo->lastInsertId());
            return $reaction;
        }
    }

    public function removeReaction($user_id, $reply_id) {
        $stmt = $this->pdo->prepare("DELETE FROM ReactionReply WHERE user_id = ? AND reply_id = ?");
        $stmt->execute([$user_id, $reply_id]);
    }

    public function getReaction($user_id, $reply_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ReactionReply WHERE user_id = ? AND reply_id = ?");
        $stmt->execute([$user_id, $reply_id]);
        $row = $stmt->fetch();
        if ($row) {
            $reaction = new ReactionReply();
            $reaction->setIdReactionReply($row['id_reaction_reply']);
            $reaction->setTypeReaction($row['type_reaction']);
            $reaction->setUserId($row['user_id']);
            $reaction->setReplyId($row['reply_id']);
            return $reaction;
        }
        return null;
    }

    public function getReactionCounts($reply_id) {
        $stmt = $this->pdo->prepare("SELECT type_reaction, COUNT(*) as count FROM ReactionReply WHERE reply_id = ? GROUP BY type_reaction");
        $stmt->execute([$reply_id]);
        $counts = ['Like' => 0, 'Dislike' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['type_reaction']] = $row['count'];
        }
        return $counts;
    }

    public function toggleReaction($user_id, $reply_id, $type) {
        $existing = $this->getReaction($user_id, $reply_id);
        if ($existing) {
            if ($existing->getTypeReaction() == $type) {
                $this->removeReaction($user_id, $reply_id);
                return null;
            } else {
                $existing->setTypeReaction($type);
                return $this->addReaction($existing);
            }
        } else {
            $reaction = new ReactionReply($type, $user_id, $reply_id);
            return $this->addReaction($reaction);
        }
    }
}
?>
