<?php
require __DIR__ . '/includes/bootstrap.php';

$topicId = (int)($_GET['id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && is_logged_in()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_topic') {
        $title = trim($_POST['title'] ?? '');
        $stmt = $pdo->prepare('SELECT user_id FROM topics WHERE id = ?');
        $stmt->execute([$topicId]);
        $ownerId = (int) $stmt->fetchColumn();
        if ($title !== '' && ($ownerId === current_user_id() || is_admin())) {
            $stmt = $pdo->prepare('UPDATE topics SET title = ?, edited_at = NOW() WHERE id = ?');
            $stmt->execute([$title, $topicId]);
        }
    }

    if ($action === 'delete_topic') {
        $stmt = $pdo->prepare('SELECT user_id FROM topics WHERE id = ?');
        $stmt->execute([$topicId]);
        $ownerId = (int) $stmt->fetchColumn();
        if ($ownerId === current_user_id() || is_admin()) {
            $stmt = $pdo->prepare('UPDATE topics SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$topicId]);
        }
    }

    if ($action === 'lock_topic') {
        if (is_admin()) {
            $stmt = $pdo->prepare('UPDATE topics SET locked_at = NOW() WHERE id = ?');
            $stmt->execute([$topicId]);
        }
    }

    if ($action === 'unlock_topic') {
        if (is_admin()) {
            $stmt = $pdo->prepare('UPDATE topics SET locked_at = NULL WHERE id = ?');
            $stmt->execute([$topicId]);
        }
    }

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

    if ($action === 'delete_post') {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        $ownerId = (int) $stmt->fetchColumn();
        if ($ownerId === current_user_id() || is_admin()) {
            $stmt = $pdo->prepare('UPDATE posts SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$postId]);
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

            $stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
            $stmt->execute([$postId]);
            $ownerId = (int) $stmt->fetchColumn();
            if ($ownerId && $ownerId !== current_user_id()) {
                $label = $value === 1 ? 'Upvote' : 'Downvote';
                create_notification($pdo, $ownerId, 'vote', $label . ' sur votre message', $topicId, $postId, current_user_id());
            }
        }
    }

    if ($action === 'reply') {
        $content = trim($_POST['content'] ?? '');
        if ($content !== '' && $topicId) {
            $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
            $stmt->execute([$topicId, current_user_id(), $content]);
            $postId = (int) $pdo->lastInsertId();
            award_badges($pdo, current_user_id());

            $stmt = $pdo->prepare('SELECT user_id FROM topics WHERE id = ?');
            $stmt->execute([$topicId]);
            $topicOwner = (int) $stmt->fetchColumn();
            if ($topicOwner && $topicOwner !== current_user_id()) {
                create_notification($pdo, $topicOwner, 'reply', 'Nouvelle réponse sur votre sujet', $topicId, $postId, current_user_id());
            }

            foreach (parse_mentions($content) as $mention) {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                $stmt->execute([$mention]);
                $mentionId = (int) $stmt->fetchColumn();
                if ($mentionId && $mentionId !== current_user_id()) {
                    create_notification($pdo, $mentionId, 'mention', 'Vous avez été mentionné', $topicId, $postId, current_user_id());
                }
            }
        }
    }

    header('Location: topic.php?id=' . $topicId);
    exit;
}

require __DIR__ . '/includes/header.php';
require_db();

$topic = null;
$posts = [];

if ($topicId) {
    $stmt = $pdo->prepare('SELECT t.id, t.title, t.created_at, t.edited_at, t.locked_at, t.deleted_at, c.is_readonly, u.name, u.username, u.role, u.id AS user_id FROM topics t JOIN users u ON u.id = t.user_id JOIN categories c ON c.id = t.category_id WHERE t.id = ?');
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT p.id, p.content, p.created_at, p.edited_at, p.deleted_at, u.name, u.username, u.avatar, u.role, u.id AS user_id,
        COALESCE((SELECT SUM(value) FROM post_votes v WHERE v.post_id = p.id), 0) AS score,
        (SELECT value FROM post_votes v WHERE v.post_id = p.id AND v.user_id = ?) AS user_vote
        FROM posts p JOIN users u ON u.id = p.user_id WHERE p.topic_id = ? ORDER BY p.created_at');
    $stmt->execute([current_user_id() ?? 0, $topicId]);
    $posts = $stmt->fetchAll();
}

if (!$topic) {
    echo '<div class="alert alert-danger">Sujet introuvable.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
?>
<section class="mb-4">
    <h1 class="h4 mb-1"><?php echo e($topic['title']); ?></h1>
    <p class="text-muted">
        Par <a class="text-decoration-none" href="profile.php?id=<?php echo e((string) $topic['user_id']); ?>"><?php echo e($topic['name'] ?: $topic['username']); ?></a>
        <span class="text-muted">@<?php echo e($topic['username']); ?></span>
        <span class="<?php echo e(role_badge_class($topic['role'] ?? null)); ?>"><?php echo e(role_label($topic['role'] ?? null)); ?></span>
        · <?php echo e(format_date($topic['created_at'])); ?>
        <?php if (!empty($topic['edited_at'])): ?>
            · modifié le <?php echo e(format_date($topic['edited_at'])); ?>
        <?php endif; ?>
        <?php if (!empty($topic['locked_at'])): ?>
            · sujet verrouillé
        <?php endif; ?>
    </p>
    <?php if (is_logged_in() && ($topic['user_id'] === current_user_id() || is_admin())): ?>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-topic">Modifier le titre</button>
            <form method="post">
                <input type="hidden" name="action" value="delete_topic">
                <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer le sujet</button>
            </form>
            <?php if (is_admin()): ?>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo empty($topic['locked_at']) ? 'lock_topic' : 'unlock_topic'; ?>">
                    <button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo empty($topic['locked_at']) ? 'Clôturer' : 'Réouvrir'; ?></button>
                </form>
            <?php endif; ?>
        </div>
        <div class="collapse mt-2" id="edit-topic">
            <form method="post" class="d-flex gap-2">
                <input type="hidden" name="action" value="edit_topic">
                <input class="form-control" name="title" value="<?php echo e($topic['title']); ?>">
                <button class="btn btn-primary" type="submit">OK</button>
            </form>
        </div>
    <?php endif; ?>
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
                                <?php echo e($post['name'] ?: $post['username']); ?>
                                <span class="text-muted">@<?php echo e($post['username']); ?></span>
                            </a>
                            <span class="<?php echo e(role_badge_class($post['role'] ?? null)); ?>"><?php echo e(role_label($post['role'] ?? null)); ?></span>
                        </div>
                        <small class="text-muted">
                            <?php echo e(format_date($post['created_at'])); ?>
                        <?php if (!empty($post['edited_at'])): ?>
                            · modifié le <?php echo e(format_date($post['edited_at'])); ?>
                        <?php endif; ?>
                        </small>
                    </div>
                    <?php if (empty($post['deleted_at'])): ?>
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
                    <?php endif; ?>
                </div>
                <div class="content">
                    <?php if ($post['deleted_at']): ?>
                        <div class="text-muted">Message supprimé.</div>
                    <?php else: ?>
                        <?php echo render_markdown_with_mentions($pdo, $post['content']); ?>
                    <?php endif; ?>
                </div>
                <?php if (is_logged_in() && $post['user_id'] === current_user_id() && empty($post['deleted_at'])): ?>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-<?php echo e((string) $post['id']); ?>">Modifier</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="post_id" value="<?php echo e((string) $post['id']); ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                        <div class="collapse mt-2" id="edit-<?php echo e((string) $post['id']); ?>">
                            <form method="post">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="post_id" value="<?php echo e((string) $post['id']); ?>">
                                <textarea class="form-control" name="content" rows="4" data-mentions="1" data-emotes="1"><?php echo e($post['content']); ?></textarea>
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
    <div class="card-header bg-white">Répondre</div>
    <div class="card-body">
        <?php if (!empty($topic['deleted_at']) || !empty($topic['locked_at']) || (!empty($topic['is_readonly']) && !is_admin())): ?>
            <div class="alert alert-secondary mb-0">Sujet fermé.</div>
        <?php elseif (!is_logged_in()): ?>
            <div class="alert alert-warning mb-0">Connectez-vous pour répondre.</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="reply">
                <div class="mb-3">
                <textarea class="form-control" name="content" rows="4" placeholder="Votre message..." data-mentions="1" data-emotes="1"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Poster</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
