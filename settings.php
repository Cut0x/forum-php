<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: ' . login_redirect_target());
    exit;
}

$userId = current_user_id();
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        require __DIR__ . '/includes/header.php';
        require_db();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_notifications') {
        $notificationsAvailable = column_exists($pdo, 'users', 'notifications_enabled');
        if (!$notificationsAvailable) {
            $error = 'Paramètre indisponible. Lancez la migration pour activer cette option.';
        } else {
            $enabled = isset($_POST['notifications_enabled']) ? 1 : 0;
            $stmt = $pdo->prepare('UPDATE users SET notifications_enabled = ? WHERE id = ?');
            $stmt->execute([$enabled, $userId]);
            $success = 'Paramètres enregistrés.';
        }
    } elseif ($action === 'update_email') {
        $newEmail = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['current_password'] ?? '');

        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif ($password === '') {
            $error = 'Mot de passe requis.';
        } else {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $hash = (string) $stmt->fetchColumn();
            if (!$hash || !password_verify($password, $hash)) {
                $error = 'Mot de passe incorrect.';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
                $stmt->execute([$newEmail, $userId]);
                if ($stmt->fetch()) {
                    $error = 'Cet email est déjà utilisé.';
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
                    $stmt->execute([$newEmail, $userId]);
                    $success = 'Email mis à jour.';
                }
            }
        }
    } elseif ($action === 'delete_account') {
        $password = (string) ($_POST['current_password'] ?? '');
        $confirm = trim((string) ($_POST['confirm_text'] ?? ''));
        if ($confirm !== 'SUPPRIMER') {
            $error = 'Veuillez saisir SUPPRIMER pour confirmer.';
        } elseif ($password === '') {
            $error = 'Mot de passe requis.';
        } else {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $hash = (string) $stmt->fetchColumn();
            if (!$hash || !password_verify($password, $hash)) {
                $error = 'Mot de passe incorrect.';
            } else {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                session_destroy();
                header('Location: index.php');
                exit;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
require_db();

$notificationsAvailable = column_exists($pdo, 'users', 'notifications_enabled');
if ($notificationsAvailable) {
    if (function_exists('user_notifications_enabled')) {
        $notificationsEnabled = user_notifications_enabled($pdo, $userId);
    } else {
        $stmt = $pdo->prepare('SELECT notifications_enabled FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $notificationsEnabled = (int) $stmt->fetchColumn() === 1;
    }
} else {
    $notificationsEnabled = true;
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

        <div class="row g-4">
            <div class="col-lg-6">
                <form method="post" class="d-flex flex-column gap-3">
                    <input type="hidden" name="action" value="update_notifications">
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

            <div class="col-lg-6">
                <form method="post" class="d-flex flex-column gap-3">
                    <input type="hidden" name="action" value="update_email">
                    <div>
                        <label class="form-label">Nouvel email</label>
                        <input class="form-control" name="email" type="email" required>
                    </div>
                    <div>
                        <label class="form-label">Mot de passe actuel</label>
                        <input class="form-control" name="current_password" type="password" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-outline-primary">Mettre à jour l’email</button>
                    </div>
                </form>
            </div>
        </div>

        <hr class="my-4">

        <div class="card border-danger">
            <div class="card-body">
                <h6 class="text-danger mb-2">Zone dangereuse</h6>
                <form method="post" class="d-flex flex-column gap-3">
                    <input type="hidden" name="action" value="delete_account">
                    <div>
                        <label class="form-label">Confirmation</label>
                        <input class="form-control" name="confirm_text" placeholder="Tapez SUPPRIMER" required>
                    </div>
                    <div>
                        <label class="form-label">Mot de passe actuel</label>
                        <input class="form-control" name="current_password" type="password" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
