-- Script SQL pour ajouter la colonne mot_de_passe à la table abonne
-- À exécuter dans phpMyAdmin

-- 1. Ajouter la colonne mot_de_passe (si elle n'existe pas)
ALTER TABLE `abonne` ADD COLUMN `mot_de_passe` VARCHAR(255) NULL AFTER `email`;

-- 2. Vérifier la structure de la table
DESCRIBE `abonne`;

-- 3. Pour les abonnés existants sans mot de passe, vous pouvez :
-- Option A : Leur mettre un mot de passe par défaut (non recommandé)
-- UPDATE `abonne` SET `mot_de_passe` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE `mot_de_passe` IS NULL;
-- (ce hash correspond au mot de passe "motdepasse123")

-- Option B : Les forcer à créer un nouveau compte avec mot de passe
-- (recommandé pour la sécurité)