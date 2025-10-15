<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';
$productId = (int)($input['productId'] ?? 0);
$quantity = max(1, (int)($input['quantity'] ?? 1));

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

try {
    if ($action === 'add') {
        $exists = $pdo->prepare('SELECT id FROM products WHERE id = :id AND is_active = 1');
        $exists->execute([':id' => $productId]);
        if (!$exists->fetch()) throw new Exception('Produto inválido.');
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
    } elseif ($action === 'set') {
        if ($quantity < 1) $quantity = 1;
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $quantity;
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    $count = array_sum($_SESSION['cart']);
    echo json_encode(['success' => true, 'count' => $count]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Erro no carrinho']);
}


