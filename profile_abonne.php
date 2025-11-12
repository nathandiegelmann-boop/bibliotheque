<?php
session_start();

// Vérifier si l'abonné est connecté
if (!isset($_SESSION['abonne_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$page_title = "Mon Profil - Abonné";
$pdo = getDbConnection();

// Récupérer les informations complètes de l'abonné
$sql = "SELECT * FROM abonne WHERE id_abonne = :id_abonne";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_abonne', $_SESSION['abonne_id'], PDO::PARAM_INT);
$stmt->execute();
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les statistiques d'emprunts de l'abonné
$sql_stats = "SELECT 
                COUNT(*) as total_emprunts,
                COUNT(CASE WHEN date_rendu IS NULL THEN 1 END) as emprunts_en_cours,
                COUNT(CASE WHEN date_rendu IS NOT NULL THEN 1 END) as emprunts_rendus
              FROM emprunt 
              WHERE id_abonne = :id_abonne";
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->bindParam(':id_abonne', $_SESSION['abonne_id'], PDO::PARAM_INT);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Récupérer les emprunts en cours avec durée
$sql_en_cours = "SELECT e.*, l.titre, l.auteur, l.couverture,
                        DATEDIFF(NOW(), e.date_sortie) as jours_emprunt
                 FROM emprunt e 
                 JOIN livre l ON e.id_livre = l.id_livre 
                 WHERE e.id_abonne = :id_abonne AND e.date_rendu IS NULL
                 ORDER BY e.date_sortie DESC";
$stmt_en_cours = $pdo->prepare($sql_en_cours);
$stmt_en_cours->bindParam(':id_abonne', $_SESSION['abonne_id'], PDO::PARAM_INT);
$stmt_en_cours->execute();
$emprunts_en_cours = $stmt_en_cours->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/nav.php';
?>

<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-4xl mx-auto">
        
        <!-- En-tête du profil -->
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">
                    👤 Mon Profil
                </h1>
                <p class="text-gray-600">
                    Gérez vos informations personnelles et vos emprunts
                </p>
            </div>
            <div>
                <a href="index.php" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Informations personnelles -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    📋 Informations personnelles
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">ID Abonné :</span>
                        <span class="text-gray-600">#<?= htmlspecialchars($abonne['id_abonne']) ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">Civilité :</span>
                        <span class="text-gray-600"><?= htmlspecialchars($abonne['civilite']) ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">Nom :</span>
                        <span class="text-gray-600"><?= htmlspecialchars($abonne['nom']) ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">Prénom :</span>
                        <span class="text-gray-600"><?= htmlspecialchars($abonne['prenom']) ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">Email :</span>
                        <span class="text-gray-600"><?= htmlspecialchars($abonne['email']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Statistiques d'emprunts -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    📊 Mes statistiques
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?= $stats['emprunts_en_cours'] ?></div>
                        <div class="text-sm text-blue-800">En cours</div>
                    </div>
                    
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?= $stats['emprunts_rendus'] ?></div>
                        <div class="text-sm text-green-800">Rendus</div>
                    </div>
                    
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-600"><?= $stats['total_emprunts'] ?></div>
                        <div class="text-sm text-gray-800">Total</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emprunts en cours -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                📚 Mes emprunts en cours
            </h2>
            
            <?php if (empty($emprunts_en_cours)): ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">📖</div>
                    <p class="text-gray-600">Aucun emprunt en cours</p>
                    <p class="text-gray-500 text-sm">Rendez-vous dans le catalogue pour emprunter des livres</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($emprunts_en_cours as $emprunt): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start space-x-3">
                                <!-- Image de couverture -->
                                <div class="flex-shrink-0">
                                    <?php if (!empty($emprunt['couverture'])): ?>
                                        <img src="<?= htmlspecialchars($emprunt['couverture']) ?>" 
                                             alt="Couverture"
                                             class="w-16 h-20 object-cover rounded shadow-sm"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-16 h-20 bg-gray-200 rounded flex items-center justify-center" style="display: none;">
                                            <span class="text-gray-500 text-xs">📖</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-16 h-20 bg-gray-200 rounded flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">📖</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Informations du livre -->
                                <div class="flex-grow">
                                    <h3 class="font-semibold text-gray-900 text-sm">
                                        <?= htmlspecialchars($emprunt['titre']) ?>
                                    </h3>
                                    <p class="text-gray-600 text-xs mb-2">
                                        par <?= htmlspecialchars($emprunt['auteur']) ?>
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        📅 Emprunté le <?= date('d/m/Y', strtotime($emprunt['date_sortie'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions rapides -->
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                ⚡ Actions rapides
            </h2>
            <div class="flex flex-wrap gap-4">
                <a href="index.php" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                    📚 Parcourir le catalogue
                </a>
                <a href="logout_abonne.php" 
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                    🚪 Se déconnecter
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>