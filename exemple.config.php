<?php
return [
    'app' => [
        'name' => 'Forum PHP',
        'base_url' => 'http://localhost/forum-php',
        'uploads_dir' => __DIR__ . '/uploads',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'forum_php',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'user' => 'user@example.com',
        'pass' => 'password',
        'port' => 587,
        'from' => 'no-reply@example.com',
    ],
    'hcaptcha' => [
        'enabled' => false,
        'site_key' => '',
        'secret' => '',
    ],
];
