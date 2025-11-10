<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

// Récupérer tous les livres avec les informations d'emprunt
$pdo = getDbConnection();
$sql = "SELECT l.*, 
               CONCAT(a.nom, ' ', a.prenom) as abonne_nom
        FROM livre l 
        LEFT JOIN emprunt e ON l.id_livre = e.id_livre AND e.date_rendu IS NULL
        LEFT JOIN abonne a ON e.id_abonne = a.id_abonne
        ORDER BY l.id_livre DESC";
$stmt = $pdo->query($sql);
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le total des livres
$totalLivres = count($livres);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';

?>
<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <!-- En-tête de la page -->
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Gestion des livres
            </h1>
            <p class="text-gray-600">
                Total : <?= $totalLivres ?> livre(s)
            </p>
        </div>

        <!-- Bouton pour ajouter un nouveau livre -->
        <a href="livre_add.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
            ➕ Ajouter un livre
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

    <!-- Section de la liste des livres -->
    <section aria-label="Liste des livres">

        <?php if (empty($livres)): ?>
        <!-- Message si aucun livre n'est disponible -->
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p class="font-bold">Aucun livre</p>
            <p>La bibliothèque ne contient actuellement aucun livre.</p>
        </div>
        <?php else: ?>

        <!-- Tableau des livres -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Titre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Auteur
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Emprunté par
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($livres as $livre): ?>
                    <tr class="hover:bg-gray-50">
                        <!-- ID du livre -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($livre['id_livre']) ?>
                        </td>

                        <!-- Titre du livre -->
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($livre['titre']) ?>
                        </td>

                        <!-- Auteur du livre -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($livre['auteur']) ?>
                        </td>

                        <!-- Statut du livre -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if ($livre['abonne_nom']): ?>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">
                                ✗ En prêt
                            </span>
                            <?php else: ?>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                ✓ Disponible
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Abonné ayant emprunté le livre -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= $livre['abonne_nom'] ? htmlspecialchars($livre['abonne_nom']) : '-' ?>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <!-- Bouton modifier -->
                            <a href="livre_edit.php?id=<?= $livre['id_livre'] ?>"
                                class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition font-medium">
                                ✏️ Modifier
                            </a>

                            <!-- Bouton supprimer -->
                            <a href="livre_delete.php?id=<?= $livre['id_livre'] ?>"
                                class="inline-block bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition font-medium"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?');">
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
<?php
include __DIR__ . '/../includes/footer.php';
?>