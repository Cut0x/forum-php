<?php
$appName = $config['app']['name'] ?? 'Forum PHP';
$baseUrl = $config['app']['base_url'] ?? '';
$isLogged = is_logged_in();
$username = current_username();
$displayName = current_user_name() ?? $username;
$role = current_user_role();
$theme = current_theme();
$notifCount = 0;
if ($pdo && $isLogged) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([current_user_id()]);
    $notifCount = (int) $stmt->fetchColumn();
    $appName = get_setting($pdo, 'site_title', $appName) ?? $appName;
}
$themeVars = [
    'light_bg' => $pdo ? get_setting($pdo, 'theme_light_bg', '#f1f5f9') : '#f1f5f9',
    'light_surface' => $pdo ? get_setting($pdo, 'theme_light_surface', '#ffffff') : '#ffffff',
    'light_text' => $pdo ? get_setting($pdo, 'theme_light_text', '#0f172a') : '#0f172a',
    'light_muted' => $pdo ? get_setting($pdo, 'theme_light_muted', '#64748b') : '#64748b',
    'light_primary' => $pdo ? get_setting($pdo, 'theme_light_primary', '#4f8cff') : '#4f8cff',
    'light_accent' => $pdo ? get_setting($pdo, 'theme_light_accent', '#00d1b2') : '#00d1b2',
    'dark_bg' => $pdo ? get_setting($pdo, 'theme_dark_bg', '#0b1220') : '#0b1220',
    'dark_surface' => $pdo ? get_setting($pdo, 'theme_dark_surface', '#0f172a') : '#0f172a',
    'dark_text' => $pdo ? get_setting($pdo, 'theme_dark_text', '#e2e8f0') : '#e2e8f0',
    'dark_muted' => $pdo ? get_setting($pdo, 'theme_dark_muted', '#94a3b8') : '#94a3b8',
    'dark_primary' => $pdo ? get_setting($pdo, 'theme_dark_primary', '#4f8cff') : '#4f8cff',
    'dark_accent' => $pdo ? get_setting($pdo, 'theme_dark_accent', '#00d1b2') : '#00d1b2',
    'font' => $pdo ? get_setting($pdo, 'theme_font', '\"Inter\", system-ui, sans-serif') : '\"Inter\", system-ui, sans-serif',
    'version' => $pdo ? get_setting($pdo, 'theme_version', '1') : '1',
];
$siteDescription = $pdo ? get_setting($pdo, 'site_description', 'Forum communautaire.') : 'Forum communautaire.';
$canonical = $baseUrl ?: '';
?>
<!doctype html>
<html lang="fr" data-bs-theme="<?php echo e($theme); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($appName); ?></title>
    <meta name="description" content="<?php echo e($siteDescription); ?>">
    <?php if ($canonical): ?>
        <link rel="canonical" href="<?php echo e($canonical); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo e($appName); ?>">
    <meta property="og:description" content="<?php echo e($siteDescription); ?>">
    <?php if ($canonical): ?>
        <meta property="og:url" content="<?php echo e($canonical); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo e($appName); ?>">
    <meta name="twitter:description" content="<?php echo e($siteDescription); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo asset('assets/style.css'); ?>?v=<?php echo e($themeVars['version']); ?>" rel="stylesheet">
    <style>
        :root {
            --brand: <?php echo e($themeVars['light_primary']); ?>;
            --accent: <?php echo e($themeVars['light_accent']); ?>;
            --surface: <?php echo e($themeVars['light_surface']); ?>;
            --surface-2: <?php echo e($themeVars['light_bg']); ?>;
            --text: <?php echo e($themeVars['light_text']); ?>;
            --muted: <?php echo e($themeVars['light_muted']); ?>;
            --font: <?php echo e($themeVars['font']); ?>;
            --bs-body-bg: <?php echo e($themeVars['light_bg']); ?>;
            --bs-body-color: <?php echo e($themeVars['light_text']); ?>;
        }

        [data-bs-theme="dark"] {
            --brand: <?php echo e($themeVars['dark_primary']); ?>;
            --accent: <?php echo e($themeVars['dark_accent']); ?>;
            --surface: <?php echo e($themeVars['dark_surface']); ?>;
            --surface-2: <?php echo e($themeVars['dark_bg']); ?>;
            --text: <?php echo e($themeVars['dark_text']); ?>;
            --muted: <?php echo e($themeVars['dark_muted']); ?>;
            --bs-body-bg: <?php echo e($themeVars['dark_bg']); ?>;
            --bs-body-color: <?php echo e($themeVars['dark_text']); ?>;
        }
    </style>
</head>
<body class="app-body d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg shadow-sm">
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
                <li class="nav-item"><a class="nav-link" href="categories.php">Catégories</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="theme.php" data-bs-toggle="tooltip" title="Basculer le thème">
                        <?php if ($theme === 'dark'): ?>
                            <i class="bi bi-sun"></i>
                        <?php else: ?>
                            <i class="bi bi-moon-stars"></i>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($isLogged): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="bi bi-bell"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo e((string) $notifCount); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item d-flex align-items-center me-2">
                        <span class="<?php echo e(role_badge_class($role)); ?>"><?php echo e(role_label($role)); ?></span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-1"></i><?php echo e($displayName ?? 'Profil'); ?></a></li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-shield-lock me-1"></i>Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4 flex-fill">
