<?php
require __DIR__ . '/includes/bootstrap.php';

function is_protected_path(string $path): bool
{
    $base = basename($path);
    return in_array($base, ['admin.php', 'notifications.php', 'new-topic.php'], true);
}

$redirect = sanitize_redirect($_GET['redirect_to'] ?? '') ?? '';
if ($redirect === '') {
    $redirect = sanitize_redirect($_SERVER['HTTP_REFERER'] ?? '') ?? '';
}

$redirectPath = $redirect ? (parse_url($redirect, PHP_URL_PATH) ?? '') : '';
if ($redirect && $redirectPath && is_protected_path($redirectPath)) {
    $redirect = '';
}
if ($redirect === '') {
    $redirect = 'index.php';
}

session_unset();
session_destroy();

header('Location: ' . $redirect);
exit;
