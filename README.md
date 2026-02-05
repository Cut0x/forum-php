# Forum PHP

Template de forum en PHP + MySQL et avec Bootstrap 5.

## Fonctionnalites
- Categories et sujets
- Profils utilisateurs (bio + liens)
- Photo de profil
- Systeme de badges

## Installation rapide
1. Copier `exemple.config.php` vers `config.php` et adapter les valeurs.
2. Importer `schema.sql` dans votre base MySQL.
3. Ouvrir `http://localhost/forum-php` (si vous utilisez XAMPP).

## Arborescence
- `index.php` page d'accueil
- `category.php` liste des sujets d'une categorie
- `topic.php` lecture d'un sujet
- `profile.php` profil utilisateur

## Notes
Les pages de formulaire (connexion, inscription, nouveau sujet) sont des templates a brancher avec votre logique.
