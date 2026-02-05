<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$userId = (int)($_GET['id'] ?? 1);
$user = null;
$badges = [];
$links = [];

if ($pdo) {
    $stmt = $pdo->prepare('SELECT id, username, bio, avatar FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT b.name, b.color FROM badges b JOIN user_badges ub ON ub.badge_id = b.id WHERE ub.user_id = ?');
    $stmt->execute([$userId]);
    $badges = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT label, url FROM user_links WHERE user_id = ?');
    $stmt->execute([$userId]);
    $links = $stmt->fetchAll();
}

if (!$user) {
    $user = [
        'username' => 'admin',
        'bio' => 'Developpeur et mainteneur du forum.',
        'avatar' => 'https://via.placeholder.com/96',
    ];
    $badges = [
        ['name' => 'Fondateur', 'color' => '#0d6efd'],
        ['name' => 'Contributeur', 'color' => '#198754'],
    ];
    $links = [
        ['label' => 'GitHub', 'url' => 'https://github.com/'],
        ['label' => 'Portfolio', 'url' => 'https://example.com'],
    ];
}
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <img class="profile-avatar mb-3" src="<?php echo e($user['avatar'] ?: 'https://via.placeholder.com/96'); ?>" alt="avatar">
                <h5 class="mb-1"><?php echo e($user['username']); ?></h5>
                <p class="text-muted mb-2"><?php echo e($user['bio']); ?></p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <?php foreach ($badges as $badge): ?>
                        <span class="badge" style="background: <?php echo e($badge['color']); ?>;">
                            <?php echo e($badge['name']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Liens</div>
            <div class="card-body">
                <div class="link-list d-flex flex-column gap-2">
                    <?php foreach ($links as $link): ?>
                        <a href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener" class="text-decoration-none">
                            <?php echo e($link['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
