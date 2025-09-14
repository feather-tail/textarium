<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Flash;
use App\Services\MailerService;

class LoginController extends BaseController
{
    public function login(): void
    {
        $errors = [];
        $title = 'Вход';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $loginResult = Auth::login($username, $password);

            if ($loginResult === 'success') {
                header('Location: /');
                exit;
            }

            switch ($loginResult) {
                case 'not_verified':
                    $errors[] = 'Подтвердите email перед входом.';
                    break;
                case 'blocked':
                    $errors[] = 'Слишком много попыток входа. Попробуйте позже.';
                    break;
                case 'not_found':
                default:
                    $errors[] = 'Неверный логин или пароль';
                    break;
            }

            foreach ($errors as $e) {
                Flash::error($e);
            }
        }

        ob_start();
        include __DIR__ . '/../../views/login.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    public function register(): void
    {
        $errors = [];
        $title = 'Регистрация';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';

            if (mb_strlen($username) < 3) {
                $errors[] = 'Логин слишком короткий';
            }

            if (!preg_match('/^[\p{L}0-9 ]+$/u', $username)) {
                $errors[] = 'Логин может содержать только буквы, цифры и пробелы';
            }

            if (mb_strlen($email) > 50) {
                $errors[] = 'Email не должен превышать 50 символов';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Некорректный email';
            }

            if (mb_strlen($password) < 6) {
                $errors[] = 'Пароль слишком короткий';
            }

            if ($password !== $confirm) {
                $errors[] = 'Пароли не совпадают';
            }

            if (empty($errors)) {
                try {
                    $pdo = \App\Lib\Db::getConnection();

                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);

                    if ((int)$stmt->fetchColumn() > 0) {
                        $errors[] = 'Пользователь с таким логином или email уже существует.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $token = bin2hex(random_bytes(32));

                        $stmt = $pdo->prepare("
                            INSERT INTO users (username, email, password_hash, email_token)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$username, $email, $hash, $token]);

                        (new MailerService())->sendVerification($email, $token);

                        Flash::success('Регистрация прошла успешно. Проверьте email для подтверждения.');
                        header('Location: /login');
                        exit;
                    }
                } catch (\PDOException $e) {
                    if (str_contains($e->getMessage(), 'email_unique') || str_contains($e->getMessage(), 'username')) {
                        $errors[] = 'Логин или email уже зарегистрированы.';
                    } else {
                        $errors[] = 'Ошибка регистрации. Попробуйте позже.';
                    }
                }
            }

            foreach ($errors as $e) {
                Flash::error($e);
            }
        }

        ob_start();
        include __DIR__ . '/../../views/register.php';
        $content = ob_get_clean();

        include __DIR__ . '/../../views/layout.php';
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        $pdo = \App\Lib\Db::getConnection();

        if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            http_response_code(400);
            echo '<h1>⛔ Неверный токен</h1>';
            return;
        }

        $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo '<h1>⛔ Токен не найден или уже использован</h1>';
            return;
        }

        if ($user['is_verified']) {
            \App\Lib\Flash::info('Email уже подтверждён.');
            header('Location: /login');
            exit;
        }

        $pdo->prepare("
            UPDATE users
            SET is_verified = 1, email_token = NULL, verified_at = NOW()
            WHERE id = ?
        ")->execute([$user['id']]);

        \App\Lib\Log::write('verify', 'user', $user['id']);

        \App\Lib\Flash::success('Email подтверждён. Теперь вы можете войти.');
        header('Location: /login');
        exit;
    }
}
