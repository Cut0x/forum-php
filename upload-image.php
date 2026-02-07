<?php
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'no_file']);
    exit;
}

$file = $_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'upload_failed']);
    exit;
}

$maxSize = 5 * 1024 * 1024;
if (($file['size'] ?? 0) > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'file_too_large']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_type']);
    exit;
}

$uploadsDir = $config['app']['uploads_dir'] ?? (__DIR__ . '/uploads');
$targetDir = rtrim($uploadsDir, '/\\') . DIRECTORY_SEPARATOR . 'images';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$ext = $allowed[$mime];
$filename = 'img_' . current_user_id() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$target = $targetDir . DIRECTORY_SEPARATOR . $filename;

if (!is_uploaded_file($file['tmp_name'] ?? '')) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_upload']);
    exit;
}

if (!move_uploaded_file($file['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode(['error' => 'move_failed']);
    exit;
}

$publicPath = 'uploads/images/' . $filename;
$publicUrl = function_exists('absolute_url') ? absolute_url($publicPath) : $publicPath;
echo json_encode(['url' => $publicUrl]);
