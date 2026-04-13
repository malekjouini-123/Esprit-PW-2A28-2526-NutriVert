<?php
require_once __DIR__ . '/config/database.php';

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $config = require __DIR__ . '/config/database.php';
            try {
                self::$connection = new PDO(
                    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
                    $config['db_user'],
                    $config['db_pass']
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$connection;
    }
}

function getPDO() {
    return Database::getConnection();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}