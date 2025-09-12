<?php
namespace App\Models;

use PDO;

class CategoryModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categories WHERE is_deleted = 0 ORDER BY name COLLATE utf8mb4_unicode_ci");
        return $stmt->fetchAll();
    }

    public function getForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name
            FROM categories c
            JOIN article_categories ac ON ac.category_id = c.id
            WHERE ac.article_id = ? AND ac.is_deleted = 0
            ORDER BY c.name COLLATE utf8mb4_unicode_ci
        ");
        $stmt->execute([$articleId]);
        return $stmt->fetchAll();
    }

    public function create(string $name): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    public function update(int $id, string $name): void
    {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("UPDATE categories SET is_deleted = 1 WHERE id = ?")->execute([$id]);
    }

    public function restore(int $id): void
    {
        $this->pdo->prepare("UPDATE categories SET is_deleted = 0 WHERE id = ?")->execute([$id]);
    }

    public function getCategoriesForArticles(array $articleIds): array
    {
        if (empty($articleIds)) return [];
        $in = implode(',', array_fill(0, count($articleIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT ac.article_id, c.id, c.name
            FROM article_categories ac
            JOIN categories c ON c.id = ac.category_id
            WHERE ac.article_id IN ($in) AND ac.is_deleted = 0
            ORDER BY c.name COLLATE utf8mb4_unicode_ci
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
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name COLLATE utf8mb4_unicode_ci");
        return $stmt->fetchAll();
    }
}
