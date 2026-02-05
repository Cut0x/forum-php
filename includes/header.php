<?php
$appName = $config['app']['name'] ?? 'Forum PHP';
$baseUrl = $config['app']['base_url'] ?? '';
$isLogged = is_logged_in();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($appName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo asset('assets/style.css'); ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?php echo e($baseUrl ?: 'index.php'); ?>">
            <i class="bi bi-chat-left-text me-2"></i><?php echo e($appName); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarForum">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarForum">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php">Categories</a></li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($isLogged): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-1"></i>Profil</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4 flex-fill">
