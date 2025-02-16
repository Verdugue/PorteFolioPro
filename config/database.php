<?php
// Définition des constantes de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'projetb2');
define('DB_USER', 'projetb2');
define('DB_PASS', 'password');

try {
    // Création de la connexion PDO avec le charset UTF8
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch(PDOException $e) {
    // En cas d'erreur, afficher un message d'erreur et arrêter le script
    die("Erreur de connexion : " . $e->getMessage());
}
?>
