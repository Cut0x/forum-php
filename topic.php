<?php
require __DIR__ . '/includes/bootstrap.php';

$topicId = (int)($_GET['id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && is_logged_in()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        $ownerId = (int) $stmt->fetchColumn();
        if ($ownerId === current_user_id() && $content !== '') {
            $stmt = $pdo->prepare('UPDATE posts SET content = ?, edited_at = NOW() WHERE id = ?');
            $stmt->execute([$content, $postId]);
        }
    }

    if ($action === 'vote') {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $value = (int) ($_POST['value'] ?? 0);
        if (in_array($value, [1, -1], true)) {
            $stmt = $pdo->prepare('SELECT value FROM post_votes WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$postId, current_user_id()]);
            $existing = $stmt->fetchColumn();
            if ($existing === false) {
                $stmt = $pdo->prepare('INSERT INTO post_votes (user_id, post_id, value) VALUES (?, ?, ?)');
                $stmt->execute([current_user_id(), $postId, $value]);
            } elseif ((int) $existing === $value) {
                $stmt = $pdo->prepare('DELETE FROM post_votes WHERE user_id = ? AND post_id = ?');
                $stmt->execute([current_user_id(), $postId]);
            } else {
                $stmt = $pdo->prepare('UPDATE post_votes SET value = ? WHERE user_id = ? AND post_id = ?');
                $stmt->execute([$value, current_user_id(), $postId]);
            }
        }
    }

    if ($action === 'reply') {
        $content = trim($_POST['content'] ?? '');
        if ($content !== '' && $topicId) {
            $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
            $stmt->execute([$topicId, current_user_id(), $content]);
            award_badges($pdo, current_user_id());
        }
    }

    header('Location: topic.php?id=' . $topicId);
    exit;
}

require __DIR__ . '/includes/header.php';

$topic = null;
$posts = [];

if ($pdo && $topicId) {
    $stmt = $pdo->prepare('SELECT t.id, t.title, t.created_at, u.username, u.role, u.id AS user_id FROM topics t JOIN users u ON u.id = t.user_id WHERE t.id = ?');
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT p.id, p.content, p.created_at, p.edited_at, u.username, u.avatar, u.role, u.id AS user_id,
        COALESCE((SELECT SUM(value) FROM post_votes v WHERE v.post_id = p.id), 0) AS score,
        (SELECT value FROM post_votes v WHERE v.post_id = p.id AND v.user_id = ?) AS user_vote
        FROM posts p JOIN users u ON u.id = p.user_id WHERE p.topic_id = ? ORDER BY p.created_at');
    $stmt->execute([current_user_id() ?? 0, $topicId]);
    $posts = $stmt->fetchAll();
}

if (!$topic) {
    $topic = ['title' => 'Bienvenue sur le forum', 'created_at' => '2026-02-05 10:15:00', 'username' => 'admin', 'role' => 'admin', 'user_id' => 1];
    $posts = [
        ['id' => 1, 'content' => 'Ravi de vous accueillir sur ce forum open-source.', 'created_at' => '2026-02-05 10:20:00', 'edited_at' => null, 'username' => 'admin', 'avatar' => '', 'role' => 'admin', 'user_id' => 1, 'score' => 5, 'user_vote' => null],
        ['id' => 2, 'content' => 'Merci ! HATE de contribuer.', 'created_at' => '2026-02-05 11:00:00', 'edited_at' => null, 'username' => 'alex', 'avatar' => '', 'role' => 'member', 'user_id' => 2, 'score' => 2, 'user_vote' => 1],
    ];
}
?>
<section class="mb-4">
    <h1 class="h4 mb-1"><?php echo e($topic['title']); ?></h1>
    <p class="text-muted">
        Par <a class="text-decoration-none" href="profile.php?id=<?php echo e((string) $topic['user_id']); ?>"><?php echo e($topic['username']); ?></a>
        <span class="<?php echo e(role_badge_class($topic['role'] ?? null)); ?>"><?php echo e(role_label($topic['role'] ?? null)); ?></span>
        · <?php echo e(format_date($topic['created_at'])); ?>
    </p>
</section>

<div class="vstack gap-3">
    <?php foreach ($posts as $post): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="post-header mb-2">
                    <?php $avatar = $post['avatar'] ?: 'assets/default_user.jpg'; ?>
                    <img class="profile-avatar" src="<?php echo e($avatar); ?>" alt="avatar">
                    <div class="flex-grow-1">
                        <div class="post-meta">
                            <a class="fw-semibold text-decoration-none" href="profile.php?id=<?php echo e((string) $post['user_id']); ?>">
                                <?php echo e($post['username']); ?>
                            </a>
                            <span class="<?php echo e(role_badge_class($post['role'] ?? null)); ?>"><?php echo e(role_label($post['role'] ?? null)); ?></span>
                        </div>
                        <small class="text-muted">
                            <?php echo e(format_date($post['created_at'])); ?>
                            <?php if (!empty($post['edited_at'])): ?>
                                · modifie le <?php echo e(format_date($post['edited_at'])); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php
                            $upClass = ($post['user_vote'] ?? 0) == 1 ? 'btn-primary' : 'btn-outline-primary';
                            $downClass = ($post['user_vote'] ?? 0) == -1 ? 'btn-secondary' : 'btn-outline-secondary';
                        ?>
                        <form method="post" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="action" value="vote">
                            <input type="hidden" name="post_id" value="<?php echo e((string) $post['id']); ?>">
                            <button class="btn btn-sm <?php echo e($upClass); ?>" type="submit" name="value" value="1" <?php echo !is_logged_in() ? 'disabled' : ''; ?>><i class="bi bi-hand-thumbs-up"></i></button>
                            <span class="vote-pill"><?php echo e((string) $post['score']); ?></span>
                            <button class="btn btn-sm <?php echo e($downClass); ?>" type="submit" name="value" value="-1" <?php echo !is_logged_in() ? 'disabled' : ''; ?>><i class="bi bi-hand-thumbs-down"></i></button>
                        </form>
                    </div>
                </div>
                <div class="content">
                    <?php echo render_markdown($post['content']); ?>
                </div>
                <?php if (is_logged_in() && $post['user_id'] === current_user_id()): ?>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-<?php echo e((string) $post['id']); ?>">Modifier</button>
                        <div class="collapse mt-2" id="edit-<?php echo e((string) $post['id']); ?>">
                            <form method="post">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="post_id" value="<?php echo e((string) $post['id']); ?>">
                                <textarea class="form-control" name="content" rows="4"><?php echo e($post['content']); ?></textarea>
                                <button class="btn btn-primary btn-sm mt-2" type="submit">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">Repondre</div>
    <div class="card-body">
        <?php if (!is_logged_in()): ?>
            <div class="alert alert-warning mb-0">Connectez-vous pour repondre.</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="reply">
                <div class="mb-3">
                    <textarea class="form-control" name="content" rows="4" placeholder="Votre message..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Poster</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
