<?php
$root = dirname(__DIR__);
$configPath = $root . '/config.php';

if (!file_exists($configPath)) {
    $config = null;
} else {
    $config = require $configPath;
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Parsedown.php';
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

session_start();

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

if (is_array($config) && isset($config['mail'])) {
    $_ENV['MAIL_ENABLED'] = !empty($config['mail']['enabled']) ? '1' : '0';
    $_ENV['SMTP_HOST'] = $config['mail']['host'] ?? ($_ENV['SMTP_HOST'] ?? '');
    $_ENV['SMTP_USER'] = $config['mail']['user'] ?? ($_ENV['SMTP_USER'] ?? '');
    $_ENV['SMTP_PASS'] = $config['mail']['pass'] ?? ($_ENV['SMTP_PASS'] ?? '');
    $_ENV['SMTP_PORT'] = $config['mail']['port'] ?? ($_ENV['SMTP_PORT'] ?? 587);
    $_ENV['SMTP_FROM'] = $config['mail']['from'] ?? ($_ENV['SMTP_FROM'] ?? '');
}

if (is_array($config) && isset($config['app'])) {
    $_ENV['APP_BASE_URL'] = $config['app']['base_url'] ?? '';
}

if (is_array($config) && isset($config['hcaptcha'])) {
    $_ENV['HCAPTCHA_ENABLED'] = !empty($config['hcaptcha']['enabled']) ? '1' : '0';
    $_ENV['HCAPTCHA_SITE'] = $config['hcaptcha']['site_key'] ?? '';
    $_ENV['HCAPTCHA_SECRET'] = $config['hcaptcha']['secret'] ?? '';
}

if (isset($_SESSION['user_id']) && $pdo) {
    $hasName = column_exists($pdo, 'users', 'name');
    if ($hasName) {
        $stmt = $pdo->prepare('SELECT role, username, name FROM users WHERE id = ?');
    } else {
        $stmt = $pdo->prepare('SELECT role, username FROM users WHERE id = ?');
    }
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $_SESSION['role'] = $row['role'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['name'] = $row['name'] ?? $row['username'];
    }
}

if ($pdo) {
    ensure_defaults($pdo);
}
