<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/includes/header.php';

$userId = (int)($_GET['id'] ?? current_user_id());
$user = null;
$badges = [];
$links = [];
$stats = [
    'topics' => 0,
    'posts' => 0,
    'badges' => 0,
];

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

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM topics WHERE user_id = ?');
    $stmt->execute([$userId]);
    $stats['topics'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
    $stmt->execute([$userId]);
    $stats['posts'] = (int) $stmt->fetchColumn();

    $stats['badges'] = count($badges);
}

if (!$user) {
    $user = [
        'username' => 'admin',
        'bio' => 'Developpeur et mainteneur du forum.',
        'avatar' => 'assets/default_user.jpg',
    ];
    $badges = [
        ['name' => 'Fondateur', 'color' => '#0d6efd'],
        ['name' => 'Contributeur', 'color' => '#198754'],
    ];
    $links = [
        ['label' => 'GitHub', 'url' => 'https://github.com/'],
        ['label' => 'Portfolio', 'url' => 'https://example.com'],
    ];
    $stats = [
        'topics' => 12,
        'posts' => 48,
        'badges' => count($badges),
    ];
}

$avatar = $user['avatar'] ?: 'assets/default_user.jpg';
?>
<section class="profile-hero p-4 mb-4 shadow-sm">
    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
        <img class="profile-avatar" src="<?php echo e($avatar); ?>" alt="avatar">
        <div class="flex-grow-1">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h1 class="h4 mb-1"><?php echo e($user['username']); ?></h1>
                    <p class="text-muted mb-0"><?php echo e($user['bio']); ?></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" type="button"><i class="bi bi-pencil-square me-1"></i>Editer</button>
                    <button class="btn btn-primary" type="button"><i class="bi bi-shield-check me-1"></i>Badges</button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <?php foreach ($badges as $badge): ?>
                    <span class="badge" style="background: <?php echo e($badge['color']); ?>;" data-bs-toggle="tooltip" title="Badge obtenu">
                        <?php echo e($badge['name']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-journal-text fs-4 text-primary"></i>
                <div>
                    <div class="text-muted small">Sujets</div>
                    <div class="fs-5 fw-semibold"><?php echo e((string) $stats['topics']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chat-dots fs-4 text-primary"></i>
                <div>
                    <div class="text-muted small">Messages</div>
                    <div class="fs-5 fw-semibold"><?php echo e((string) $stats['posts']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-award fs-4 text-primary"></i>
                <div>
                    <div class="text-muted small">Badges</div>
                    <div class="fs-5 fw-semibold"><?php echo e((string) $stats['badges']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Liens</strong>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="Ajoutez vos reseaux">
            <i class="bi bi-link-45deg"></i>
        </button>
    </div>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
