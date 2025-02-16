-- Création de la base de données
CREATE DATABASE IF NOT EXISTS projetb2 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Création de l'utilisateur et attribution des droits
CREATE USER IF NOT EXISTS 'projetb2'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON projetb2.* TO 'projetb2'@'localhost';
FLUSH PRIVILEGES;

-- Utilisation de la base de données
USE projetb2;

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    remember_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des réinitialisations de mot de passe
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des catégories de projets
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des tags
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des compétences
CREATE TABLE skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des projets
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    external_link VARCHAR(255),
    user_id INT,
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison projets-tags
CREATE TABLE project_tags (
    project_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (project_id, tag_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison utilisateurs-compétences avec niveau
CREATE TABLE user_skills (
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    level ENUM('debutant', 'intermediaire', 'avance', 'expert') NOT NULL DEFAULT 'debutant',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, skill_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des commentaires
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les recherches
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_remember_token ON users(remember_token);
CREATE INDEX idx_reset_token ON users(reset_token);

-- Insertion des utilisateurs de test avec des mots de passe hachés avec password_hash()
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password
('jean.dupont', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'), -- password
('marie.martin', 'marie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); -- password

-- Insertion des compétences de test
INSERT INTO skills (name, description) VALUES
('PHP', 'Langage de programmation côté serveur'),
('JavaScript', 'Langage de programmation côté client'),
('HTML/CSS', 'Langages de structure et de style pour le web'),
('MySQL', 'Système de gestion de base de données'),
('Git', 'Système de contrôle de version'),
('React', 'Bibliothèque JavaScript pour créer des interfaces utilisateur'),
('Node.js', 'Environnement d''exécution JavaScript côté serveur'),
('Python', 'Langage de programmation polyvalent'),
('Docker', 'Plateforme de conteneurisation'),
('AWS', 'Services cloud Amazon Web Services');

-- Attribution des compétences aux utilisateurs
INSERT INTO user_skills (user_id, skill_id, level) VALUES
-- Compétences de l'admin
(1, 1, 'expert'),      -- PHP
(1, 2, 'expert'),      -- JavaScript
(1, 3, 'expert'),      -- HTML/CSS
(1, 4, 'expert'),      -- MySQL
(1, 5, 'avance'),      -- Git
-- Compétences de Jean Dupont
(2, 1, 'intermediaire'), -- PHP
(2, 2, 'avance'),       -- JavaScript
(2, 3, 'expert'),       -- HTML/CSS
(2, 6, 'avance'),       -- React
(2, 7, 'intermediaire'), -- Node.js
-- Compétences de Marie Martin
(3, 8, 'expert'),       -- Python
(3, 9, 'avance'),       -- Docker
(3, 10, 'intermediaire'), -- AWS
(3, 5, 'expert'),       -- Git
(3, 4, 'avance');       -- MySQL

-- Insertion des catégories de test
INSERT INTO categories (name, description) VALUES
('Web', 'Applications et sites web'),
('Mobile', 'Applications mobiles iOS et Android'),
('Desktop', 'Applications de bureau'),
('API', 'Services web et APIs'),
('DevOps', 'Infrastructure et déploiement');

-- Insertion des tags de test
INSERT INTO tags (name) VALUES
('PHP'),
('JavaScript'),
('Python'),
('React'),
('Vue.js'),
('Laravel'),
('Node.js'),
('Docker'),
('AWS'),
('MySQL'),
('MongoDB'),
('REST API'),
('GraphQL'),
('Bootstrap'),
('Material-UI');

-- Insertion des projets de test
INSERT INTO projects (title, description, external_link, user_id, category_id) VALUES
-- Projets de l'admin
('Portfolio Administration', 'Système d''administration complet avec gestion des utilisateurs et des droits', 'https://admin-portfolio.example.com', 1, 1),
('Dashboard Analytics', 'Tableau de bord pour le suivi des métriques en temps réel', 'https://analytics.example.com', 1, 1),
('API Gateway', 'Passerelle API sécurisée avec authentification JWT', 'https://api.example.com', 1, 4),
-- Projets de Jean Dupont
('Application React', 'Application web moderne utilisant React et Material-UI', 'https://react-app.example.com', 2, 1),
('Site E-commerce', 'Boutique en ligne avec panier et paiement intégré', 'https://shop.example.com', 2, 1),
('Blog Personnel', 'Blog développé avec Node.js et Express', 'https://blog.example.com', 2, 1),
-- Projets de Marie Martin
('Microservices Python', 'Architecture de microservices avec Python et Docker', 'https://microservices.example.com', 3, 4),
('Cloud Dashboard', 'Interface de gestion des services AWS', 'https://cloud.example.com', 3, 5),
('DevOps Pipeline', 'Pipeline CI/CD automatisé avec Jenkins et Docker', 'https://devops.example.com', 3, 5);

-- Association des tags aux projets
INSERT INTO project_tags (project_id, tag_id) VALUES
(1, 1), -- Portfolio Admin: PHP
(1, 10), -- Portfolio Admin: MySQL
(1, 14), -- Portfolio Admin: Bootstrap
(2, 2), -- Dashboard: JavaScript
(2, 4), -- Dashboard: React
(2, 15), -- Dashboard: Material-UI
(3, 12), -- API Gateway: REST API
(3, 13), -- API Gateway: GraphQL
(3, 7), -- API Gateway: Node.js
(4, 4), -- Application React: React
(4, 15), -- Application React: Material-UI
(4, 2), -- Application React: JavaScript
(5, 1), -- E-commerce: PHP
(5, 6), -- E-commerce: Laravel
(5, 10), -- E-commerce: MySQL
(6, 7), -- Blog: Node.js
(6, 11), -- Blog: MongoDB
(6, 14), -- Blog: Bootstrap
(7, 3), -- Microservices: Python
(7, 8), -- Microservices: Docker
(8, 9), -- Cloud Dashboard: AWS
(8, 4), -- Cloud Dashboard: React
(9, 8), -- DevOps Pipeline: Docker
(9, 9); -- DevOps Pipeline: AWS

-- Insertion de quelques commentaires de test
INSERT INTO comments (project_id, user_id, content, status) VALUES
(1, 2, 'Super projet ! J''aime particulièrement l''interface d''administration.', 'approved'),
(1, 3, 'Très bien structuré et facile à utiliser.', 'approved'),
(2, 2, 'Le dashboard est vraiment intuitif.', 'approved'),
(3, 3, 'Documentation API très claire.', 'pending'),
(4, 1, 'Belle utilisation de React et Material-UI !', 'approved'),
(5, 3, 'La fonction de panier est très bien implémentée.', 'approved'),
(6, 2, 'Le design est superbe !', 'pending'),
(7, 1, 'Architecture microservices très bien pensée.', 'approved'),
(8, 2, 'Interface cloud très intuitive.', 'approved'),
(9, 1, 'Pipeline CI/CD très robuste.', 'approved');
