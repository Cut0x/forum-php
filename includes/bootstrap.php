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

session_start();
