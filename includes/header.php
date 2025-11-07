<?php
//démarrage de la session 
if (session_status() === PHP_SESSION_NONE) {
      session_start();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">

      <title><?= $page_title ?? "Site Bibliothèque"; ?></title>

      <script src="https://cdn.tailwindcss.com"></script>

      <meta name="description" content="Système de gestion de bibliothèque">
      <meta name="author" content="Bibliothèque">
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
      <?php if (isset($_SESSION['message'])) : ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                  <p><?= $_SESSION['message'] ?></p>
            </div>
      <?php
            unset($_SESSION['message']); //supprimer le message après affichage
      endif;
      ?>
      <?php if (isset($_SESSION['error'])) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                  <p><?= $_SESSION['error']; ?></p>
            </div>
      <?php
            unset($_SESSION['error']); //supprimer le message après affichage
      endif;
      ?>