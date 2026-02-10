<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
require_db();

$q = trim((string) ($_GET['q'] ?? ''));
$results = [];
$matches = 0;

if ($q !== '') {
    $needle = '%' . $q . '%';
    $stmt = $pdo->prepare('SELECT DISTINCT t.id, t.title, t.created_at, c.name AS category_name
        FROM topics t
        JOIN categories c ON c.id = t.category_id
        LEFT JOIN posts p ON p.topic_id = t.id
        WHERE t.deleted_at IS NULL AND (t.title LIKE ? OR p.content LIKE ?)
        ORDER BY t.created_at DESC
        LIMIT 50');
    $stmt->execute([$needle, $needle]);
    $results = $stmt->fetchAll();
    $matches = count($results);
}
?>

<section class="bg-white p-4 rounded shadow-sm mb-4">
    <div class="d-flex flex-column gap-2">
        <h1 class="h4 mb-0">Recherche</h1>
        <form method="get" action="search.php" class="d-flex flex-column flex-md-row gap-2">
            <input class="form-control" type="search" name="q" placeholder="Rechercher un sujet, un message..." value="<?php echo e($q); ?>">
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </form>
        <?php if ($q !== ''): ?>
            <div class="text-muted small"><?php echo e((string) $matches); ?> résultat<?php echo $matches > 1 ? 's' : ''; ?> pour “<?php echo e($q); ?>”.</div>
        <?php endif; ?>
    </div>
</section>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <strong>Résultats</strong>
    </div>
    <div class="list-group list-group-flush">
        <?php if ($q === ''): ?>
            <div class="list-group-item text-muted">Entrez une recherche pour afficher des résultats.</div>
        <?php elseif (!$results): ?>
            <div class="list-group-item text-muted">Aucun résultat.</div>
        <?php else: ?>
            <?php foreach ($results as $row): ?>
                <a class="list-group-item list-group-item-action" href="topic.php?id=<?php echo e((string) $row['id']); ?>">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1"><?php echo e($row['title']); ?></h6>
                            <small class="text-muted"><?php echo e($row['category_name']); ?></small>
                        </div>
                        <small class="text-muted"><?php echo e(format_date($row['created_at'])); ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
