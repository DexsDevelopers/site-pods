<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header('Location: /');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               GROUP_CONCAT(
                   CONCAT(oi.product_name, ' (', oi.quantity, 'x)')
                   SEPARATOR ', '
               ) as items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ?
        GROUP BY o.id
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: /');
        exit;
    }
} catch (Exception $e) {
    header('Location: /');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Wazzy Pods</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: white;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .confirmation-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .confirmation-subtitle {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 3rem;
        }
        
        .order-details {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .order-details h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #8b5cf6;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            color: #94a3b8;
            font-weight: 500;
        }
        
        .detail-value {
            color: #cbd5e1;
            font-weight: 600;
        }
        
        .total-value {
            color: #10b981;
            font-weight: 800;
            font-size: 1.2rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        .status-confirmed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        @media (max-width: 768px) {
            .confirmation-container {
                padding: 1rem;
            }
            
            .confirmation-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="confirmation-title">Pedido Confirmado!</h1>
        <p class="confirmation-subtitle">Seu pedido foi recebido e está sendo processado</p>
        
        <div class="status-badge status-pending">
            <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
            Status: <?php echo ucfirst($order['status']); ?>
        </div>
        
        <div class="order-details">
            <h3>Detalhes do Pedido</h3>
            
            <div class="detail-row">
                <span class="detail-label">Número do Pedido:</span>
                <span class="detail-value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Cliente:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['nome']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">E-mail:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Telefone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['telefone']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Endereço:</span>
                <span class="detail-value">
                    <?php echo htmlspecialchars($order['endereco']); ?>, 
                    <?php echo htmlspecialchars($order['numero']); ?>
                    <?php if (!empty($order['complemento'])): ?>
                        - <?php echo htmlspecialchars($order['complemento']); ?>
                    <?php endif; ?>
                    <br>
                    <?php echo htmlspecialchars($order['bairro']); ?> - 
                    <?php echo htmlspecialchars($order['cidade']); ?>/<?php echo htmlspecialchars($order['estado']); ?>
                    <br>
                    CEP: <?php echo htmlspecialchars($order['cep']); ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Itens:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['items_summary']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total:</span>
                <span class="detail-value total-value">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Data do Pedido:</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Voltar ao Início
            </a>
            <a href="produtos.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i>
                Continuar Comprando
            </a>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(59, 130, 246, 0.1); border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.3);">
            <h4 style="color: #3b82f6; margin-bottom: 1rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                Próximos Passos
            </h4>
            <ul style="color: #94a3b8; text-align: left; line-height: 1.8;">
                <li>Você receberá um e-mail de confirmação em breve</li>
                <li>Nosso time irá processar seu pedido em até 24 horas</li>
                <li>Você será notificado sobre o status do envio</li>
                <li>O prazo de entrega é de 3-7 dias úteis</li>
            </ul>
        </div>
    </div>
</body>
</html>