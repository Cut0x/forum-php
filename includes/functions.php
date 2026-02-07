<?php
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function normalize_username(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $lower = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $ascii = $lower;
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $lower);
        if ($conv !== false) {
            $ascii = $conv;
        }
    }

    $ascii = strtr($ascii, [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'œ' => 'oe', 'æ' => 'ae',
    ]);

    $ascii = preg_replace('/[^a-z0-9_]+/', '_', $ascii);
    $ascii = trim($ascii, '_');
    $ascii = preg_replace('/_+/', '_', $ascii);

    return $ascii;
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

function sanitize_redirect(?string $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }
    if (preg_match("/[\r\n]/", $value)) {
        return null;
    }
    if (str_starts_with($value, '\\') || str_starts_with($value, '//')) {
        return null;
    }
    $parts = parse_url($value);
    if ($parts === false) {
        return null;
    }
    if (isset($parts['scheme']) || isset($parts['host'])) {
        return null;
    }
    return $value;
}

function login_redirect_target(string $fallback = 'login.php'): string
{
    $redirect = sanitize_redirect($_SERVER['REQUEST_URI'] ?? '');
    if ($redirect) {
        return $fallback . '?redirect=' . rawurlencode($redirect);
    }
    return $fallback;
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function current_username(): ?string
{
    return $_SESSION['username'] ?? null;
}

function current_user_name(): ?string
{
    return $_SESSION['name'] ?? null;
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

function get_emotes(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    try {
        $stmt = $pdo->query('SELECT name, file, title FROM emotes WHERE is_enabled = 1 ORDER BY name');
        $rows = $stmt->fetchAll();
    } catch (Throwable $e) {
        $cache = [];
        return $cache;
    }

    $map = [];
    foreach ($rows as $row) {
        $name = trim((string) ($row['name'] ?? ''));
        $file = trim((string) ($row['file'] ?? ''));
        if ($name === '' || $file === '') {
            continue;
        }
        $map[$name] = [
            'file' => $file,
            'title' => (string) ($row['title'] ?? ''),
        ];
    }
    $cache = $map;
    return $cache;
}

function node_is_in_tags(DOMNode $node, array $tags): bool
{
    $tags = array_map('strtolower', $tags);
    $current = $node;
    while ($current) {
        if ($current->nodeType === XML_ELEMENT_NODE) {
            $name = strtolower($current->nodeName);
            if (in_array($name, $tags, true)) {
                return true;
            }
        }
        $current = $current->parentNode;
    }
    return false;
}

function apply_emotes_to_html(PDO $pdo, string $html): string
{
    $emotes = get_emotes($pdo);
    if (!$emotes) {
        return $html;
    }

    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="UTF-8" ?><div>' . $html . '</div>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($prev);

    $pattern = '/:([a-zA-Z0-9_+-]{2,50}):/';
    $xpath = new DOMXPath($doc);
    $textNodes = $xpath->query('//text()');
    if (!$textNodes) {
        return $html;
    }

    foreach ($textNodes as $textNode) {
        if (node_is_in_tags($textNode, ['code', 'pre'])) {
            continue;
        }

        $value = $textNode->nodeValue;
        if (!preg_match($pattern, $value)) {
            continue;
        }

        $parts = preg_split($pattern, $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false || $parts === null) {
            continue;
        }

        $fragment = $doc->createDocumentFragment();
        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                if ($part !== '') {
                    $fragment->appendChild($doc->createTextNode($part));
                }
                continue;
            }

            $name = $part;
            if (!isset($emotes[$name])) {
                $fragment->appendChild($doc->createTextNode(':' . $name . ':'));
                continue;
            }

            $emote = $emotes[$name];
            $img = $doc->createElement('img');
            $img->setAttribute('src', 'assets/emotes/' . $emote['file']);
            $img->setAttribute('alt', ':' . $name . ':');
            $img->setAttribute('class', 'emote');
            if (!empty($emote['title'])) {
                $img->setAttribute('title', $emote['title']);
            }
            $fragment->appendChild($img);
        }

        $textNode->parentNode->replaceChild($fragment, $textNode);
    }

    $root = $doc->documentElement;
    if ($root) {
        return $doc->saveHTML($root);
    }
    return $html;
}

function render_markdown_with_emotes(PDO $pdo, string $text): string
{
    $html = render_markdown($text);
    return apply_emotes_to_html($pdo, $html);
}

function render_markdown_with_mentions(PDO $pdo, string $text): string
{
    $html = render_markdown_with_emotes($pdo, $text);
    preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $text, $matches);
    $rawUsernames = array_unique($matches[1] ?? []);
    $usernames = [];
    foreach ($rawUsernames as $name) {
        $norm = normalize_username($name);
        if ($norm !== '' && strlen($norm) >= 3 && strlen($norm) <= 30) {
            $usernames[] = $norm;
        }
    }
    $usernames = array_values(array_unique($usernames));
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
        $norm = normalize_username($name);
        if (!isset($map[$norm])) {
            return '@' . $name;
        }
        $id = $map[$norm];
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

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
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
        $stmt = $pdo->prepare('INSERT INTO categories (name, description, sort_order, is_readonly, is_pinned) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['Annonces', 'Nouveautés et mises à jour.', 1, 1, 1]);
        $stmt->execute(['Support', 'Questions et aide technique.', 2, 0, 0]);
        $stmt->execute(['Discussions', 'Sujets libres.', 3, 0, 0]);
    }
    if (column_exists($pdo, 'categories', 'is_readonly')) {
        $stmt = $pdo->prepare("UPDATE categories SET is_readonly = 1 WHERE name = 'Annonces'");
        $stmt->execute();
    }
    if (column_exists($pdo, 'categories', 'is_pinned')) {
        $stmt = $pdo->prepare("UPDATE categories SET is_pinned = 1 WHERE name = 'Annonces'");
        $stmt->execute();
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

    $defaults = default_settings();
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

function default_settings(): array
{
    return [
        'site_title' => 'Forum PHP',
        'site_description' => 'Forum communautaire.',
        'footer_text' => 'Forum PHP',
        'footer_link' => '',
        'stripe_enabled' => '0',
        'stripe_url' => '',
        'theme_light_bg' => '#f6f4ef',
        'theme_light_surface' => '#ffffff',
        'theme_light_text' => '#1f2937',
        'theme_light_muted' => '#6b7280',
        'theme_light_primary' => '#d97706',
        'theme_light_accent' => '#0f766e',
        'theme_dark_bg' => '#0e1111',
        'theme_dark_surface' => '#151a1b',
        'theme_dark_text' => '#e5e7eb',
        'theme_dark_muted' => '#9ca3af',
        'theme_dark_primary' => '#f59e0b',
        'theme_dark_accent' => '#14b8a6',
        'theme_font' => '\"Inter\", system-ui, sans-serif',
        'theme_version' => '1',
    ];
}

function theme_presets(): array
{
    return [
        'amber_teal' => [
            'theme_light_bg' => '#f6f4ef',
            'theme_light_surface' => '#ffffff',
            'theme_light_text' => '#1f2937',
            'theme_light_muted' => '#6b7280',
            'theme_light_primary' => '#d97706',
            'theme_light_accent' => '#0f766e',
            'theme_dark_bg' => '#0e1111',
            'theme_dark_surface' => '#151a1b',
            'theme_dark_text' => '#e5e7eb',
            'theme_dark_muted' => '#9ca3af',
            'theme_dark_primary' => '#f59e0b',
            'theme_dark_accent' => '#14b8a6',
            'theme_font' => '\"Inter\", system-ui, sans-serif',
        ],
        'slate_mint' => [
            'theme_light_bg' => '#f3f4f6',
            'theme_light_surface' => '#ffffff',
            'theme_light_text' => '#111827',
            'theme_light_muted' => '#6b7280',
            'theme_light_primary' => '#334155',
            'theme_light_accent' => '#10b981',
            'theme_dark_bg' => '#0b1220',
            'theme_dark_surface' => '#111827',
            'theme_dark_text' => '#e5e7eb',
            'theme_dark_muted' => '#9ca3af',
            'theme_dark_primary' => '#475569',
            'theme_dark_accent' => '#34d399',
            'theme_font' => '\"Inter\", system-ui, sans-serif',
        ],
        'sand_rose' => [
            'theme_light_bg' => '#f8f5f0',
            'theme_light_surface' => '#ffffff',
            'theme_light_text' => '#292524',
            'theme_light_muted' => '#78716c',
            'theme_light_primary' => '#ea580c',
            'theme_light_accent' => '#be185d',
            'theme_dark_bg' => '#140f10',
            'theme_dark_surface' => '#1f1618',
            'theme_dark_text' => '#f3f4f6',
            'theme_dark_muted' => '#a8a29e',
            'theme_dark_primary' => '#fb7185',
            'theme_dark_accent' => '#f472b6',
            'theme_font' => '\"Inter\", system-ui, sans-serif',
        ],
    ];
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

function create_notification(PDO $pdo, int $userId, string $type, string $message, ?int $topicId = null, ?int $postId = null, ?int $actorId = null): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, actor_id, type, message, topic_id, post_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $actorId, $type, $message, $topicId, $postId]);

    $mailEnabled = ($_ENV['MAIL_ENABLED'] ?? '0') === '1';
    if ($mailEnabled) {
        $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $email = $stmt->fetchColumn();
        if ($email) {
            $notifUrl = absolute_url('notifications.php');
            $body = '<p>' . e($message) . '</p><p style="color:#6b7280;margin:0;">Consultez vos notifications pour plus de détails.</p>';
            $html = mail_layout('Nouvelle notification', $body, 'Voir mes notifications', $notifUrl);
            send_mail($email, 'Notification', $html);
        }
    }
}

function notification_label(string $type): string
{
    return match ($type) {
        'reply' => 'Réponses',
        'mention' => 'Mentions',
        'vote' => 'Votes',
        default => 'Autres',
    };
}

function parse_mentions(string $content): array
{
    preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $content, $matches);
    $raw = array_unique($matches[1] ?? []);
    $names = [];
    foreach ($raw as $name) {
        $norm = normalize_username($name);
        if ($norm !== '' && strlen($norm) >= 3 && strlen($norm) <= 30) {
            $names[] = $norm;
        }
    }
    return array_values(array_unique($names));
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
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $line = '[' . date('Y-m-d H:i:s') . '] MAIL ERROR: ' . $e->getMessage() . PHP_EOL;
        @file_put_contents($logDir . '/mail.log', $line, FILE_APPEND);
        return false;
    }
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function app_base_url(): string
{
    $base = trim((string) ($_ENV['APP_BASE_URL'] ?? ''));
    return rtrim($base, '/');
}

function absolute_url(string $path): string
{
    if (preg_match('/^https?:\\/\\//i', $path)) {
        return $path;
    }
    $base = app_base_url();
    if ($base === '') {
        return $path;
    }
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

function mail_button(string $label, string $url): string
{
    $safeUrl = e($url);
    $safeLabel = e($label);
    return '<a href="' . $safeUrl . '" style="display:inline-block;padding:10px 16px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">' . $safeLabel . '</a>';
}

function mail_layout(string $title, string $body, ?string $buttonLabel = null, ?string $buttonUrl = null): string
{
    $safeTitle = e($title);
    $button = '';
    if ($buttonLabel && $buttonUrl) {
        $button = '<div style="margin-top:16px;">' . mail_button($buttonLabel, $buttonUrl) . '</div>';
    }
    return '<div style="font-family:Arial,Helvetica,sans-serif;color:#111827;line-height:1.5;">'
        . '<h2 style="margin:0 0 12px;font-size:20px;">' . $safeTitle . '</h2>'
        . '<div>' . $body . '</div>'
        . $button
        . '</div>';
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
