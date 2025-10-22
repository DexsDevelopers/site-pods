<?php
/**
 * ========================================
 * CONFIGURAÇÃO PARA HOSTINGER
 * ========================================
 * 
 * Configuração direta sem .env para Hostinger
 */

// Detecta o diretório raiz do projeto
define('PROJECT_ROOT', dirname(dirname(__FILE__)));

// ========================================
// CONFIGURAÇÕES DO BANCO DE DADOS - HOSTINGER
// ========================================
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'u853242961_loja_pods');
define('DB_USER', 'u853242961_pods_saluc');
define('DB_PASSWORD', 'Lucastav8012@');
define('DB_CHARSET', 'utf8mb4');

// DSN para PDO
define('DATABASE_URL', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// ========================================
// CONFIGURAÇÕES DA APLICAÇÃO
// ========================================
define('APP_NAME', 'Wazzy Pods');
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://wazzypods.com'); // Altere para seu domínio

// ========================================
// CONFIGURAÇÕES DE SESSÃO
// ========================================
define('SESSION_LIFETIME', 3600);
define('CSRF_TOKEN_LENGTH', 32);
define('HASH_ALGORITHM', 'bcrypt');

// ========================================
// CONFIGURAÇÕES DE LOGS
// ========================================
define('LOG_LEVEL', 'info');
define('LOG_PATH', PROJECT_ROOT . '/logs/');

// Cria diretório de logs se não existir
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// ========================================
// CONFIGURAÇÕES DE UPLOAD
// ========================================
define('UPLOAD_PATH', PROJECT_ROOT . '/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ========================================
// FUSO HORÁRIO
// ========================================
date_default_timezone_set('America/Sao_Paulo');

// ========================================
// MODO DE PRODUÇÃO
// ========================================
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', APP_DEBUG ? 1 : 0);
}

// ========================================
// INICIA SESSÃO
// ========================================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => APP_URL !== 'http://localhost',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
?>
