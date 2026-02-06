<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/includes/header.php';
require_db();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'member';
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $userId]);
        sync_role_badges($pdo, $userId, $role);
        $message = 'Rôle mis à jour.';
    }

    if ($action === 'badge') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $badgeId = (int) ($_POST['badge_id'] ?? 0);
        $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
        $stmt->execute([$userId, $badgeId]);
        $message = 'Badge ajouté.';
    }

    if ($action === 'badge_remove') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $badgeId = (int) ($_POST['badge_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM user_badges WHERE user_id = ? AND badge_id = ?');
        $stmt->execute([$userId, $badgeId]);
        $message = 'Badge retiré.';
    }

    if ($action === 'badge_create') {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $color = trim($_POST['color'] ?? '#0d6efd');
        if ($name && $code && $icon) {
            $stmt = $pdo->prepare('INSERT INTO badges (name, code, icon, color) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $code, $icon, $color]);
            $message = 'Badge créé.';
        }
    }

    if ($action === 'footer_category_add') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $stmt = $pdo->prepare('INSERT INTO footer_categories (name, sort_order) VALUES (?, 0)');
            $stmt->execute([$name]);
        }
    }

    if ($action === 'footer_category_delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM footer_categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    if ($action === 'footer_link_add') {
        $cat = (int) ($_POST['category_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $url = trim($_POST['url'] ?? '');
        if ($cat && $label && $url) {
            $stmt = $pdo->prepare('INSERT INTO footer_links (category_id, label, url, sort_order) VALUES (?, ?, ?, 0)');
            $stmt->execute([$cat, $label, $url]);
        }
    }

    if ($action === 'footer_link_delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM footer_links WHERE id = ?');
        $stmt->execute([$id]);
    }

    if ($action === 'category_add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $readonly = isset($_POST['is_readonly']) ? 1 : 0;
        if ($name && $description) {
            $stmt = $pdo->prepare('INSERT INTO categories (name, description, sort_order, is_readonly) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $description, 0, $readonly]);
            $message = 'Catégorie créée.';
        }
    }

    if ($action === 'category_edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $readonly = isset($_POST['is_readonly']) ? 1 : 0;
        if ($id && $name && $description) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ?, is_readonly = ? WHERE id = ?');
            $stmt->execute([$name, $description, $readonly, $id]);
            $message = 'Catégorie mise à jour.';
        }
    }

    if ($action === 'category_delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Catégorie supprimée.';
        }
    }

    if ($action === 'settings') {
        set_setting($pdo, 'site_title', trim($_POST['site_title'] ?? 'Forum PHP'));
        set_setting($pdo, 'site_description', trim($_POST['site_description'] ?? 'Forum communautaire.'));
        set_setting($pdo, 'footer_text', trim($_POST['footer_text'] ?? 'Forum PHP'));
        set_setting($pdo, 'footer_link', trim($_POST['footer_link'] ?? ''));
        set_setting($pdo, 'stripe_enabled', isset($_POST['stripe_enabled']) ? '1' : '0');
        set_setting($pdo, 'stripe_url', trim($_POST['stripe_url'] ?? ''));
        $message = 'Paramètres mis à jour.';
    }

    if ($action === 'theme') {
        $pairs = [
            'theme_light_bg', 'theme_light_surface', 'theme_light_text', 'theme_light_muted', 'theme_light_primary', 'theme_light_accent',
            'theme_dark_bg', 'theme_dark_surface', 'theme_dark_text', 'theme_dark_muted', 'theme_dark_primary', 'theme_dark_accent',
            'theme_font',
        ];
        foreach ($pairs as $key) {
            if (isset($_POST[$key])) {
                set_setting($pdo, $key, trim($_POST[$key]));
            }
        }
        set_setting($pdo, 'theme_version', (string) time());
        $message = 'Thème mis à jour.';
    }

    if ($action === 'theme_preset') {
        $preset = $_POST['preset'] ?? '';
        $presets = theme_presets();
        if (isset($presets[$preset])) {
            foreach ($presets[$preset] as $key => $value) {
                set_setting($pdo, $key, $value);
            }
            set_setting($pdo, 'theme_version', (string) time());
            $message = 'Preset applique.';
        }
    }

    if ($action === 'theme_reset') {
        $defaults = default_settings();
        foreach ($defaults as $key => $value) {
            if (str_starts_with($key, 'theme_')) {
                set_setting($pdo, $key, $value);
            }
        }
        set_setting($pdo, 'theme_version', (string) time());
        $message = 'Thème réinitialisé.';
    }
}

$users = $pdo->query('SELECT id, username, role FROM users ORDER BY username')->fetchAll();
$badges = $pdo->query('SELECT id, name, code, icon, color FROM badges ORDER BY name')->fetchAll();
$siteTitle = get_setting($pdo, 'site_title', 'Forum PHP');
$siteDescription = get_setting($pdo, 'site_description', 'Forum communautaire.');
$footerText = get_setting($pdo, 'footer_text', 'Forum PHP');
$footerLink = get_setting($pdo, 'footer_link', '');
$stripeEnabled = get_setting($pdo, 'stripe_enabled', '0');
$stripeUrl = get_setting($pdo, 'stripe_url', '');
$footerCategories = $pdo->query('SELECT id, name FROM footer_categories ORDER BY sort_order, name')->fetchAll();
$footerLinks = $pdo->query('SELECT id, category_id, label, url FROM footer_links ORDER BY sort_order, label')->fetchAll();
$categories = $pdo->query('SELECT id, name, description, is_readonly FROM categories ORDER BY sort_order, name')->fetchAll();
$theme = [
    'theme_light_bg' => get_setting($pdo, 'theme_light_bg', '#f1f5f9'),
    'theme_light_surface' => get_setting($pdo, 'theme_light_surface', '#ffffff'),
    'theme_light_text' => get_setting($pdo, 'theme_light_text', '#0f172a'),
    'theme_light_muted' => get_setting($pdo, 'theme_light_muted', '#64748b'),
    'theme_light_primary' => get_setting($pdo, 'theme_light_primary', '#4f8cff'),
    'theme_light_accent' => get_setting($pdo, 'theme_light_accent', '#00d1b2'),
    'theme_dark_bg' => get_setting($pdo, 'theme_dark_bg', '#0b1220'),
    'theme_dark_surface' => get_setting($pdo, 'theme_dark_surface', '#0f172a'),
    'theme_dark_text' => get_setting($pdo, 'theme_dark_text', '#e2e8f0'),
    'theme_dark_muted' => get_setting($pdo, 'theme_dark_muted', '#94a3b8'),
    'theme_dark_primary' => get_setting($pdo, 'theme_dark_primary', '#4f8cff'),
    'theme_dark_accent' => get_setting($pdo, 'theme_dark_accent', '#00d1b2'),
    'theme_font' => get_setting($pdo, 'theme_font', '"Space Grotesk", system-ui, -apple-system, Segoe UI, sans-serif'),
];
?>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="card admin-card shadow-sm p-3">
            <div class="fw-semibold mb-2">Admin</div>
            <nav class="nav flex-column admin-nav">
                <a class="nav-link" href="#section-theme">Thème</a>
                <a class="nav-link" href="#section-settings">Paramètres</a>
                <a class="nav-link" href="#section-categories">Catégories</a>
                <a class="nav-link" href="#section-footer">Footer</a>
                <a class="nav-link" href="#section-badges">Badges</a>
                <a class="nav-link" href="#section-users">Utilisateurs</a>
            </nav>
            <?php if ($message): ?>
                <div class="alert alert-success py-2 mt-3 mb-0"><?php echo e($message); ?></div>
            <?php endif; ?>
        </div>
    </aside>
    <div>
        <section id="section-theme" class="card admin-card shadow-sm mb-4">
            <div class="card-header bg-white">Thème</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <form method="post">
                        <input type="hidden" name="action" value="theme_preset">
                        <input type="hidden" name="preset" value="amber_teal">
                        <button class="btn btn-outline-primary" type="submit">Amber / Teal</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="action" value="theme_preset">
                        <input type="hidden" name="preset" value="slate_mint">
                        <button class="btn btn-outline-primary" type="submit">Slate / Mint</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="action" value="theme_preset">
                        <input type="hidden" name="preset" value="sand_rose">
                        <button class="btn btn-outline-primary" type="submit">Sand / Rose</button>
                    </form>
                    <form method="post" class="ms-auto">
                        <input type="hidden" name="action" value="theme_reset">
                        <button class="btn btn-outline-secondary" type="submit">Reset</button>
                    </form>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="theme">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>Light</h6>
                            <label class="form-label">Background</label>
                            <input class="form-control" name="theme_light_bg" value="<?php echo e($theme['theme_light_bg']); ?>">
                            <label class="form-label mt-2">Surface</label>
                            <input class="form-control" name="theme_light_surface" value="<?php echo e($theme['theme_light_surface']); ?>">
                            <label class="form-label mt-2">Text</label>
                            <input class="form-control" name="theme_light_text" value="<?php echo e($theme['theme_light_text']); ?>">
                            <label class="form-label mt-2">Muted</label>
                            <input class="form-control" name="theme_light_muted" value="<?php echo e($theme['theme_light_muted']); ?>">
                            <label class="form-label mt-2">Primary</label>
                            <input class="form-control" name="theme_light_primary" value="<?php echo e($theme['theme_light_primary']); ?>">
                            <label class="form-label mt-2">Accent</label>
                            <input class="form-control" name="theme_light_accent" value="<?php echo e($theme['theme_light_accent']); ?>">
                        </div>
                        <div class="col-md-6">
                            <h6>Dark</h6>
                            <label class="form-label">Background</label>
                            <input class="form-control" name="theme_dark_bg" value="<?php echo e($theme['theme_dark_bg']); ?>">
                            <label class="form-label mt-2">Surface</label>
                            <input class="form-control" name="theme_dark_surface" value="<?php echo e($theme['theme_dark_surface']); ?>">
                            <label class="form-label mt-2">Text</label>
                            <input class="form-control" name="theme_dark_text" value="<?php echo e($theme['theme_dark_text']); ?>">
                            <label class="form-label mt-2">Muted</label>
                            <input class="form-control" name="theme_dark_muted" value="<?php echo e($theme['theme_dark_muted']); ?>">
                            <label class="form-label mt-2">Primary</label>
                            <input class="form-control" name="theme_dark_primary" value="<?php echo e($theme['theme_dark_primary']); ?>">
                            <label class="form-label mt-2">Accent</label>
                            <input class="form-control" name="theme_dark_accent" value="<?php echo e($theme['theme_dark_accent']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Font</label>
                            <input class="form-control" name="theme_font" value="<?php echo e($theme['theme_font']); ?>">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Enregistrer</button>
                </form>
            </div>
        </section>

        <section id="section-settings" class="card admin-card shadow-sm mb-4">
            <div class="card-header bg-white">Paramètres</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="settings">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Titre du site</label>
                            <input class="form-control" name="site_title" value="<?php echo e($siteTitle); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input class="form-control" name="site_description" value="<?php echo e($siteDescription); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer texte</label>
                            <input class="form-control" name="footer_text" value="<?php echo e($footerText); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer lien</label>
                            <input class="form-control" name="footer_link" value="<?php echo e($footerLink); ?>">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="stripe_enabled" id="stripe_enabled" <?php echo $stripeEnabled === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="stripe_enabled">Stripe actif</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stripe URL</label>
                            <input class="form-control" name="stripe_url" value="<?php echo e($stripeUrl); ?>" placeholder="https://buy.stripe.com/...">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Enregistrer</button>
                </form>
            </div>
        </section>

        <section id="section-categories" class="card admin-card shadow-sm mb-4">
            <div class="card-header bg-white">Catégories</div>
            <div class="card-body">
                <form method="post" class="mb-4">
                    <input type="hidden" name="action" value="category_add">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nom</label>
                            <input class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input class="form-control" name="description" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_readonly" id="is_readonly">
                                <label class="form-check-label" for="is_readonly">Lecture seule</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Créer</button>
                </form>

                <div class="row g-3">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($cat['name']); ?></div>
                                        <div class="text-muted small"><?php echo e($cat['description']); ?></div>
                                        <?php if (!empty($cat['is_readonly'])): ?>
                                            <span class="badge bg-secondary mt-2">Lecture seule</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-cat-<?php echo e((string) $cat['id']); ?>">Éditer</button>
                                        <form method="post">
                                            <input type="hidden" name="action" value="category_delete">
                                            <input type="hidden" name="id" value="<?php echo e((string) $cat['id']); ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="collapse mt-3" id="edit-cat-<?php echo e((string) $cat['id']); ?>">
                                    <form method="post">
                                        <input type="hidden" name="action" value="category_edit">
                                        <input type="hidden" name="id" value="<?php echo e((string) $cat['id']); ?>">
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <input class="form-control" name="name" value="<?php echo e($cat['name']); ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <input class="form-control" name="description" value="<?php echo e($cat['description']); ?>">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_readonly" id="readonly-<?php echo e((string) $cat['id']); ?>" <?php echo !empty($cat['is_readonly']) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="readonly-<?php echo e((string) $cat['id']); ?>">Lecture seule</label>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm mt-2" type="submit">Enregistrer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="section-footer" class="card admin-card shadow-sm mb-4">
            <div class="card-header bg-white">Footer</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="footer_category_add">
                            <label class="form-label">Catégorie</label>
                            <div class="d-flex gap-2">
                                <input class="form-control" name="name" placeholder="Utiles">
                                <button class="btn btn-outline-primary" type="submit">Ajouter</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="post">
                            <input type="hidden" name="action" value="footer_link_add">
                            <label class="form-label">Lien</label>
                            <select class="form-select mb-2" name="category_id">
                                <?php foreach ($footerCategories as $cat): ?>
                                    <option value="<?php echo e((string) $cat['id']); ?>"><?php echo e($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input class="form-control mb-2" name="label" placeholder="Documentation">
                            <input class="form-control mb-2" name="url" placeholder="https://">
                            <button class="btn btn-outline-primary" type="submit">Ajouter</button>
                        </form>
                    </div>
                </div>
                <div class="mt-3">
                    <?php foreach ($footerCategories as $cat): ?>
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><?php echo e($cat['name']); ?></strong>
                                <form method="post">
                                    <input type="hidden" name="action" value="footer_category_delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) $cat['id']); ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </div>
                            <div class="mt-2">
                                <?php foreach ($footerLinks as $link): ?>
                                    <?php if ((int) $link['category_id'] === (int) $cat['id']): ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo e($link['label']); ?></span>
                                            <form method="post">
                                                <input type="hidden" name="action" value="footer_link_delete">
                                                <input type="hidden" name="id" value="<?php echo e((string) $link['id']); ?>">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">Retirer</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="section-badges" class="card admin-card shadow-sm mb-4">
            <div class="card-header bg-white">Badges</div>
            <div class="card-body">
                <form method="post" class="mb-4">
                    <input type="hidden" name="action" value="badge_create">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nom</label>
                            <input class="form-control" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Code</label>
                            <input class="form-control" name="code" placeholder="starter" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Icon</label>
                            <input class="form-control" name="icon" placeholder="assets/badges/starter.png" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Couleur</label>
                            <input class="form-control" name="color" value="#0d6efd">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Ajouter</button>
                </form>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($badges as $badge): ?>
                        <div class="d-flex align-items-center gap-2 border rounded px-2 py-1">
                            <img class="badge-icon" src="<?php echo e($badge['icon']); ?>" alt="badge">
                            <div>
                                <div class="fw-semibold"><?php echo e($badge['name']); ?></div>
                                <small class="text-muted"><?php echo e($badge['code']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="section-users" class="card admin-card shadow-sm">
            <div class="card-header bg-white">Utilisateurs</div>
            <div class="card-body">
                <?php foreach ($users as $user): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div class="fw-semibold"><?php echo e($user['username']); ?></div>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="action" value="role">
                                <input type="hidden" name="user_id" value="<?php echo e((string) $user['id']); ?>">
                                <select class="form-select" name="role">
                                    <option value="member" <?php echo $user['role'] === 'member' ? 'selected' : ''; ?>>Membre</option>
                                    <option value="moderator" <?php echo $user['role'] === 'moderator' ? 'selected' : ''; ?>>Modo</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button class="btn btn-outline-primary" type="submit">OK</button>
                            </form>
                        </div>
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <?php foreach ($badges as $badge): ?>
                                <form method="post" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="user_id" value="<?php echo e((string) $user['id']); ?>">
                                    <input type="hidden" name="badge_id" value="<?php echo e((string) $badge['id']); ?>">
                                    <img class="badge-icon" src="<?php echo e($badge['icon']); ?>" alt="badge">
                                    <button class="btn btn-sm btn-outline-primary" type="submit" name="action" value="badge">+</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="submit" name="action" value="badge_remove">-</button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
