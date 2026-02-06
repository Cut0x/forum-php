<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$categories = [];
require __DIR__ . '/includes/header.php';
require_db();
$categories = $pdo->query('SELECT id, name, is_readonly FROM categories ORDER BY sort_order, name')->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($title && $content && $categoryId) {
        $stmt = $pdo->prepare('SELECT is_readonly FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $readonly = (int) $stmt->fetchColumn();
        if ($readonly === 1 && !is_admin()) {
            $error = 'Catégorie en lecture seule.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO topics (category_id, user_id, title) VALUES (?, ?, ?)');
            $stmt->execute([$categoryId, current_user_id(), $title]);
            $topicId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
            $stmt->execute([$topicId, current_user_id(), $content]);
            $postId = (int) $pdo->lastInsertId();
            award_badges($pdo, current_user_id());

        foreach (parse_mentions($content) as $mention) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$mention]);
            $mentionId = (int) $stmt->fetchColumn();
            if ($mentionId && $mentionId !== current_user_id()) {
                create_notification($pdo, $mentionId, 'mention', 'Vous avez été mentionné', $topicId, $postId, current_user_id());
            }
        }

            header('Location: topic.php?id=' . $topicId);
            exit;
        }
    }

    $error = 'Champs manquants.';
}
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Nouveau sujet</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo e((string) $category['id']); ?>" <?php echo !empty($category['is_readonly']) && !is_admin() ? 'disabled' : ''; ?>>
                                    <?php echo e($category['name']); ?><?php echo !empty($category['is_readonly']) ? ' (lecture seule)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input class="form-control" name="title" type="text" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Markdown)</label>
                    <textarea class="form-control" id="markdown" name="content" rows="6" placeholder="Votre message..." data-mentions="1" data-emotes="1"></textarea>
                    </div>
                    <div class="preview-box mb-3" id="preview">Aperçu...</div>
                    <button class="btn btn-primary" type="submit">Publier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const textarea = document.getElementById('markdown');
    const preview = document.getElementById('preview');
    let timer = null;

    function updatePreview() {
        const body = new FormData();
        body.append('content', textarea.value);
        fetch('preview.php', { method: 'POST', body })
            .then((r) => r.text())
            .then((html) => { preview.innerHTML = html || 'Aperçu...'; })
            .catch(() => { preview.textContent = 'Aperçu...'; });
    }

    textarea.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(updatePreview, 300);
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
