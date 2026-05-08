<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/post.php';

class PostController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Get all available icons
    public function getAllIcons() {
        $stmt = $this->pdo->query("SELECT * FROM PostIcon ORDER BY category, id_icon");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get icon by ID
    public function getIconById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM PostIcon WHERE id_icon = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get icons for a specific post
    public function getPostIcons($postId) {
        $stmt = $this->pdo->prepare("
            SELECT pi.* FROM PostIcon pi
            JOIN Post_Has_Icon phi ON pi.id_icon = phi.id_icon
            WHERE phi.id_post = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper to populate Post from DB row
    private function mapRowToPost($row) {
        $post = new Post();
        $post->setIdPost($row['id_post']);
        $post->setTitre($row['titre']);
        $post->setContenu($row['contenu']);
        $post->setMediaUrl($row['media_url']);
        $post->setTypePost($row['type_post']);
        $post->setDatePublication($row['date_publication']);
        $post->setAuteurId($row['auteur_id']);
        
        // Fetch multiple icons
        $icons = $this->getPostIcons($row['id_post']);
        $iconIds = array_map(function($i) { return $i['id_icon']; }, $icons);
        $post->setIcons($iconIds);
        
        return $post;
    }

    // Get all posts
    public function getAllPosts() {
        $stmt = $this->pdo->query("SELECT * FROM Post ORDER BY date_publication DESC");
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->mapRowToPost($row);
        }
        return $posts;
    }

    // Get post by ID
    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE id_post = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            return $this->mapRowToPost($row);
        }
        return null;
    }

    // Create a new post
    public function createPost(Post $post) {
        // Filtrage des mots inappropriés
        $post->setTitre(filterProfanity($post->getTitre()));
        $post->setContenu(filterProfanity($post->getContenu()));

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO Post (titre, contenu, media_url, type_post, auteur_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $post->getTitre(),
                $post->getContenu(),
                $post->getMediaUrl(),
                $post->getTypePost(),
                $post->getAuteurId()
            ]);
            $postId = $this->pdo->lastInsertId();
            $post->setIdPost($postId);

            // Insert icons
            if (!empty($post->getIcons())) {
                $stmtIcon = $this->pdo->prepare("INSERT INTO Post_Has_Icon (id_post, id_icon) VALUES (?, ?)");
                foreach ($post->getIcons() as $iconId) {
                    $stmtIcon->execute([$postId, $iconId]);
                }
            }

            $this->pdo->commit();
            return $post;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Update a post
    public function updatePost(Post $post) {
        // Filtrage des mots inappropriés
        $post->setTitre(filterProfanity($post->getTitre()));
        $post->setContenu(filterProfanity($post->getContenu()));

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE Post SET titre = ?, contenu = ?, media_url = ?, type_post = ? WHERE id_post = ?");
            $stmt->execute([
                $post->getTitre(),
                $post->getContenu(),
                $post->getMediaUrl(),
                $post->getTypePost(),
                $post->getIdPost()
            ]);

            // Update icons: delete old ones and insert new ones
            $stmtDel = $this->pdo->prepare("DELETE FROM Post_Has_Icon WHERE id_post = ?");
            $stmtDel->execute([$post->getIdPost()]);

            if (!empty($post->getIcons())) {
                $stmtIcon = $this->pdo->prepare("INSERT INTO Post_Has_Icon (id_post, id_icon) VALUES (?, ?)");
                foreach ($post->getIcons() as $iconId) {
                    $stmtIcon->execute([$post->getIdPost(), $iconId]);
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Delete a post
    public function deletePost($id) {
        $this->pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
        
        try {
            // Delete junction data first (though CASCADE should handle it, let's be safe or just let CASCADE work)
            // If fk_phi_post has ON DELETE CASCADE, it's fine.

            // 1. Delete related reactions on the post itself
            $stmt = $this->pdo->prepare("DELETE FROM Reaction WHERE post_id = ?");
            $stmt->execute([$id]);

            // 2. Get all replies for this post to delete their reactions
            $stmt = $this->pdo->prepare("SELECT id_reply FROM Reply WHERE post_id = ?");
            $stmt->execute([$id]);
            $replies = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($replies)) {
                // 3. Delete all reactions for all these replies
                $in = str_repeat('?,', count($replies) - 1) . '?';
                $stmt = $this->pdo->prepare("DELETE FROM ReactionReply WHERE reply_id IN ($in)");
                $stmt->execute($replies);

                // 4. Delete all replies for this post
                $stmt = $this->pdo->prepare("DELETE FROM Reply WHERE post_id = ?");
                $stmt->execute([$id]);
            }

            // 5. Finally delete the post
            $stmt = $this->pdo->prepare("DELETE FROM Post WHERE id_post = ?");
            $stmt->execute([$id]);
            
        } finally {
            $this->pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
        }
    }

    // Get posts by user
    public function getPostsByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE auteur_id = ? ORDER BY date_publication DESC");
        $stmt->execute([$user_id]);
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->mapRowToPost($row);
        }
        return $posts;
    }

    // Get posts by type
    public function getPostsByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE type_post = ? ORDER BY date_publication DESC");
        $stmt->execute([$type]);
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->mapRowToPost($row);
        }
        return $posts;
    }
}
?>