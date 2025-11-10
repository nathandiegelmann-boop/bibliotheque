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

// Récupérer l'historique complet des emprunts
$sql_emprunts = "SELECT e.*, l.titre, l.auteur, l.couverture,
                        e.date_sortie, e.date_rendu,
                        CASE WHEN e.date_rendu IS NULL THEN 'En cours' ELSE 'Rendu' END as statut
                 FROM emprunt e 
                 JOIN livre l ON e.id_livre = l.id_livre 
                 WHERE e.id_abonne = :id_abonne 
                 ORDER BY e.date_sortie DESC";
$stmt_emprunts = $pdo->prepare($sql_emprunts);
$stmt_emprunts->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
$stmt_emprunts->execute();
$emprunts = $stmt_emprunts->fetchAll(PDO::FETCH_ASSOC);

// Calculer les statistiques
$total_emprunts = count($emprunts);
$emprunts_en_cours = count(array_filter($emprunts, fn($e) => is_null($e['date_rendu'])));
$emprunts_rendus = $total_emprunts - $emprunts_en_cours;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">
                    Détails de l'abonné
                </h1>
                <p class="text-gray-600">
                    <?= htmlspecialchars($abonne['civilite']) ?> <?= htmlspecialchars($abonne['nom']) ?> <?= htmlspecialchars($abonne['prenom']) ?>
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="abonne_edit.php?id=<?= $abonne['id_abonne'] ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                    ✏️ Modifier
                </a>
                <a href="abonnes.php" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                    ← Retour
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informations de l'abonné -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Informations personnelles</h2>
                    
                    <div class="space-y-3">
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
                        <div>
                            <span class="font-semibold text-gray-700">Email :</span>
                            <span class="text-gray-600"><?= htmlspecialchars($abonne['email']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistiques</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                            <span class="font-medium text-blue-800">Emprunts en cours</span>
                            <span class="text-2xl font-bold text-blue-600"><?= $emprunts_en_cours ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                            <span class="font-medium text-green-800">Emprunts rendus</span>
                            <span class="text-2xl font-bold text-green-600"><?= $emprunts_rendus ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium text-gray-800">Total emprunts</span>
                            <span class="text-2xl font-bold text-gray-600"><?= $total_emprunts ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des emprunts -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Historique des emprunts</h2>
                    
                    <?php if (empty($emprunts)): ?>
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-6xl mb-4">📚</div>
                            <p class="text-gray-600 text-lg">Aucun emprunt trouvé</p>
                            <p class="text-gray-500">Cet abonné n'a encore effectué aucun emprunt.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($emprunts as $emprunt): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start space-x-4">
                                        <!-- Image de couverture -->
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($emprunt['couverture'])): ?>
                                                <img src="../<?= htmlspecialchars($emprunt['couverture']) ?>" 
                                                     alt="Couverture de <?= htmlspecialchars($emprunt['titre']) ?>"
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

                                        <!-- Informations du livre et de l'emprunt -->
                                        <div class="flex-grow">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h3 class="font-semibold text-gray-800 text-lg">
                                                        <?= htmlspecialchars($emprunt['titre']) ?>
                                                    </h3>
                                                    <p class="text-gray-600 mb-2">
                                                        par <?= htmlspecialchars($emprunt['auteur']) ?>
                                                    </p>
                                                </div>
                                                
                                                <!-- Statut -->
                                                <div>
                                                    <?php if ($emprunt['statut'] === 'En cours'): ?>
                                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                            📖 En cours
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                            ✅ Rendu
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Dates -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 text-sm">
                                                <div class="flex items-center text-gray-600">
                                                    <span class="font-medium mr-2">📅 Emprunté le :</span>
                                                    <span><?= date('d/m/Y', strtotime($emprunt['date_sortie'])) ?></span>
                                                </div>
                                                
                                                <?php if ($emprunt['date_rendu']): ?>
                                                    <div class="flex items-center text-gray-600">
                                                        <span class="font-medium mr-2">📋 Rendu le :</span>
                                                        <span><?= date('d/m/Y', strtotime($emprunt['date_rendu'])) ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex items-center text-orange-600">
                                                        <span class="font-medium mr-2">⏳ En cours depuis :</span>
                                                        <span>
                                                            <?php
                                                            $date_emprunt = new DateTime($emprunt['date_sortie']);
                                                            $maintenant = new DateTime();
                                                            $diff = $maintenant->diff($date_emprunt);
                                                            echo $diff->days . ' jour(s)';
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>