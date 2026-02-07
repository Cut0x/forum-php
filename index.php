<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
require_db();

$categories = $pdo->query('SELECT c.id, c.name, c.description, c.is_pinned, c.is_readonly, COUNT(t.id) AS topic_count
    FROM categories c
    LEFT JOIN topics t ON t.category_id = c.id AND t.deleted_at IS NULL
    GROUP BY c.id
    ORDER BY c.is_pinned DESC, c.sort_order, c.name')->fetchAll();
$topics = $pdo->query('SELECT t.id, t.title, t.created_at, c.name AS category_name FROM topics t JOIN categories c ON c.id = t.category_id WHERE t.deleted_at IS NULL ORDER BY t.created_at DESC LIMIT 5')->fetchAll();
?>
<section class="bg-white p-4 rounded shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Forum</h1>
            <p class="text-muted mb-0">Discussions, entraide et annonces.</p>
        </div>
        <div class="text-md-end">
            <?php if (is_logged_in()): ?>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newTopicModal">
                    <i class="bi bi-plus-circle me-1"></i>Nouveau sujet
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Catégories</strong>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($categories as $category): ?>
                    <a class="list-group-item list-group-item-action card-category" href="categorie.php?id=<?php echo e((string) $category['id']); ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">
                                    <?php echo e($category['name']); ?>
                                    <?php if (!empty($category['is_pinned'])): ?>
                                        <i class="bi bi-pin-angle-fill text-warning ms-1" title="Épinglée"></i>
                                    <?php endif; ?>
                                </h6>
                                <?php $count = (int) $category['topic_count']; ?>
                                <small class="text-muted"><?php echo e((string) $count); ?> sujet<?php echo $count > 1 ? 's' : ''; ?> -</small>
                                <small class="text-muted"><?php echo e($category['description']); ?></small>
                            </div>
                            <span class="text-muted">Voir</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Derniers sujets</strong>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($topics as $topic): ?>
                    <a class="list-group-item list-group-item-action" href="topic.php?id=<?php echo e((string) $topic['id']); ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?php echo e($topic['title']); ?></h6>
                                <small class="text-muted"><?php echo e($topic['category_name']); ?></small>
                            </div>
                            <small class="text-muted"><?php echo e(format_date($topic['created_at'])); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau sujet</h5>
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
