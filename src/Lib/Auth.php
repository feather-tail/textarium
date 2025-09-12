<?php
namespace App\Lib;

use App\Lib\Db;
use PDO;

class Auth
{
    public static function login(string $username, string $password): string
    {
        if (self::isLoginBlocked($username)) {
            self::logLoginAttempt($username);
            \App\Lib\Log::write('blocked-login', 'user', null, "Попытка входа заблокирована: $username");
            return 'blocked';
        }

        $pdo = Db::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::logLoginAttempt($username);
            return 'not_found';
        }

        if ((int)$user['is_verified'] !== 1) {
            return 'not_verified';
        }

        $stmt = $pdo->prepare("
            SELECT r.name
            FROM user_roles ur
            JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($roles)) {
            $roles = ['user'];
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'roles' => $roles,
        ];

        return 'success';
    }

    public static function logout(): void
    {
        unset($_SESSION['user'], $_SESSION['user_roles']);
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        return in_array($role, $_SESSION['user']['roles'] ?? [], true);
    }

    public static function hasAnyRole(array $roles): bool
    {
        $userRoles = $_SESSION['user']['roles'] ?? [];
        return !empty($userRoles) && count(array_intersect($userRoles, $roles)) > 0;
    }

    public static function requireRole(array $roles): void
    {
        if (!self::hasAnyRole($roles)) {
            http_response_code(403);
            echo '⛔ Доступ запрещён';
            exit;
        }
    }

    private static function logLoginAttempt(string $username): void
    {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (ip_address, username)
            VALUES (?, ?)
        ");
        $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown', $username]);

        $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL 1 HOUR)");
    }

    private static function isLoginBlocked(string $username): bool
    {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE username = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() >= 5;
    }
}
