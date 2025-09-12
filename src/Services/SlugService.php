<?php
namespace App\Services;

use PDO;

class SlugService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function generate(string $title): string
    {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
            'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
            'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
            'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','Ж'=>'Zh','З'=>'Z',
            'И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
            'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch',
            'Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya'
        ];

        $translit = strtr($title, $map);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($translit)));
        $slug = trim($slug, '-');

        $base = $slug ?: 'article';
        $slug = $base;
        $i = 1;

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");

        while (true) {
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) {
                break;
            }
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function generateOrReuse(string $title, ?string $existingSlug = null, ?string $existingTitle = null): string
    {
        $normalizedTitle = trim($title);
        $normalizedExistingTitle = trim((string)$existingTitle);

        if (!empty($existingSlug) && $normalizedTitle === $normalizedExistingTitle) {
            return $existingSlug;
        }

        return $this->generate($title);
    }

}
