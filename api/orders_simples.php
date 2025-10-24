<?php
require_once '../includes/config_hostinger.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Conexão direta sem classe Database
    $host = 'localhost';
    $db = 'u853242961_loja_pods';
    $user = 'u853242961_pods_saluc';
    $pass = 'Lucastav8012@';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    if ($method === 'GET') {
        // Buscar pedidos
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(o.nome LIKE ? OR o.email LIKE ? OR o.telefone LIKE ?)';
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($status) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM orders o WHERE $whereClause";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // Buscar pedidos
        $sql = "SELECT o.*, COUNT(oi.id) as total_items
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE $whereClause
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pedidos = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $pedidos,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ]);

    } elseif ($method === 'PUT') {
        // Atualizar status do pedido
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id']) || empty($data['status'])) {
            throw new Exception('ID do pedido e status são obrigatórios.');
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Status do pedido atualizado com sucesso.'
        ]);

    } elseif ($method === 'DELETE') {
        // Deletar pedido
        $id = $_GET['id'] ?? null;
        
        if (empty($id)) {
            throw new Exception('ID do pedido é obrigatório.');
        }

        $pdo->beginTransaction();
        
        // Deletar itens do pedido primeiro
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);
        
        // Deletar pedido
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pedido deletado com sucesso.'
        ]);

    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método não permitido.'
        ]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
