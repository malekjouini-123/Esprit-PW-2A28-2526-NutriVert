<?php
class ReactionReply {
    private $id_reaction_reply;
    private $type_reaction;
    private $user_id;
    private $reply_id;

    public function __construct($type_reaction = null, $user_id = null, $reply_id = null) {
        $this->type_reaction = $type_reaction;
        $this->user_id = $user_id;
        $this->reply_id = $reply_id;
    }

    public function getIdReactionReply() { return $this->id_reaction_reply; }
    public function getTypeReaction() { return $this->type_reaction; }
    public function getUserId() { return $this->user_id; }
    public function getReplyId() { return $this->reply_id; }

    public function setIdReactionReply($id) { $this->id_reaction_reply = $id; }
    public function setTypeReaction($type) { $this->type_reaction = $type; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setReplyId($reply_id) { $this->reply_id = $reply_id; }
}
?>
