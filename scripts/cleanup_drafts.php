<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Lib\Db;

$pdo = Db::getConnection();

$stmt = $pdo->prepare("
    DELETE FROM articles
    WHERE is_draft = 1
      AND created_at < (NOW() - INTERVAL 90 DAY)
");

$stmt->execute();
$count = $stmt->rowCount();

echo "[" . date('Y-m-d H:i:s') . "] Удалено старых черновиков: $count\n";
