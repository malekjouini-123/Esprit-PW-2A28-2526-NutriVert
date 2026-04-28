<?php

class User {
    private $id_user;
    private $nom_utilisateur;
    private $email;
    private $mot_de_passe;
    private $date_inscription;
    private $photo_profil;
    private $bio;
    private $post_count = 0;
    private $reply_count = 0;
    private $like_count = 0;
    private $dislike_count = 0;

    // Getters
    public function getIdUser() {
        return $this->id_user;
    }

    public function getNomUtilisateur() {
        return $this->nom_utilisateur;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getMotDePasse() {
        return $this->mot_de_passe;
    }

    public function getDateInscription() {
        return $this->date_inscription;
    }

    public function getPhotoProfil() {
        return $this->photo_profil;
    }

    public function getBio() {
        return $this->bio;
    }

    public function getPostCount() {
        return $this->post_count;
    }

    public function getReplyCount() {
        return $this->reply_count;
    }

    public function getLikeCount() {
        return $this->like_count;
    }

    public function getDislikeCount() {
        return $this->dislike_count;
    }

    // Setters
    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }

    public function setNomUtilisateur($nom_utilisateur) {
        $this->nom_utilisateur = $nom_utilisateur;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setMotDePasse($mot_de_passe) {
        $this->mot_de_passe = $mot_de_passe;
    }

    public function setDateInscription($date_inscription) {
        $this->date_inscription = $date_inscription;
    }

    public function setPhotoProfil($photo_profil) {
        $this->photo_profil = $photo_profil;
    }

    public function setBio($bio) {
        $this->bio = $bio;
    }

    public function setPostCount($post_count) {
        $this->post_count = $post_count;
    }

    public function setReplyCount($reply_count) {
        $this->reply_count = $reply_count;
    }

    public function setLikeCount($like_count) {
        $this->like_count = $like_count;
    }

    public function setDislikeCount($dislike_count) {
        $this->dislike_count = $dislike_count;
    }
}
?>
