<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: ' . login_redirect_target());
    exit;
}

require __DIR__ . '/includes/header.php';
require_db();

$userId = current_user_id();
$notificationsAvailable = column_exists($pdo, 'users', 'notifications_enabled');
$notificationsEnabled = $notificationsAvailable ? user_notifications_enabled($pdo, $userId) : true;
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$notificationsAvailable) {
        $error = 'Paramètre indisponible. Lancez la migration pour activer cette option.';
    } else {
        $enabled = isset($_POST['notifications_enabled']) ? 1 : 0;
        $stmt = $pdo->prepare('UPDATE users SET notifications_enabled = ? WHERE id = ?');
        $stmt->execute([$enabled, $userId]);
        $notificationsEnabled = $enabled === 1;
        $success = 'Paramètres enregistrés.';
    }
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <strong>Paramètres</strong>
    </div>
    <div class="card-body">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" class="d-flex flex-column gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="notifications_enabled" name="notifications_enabled" <?php echo $notificationsEnabled ? 'checked' : ''; ?> <?php echo $notificationsAvailable ? '' : 'disabled'; ?>>
                <label class="form-check-label" for="notifications_enabled">
                    Activer les notifications
                </label>
                <div class="form-text text-muted">Désactivez pour ne plus recevoir de nouvelles notifications.</div>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
