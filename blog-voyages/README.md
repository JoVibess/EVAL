# Blog Voyages

Un blog de voyages développé avec Symfony :  
- CRUD complet pour les **articles**  
- Interface Web sécurisée (auteurs et admin)  
- Commentaires par utilisateur connecté  
- API REST exposant `GET`, `POST`, `PUT`, `PATCH`, `DELETE`  
- Authentification / autorisation (`ROLE_USER`, `ROLE_ADMIN`)

---

## Prérequis

- PHP 8.1+  
- Composer  
- MySQL (ou MariaDB)  
- Symfony CLI (recommandé)   

---

## Installation et configuration

```bash
# Cloner le dépôt
git clone <url-du-repo>
cd blog-voyages

# Installer les dépendances PHP
composer install

# Créer la base de données
php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures 
# L'admin est déjà crée dasn les fixtures
php bin/console doctrine:fixtures:load

# Lancer le serveur de développement
symfony serve