<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

include '../includes/config.php';
include '../includes/db.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

try {
    $db = Database::getInstance();
    
    // GET - Listar pedidos
    if ($method === 'GET' && $action === 'list') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $stmt = $db->getConnection()->prepare(
            "SELECT o.*, u.nome as cliente_nome, u.email 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             ORDER BY o.criado_em DESC 
             LIMIT 100"
        );
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(['success' => true, 'data' => $orders]);
    }
    
    // GET - Detalhes do pedido
    if ($method === 'GET' && $action === 'detail') {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $stmt = $db->getConnection()->prepare(
            "SELECT o.*, u.nome, u.email 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE o.id = ?"
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }
        
        // Items do pedido
        $stmt = $db->getConnection()->prepare(
            "SELECT oi.*, p.nome, p.preco FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?"
        );
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $order['items'] = $items;
        
        jsonResponse(['success' => true, 'data' => $order]);
    }
    
    // PUT - Atualizar status do pedido
    if ($method === 'PUT' && $action === 'update_status') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $id = $_GET['id'] ?? null;
        $dados = json_decode(file_get_contents('php://input'), true);
        $status = $dados['status'] ?? null;
        
        if (!$id || !$status) {
            jsonResponse(['success' => false, 'message' => 'ID e status obrigatórios'], 400);
        }
        
        $statuses = ['pendente', 'enviado', 'entregue', 'cancelado'];
        if (!in_array($status, $statuses)) {
            jsonResponse(['success' => false, 'message' => 'Status inválido'], 400);
        }
        
        $stmt = $db->getConnection()->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);
        
        jsonResponse(['success' => $result, 'message' => $result ? 'Status atualizado' : 'Erro ao atualizar']);
    }
    
    // POST - Criar pedido
    if ($method === 'POST' && $action === 'create') {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        $user_id = $dados['user_id'] ?? null;
        $items = $dados['items'] ?? [];
        $total = $dados['total'] ?? 0;
        
        if (!$user_id || empty($items)) {
            jsonResponse(['success' => false, 'message' => 'Dados incompletos'], 400);
        }
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->getConnection()->prepare(
                "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pendente')"
            );
            $stmt->execute([$user_id, $total]);
            $order_id = $db->getConnection()->lastInsertId();
            
            $stmt = $db->getConnection()->prepare(
                "INSERT INTO order_items (order_id, product_id, quantidade, preco) 
                 VALUES (?, ?, ?, ?)"
            );
            
            foreach ($items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantidade'],
                    $item['preco']
                ]);
            }
            
            $db->commit();
            
            jsonResponse(['success' => true, 'message' => 'Pedido criado com sucesso', 'order_id' => $order_id]);
        } catch (Exception $e) {
            $db->rollback();
            jsonResponse(['success' => false, 'message' => 'Erro ao criar pedido'], 500);
        }
    }
    
    jsonResponse(['success' => false, 'message' => 'Ação não encontrada'], 404);
    
} catch (Exception $e) {
    logError($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Erro do servidor'], 500);
}
