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
    edited_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    code VARCHAR(40) NOT NULL UNIQUE,
    icon VARCHAR(255) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#0d6efd'
);

CREATE TABLE user_badges (
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    PRIMARY KEY (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

CREATE TABLE post_votes (
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    value TINYINT NOT NULL,
    PRIMARY KEY (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE user_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(80) NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO categories (name, description, sort_order) VALUES
('Annonces', 'Nouveautes et mises a jour.', 1),
('Support', 'Questions et aide technique.', 2),
('Discussions', 'Sujets libres.', 3);

INSERT INTO badges (name, code, icon, color) VALUES
('Premier message', 'starter', 'assets/badges/starter.png', '#4f8cff'),
('10 messages', 'writer', 'assets/badges/writer.png', '#00d1b2'),
('25 messages', 'speaker', 'assets/badges/speaker.png', '#ffb020'),
('50 messages', 'veteran', 'assets/badges/veteran.png', '#7c5cff'),
('Premier sujet', 'first_topic', 'assets/badges/founder.png', '#ff4d4f'),
('10 sujets', 'topics_10', 'assets/badges/founder.png', '#ff4d4f');

INSERT INTO topics (category_id, user_id, title) VALUES
(1, 1, 'Bienvenue sur le forum'),
(2, 1, 'Comment configurer le projet ?');