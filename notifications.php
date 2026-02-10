<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: ' . login_redirect_target());
    exit;
}

require __DIR__ . '/includes/header.php';
require_db();

$notificationsEnabled = user_notifications_enabled($pdo, current_user_id());

$filters = ['all', 'reply', 'mention', 'vote'];
$type = $_GET['type'] ?? 'all';
if (!in_array($type, $filters, true)) {
    $type = 'all';
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        require __DIR__ . '/includes/header.php';
        require_db();
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        if ($type === 'all') {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
            $stmt->execute([current_user_id()]);
        } else {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND type = ?');
            $stmt->execute([current_user_id(), $type]);
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, current_user_id()]);
    }

    if ($action === 'delete_all') {
        if ($type === 'all') {
            $stmt = $pdo->prepare('DELETE FROM notifications WHERE user_id = ?');
            $stmt->execute([current_user_id()]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM notifications WHERE user_id = ? AND type = ?');
            $stmt->execute([current_user_id(), $type]);
        }
    }

    if ($type === 'all') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
        $stmt->execute([current_user_id()]);
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = ?');
        $stmt->execute([current_user_id(), $type]);
    }
    $total = (int) $stmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $q = http_build_query(['type' => $type, 'page' => $page]);
    header('Location: notifications.php?' . $q);
    exit;
}

require __DIR__ . '/includes/header.php';
require_db();
$notificationsEnabled = function_exists('user_notifications_enabled')
    ? user_notifications_enabled($pdo, current_user_id())
    : true;

if ($type === 'all') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
    $stmt->execute([current_user_id()]);
} else {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = ?');
    $stmt->execute([current_user_id(), $type]);
}
$total = (int) $stmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$hasActor = column_exists($pdo, 'notifications', 'actor_id');
if ($type === 'all') {
    if ($hasActor) {
        $stmt = $pdo->prepare('SELECT n.id, n.type, n.message, n.topic_id, n.post_id, n.is_read, n.created_at, u.id AS actor_id, u.name AS actor_name, u.username AS actor_username
            FROM notifications n LEFT JOIN users u ON u.id = n.actor_id
            WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT ? OFFSET ?');
    } else {
        $stmt = $pdo->prepare('SELECT n.id, n.type, n.message, n.topic_id, n.post_id, n.is_read, n.created_at, NULL AS actor_id, NULL AS actor_name, NULL AS actor_username
            FROM notifications n
            WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT ? OFFSET ?');
    }
    $stmt->bindValue(1, current_user_id(), PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    if ($hasActor) {
        $stmt = $pdo->prepare('SELECT n.id, n.type, n.message, n.topic_id, n.post_id, n.is_read, n.created_at, u.id AS actor_id, u.name AS actor_name, u.username AS actor_username
            FROM notifications n LEFT JOIN users u ON u.id = n.actor_id
            WHERE n.user_id = ? AND n.type = ? ORDER BY n.created_at DESC LIMIT ? OFFSET ?');
    } else {
        $stmt = $pdo->prepare('SELECT n.id, n.type, n.message, n.topic_id, n.post_id, n.is_read, n.created_at, NULL AS actor_id, NULL AS actor_name, NULL AS actor_username
            FROM notifications n
            WHERE n.user_id = ? AND n.type = ? ORDER BY n.created_at DESC LIMIT ? OFFSET ?');
    }
    $stmt->bindValue(1, current_user_id(), PDO::PARAM_INT);
    $stmt->bindValue(2, $type, PDO::PARAM_STR);
    $stmt->bindValue(3, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
}
$notifications = $stmt->fetchAll();

$counts = [];
$stmt = $pdo->prepare('SELECT type, COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0 GROUP BY type');
$stmt->execute([current_user_id()]);
foreach ($stmt->fetchAll() as $row) {
    $counts[$row['type']] = (int) $row['total'];
}
?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong>Notifications</strong>
        <div class="d-flex gap-2">
            <form method="post">
                <input type="hidden" name="action" value="mark_read">
                <button class="btn btn-sm btn-outline-primary" type="submit">Tout marquer comme lu</button>
            </form>
            <form method="post">
                <input type="hidden" name="action" value="delete_all">
                <button class="btn btn-sm btn-outline-danger" type="submit">Tout supprimer</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$notificationsEnabled): ?>
            <div class="alert alert-warning">
                Les notifications sont désactivées. Vous pouvez les réactiver dans <a href="settings.php">vos paramètres</a>.
            </div>
        <?php endif; ?>
        <div class="btn-group mb-3" role="group">
            <a class="btn btn-outline-secondary <?php echo $type === 'all' ? 'active' : ''; ?>" href="notifications.php?type=all">Toutes</a>
            <a class="btn btn-outline-secondary <?php echo $type === 'reply' ? 'active' : ''; ?>" href="notifications.php?type=reply">Réponses <?php echo isset($counts['reply']) ? '(' . $counts['reply'] . ')' : ''; ?></a>
            <a class="btn btn-outline-secondary <?php echo $type === 'mention' ? 'active' : ''; ?>" href="notifications.php?type=mention">Mentions <?php echo isset($counts['mention']) ? '(' . $counts['mention'] . ')' : ''; ?></a>
            <a class="btn btn-outline-secondary <?php echo $type === 'vote' ? 'active' : ''; ?>" href="notifications.php?type=vote">Votes <?php echo isset($counts['vote']) ? '(' . $counts['vote'] . ')' : ''; ?></a>
        </div>
        <div class="list-group">
            <?php if (!$notifications): ?>
                <div class="list-group-item text-muted">Aucune notification.</div>
            <?php endif; ?>
            <?php foreach ($notifications as $notif): ?>
                <?php $link = $notif['topic_id'] ? 'topic.php?id=' . $notif['topic_id'] : '#'; ?>
                <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold"><?php echo e(notification_label($notif['type'])); ?></div>
                        <a class="text-decoration-none" href="<?php echo e($link); ?>"><?php echo e($notif['message']); ?></a>
                        <?php if (!empty($notif['actor_id'])): ?>
                            <div class="small">
                                Par <a class="text-decoration-none" href="profile.php?id=<?php echo e((string) $notif['actor_id']); ?>">
                                    <?php echo e($notif['actor_name'] ?: $notif['actor_username']); ?>
                                </a>
                                <?php if (!empty($notif['actor_username'])): ?>
                                    <span class="text-muted">@<?php echo e($notif['actor_username']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted small"><?php echo e(format_date($notif['created_at'])); ?></div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <?php if (!$notif['is_read']): ?>
                            <span class="badge bg-primary">Nouveau</span>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo e((string) $notif['id']); ?>">
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <nav class="mt-3">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="notifications.php?type=<?php echo e($type); ?>&page=<?php echo e((string) $i); ?>"><?php echo e((string) $i); ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
