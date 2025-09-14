<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\BbcodeParser;
use App\Lib\Db;
use App\Lib\ArticleStatus;
use App\Lib\Permissions;
use App\Services\ArticleContextService;
use App\Services\ArticleService;

class ArticleController extends BaseController
{
    public function show(string $slug = ''): void
    {
        if (!preg_match('/^(\d+)-/', $slug, $matches)) {
            http_response_code(404);
            echo '⛔ Неверный формат URL';
            return;
        }

        $id = (int)$matches[1];
        if ($id <= 0) {
            http_response_code(404);
            echo '⛔ Статья не найдена';
            return;
        }

        $pdo = Db::getConnection();
        $articleModel = new \App\Models\ArticleModel($pdo);
        $tagModel = new \App\Models\TagModel($pdo);
        $categoryModel = new \App\Models\CategoryModel($pdo);

        $article = $articleModel->getById($id);
        if (!$article) {
            http_response_code(404);
            echo '⛔ Статья не найдена';
            return;
        }

        $parser = new BbcodeParser();
        $article['content_html'] = $parser->toHtml($article['content']);

        $tags = $tagModel->getTagsForArticle($id);
        $categories = $categoryModel->getForArticle($id);
        $title = htmlspecialchars($article['title'], ENT_QUOTES | ENT_SUBSTITUTE);
        $currentUser = Auth::currentUser();

        ob_start();
        include __DIR__ . '/../../views/article.php';
        $content = ob_get_clean();

        include __DIR__ . '/../../views/layout.php';
    }

    public function edit(int $id): void
    {
        if ($id <= 0) {
            http_response_code(400);
            echo '⛔ Неверный ID';
            return;
        }

        $pdo = Db::getConnection();
        $articleService = new ArticleService($pdo);
        $contextService = new ArticleContextService($pdo);

        $ctx = $contextService->getEditContext($id);
        if (!$ctx) {
            http_response_code(404);
            echo '⛔ Статья не найдена';
            return;
        }

        $article = $ctx['article'];
        $tags = $ctx['tags'];
        $categories = $ctx['categories'];
        $users = $ctx['users'];
        $selectedTags = $ctx['selectedTags'];
        $selectedCategories = $ctx['selectedCategories'];

        $currentUser = \App\Lib\Auth::currentUser();
        $userRoles = $currentUser['roles'] ?? [];
        $isOwner = $article['author_id'] === $currentUser['id'];
        $isModerator = in_array('moderator', $userRoles, true);
        $isAdmin = in_array('admin', $userRoles, true);

        if ($isModerator || $isAdmin) {
        } elseif ($isOwner && \App\Lib\Permissions::userCan('edit_own_draft', $userRoles)) {
        } else {
            http_response_code(403);
            echo '⛔ Нет доступа к статье';
            return;
        }

        $categoryModel = new \App\Models\CategoryModel($pdo);
        $tagModel = new \App\Models\TagModel($pdo);

        $allCategories = $categoryModel->getAllWithHidden();
        $categoryIds = array_column($selectedCategories ?? [], 'id');
        $categories = [];
        foreach ($allCategories as $cat) {
            if (!$cat['is_deleted'] || in_array($cat['id'], $categoryIds, true)) {
                $categories[] = $cat;
            }
        }

        $allTags = $tagModel->getAllWithHidden();
        $tagIds = array_column($selectedTags ?? [], 'id');
        $tags = [];
        foreach ($allTags as $t) {
            if (!$t['is_deleted'] || in_array($t['id'], $tagIds, true)) {
                $tags[] = $t;
            }
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $result = $articleService->update($id, $_POST);
            if ($result['success']) {
                $articleModel = new \App\Models\ArticleModel($pdo);
                $updatedArticle = $articleModel->getById($id);

                if ($updatedArticle && $updatedArticle['slug']) {
                    header('Location: /article/' . $id . '-' . urlencode($updatedArticle['slug']));
                } else {
                    header('Location: /');
                }
                exit;
            } else {
                foreach ($result['errors'] as $err) {
                    \App\Lib\Flash::error($err);
                }
            }
        }

        $title = 'Редактировать статью';
        ob_start();
        include __DIR__ . '/../../views/admin_edit.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }


    public function delete(int $id): void
    {
        $this->requirePostWithCsrf();

        if ($id <= 0) {
            http_response_code(400);
            echo 'Некорректный ID';
            return;
        }

        $pdo = Db::getConnection();
        $articleModel = new \App\Models\ArticleModel($pdo);
        $article = $articleModel->getById($id);

        if (!$article) {
            http_response_code(404);
            echo 'Статья не найдена';
            return;
        }

        $currentUser = Auth::currentUser();
        $isOwner = $article['author_id'] === $currentUser['id'];
        $canDelete = in_array($article['status'], [ArticleStatus::DRAFT, ArticleStatus::PENDING], true);

        if ($isOwner && $canDelete) {
            if (!Permissions::userCan('edit_own_draft')) {
                http_response_code(403);
                echo '⛔ Нет прав для удаления черновика';
                return;
            }
        } else {
            if (!Permissions::userCan('admin_dashboard')) {
                http_response_code(403);
                echo '⛔ Нет прав для удаления этой статьи';
                return;
            }
        }

        $articleModel->softDelete($id);
        \App\Lib\Log::write('delete', 'article', $id);

        if (\App\Lib\Permissions::userCan('admin_dashboard')) {
            header('Location: /admin?status=' . ArticleStatus::APPROVED);
        } else {
            header('Location: /my-drafts');
        }
        exit;
    }

    public function approve(): void
    {
        $this->requirePostWithCsrf();
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Только модераторам доступна модерация статей.';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Некорректный ID';
            return;
        }

        $pdo = Db::getConnection();
        $articleModel = new \App\Models\ArticleModel($pdo);
        $articleModel->approve($id);

        header('Location: /admin?status=' . ArticleStatus::PENDING);
        exit;
    }

    public function restore(): void
    {
        $this->requirePostWithCsrf();
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Только модераторам доступно восстановление статей.';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo '⛔ Неверный ID';
            return;
        }

        $pdo = Db::getConnection();
        $articleModel = new \App\Models\ArticleModel($pdo);
        $articleModel->restore($id);

        \App\Lib\Log::write('restore', 'article', $id);
        \App\Lib\Flash::success("Статья #$id восстановлена");

        header('Location: /admin?status=' . ArticleStatus::DELETED . '&show_deleted=1');
        exit;
    }
}
