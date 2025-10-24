<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Criar novo pedido
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('Dados inválidos');
            }
            
            // Validar dados obrigatórios
            $required = ['nome', 'email', 'telefone', 'endereco', 'items', 'total'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Campo obrigatório: $field");
                }
            }
            
            // Inserir pedido no banco
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    nome, email, telefone, endereco, numero, complemento, 
                    bairro, cidade, estado, cep, total, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $data['nome'],
                $data['email'],
                $data['telefone'],
                $data['endereco'],
                $data['numero'] ?? '',
                $data['complemento'] ?? '',
                $data['bairro'] ?? '',
                $data['cidade'] ?? '',
                $data['estado'] ?? '',
                $data['cep'] ?? '',
                $data['total']
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Inserir itens do pedido
            foreach ($data['items'] as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (
                        order_id, product_id, product_name, price, quantity, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $orderId,
                    $item['id'],
                    $item['nome'],
                    $item['preco'] ?? $item['preco_final'] ?? 0,
                    $item['qty'] ?? $item['quantity'] ?? 1
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Pedido criado com sucesso'
            ]);
            break;
            
        case 'GET':
            // Buscar pedidos
            $orderId = $_GET['id'] ?? null;
            
            if ($orderId) {
                // Buscar pedido específico
                $stmt = $pdo->prepare("
                    SELECT o.*, 
                           GROUP_CONCAT(
                               CONCAT(oi.product_name, ' (', oi.quantity, 'x)')
                               SEPARATOR ', '
                           ) as items_summary
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.id = ?
                    GROUP BY o.id
                ");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new Exception('Pedido não encontrado');
                }
                
                echo json_encode([
                    'success' => true,
                    'order' => $order
                ]);
            } else {
                // Buscar todos os pedidos
                $stmt = $pdo->query("
                    SELECT o.*, 
                           GROUP_CONCAT(
                               CONCAT(oi.product_name, ' (', oi.quantity, 'x)')
                               SEPARATOR ', '
                           ) as items_summary
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    GROUP BY o.id
                    ORDER BY o.created_at DESC
                ");
                $orders = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'orders' => $orders
                ]);
            }
            break;
            
        case 'PUT':
            // Atualizar status do pedido
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['status'])) {
                throw new Exception('ID e status são obrigatórios');
            }
            
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$data['status'], $data['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso'
            ]);
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
