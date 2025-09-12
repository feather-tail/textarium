<?php
namespace App\Services;

use PDO;
use App\Services\SlugService;
use App\Services\CategoryService;
use App\Services\TagService;

class ArticleService
{
    private PDO $pdo;
    private SlugService $slugService;
    private CategoryService $categoryService;
    private TagService $tagService;

    public function __construct(PDO $pdo)
    {
        $this->pdo             = $pdo;
        $this->slugService     = new SlugService($pdo);
        $this->categoryService = new CategoryService($pdo);
        $this->tagService      = new TagService($pdo);
    }

    public function create(array $data): array
    {
        $errors = [];

        if (
            empty($data['title'])
            || empty($data['content'])
            || empty($data['author_id'])
            || empty($data['categories']) || !is_array($data['categories'])
        ) {
            $errors[] = 'Все поля обязательны, включая выбор категории';
        }

        if (empty($data['tags']) || !is_array($data['tags'])) {
            $errors[] = 'Нужно выбрать хотя бы один тэг';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $slug = $this->slugService->generate($data['title']);

        $stmt = $this->pdo->prepare("
            INSERT INTO articles 
                (title, slug, content, author_id, created_at, status)
            VALUES 
                (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([
            $data['title'],
            $slug,
            $data['content'],
            $data['author_id'],
            $data['status'] ?? 'draft',
        ]);

        $articleId = (int)$this->pdo->lastInsertId();

        $this->categoryService->attach($articleId, $data['categories']);
        $this->tagService->attach     ($articleId, $data['tags']);

        return ['success' => true, 'errors' => [], 'id' => $articleId];
    }

    public function update(int $id, array $data): array
    {
        $errors = [];

        if (
            empty($data['title'])
            || empty($data['content'])
            || empty($data['author_id'])
            || empty($data['categories']) || !is_array($data['categories'])
        ) {
            $errors[] = 'Все поля обязательны, включая выбор категории';
        }

        if (empty($data['tags']) || !is_array($data['tags'])) {
            $errors[] = 'Нужно выбрать хотя бы один тэг';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->pdo->prepare("SELECT title, slug FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            return ['success' => false, 'errors' => ['Статья не найдена']];
        }

        $title = $data['title'];
        $slug  = $this->slugService->generateOrReuse(
            $title,
            $existing['slug'],
            $existing['title']
        );

        $stmt = $this->pdo->prepare("
            UPDATE articles SET
                title      = ?,
                slug       = ?,
                content    = ?,
                author_id  = ?,
                status     = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $title,
            $slug,
            $data['content'],
            $data['author_id'],
            $data['status'] ?? 'draft',
            $id
        ]);

        $this->categoryService->attach($id,           $data['categories']);
        $this->tagService->attach     ($id,           $data['tags']);

        return ['success' => true, 'errors' => []];
    }
}
