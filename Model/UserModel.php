<?php
require_once __DIR__ . '/../Database.php';

class UserModel {
    private $pdo;
    public function __construct() {
        $this->pdo = getPDO();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nom, $prenom, $email, $password, $role = 'client') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (:nom, :prenom, :email, :mdp, :role)");
        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mdp' => $hashed,
            ':role' => $role
        ]);
    }

    public function updateProfile($id, $poids, $taille, $objectif, $regime) {
        $imc = ($poids && $taille && $taille > 0) ? round($poids / (($taille/100)*($taille/100)), 2) : null;
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET poids = :poids, taille = :taille, imc = :imc, objectif_nutritionnel = :obj, regime_alimentaire = :reg WHERE id_utilisateur = :id");
        return $stmt->execute([
            ':poids' => $poids,
            ':taille' => $taille,
            ':imc' => $imc,
            ':obj' => $objectif,
            ':reg' => $regime,
            ':id' => $id
        ]);
    }

    public function logAuth($userId, $type = 'email') {
        $stmt = $this->pdo->prepare("INSERT INTO authentifications (id_utilisateur, type_connexion, derniere_connexion) VALUES (:uid, :type, NOW())");
        $stmt->execute([':uid' => $userId, ':type' => $type]);
    }

    /** Save or replace the JSON face encoding for one user. */
    public function saveFaceEncoding($userId, $encoding) {
        $stmt = $this->pdo->prepare("
            INSERT INTO visages_utilisateurs (id_utilisateur, face_encoding, created_at, updated_at)
            VALUES (:uid, :encoding, NOW(), NOW())
            ON DUPLICATE KEY UPDATE face_encoding = VALUES(face_encoding), updated_at = NOW()
        ");
        return $stmt->execute([
            ':uid' => (int)$userId,
            ':encoding' => $encoding
        ]);
    }

    /** Return the stored JSON face encoding for one user. */
    public function getFaceEncoding($userId) {
        $stmt = $this->pdo->prepare("SELECT face_encoding FROM visages_utilisateurs WHERE id_utilisateur = :uid");
        $stmt->execute([':uid' => (int)$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['face_encoding'] : false;
    }

    /** Delete the stored Face ID encoding for one user. */
    public function deleteFaceEncoding($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM visages_utilisateurs WHERE id_utilisateur = :uid");
        return $stmt->execute([':uid' => (int)$userId]);
    }

    /** Return all stored Face ID encodings for login matching. */
    public function getAllFaceEncodings() {
        $stmt = $this->pdo->query("
            SELECT id_utilisateur AS user_id, face_encoding
            FROM visages_utilisateurs
            WHERE face_encoding IS NOT NULL AND face_encoding <> ''
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Return all users with Face ID status metadata for backoffice pages. */
    public function getAllWithFaceStatus() {
        $stmt = $this->pdo->query("
            SELECT u.*, v.id_visage, v.created_at AS face_created_at, v.updated_at AS face_updated_at,
                   CASE WHEN v.id_visage IS NULL THEN 0 ELSE 1 END AS has_face
            FROM utilisateurs u
            LEFT JOIN visages_utilisateurs v ON v.id_utilisateur = u.id_utilisateur
            ORDER BY u.id_utilisateur DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Return one user's Face ID metadata for the admin detail page. */
    public function getUserFaceDetails($userId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.role,
                   v.face_encoding, v.created_at, v.updated_at
            FROM utilisateurs u
            LEFT JOIN visages_utilisateurs v ON v.id_utilisateur = u.id_utilisateur
            WHERE u.id_utilisateur = :uid
        ");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
    $stmt = $this->pdo->query("SELECT * FROM utilisateurs ORDER BY id_utilisateur DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function updateUser($id, $nom, $prenom, $email, $role, $password = null) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role, mot_de_passe = :mdp WHERE id_utilisateur = :id");
            return $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':role' => $role,
                ':mdp' => $hashed,
                ':id' => $id
            ]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role WHERE id_utilisateur = :id");
            return $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':role' => $role,
                ':id' => $id
            ]);
        }
    }

    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email");
        $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':email' => $email
        ]);
        
        return $token;
    }

    public function findByResetToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE reset_token = :token AND reset_token_expiry > NOW()");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resetPassword($token, $newPassword) {
        $user = $this->findByResetToken($token);
        if (!$user) {
            return false;
        }
        
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :mdp, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = :token");
        return $stmt->execute([
            ':mdp' => $hashed,
            ':token' => $token
        ]);
    }
}
