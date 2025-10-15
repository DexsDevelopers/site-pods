<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');
$payload = json_decode(file_get_contents('php://input'), true) ?? [];

if (!($_SESSION['cart'] ?? [])) { echo json_encode(['success'=>false,'message'=>'Carrinho vazio']); exit; }

// Basic validation
$name = trim($payload['name'] ?? '');
$email = trim($payload['email'] ?? '');
$phone = trim($payload['phone'] ?? '');
$address = trim($payload['address'] ?? '');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $address === '') {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Dados inválidos']);
  exit;
}

try {
  $pdo->beginTransaction();
  $cart = $_SESSION['cart'];
  $ids = array_keys($cart);
  $in = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($in)");
  $stmt->execute($ids);
  $total = 0.0; $lines = [];
  foreach ($stmt as $row) {
    $qty = (int)$cart[$row['id']];
    $price = (float)$row['price'];
    $sub = $qty * $price; $total += $sub;
    $lines[] = ['product_id'=>$row['id'],'qty'=>$qty,'price'=>$price,'subtotal'=>$sub];
  }

  $ins = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount) VALUES (:n,:e,:p,:a,:t)");
  $ins->execute([':n'=>$name, ':e'=>$email, ':p'=>$phone, ':a'=>$address, ':t'=>$total]);
  $orderId = (int)$pdo->lastInsertId();

  $insL = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (:o,:p,:q,:pr,:s)");
  foreach ($lines as $ln) {
    $insL->execute([':o'=>$orderId, ':p'=>$ln['product_id'], ':q'=>$ln['qty'], ':pr'=>$ln['price'], ':s'=>$ln['subtotal']]);
  }

  $pdo->commit();
  $_SESSION['cart'] = [];
  echo json_encode(['success'=>true,'orderId'=>$orderId]);
} catch (Throwable $e) {
  $pdo->rollBack();
  error_log('['.date('Y-m-d H:i:s')."] [ERROR] [checkout] ".$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Erro ao processar pedido']);
}


