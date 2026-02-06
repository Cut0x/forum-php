# Forum PHP

Template de forum PHP + MySQL (PDO) avec Bootstrap.

## Sommaire
1. Prérequis
2. Installation rapide
3. Configuration
4. Configuration Nginx (VPS)
5. Configuration Apache (VPS)
6. Importer la base de données
7. Créer le compte admin
8. Sécurité et production
9. Dépannage

## 1. Prérequis
- PHP 8.1+
- MySQL 8 ou MariaDB 10.4+
- Extensions PHP: pdo_mysql, mbstring, openssl, fileinfo
- Serveur web: Nginx ou Apache

## 2. Installation rapide
1. Copier le projet dans votre serveur web.
2. Copier `exemple.config.php` vers `config.php`.
3. Importer `schema.sql` dans votre base.
4. Ouvrir le site et créer l'admin via `setup-admin.php`.

## 3. Configuration
Editez `config.php`.

Exemple:
```php
<?php
return [
    'app' => [
        'name' => 'Forum PHP',
        'base_url' => 'https://votre-domaine.tld',
        'uploads_dir' => __DIR__ . '/uploads',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'forum_php',
        'user' => 'db_user',
        'pass' => 'db_pass',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'user' => 'user@example.com',
        'pass' => 'password',
        'port' => 587,
        'from' => 'no-reply@example.com',
    ],
    'hcaptcha' => [
        'enabled' => false,
        'site_key' => '',
        'secret' => '',
    ],
];
```

## 4. Configuration Nginx (VPS)
Exemple de vhost:
```
server {
    listen 80;
    server_name votre-domaine.tld;

    root /var/www/forum-php;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~* \.(jpg|jpeg|png|gif|svg|css|js)$ {
        expires 7d;
    }
}
```

## 5. Configuration Apache (VPS)
Assurez-vous que `mod_rewrite` est actif.

Exemple de vhost:
```
<VirtualHost *:80>
    ServerName votre-domaine.tld
    DocumentRoot /var/www/forum-php

    <Directory /var/www/forum-php>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
</VirtualHost>
```

## 6. Importer la base de données
1. Créez la base et importez `schema.sql`.
2. Vérifiez que les tables sont présentes.

## 7. Créer le compte admin
Ouvrez `setup-admin.php` une seule fois.
Une fois créé, supprimez ou bloquez ce fichier.

## 8. Sécurité et production
- Désactivez l'affichage des erreurs en production.
- Configurez HTTPS.
- Vérifiez les permissions du dossier `uploads/`.

## 9. Dépannage
- Erreur BDD: vérifiez `config.php`.
- Upload avatar: vérifiez l'extension PHP GD.
- Emails: activez `mail.enabled` et installez PHPMailer.
