<?php

//démarrer le ssion : 
session_start();
//appel à la bdd : 
require_once __DIR__ . '/../config/database.php';

//si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord admin
if (isset($_SESSION['abonne_id'])) {
    header('Location: abonne/dashboard.php');
    exit;
}

//page title
$page_title = 'Page d inscription';

//variable d'affichage
$error = "";

$civilite = "";
$nom = "";
$prenom = "";
$email = "";
$mot_de_passe = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);
    var_dump($_POST);
    var_dump($civilite);



    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error .= "<p>Format d'email invalide.</p>";
    }
    if (iconv_strlen(trim($mot_de_passe)) < 5) {
        $error .= "<p>Le mot de passe doit contenir au moins 5 caractères.</p>";
    }

    // Si pas d'erreurs, insérer en base
    if (empty($error)) {
        if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $sql = "INSERT INTO abonne (civilite, nom, prenom, email, mot_de_passe) VALUES (:civilite, :nom, :prenom, :email, :mot_de_passe)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':civilite', $civilite, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe, PDO::PARAM_STR);
            
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Vous êtes desormais des notres';
                header('Location: /bibliotheque/index.php');
                exit();
            } else {
                $errors[] = "Erreur lors de l'inscription.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
    }
}

require_once __DIR__ . '/../config/database.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';

?>

<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-md mx-auto">
        <!-- Titre de la page -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Inscription Abonné(e)
            </h1>
            <p class="text-gray-600">
                création d'un compte abonné
            </p>
        </header>

        <!-- Formulaire de connexion -->
        <section class="bg-white rounded-lg shadow-md p-8">
            <!-- Affichage des erreurs -->

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Erreur</p>
                    <p><?= $error; ?></p>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" novalidate>
                <!-- Champ nom -->
                 <div class="mb-6">
                    <label for="civilite" class="block text-gray-700 font-semibold mb-2">
                        Civilité
                    </label>
                    <label>
                        <input type="radio" name="civilite" value="M." required> M.
                    </label>
                    <label>
                        <input type="radio" name="civilite" value="Mme" required> Mme
                    </label>
                </div>
                 <div class="mb-6">
                    <label for="nom" class="block text-gray-700 font-semibold mb-2">
                        Nom
                    </label>
                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        required
                        autocomplete="nom"
                        value="<?= $_POST['nom'] ?? ""; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Entrez votre nom">
                </div>
                <!-- Champ pnom -->
                 <div class="mb-6">
                    <label for="pnom" class="block text-gray-700 font-semibold mb-2">
                        Prénom
                    </label>
                    <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        required
                        autocomplete="prenom"
                        value="<?= $_POST['prenom'] ?? ""; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Entrez votre prénom">
                </div>
                <!-- Champ email -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        value="<?= $_POST['email'] ?? ""; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Entrez votre email">
                </div>


                <!-- Champ Mot de passe -->
                <div class="mb-6">
                    <label for="mot_de_passe" class="block text-gray-700 font-semibold mb-2">
                        Mot de passe
                    </label>
                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Définissez votre mot de passe">
                </div>

                <!-- Bouton de soumission -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    S'inscrire
                </button>
            </form>

            <!-- Lien de retour -->
            <div class="mt-6 text-center">
                <a href="/bibliotheque/index.php" class="text-blue-600 hover:text-blue-800 transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </section>

    </div>
</main>
<?php
include __DIR__ . '/../includes/footer.php';
?>