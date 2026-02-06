<?php
header('Content-Type: text/plain; charset=UTF-8');

$root = __DIR__;
$configPath = $root . '/config.php';
if (!file_exists($configPath)) {
    echo "config.php introuvable.\n";
    exit(1);
}

$config = require $configPath;
if (!is_array($config) || empty($config['db'])) {
    echo "Configuration BDD invalide.\n";
    exit(1);
}

$db = $config['db'];
$host = $db['host'] ?? 'localhost';
$name = $db['name'] ?? '';
$user = $db['user'] ?? '';
$pass = $db['pass'] ?? '';
$charset = $db['charset'] ?? 'utf8mb4';
$collation = $db['collation'] ?? 'utf8mb4_unicode_ci';

if ($name === '' || $user === '') {
    echo "Configuration BDD incomplète.\n";
    exit(1);
}

try {
    $pdo = new PDO("mysql:host={$host};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Throwable $e) {
    echo "Connexion BDD impossible: " . $e->getMessage() . "\n";
    exit(1);
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET {$charset} COLLATE {$collation}");
$pdo->exec("USE `{$name}`");

$tables = [
    'CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT \'member\',
        bio TEXT NULL,
        avatar VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        description VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_readonly TINYINT NOT NULL DEFAULT 0,
        is_pinned TINYINT NOT NULL DEFAULT 0
    )',
    'CREATE TABLE IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(180) NOT NULL,
        edited_at DATETIME NULL,
        locked_at DATETIME NULL,
        deleted_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        edited_at DATETIME NULL,
        deleted_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        code VARCHAR(40) NOT NULL UNIQUE,
        icon VARCHAR(255) NOT NULL,
        color VARCHAR(20) NOT NULL DEFAULT \'#0d6efd\'
    )',
    'CREATE TABLE IF NOT EXISTS user_badges (
        user_id INT NOT NULL,
        badge_id INT NOT NULL,
        PRIMARY KEY (user_id, badge_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS post_votes (
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        value TINYINT NOT NULL,
        PRIMARY KEY (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(80) PRIMARY KEY,
        value TEXT NOT NULL
    )',
    'CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        actor_id INT NULL,
        type VARCHAR(30) NOT NULL,
        message VARCHAR(255) NOT NULL,
        topic_id INT NULL,
        post_id INT NULL,
        is_read TINYINT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
    )',
    'CREATE TABLE IF NOT EXISTS user_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        label VARCHAR(80) NOT NULL,
        url VARCHAR(255) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS footer_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0
    )',
    'CREATE TABLE IF NOT EXISTS footer_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        label VARCHAR(80) NOT NULL,
        url VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (category_id) REFERENCES footer_categories(id) ON DELETE CASCADE
    )',
    'CREATE TABLE IF NOT EXISTS emotes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        file VARCHAR(255) NOT NULL,
        title VARCHAR(80) NULL,
        is_enabled TINYINT NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
}

require_once __DIR__ . '/includes/functions.php';
ensure_defaults($pdo);

echo "Migration terminée.\n";
