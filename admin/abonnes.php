<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

// Récupérer tous les abonnés avec leurs emprunts en cours
$pdo = getDbConnection();
$sql = "SELECT a.*, 
               COUNT(e.id_emprunt) as total_emprunts,
               COUNT(CASE WHEN e.date_rendu IS NULL THEN 1 END) as emprunts_en_cours,
               GROUP_CONCAT(CASE WHEN e.date_rendu IS NULL THEN l.titre END SEPARATOR ', ') as livres_en_cours
        FROM abonne a 
        LEFT JOIN emprunt e ON a.id_abonne = e.id_abonne
        LEFT JOIN livre l ON e.id_livre = l.id_livre
        GROUP BY a.id_abonne
        ORDER BY a.nom, a.prenom";
$stmt = $pdo->query($sql);
$abonnes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAbonnes = count($abonnes);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <!-- En-tête de la page -->
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Gestion des abonnés
            </h1>
            <p class="text-gray-600">
                Total : <?= $totalAbonnes ?> abonné(s)
            </p>
        </div>

        <!-- Bouton pour ajouter un nouvel abonné -->
        <a href="abonne_add.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
            ➕ Ajouter un abonné
        </a>
    </header>

    <!-- Message de succès -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Succès</p>
            <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Section de la liste des abonnés -->
    <section aria-label="Liste des abonnés">

        <?php if (empty($abonnes)): ?>
        <!-- Message si aucun abonné n'est disponible -->
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p class="font-bold">Aucun abonné</p>
            <p>La bibliothèque ne contient actuellement aucun abonné.</p>
        </div>
        <?php else: ?>

        <!-- Tableau des abonnés -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Civilité
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prénom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Emprunts
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Livres en cours
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($abonnes as $abonne): ?>
                    <tr class="hover:bg-gray-50">
                        <!-- ID de l'abonné -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($abonne['id_abonne']) ?>
                        </td>

                        <!-- Civilité -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($abonne['civilite']) ?>
                        </td>

                        <!-- Nom -->
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($abonne['nom']) ?>
                        </td>

                        <!-- Prénom -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($abonne['prenom']) ?>
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($abonne['email']) ?>
                        </td>

                        <!-- Statistiques d'emprunts -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col">
                                <span class="text-blue-600 font-semibold"><?= $abonne['emprunts_en_cours'] ?> en cours</span>
                                <span class="text-gray-400 text-xs"><?= $abonne['total_emprunts'] ?> total</span>
                            </div>
                        </td>

                        <!-- Livres actuellement empruntés -->
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                            <?php if ($abonne['livres_en_cours']): ?>
                                <div class="truncate" title="<?= htmlspecialchars($abonne['livres_en_cours']) ?>">
                                    <?= htmlspecialchars($abonne['livres_en_cours']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Aucun</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <!-- Bouton voir détails -->
                            <a href="abonne_details.php?id=<?= $abonne['id_abonne'] ?>"
                                class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition font-medium">
                                👁️ Voir
                            </a>

                            <!-- Bouton modifier -->
                            <a href="abonne_edit.php?id=<?= $abonne['id_abonne'] ?>"
                                class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded transition font-medium">
                                ✏️ Modifier
                            </a>

                            <!-- Bouton supprimer -->
                            <a href="abonne_delete.php?id=<?= $abonne['id_abonne'] ?>"
                                class="inline-block bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition font-medium"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet abonné ?');">
                                🗑️ Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>