<?php
 
 // Activer l'affichage des erreurs pour le débogage
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

// Configuration de la base de données
// Copiez ce fichier en database.php et modifiez les valeurs ci-dessous
define('DB_HOST', 'localhost');        // Hôte de la base de données
define('DB_NAME', 'bibliotheque_crud'); // Nom de la base de données
define('DB_USER', 'root');             // Nom d'utilisateur de la base de données
define('DB_PASS', '');                 // Mot de passe de la base de données
define('DB_CHARSET', 'utf8mb4');       // Jeu de caractères de la base de données

function getDbConnection(){
    // Data source name
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // Options de connexion
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Activer les exceptions d'erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mode de récupération par défaut
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactiver l'émulation des requêtes préparées
    ];
    
    try {
        // Création de l'objet PDO => connexion à la base de données
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En cas d'erreur, afficher le message et arrêter le script
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}