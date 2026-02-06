<?php
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (!$pdo) {
    echo json_encode([]);
    exit;
}

try {
    $rows = $pdo->query('SELECT name, file, title FROM emotes WHERE is_enabled = 1 ORDER BY name')->fetchAll();
} catch (Throwable $e) {
    echo json_encode([]);
    exit;
}

echo json_encode($rows);
