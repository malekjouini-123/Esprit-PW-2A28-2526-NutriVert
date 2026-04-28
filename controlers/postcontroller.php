<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/post.php';

class PostController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Get all posts
    public function getAllPosts() {
        $stmt = $this->pdo->query("SELECT * FROM Post ORDER BY date_publication DESC");
        $posts = [];
        while ($row = $stmt->fetch()) {
            $post = new Post();
            $post->setIdPost($row['id_post']);
            $post->setTitre($row['titre']);
            $post->setContenu($row['contenu']);
            $post->setMediaUrl($row['media_url']);
            $post->setTypePost($row['type_post']);
            $post->setDatePublication($row['date_publication']);
            $post->setAuteurId($row['auteur_id']);
            $posts[] = $post;
        }
        return $posts;
    }

    // Get post by ID
    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE id_post = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $post = new Post();
            $post->setIdPost($row['id_post']);
            $post->setTitre($row['titre']);
            $post->setContenu($row['contenu']);
            $post->setMediaUrl($row['media_url']);
            $post->setTypePost($row['type_post']);
            $post->setDatePublication($row['date_publication']);
            $post->setAuteurId($row['auteur_id']);
            return $post;
        }
        return null;
    }

    // Create a new post
    public function createPost(Post $post) {
        $stmt = $this->pdo->prepare("INSERT INTO Post (titre, contenu, media_url, type_post, auteur_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $post->getTitre(),
            $post->getContenu(),
            $post->getMediaUrl(),
            $post->getTypePost(),
            $post->getAuteurId()
        ]);
        $post->setIdPost($this->pdo->lastInsertId());
        return $post;
    }

    // Update a post
    public function updatePost(Post $post) {
        $stmt = $this->pdo->prepare("UPDATE Post SET titre = ?, contenu = ?, media_url = ?, type_post = ? WHERE id_post = ?");
        $stmt->execute([
            $post->getTitre(),
            $post->getContenu(),
            $post->getMediaUrl(),
            $post->getTypePost(),
            $post->getIdPost()
        ]);
    }

    // Delete a post
    public function deletePost($id) {
        $this->pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
        
        try {
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
            $post = new Post();
            $post->setIdPost($row['id_post']);
            $post->setTitre($row['titre']);
            $post->setContenu($row['contenu']);
            $post->setMediaUrl($row['media_url']);
            $post->setTypePost($row['type_post']);
            $post->setDatePublication($row['date_publication']);
            $post->setAuteurId($row['auteur_id']);
            $posts[] = $post;
        }
        return $posts;
    }

    // Get posts by type
    public function getPostsByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE type_post = ? ORDER BY date_publication DESC");
        $stmt->execute([$type]);
        $posts = [];
        while ($row = $stmt->fetch()) {
            $post = new Post();
            $post->setIdPost($row['id_post']);
            $post->setTitre($row['titre']);
            $post->setContenu($row['contenu']);
            $post->setMediaUrl($row['media_url']);
            $post->setTypePost($row['type_post']);
            $post->setDatePublication($row['date_publication']);
            $post->setAuteurId($row['auteur_id']);
            $posts[] = $post;
        }
        return $posts;
    }
}
?>