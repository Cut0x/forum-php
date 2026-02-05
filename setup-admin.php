<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
require_db();

$exists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
if ($exists > 0) {
    echo '<div class="alert alert-warning">Un administrateur existe deja.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $email && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $hash, 'admin']);
        $adminId = (int) $pdo->lastInsertId();

        $badges = [
            ['Fondateur', 'founder', 'assets/badges/founder.png', '#4f8cff'],
            ['Admin', 'admin', 'assets/badges/admin.png', '#ff4d4f'],
            ['Moderateur', 'moderator', 'assets/badges/moderator.png', '#7c5cff'],
            ['Contributeur', 'contributor', 'assets/badges/contributor.png', '#16a34a'],
            ['Premier message', 'starter', 'assets/badges/starter.png', '#4f8cff'],
            ['10 messages', 'writer', 'assets/badges/writer.png', '#00d1b2'],
            ['25 messages', 'speaker', 'assets/badges/speaker.png', '#ffb020'],
            ['50 messages', 'veteran', 'assets/badges/veteran.png', '#7c5cff'],
            ['Premier sujet', 'first_topic', 'assets/badges/founder.png', '#ff4d4f'],
            ['10 sujets', 'topics_10', 'assets/badges/founder.png', '#ff4d4f'],
            ['Donator', 'donator', 'assets/badges/donator.png', '#00c2ff'],
        ];

        $stmt = $pdo->prepare('INSERT IGNORE INTO badges (name, code, icon, color) VALUES (?, ?, ?, ?)');
        foreach ($badges as $b) {
            $stmt->execute($b);
        }

        $stmt = $pdo->prepare('SELECT id FROM badges WHERE code IN (?, ?)');
        $stmt->execute(['founder', 'admin']);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
        foreach ($ids as $id) {
            $stmt->execute([$adminId, (int) $id]);
        }

        $_SESSION['user_id'] = $adminId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';

        header('Location: admin.php');
        exit;
    } else {
        $error = 'Champs manquants.';
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Creer l\'administrateur</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Pseudo</label>
                        <input class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" type="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input class="form-control" name="password" type="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Creer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
