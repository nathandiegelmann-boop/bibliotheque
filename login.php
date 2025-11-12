<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();
session_start();
if (isset($_SESSION["admin_id"])) {
    header('Location: admin/dashboard.php');
    exit;
}

$page_title = 'Login Admin';

$error = "";
$email = "";
$log = null;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    extract($_POST);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error .= "<p>Format de l'email invalide.</p>";
    }
    if (iconv_strlen(trim($mot_de_passe)) < 5) {
        $error .= "<p>Le mot de passe doit contenir au moins 5 caractères.</p>";
    }
    if (empty($error)) {
        // D'abord vérifier dans la table abonne
        $sql_abonne = "SELECT id_abonne, civilite, nom, prenom, email, mot_de_passe FROM abonne WHERE email = :email LIMIT 1;";
        $log_abonne = $pdo->prepare($sql_abonne);
        $log_abonne->bindParam(":email", $email, PDO::PARAM_STR);
        $log_abonne->execute();
        
        if ($log_abonne->rowCount() === 1) {
            $abonne = $log_abonne->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier si l'abonné a un mot de passe (nouveaux comptes)
            if (!empty($abonne['mot_de_passe'])) {
                // Nouveau système avec mot de passe hashé
                if (password_verify($mot_de_passe, $abonne['mot_de_passe'])) {
                    $_SESSION['abonne_id'] = $abonne['id_abonne'];
                    $_SESSION['abonne_civilite'] = $abonne['civilite'];
                    $_SESSION['abonne_nom'] = $abonne['nom'];
                    $_SESSION['abonne_prenom'] = $abonne['prenom'];
                    $_SESSION['abonne_email'] = $abonne['email'];
                    
                    $_SESSION['message'] = 'Bienvenue ' . $abonne['prenom'] . ' (Abonné)';
                    header('Location: index.php');
                    exit;
                } else {
                    $error = '<p>Email ou mot de passe incorrect</p>';
                }
            } else {
                // Ancien système sans mot de passe (abonnés existants)
                $_SESSION['abonne_id'] = $abonne['id_abonne'];
                $_SESSION['abonne_civilite'] = $abonne['civilite'];
                $_SESSION['abonne_nom'] = $abonne['nom'];
                $_SESSION['abonne_prenom'] = $abonne['prenom'];
                $_SESSION['abonne_email'] = $abonne['email'];
                
                $_SESSION['message'] = 'Bienvenue ' . $abonne['prenom'] . ' (Abonné) - Pensez à créer un mot de passe !';
                header('Location: index.php');
                exit;
            }
        } else {
            // Pas trouvé dans abonne, vérifier dans administrateur
            $sql = "SELECT id_admin, login, mot_de_passe, nom, prenom, email FROM administrateur WHERE email = :email LIMIT 1;";
            $log = $pdo->prepare($sql);
            $log->bindParam(":email", $email, PDO::PARAM_STR);
            $log->execute();
            
            if ($log && $log->rowCount() === 1) {
                $admin = $log->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($mot_de_passe, $admin['mot_de_passe'])) {
                    $_SESSION['admin_id'] = $admin['id_admin'];
                    $_SESSION['admin_login'] = $admin['login'];
                    $_SESSION['admin_nom'] = $admin['nom'];
                    $_SESSION['admin_prenom'] = $admin['prenom'];
                    $_SESSION['admin_email'] = $admin['email'];

                    $update_sql = 'UPDATE administrateur SET dernier_acces = NOW() WHERE id_admin = :id_admin';
                    $update_value = $pdo->prepare($update_sql);
                    $update_value->bindParam(':id_admin', $admin['id_admin'], PDO::PARAM_INT);
                    $update_value->execute();

                    $_SESSION['message'] = 'Bienvenue ' . $admin['prenom'] . '';
                    header('Location: admin/dashboard.php');
                    exit;
                } else {
                    $error = '<p>Email ou mot de passe incorrect</p>';
                }
            } else {
                $error = '<p>Email non trouvé dans le système</p>';
            }
        }
    }
}



include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/nav.php';
?>

<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-md mx-auto">
        <!-- Titre de la page -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Connexion 
            </h1>
            <p class="text-gray-600">
                Connectez-vous pour accéder à l'interface d'administration
            </p>
        </header>

        <!-- Formulaire de connexion -->
        <section class="bg-white rounded-lg shadow-md p-8">
            <!-- Affichage des erreurs -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Erreur</p>
                    <p><?= $error ?></p>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" novalidate>
                <!-- Champ Login -->
                <div class="mb-6">
                    <label for="login" class="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        value="<?= $_POST['email'] ?? "" ?>"
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
                        placeholder="Entrez votre mot de passe">
                </div>

                <!-- Bouton de soumission -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    Se connecter
                </button>
            </form>

            <!-- Liens de navigation -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-gray-600">Pas encore inscrit ?</p>
                <a href="admin/signup.php" class="text-green-600 hover:text-green-800 transition font-medium">
                    ✍️ Créer un compte abonné
                </a>
                <br>
                <a href="/bibliotheque/index.php" class="text-blue-600 hover:text-blue-800 transition text-sm">
                    ← Retour à l'accueil
                </a>
            </div>
        </section>

    </div>
</main>
<?php
include __DIR__ . '/includes/footer.php'
?>