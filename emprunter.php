<?php
session_start();

// Vérifier si l'abonné est connecté
if (!isset($_SESSION['abonne_id'])) {
    $_SESSION['error'] = 'Vous devez être connecté pour emprunter un livre.';
    header('Location: login.php');
    exit;
}

// Vérifier si un ID de livre est fourni
if (!isset($_POST['livre_id']) || empty($_POST['livre_id'])) {
    $_SESSION['error'] = 'Livre non spécifié.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
$livre_id = (int)$_POST['livre_id'];
$abonne_id = $_SESSION['abonne_id'];

try {
    // Vérifier si le livre existe et est disponible
    $sql_livre = "SELECT l.*, 
                         COUNT(e.id_emprunt) as emprunts_en_cours
                  FROM livre l
                  LEFT JOIN emprunt e ON l.id_livre = e.id_livre AND e.date_rendu IS NULL
                  WHERE l.id_livre = :livre_id
                  GROUP BY l.id_livre";
    
    $stmt_livre = $pdo->prepare($sql_livre);
    $stmt_livre->bindParam(':livre_id', $livre_id, PDO::PARAM_INT);
    $stmt_livre->execute();
    $livre = $stmt_livre->fetch(PDO::FETCH_ASSOC);
    
    if (!$livre) {
        $_SESSION['error'] = 'Livre introuvable.';
        header('Location: index.php');
        exit;
    }
    
    // Vérifier la disponibilité (un seul exemplaire par livre)
    if ($livre['emprunts_en_cours'] > 0) {
        $_SESSION['error'] = 'Ce livre n\'est plus disponible.';
        header('Location: index.php');
        exit;
    }
    
    // Vérifier si l'abonné n'a pas déjà emprunté ce livre
    $sql_check = "SELECT COUNT(*) FROM emprunt 
                  WHERE id_abonne = :abonne_id AND id_livre = :livre_id AND date_rendu IS NULL";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':abonne_id', $abonne_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':livre_id', $livre_id, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error'] = 'Vous avez déjà emprunté ce livre.';
        header('Location: index.php');
        exit;
    }
    
    // Effectuer l'emprunt
    $sql_emprunt = "INSERT INTO emprunt (id_abonne, id_livre, date_sortie) VALUES (:abonne_id, :livre_id, NOW())";
    $stmt_emprunt = $pdo->prepare($sql_emprunt);
    $stmt_emprunt->bindParam(':abonne_id', $abonne_id, PDO::PARAM_INT);
    $stmt_emprunt->bindParam(':livre_id', $livre_id, PDO::PARAM_INT);
    $stmt_emprunt->execute();
    
    $_SESSION['success'] = 'Livre "' . $livre['titre'] . '" emprunté avec succès !';
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur lors de l\'emprunt. Veuillez réessayer.';
}

header('Location: index.php');
exit;
?>