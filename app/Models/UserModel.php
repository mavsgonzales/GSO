<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class UserModel
{
    public static function authenticate(string $username, string $password): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT username, password_hash, role, department
             FROM users
             WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT username, role, department, created_at FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT username, role, department, created_at FROM users ORDER BY username');
        return $stmt->fetchAll();
    }

    public static function create(string $username, string $password, string $department): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password_hash, role, department)
             VALUES (:username, :password_hash, 'user', :department)"
        );
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':department' => $department,
        ]);
    }

    public static function verifyPassword(string $username, string $password): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $hash = $stmt->fetchColumn();

        if (!is_string($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    public static function changePassword(string $username, string $newPassword): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE username = :username');
        return $stmt->execute([
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':username' => $username,
        ]);
    }
}
