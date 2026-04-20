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
        $stmt = $this->pdo->prepare("DELETE FROM Post WHERE id_post = ?");
        $stmt->execute([$id]);
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