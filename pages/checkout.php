<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

// Verificar se há itens no carrinho
$cartItems = json_decode($_COOKIE['cart'] ?? '[]', true);
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Buscar configurações do Mercado Pago
try {
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mercado_pago_public_key'");
    $stmt->execute();
    $publicKey = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mercado_pago_access_token'");
    $stmt->execute();
    $accessToken = $stmt->fetchColumn();
} catch (Exception $e) {
    $publicKey = '';
    $accessToken = '';
}

// Calcular totais
$subtotal = 0;
foreach ($cartItems as $item) {
    $preco = $item['preco'] ?? $item['preco_final'] ?? 0;
    $qty = $item['qty'] ?? $item['quantity'] ?? 0;
    $subtotal += $preco * $qty;
}
$taxa = $subtotal * 0.08; // 8% de taxa
$total = $subtotal + $taxa;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wazzy Pods</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .checkout-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
        }
        
        .checkout-form {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #cbd5e1;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .order-summary {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
            height: fit-content;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #8b5cf6;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .summary-total {
            font-size: 1.5rem;
            font-weight: 800;
            color: #10b981;
        }
        
        .payment-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .payment-button {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .payment-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
        }
        
        .payment-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .checkout-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cart.php" class="back-button">
            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
            Voltar ao Carrinho
        </a>
        
        <div class="checkout-header">
            <h1 class="checkout-title">Finalizar Compra</h1>
            <p style="color: #94a3b8;">Complete seus dados para finalizar o pedido</p>
        </div>
        
        <div class="checkout-grid">
            <!-- Formulário de Checkout -->
            <div class="checkout-form">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 2rem; color: #8b5cf6;">Dados de Entrega</h2>
                
                <form id="checkoutForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telefone *</label>
                            <input type="tel" name="telefone" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">E-mail *</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">CEP *</label>
                        <input type="text" name="cep" class="form-input" required maxlength="9">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / 3;">
                            <label class="form-label">Endereço *</label>
                            <input type="text" name="endereco" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Número *</label>
                            <input type="text" name="numero" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Bairro *</label>
                            <input type="text" name="bairro" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cidade *</label>
                            <input type="text" name="cidade" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estado *</label>
                        <select name="estado" class="form-input" required>
                            <option value="">Selecione o estado</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                </form>
            </div>
            
            <!-- Resumo do Pedido -->
            <div class="order-summary">
                <h3 class="summary-title">Resumo do Pedido</h3>
                
                <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <div>
                        <p style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($item['nome']); ?></p>
                        <p style="color: #94a3b8; font-size: 0.9rem;">Qty: <?php echo $item['qty'] ?? $item['quantity']; ?></p>
                    </div>
                    <p style="font-weight: 600;">R$ <?php echo number_format(($item['preco'] ?? $item['preco_final'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 0), 2, ',', '.'); ?></p>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                </div>
                
                <div class="summary-item">
                    <span>Taxa (8%):</span>
                    <span>R$ <?php echo number_format($taxa, 2, ',', '.'); ?></span>
                </div>
                
                <div class="summary-item summary-total">
                    <span>Total:</span>
                    <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
                
                <div class="payment-section">
                    <h4 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; color: #8b5cf6;">Pagamento</h4>
                    
                    <?php if (!empty($publicKey) && !empty($accessToken)): ?>
                    <div id="mercadopago-button"></div>
                    <div id="payment-status" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px; text-align: center;"></div>
                    <?php else: ?>
                    <p style="color: #ef4444; text-align: center; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                        Configuração do Mercado Pago não encontrada. Configure as chaves no painel administrativo.
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração do Mercado Pago
        const mp = new MercadoPago('<?php echo $publicKey; ?>', {
            locale: 'pt-BR'
        });

        let orderId = null;

        // Função para criar pedido no banco
        async function createOrder() {
            console.log('📝 Iniciando criação do pedido...');
            
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);
            
            // Validar formulário
            if (!form.checkValidity()) {
                console.error('❌ Formulário inválido');
                alert('Por favor, preencha todos os campos obrigatórios.');
                return null;
            }
            
            // Coletar dados do formulário
            const orderData = {
                nome: formData.get('nome'),
                telefone: formData.get('telefone'),
                email: formData.get('email'),
                cep: formData.get('cep'),
                endereco: formData.get('endereco'),
                numero: formData.get('numero'),
                complemento: formData.get('complemento'),
                bairro: formData.get('bairro'),
                cidade: formData.get('cidade'),
                estado: formData.get('estado'),
                items: <?php echo json_encode($cartItems); ?>,
                total: <?php echo $total; ?>
            };
            
            console.log('📦 Dados do pedido:', orderData);
            
            try {
                console.log('🌐 Enviando requisição para api/pedidos.php...');
                const response = await fetch('../api/pedidos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });
                
                console.log('📡 Resposta recebida:', response.status);
                
                const result = await response.json();
                console.log('📄 Resultado:', result);
                
                if (result.success) {
                    console.log('✅ Pedido criado com sucesso:', result.order_id);
                    return result.order_id;
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('❌ Erro ao criar pedido:', error);
                alert('Erro ao criar pedido: ' + error.message);
                return null;
            }
        }

        // Função para criar preferência do Mercado Pago
        async function createPreference(orderId) {
            console.log('💳 Iniciando criação da preferência...');
            
            try {
                const preferenceData = {
                    order_id: orderId,
                    items: <?php echo json_encode($cartItems); ?>,
                    total: <?php echo $total; ?>
                };
                
                console.log('📦 Dados da preferência:', preferenceData);
                
                console.log('🌐 Enviando requisição para api/mercadopago.php...');
                const response = await fetch('../api/mercadopago.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(preferenceData)
                });
                
                console.log('📡 Resposta recebida:', response.status);
                
                const result = await response.json();
                console.log('📄 Resultado da preferência:', result);
                
                if (result.success) {
                    console.log('✅ Preferência criada com sucesso:', result.preference_id);
                    return result.preference_id;
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('❌ Erro ao criar preferência:', error);
                alert('Erro ao configurar pagamento: ' + error.message);
                return null;
            }
        }

        // Função para processar o pagamento
        async function processPayment() {
            try {
                console.log('🚀 Iniciando processamento do pagamento...');
                
                // Mostrar loading
                showPaymentStatus('Processando pedido...', 'loading');
                
                // Criar pedido no banco
                console.log('📝 Criando pedido no banco...');
                orderId = await createOrder();
                if (!orderId) {
                    console.error('❌ Falha ao criar pedido');
                    return;
                }
                console.log('✅ Pedido criado com ID:', orderId);
                
                // Criar preferência do Mercado Pago
                console.log('💳 Criando preferência do Mercado Pago...');
                const preferenceId = await createPreference(orderId);
                if (!preferenceId) {
                    console.error('❌ Falha ao criar preferência');
                    return;
                }
                console.log('✅ Preferência criada:', preferenceId);
                
                // Configurar botão do Mercado Pago
                console.log('🔘 Configurando botão do Mercado Pago...');
                mp.bricks().create("wallet", "mercadopago-button", {
                    initialization: {
                        preferenceId: preferenceId
                    },
                    callbacks: {
                        onReady: () => {
                            console.log('✅ Botão do Mercado Pago pronto');
                            showPaymentStatus('Pagamento configurado! Clique no botão para pagar.', 'success');
                        },
                        onError: (error) => {
                            console.error('❌ Erro no Mercado Pago:', error);
                            showPaymentStatus('Erro ao configurar pagamento. Tente novamente.', 'error');
                        }
                    }
                });
                
            } catch (error) {
                console.error('❌ Erro no processamento:', error);
                showPaymentStatus('Erro no processamento: ' + error.message, 'error');
            }
        }

        // Função para mostrar status do pagamento
        function showPaymentStatus(message, type) {
            const statusDiv = document.getElementById('payment-status');
            statusDiv.style.display = 'block';
            
            const colors = {
                loading: 'rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6;',
                success: 'rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981;',
                error: 'rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;'
            };
            
            statusDiv.style.cssText = `
                display: block;
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 8px;
                text-align: center;
                background: ${colors[type]};
            `;
            statusDiv.textContent = message;
        }
        
        // Máscara para CEP
        document.querySelector('input[name="cep"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });
        
        // Máscara para telefone
        document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            e.target.value = value;
        });

        // Inicializar pagamento quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('mercadopago-button')) {
                processPayment();
            }
        });
    </script>
</body>
</html>