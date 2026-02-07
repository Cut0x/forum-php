<?php
require __DIR__ . '/includes/bootstrap.php';

$theme = current_theme();
$newTheme = $theme === 'dark' ? 'light' : 'dark';
$_SESSION['theme'] = $newTheme;
$baseUrl = trim((string) ($_ENV['APP_BASE_URL'] ?? ''));
$cookieDomain = '';
if ($baseUrl !== '') {
    $host = parse_url($baseUrl, PHP_URL_HOST);
    if ($host) {
        $cookieDomain = $host;
    }
}
setcookie('theme', $newTheme, [
    'expires' => time() + 365 * 24 * 60 * 60,
    'path' => '/',
    'domain' => $cookieDomain ?: null,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => false,
    'samesite' => 'Lax',
]);

$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $back);
exit;
