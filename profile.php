<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$userId = (int)($_GET['id'] ?? current_user_id());
$canEdit = $userId === current_user_id();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && $canEdit) {
    $bio = trim($_POST['bio'] ?? '');
    $links = [];

    for ($i = 1; $i <= 3; $i++) {
        $label = trim($_POST['link_label_' . $i] ?? '');
        $url = trim($_POST['link_url_' . $i] ?? '');
        if ($label && $url) {
            $links[] = ['label' => $label, 'url' => $url];
        }
    }

    $avatarPath = null;
    if (!empty($_FILES['avatar']['name'])) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (isset($allowed[$mime]) && $file['size'] <= 2 * 1024 * 1024) {
            $ext = $allowed[$mime];
            $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
            $targetDir = __DIR__ . '/uploads/avatars';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $target = $targetDir . '/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $avatarPath = 'uploads/avatars/' . $filename;
            }
        } else {
            $error = 'Avatar invalide.';
        }
    }

    if (!$error) {
        if ($avatarPath) {
            $stmt = $pdo->prepare('UPDATE users SET bio = ?, avatar = ? WHERE id = ?');
            $stmt->execute([$bio, $avatarPath, $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET bio = ? WHERE id = ?');
            $stmt->execute([$bio, $userId]);
        }

        $stmt = $pdo->prepare('DELETE FROM user_links WHERE user_id = ?');
        $stmt->execute([$userId]);

        if ($links) {
            $stmt = $pdo->prepare('INSERT INTO user_links (user_id, label, url) VALUES (?, ?, ?)');
            foreach ($links as $link) {
                $stmt->execute([$userId, $link['label'], $link['url']]);
            }
        }

        $message = 'Profil mis a jour.';
    }
}

require __DIR__ . '/includes/header.php';

$user = null;
$badges = [];
$links = [];
$stats = [
    'topics' => 0,
    'posts' => 0,
    'badges' => 0,
];

if ($pdo) {
    $stmt = $pdo->prepare('SELECT id, username, bio, avatar, role FROM users WHERE id = ?');
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
        'role' => 'admin',
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
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h1 class="h4 mb-0"><?php echo e($user['username']); ?></h1>
                        <span class="<?php echo e(role_badge_class($user['role'] ?? null)); ?>" data-bs-toggle="tooltip" title="Role utilisateur">
                            <?php echo e(role_label($user['role'] ?? null)); ?>
                        </span>
                    </div>
                    <p class="text-muted mb-0"><?php echo e($user['bio']); ?></p>
                </div>
                <?php if ($canEdit): ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editProfile">
                            <i class="bi bi-pencil-square me-1"></i>Editer
                        </button>
                    </div>
                <?php endif; ?>
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

<?php if ($canEdit): ?>
    <div class="collapse mb-4" id="editProfile">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Profil</div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success py-2 mb-3"><?php echo e($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" name="bio" rows="3"><?php echo e($user['bio']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Avatar</label>
                        <input class="form-control" name="avatar" type="file" accept="image/png, image/jpeg">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Lien 1</label>
                            <input class="form-control" name="link_label_1" value="<?php echo e($links[0]['label'] ?? ''); ?>" placeholder="Label">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">URL 1</label>
                            <input class="form-control" name="link_url_1" value="<?php echo e($links[0]['url'] ?? ''); ?>" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lien 2</label>
                            <input class="form-control" name="link_label_2" value="<?php echo e($links[1]['label'] ?? ''); ?>" placeholder="Label">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">URL 2</label>
                            <input class="form-control" name="link_url_2" value="<?php echo e($links[1]['url'] ?? ''); ?>" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lien 3</label>
                            <input class="form-control" name="link_label_3" value="<?php echo e($links[2]['label'] ?? ''); ?>" placeholder="Label">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">URL 3</label>
                            <input class="form-control" name="link_url_3" value="<?php echo e($links[2]['url'] ?? ''); ?>" placeholder="https://">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

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
