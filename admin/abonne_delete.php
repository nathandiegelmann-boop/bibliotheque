<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

// Vérifier si un ID d'abonné est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: abonnes.php');
    exit();
}

$id_abonne = (int)$_GET['id'];
$pdo = getDbConnection();

// Récupérer les informations de l'abonné
$sql = "SELECT * FROM abonne WHERE id_abonne = :id_abonne";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
$stmt->execute();
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonne) {
    header('Location: abonnes.php');
    exit();
}

$error = '';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Vérifier si l'abonné a des emprunts en cours
        $sql_check = "SELECT COUNT(*) FROM emprunt WHERE id_abonne = :id_abonne AND date_rendu IS NULL";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
        $stmt_check->execute();
        $has_active_loans = $stmt_check->fetchColumn() > 0;

        if ($has_active_loans) {
            $error = "Impossible de supprimer cet abonné car il a des emprunts en cours.";
        } else {
            // Supprimer d'abord l'historique des emprunts
            $sql_delete_emprunts = "DELETE FROM emprunt WHERE id_abonne = :id_abonne";
            $stmt_delete_emprunts = $pdo->prepare($sql_delete_emprunts);
            $stmt_delete_emprunts->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
            $stmt_delete_emprunts->execute();

            // Supprimer l'abonné
            $sql_delete = "DELETE FROM abonne WHERE id_abonne = :id_abonne";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
            
            if ($stmt_delete->execute()) {
                $_SESSION['success_message'] = 'Abonné supprimé avec succès';
                header('Location: abonnes.php');
                exit();
            } else {
                $error = "Erreur lors de la suppression de l'abonné.";
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur de base de données : " . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-2xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Supprimer un abonné</h1>
            <p class="text-gray-600">Confirmez la suppression de cet abonné</p>
        </header>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Erreur</p>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Informations de l'abonné</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="font-semibold text-gray-700">ID :</span>
                    <span class="text-gray-600"><?= htmlspecialchars($abonne['id_abonne']) ?></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Civilité :</span>
                    <span class="text-gray-600"><?= htmlspecialchars($abonne['civilite']) ?></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Nom :</span>
                    <span class="text-gray-600"><?= htmlspecialchars($abonne['nom']) ?></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Prénom :</span>
                    <span class="text-gray-600"><?= htmlspecialchars($abonne['prenom']) ?></span>
                </div>
                <div class="md:col-span-2">
                    <span class="font-semibold text-gray-700">Email :</span>
                    <span class="text-gray-600"><?= htmlspecialchars($abonne['email']) ?></span>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Attention ! Cette action est irréversible.</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>Êtes-vous sûr de vouloir supprimer définitivement cet abonné ? Cette action supprimera également tout l'historique des emprunts associés.</p>
                    </div>
                    <div class="mt-4">
                        <div class="flex space-x-4">
                            <form method="POST" class="inline">
                                <button type="submit" name="confirm_delete" value="1"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                                    🗑️ Confirmer la suppression
                                </button>
                            </form>
                            <a href="abonnes.php" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">
                                ← Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>