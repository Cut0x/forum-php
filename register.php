<?php
require __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$hcaptchaSite = $_ENV['HCAPTCHA_SITE'] ?? '';
$hcaptchaEnabled = ($_ENV['HCAPTCHA_ENABLED'] ?? '0') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($hcaptchaEnabled) {
        $token = $_POST['h-captcha-response'] ?? '';
        $secret = $_ENV['HCAPTCHA_SECRET'] ?? '';
        if (!$secret) {
            $error = 'hCaptcha non configure.';
        } else {
            $data = http_build_query(['secret' => $secret, 'response' => $token]);
            $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $data]];
            $resp = @file_get_contents('https://hcaptcha.com/siteverify', false, stream_context_create($opts));
            $result = $resp ? json_decode($resp, true) : null;
            if (!$result || empty($result['success'])) {
                $error = 'hCaptcha invalide.';
            }
        }
    }

    if (!$error && $username && $email && $password) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        $exists = $stmt->fetch();

        if (!$exists) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hash, 'member']);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'member';

            $mailEnabled = ($_ENV['MAIL_ENABLED'] ?? '0') === '1';
            if ($mailEnabled) {
                send_mail($email, 'Bienvenue', '<p>Merci pour votre inscription.</p>');
            }
            header('Location: profile.php');
            exit;
        }
    }

    $error = 'Impossible de creer le compte.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Inscription</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Pseudo</label>
                        <input class="form-control" name="username" type="text" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" type="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input class="form-control" name="password" type="password" required>
                    </div>
                    <?php if ($hcaptchaEnabled): ?>
                        <div class="mb-3">
                            <div class="h-captcha" data-sitekey="<?php echo e($hcaptchaSite); ?>"></div>
                        </div>
                        <script src="https://hcaptcha.com/1/api.js" async defer></script>
                    <?php endif; ?>
                    <button class="btn btn-primary w-100" type="submit">Creer un compte</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
