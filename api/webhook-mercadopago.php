<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

// Log para debug
error_log('Webhook Mercado Pago recebido: ' . file_get_contents('php://input'));

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Buscar access token
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mercado_pago_access_token'");
    $stmt->execute();
    $accessToken = $stmt->fetchColumn();
    
    if (!$accessToken) {
        throw new Exception('Access token não configurado');
    }
    
    // Obter dados do webhook
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['type'])) {
        throw new Exception('Dados inválidos do webhook');
    }
    
    // Processar diferentes tipos de notificação
    switch ($data['type']) {
        case 'payment':
            if (isset($data['data']['id'])) {
                $paymentId = $data['data']['id'];
                processPayment($paymentId, $accessToken, $pdo);
            }
            break;
            
        case 'merchant_order':
            if (isset($data['data']['id'])) {
                $orderId = $data['data']['id'];
                processMerchantOrder($orderId, $accessToken, $pdo);
            }
            break;
            
        default:
            error_log('Tipo de notificação não processado: ' . $data['type']);
    }
    
    // Responder com sucesso
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Erro no webhook: ' . $e->getMessage());
    http_response_code(500);
    echo 'Erro: ' . $e->getMessage();
}

function processPayment($paymentId, $accessToken, $pdo) {
    try {
        // Buscar dados do pagamento
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$paymentId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Erro ao buscar dados do pagamento');
        }
        
        $payment = json_decode($response, true);
        
        if (!$payment) {
            throw new Exception('Dados do pagamento inválidos');
        }
        
        // Buscar pedido pelo external_reference
        $externalReference = $payment['external_reference'] ?? null;
        
        if (!$externalReference) {
            throw new Exception('External reference não encontrado');
        }
        
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$externalReference]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('Pedido não encontrado');
        }
        
        // Atualizar status do pedido baseado no status do pagamento
        $newStatus = 'pending';
        $paymentStatus = $payment['status'] ?? '';
        
        switch ($paymentStatus) {
            case 'approved':
                $newStatus = 'paid';
                break;
            case 'pending':
                $newStatus = 'pending';
                break;
            case 'rejected':
            case 'cancelled':
                $newStatus = 'cancelled';
                break;
            case 'refunded':
                $newStatus = 'refunded';
                break;
        }
        
        // Atualizar pedido
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, 
                mercado_pago_payment_id = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $paymentId, $externalReference]);
        
        // Log da atualização
        error_log("Pedido $externalReference atualizado para status: $newStatus");
        
        // Enviar e-mail de confirmação se aprovado
        if ($newStatus === 'paid') {
            sendConfirmationEmail($order, $pdo);
        }
        
    } catch (Exception $e) {
        error_log('Erro ao processar pagamento: ' . $e->getMessage());
    }
}

function processMerchantOrder($orderId, $accessToken, $pdo) {
    try {
        // Buscar dados da ordem
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/merchant_orders/$orderId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Erro ao buscar dados da ordem');
        }
        
        $merchantOrder = json_decode($response, true);
        
        if (!$merchantOrder) {
            throw new Exception('Dados da ordem inválidos');
        }
        
        // Processar status da ordem
        $status = $merchantOrder['status'] ?? '';
        $externalReference = $merchantOrder['external_reference'] ?? null;
        
        if ($externalReference) {
            $newStatus = 'pending';
            
            if ($status === 'closed') {
                $newStatus = 'paid';
            }
            
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $externalReference]);
            
            error_log("Ordem $orderId processada. Pedido $externalReference atualizado para: $newStatus");
        }
        
    } catch (Exception $e) {
        error_log('Erro ao processar ordem: ' . $e->getMessage());
    }
}

function sendConfirmationEmail($order, $pdo) {
    try {
        // Buscar configurações de e-mail
        $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'site_email'");
        $stmt->execute();
        $siteEmail = $stmt->fetchColumn();
        
        if (!$siteEmail) {
            error_log('E-mail do site não configurado');
            return;
        }
        
        $subject = "Pedido Confirmado - Wazzy Pods #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
        
        $message = "
        <html>
        <head>
            <title>Pedido Confirmado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #8b5cf6;'>Pedido Confirmado!</h2>
                
                <p>Olá " . htmlspecialchars($order['nome']) . ",</p>
                
                <p>Seu pedido foi confirmado e está sendo processado!</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3>Detalhes do Pedido</h3>
                    <p><strong>Número:</strong> #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT) . "</p>
                    <p><strong>Total:</strong> R$ " . number_format($order['total'], 2, ',', '.') . "</p>
                    <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
                </div>
                
                <p>Você será notificado sobre o status do envio em breve.</p>
                
                <p>Obrigado por escolher a Wazzy Pods!</p>
            </div>
        </body>
        </html>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Wazzy Pods <' . $siteEmail . '>',
            'Reply-To: ' . $siteEmail
        ];
        
        mail($order['email'], $subject, $message, implode("\r\n", $headers));
        
        error_log("E-mail de confirmação enviado para: " . $order['email']);
        
    } catch (Exception $e) {
        error_log('Erro ao enviar e-mail: ' . $e->getMessage());
    }
}
?>
