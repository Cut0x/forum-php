<?php
require __DIR__ . '/includes/bootstrap.php';

$theme = current_theme();
$_SESSION['theme'] = $theme === 'dark' ? 'light' : 'dark';

$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $back);
exit;
