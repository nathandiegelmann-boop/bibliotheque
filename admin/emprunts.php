<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

// Gestion du retour de livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_rendu'])) {
    $emprunt_id = (int)$_POST['emprunt_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE emprunt SET date_rendu = NOW() WHERE id_emprunt = ? AND date_rendu IS NULL");
        $result = $stmt->execute([$emprunt_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            $_SESSION['message'] = "Livre marqué comme rendu avec succès !";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Erreur : Emprunt introuvable ou déjà rendu.";
            $_SESSION['message_type'] = 'error';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur lors du retour du livre.";
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: emprunts.php');
    exit();
}

// Récupérer tous les emprunts avec les informations des livres et abonnés
$sql = "SELECT e.id_emprunt, e.date_sortie, e.date_rendu,
               l.titre, l.auteur, l.couverture,
               a.civilite, a.nom, a.prenom, a.email,
               CASE WHEN e.date_rendu IS NULL THEN 'En cours' ELSE 'Rendu' END as statut,
               CASE WHEN e.date_rendu IS NULL 
                    THEN DATEDIFF(NOW(), e.date_sortie) 
                    ELSE DATEDIFF(e.date_rendu, e.date_sortie) 
               END as duree_jours
        FROM emprunt e
        JOIN livre l ON e.id_livre = l.id_livre
        JOIN abonne a ON e.id_abonne = a.id_abonne
        ORDER BY e.date_sortie DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer les statistiques
$stats = [
    'total' => count($emprunts),
    'en_cours' => count(array_filter($emprunts, fn($e) => $e['statut'] === 'En cours')),
    'rendus' => count(array_filter($emprunts, fn($e) => $e['statut'] === 'Rendu')),
    'retard' => count(array_filter($emprunts, fn($e) => $e['statut'] === 'En cours' && $e['duree_jours'] > 30))
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <div class="max-w-7xl mx-auto">
        <!-- En-tête -->
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">
                    📚 Gestion des Emprunts
                </h1>
                <p class="text-gray-600">
                    Gérez les emprunts et les retours de livres
                </p>
            </div>
        </header>

        <!-- Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 rounded-md p-4 <?= $_SESSION['message_type'] === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php if ($_SESSION['message_type'] === 'success'): ?>
                            <span class="text-green-600">✅</span>
                        <?php else: ?>
                            <span class="text-red-600">❌</span>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm <?= $_SESSION['message_type'] === 'success' ? 'text-green-800' : 'text-red-800' ?>">
                            <?= $_SESSION['message'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">📊</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total emprunts
                                </dt>
                                <dd class="text-3xl font-bold text-gray-900">
                                    <?= $stats['total'] ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">📖</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    En cours
                                </dt>
                                <dd class="text-3xl font-bold text-blue-900">
                                    <?= $stats['en_cours'] ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">✅</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Rendus
                                </dt>
                                <dd class="text-3xl font-bold text-green-900">
                                    <?= $stats['rendus'] ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">⚠️</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    En retard
                                </dt>
                                <dd class="text-3xl font-bold text-red-900">
                                    <?= $stats['retard'] ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des emprunts -->
        <section class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    Liste des emprunts
                </h2>
            </div>

            <?php if (empty($emprunts)): ?>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📚</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun emprunt trouvé</h3>
                    <p class="text-gray-600">Il n'y a actuellement aucun emprunt enregistré.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Livre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Abonné
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date d'emprunt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durée
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($emprunts as $emprunt): ?>
                                <tr class="hover:bg-gray-50">
                                    <!-- Livre -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-10">
                                                <?php if (!empty($emprunt['couverture'])): ?>
                                                    <img src="../<?= htmlspecialchars($emprunt['couverture']) ?>" 
                                                         alt="Couverture"
                                                         class="h-12 w-10 object-cover rounded shadow-sm"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="h-12 w-10 bg-gray-200 rounded flex items-center justify-center" style="display: none;">
                                                        <span class="text-gray-500 text-xs">📖</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="h-12 w-10 bg-gray-200 rounded flex items-center justify-center">
                                                        <span class="text-gray-500 text-xs">📖</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($emprunt['titre']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($emprunt['auteur']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Abonné -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($emprunt['civilite']) ?> <?= htmlspecialchars($emprunt['nom']) ?> <?= htmlspecialchars($emprunt['prenom']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($emprunt['email']) ?>
                                        </div>
                                    </td>

                                    <!-- Date d'emprunt -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($emprunt['date_sortie'])) ?>
                                        <?php if ($emprunt['date_rendu']): ?>
                                            <br><small class="text-green-600">Rendu le <?= date('d/m/Y', strtotime($emprunt['date_rendu'])) ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Durée -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="<?= $emprunt['statut'] === 'En cours' && $emprunt['duree_jours'] > 30 ? 'text-red-600 font-semibold' : 'text-gray-500' ?>">
                                            <?= $emprunt['duree_jours'] ?> jour(s)
                                        </span>
                                    </td>

                                    <!-- Statut -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($emprunt['statut'] === 'En cours'): ?>
                                            <?php if ($emprunt['duree_jours'] > 30): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    ⚠️ En retard
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    📖 En cours
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✅ Rendu
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($emprunt['statut'] === 'En cours'): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="emprunt_id" value="<?= $emprunt['id_emprunt'] ?>">
                                                <button type="submit" name="marquer_rendu" 
                                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs transition"
                                                        onclick="return confirm('Marquer ce livre comme rendu ?');">
                                                    ✅ Marquer rendu
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">Déjà rendu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>