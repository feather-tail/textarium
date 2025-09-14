<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Db;
use App\Lib\Auth;
use App\Lib\Flash;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Services\ArticleService;
use App\Lib\ArticleStatus;
use App\Lib\Permissions;

class HomeController extends BaseController
{
  public function index(): void
  {
    $pdo = Db::getConnection();

    $query = trim($_GET["q"] ?? "");

    $tagIds = $_GET["tag"] ?? [];
    $categoryIds = $_GET["category"] ?? [];
    if (!is_array($tagIds)) {
      $tagIds = [$tagIds];
    }
    if (!is_array($categoryIds)) {
      $categoryIds = [$categoryIds];
    }
    $tagIds = array_filter($tagIds, fn($id) => $id !== "");
    $categoryIds = array_filter($categoryIds, fn($id) => $id !== "");

    $articleModel = new ArticleModel($pdo);
    $categoryModel = new CategoryModel($pdo);
    $tagModel = new TagModel($pdo);

    $page = max(1, (int) ($_GET["page"] ?? 1));
    $perPage = 10;

    $filters = [
      "query" => $query,
      "tag_id" => $tagIds,
      "category_id" => $categoryIds,
      "status" => ArticleStatus::APPROVED,
      "sort" => $_GET["sort"] ?? "created_desc",
    ];

    [$sql, $params, $total] = \App\Lib\SqlHelper::buildPaginatedArticleQuery(
      $pdo,
      $filters,
      $page,
      $perPage,
    );

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":limit", $params["limit"], \PDO::PARAM_INT);
    $stmt->bindValue(":offset", $params["offset"], \PDO::PARAM_INT);
    unset($params["limit"], $params["offset"]);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    $articleIds = array_column($articles, "id");
    $categoriesByArticle = $categoryModel->getCategoriesForArticles($articleIds);

    foreach ($articles as &$article) {
      $cid = $categoriesByArticle[$article["id"]][0]["id"] ?? null;
      $article["category_name"] = $cid ? $categoriesByArticle[$article["id"]][0]["name"] : null;
    }
    unset($article);

    $categories = $categoryModel->getAll();
    $tags = $tagModel->getAll();
    $title = "Главная";
    $allTags = $tagModel->getTagsForArticles($articleIds);
    $allCategories = $categoriesByArticle;

    ob_start();
    include __DIR__ . "/../../views/home.php";
    $content = ob_get_clean();
    include __DIR__ . "/../../views/layout.php";
  }

  public function submitForm(): void
  {
    if (!Permissions::userCan("create_article")) {
      http_response_code(403);
      echo "⛔ Доступ запрещён. Только для верифицированных пользователей.";
      exit();
    }

    $pdo = Db::getConnection();
    $categoryModel = new CategoryModel($pdo);
    $tagModel = new TagModel($pdo);

    $categories = $categoryModel->getAll();
    $tags = $tagModel->getAll();
    $articleService = new ArticleService($pdo);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $this->requirePostWithCsrf();

      $action = $_POST["client_action"] ?? ($_POST["action"] ?? "");

      if ($action === "draft") {
        $status = ArticleStatus::DRAFT;
      } elseif ($action === "publish") {
        $status = Auth::hasAnyRole(["moderator", "admin"])
          ? ArticleStatus::APPROVED
          : ArticleStatus::PENDING;
      } else {
        $status = ArticleStatus::PENDING;
      }

      $data = [
        "title" => trim($_POST["title"] ?? ""),
        "content" => trim($_POST["content"] ?? ""),
        "author_id" => Auth::currentUser()["id"],
        "categories" => $_POST["categories"] ?? [],
        "tags" => $_POST["tags"] ?? [],
        "status" => $status,
      ];

      $result = $articleService->create($data);

      if ($result["success"]) {
        if ($status === ArticleStatus::DRAFT) {
          Flash::success("Черновик сохранён");
        } elseif ($status === ArticleStatus::APPROVED) {
          Flash::success("Статья опубликована");
        } else {
          Flash::success("Статья отправлена на модерацию");
        }

        header("Location: " . ($status === ArticleStatus::DRAFT ? "/my-drafts" : "/my-articles"));
        exit();
      } else {
        foreach ($result["errors"] as $err) {
          Flash::error($err);
        }
      }
    }

    $title = "Создание статьи";
    ob_start();
    include __DIR__ . "/../../views/submit_form.php";
    $content = ob_get_clean();
    include __DIR__ . "/../../views/layout.php";
  }

  public function myArticles(): void
  {
    if (!Permissions::userCan("create_article")) {
      http_response_code(403);
      echo "⛔ Доступ запрещён.";
      exit();
    }

    $pdo = Db::getConnection();
    $currentUser = Auth::currentUser();

    $page = max(1, (int) ($_GET["page"] ?? 1));
    $perPage = 10;

    [$sql, $params, $total] = \App\Lib\SqlHelper::buildPaginatedArticleQuery(
      $pdo,
      ["author_id" => $currentUser["id"], "status" => ArticleStatus::APPROVED],
      $page,
      $perPage,
    );

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":limit", $params["limit"], \PDO::PARAM_INT);
    $stmt->bindValue(":offset", $params["offset"], \PDO::PARAM_INT);
    unset($params["limit"], $params["offset"]);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    $title = "Мои статьи";
    $tagModel = new TagModel($pdo);
    $categoryModel = new CategoryModel($pdo);
    $articleIds = array_column($articles, "id");
    $allTags = $tagModel->getTagsForArticles($articleIds);
    $allCategories = $categoryModel->getCategoriesForArticles($articleIds);

    ob_start();
    include __DIR__ . "/../../views/my_articles.php";
    $content = ob_get_clean();
    include __DIR__ . "/../../views/layout.php";
  }

  public function myDrafts(): void
  {
    if (!Permissions::userCan("create_article")) {
      http_response_code(403);
      echo "⛔ Доступ запрещён.";
      exit();
    }

    $pdo = Db::getConnection();
    $currentUser = Auth::currentUser();

    $page = max(1, (int) ($_GET["page"] ?? 1));
    $perPage = 10;

    [$sql, $params, $total] = \App\Lib\SqlHelper::buildPaginatedArticleQuery(
      $pdo,
      ["author_id" => $currentUser["id"], "status" => ArticleStatus::DRAFT],
      $page,
      $perPage,
    );

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":limit", $params["limit"], \PDO::PARAM_INT);
    $stmt->bindValue(":offset", $params["offset"], \PDO::PARAM_INT);
    unset($params["limit"], $params["offset"]);
    $stmt->execute($params);
    $drafts = $stmt->fetchAll();

    $title = "Мои черновики";
    $tagModel = new TagModel($pdo);
    $categoryModel = new CategoryModel($pdo);
    $articleIds = array_column($drafts, "id");
    $allTags = $tagModel->getTagsForArticles($articleIds);
    $allCategories = $categoryModel->getCategoriesForArticles($articleIds);

    ob_start();
    include __DIR__ . "/../../views/my_drafts.php";
    $content = ob_get_clean();
    include __DIR__ . "/../../views/layout.php";
  }

  public function editDraft(): void
  {
    $id = (int) ($_GET["id"] ?? 0);
    $currentUser = \App\Lib\Auth::currentUser();
    $userRoles = $currentUser["roles"] ?? [];

    if (in_array("moderator", $userRoles, true) || in_array("admin", $userRoles, true)) {
      header("Location: /admin/edit?id=" . $id);
      exit();
    }

    if (!Permissions::userCan("edit_own_draft")) {
      http_response_code(403);
      echo "⛔ Доступ запрещён.";
      exit();
    }

    if ($id <= 0) {
      http_response_code(400);
      echo "⛔ Неверный ID";
      return;
    }

    $pdo = Db::getConnection();
    $articleModel = new ArticleModel($pdo);
    $article = $articleModel->getById($id);
    $currentUser = Auth::currentUser();

    if (
      !$article ||
      $article["author_id"] !== $currentUser["id"] ||
      $article["status"] !== ArticleStatus::DRAFT
    ) {
      http_response_code(403);
      echo "⛔ Нет доступа к черновику";
      return;
    }

    $tagModel = new TagModel($pdo);
    $categoryModel = new CategoryModel($pdo);

    $tags = $tagModel->getAll();
    $categories = $categoryModel->getAll();
    $selectedTagIds = array_column($tagModel->getTagsForArticle($id), "id");
    $selectedCategoryIds = array_column($categoryModel->getForArticle($id), "id");

    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $this->requirePostWithCsrf();

      $title = trim($_POST["title"] ?? "");
      $content = trim($_POST["content"] ?? "");
      $selectedCategories = $_POST["categories"] ?? [];
      $selectedTags = $_POST["tags"] ?? [];

      if ($title === "" || $content === "" || empty($selectedCategories)) {
        $errors[] = "Все поля обязательны";
      } else {
        $service = new \App\Services\ArticleService($pdo);
        $result = $service->update($id, [
          "title" => $title,
          "content" => $content,
          "author_id" => $currentUser["id"],
          "status" => ArticleStatus::DRAFT,
          "categories" => $selectedCategories,
          "tags" => $selectedTags,
        ]);

        if ($result["success"]) {
          Flash::success("Черновик сохранён");
          header("Location: /my-drafts");
          exit();
        } else {
          $errors = $result["errors"];
        }
      }
    }

    $title = "Редактировать черновик";
    ob_start();
    include __DIR__ . "/../../views/article_edit.php";
    $content = ob_get_clean();
    include __DIR__ . "/../../views/layout.php";
  }

  public function submitDraft(): void
  {
    if (!Permissions::userCan("submit_article")) {
      http_response_code(403);
      echo "⛔ Доступ запрещён.";
      exit();
    }

    $this->requirePostWithCsrf();

    $id = (int) ($_POST["id"] ?? 0);
    if (!$id) {
      http_response_code(400);
      echo "Неверный ID";
      return;
    }

    $pdo = Db::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND status = ?");
    $stmt->execute([$id, ArticleStatus::DRAFT]);
    $article = $stmt->fetch();

    if (!$article || $article["author_id"] != Auth::currentUser()["id"]) {
      http_response_code(403);
      echo "⛔ Нет доступа";
      return;
    }

    $status = Auth::hasAnyRole(["moderator", "admin"])
      ? ArticleStatus::APPROVED
      : ArticleStatus::PENDING;

    $stmt = $pdo->prepare("
            UPDATE articles
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
    $stmt->execute([$status, $id]);

    Flash::success("Черновик отправлен на модерацию");
    header("Location: /my-articles");
    exit();
  }
}
