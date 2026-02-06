<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$categoryId = (int)($_GET['id'] ?? 0);
$category = null;
$topics = [];

require_db();
if ($categoryId) {
    $stmt = $pdo->prepare('SELECT id, name, description, is_readonly, is_pinned FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

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
    <p class="text-muted"><?php echo e($category['description']); ?></p>
</section>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Sujets</strong>
        <?php if (empty($category['is_readonly'])): ?>
            <a class="btn btn-sm btn-primary" href="new-topic.php">Nouveau sujet</a>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
