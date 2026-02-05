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
