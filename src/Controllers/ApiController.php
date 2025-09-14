<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\BbcodeParser;
use App\Lib\Db;
use App\Lib\ApiResponse;
use App\Lib\Permissions;

class ApiController
{
  public function preview(): void
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      ApiResponse::error("Метод не разрешён", 405);
    }

    if (!\App\Lib\Csrf::check()) {
      \App\Lib\Csrf::deny();
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $content = trim($input["content"] ?? "");

    if ($content === "") {
      ApiResponse::error("Поле content обязательно");
    }

    $parser = new BbcodeParser();
    $html = $parser->toHtml($content);

    ApiResponse::success(["html" => '<div class="article-content">' . $html . "</div>"], "preview");
  }

  public function autosave(): void
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      ApiResponse::error("Метод не разрешён", 405);
    }

    if (!\App\Lib\Csrf::check()) {
      \App\Lib\Csrf::deny();
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int) ($data["id"] ?? 0);
    $content = trim($data["content"] ?? "");

    if ($id <= 0 || $content === "") {
      ApiResponse::error("Некорректные данные");
    }

    $pdo = Db::getConnection();
    $stmt = $pdo->prepare("
            UPDATE articles 
            SET content = ?, updated_at = NOW() 
            WHERE id = ? AND status = 'draft'
        ");
    $stmt->execute([$content, $id]);

    ApiResponse::success(["message" => "Сохранено"], "autosave");
  }

  public function uploadImage(): void
  {
    if (!Permissions::userCan("upload_image")) {
      http_response_code(403);
      header("Content-Type: application/json");
      echo json_encode([
        "error" => "⛔ Доступ к загрузке изображений только для верифицированных и выше.",
      ]);
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      ApiResponse::error("Метод не разрешён", 405);
    }

    if (!\App\Lib\Csrf::check()) {
      \App\Lib\Csrf::deny();
    }

    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
      ApiResponse::error("Файл не получен");
    }

    $f = $_FILES["file"];

    $maxSize = 5 * 1024 * 1024;
    if ($f["size"] > $maxSize) {
      ApiResponse::error("Файл слишком большой (максимум 5MB)");
    }

    $ext = strtolower(pathinfo($f["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
    if (!in_array($ext, $allowed, true)) {
      ApiResponse::error("Недопустимый формат изображения");
    }

    $imgInfo = @getimagesize($f["tmp_name"]);
    if ($imgInfo === false) {
      ApiResponse::error("Файл не является изображением");
    }

    $maxWidth = 4096;
    $maxHeight = 4096;
    if ($imgInfo[0] > $maxWidth || $imgInfo[1] > $maxHeight) {
      ApiResponse::error("Изображение слишком большое (максимум 4096x4096 пикселей)");
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f["tmp_name"]);
    if (strpos($mime, "image/") !== 0) {
      ApiResponse::error("Файл не является изображением");
    }

    $subdir = date("Y/m/d");
    $baseDir = dirname(__DIR__, 2);
    $uploadDir = $baseDir . "/public_html/uploads/" . $subdir;
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
      ApiResponse::error("Не удалось создать каталог для загрузки");
    }

    if (!is_writable($uploadDir)) {
      ApiResponse::error("Папка uploads недоступна для записи");
    }

    $filename = bin2hex(random_bytes(8)) . "." . $ext;
    $target = $uploadDir . "/" . $filename;

    if (!move_uploaded_file($f["tmp_name"], $target)) {
      ApiResponse::error("Не удалось сохранить файл");
    }

    @chmod($target, 0644);

    $url = "/uploads/" . $subdir . "/" . $filename;
    ApiResponse::success(["url" => $url]);
  }

  private function requireAdmin(): void
  {
    if (!Permissions::userCan("manage_users")) {
      http_response_code(403);
      header("Content-Type: application/json");
      echo json_encode(["error" => "⛔ Только администраторам доступно действие."]);
      exit();
    }
  }

  public function searchUsers(): void
  {
    $this->requireAdmin();

    $query = trim($_GET["q"] ?? "");
    $page = max(1, (int) ($_GET["page"] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    $pdo = \App\Lib\Db::getConnection();
    $model = new \App\Models\UserModel($pdo);

    if ($query !== "") {
      $users = $model->searchPaginated($query, $perPage, $offset);
      $total = $model->countSearch($query);
    } else {
      $users = $model->getAllPaginated($perPage, $offset);
      $total = $model->countAll();
    }

    $allRoles = [];
    $stmt = $pdo->query("SELECT name FROM roles ORDER BY name");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $allRoles[] = $row["name"];
    }

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(
      [
        "users" => array_map(function ($u) {
          return [
            "id" => $u["id"],
            "username" => $u["username"],
            "created_at" => $u["created_at"],
            "roles" => $u["roles"] ?? [],
          ];
        }, $users),
        "total" => $total,
        "page" => $page,
        "perPage" => $perPage,
        "allRoles" => $allRoles,
      ],
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    );

    exit();
  }

  public function users(): void
  {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
      ApiResponse::error("Метод не разрешён", 405);
    }

    $q = trim((string) ($_GET["q"] ?? ""));
    $page = max(1, (int) ($_GET["page"] ?? 1));
    $perPage = 50;
    $offset = ($page - 1) * $perPage;
    $showDeleted = isset($_GET["show_deleted"]) && $_GET["show_deleted"] === "1";

    $pdo = Db::getConnection();
    $userModel = new \App\Models\UserModel($pdo);

    if ($q !== "") {
      $users = $userModel->searchPaginated($q, $perPage, $offset);
      $total = $userModel->countSearch($q);
    } else {
      $users = $userModel->getAllPaginated($perPage, $offset, $showDeleted);
      $total = $userModel->countAll($showDeleted);
    }

    $allRoles = $pdo->query("SELECT name FROM roles ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN);

    ApiResponse::success(
      [
        "users" => array_values($users),
        "total" => $total,
        "page" => $page,
        "perPage" => $perPage,
        "allRoles" => $allRoles,
      ],
      "users",
    );
  }
}
