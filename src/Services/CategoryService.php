<?php
namespace App\Services;

use PDO;

class CategoryService
{
    use AttachServiceTrait;
    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function attach(int $articleId, array $newCategoryIds): void
    {
        $this->attachGeneric($articleId, $newCategoryIds, 'article_categories', 'category_id');
    }
}
