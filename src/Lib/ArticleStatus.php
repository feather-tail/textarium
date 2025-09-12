<?php
namespace App\Lib;

final class ArticleStatus
{
    public const DRAFT    = 'draft';
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const DELETED  = 'deleted';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::PENDING,
            self::APPROVED,
            self::DELETED
        ];
    }
}
