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

    public function create($nom, $prenom, $email, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (:nom, :prenom, :email, :mdp)");
        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mdp' => $hashed
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
    public function getAll() {
    $stmt = $this->pdo->query("SELECT * FROM utilisateurs ORDER BY id_utilisateur DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function delete($id) {
    $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = :id");
    return $stmt->execute([':id' => $id]);
}
}