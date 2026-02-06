<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
require_db();

$categories = $pdo->query('SELECT id, name, description FROM categories ORDER BY sort_order, name')->fetchAll();
$topics = $pdo->query('SELECT t.id, t.title, t.created_at, c.name AS category_name FROM topics t JOIN categories c ON c.id = t.category_id WHERE t.deleted_at IS NULL ORDER BY t.created_at DESC LIMIT 5')->fetchAll();
?>
<section class="bg-white p-4 rounded shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Forum</h1>
            <p class="text-muted mb-0">Discussions, entraide et annonces.</p>
        </div>
        <div class="text-md-end">
            <a class="btn btn-primary" href="new-topic.php"><i class="bi bi-plus-circle me-1"></i>Nouveau sujet</a>
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
                    <a class="list-group-item list-group-item-action card-category" href="category.php?id=<?php echo e((string) $category['id']); ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?php echo e($category['name']); ?></h6>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
