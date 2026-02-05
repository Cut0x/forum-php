<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$content = trim($_POST['content'] ?? '');
if ($content === '') {
    echo '';
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
if ($pdo) {
    echo render_markdown_with_mentions($pdo, $content);
} else {
    echo render_markdown($content);
}
