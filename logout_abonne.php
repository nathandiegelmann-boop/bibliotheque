<?php
session_start();

// Supprimer toutes les variables de session abonné
unset($_SESSION['abonne_id']);
unset($_SESSION['abonne_civilite']);
unset($_SESSION['abonne_nom']);
unset($_SESSION['abonne_prenom']);
unset($_SESSION['abonne_email']);

// Message de déconnexion
$_SESSION['message'] = 'Vous avez été déconnecté avec succès.';

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;
?>