<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if ($pdo) {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->execute([current_user_id()]);

    $stmt = $pdo->prepare('SELECT id, type, message, topic_id, post_id, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([current_user_id()]);
    $notifications = $stmt->fetchAll();
} else {
    $notifications = [];
}

require __DIR__ . '/includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-header bg-white">Notifications</div>
    <div class="list-group list-group-flush">
        <?php if (!$notifications): ?>
            <div class="list-group-item text-muted">Aucune notification.</div>
        <?php endif; ?>
        <?php foreach ($notifications as $notif): ?>
            <?php $link = $notif['topic_id'] ? 'topic.php?id=' . $notif['topic_id'] : '#'; ?>
            <a class="list-group-item list-group-item-action" href="<?php echo e($link); ?>">
                <div class="d-flex justify-content-between">
                    <span><?php echo e($notif['message']); ?></span>
                    <small class="text-muted"><?php echo e(format_date($notif['created_at'])); ?></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
