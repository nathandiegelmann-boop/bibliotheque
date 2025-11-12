<?php
session_start();
require_once __DIR__ . '/config/database.php';

$page_title = "Accueil - Bibliothèque";
$pdo = getDbConnection();

$sql = "SELECT l.id_livre, l.auteur, l.titre, l.couverture,
COUNT(e.id_emprunt) as emprunts_en_cours,
CASE WHEN COUNT(e.id_emprunt) > 0 THEN 'emprunte'
	ELSE 'disponible'
END AS statut
FROM livre l 
LEFT JOIN emprunt e ON l.id_livre = e.id_livre AND e.date_rendu IS NULL
GROUP BY l.id_livre, l.auteur, l.titre, l.couverture
ORDER BY l.titre ASC;";

$reqPreparee = $pdo->prepare($sql);
$reqPreparee->execute();

$livres = $reqPreparee->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/nav.php';
?>


<!-- Contenu principal de la page -->
<main class="container mx-auto px-4 py-8 flex-grow" role="main">
    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
            <span class="block sm:inline"><?= $_SESSION['message'] ?></span>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
            <span class="block sm:inline">✅ <?= $_SESSION['success'] ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
            <span class="block sm:inline">❌ <?= $_SESSION['error'] ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>



    <!-- Titre de la page -->
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">
            Catalogue de la Bibliothèque
        </h1>
        <p class="text-gray-600">
            Découvrez notre collection de <?php echo count($livres)  ?> livres
        </p>
    </header>

    <!-- Section des livres -->
    <section aria-label="Liste des livres">
        <!-- Message si aucun livre n'est disponible -->
        <?php if (empty($livres)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucun livre disponible</p>
                <p>La bibliothèque ne contient actuellement aucun livre.</p>
            </div>
        <?php else: ?>
            <!-- Grille de livres -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($livres as $livre): ?>
                    <!-- Carte de livre -->
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Image de couverture du livre -->
                        <div class="h-64 bg-gray-200 flex items-center justify-center relative">
                            <!-- Affichage de la couverture si disponible -->
                            <?php if (!empty($livre['couverture'])): ?>
                                <img
                                    src="<?= $livre['couverture']; ?>"
                                    alt="Couverture de <?= $livre['titre']; ?>"
                                    class="h-full w-full object-cover">
                                <!-- Placeholder si aucune couverture n'est disponible -->
                            <?php else: ?>
                                <span class="text-6xl" aria-hidden="true">📖</span>
                            <?php endif; ?>

                            <!-- Étiquette de statut -->
                            <div class="absolute top-2 right-2">
                                <?php if ($livre['statut'] === 'disponible'): ?>
                                    <!-- Badge vert pour les livres disponibles -->
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold shadow">
                                        <?= $livre['disponible'] ?>/<?= $livre['stock'] ?> dispo
                                    </span>
                                <?php else: ?>
                                    <!-- Badge rouge pour les livres empruntés -->
                                    <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold shadow">
                                        ✗ Complet
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Informations du livre -->
                        <div class="p-4">
                            <!-- Titre du livre -->
                            <h2 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                                <?= htmlspecialchars($livre['titre']); ?>
                            </h2>

                            <!-- Auteur du livre -->
                            <p class="text-gray-600 mb-2">
                                <span class="font-semibold">Auteur :</span>
                                <?= htmlspecialchars($livre['auteur']); ?>
                            </p>

                            <!-- ID du livre -->
                            <p class="text-gray-500 text-sm mb-3">
                                Référence : #<?= $livre['id_livre']; ?>
                            </p>

                            <!-- Bouton d'emprunt (visible seulement pour les abonnés connectés) -->
                            <?php if (isset($_SESSION['abonne_id'])): ?>
                                <?php if ($livre['statut'] === 'disponible'): ?>
                                    <form method="POST" action="emprunter.php" class="w-full">
                                        <input type="hidden" name="livre_id" value="<?= $livre['id_livre'] ?>">
                                        <button type="submit" 
                                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition"
                                                onclick="return confirm('Confirmer l\'emprunt de &quot;<?= htmlspecialchars($livre['titre']) ?>&quot; ?')">
                                            📖 Emprunter
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled 
                                            class="w-full bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed">
                                        ❌ Non disponible
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center">
                                    <a href="login.php" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Connectez-vous pour emprunter
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php
include __DIR__ . '/includes/footer.php'
?>