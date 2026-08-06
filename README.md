# Forum PHP

Forum communautaire en Laravel + Tailwind CSS + Alpine.js, avec panel d'administration et panel de modération séparés.

Auteur et mainteneur : **Loic VALENCE** ([valloic.fr](https://valloic.fr))

## Documentation complète

Ce README couvre une installation locale rapide. Pour un guide complet (prérequis détaillés, installation locale pas à pas, mise en production avec Apache, mise en production avec Nginx, dépannage), consultez le wiki du projet :

https://github.com/Cut0x/forum-php/wiki

## Sommaire
1. Prérequis
2. Installation
3. Configuration
4. Compte administrateur
5. Configuration Apache/XAMPP (production locale)
6. Fonctionnalités
7. Tests
8. Licence

## 1. Prérequis
- PHP 8.3+ avec extensions : pdo_mysql, mbstring, openssl, fileinfo, gd
- Composer 2
- Node.js 18+ et npm
- MySQL 8 ou MariaDB 10.4+

## 2. Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Éditez `.env` et renseignez vos identifiants MySQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), puis créez la base :

```bash
mysql -u root -e "CREATE DATABASE forum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Migrations, données de base et lien de stockage :

```bash
php artisan migrate --seed
php artisan storage:link
```

Le seeder par défaut crée les catégories, badges et réglages de base (sans contenu de démonstration). Pour peupler le forum avec des utilisateurs/sujets/messages factices en local :

```bash
php artisan db:seed --class=DemoContentSeeder
```

Build des assets (Tailwind + Alpine via Vite) :

```bash
npm run build   # une fois
npm run dev     # en développement, avec hot-reload
```

Lancer le serveur de développement :

```bash
php artisan serve
```

Le site est alors accessible sur `http://127.0.0.1:8000`.

## 3. Configuration

Toute la configuration applicative (hors thème) passe par `.env`. Les réglages éditables depuis l'admin (titre du site, couleurs, police, footer, lien de soutien…) sont stockés en base et gérés dans `/admin`.

- `APP_URL` doit correspondre à l'URL réellement utilisée (impacte les liens absolus, l'upload d'images, etc.).
- `MAIL_MAILER=log` par défaut : les emails (notifications, avertissements) sont écrits dans `storage/logs/laravel.log`. Configurez un vrai transport SMTP en production.

## 4. Compte administrateur

Créez ou promouvez un compte administrateur avec :

```bash
php artisan app:create-admin
```

La commande demande un email : si le compte existe déjà, il est promu admin ; sinon elle crée le compte.

## 5. Configuration Apache/XAMPP (production locale)

Laravel sert l'application depuis le dossier `public/`, ne pointez jamais un vhost directement sur la racine du projet. Deux options :

**Option recommandée : vhost dédié**

```apache
<VirtualHost *:80>
    ServerName forum.local
    DocumentRoot "C:/Users/loic/Documents/Xampp/htdocs/forum-php/public"

    <Directory "C:/Users/loic/Documents/Xampp/htdocs/forum-php/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Ajoutez `127.0.0.1 forum.local` à votre fichier hosts, puis réglez `APP_URL=http://forum.local` dans `.env`.

**Option rapide (dev uniquement) :** `php artisan serve` (voir section 2), pas de configuration Apache nécessaire.

Pour un déploiement en production réel (serveur Linux, Apache ou Nginx, HTTPS, file d'attente, cron, permissions), suivez le guide dédié du wiki plutôt que cette section, qui ne couvre que l'usage local sous XAMPP.

## 6. Fonctionnalités

- Mise en page façon Reddit : sidebar de navigation/catégories, fil de sujets en cartes avec flèches de vote, sidebar contextuelle (infos catégorie/sujet).
- Catégories, sujets et messages en Markdown (mentions `@pseudo`, émotes `:nom:`, images), **vote +1/-1 sur les sujets et sur les messages**, notifications.
- **Réponses en fil de discussion** : chaque message peut être répondu individuellement (indentation + ligne de connexion vers le parent), avec pré-remplissage automatique de la mention `@pseudo` du message parent — ce qui déclenche automatiquement une notification à son auteur.
- Profils (avatar, bio, liens), paramètres de compte (email, mot de passe, export de données, suppression de compte).
- **Badges à règles configurables** : chaque badge peut être manuel, ou attribué automatiquement selon un seuil de messages/sujets, l'ancienneté du compte, ou le rôle — le tout depuis `/admin/badges` (voir le wiki, page [Badges](https://github.com/Cut0x/forum-php/wiki/Badges)).
- **Panel admin** (`/admin`, rôle `admin`) : réglages du site, thème (couleurs clair/sombre, police, logo de navigation, favicon, logo de pied de page, presets), catégories, footer, badges (icône, règle d'attribution), émotes, gestion des rôles.
- **Panel de modération** (`/moderation`, rôles `moderator` et `admin`) : file de signalements, verrouillage/épinglage/déplacement de sujets, suppression de messages, avertissements et suspensions temporaires d'utilisateurs, journal des actions.

## 7. Tests

```bash
php artisan test
```

## 8. Licence

Ce projet est distribué sous licence **Common Public Attribution License 1.0 (CPAL-1.0)**, disponible dans le fichier [LICENSE](LICENSE) et en ligne sur https://opensource.org/license/cpal-1-0.

La CPAL est une licence open source. Elle impose en particulier une clause d'attribution (article 14) : toute utilisation, modification ou mise à disposition du logiciel, y compris en tant que service accessible sur un réseau, doit conserver visible sur l'interface l'information d'attribution suivante, définie dans le fichier [LICENSE](LICENSE) (Exhibit B) et rappelée dans [NOTICE](NOTICE) :

> Forum PHP, par Loic VALENCE ([valloic.fr](https://valloic.fr))

Cette attribution est affichée dans le pied de page de chaque page de l'application (forum public, panel admin, panel de modération, pages d'authentification). Elle ne doit pas être retirée, masquée ou modifiée dans les versions dérivées ou déployées de ce projet.

Copyright (c) 2026 Loic VALENCE. Tous droits réservés.
