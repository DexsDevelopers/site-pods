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
    
    // GET - Listar produtos
    if ($method === 'GET' && $action === 'list') {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $query = "SELECT id, nome, descricao, preco, estoque, imagem, categoria_id, criado_em FROM products WHERE 1=1";
        $params = [];
        
        if ($category) {
            $query .= " AND categoria_id = ?";
            $params[] = $category;
        }
        
        if ($search) {
            $query .= " AND (nome LIKE ? OR descricao LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $query .= " ORDER BY criado_em DESC LIMIT 50";
        
        $stmt = $db->getConnection()->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(['success' => true, 'data' => $products]);
    }
    
    // GET - Produto por ID
    if ($method === 'GET' && $action === 'detail') {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $stmt = $db->getConnection()->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Produto não encontrado'], 404);
        }
        
        jsonResponse(['success' => true, 'data' => $product]);
    }
    
    // POST - Criar produto (Admin)
    if ($method === 'POST' && $action === 'create') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $dados = json_decode(file_get_contents('php://input'), true);
        
        $nome = $dados['nome'] ?? null;
        $descricao = $dados['descricao'] ?? null;
        $preco = $dados['preco'] ?? null;
        $estoque = $dados['estoque'] ?? null;
        $categoria_id = $dados['categoria_id'] ?? null;
        $imagem = $dados['imagem'] ?? null;
        
        if (!$nome || !$preco || !$categoria_id) {
            jsonResponse(['success' => false, 'message' => 'Campos obrigatórios faltando'], 400);
        }
        
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO products (nome, descricao, preco, estoque, categoria_id, imagem) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            $nome,
            $descricao,
            $preco,
            $estoque,
            $categoria_id,
            $imagem
        ]);
        
        if ($result) {
            $id = $db->getConnection()->lastInsertId();
            jsonResponse(['success' => true, 'message' => 'Produto criado com sucesso', 'id' => $id]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Erro ao criar produto'], 500);
        }
    }
    
    // PUT - Atualizar produto
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
        
        foreach (['nome', 'descricao', 'preco', 'estoque', 'categoria_id', 'imagem'] as $field) {
            if (isset($dados[$field])) {
                $updates[] = "$field = ?";
                $params[] = $dados[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'Nenhum campo para atualizar'], 400);
        }
        
        $params[] = $id;
        
        $query = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->getConnection()->prepare($query);
        $result = $stmt->execute($params);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Produto atualizado com sucesso' : 'Erro ao atualizar'
        ]);
    }
    
    // DELETE - Deletar produto
    if ($method === 'DELETE' && $action === 'delete') {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
        }
        
        $stmt = $db->getConnection()->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Produto deletado com sucesso' : 'Erro ao deletar'
        ]);
    }
    
    // POST - Upload de imagem
    if ($method === 'POST' && $action === 'upload') {
        if (!isset($_FILES['image'])) {
            jsonResponse(['success' => false, 'message' => 'Nenhuma imagem enviada'], 400);
        }
        
        $file = $_FILES['image'];
        $upload_dir = '../uploads/products/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            jsonResponse(['success' => true, 'image' => $filename, 'path' => '/uploads/products/' . $filename]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Erro ao fazer upload'], 500);
        }
    }
    
    jsonResponse(['success' => false, 'message' => 'Ação não encontrada'], 404);
    
} catch (Exception $e) {
    logError($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Erro do servidor'], 500);
}
