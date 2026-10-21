<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['username']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }

        $user = UserModel::findByUsername((string)$_SESSION['username']);

        if (!$user) {
            $_SESSION = [];
            header('Location: login.php');
            exit;
        }

        $_SESSION['role'] = $user['role'];
        $_SESSION['department'] = $user['department'];
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Administrator access is required.');
        }
    }

    public static function username(): string
    {
        return (string)($_SESSION['username'] ?? '');
    }

    public static function department(): string
    {
        return (string)($_SESSION['department'] ?? '');
    }
}
