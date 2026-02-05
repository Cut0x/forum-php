<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$topicId = (int)($_GET['id'] ?? 0);
$topic = null;
$posts = [];

if ($pdo && $topicId) {
    $stmt = $pdo->prepare('SELECT t.id, t.title, t.created_at, u.username FROM topics t JOIN users u ON u.id = t.user_id WHERE t.id = ?');
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT p.id, p.content, p.created_at, u.username, u.avatar FROM posts p JOIN users u ON u.id = p.user_id WHERE p.topic_id = ? ORDER BY p.created_at');
    $stmt->execute([$topicId]);
    $posts = $stmt->fetchAll();
}

if (!$topic) {
    $topic = ['title' => 'Bienvenue sur le forum', 'created_at' => '2026-02-05 10:15:00', 'username' => 'admin'];
    $posts = [
        ['content' => 'Ravi de vous accueillir sur ce forum open-source.', 'created_at' => '2026-02-05 10:20:00', 'username' => 'admin', 'avatar' => ''],
        ['content' => 'Merci ! HATE de contribuer.', 'created_at' => '2026-02-05 11:00:00', 'username' => 'alex', 'avatar' => ''],
    ];
}
?>
<section class="mb-4">
    <h1 class="h4 mb-1"><?php echo e($topic['title']); ?></h1>
    <p class="text-muted">Par <?php echo e($topic['username']); ?> · <?php echo e(format_date($topic['created_at'])); ?></p>
</section>

<div class="vstack gap-3">
    <?php foreach ($posts as $post): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <?php $avatar = $post['avatar'] ?: 'assets/default_user.jpg'; ?>
                    <img class="profile-avatar" src="<?php echo e($avatar); ?>" alt="avatar">
                    <div>
                        <strong><?php echo e($post['username']); ?></strong><br>
                        <small class="text-muted"><?php echo e(format_date($post['created_at'])); ?></small>
                    </div>
                </div>
                <p class="mb-0"><?php echo e($post['content']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">Repondre</div>
    <div class="card-body">
        <form>
            <div class="mb-3">
                <textarea class="form-control" rows="4" placeholder="Votre message..."></textarea>
            </div>
            <button type="button" class="btn btn-primary">Poster</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
