<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

$message = '';
$errors = [];
$titre = '';
$auteur = '';
$couverture = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $couverture = trim($_POST['couverture'] ?? '');

    // Validation
    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire.";
    } elseif (strlen($titre) > 30) {
        $errors[] = "Le titre ne peut pas dépasser 30 caractères.";
    }

    if (empty($auteur)) {
        $errors[] = "L'auteur est obligatoire.";
    } elseif (strlen($auteur) > 25) {
        $errors[] = "L'auteur ne peut pas dépasser 25 caractères.";
    }

    if (!empty($couverture) && strlen($couverture) > 100) {
        $errors[] = "L'URL de la couverture ne peut pas dépasser 100 caractères.";
    }

    // Si pas d'erreurs, insérer en base
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $sql = "INSERT INTO livre (titre, auteur, couverture) VALUES (:titre, :auteur, :couverture)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
            $stmt->bindParam(':auteur', $auteur, PDO::PARAM_STR);
            $stmt->bindParam(':couverture', $couverture, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                header('Location: livres.php?message=Livre ajouté avec succès');
                exit();
            } else {
                $errors[] = "Erreur lors de l'ajout du livre.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';

?>

<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-2xl mx-auto">
        <!-- En-tête de la page -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Ajouter un nouveau livre
            </h1>
            <p class="text-gray-600">
                Remplissez le formulaire ci-dessous pour ajouter un livre à la bibliothèque
            </p>
        </header>

        <!-- Formulaire d'ajout -->
        <section class="bg-white rounded-lg shadow-md p-8">
            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Erreur(s) :</p>
                <ul class="list-disc list-inside mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" action="" novalidate>
                <!-- Champ Titre -->
                <div class="mb-6">
                    <label for="titre" class="block text-gray-700 font-semibold mb-2">
                        Titre du livre <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="titre"
                        name="titre"
                        required
                        maxlength="30"
                        value="<?= htmlspecialchars($titre) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: Les Misérables">
                    <p class="text-gray-500 text-sm mt-1">Maximum 30 caractères</p>
                </div>

                <!-- Champ Auteur -->
                <div class="mb-6">
                    <label for="auteur" class="block text-gray-700 font-semibold mb-2">
                        Auteur <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="auteur"
                        name="auteur"
                        required
                        maxlength="25"
                        value="<?= htmlspecialchars($auteur) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: VICTOR HUGO">
                    <p class="text-gray-500 text-sm mt-1">Maximum 25 caractères</p>
                </div>

                <!-- Champ Couverture -->
                <div class="mb-6">
                    <label for="couverture" class="block text-gray-700 font-semibold mb-2">
                        URL de la couverture
                    </label>
                    <input
                        type="text"
                        id="couverture"
                        name="couverture"
                        maxlength="100"
                        value="<?= htmlspecialchars($couverture) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: images/couvertures/les_miserables.jpg">
                    <p class="text-gray-500 text-sm mt-1">Optionnel - Chemin relatif vers l'image de couverture</p>
                </div>

                <!-- Légende des champs obligatoires -->
                <p class="text-gray-600 text-sm mb-6">
                    <span class="text-red-500">*</span> Champs obligatoires
                </p>

                <!-- Boutons d'action -->
                <div class="flex justify-between items-center">
                    <!-- Bouton Annuler -->
                    <a href="livres.php" class="text-gray-600 hover:text-gray-800 transition font-medium">
                        ← Annuler
                    </a>

                    <!-- Bouton Enregistrer -->
                    <button
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
                        ✓ Enregistrer le livre
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>
<?php
include __DIR__ . '/../includes/footer.php';
?>