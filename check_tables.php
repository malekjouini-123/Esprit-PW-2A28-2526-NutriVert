<?php
require_once 'config.php';
$pdo = getDB();
$stmt=$pdo->query('SHOW COLUMNS FROM reaction');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
