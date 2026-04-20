<?php
class Reply {
    private $id_reply;
    private $commentaire;
    private $image_url;
    private $date_reply;
    private $post_id;
    private $auteur_id;
    private $parent_reply_id;

    // Constructor
    public function __construct($commentaire = null, $image_url = null, $post_id = null, $auteur_id = null, $parent_reply_id = null) {
        $this->commentaire = $commentaire;
        $this->image_url = $image_url;
        $this->post_id = $post_id;
        $this->auteur_id = $auteur_id;
        $this->parent_reply_id = $parent_reply_id;
    }

    // Getters
    public function getIdReply() {
        return $this->id_reply;
    }

    public function getCommentaire() {
        return $this->commentaire;
    }

    public function getImageUrl() {
        return $this->image_url;
    }

    public function getDateReply() {
        return $this->date_reply;
    }

    public function getPostId() {
        return $this->post_id;
    }

    public function getAuteurId() {
        return $this->auteur_id;
    }

    public function getParentReplyId() {
        return $this->parent_reply_id;
    }

    // Setters
    public function setIdReply($id_reply) {
        $this->id_reply = $id_reply;
    }

    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
    }

    public function setImageUrl($image_url) {
        $this->image_url = $image_url;
    }

    public function setDateReply($date_reply) {
        $this->date_reply = $date_reply;
    }

    public function setPostId($post_id) {
        $this->post_id = $post_id;
    }

    public function setAuteurId($auteur_id) {
        $this->auteur_id = $auteur_id;
    }

    public function setParentReplyId($parent_reply_id) {
        $this->parent_reply_id = $parent_reply_id;
    }
}
?>