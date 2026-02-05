CREATE DATABASE IF NOT EXISTS forum_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_php;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'member',
    bio TEXT NULL,
    avatar VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#0d6efd'
);

CREATE TABLE user_badges (
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    PRIMARY KEY (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

CREATE TABLE user_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(80) NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (username, email, password_hash, role, bio, avatar)
VALUES ('admin', 'admin@example.com', '$2y$10$1fhHskges6WOwXtC5nDUcui2k07hvQzcnyZoQZooXiz51Rswegjfa', 'admin', 'Developpeur et mainteneur du forum.', NULL);

INSERT INTO categories (name, description, sort_order) VALUES
('Annonces', 'Nouveautes et mises a jour.', 1),
('Support', 'Questions et aide technique.', 2),
('Discussions', 'Sujets libres.', 3);

INSERT INTO badges (name, color) VALUES
('Fondateur', '#0d6efd'),
('Contributeur', '#198754');

INSERT INTO user_badges (user_id, badge_id) VALUES (1, 1), (1, 2);

INSERT INTO user_links (user_id, label, url) VALUES
(1, 'GitHub', 'https://github.com/'),
(1, 'Portfolio', 'https://example.com');

INSERT INTO topics (category_id, user_id, title) VALUES
(1, 1, 'Bienvenue sur le forum'),
(2, 1, 'Comment configurer le projet ?');

INSERT INTO posts (topic_id, user_id, content) VALUES
(1, 1, 'Ravi de vous accueillir sur ce forum open-source.'),
(1, 1, 'N''hesitez pas a poser vos questions.');
