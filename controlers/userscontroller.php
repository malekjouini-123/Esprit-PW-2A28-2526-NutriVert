<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/user.php';

class UsersController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Get all users with statistics
    public function getAllUsers() {
        try {
            $query = "
                SELECT u.*, 
                    (SELECT COUNT(*) FROM Post WHERE auteur_id = u.id_user) as post_count,
                    (SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) as reply_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') as like_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') as dislike_count
                FROM Utilisateur u 
                ORDER BY u.id_user DESC
            ";
            $stmt = $this->pdo->query($query);
            $users = [];
            while ($row = $stmt->fetch()) {
                $user = new User();
                $user->setIdUser($row['id_user']);
                $user->setNomUtilisateur($row['nom_utilisateur']);
                $user->setEmail($row['email']);
                $user->setDateInscription($row['date_inscription'] ?? null);
                $user->setPhotoProfil($row['photo_profil'] ?? null);
                $user->setBio($row['bio'] ?? '');
                $user->setPostCount($row['post_count']);
                $user->setReplyCount($row['reply_count']);
                $user->setLikeCount($row['like_count']);
                $user->setDislikeCount($row['dislike_count']);
                $users[] = $user;
            }
            return $users;
        } catch (Exception $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    // Get user by ID
    public function getUserById($id) {
        try {
            $query = "
                SELECT u.*, 
                    (SELECT COUNT(*) FROM Post WHERE auteur_id = u.id_user) as post_count,
                    (SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) as reply_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') as like_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') as dislike_count
                FROM Utilisateur u 
                WHERE u.id_user = ?
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            
            if (!$row) {
                return null;
            }
            
            $user = new User();
            $user->setIdUser($row['id_user']);
            $user->setNomUtilisateur($row['nom_utilisateur']);
            $user->setEmail($row['email']);
            $user->setDateInscription($row['date_inscription'] ?? null);
            $user->setPhotoProfil($row['photo_profil'] ?? null);
            $user->setBio($row['bio'] ?? '');
            $user->setPostCount($row['post_count']);
            $user->setReplyCount($row['reply_count']);
            $user->setLikeCount($row['like_count']);
            $user->setDislikeCount($row['dislike_count']);
            return $user;
        } catch (Exception $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }

    // Delete user
    public function deleteUser($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM Utilisateur WHERE id_user = ?");
            $stmt->execute([$id]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }

    // Search users
    public function searchUsers($search) {
        $val = strtolower(trim($search));
        if ($val === '') {
            return $this->getAllUsers();
        }
        
        try {
            $searchTerm = "%$search%";
            
            $whereClause = "(u.nom_utilisateur LIKE ? OR u.email LIKE ?)";
            $params = [$searchTerm, $searchTerm];

            if (in_array($val, ['like', 'likes', 'j\'aime'])) {
                $whereClause = "(SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') > 0";
                $params = [];
            } elseif (in_array($val, ['dislike', 'dislikes', 'je n\'aime pas'])) {
                $whereClause = "(SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') > 0";
                $params = [];
            } elseif (in_array($val, ['commentaire', 'commentaires', 'comm', 'comms'])) {
                $whereClause = "(SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) > 0";
                $params = [];
            }

            $query = "
                SELECT u.*, 
                    (SELECT COUNT(*) FROM Post WHERE auteur_id = u.id_user) as post_count,
                    (SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) as reply_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') as like_count,
                    (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') as dislike_count
                FROM Utilisateur u 
                WHERE $whereClause
                ORDER BY u.id_user DESC
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $users = [];
            while ($row = $stmt->fetch()) {
                $user = new User();
                $user->setIdUser($row['id_user']);
                $user->setNomUtilisateur($row['nom_utilisateur']);
                $user->setEmail($row['email']);
                $user->setDateInscription($row['date_inscription'] ?? null);
                $user->setPhotoProfil($row['photo_profil'] ?? null);
                $user->setBio($row['bio'] ?? '');
                $user->setPostCount($row['post_count']);
                $user->setReplyCount($row['reply_count']);
                $user->setLikeCount($row['like_count']);
                $user->setDislikeCount($row['dislike_count']);
                $users[] = $user;
            }
            return $users;
        } catch (Exception $e) {
            throw new Exception("Error searching users: " . $e->getMessage());
        }
    }

    // Get total user count
    public function getUserCount() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Utilisateur");
            $row = $stmt->fetch();
            return $row['count'];
        } catch (Exception $e) {
            throw new Exception("Error counting users: " . $e->getMessage());
        }
    }
}
?>
