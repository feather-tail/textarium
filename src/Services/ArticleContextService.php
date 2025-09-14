<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use App\Models\ArticleModel;
use App\Models\TagModel;
use App\Models\CategoryModel;
use App\Models\UserModel;

class ArticleContextService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getEditContext(int $id): ?array
    {
        $articleModel = new ArticleModel($this->pdo);
        $tagModel = new TagModel($this->pdo);
        $categoryModel = new CategoryModel($this->pdo);
        $userModel = new UserModel($this->pdo);

        $article = $articleModel->getById($id);
        if (!$article) return null;

        return [
            'article' => $article,
            'tags' => $tagModel->getAll(),
            'categories' => $categoryModel->getAll(),
            'users' => $userModel->getAll(),
            'selectedTags' => $tagModel->getTagsForArticle($id),
            'selectedCategories' => $categoryModel->getForArticle($id),
        ];
    }
}
