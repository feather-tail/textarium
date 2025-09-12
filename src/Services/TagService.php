<?php
namespace App\Services;

use PDO;

class TagService
{
    use AttachServiceTrait;
    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function attach(int $articleId, array $newTagIds): void
    {
        $this->attachGeneric($articleId, $newTagIds, 'article_tags', 'tag_id');
    }
}
