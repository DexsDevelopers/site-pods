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
    
    // GET - Listar clientes
    if ($method === 'GET' && $action === 'list') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $stmt = $db->getConnection()->prepare(
            "SELECT u.id, u.nome, u.email, u.telefone, u.criado_em,
                    COUNT(o.id) as total_pedidos,
                    COALESCE(SUM(o.total), 0) as total_gasto
             FROM users u
             LEFT JOIN orders o ON u.id = o.user_id
             WHERE u.role = 'customer'
             GROUP BY u.id
             ORDER BY u.criado_em DESC
             LIMIT 100"
        );
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(['success' => true, 'data' => $customers]);
    }
    
    // GET - Detalhes do cliente
    if ($method === 'GET' && $action === 'detail') {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $stmt = $db->getConnection()->prepare(
            "SELECT u.*, 
                    COUNT(o.id) as total_pedidos,
                    COALESCE(SUM(o.total), 0) as total_gasto
             FROM users u
             LEFT JOIN orders o ON u.id = o.user_id
             WHERE u.id = ? AND u.role = 'customer'
             GROUP BY u.id"
        );
        $stmt->execute([$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            jsonResponse(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }
        
        // Últimos pedidos
        $stmt = $db->getConnection()->prepare(
            "SELECT id, total, status, criado_em FROM orders 
             WHERE user_id = ? 
             ORDER BY criado_em DESC 
             LIMIT 10"
        );
        $stmt->execute([$id]);
        $customer['pedidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(['success' => true, 'data' => $customer]);
    }
    
    // POST - Criar usuário/cliente
    if ($method === 'POST' && $action === 'create') {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        $nome = $dados['nome'] ?? null;
        $email = $dados['email'] ?? null;
        $telefone = $dados['telefone'] ?? null;
        $password = $dados['password'] ?? null;
        
        if (!$nome || !$email || !$password) {
            jsonResponse(['success' => false, 'message' => 'Campos obrigatórios faltando'], 400);
        }
        
        if (!validateEmail($email)) {
            jsonResponse(['success' => false, 'message' => 'Email inválido'], 400);
        }
        
        // Verificar se email já existe
        $stmt = $db->getConnection()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email já cadastrado'], 409);
        }
        
        $password_hash = hashPassword($password);
        
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO users (nome, email, telefone, password, role) 
             VALUES (?, ?, ?, ?, 'customer')"
        );
        
        $result = $stmt->execute([$nome, $email, $telefone, $password_hash]);
        
        if ($result) {
            $id = $db->getConnection()->lastInsertId();
            jsonResponse(['success' => true, 'message' => 'Cliente criado com sucesso', 'id' => $id]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Erro ao criar cliente'], 500);
        }
    }
    
    // PUT - Atualizar cliente
    if ($method === 'PUT' && $action === 'update') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $id = $_GET['id'] ?? null;
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $updates = [];
        $params = [];
        
        foreach (['nome', 'email', 'telefone'] as $field) {
            if (isset($dados[$field])) {
                $updates[] = "$field = ?";
                $params[] = $dados[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'Nenhum campo para atualizar'], 400);
        }
        
        $params[] = $id;
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ? AND role = 'customer'";
        $stmt = $db->getConnection()->prepare($query);
        $result = $stmt->execute($params);
        
        jsonResponse(['success' => $result, 'message' => $result ? 'Cliente atualizado' : 'Erro ao atualizar']);
    }
    
    jsonResponse(['success' => false, 'message' => 'Ação não encontrada'], 404);
    
} catch (Exception $e) {
    logError($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Erro do servidor'], 500);
}
