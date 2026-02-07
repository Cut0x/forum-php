<?php
require __DIR__ . '/includes/bootstrap.php';

$redirect = sanitize_redirect($_GET['redirect'] ?? ($_POST['redirect'] ?? '')) ?? 'profile.php';

if (is_logged_in()) {
    header('Location: ' . $redirect);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, username, name, password_hash, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'] ?? $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ' . $redirect);
            exit;
        }
    }

    $error = 'Identifiants invalides.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Connexion</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <?php if ($redirect !== 'profile.php'): ?>
                        <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" type="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input class="form-control" name="password" type="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
