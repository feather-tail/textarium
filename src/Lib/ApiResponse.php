<?php
namespace App\Lib;

class ApiResponse
{
    public static function success(array $attributes = [], string $type = 'generic', int $code = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);

        echo json_encode([
            'success' => true,
            'code' => $code,
            'data' => [
                'type' => $type,
                'attributes' => $attributes
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function error(array|string $errors, int $code = 400): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);

        $errors = is_array($errors) ? array_values($errors) : [$errors];

        echo json_encode([
            'success' => false,
            'code' => $code,
            'data' => [
                'type' => 'error',
                'attributes' => [
                    'error' => $errors[0],
                    'errors' => $errors
                ]
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
}
