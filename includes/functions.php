<?php
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_date(?string $date): string
{
    if (!$date) {
        return '—';
    }

    $dt = new DateTime($date);
    return $dt->format('d/m/Y H:i');
}

function asset(string $path): string
{
    return $path;
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function current_username(): ?string
{
    return $_SESSION['username'] ?? null;
}

function current_user_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function current_theme(): string
{
    return $_SESSION['theme'] ?? 'light';
}

function is_admin(): bool
{
    return current_user_role() === 'admin';
}

function render_markdown(string $text): string
{
    $parser = new Parsedown();
    return $parser->text($text);
}

function render_markdown_with_mentions(PDO $pdo, string $text): string
{
    $html = render_markdown($text);
    preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $text, $matches);
    $usernames = array_unique($matches[1] ?? []);
    if (!$usernames) {
        return $html;
    }

    $placeholders = implode(',', array_fill(0, count($usernames), '?'));
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username IN ($placeholders)");
    $stmt->execute($usernames);
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[$row['username']] = (int) $row['id'];
    }

    return preg_replace_callback('/@([a-zA-Z0-9_]{3,30})/', function ($m) use ($map) {
        $name = $m[1];
        if (!isset($map[$name])) {
            return '@' . $name;
        }
        $id = $map[$name];
        return '<a href="profile.php?id=' . $id . '" class="text-decoration-none">@' . e($name) . '</a>';
    }, $html);
}

function get_setting(PDO $pdo, string $key, ?string $default = null): ?string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

function db_error_message(): string
{
    global $dbError;
    return $dbError ? ('Erreur BDD: ' . $dbError) : 'Erreur BDD: connexion impossible.';
}

function ensure_defaults(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO categories (name, description, sort_order) VALUES (?, ?, ?)');
        $stmt->execute(['Annonces', 'Nouveautes et mises a jour.', 1]);
        $stmt->execute(['Support', 'Questions et aide technique.', 2]);
        $stmt->execute(['Discussions', 'Sujets libres.', 3]);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM badges')->fetchColumn();
    if ($count === 0) {
        $badges = [
            ['Fondateur', 'founder', 'assets/badges/founder.png', '#4f8cff'],
            ['Admin', 'admin', 'assets/badges/admin.png', '#ff4d4f'],
            ['Moderateur', 'moderator', 'assets/badges/moderator.png', '#7c5cff'],
            ['Contributeur', 'contributor', 'assets/badges/contributor.png', '#16a34a'],
            ['Premier message', 'starter', 'assets/badges/starter.png', '#4f8cff'],
            ['10 messages', 'writer', 'assets/badges/writer.png', '#00d1b2'],
            ['25 messages', 'speaker', 'assets/badges/speaker.png', '#ffb020'],
            ['50 messages', 'veteran', 'assets/badges/veteran.png', '#7c5cff'],
            ['Premier sujet', 'first_topic', 'assets/badges/founder.png', '#ff4d4f'],
            ['10 sujets', 'topics_10', 'assets/badges/founder.png', '#ff4d4f'],
            ['Donator', 'donator', 'assets/badges/donator.png', '#00c2ff'],
        ];
        $stmt = $pdo->prepare('INSERT INTO badges (name, code, icon, color) VALUES (?, ?, ?, ?)');
        foreach ($badges as $b) {
            $stmt->execute($b);
        }
    }

    $defaults = [
        'footer_text' => 'Forum',
        'footer_link' => '',
        'stripe_enabled' => '0',
        'stripe_url' => '',
        'theme_light_bg' => '#f1f5f9',
        'theme_light_surface' => '#ffffff',
        'theme_light_text' => '#0f172a',
        'theme_light_muted' => '#64748b',
        'theme_light_primary' => '#4f8cff',
        'theme_light_accent' => '#00d1b2',
        'theme_dark_bg' => '#0b1220',
        'theme_dark_surface' => '#0f172a',
        'theme_dark_text' => '#e2e8f0',
        'theme_dark_muted' => '#94a3b8',
        'theme_dark_primary' => '#4f8cff',
        'theme_dark_accent' => '#00d1b2',
        'theme_font' => '\"Space Grotesk\", system-ui, -apple-system, Segoe UI, sans-serif',
    ];
    foreach ($defaults as $key => $value) {
        if (get_setting($pdo, $key) === null) {
            set_setting($pdo, $key, $value);
        }
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM footer_categories')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO footer_categories (name, sort_order) VALUES (?, ?)');
        $stmt->execute(['Utiles', 1]);
        $stmt->execute(['Ressources', 2]);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM footer_links')->fetchColumn();
    if ($count === 0) {
        $cats = $pdo->query('SELECT id, name FROM footer_categories')->fetchAll();
        $catMap = [];
        foreach ($cats as $c) {
            $catMap[$c['name']] = (int) $c['id'];
        }
        $stmt = $pdo->prepare('INSERT INTO footer_links (category_id, label, url, sort_order) VALUES (?, ?, ?, ?)');
        if (isset($catMap['Utiles'])) {
            $stmt->execute([$catMap['Utiles'], 'Accueil', '/', 1]);
            $stmt->execute([$catMap['Utiles'], 'Contact', '#', 2]);
        }
        if (isset($catMap['Ressources'])) {
            $stmt->execute([$catMap['Ressources'], 'Documentation', '#', 1]);
            $stmt->execute([$catMap['Ressources'], 'GitHub', 'https://github.com/', 2]);
        }
    }
}

function require_db(): void
{
    global $pdo;
    if (!$pdo) {
        echo '<div class="alert alert-danger">' . e(db_error_message()) . '</div>';
        require __DIR__ . '/footer.php';
        exit;
    }
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    $stmt->execute([$key, $value]);
}

function create_notification(PDO $pdo, int $userId, string $type, string $message, ?int $topicId = null, ?int $postId = null): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, message, topic_id, post_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $type, $message, $topicId, $postId]);

    $mailEnabled = ($_ENV['MAIL_ENABLED'] ?? '0') === '1';
    if ($mailEnabled) {
        $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $email = $stmt->fetchColumn();
        if ($email) {
            send_mail($email, 'Notification', '<p>' . e($message) . '</p>');
        }
    }
}

function parse_mentions(string $content): array
{
    preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $content, $matches);
    return array_unique($matches[1] ?? []);
}

function award_badges(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
    $stmt->execute([$userId]);
    $postCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM topics WHERE user_id = ?');
    $stmt->execute([$userId]);
    $topicCount = (int) $stmt->fetchColumn();

    $badgeMap = [
        ['code' => 'starter', 'min_posts' => 1, 'min_topics' => 0],
        ['code' => 'writer', 'min_posts' => 10, 'min_topics' => 0],
        ['code' => 'speaker', 'min_posts' => 25, 'min_topics' => 0],
        ['code' => 'veteran', 'min_posts' => 50, 'min_topics' => 0],
        ['code' => 'first_topic', 'min_posts' => 0, 'min_topics' => 1],
        ['code' => 'topics_10', 'min_posts' => 0, 'min_topics' => 10],
        ['code' => 'contributor', 'min_posts' => 5, 'min_topics' => 1],
    ];

    $stmt = $pdo->prepare('SELECT id, code FROM badges');
    $stmt->execute();
    $badges = $stmt->fetchAll();
    $badgeIndex = [];
    foreach ($badges as $badge) {
        $badgeIndex[$badge['code']] = (int) $badge['id'];
    }

    foreach ($badgeMap as $rule) {
        $ok = ($postCount >= $rule['min_posts']) && ($topicCount >= $rule['min_topics']);
        if ($ok && isset($badgeIndex[$rule['code']])) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
            $stmt->execute([$userId, $badgeIndex[$rule['code']]]);
        }
    }
}

function sync_role_badges(PDO $pdo, int $userId, ?string $role): void
{
    $roleCodes = [
        'admin' => 'admin',
        'moderator' => 'moderator',
    ];

    if (!$role) {
        return;
    }

    $stmt = $pdo->prepare('SELECT id, code FROM badges WHERE code IN (?, ?)');
    $stmt->execute(['admin', 'moderator']);
    $badgeIndex = [];
    foreach ($stmt->fetchAll() as $badge) {
        $badgeIndex[$badge['code']] = (int) $badge['id'];
    }

    if (isset($roleCodes[$role]) && isset($badgeIndex[$roleCodes[$role]])) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
        $stmt->execute([$userId, $badgeIndex[$roleCodes[$role]]]);
    }
}

function send_mail(string $to, string $subject, string $html): bool
{
    if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'localhost';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'] ?? '';
        $mail->Password = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 587);
        $mail->setFrom($_ENV['SMTP_FROM'] ?? 'no-reply@example.com', 'Forum');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function role_badge_class(?string $role): string
{
    return match ($role) {
        'admin' => 'role-badge role-admin',
        'moderator' => 'role-badge role-moderator',
        default => 'role-badge role-member',
    };
}

function role_label(?string $role): string
{
    return match ($role) {
        'admin' => 'Admin',
        'moderator' => 'Modo',
        default => 'Membre',
    };
}

function ensure_config(): bool
{
    global $config;

    return is_array($config);
}
