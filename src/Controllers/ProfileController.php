<?php
namespace App\Controllers;

use App\Lib\Permissions;
use App\Lib\Db;
use App\Lib\Flash;
use App\Lib\Log;

class ProfileController extends BaseController
{
    public function show(): void
    {
        if (!Permissions::userCan('change_password')) {
            http_response_code(403);
            echo '⛔ Доступ запрещён.';
            exit;
        }

        $user = \App\Lib\Auth::currentUser();
        $title = 'Мой профиль';

        ob_start();
        include __DIR__ . '/../../views/profile.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function changePassword(): void
    {
        if (!Permissions::userCan('change_password')) {
            http_response_code(403);
            echo '⛔ Доступ запрещён.';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $confirm         = $_POST['confirm_password'] ?? '';

            $user = \App\Lib\Auth::currentUser();
            $pdo  = Db::getConnection();

            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $hash = $stmt->fetchColumn();

            if (!$hash || !password_verify($currentPassword, $hash)) {
                Flash::error('Неверный текущий пароль');
            } elseif ($newPassword !== $confirm) {
                Flash::error('Новый пароль и подтверждение не совпадают');
            } elseif (strlen($newPassword) < 6) {
                Flash::error('Пароль должен содержать не менее 6 символов');
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $user['id']]);

                Log::write('change-password', 'user', $user['id'], 'Сменил свой пароль');
                Flash::success('Пароль успешно изменён');
                header('Location: /profile');
                exit;
            }
        }

        $title = 'Сменить пароль';
        $activeProfile = 'change-password';
        ob_start();
        include __DIR__ . '/../../views/change_password.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }
}
