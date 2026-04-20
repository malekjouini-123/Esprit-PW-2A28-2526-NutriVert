<?php
class Post {
    private $id_post;
    private $titre;
    private $contenu;
    private $media_url;
    private $type_post;
    private $date_publication;
    private $auteur_id;

    // Constructor
    public function __construct($titre = null, $contenu = null, $media_url = null, $type_post = 'Article', $auteur_id = null) {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->media_url = $media_url;
        $this->type_post = $type_post;
        $this->auteur_id = $auteur_id;
    }

    // Getters
    public function getIdPost() {
        return $this->id_post;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getContenu() {
        return $this->contenu;
    }

    public function getMediaUrl() {
        return $this->media_url;
    }

    public function getTypePost() {
        return $this->type_post;
    }

    public function getDatePublication() {
        return $this->date_publication;
    }

    public function getAuteurId() {
        return $this->auteur_id;
    }

    // Setters
    public function setIdPost($id_post) {
        $this->id_post = $id_post;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function setContenu($contenu) {
        $this->contenu = $contenu;
    }

    public function setMediaUrl($media_url) {
        $this->media_url = $media_url;
    }

    public function setTypePost($type_post) {
        $this->type_post = $type_post;
    }

    public function setDatePublication($date_publication) {
        $this->date_publication = $date_publication;
    }

    public function setAuteurId($auteur_id) {
        $this->auteur_id = $auteur_id;
    }
}
?>