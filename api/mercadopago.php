<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Dados inválidos');
    }
    
    // Buscar access token do Mercado Pago
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mercado_pago_access_token'");
    $stmt->execute();
    $accessToken = $stmt->fetchColumn();
    
    if (!$accessToken) {
        throw new Exception('Access token do Mercado Pago não configurado');
    }
    
    // Buscar dados do pedido
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$data['order_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Pedido não encontrado');
    }
    
    // Preparar itens para o Mercado Pago
    $items = [];
    foreach ($data['items'] as $item) {
        $items[] = [
            'title' => $item['nome'],
            'quantity' => $item['qty'] ?? $item['quantity'] ?? 1,
            'unit_price' => floatval($item['preco'] ?? $item['preco_final'] ?? 0),
            'currency_id' => 'BRL'
        ];
    }
    
    // Dados da preferência
    $preferenceData = [
        'items' => $items,
        'payer' => [
            'name' => $order['nome'],
            'email' => $order['email'],
            'phone' => [
                'number' => preg_replace('/\D/', '', $order['telefone'])
            ],
            'address' => [
                'street_name' => $order['endereco'],
                'street_number' => intval($order['numero']),
                'zip_code' => preg_replace('/\D/', '', $order['cep'])
            ]
        ],
        'back_urls' => [
            'success' => 'https://' . $_SERVER['HTTP_HOST'] . '/pages/pagamento-sucesso.php',
            'failure' => 'https://' . $_SERVER['HTTP_HOST'] . '/pages/pagamento-falha.php',
            'pending' => 'https://' . $_SERVER['HTTP_HOST'] . '/pages/pagamento-pendente.php'
        ],
        'auto_return' => 'approved',
        'external_reference' => $order['id'],
        'notification_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/webhook-mercadopago.php',
        'statement_descriptor' => 'WAZZY PODS'
    ];
    
    // Fazer requisição para o Mercado Pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Erro na API do Mercado Pago: ' . $response);
    }
    
    $preference = json_decode($response, true);
    
    if (!$preference || !isset($preference['id'])) {
        throw new Exception('Resposta inválida do Mercado Pago');
    }
    
    // Log da resposta para debug
    error_log('Mercado Pago Response: ' . $response);
    
    // Salvar ID da preferência no pedido
    $stmt = $pdo->prepare("UPDATE orders SET mercado_pago_preference_id = ? WHERE id = ?");
    $stmt->execute([$preference['id'], $order['id']]);
    
    echo json_encode([
        'success' => true,
        'preference_id' => $preference['id'],
        'init_point' => $preference['init_point'],
        'message' => 'Preferência criada com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
