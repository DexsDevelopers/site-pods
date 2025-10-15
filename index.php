<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db_connect.php';

// Simple router using REQUEST_URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = '/' . ltrim($requestUri, '/');

// Route map
$routes = [
    '/' => 'pages/home.php',
    '/loja' => 'pages/shop.php',
    '/carrinho' => 'pages/cart.php',
    '/checkout' => 'pages/checkout.php',
    '/sucesso' => 'pages/success.php',
    // Admin
    '/admin' => 'admin/index.php',
    '/admin/login' => 'admin/login.php',
    '/admin/logout' => 'admin/logout.php',
    '/admin/produtos' => 'admin/products.php',
    '/admin/produto' => 'admin/product_edit.php',
    '/admin/categorias' => 'admin/categories.php',
    '/admin/categoria' => 'admin/category_edit.php',
    '/admin/pedidos' => 'admin/orders.php',
    // API
    '/api/cart' => 'api/cart.php',
    '/api/checkout' => 'api/checkout.php',
];

// Dynamic product route: /produto/{slug}
if (preg_match('#^/produto/([a-z0-9\-]+)$#i', $requestUri, $m)) {
    $params = ['slug' => $m[1]];
    $page = 'pages/product.php';
} else {
    $page = $routes[$requestUri] ?? null;
    $params = [];
}

// Serve API endpoints directly (JSON)
if ($page && str_starts_with($page, 'api/')) {
    require __DIR__ . '/' . $page;
    exit;
}

// 404 handler
if (!$page || !file_exists(__DIR__ . '/' . $page)) {
    http_response_code(404);
    $page = 'pages/404.php';
}

// Render
$pageTitle = 'Pods Store';
include __DIR__ . '/templates/header.php';
include __DIR__ . '/' . $page;
include __DIR__ . '/templates/footer.php';


