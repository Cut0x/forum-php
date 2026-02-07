<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

require_db();
$categories = $pdo->query('SELECT c.id, c.name, c.description, c.is_pinned, c.is_readonly, COUNT(t.id) AS topic_count
    FROM categories c
    LEFT JOIN topics t ON t.category_id = c.id AND t.deleted_at IS NULL
    GROUP BY c.id
    ORDER BY c.is_pinned DESC, c.sort_order, c.name')->fetchAll();
?>
<h1 class="h4 mb-3">Toutes les catégories</h1>
<div class="row g-3">
    <?php foreach ($categories as $category): ?>
        <div class="col-md-6">
            <div class="card shadow-sm card-category h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo e($category['name']); ?>
                        <?php if (!empty($category['is_pinned'])): ?>
                            <i class="bi bi-pin-angle-fill text-warning ms-1" title="Épinglée"></i>
                        <?php endif; ?>
                    </h5>
                    <?php $count = (int) $category['topic_count']; ?>
                    <p class="text-muted mb-1"><?php echo e((string) $count); ?> sujet<?php echo $count > 1 ? 's' : ''; ?> -</p>
                    <p class="text-muted mb-3"><?php echo e($category['description']); ?></p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="categorie.php?id=<?php echo e((string) $category['id']); ?>" class="btn btn-sm btn-outline-primary">Voir les sujets</a>
                        <?php if (is_logged_in() && (empty($category['is_readonly']) || is_admin())): ?>
                            <button class="btn btn-sm btn-primary" type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#newTopicModal"
                                    data-category-id="<?php echo e((string) $category['id']); ?>"
                                    data-category-name="<?php echo e($category['name']); ?>">
                                Nouveau sujet
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
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
