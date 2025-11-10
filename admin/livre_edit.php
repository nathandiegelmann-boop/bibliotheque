<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

// Vérifier si un ID de livre est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: livres.php');
    exit();
}

$id_livre = (int)$_GET['id'];
$pdo = getDbConnection();

// Récupérer les informations du livre
$sql = "SELECT * FROM livre WHERE id_livre = :id_livre";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
$stmt->execute();
$livre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$livre) {
    header('Location: livres.php');
    exit();
}

$errors = [];
$titre = $livre['titre'];
$auteur = $livre['auteur'];
$couverture = $livre['couverture'] ?? '';

// Traitement du formulaire de modification
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

    // Si pas d'erreurs, mettre à jour en base
    if (empty($errors)) {
        try {
            $sql_update = "UPDATE livre SET titre = :titre, auteur = :auteur, couverture = :couverture WHERE id_livre = :id_livre";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':titre', $titre, PDO::PARAM_STR);
            $stmt_update->bindParam(':auteur', $auteur, PDO::PARAM_STR);
            $stmt_update->bindParam(':couverture', $couverture, PDO::PARAM_STR);
            $stmt_update->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
            
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = 'Livre modifié avec succès';
                header('Location: livres.php');
                exit();
            } else {
                $errors[] = "Erreur lors de la modification du livre.";
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
                    Modifier un livre
                </h1>
                <p class="text-gray-600">
                    Livre ID : <?= htmlspecialchars($livre['id_livre']) ?> - <?= htmlspecialchars($livre['titre']) ?>
                </p>
            </header>

            <!-- Formulaire de modification -->
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
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
                            ✓ Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
