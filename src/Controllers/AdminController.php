<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Permissions;
use App\Lib\Db;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Lib\SqlHelper;
use App\Services\ArticleService;
use App\Services\ArticleContextService;
use App\Lib\ArticleStatus;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }

        $pdo = Db::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'restore_article' && isset($_POST['article_id'])) {
                $id = (int)$_POST['article_id'];
                $stmt = $pdo->prepare("UPDATE articles SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$id]);
                \App\Lib\Flash::success('Статья восстановлена');
                \App\Lib\Log::write('restore', 'article', $id, null);
                header('Location: /admin?status=deleted&show_deleted=1');
                exit;
            }
        }

        $status = $_GET['status'] ?? \App\Lib\ArticleStatus::APPROVED;
        if (!in_array($status, \App\Lib\ArticleStatus::all())) {
            $status = \App\Lib\ArticleStatus::APPROVED;
        }

        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $filters = ['status' => $status];
        if (!$showDeleted) {
            $filters['exclude_deleted'] = true;
        }

        [$sql, $params, $total] = \App\Lib\SqlHelper::buildPaginatedArticleQuery($pdo, $filters, $page, $perPage);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();

        $tagModel = new \App\Models\TagModel($pdo);
        $categoryModel = new \App\Models\CategoryModel($pdo);
        $articleIds = array_column($articles, 'id');
        $allTags = $tagModel->getTagsForArticles($articleIds);
        $allCategories = $categoryModel->getCategoriesForArticles($articleIds);

        $title = 'Панель администратора';
        ob_start();
        include __DIR__ . '/../../views/admin.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function users(): void
    {
        if (!Permissions::userCan('manage_users')) {
            http_response_code(403);
            echo '⛔ Доступ только для администратора.';
            exit;
        }
        $pdo = Db::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $action = $_POST['action'] ?? '';
            $targetId = (int)($_POST['user_id'] ?? 0);
            $currentUser = \App\Lib\Auth::currentUser();
            $userModel = new \App\Models\UserModel($pdo);

            if ($action === 'add_role') {
                $newRole = $_POST['new_role'] ?? '';
                if ($targetId === $currentUser['id'] && $newRole !== 'admin') {
                    \App\Lib\Flash::error('Нельзя понизить собственную роль');
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
                    $stmt->execute([$newRole]);
                    $roleId = $stmt->fetchColumn();

                    if ($roleId) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
                        $stmt->execute([$targetId, $roleId]);
                        \App\Lib\Flash::success('Роль добавлена');
                        \App\Lib\Log::write('update-role', 'user', $targetId, "Добавлена роль: $newRole");
                    } else {
                        \App\Lib\Flash::error('Такой роли не существует');
                    }
                }

                header('Location: /users');
                exit;
            }

            if ($action === 'remove_role') {
                $removeRole = $_POST['remove_role'] ?? '';
                if ($targetId === $currentUser['id'] && $removeRole === 'admin') {
                    \App\Lib\Flash::error('Нельзя удалить свою роль администратора');
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
                    $stmt->execute([$removeRole]);
                    $roleId = $stmt->fetchColumn();

                    if ($roleId) {
                        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
                        $stmt->execute([$targetId, $roleId]);
                        \App\Lib\Flash::success('Роль удалена');
                        \App\Lib\Log::write('update-role', 'user', $targetId, "Удалена роль: $removeRole");
                    } else {
                        \App\Lib\Flash::error('Такой роли не существует');
                    }
                }

                header('Location: /users');
                exit;
            }

            if ($action === 'reset_password') {
                $newPassword = $_POST['new_password'] ?? '';
                if ($targetId > 0 && strlen($newPassword) >= 6) {
                    $userModel->resetPassword($targetId, $newPassword);
                    \App\Lib\Flash::success('Пароль успешно обновлён');
                    \App\Lib\Log::write('reset-password', 'user', $targetId, 'Пароль сброшен вручную');
                } else {
                    \App\Lib\Flash::error('Пароль должен быть не короче 6 символов');
                }

                header('Location: /users');
                exit;
            }

            if ($action === 'delete_user') {
                if ($targetId > 0) {
                    $userModel->softDelete($targetId);
                    \App\Lib\Flash::success('Пользователь удалён');
                    \App\Lib\Log::write('delete-user', 'user', $targetId, 'Soft-delete');
                } else {
                    \App\Lib\Flash::error('Некорректный ID пользователя');
                }

                header('Location: /users');
                exit;
            }

            if ($action === 'restore_user') {
                if ($targetId > 0) {
                    $userModel->restore($targetId);
                    \App\Lib\Flash::success('Пользователь восстановлен');
                    \App\Lib\Log::write('restore-user', 'user', $targetId, 'Soft-restore');
                } else {
                    \App\Lib\Flash::error('Некорректный ID пользователя');
                }

                header('Location: /users');
                exit;
            }
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $userModel = new \App\Models\UserModel($pdo);
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

        $users = $userModel->getAllPaginated($perPage, $offset, $showDeleted);
        $total = $userModel->countAll($showDeleted);

        $allRoles = $pdo->query("SELECT name FROM roles ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN);
        $title = 'Управление пользователями';

        ob_start();
        include __DIR__ . '/../../views/users.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function categories(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }
        $pdo = Db::getConnection();
        $categoryModel = new CategoryModel($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $action = $_POST['action'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $id = (int)($_POST['id'] ?? 0);

            switch ($action) {
                case 'create':
                    if ($name) {
                        $categoryModel->create($name);
                        \App\Lib\Flash::success('Категория добавлена');
                    } else {
                        \App\Lib\Flash::error('Название категории не указано');
                    }
                    break;

                case 'update':
                    if ($id && $name) {
                        $categoryModel->update($id, $name);
                        \App\Lib\Flash::success('Категория обновлена');
                    } else {
                        \App\Lib\Flash::error('Недостаточно данных для обновления');
                    }
                    break;

                case 'delete':
                    if ($id) {
                        $categoryModel->delete($id);
                        \App\Lib\Flash::success('Категория скрыта');
                    } else {
                        \App\Lib\Flash::error('ID категории не указан');
                    }
                    break;

                case 'restore':
                    if ($id) {
                        $categoryModel->restore($id);
                        \App\Lib\Flash::success('Категория восстановлена');
                    } else {
                        \App\Lib\Flash::error('ID категории не указан');
                    }
                    break;

                default:
                    \App\Lib\Flash::error('Неизвестное действие');
            }

            header('Location: /admin/categories', true, 303);
            exit;
        }

        $categories = $pdo->query("SELECT * FROM categories WHERE is_deleted = 0 ORDER BY name")->fetchAll();
        $deletedCategories = $pdo->query("SELECT * FROM categories WHERE is_deleted = 1 ORDER BY name")->fetchAll();

        $title = 'Управление категориями';
        ob_start();
        include __DIR__ . '/../../views/admin_categories.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function tags(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }
        $pdo = Db::getConnection();
        $tagModel = new TagModel($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $action = $_POST['action'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $id = (int)($_POST['id'] ?? 0);

            switch ($action) {
                case 'create':
                    if ($name) {
                        $tagModel->create($name);
                        \App\Lib\Flash::success('Тег добавлен');
                    } else {
                        \App\Lib\Flash::error('Название тега не указано');
                    }
                    break;

                case 'update':
                    if ($id && $name) {
                        $tagModel->update($id, $name);
                        \App\Lib\Flash::success('Тег обновлён');
                    } else {
                        \App\Lib\Flash::error('Недостаточно данных для обновления');
                    }
                    break;

                case 'delete':
                    if ($id) {
                        $tagModel->delete($id);
                        \App\Lib\Flash::success('Тег скрыт');
                    } else {
                        \App\Lib\Flash::error('ID тега не указан');
                    }
                    break;

                case 'restore':
                    if ($id) {
                        $tagModel->restore($id);
                        \App\Lib\Flash::success('Тег восстановлен');
                    } else {
                        \App\Lib\Flash::error('ID тега не указан');
                    }
                    break;

                default:
                    \App\Lib\Flash::error('Неизвестное действие');
            }

            header('Location: /admin/tags', true, 303);
            exit;
        }

        $tags = $pdo->query("SELECT * FROM tags WHERE is_deleted = 0 ORDER BY name")->fetchAll();
        $deletedTags = $pdo->query("SELECT * FROM tags WHERE is_deleted = 1 ORDER BY name")->fetchAll();

        $title = 'Управление тегами';
        ob_start();
        include __DIR__ . '/../../views/admin_tags.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function create(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }
        $pdo = Db::getConnection();
        $articleService = new ArticleService($pdo);
        $categoryModel = new CategoryModel($pdo);
        $tagModel = new TagModel($pdo);
        $userModel = new UserModel($pdo);

        $categories = $categoryModel->getAll();
        $tags = $tagModel->getAll();
        $users = $userModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $result = $articleService->create($_POST);
            if ($result['success']) {
                \App\Lib\Flash::success('Статья успешно создана');
                header('Location: /admin');
                exit;
            } else {
                foreach ($result['errors'] as $err) {
                    \App\Lib\Flash::error($err);
                }
            }
        }

        $title = 'Создать статью';
        ob_start();
        include __DIR__ . '/../../views/admin_create.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function edit(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
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
                $article = $articleModel->getById($id);
                if ($article && $article['slug']) {
                    \App\Lib\Flash::success('Статья обновлена');
                    header('Location: /article/' . $id . '-' . urlencode($article['slug']));
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

    public function search(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }
        $pdo = Db::getConnection();
        $articleModel = new ArticleModel($pdo);
        $categoryModel = new CategoryModel($pdo);
        $tagModel = new TagModel($pdo);
        $userModel = new UserModel($pdo);
        $status = $_GET['status'] ?? '';
        if ($status !== '' && !in_array($status, ArticleStatus::all())) {
            $status = '';
        }
        $filters = [
            'query'       => trim($_GET['query'] ?? ''),
            'category_id' => (int)($_GET['category_id'] ?? 0),
            'tag_id'      => (int)($_GET['tag_id'] ?? 0),
            'author_id'   => (int)($_GET['author_id'] ?? 0),
            'status'      => $status
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        [$sql, $params, $total] = SqlHelper::buildPaginatedArticleQuery($pdo, $filters, $page, $perPage);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();

        $articleIds = array_column($articles, 'id');
        $allTags = $tagModel->getTagsForArticles($articleIds);
        $allCategories = $categoryModel->getCategoriesForArticles($articleIds);

        $categories = $categoryModel->getAll();
        $tags = $tagModel->getAll();
        $users = $userModel->getAll();
        $title = 'Расширенный поиск';

        ob_start();
        include __DIR__ . '/../../views/admin_search.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function logs(): void
    {
        if (!Permissions::userCan('admin_dashboard')) {
            http_response_code(403);
            echo '⛔ Доступ только для модераторов и администраторов.';
            exit;
        }

        $pdo = Db::getConnection();

        $userId = (int)($_GET['user_id'] ?? 0);
        $action = trim($_GET['action'] ?? '');

        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';

        $where  = 'WHERE 1=1';
        $params = [];

        if ($userId > 0) {
            $where               .= ' AND l.user_id = :user_id';
            $params[':user_id']   = $userId;
        }

        if ($action !== '') {
            $where              .= ' AND l.action  = :action';
            $params[':action']   = $action;
        }

        if ($from !== '') {
            $where             .= ' AND l.created_at >= :from';
            $params[':from']    = $from . ' 00:00:00';
        }

        if ($to !== '') {
            $where           .= ' AND l.created_at <= :to';
            $params[':to']    = $to   . ' 23:59:59';
        }

        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 50;
        $offset   = ($page - 1) * $perPage;

        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM logs l $where");
        $totalStmt->execute($params);
        $total = (int)$totalStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT l.*, u.username
            FROM   logs l
            LEFT JOIN users u ON u.id = l.user_id
            $where
            ORDER BY l.created_at DESC
            LIMIT  :limit OFFSET :offset
        ");

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        $logs = $stmt->fetchAll();

        $users   = $pdo->query("
            SELECT id, username
            FROM   users
            ORDER BY username
        ")->fetchAll();

        $actions = $pdo->query("
            SELECT DISTINCT action
            FROM   logs
            ORDER  BY action
        ")->fetchAll(\PDO::FETCH_COLUMN);

        $title = 'Журнал действий';
        ob_start();
        include __DIR__ . '/../../views/admin_logs.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }
}
