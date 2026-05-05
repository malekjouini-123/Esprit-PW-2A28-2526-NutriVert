<?php
function getDB() {
    $host = 'localhost';
    $db   = 'nutrivert_db';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

/**
 * Filtre les mots inappropriés en les remplaçant par des étoiles.
 * Supporte le français et l'anglais.
 */
function filterProfanity($text) {
    if (empty($text)) return $text;

    $badWords = [
        'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'crap', 'damn', 'piss', 'dick', 'pussy', 'cock', 'faggot', 'nigger', 'slut', 'bad word',
        'merde', 'connard', 'connasse', 'salope', 'enculé', 'pute', 'bordel', 'con', 'chier', 'salaud', 'abruti', 'nique', 'teub', 'bite', 'cul', 'mauvais mot', 'mauvaise mot'
    ];
    
    foreach ($badWords as $word) {
        $replacement = str_repeat('*', mb_strlen($word));
        // Utilise preg_replace avec l'option 'i' pour l'insensibilité à la casse
        // et '\b' pour ne remplacer que des mots entiers.
        $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    return $text;
}
?>
