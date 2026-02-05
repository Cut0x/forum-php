<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
