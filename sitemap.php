<?php
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = $config['app']['base_url'] ?? '';
if (!$base) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
}

$urls = [
    ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => $base . '/categories.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
];

if ($pdo) {
    $cats = $pdo->query('SELECT id FROM categories')->fetchAll();
    foreach ($cats as $c) {
        $urls[] = ['loc' => $base . '/categorie.php?id=' . $c['id'], 'priority' => '0.7', 'changefreq' => 'weekly'];
    }

    $topics = $pdo->query('SELECT id, created_at, edited_at FROM topics WHERE deleted_at IS NULL')->fetchAll();
    foreach ($topics as $t) {
        $lastmod = $t['edited_at'] ?: $t['created_at'];
        $lastmod = $lastmod ? date('c', strtotime($lastmod)) : null;
        $urls[] = ['loc' => $base . '/topic.php?id=' . $t['id'], 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $lastmod];
    }
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?php echo e($url['loc']); ?></loc>
        <?php if (!empty($url['lastmod'])): ?>
            <lastmod><?php echo e($url['lastmod']); ?></lastmod>
        <?php endif; ?>
        <changefreq><?php echo e($url['changefreq']); ?></changefreq>
        <priority><?php echo e($url['priority']); ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
