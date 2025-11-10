<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

$errors = [];
$civilite = '';
$nom = '';
$prenom = '';
$email = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $civilite = trim($_POST['civilite'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validation
    if (empty($civilite)) {
        $errors[] = "La civilité est obligatoire.";
    }

    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    } elseif (strlen($nom) > 25) {
        $errors[] = "Le nom ne peut pas dépasser 25 caractères.";
    }

    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    } elseif (strlen($prenom) > 15) {
        $errors[] = "Le prénom ne peut pas dépasser 15 caractères.";
    }

    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    } elseif (strlen($email) > 50) {
        $errors[] = "L'email ne peut pas dépasser 50 caractères.";
    }

    // Vérifier l'unicité de l'email
    if (empty($errors)) {
        $pdo = getDbConnection();
        $sql_check = "SELECT COUNT(*) FROM abonne WHERE email = :email";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check->execute();
        
        if ($stmt_check->fetchColumn() > 0) {
            $errors[] = "Cet email est déjà utilisé par un autre abonné.";
        }
    }

    // Si pas d'erreurs, insérer en base
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO abonne (civilite, nom, prenom, email) VALUES (:civilite, :nom, :prenom, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':civilite', $civilite, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Abonné ajouté avec succès';
                header('Location: abonnes.php');
                exit();
            } else {
                $errors[] = "Erreur lors de l'ajout de l'abonné.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-2xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Ajouter un nouvel abonné</h1>
            <p class="text-gray-600">Remplissez le formulaire ci-dessous pour ajouter un abonné</p>
        </header>

        <section class="bg-white rounded-lg shadow-md p-8">
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

            <form method="POST" action="" novalidate>
                <!-- Champ Civilité -->
                <div class="mb-6">
                    <label for="civilite" class="block text-gray-700 font-semibold mb-2">
                        Civilité <span class="text-red-500">*</span>
                    </label>
                    <select id="civilite" name="civilite" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionnez...</option>
                        <option value="M." <?= $civilite === 'M.' ? 'selected' : '' ?>>M.</option>
                        <option value="Mme" <?= $civilite === 'Mme' ? 'selected' : '' ?>>Mme</option>
                    </select>
                </div>

                <!-- Champ Nom -->
                <div class="mb-6">
                    <label for="nom" class="block text-gray-700 font-semibold mb-2">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nom" name="nom" required maxlength="25"
                           value="<?= htmlspecialchars($nom) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: MARTIN">
                    <p class="text-gray-500 text-sm mt-1">Maximum 25 caractères</p>
                </div>

                <!-- Champ Prénom -->
                <div class="mb-6">
                    <label for="prenom" class="block text-gray-700 font-semibold mb-2">
                        Prénom <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="prenom" name="prenom" required maxlength="15"
                           value="<?= htmlspecialchars($prenom) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: Jean">
                    <p class="text-gray-500 text-sm mt-1">Maximum 15 caractères</p>
                </div>

                <!-- Champ Email -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required maxlength="50"
                           value="<?= htmlspecialchars($email) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: jean.martin@email.fr">
                    <p class="text-gray-500 text-sm mt-1">Maximum 50 caractères</p>
                </div>

                <p class="text-gray-600 text-sm mb-6">
                    <span class="text-red-500">*</span> Champs obligatoires
                </p>

                <div class="flex justify-between items-center">
                    <a href="abonnes.php" class="text-gray-600 hover:text-gray-800 transition font-medium">
                        ← Annuler
                    </a>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
                        ✓ Enregistrer l'abonné
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>