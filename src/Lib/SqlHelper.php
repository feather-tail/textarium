<?php
declare(strict_types=1);

namespace App\Lib;

use PDO;
use PDOException;

class SqlHelper
{
  /**
   * Cached result of probing for full-text search support.
   */
  private static ?bool $fullTextSupported = null;

  private static function addQuerySearch(
    PDO $pdo,
    string &$sql,
    array &$params,
    string $search,
    string $tableAlias = "a",
  ): void {
    if (self::$fullTextSupported === null) {
      try {
        $pdo->query("SELECT MATCH(title, content) AGAINST('test') FROM articles LIMIT 1");
        self::$fullTextSupported = true;
      } catch (PDOException $e) {
        self::$fullTextSupported = false;
      }
    }

    if (self::$fullTextSupported) {
      $sql .= " AND MATCH($tableAlias.title, $tableAlias.content) AGAINST (:q IN BOOLEAN MODE)";
      $params["q"] = $search;
    } else {
      $sql .= " AND ($tableAlias.title LIKE :q OR $tableAlias.content LIKE :q)";
      $params["q"] = "%" . $search . "%";
    }
  }

  public static function buildPaginatedArticleQuery(
    PDO $pdo,
    array $filters,
    int $page = 1,
    int $perPage = 20,
  ): array {
    $baseSql = "FROM articles a
                    JOIN users u ON a.author_id = u.id
                    WHERE 1=1";
    $params = [];
    $where = "";

    if (!empty($filters["query"])) {
      $search = trim($filters["query"]);
      self::addQuerySearch($pdo, $where, $params, $search, "a");
    }

    if (!empty($filters["category_id"])) {
      $categoryIds = is_array($filters["category_id"])
        ? $filters["category_id"]
        : [$filters["category_id"]];
      $placeholders = [];

      foreach ($categoryIds as $i => $id) {
        $key = "cat_$i";
        $placeholders[] = ":$key";
        $params[$key] = $id;
      }

      $where .=
        " AND EXISTS (
                SELECT 1 FROM article_categories ac
                WHERE ac.article_id = a.id AND ac.category_id IN (" .
        implode(",", $placeholders) .
        ")
            )";
    }

    if (!empty($filters["tag_id"])) {
      $tagIds = is_array($filters["tag_id"]) ? $filters["tag_id"] : [$filters["tag_id"]];
      $placeholders = [];

      foreach ($tagIds as $i => $id) {
        $key = "tag_$i";
        $placeholders[] = ":$key";
        $params[$key] = $id;
      }

      $where .=
        " AND EXISTS (
                SELECT 1 FROM article_tags at
                WHERE at.article_id = a.id AND at.tag_id IN (" .
        implode(",", $placeholders) .
        ")
            )";
    }

    if (!empty($filters["author_id"])) {
      $where .= " AND a.author_id = :author_id";
      $params["author_id"] = $filters["author_id"];
    }

    if (
      !empty($filters["status"]) &&
      in_array($filters["status"], \App\Lib\ArticleStatus::all(), true)
    ) {
      $where .= " AND a.status = :status";
      $params["status"] = $filters["status"];
    }

    if (!empty($filters["exclude_deleted"])) {
      $where .= " AND a.deleted_at IS NULL";
    }

    $orderBy = match ($filters["sort"] ?? "created_desc") {
      "created_asc" => "a.created_at ASC",
      "title_asc" => "a.title ASC",
      "title_desc" => "a.title DESC",
      default => "a.created_at DESC",
    };

    $countStmt = $pdo->prepare("SELECT COUNT(*) $baseSql $where");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $offset = max(0, ($page - 1) * $perPage);
    $sql = "
            SELECT a.*, u.username AS author_username
            $baseSql $where
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ";
    $params["limit"] = $perPage;
    $params["offset"] = $offset;

    return [$sql, $params, $total];
  }
}
