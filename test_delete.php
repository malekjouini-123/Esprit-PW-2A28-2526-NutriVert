<?php
require_once 'config.php';
require_once 'controlers/postcontroller.php';

try {
    $pc = new PostController();
    // Assuming there is a post to delete, we will just simulate what happens
    // Actually, let's just create a mock post and delete it.
    
    $post = new Post();
    $post->setTitre("Test");
    $post->setContenu("Test content");
    $post->setTypePost("Article");
    $post->setAuteurId(1);
    
    $pc->createPost($post);
    $id = $post->getIdPost();
    echo "Created post $id\n";
    
    $pc->deletePost($id);
    echo "Deleted post $id successfully\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
