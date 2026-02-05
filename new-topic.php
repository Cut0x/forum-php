<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$categories = [];
if ($pdo) {
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll();
} else {
    $categories = [
        ['id' => 1, 'name' => 'Annonces'],
        ['id' => 2, 'name' => 'Support'],
    ];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($title && $content && $categoryId) {
        $stmt = $pdo->prepare('INSERT INTO topics (category_id, user_id, title) VALUES (?, ?, ?)');
        $stmt->execute([$categoryId, current_user_id(), $title]);
        $topicId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$topicId, current_user_id(), $content]);
        award_badges($pdo, current_user_id());

        header('Location: topic.php?id=' . $topicId);
        exit;
    }

    $error = 'Champs manquants.';
}

require __DIR__ . '/includes/header.php';
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
                        <label class="form-label">Categorie</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Selectionner...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo e((string) $category['id']); ?>"><?php echo e($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input class="form-control" name="title" type="text" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Markdown)</label>
                        <textarea class="form-control" id="markdown" name="content" rows="6" placeholder="Votre message..."></textarea>
                    </div>
                    <div class="preview-box mb-3" id="preview">Apercu...</div>
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
            .then((html) => { preview.innerHTML = html || 'Apercu...'; })
            .catch(() => { preview.textContent = 'Apercu...'; });
    }

    textarea.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(updatePreview, 300);
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
