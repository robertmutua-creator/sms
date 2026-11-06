<?php

namespace App\Core;

abstract class Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function view(string $path, array $data = []): void
    {
        $data['content'] = $path;
        extract($data);
        require_once __DIR__ . "/../Views/layout.php";
    }

    protected function flash(string $type, ?string $message = null): void
    {
        if ($message === null) {
            $message = strtolower($type) === 'success' ? 'Success.' : 'Failed.';
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function textFormat(string $text, string $format): string
    {
        return match ($format) {
            'upper' => strtoupper($text),
            'lower' => strtolower($text),
            'title' => ucwords(strtolower($text)),
            default => $text,
        };
    }

    protected function handleFileUpload(array $file, string $folder): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . "/../storage/{$folder}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('file_', true) . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return "storage/{$folder}/{$filename}";
        }

        return null;
    }

    protected function deleteFile(?string $path): bool
    {
        if ($path && file_exists(__DIR__ . "/../" . $path)) {
            return unlink(__DIR__ . "/../" . $path);
        }
        return false;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function checkRole(string $requiredRole): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === $requiredRole;
    }

    protected function setToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function confirmToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
