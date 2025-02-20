# DevShowcase - Plateforme de Gestion de Projets et Compétences

## Présentation du Projet
DevShowcase est une application web développée en PHP & MySQL permettant aux utilisateurs de :
- [x] Gérer leur profil (inscription, connexion, mise à jour des informations).
- [x] Ajouter et modifier leurs compétences parmi celles définies par un administrateur.
- [x] Ajouter et gérer leurs projets (titre, description, image et lien).
- [x] Un administrateur peut gérer les compétences, tags, et catégories disponibles.

## Fonctionnalités Implémentées

### Authentification & Gestion des Comptes
- [x] Inscription avec validation des champs
- [] Connexion sécurisée avec sessions et option "Se souvenir de moi"
- [x] Gestion des rôles (Admin / Utilisateur)
- [x] Mise à jour des informations utilisateur
- [] Réinitialisation du mot de passe
- [x] Déconnexion sécurisée

### Gestion des Compétences
- [x] L'administrateur peut gérer les compétences proposées
- [x] Un utilisateur peut sélectionner ses compétences parmi celles disponibles
- [x] Niveau de compétence défini sur une échelle (débutant → expert)
- [x] Interface intuitive pour la gestion des compétences

### Gestion des Projets
- [x] Ajout, modification et suppression de projets
- [x] Chaque projet contient : Titre, Description, Image, Lien externe
- [x] Upload sécurisé des images avec restrictions de format et taille
- [x] Système de tags et catégories pour organiser les projets
- [x] Système de commentaires avec modération
- [x] Affichage structuré des projets

### Administration
- [x] Panneau d'administration complet
- [x] Gestion des compétences
- [x] Gestion des tags
- [x] Gestion des catégories
- [x] Modération des commentaires
- [x] Statistiques du site

### Sécurité
- [x] Protection contre XSS, CSRF et injections SQL
- [x] Hachage sécurisé des mots de passe
- [x] Gestion des erreurs utilisateur avec affichage des messages et conservation des champs remplis
- [x] Expiration automatique de la session après inactivité

## Installation et Configuration

### Prérequis
- Serveur local (XAMPP, WAMP, etc.)
- PHP 8.x et MySQL
- Un navigateur moderne

### Étapes d'Installation
1. Cloner le projet sur votre serveur local :
   ```sh
   git clone https://github.com/votre-repo/portfolio.git
   cd portfolio
   ```

2. Importer la base de données :
   - Créer une base de données nommée `projetb2`
   - Importer le fichier `config/database.sql`

3. Configurer la connexion à la base de données :
   Modifier le fichier `config/database.php` avec vos informations :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'projetb2');
   define('DB_USER', 'projetb2');
   define('DB_PASS', 'password');
   define('DB_PORT', 3306);
   ```

4. Démarrer le serveur :
   ```sh
   ./start.bat
   ```
   Ou manuellement :
   ```sh
   php -S localhost:8000
   ```
   Puis accéder à l'application via `http://localhost:8000`

## Comptes de Test

### Compte Administrateur
- **Email** : admin@example.com
- **Mot de passe** : password

### Comptes Utilisateurs
- **Email** : jean.dupont@example.com
- **Mot de passe** : password

- **Email** : marie.martin@example.com
- **Mot de passe** : password

## Structure du Projet
```
/
├── admin/              # Pages d'administration
├── classes/            # Classes PHP (Auth, Project, Skill, etc.)
├── config/             # Configuration (BDD, etc.)
├── includes/           # Éléments communs (header, footer)
├── public/            
│   ├── css/           # Styles CSS
│   └── uploads/       # Images uploadées
└── uploads/           # Dossier pour les uploads de projets
```

## Technologies Utilisées
- **Backend** : PHP 8.x, MySQL
- **Frontend** : HTML5, CSS3, JavaScript, Bootstrap 5
- **Sécurité** : 
  - Protection CSRF
  - Hachage des mots de passe avec Bcrypt
  - Validation des entrées
  - Sessions sécurisées
- **Bibliothèques** : 
  - Font Awesome 
  - Bootstrap 5 (framework CSS)

## Fonctionnalités Principales
- Interface responsive et moderne
- Système complet de gestion des projets
- Gestion des compétences avec niveaux
- Système de tags et catégories
- Modération des commentaires
- Statistiques détaillées
- Upload d'images sécurisé
- Interface d'administration intuitive

## Licence
Ce projet est sous licence MIT.

## Contact
Une question ou un bug ? contactez moi sur mon email :  eliott.pro2004@gmail.com
Eliott Bellais
