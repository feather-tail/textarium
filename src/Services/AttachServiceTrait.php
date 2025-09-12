<?php
namespace App\Services;

trait AttachServiceTrait
{
    public function attachGeneric(int $articleId, array $newIds, string $linkTable, string $keyColumn): void
    {
        $stmt = $this->pdo->prepare("SELECT {$keyColumn}, is_deleted FROM {$linkTable} WHERE article_id = ?");
        $stmt->execute([$articleId]);

        $existing = [];
        foreach ($stmt->fetchAll() as $row) {
            $existing[(int)$row[$keyColumn]] = (int)$row['is_deleted'];
        }

        $toAddOrRestore = array_diff($newIds, array_keys($existing));

        $deletedIds    = array_keys(array_filter($existing, fn($deleted) => $deleted === 1));
        $toReactivate  = array_intersect($deletedIds, $newIds);

        $toDeactivate  = array_diff(
            array_keys(array_filter($existing, fn($deleted) => $deleted === 0)),
            $newIds
        );

        if ($toReactivate) {
            $in     = implode(',', array_fill(0, count($toReactivate), '?'));
            $params = array_merge([$articleId], $toReactivate);
            $this->pdo
                 ->prepare("UPDATE {$linkTable} SET is_deleted = 0 WHERE article_id = ? AND {$keyColumn} IN ($in)")
                 ->execute($params);
        }

        if ($toDeactivate) {
            $in     = implode(',', array_fill(0, count($toDeactivate), '?'));
            $params = array_merge([$articleId], $toDeactivate);
            $this->pdo
                 ->prepare("UPDATE {$linkTable} SET is_deleted = 1 WHERE article_id = ? AND {$keyColumn} IN ($in)")
                 ->execute($params);
        }

        if ($toAddOrRestore) {
            $insert = $this->pdo->prepare(
                "INSERT INTO {$linkTable} (article_id, {$keyColumn}, is_deleted) VALUES (?, ?, 0)"
            );
            foreach ($toAddOrRestore as $id) {
                $insert->execute([$articleId, $id]);
            }
        }
    }
}
