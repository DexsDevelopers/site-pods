<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "customer"');
        $result = $stmt->execute([$id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cliente deletado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao deletar cliente']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}
