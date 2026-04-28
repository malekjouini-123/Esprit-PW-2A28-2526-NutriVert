<?php
require_once 'config.php';
$pdo = getDB();
$stmt = $pdo->query('DESCRIBE Utilisateur');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
