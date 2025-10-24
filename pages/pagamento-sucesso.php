<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

$paymentId = $_GET['payment_id'] ?? null;
$externalReference = $_GET['external_reference'] ?? null;

if (!$externalReference) {
    header('Location: /');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$externalReference]);
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
    <title>Pagamento Aprovado - Wazzy Pods</title>
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
        
        .success-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        
        .success-icon i {
            font-size: 4rem;
            color: white;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .success-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .success-subtitle {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 3rem;
        }
        
        .order-info {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(16, 185, 129, 0.3);
            margin-bottom: 2rem;
        }
        
        .order-info h3 {
            color: #10b981;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            color: #94a3b8;
            font-weight: 500;
        }
        
        .info-value {
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
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
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
        
        .next-steps {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .next-steps h4 {
            color: #10b981;
            margin-bottom: 1rem;
        }
        
        .next-steps ul {
            color: #94a3b8;
            text-align: left;
            line-height: 1.8;
        }
        
        @media (max-width: 768px) {
            .success-container {
                padding: 1rem;
            }
            
            .success-title {
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
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="success-title">Pagamento Aprovado!</h1>
        <p class="success-subtitle">Seu pagamento foi processado com sucesso</p>
        
        <div class="order-info">
            <h3><i class="fas fa-credit-card" style="margin-right: 0.5rem;"></i>Detalhes do Pagamento</h3>
            
            <div class="info-row">
                <span class="info-label">Número do Pedido:</span>
                <span class="info-value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Status do Pagamento:</span>
                <span class="info-value" style="color: #10b981; font-weight: 700;">Aprovado</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Valor Pago:</span>
                <span class="info-value total-value">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data do Pagamento:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i'); ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="confirmacao.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                <i class="fas fa-receipt"></i>
                Ver Detalhes do Pedido
            </a>
            <a href="/" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Voltar ao Início
            </a>
        </div>
        
        <div class="next-steps">
            <h4><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Próximos Passos</h4>
            <ul>
                <li>Você receberá um e-mail de confirmação em breve</li>
                <li>Seu pedido será processado em até 24 horas</li>
                <li>Você será notificado sobre o envio</li>
                <li>O prazo de entrega é de 3-7 dias úteis</li>
            </ul>
        </div>
    </div>
</body>
</html>
