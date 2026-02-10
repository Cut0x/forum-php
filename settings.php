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
    } elseif ($action === 'update_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            $error = 'Tous les champs sont requis.';
        } elseif (strlen($new) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } elseif ($new !== $confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $hash = (string) $stmt->fetchColumn();
            if (!$hash || !password_verify($current, $hash)) {
                $error = 'Mot de passe actuel incorrect.';
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $stmt->execute([$newHash, $userId]);
                $success = 'Mot de passe mis à jour.';
            }
        }
    } elseif ($action === 'export_data') {
        $stmt = $pdo->prepare('SELECT id, name, username, email, role, bio, avatar, created_at FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT label, url FROM user_links WHERE user_id = ?');
        $stmt->execute([$userId]);
        $links = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT id, category_id, title, created_at, edited_at, locked_at, deleted_at FROM topics WHERE user_id = ?');
        $stmt->execute([$userId]);
        $topics = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT id, topic_id, content, created_at, edited_at, deleted_at FROM posts WHERE user_id = ?');
        $stmt->execute([$userId]);
        $posts = $stmt->fetchAll();

        $payload = [
            'user' => $user ?: [],
            'links' => $links,
            'topics' => $topics,
            'posts' => $posts,
            'exported_at' => date('c'),
        ];

        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="forum_export_' . $userId . '.json"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
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

        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <form method="post" class="d-flex flex-column gap-3">
                    <input type="hidden" name="action" value="update_password">
                    <div>
                        <label class="form-label">Mot de passe actuel</label>
                        <input class="form-control" name="current_password" type="password" required>
                    </div>
                    <div>
                        <label class="form-label">Nouveau mot de passe</label>
                        <input class="form-control" name="new_password" type="password" required>
                    </div>
                    <div>
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input class="form-control" name="confirm_password" type="password" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-outline-primary">Mettre à jour le mot de passe</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <form method="post" class="d-flex flex-column gap-3">
                    <input type="hidden" name="action" value="export_data">
                    <div>
                        <label class="form-label">Exporter mes données</label>
                        <div class="form-text text-muted">Télécharge un fichier JSON avec votre profil, vos liens, vos sujets et vos messages.</div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-outline-secondary">Télécharger l’export</button>
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
