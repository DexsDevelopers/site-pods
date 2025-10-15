<?php
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(APP_URL, '/') . '/' . ltrim($path, '/'); }
function redirect(string $path): void { header('Location: ' . url($path)); exit; }
function money(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_check(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function current_path(): string {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
}


