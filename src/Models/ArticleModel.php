<?php
namespace App\Models;

use PDO;
use App\Services\SlugService;

class ArticleModel
{
    private PDO $pdo;
    private SlugService $slugService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->slugService = new SlugService($pdo);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, slug, title, content, created_at, author_id, status, deleted_at
            FROM articles
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function softDelete(int $id): void
    {
        $this->pdo->prepare("
            UPDATE articles SET status = 'deleted', deleted_at = NOW()
            WHERE id = ?
        ")->execute([$id]);
    }

    public function restore(int $id): void
    {
        $this->pdo->prepare("
            UPDATE articles SET status = 'pending', deleted_at = NULL
            WHERE id = ?
        ")->execute([$id]);
    }

    public function approve(int $id): void
    {
        $this->pdo->prepare("
            UPDATE articles SET status = 'approved'
            WHERE id = ?
        ")->execute([$id]);
    }

    public function getCategoriesForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name
            FROM categories c
            JOIN article_categories ac ON c.id = ac.category_id
            WHERE ac.article_id = ?
        ");
        $stmt->execute([$articleId]);
        return $stmt->fetchAll();
    }

    public function backfillSlugs(): void
    {
        $stmt = $this->pdo->query("SELECT id, title FROM articles WHERE slug = '' OR slug IS NULL");
        foreach ($stmt->fetchAll() as $row) {
            $slug = $this->slugService->generate($row['title']);
            $update = $this->pdo->prepare("UPDATE articles SET slug = ? WHERE id = ?");
            $update->execute([$slug, $row['id']]);
        }
    }
}
