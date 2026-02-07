<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$categoryId = (int)($_GET['id'] ?? 0);
$category = null;
$topics = [];
$categories = [];

require_db();
if ($categoryId) {
    $stmt = $pdo->prepare('SELECT c.id, c.name, c.description, c.is_readonly, c.is_pinned, COUNT(t.id) AS topic_count
        FROM categories c
        LEFT JOIN topics t ON t.category_id = c.id AND t.deleted_at IS NULL
        WHERE c.id = ?
        GROUP BY c.id');
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    $categories = $pdo->query('SELECT id, name, is_readonly FROM categories ORDER BY is_pinned DESC, sort_order, name')->fetchAll();

    $stmt = $pdo->prepare('SELECT id, title, created_at FROM topics WHERE category_id = ? AND deleted_at IS NULL ORDER BY created_at DESC');
    $stmt->execute([$categoryId]);
    $topics = $stmt->fetchAll();
}

if (!$category) {
    echo '<div class="alert alert-danger">Catégorie introuvable.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
?>
<section class="mb-4">
    <h1 class="h4 mb-1">
        <?php echo e($category['name']); ?>
        <?php if (!empty($category['is_pinned'])): ?>
            <i class="bi bi-pin-angle-fill text-warning ms-1" title="Épinglée"></i>
        <?php endif; ?>
    </h1>
    <?php $count = (int) $category['topic_count']; ?>
    <p class="text-muted mb-1"><?php echo e((string) $count); ?> sujet<?php echo $count > 1 ? 's' : ''; ?> -</p>
    <p class="text-muted"><?php echo e($category['description']); ?></p>
</section>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Sujets</strong>
        <?php if (is_logged_in() && (empty($category['is_readonly']) || is_admin())): ?>
            <button class="btn btn-sm btn-primary" type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#newTopicModal"
                    data-category-id="<?php echo e((string) $category['id']); ?>"
                    data-category-name="<?php echo e($category['name']); ?>">
                Nouveau sujet
            </button>
        <?php else: ?>
            <span class="text-muted small">Lecture seule</span>
        <?php endif; ?>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($topics as $topic): ?>
            <a class="list-group-item list-group-item-action" href="topic.php?id=<?php echo e((string) $topic['id']); ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1"><?php echo e($topic['title']); ?></h6>
                        <small class="text-muted">Dernière activité</small>
                    </div>
                    <small class="text-muted"><?php echo e(format_date($topic['created_at'])); ?></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="newTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-modal-title>Nouveau sujet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="post" action="new-topic.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo e((string) $cat['id']); ?>" <?php echo !empty($cat['is_readonly']) && !is_admin() ? 'disabled' : ''; ?> <?php echo (int) $cat['id'] === (int) $categoryId ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?><?php echo !empty($cat['is_readonly']) ? ' (lecture seule)' : ''; ?>
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
                        <textarea class="form-control" id="newTopicContent" name="content" rows="6" placeholder="Votre message..." data-mentions="1" data-emotes="1" data-images="1"></textarea>
                    </div>
                    <div class="preview-box" id="newTopicPreview">Aperçu...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary" type="submit">Publier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const newTopicModal = document.getElementById('newTopicModal');
    if (newTopicModal) {
        newTopicModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            if (!button) return;
            const catId = button.getAttribute('data-category-id');
            const catName = button.getAttribute('data-category-name');
            const select = newTopicModal.querySelector('select[name="category_id"]');
            const title = newTopicModal.querySelector('[data-modal-title]');
            if (catId && select) {
                select.value = catId;
            }
            if (catName && title) {
                title.textContent = 'Nouveau sujet - ' + catName;
            }
        });
    }

    const topicContent = document.getElementById('newTopicContent');
    const topicPreview = document.getElementById('newTopicPreview');
    let topicTimer = null;
    function updateTopicPreview() {
        const body = new FormData();
        body.append('content', topicContent.value);
        fetch('preview.php', { method: 'POST', body })
            .then((r) => r.text())
            .then((html) => { topicPreview.innerHTML = html || 'Aperçu...'; })
            .catch(() => { topicPreview.textContent = 'Aperçu...'; });
    }
    if (topicContent) {
        topicContent.addEventListener('input', () => {
            clearTimeout(topicTimer);
            topicTimer = setTimeout(updateTopicPreview, 300);
        });
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
