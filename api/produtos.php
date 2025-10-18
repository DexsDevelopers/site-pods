<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar autenticação para operações de escrita
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit;
    }
}

// Processar requisição
try {
    switch ($method) {
        case 'GET':
            // Listar produtos ou buscar por ID
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    SELECT p.*, c.nome as categoria_nome 
                    FROM produtos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.id = ? AND p.ativo = 1
                ");
                $stmt->execute([$_GET['id']]);
                $produto = $stmt->fetch();
                
                if ($produto) {
                    // Decodificar JSON fields
                    $produto['caracteristicas'] = json_decode($produto['caracteristicas'] ?? '{}', true);
                    $produto['galeria'] = json_decode($produto['galeria'] ?? '[]', true);
                    echo json_encode(['success' => true, 'data' => $produto]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
                }
            } else {
                // Listar produtos com filtros
                $where = ['p.ativo = 1'];
                $params = [];
                
                if (isset($_GET['categoria'])) {
                    $where[] = 'p.categoria_id = ?';
                    $params[] = $_GET['categoria'];
                }
                
                if (isset($_GET['destaque'])) {
                    $where[] = 'p.destaque = 1';
                }
                
                if (isset($_GET['search'])) {
                    $where[] = '(p.nome LIKE ? OR p.descricao LIKE ?)';
                    $search = "%{$_GET['search']}%";
                    $params[] = $search;
                    $params[] = $search;
                }
                
                $whereClause = implode(' AND ', $where);
                $limit = intval($_GET['limit'] ?? 12);
                $offset = intval($_GET['offset'] ?? 0);
                
                $sql = "
                    SELECT p.*, c.nome as categoria_nome 
                    FROM produtos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE $whereClause 
                    ORDER BY p.destaque DESC, p.created_at DESC 
                    LIMIT $limit OFFSET $offset
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $produtos = $stmt->fetchAll();
                
                // Decodificar JSON fields
                foreach ($produtos as &$produto) {
                    $produto['caracteristicas'] = json_decode($produto['caracteristicas'] ?? '{}', true);
                    $produto['galeria'] = json_decode($produto['galeria'] ?? '[]', true);
                }
                
                echo json_encode(['success' => true, 'data' => $produtos]);
            }
            break;
            
        case 'POST':
            // Criar novo produto
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validação básica
            if (empty($data['nome']) || empty($data['preco'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nome e preço são obrigatórios']);
                exit;
            }
            
            // Gerar slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));
            $slug = preg_replace('/-+/', '-', $slug);
            
            // Verificar slug único
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            // Preparar dados
            $caracteristicas = isset($data['caracteristicas']) ? json_encode($data['caracteristicas']) : null;
            
            $sql = "INSERT INTO produtos (
                categoria_id, nome, slug, descricao, descricao_curta, 
                preco, preco_promocional, imagem, estoque, sku, 
                destaque, ativo, caracteristicas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['categoria_id'] ?? null,
                $data['nome'],
                $slug,
                $data['descricao'] ?? null,
                $data['descricao_curta'] ?? null,
                $data['preco'],
                $data['preco_promocional'] ?? null,
                $data['imagem'] ?? null,
                $data['estoque'] ?? 0,
                $data['sku'] ?? null,
                $data['destaque'] ?? 0,
                $data['ativo'] ?? 1,
                $caracteristicas
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar produto']);
            }
            break;
            
        case 'PUT':
            // Atualizar produto
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
                exit;
            }
            
            // Gerar slug se o nome mudou
            if (isset($data['nome'])) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));
                $slug = preg_replace('/-+/', '-', $slug);
                
                // Verificar slug único (exceto para o próprio produto)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $data['id']]);
                if ($stmt->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }
            }
            
            // Preparar dados
            $caracteristicas = isset($data['caracteristicas']) ? json_encode($data['caracteristicas']) : null;
            
            $sql = "UPDATE produtos SET 
                categoria_id = ?, nome = ?, slug = ?, descricao = ?, 
                descricao_curta = ?, preco = ?, preco_promocional = ?, 
                imagem = ?, estoque = ?, sku = ?, destaque = ?, 
                ativo = ?, caracteristicas = ?
                WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['categoria_id'] ?? null,
                $data['nome'],
                $slug ?? $data['slug'],
                $data['descricao'] ?? null,
                $data['descricao_curta'] ?? null,
                $data['preco'],
                $data['preco_promocional'] ?? null,
                $data['imagem'] ?? null,
                $data['estoque'] ?? 0,
                $data['sku'] ?? null,
                $data['destaque'] ?? 0,
                $data['ativo'] ?? 1,
                $caracteristicas,
                $data['id']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar produto']);
            }
            break;
            
        case 'PATCH':
            // Atualização parcial (ex: toggle status)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
                exit;
            }
            
            if (isset($data['ativo'])) {
                $stmt = $pdo->prepare("UPDATE produtos SET ativo = ? WHERE id = ?");
                $result = $stmt->execute([$data['ativo'] ? 1 : 0, $data['id']]);
                
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
                }
            }
            break;
            
        case 'DELETE':
            // Deletar produto
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $result = $stmt->execute([$data['id']]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao deletar produto']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
