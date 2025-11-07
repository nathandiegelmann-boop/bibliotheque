# 📚 Système de Gestion de Bibliothèque

Un système complet de gestion de bibliothèque développé en PHP avec MySQL, offrant une interface d'administration moderne et une expérience utilisateur intuitive.

## 🚀 Fonctionnalités

### Interface Publique
- 📖 Catalogue des livres avec recherche et filtres
- 👤 Consultation des informations des abonnés
- 🔍 Recherche avancée par titre, auteur
- 📱 Design responsive avec Tailwind CSS

### Interface d'Administration
- 🔐 Authentification sécurisée des administrateurs
- 📚 Gestion CRUD complète des livres
- 👥 Gestion des abonnés
- 📊 Tableau de bord avec statistiques
- 🔄 Gestion des emprunts et retours

## 🛠️ Technologies Utilisées

- **Backend:** PHP 8+ avec PDO
- **Base de données:** MySQL
- **Frontend:** HTML5, CSS3, Tailwind CSS
- **JavaScript:** Vanilla JS pour les interactions
- **Architecture:** MVC pattern

## 📋 Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Extension PHP : PDO, PDO_MySQL

## 🔧 Installation

1. **Cloner le repository**
   ```bash
   git clone git@github.com:nathandiegelmann-boop/bibliotheque.git
   cd bibliotheque
   ```

2. **Configuration de la base de données**
   - Importer le fichier `bibliotheque_crud.sql` dans votre base MySQL
   - Copier `config/database.example.php` vers `config/database.php`
   - Modifier les paramètres de connexion dans `config/database.php`

3. **Configuration du serveur web**
   - Pointer le document root vers le dossier du projet
   - S'assurer que les réécritures d'URL sont activées

## 📁 Structure du Projet

```
bibliotheque/
├── config/
│   ├── database.php          # Configuration de la base de données
│   └── database.example.php  # Exemple de configuration
├── includes/
│   ├── header.php            # En-tête commun
│   ├── nav.php               # Navigation
│   └── footer.php            # Pied de page
├── admin/
│   ├── dashboard.php         # Tableau de bord admin
│   ├── livres.php           # Gestion des livres
│   ├── livre_add.php        # Ajout de livre
│   ├── livre_edit.php       # Modification de livre
│   └── livre_delete.php     # Suppression de livre
├── css/                      # Fichiers de style
├── js/                       # Scripts JavaScript
├── images/                   # Images et ressources
├── index.php                 # Page d'accueil
├── login.php                 # Connexion administrateur
└── bibliotheque_crud.sql     # Structure de la base de données
```

## 🔐 Comptes par Défaut

**Administrateur:**
- Email: admin@bibliotheque.fr
- Mot de passe: admin123

## 🎨 Interface

Le système utilise un design moderne avec Tailwind CSS, offrant :
- Interface responsive adaptée à tous les écrans
- Design intuitif et accessible
- Thème professionnel avec palette de couleurs cohérente
- Animations et transitions fluides

## 📊 Base de Données

Le système utilise 4 tables principales :
- `livre` : Gestion des livres
- `abonne` : Gestion des abonnés
- `emprunt` : Suivi des emprunts
- `administrateur` : Comptes administrateurs

## 🔒 Sécurité

- Authentification par session sécurisée
- Protection contre les injections SQL (requêtes préparées)
- Échappement HTML des données affichées
- Validation des données côté serveur
- Protection CSRF sur les formulaires sensibles

## 🚀 Utilisation

1. **Interface Publique:** Accédez à `index.php` pour consulter le catalogue
2. **Administration:** Accédez à `login.php` pour vous connecter en tant qu'administrateur
3. **Gestion:** Utilisez le tableau de bord pour gérer les livres, abonnés et emprunts

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
- Ouvrir des issues pour signaler des bugs
- Proposer des améliorations
- Soumettre des pull requests

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Auteur

**Nathan Diegelmann**
- GitHub: [@nathandiegelmann-boop](https://github.com/nathandiegelmann-boop)

---

📚 **Système de Bibliothèque** - Gestion moderne et efficace de votre bibliothèque