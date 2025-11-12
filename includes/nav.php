  <?php if (session_status() == PHP_SESSION_NONE) { session_start(); } ?>
  <!-- Navigation principale -->
    <nav class="bg-blue-600 text-white shadow-lg" role="navigation" aria-label="Navigation principale">
          <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-4">
                      <!-- Logo et titre -->
                      <div class="flex items-center">
                            <a href="/bibliotheque/index.php" class="text-2xl font-bold hover:text-blue-200 transition">
                                  📚 Bibliothèque
                            </a>
                      </div>

                      <!-- Menu de navigation -->
                      <ul class="flex items-center space-x-6">
                            <!-- Lien vers l'accueil -->
                            <li>
                                  <a href="/bibliotheque/index.php" class="hover:text-blue-200 transition font-medium">
                                        Accueil
                                  </a>
                            </li>

                            <!-- Liens visibles uniquement pour les administrateurs connectés -->
                             <?php if (isset($_SESSION['admin_id'])): ?>
                            <li>
                                  <a href="/bibliotheque/admin/dashboard.php" class="hover:text-blue-200 transition font-medium">
                                        Tableau de bord
                                  </a>
                            </li>
                            <li>
                                  <a href="/bibliotheque/admin/livres.php" class="hover:text-blue-200 transition font-medium">
                                        Gérer les livres
                                  </a>
                            </li>
                            <li>
                                  <a href="/bibliotheque/admin/abonnes.php" class="hover:text-blue-200 transition font-medium">
                                        Gérer les abonnés
                                  </a>
                            </li>
                            <li>
                                  <a href="/bibliotheque/admin/emprunts.php" class="hover:text-blue-200 transition font-medium">
                                        Gérer les emprunts
                                  </a>
                            </li>
                            <li>
                                  <span class="text-blue-200">
                                        Bonjour, Jean-Michel
                                  </span>
                            </li>
                            <li>
                                  <!-- Bouton de déconnexion admin -->
                                  <a href="/bibliotheque/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition font-medium">
                                        Déconnexion
                                  </a>
                            </li>
                            <!-- Liens visibles uniquement pour les abonnés connectés -->
                             <?php elseif (isset($_SESSION['abonne_id'])): ?>
                            <li>
                                  <a href="/bibliotheque/profile_abonne.php" class="hover:text-blue-200 transition font-medium">
                                        👤 Mon Profil
                                  </a>
                            </li>
                            <li>
                                  <span class="text-blue-200">
                                        Bonjour, <?= htmlspecialchars($_SESSION['abonne_prenom']) ?>
                                  </span>
                            </li>
                            <li>
                                  <!-- Bouton de déconnexion abonné -->
                                  <a href="/bibliotheque/logout_abonne.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition font-medium">
                                        Déconnexion
                                  </a>
                            </li>
                            <!-- Liens visibles uniquement pour les visiteurs non connectés -->
                             <?php else: ?>
                            <li>
                                  <a href="/bibliotheque/admin/signup.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded transition font-medium">
                                        📝 Inscription
                                  </a>
                            </li>
                            <li>
                                  <a href="/bibliotheque/login.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded transition font-medium">
                                        🔑 Connexion
                                  </a>
                            </li>
                            <?php endif; ?>
                      </ul>
                </div>
          </div>
    </nav>