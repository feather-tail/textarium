<?php
namespace App\Lib;

class Permissions
{
    public const MAP = [
        'change_password'    => ['user', 'verified', 'moderator', 'admin'],
        'view_home'          => ['user', 'verified', 'moderator', 'admin'],
        'create_article'     => ['verified', 'moderator', 'admin'],
        'edit_own_draft'     => ['verified', 'moderator', 'admin'],
        'submit_article'     => ['verified', 'moderator', 'admin'],
        'upload_image'       => ['verified', 'moderator', 'admin'],
        'admin_dashboard'    => ['moderator', 'admin'],
        'manage_users'       => ['admin'],
    ];

    /**
     * Проверяет, есть ли у пользователя право выполнять действие.
     * @param string $permission — ключ права из MAP
     * @param array|null $userRoles — массив ролей пользователя (если не указано — берётся из сессии)
     * @return bool
     */
    public static function userCan(string $permission, ?array $userRoles = null): bool
    {
        $userRoles = $userRoles ?? ($_SESSION['user']['roles'] ?? []);
        $allowed = self::MAP[$permission] ?? [];
        return !empty(array_intersect($userRoles, $allowed));
    }
}
