<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class UserModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT u.id, u.username, u.created_at, r.name AS role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.is_deleted = 0
            ORDER BY u.username ASC
        ");
        $raw = $stmt->fetchAll();

        $users = [];
        foreach ($raw as $row) {
            $id = $row['id'];
            if (!isset($users[$id])) {
                $users[$id] = [
                    'id' => $id,
                    'username' => $row['username'],
                    'created_at' => $row['created_at'],
                    'roles' => []
                ];
            }
            if ($row['role']) {
                $users[$id]['roles'][] = $row['role'];
            }
        }

        return array_values($users);
    }

    public function getAllPaginated(int $limit, int $offset, bool $includeDeleted = false): array
    {
        $sql = "
            SELECT u.id, u.username, u.created_at, u.is_deleted, r.name AS role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
        ";

        if ($includeDeleted) {
            $sql .= " WHERE u.is_deleted = 1";
        } else {
            $sql .= " WHERE u.is_deleted = 0";
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $raw = $stmt->fetchAll();

        $users = [];
        foreach ($raw as $row) {
            $id = $row['id'];
            if (!isset($users[$id])) {
                $users[$id] = [
                    'id' => $id,
                    'username' => $row['username'],
                    'created_at' => $row['created_at'],
                    'roles' => [],
                    'is_deleted' => (bool)$row['is_deleted']
                ];
            }
            if ($row['role']) {
                $users[$id]['roles'][] = $row['role'];
            }
        }

        return array_values($users);
    }

    public function countAll(bool $includeDeleted = false): int
    {
        $deletedFlag = $includeDeleted ? 1 : 0;
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE is_deleted = :deleted");
        $stmt->bindValue(':deleted', $deletedFlag, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function resetPassword(int $userId, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
    }

    public function softDelete(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function restore(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_deleted = 0 WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function searchPaginated(string $query, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.created_at, r.name AS role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.is_deleted = 0 AND u.username LIKE ?
            ORDER BY u.username COLLATE utf8mb4_unicode_ci
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(["%{$query}%", $limit, $offset]);

        $raw = $stmt->fetchAll();

        $users = [];
        foreach ($raw as $row) {
            $id = $row['id'];
            if (!isset($users[$id])) {
                $users[$id] = [
                    'id' => $id,
                    'username' => $row['username'],
                    'created_at' => $row['created_at'],
                    'roles' => []
                ];
            }
            if ($row['role']) {
                $users[$id]['roles'][] = $row['role'];
            }
        }

        return array_values($users);
    }

    public function countSearch(string $query): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE is_deleted = 0 AND username LIKE ?");
        $stmt->execute(["%{$query}%"]);
        return (int) $stmt->fetchColumn();
    }
}
