<?php
require __DIR__ . '/includes/bootstrap.php';

$term = trim($_GET['q'] ?? '');
$term = normalize_username($term);
if (!$pdo || $term === '' || strlen($term) < 2) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, username, avatar FROM users WHERE username LIKE ? ORDER BY username LIMIT 8');
$stmt->execute([$term . '%']);
$rows = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($rows);
