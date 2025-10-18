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
            // Listar categorias
            $sql = "SELECT c.*, COUNT(p.id) as total_produtos 
                    FROM categorias c 
                    LEFT JOIN produtos p ON c.id = p.categoria_id 
                    WHERE c.ativo = 1 
                    GROUP BY c.id 
                    ORDER BY c.ordem, c.nome";
            
            $stmt = $pdo->query($sql);
            $categorias = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;
            
        case 'POST':
            // Criar nova categoria
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validação básica
            if (empty($data['nome'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
                exit;
            }
            
            // Gerar slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));
            $slug = preg_replace('/-+/', '-', $slug);
            
            // Verificar slug único
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            $sql = "INSERT INTO categorias (nome, slug, descricao, icone, cor, ordem, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['nome'],
                $slug,
                $data['descricao'] ?? null,
                $data['icone'] ?? 'fas fa-box',
                $data['cor'] ?? '#8B5CF6',
                $data['ordem'] ?? 0,
                $data['ativo'] ?? 1
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar categoria']);
            }
            break;
            
        case 'PUT':
            // Atualizar categoria
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                exit;
            }
            
            // Gerar slug se o nome mudou
            if (isset($data['nome'])) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));
                $slug = preg_replace('/-+/', '-', $slug);
                
                // Verificar slug único (exceto para a própria categoria)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $data['id']]);
                if ($stmt->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }
            }
            
            $sql = "UPDATE categorias SET 
                    nome = ?, slug = ?, descricao = ?, icone = ?, cor = ?, ordem = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['nome'],
                $slug ?? $data['slug'],
                $data['descricao'] ?? null,
                $data['icone'] ?? 'fas fa-box',
                $data['cor'] ?? '#8B5CF6',
                $data['ordem'] ?? 0,
                $data['id']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar categoria']);
            }
            break;
            
        case 'PATCH':
            // Atualização parcial (ex: toggle status)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                exit;
            }
            
            if (isset($data['ativo'])) {
                $stmt = $pdo->prepare("UPDATE categorias SET ativo = ? WHERE id = ?");
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
            // Deletar categoria
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
                exit;
            }
            
            // Verificar se há produtos na categoria
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
            $stmt->execute([$data['id']]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Não é possível excluir categoria com produtos']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            $result = $stmt->execute([$data['id']]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao deletar categoria']);
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
