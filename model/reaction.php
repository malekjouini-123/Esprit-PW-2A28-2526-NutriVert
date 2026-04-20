<?php
class Reaction {
    private $id_reaction;
    private $type_reaction;
    private $user_id;
    private $post_id;

    // Constructor
    public function __construct($type_reaction = null, $user_id = null, $post_id = null) {
        $this->type_reaction = $type_reaction;
        $this->user_id = $user_id;
        $this->post_id = $post_id;
    }

    // Getters
    public function getIdReaction() {
        return $this->id_reaction;
    }

    public function getTypeReaction() {
        return $this->type_reaction;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getPostId() {
        return $this->post_id;
    }

    // Setters
    public function setIdReaction($id_reaction) {
        $this->id_reaction = $id_reaction;
    }

    public function setTypeReaction($type_reaction) {
        $this->type_reaction = $type_reaction;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function setPostId($post_id) {
        $this->post_id = $post_id;
    }
}
?>