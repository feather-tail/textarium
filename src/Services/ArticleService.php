<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
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
    $this->pdo = $pdo;
    $this->slugService = new SlugService($pdo);
    $this->categoryService = new CategoryService($pdo);
    $this->tagService = new TagService($pdo);
  }

  /**
   * @param mixed $ids
   * @return int[]
   */
  private function normalizeIds(mixed $ids): array
  {
    if (!is_array($ids)) {
      $ids = [$ids];
    }

    $ids = array_map("intval", $ids);
    $ids = array_filter($ids, static fn(int $id): bool => $id > 0);

    return array_values(array_unique($ids));
  }

  /**
   * Validate article data
   *
   * @param array $data
   * @return array<string>
   */
  private function validate(array $data): array
  {
    $errors = [];

    if (empty($data["title"]) || empty($data["content"]) || empty($data["author_id"])) {
      $errors[] = "Все поля обязательны";
    }

    if (empty($data["categories"])) {
      $errors[] = "Нужно выбрать хотя бы одну категорию";
    }

    if (empty($data["tags"])) {
      $errors[] = "Нужно выбрать хотя бы один тэг";
    }

    return $errors;
  }

  public function create(array $data): array
  {
    $data["categories"] = $this->normalizeIds($data["categories"] ?? []);
    $data["tags"] = $this->normalizeIds($data["tags"] ?? []);
    $errors = $this->validate($data);

    if ($errors) {
      return ["success" => false, "errors" => $errors];
    }

    $slug = $this->slugService->generate($data["title"]);

    try {
      $this->pdo->beginTransaction();

      $stmt = $this->pdo->prepare("
            INSERT INTO articles
                (title, slug, content, author_id, created_at, status)
            VALUES
                (?, ?, ?, ?, NOW(), ?)
        ");
      $stmt->execute([
        $data["title"],
        $slug,
        $data["content"],
        $data["author_id"],
        $data["status"] ?? "draft",
      ]);

      $articleId = (int) $this->pdo->lastInsertId();

      $this->categoryService->attach($articleId, $data["categories"]);
      $this->tagService->attach($articleId, $data["tags"]);

      $this->pdo->commit();
    } catch (PDOException $e) {
      $this->pdo->rollBack();
      return ["success" => false, "errors" => ["Ошибка при создании статьи"]];
    }

    return ["success" => true, "errors" => [], "id" => $articleId];
  }

  public function update(int $id, array $data): array
  {
    $data["categories"] = $this->normalizeIds($data["categories"] ?? []);
    $data["tags"] = $this->normalizeIds($data["tags"] ?? []);

    $errors = $this->validate($data);

    if ($errors) {
      return ["success" => false, "errors" => $errors];
    }

    $stmt = $this->pdo->prepare("SELECT title, slug FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
      return ["success" => false, "errors" => ["Статья не найдена"]];
    }

    $title = $data["title"];
    $slug = $this->slugService->generateOrReuse($title, $existing["slug"], $existing["title"]);

    try {
      $this->pdo->beginTransaction();

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
        $data["content"],
        $data["author_id"],
        $data["status"] ?? "draft",
        $id,
      ]);

      $this->categoryService->attach($id, $data["categories"]);
      $this->tagService->attach($id, $data["tags"]);

      $this->pdo->commit();
    } catch (PDOException $e) {
      $this->pdo->rollBack();
      return ["success" => false, "errors" => ["Ошибка при обновлении статьи"]];
    }

    return ["success" => true, "errors" => []];
  }
}
