<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';

$categories = [];

if ($pdo) {
    $categories = $pdo->query('SELECT id, name, description FROM categories ORDER BY sort_order, name')->fetchAll();
} else {
    $categories = [
        ['id' => 1, 'name' => 'Annonces', 'description' => 'Nouveautes et mises a jour.'],
        ['id' => 2, 'name' => 'Support', 'description' => 'Questions et aide technique.'],
        ['id' => 3, 'name' => 'Discussions', 'description' => 'Sujets libres.'],
    ];
}
?>
<h1 class="h4 mb-3">Toutes les categories</h1>
<div class="row g-3">
    <?php foreach ($categories as $category): ?>
        <div class="col-md-6">
            <div class="card shadow-sm card-category h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e($category['name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo e($category['description']); ?></p>
                    <a href="category.php?id=<?php echo e((string) $category['id']); ?>" class="btn btn-sm btn-outline-primary">Voir les sujets</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
