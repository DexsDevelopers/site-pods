<?php
/**
 * ========================================
 * FUNÇÕES AUXILIARES GERAIS
 * ========================================
 * 
 * Utilitários para logging, validação,
 * sanitização e operações comuns.
 */

// ========================================
// LOGGING
// ========================================

/**
 * Registra uma mensagem de info no log
 */
function logInfo(string $message, string $module = 'APP'): void {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [INFO] [{$module}] {$message}\n";
    error_log($logMessage, 3, LOG_PATH . 'info.log');
}

/**
 * Registra um erro no log
 */
function logError(string $message, string $module = 'APP'): void {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [ERROR] [{$module}] {$message}\n";
    error_log($logMessage, 3, LOG_PATH . 'error.log');
}

/**
 * Registra um aviso no log
 */
function logWarning(string $message, string $module = 'APP'): void {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [WARNING] [{$module}] {$message}\n";
    error_log($logMessage, 3, LOG_PATH . 'warning.log');
}

/**
 * Registra um debug no log
 */
function logDebug(string $message, string $module = 'APP'): void {
    if (DEBUG_MODE) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [DEBUG] [{$module}] {$message}\n";
        error_log($logMessage, 3, LOG_PATH . 'debug.log');
    }
}

// ========================================
// SANITIZAÇÃO E VALIDAÇÃO
// ========================================

/**
 * Sanitiza string (remove tags e caracteres perigosos)
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida um email
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida uma URL
 */
function validateUrl(string $url): bool {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Valida um número inteiro
 */
function validateInteger($value): bool {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/**
 * Valida um número de ponto flutuante
 */
function validateFloat($value): bool {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

/**
 * Valida um telefone (formato brasileiro)
 */
function validatePhone(string $phone): bool {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

// ========================================
// SEGURANÇA
// ========================================

/**
 * Criptografa uma senha usando bcrypt
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica se uma senha corresponde ao hash
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Gera um token CSRF
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida um token CSRF
 */
function validateCSRFToken(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Gera um token aleatório
 */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

// ========================================
// RESPOSTAS E REDIRECIONAMENTOS
// ========================================

/**
 * Retorna uma resposta JSON
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redireciona para outra URL
 */
function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

/**
 * Redireciona com mensagem de sucesso
 */
function redirectWithSuccess(string $url, string $message): void {
    $_SESSION['success_message'] = $message;
    redirect($url);
}

/**
 * Redireciona com mensagem de erro
 */
function redirectWithError(string $url, string $message): void {
    $_SESSION['error_message'] = $message;
    redirect($url);
}

// ========================================
// UTILITÁRIOS
// ========================================

/**
 * Formata um valor monetário para BRL
 */
function formatCurrency(float $value): string {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Obtém e limpa uma mensagem da sessão
 */
function getSessionMessage(string $type): ?string {
    $key = $type . '_message';
    $message = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $message;
}

/**
 * Verifica se o usuário está autenticado
 */
function isAuthenticated(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtém o ID do usuário autenticado
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtém o usuário autenticado
 */
function getCurrentUser(): ?array {
    if (!isAuthenticated()) {
        return null;
    }
    
    // TODO: Buscar dados do usuário do banco de dados
    return $_SESSION['user'] ?? null;
}

/**
 * Valida se é uma requisição AJAX
 */
function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtém o método HTTP atual
 */
function getRequestMethod(): string {
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * Verifica se é uma requisição POST
 */
function isPost(): bool {
    return getRequestMethod() === 'POST';
}

/**
 * Verifica se é uma requisição GET
 */
function isGet(): bool {
    return getRequestMethod() === 'GET';
}
