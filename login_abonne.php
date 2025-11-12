<?php /*
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        require_once __DIR__ . '/config/database.php';
        
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT * FROM abonne WHERE email = ?");
            $stmt->execute([$email]);
            $abonne = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Pour simplifier, on vérifie juste l'email (pas de mot de passe pour l'instant)
            if ($abonne) {
                $_SESSION['abonne_id'] = $abonne['id_abonne'];
                $_SESSION['abonne_nom'] = $abonne['nom'];
                $_SESSION['abonne_prenom'] = $abonne['prenom'];
                $_SESSION['abonne_email'] = $abonne['email'];
                
                $message = "Connexion réussie ! Bonjour " . $abonne['prenom'];
            } else {
                $message = "Email introuvable.";
            }
        } catch (PDOException $e) {
            $message = "Erreur de connexion.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Connexion Abonné
            </h1>
            <p class="text-gray-600">Connectez-vous à votre compte</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?= isset($_SESSION['abonne_id']) ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email
                </label>
                <input type="email" id="email" name="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="votre.email@exemple.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Mot de passe
                </label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Votre mot de passe">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">
                Se connecter
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm">
                ← Retour à l'accueil
            </a>
        </div>

        <!-- Info pour test -->
        <div class="mt-6 p-4 bg-yellow-50 rounded-md">
            <p class="text-sm text-yellow-800">
                <strong>Test :</strong> Utilisez n'importe quel email des abonnés existants (ex: guillaume.dubois@email.fr)
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; */?>