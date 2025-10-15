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
    
    // GET - Listar categorias
    if ($method === 'GET' && $action === 'list') {
        $stmt = $db->getConnection()->prepare("SELECT id, nome, descricao, icon FROM categories ORDER BY nome");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(['success' => true, 'data' => $categories]);
    }
    
    // POST - Criar categoria (Admin)
    if ($method === 'POST' && $action === 'create') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $dados = json_decode(file_get_contents('php://input'), true);
        $nome = $dados['nome'] ?? null;
        $descricao = $dados['descricao'] ?? null;
        $icon = $dados['icon'] ?? null;
        
        if (!$nome) {
            jsonResponse(['success' => false, 'message' => 'Nome obrigatório'], 400);
        }
        
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO categories (nome, descricao, icon) VALUES (?, ?, ?)"
        );
        
        $result = $stmt->execute([$nome, $descricao, $icon]);
        
        if ($result) {
            $id = $db->getConnection()->lastInsertId();
            jsonResponse(['success' => true, 'message' => 'Categoria criada com sucesso', 'id' => $id]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Erro ao criar categoria'], 500);
        }
    }
    
    // PUT - Atualizar categoria
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
        
        foreach (['nome', 'descricao', 'icon'] as $field) {
            if (isset($dados[$field])) {
                $updates[] = "$field = ?";
                $params[] = $dados[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'Nenhum campo para atualizar'], 400);
        }
        
        $params[] = $id;
        $query = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->getConnection()->prepare($query);
        $result = $stmt->execute($params);
        
        jsonResponse(['success' => $result, 'message' => $result ? 'Categoria atualizada' : 'Erro ao atualizar']);
    }
    
    // DELETE - Deletar categoria
    if ($method === 'DELETE' && $action === 'delete') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $stmt = $db->getConnection()->prepare("DELETE FROM categories WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        jsonResponse(['success' => $result, 'message' => $result ? 'Categoria deletada' : 'Erro ao deletar']);
    }
    
    jsonResponse(['success' => false, 'message' => 'Ação não encontrada'], 404);
    
} catch (Exception $e) {
    logError($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Erro do servidor'], 500);
}
