<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';

    if ($action === 'role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'member';
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $userId]);
        $message = 'Role mis a jour.';
    }

    if ($action === 'badge') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $badgeId = (int) ($_POST['badge_id'] ?? 0);
        $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
        $stmt->execute([$userId, $badgeId]);
        $message = 'Badge ajoute.';
    }

    if ($action === 'badge_remove') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $badgeId = (int) ($_POST['badge_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM user_badges WHERE user_id = ? AND badge_id = ?');
        $stmt->execute([$userId, $badgeId]);
        $message = 'Badge retire.';
    }

    if ($action === 'badge_create') {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $color = trim($_POST['color'] ?? '#0d6efd');
        if ($name && $code && $icon) {
            $stmt = $pdo->prepare('INSERT INTO badges (name, code, icon, color) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $code, $icon, $color]);
            $message = 'Badge cree.';
        }
    }
}

require __DIR__ . '/includes/header.php';

$users = $pdo ? $pdo->query('SELECT id, username, role FROM users ORDER BY username')->fetchAll() : [];
$badges = $pdo ? $pdo->query('SELECT id, name, code, icon, color FROM badges ORDER BY name')->fetchAll() : [];
?>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Creer un badge</div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success py-2 mb-3"><?php echo e($message); ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="action" value="badge_create">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input class="form-control" name="code" placeholder="starter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input class="form-control" name="icon" placeholder="assets/badges/starter.png" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Couleur</label>
                        <input class="form-control" name="color" value="#0d6efd">
                    </div>
                    <button class="btn btn-primary" type="submit">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Utilisateurs</div>
            <div class="card-body">
                <?php foreach ($users as $user): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div class="fw-semibold"><?php echo e($user['username']); ?></div>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="action" value="role">
                                <input type="hidden" name="user_id" value="<?php echo e((string) $user['id']); ?>">
                                <select class="form-select" name="role">
                                    <option value="member" <?php echo $user['role'] === 'member' ? 'selected' : ''; ?>>Membre</option>
                                    <option value="moderator" <?php echo $user['role'] === 'moderator' ? 'selected' : ''; ?>>Modo</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button class="btn btn-outline-primary" type="submit">OK</button>
                            </form>
                        </div>
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <?php foreach ($badges as $badge): ?>
                                <form method="post" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="user_id" value="<?php echo e((string) $user['id']); ?>">
                                    <input type="hidden" name="badge_id" value="<?php echo e((string) $badge['id']); ?>">
                                    <img class="badge-icon" src="<?php echo e($badge['icon']); ?>" alt="badge">
                                    <button class="btn btn-sm btn-outline-primary" type="submit" name="action" value="badge">+</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="submit" name="action" value="badge_remove">-</button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
