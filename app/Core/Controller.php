<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view file with extracted variables.
     * View path is relative to app/Views/ (without .php extension).
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("View file [{$view}] not found.");
        }

        require $viewFile;
    }

    /**
     * Send a JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to another URL.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
