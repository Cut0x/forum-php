<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$categories = [];
$topics = [];

if ($pdo) {
    $categories = $pdo->query('SELECT id, name, description FROM categories ORDER BY sort_order, name')->fetchAll();
    $topics = $pdo->query('SELECT t.id, t.title, t.created_at, c.name AS category_name FROM topics t JOIN categories c ON c.id = t.category_id ORDER BY t.created_at DESC LIMIT 5')->fetchAll();
} else {
    $categories = [
        ['id' => 1, 'name' => 'Annonces', 'description' => 'Nouveautes et mises a jour.'],
        ['id' => 2, 'name' => 'Support', 'description' => 'Questions et aide technique.'],
        ['id' => 3, 'name' => 'Discussions', 'description' => 'Sujets libres.'],
    ];

    $topics = [
        ['id' => 1, 'title' => 'Bienvenue sur le forum', 'created_at' => '2026-02-05 10:15:00', 'category_name' => 'Annonces'],
        ['id' => 2, 'title' => 'Comment configurer le projet ?', 'created_at' => '2026-02-04 18:30:00', 'category_name' => 'Support'],
    ];
}
?>
<section class="bg-white p-4 rounded shadow-sm mb-4">
    <h1 class="h3 mb-2">Forum PHP open-source</h1>
    <p class="text-muted mb-0">Template Bootstrap + MySQL (PDO). Systeme de badges, profils utilisateurs, bios et liens.</p>
</section>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Categories</strong>
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

<?php if (!$pdo): ?>
    <div class="alert alert-warning mt-4">
        <strong>Configuration manquante :</strong> Copiez `exemple.config.php` vers `config.php` puis importez `schema.sql`.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
