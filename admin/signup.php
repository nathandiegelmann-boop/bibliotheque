<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Si l'utilisateur est déjà connecté, le rediriger
if (isset($_SESSION['abonne_id'])) {
    header('Location: ../profile_abonne.php');
    exit;
}

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'Inscription - Bibliothèque';

// Variables d'affichage
$errors = [];
$civilite = "";
$nom = "";
$prenom = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération sécurisée des données
    $civilite = trim($_POST['civilite'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Validation des données
    if (empty($civilite) || !in_array($civilite, ['M.', 'Mme'])) {
        $errors[] = "Veuillez sélectionner une civilité.";
    }
    
    if (empty($nom) || strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    }
    
    if (empty($prenom) || strlen($prenom) < 2) {
        $errors[] = "Le prénom doit contenir au moins 2 caractères.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    if (strlen($mot_de_passe) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $sql_check = "SELECT COUNT(*) FROM abonne WHERE email = :email";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_check->execute();
            
            if ($stmt_check->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de vérification : " . $e->getMessage();
        }
    }

    // Si pas d'erreurs, insérer en base
    if (empty($errors)) {
        try {
            // Hashage sécurisé du mot de passe
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO abonne (civilite, nom, prenom, email, mot_de_passe) 
                    VALUES (:civilite, :nom, :prenom, :email, :mot_de_passe)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':civilite', $civilite, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Récupérer l'ID du nouvel abonné
                $nouvel_abonne_id = $pdo->lastInsertId();
                
                // Régénérer l'ID de session pour la sécurité
                session_regenerate_id(true);
                
                // Connecter automatiquement l'utilisateur après inscription
                $_SESSION['abonne_id'] = $nouvel_abonne_id;
                $_SESSION['abonne_civilite'] = $civilite;
                $_SESSION['abonne_nom'] = $nom;
                $_SESSION['abonne_prenom'] = $prenom;
                $_SESSION['abonne_email'] = $email;
                $_SESSION['login_time'] = time(); // Timestamp de connexion
                
                // Message de bienvenue personnalisé
                $_SESSION['message'] = '🎉 Bienvenue dans notre bibliothèque, ' . htmlspecialchars($prenom) . ' ! Votre compte a été créé avec succès et vous êtes maintenant connecté(e). Découvrez notre catalogue de livres !';
                
                // Redirection vers la page d'accueil
                header('Location: ../index.php');
                exit();
            } else {
                $errors[] = "Erreur lors de l'inscription.";
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
    <div class="max-w-md mx-auto">
        <!-- Titre de la page -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                🆔 Inscription Abonné(e)
            </h1>
            <p class="text-gray-600">
                Créez votre compte et accédez immédiatement à notre catalogue
            </p>
        </header>

        <!-- Formulaire d'inscription -->
        <section class="bg-white rounded-lg shadow-md p-8">
            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">❌ Erreurs détectées :</p>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" novalidate>
                <!-- Champ Civilité -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Civilité *
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="civilite" value="M." 
                                   <?= $civilite === 'M.' ? 'checked' : '' ?> 
                                   class="mr-2 text-blue-600">
                            M.
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="civilite" value="Mme" 
                                   <?= $civilite === 'Mme' ? 'checked' : '' ?> 
                                   class="mr-2 text-blue-600">
                            Mme
                        </label>
                    </div>
                </div>

                <!-- Champ Nom -->
                <div class="mb-6">
                    <label for="nom" class="block text-gray-700 font-semibold mb-2">
                        Nom *
                    </label>
                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        required
                        value="<?= htmlspecialchars($nom) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Entrez votre nom de famille">
                </div>

                <!-- Champ Prénom -->
                <div class="mb-6">
                    <label for="prenom" class="block text-gray-700 font-semibold mb-2">
                        Prénom *
                    </label>
                    <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        required
                        value="<?= htmlspecialchars($prenom) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Entrez votre prénom">
                </div>

                <!-- Champ Email -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        Email *
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        value="<?= htmlspecialchars($email) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="votre.email@exemple.com">
                </div>

                <!-- Champ Mot de passe -->
                <div class="mb-6">
                    <label for="mot_de_passe" class="block text-gray-700 font-semibold mb-2">
                        Mot de passe *
                    </label>
                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Minimum 6 caractères">
                    <p class="text-sm text-gray-600 mt-1">
                        ⚠️ Choisissez un mot de passe sécurisé d'au moins 6 caractères
                    </p>
                </div>

                <!-- Bouton de soumission -->
                <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    🚀 Créer mon compte et me connecter
                </button>
                
                <!-- Note informative -->
                <p class="text-sm text-gray-500 mt-3 text-center">
                    💡 Vous serez automatiquement connecté(e) après l'inscription
                </p>
            </form>

            <!-- Liens de navigation -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-gray-600">Déjà inscrit ?</p>
                <a href="../login.php" class="text-blue-600 hover:text-blue-800 transition font-medium">
                    🔑 Se connecter
                </a>
                <br>
                <a href="../index.php" class="text-gray-600 hover:text-gray-800 transition text-sm">
                    ← Retour à l'accueil
                </a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>