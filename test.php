<?php
$pageTitle = "Test - Bibliothèque";

echo "Avant include header<br>";
include __DIR__ . '/includes/header.php';
echo "Après include header<br>";

include __DIR__ . '/includes/nav.php';
echo "Après include nav<br>";
?>

<main class="container mx-auto px-4 py-8">
    <h1>Test de la page</h1>
    <p>Si vous voyez ce texte, les includes fonctionnent.</p>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>