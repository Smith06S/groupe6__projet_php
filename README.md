# Projet final PHP — Site E-Commerce

Projet réalisé en **PHP** dans le cadre du module PHP.

## Prérequis

- [XAMPP](https://www.apachefriends.org/fr/index.html)
- Un navigateur web

## Installation Linux (LAMP)

- Ubuntu/Debian : `sudo apt-get install lamp-server^`
- Arch/Manjaro : `sudo pacman -S httpd php php-apache mysql phpmyadmin`

## Installation et lancement (Windows + XAMPP)

1. Télécharger et installer XAMPP depuis le site officiel.
2. Ouvrir **XAMPP Control Panel**.
3. Démarrer les services :
   - **Apache**
   - **MySQL**
4. Vérifier que les deux services sont en vert.

### 1) Placer le projet au bon endroit

Le projet doit être dans le dossier `htdocs/php_exam` de XAMPP.

Chemin attendu :

`C:\xampp\htdocs\php_exam\groupe6__projet_php`

### 2) Ouvrir le projet dans le navigateur

Quand Apache est démarré, ouvrir :

- `http://localhost/php_exam/groupe6__projet_php/Vue/products/`

### 3) Voir la base de données dans phpMyAdmin

1. Ouvrir phpMyAdmin :
   - `http://localhost/phpmyadmin`
2. Si demandé :
   - **Utilisateur** : `root`
   - **Mot de passe** : vide (`""`) sur cette configuration
3. Créer la base de données :
   - Nom : `php_exam_db`

### 4) Importer la base SQL

Le projet doit contenir un fichier SQL d’export (ex: `php_exam_db.sql`).

Pour importer :

1. Dans phpMyAdmin, cliquer sur la base `php_exam_db`.
2. Onglet **Importer**.
3. Choisir le fichier `php_exam_db.sql`.
4. Cliquer **Exécuter**.

### 5) Vérifier la connexion PHP ↔ MySQL

La connexion actuelle est définie dans `db/Database.php` :

- Host : `localhost`
- User : `root`
- Password : *(vide)*
- Database : `php_exam_db`

Si MySQL chez vous a un mot de passe `root`, modifiez `$pass` dans `db/Database.php`.

## 7) Pages attendues du projet

- `/login` : connexion
- `/register` : inscription
- `/` : home
- `/sell` : mise en vente
- `/detail` : détail produit
- `/cart` : panier
- `/cart/validate` : validation panier
- `/edit` : édition/suppression produit (propriétaire ou admin)
- `/account` : profil, articles, achats/factures
- `/admin` : gestion admin des utilisateurs/articles
