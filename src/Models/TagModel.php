<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class TagModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tags WHERE is_deleted = 0 ORDER BY name COLLATE utf8mb4_unicode_ci");
        return $stmt->fetchAll();
    }

    public function getTagsForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id, t.name
            FROM tags t
            INNER JOIN article_tags at ON at.tag_id = t.id
            WHERE at.article_id = ? AND at.is_deleted = 0
            ORDER BY t.name COLLATE utf8mb4_unicode_ci
        ");
        $stmt->execute([$articleId]);

        return $stmt->fetchAll();
    }

    public function create(string $name): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO tags (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    public function update(int $id, string $name): void
    {
        $stmt = $this->pdo->prepare("UPDATE tags SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("UPDATE tags SET is_deleted = 1 WHERE id = ?")->execute([$id]);
    }

    public function restore(int $id): void
    {
        $this->pdo->prepare("UPDATE tags SET is_deleted = 0 WHERE id = ?")->execute([$id]);
    }

    public function getTagsForArticles(array $articleIds): array
    {
        if (empty($articleIds)) return [];
        $in = implode(',', array_fill(0, count($articleIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT at.article_id, t.id, t.name
            FROM article_tags at
            JOIN tags t ON t.id = at.tag_id
            WHERE at.article_id IN ($in) AND at.is_deleted = 0
            ORDER BY t.name COLLATE utf8mb4_unicode_ci
        ");
        $stmt->execute($articleIds);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['article_id']][] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
        return $result;
    }

    public function getAllWithHidden(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tags ORDER BY name COLLATE utf8mb4_unicode_ci");
        return $stmt->fetchAll();
    }
}
