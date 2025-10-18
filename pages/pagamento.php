<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se ordem existe
$order_id = $_GET['order_id'] ?? 0;
if (empty($order_id)) {
    header('Location: /');
    exit;
}

// Buscar pedido
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: /');
    exit;
}

// Buscar endereço
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ?");
$stmt->execute([$order['address_id']]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar configurações de pagamento
$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave IN ('mercado_pago_public_key', 'site_name')");
$stmt->execute();
$configs = array_column($stmt->fetchAll(PDO::FETCH_KEY_PAIR), 1, 0);

$mp_public_key = $configs['mercado_pago_public_key'] ?? '';
$site_name = $configs['site_name'] ?? 'Wazzy Pods';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl font-black mb-2"><span class="gradient-text">Pagamento</span></h1>
            <p class="text-slate-400 mb-8">Pedido: <?php echo htmlspecialchars($order['order_number']); ?></p>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Informações do Pedido -->
                <div class="space-y-6">
                    <!-- Endereço de Entrega -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h2 class="text-xl font-bold mb-4 gradient-text">
                            <i class="fas fa-map-marker-alt mr-2"></i> Endereço de Entrega
                        </h2>
                        <div class="text-slate-300 space-y-1">
                            <p class="font-bold text-lg"><?php echo htmlspecialchars($address['street'] . ', ' . $address['number']); ?></p>
                            <?php if ($address['complement']): ?>
                                <p>Complemento: <?php echo htmlspecialchars($address['complement']); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($address['neighborhood'] . ', ' . $address['city'] . ' - ' . $address['state']); ?></p>
                            <p class="font-mono text-purple-400">CEP: <?php echo htmlspecialchars($address['cep']); ?></p>
                        </div>
                    </div>

                    <!-- Resumo Financeiro -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h2 class="text-xl font-bold mb-4 gradient-text">Resumo do Pagamento</h2>
                        
                        <div class="space-y-2 border-b border-purple-800/20 pb-4">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>R$ <?php echo number_format($order['subtotal'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Frete:</span>
                                <span>R$ <?php echo number_format($order['shipping'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Impostos:</span>
                                <span>R$ <?php echo number_format($order['tax'], 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div class="flex justify-between text-2xl font-bold pt-4 gradient-text">
                            <span>Total:</span>
                            <span>R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Pagamento -->
                <div class="glass rounded-xl p-6 border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">
                        <i class="fas fa-credit-card mr-2"></i> Dados do Cartão
                    </h2>

                    <?php if ($mp_public_key): ?>
                        <form id="form-checkout" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Número do Cartão</label>
                                <input type="text" id="form-checkout_cardNumber" placeholder="Número do cartão"
                                       class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Validade</label>
                                    <input type="text" id="form-checkout_cardExpirationMonth" placeholder="MM"
                                           class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Ano</label>
                                    <input type="text" id="form-checkout_cardExpirationYear" placeholder="YY"
                                           class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">CVV</label>
                                <input type="text" id="form-checkout_cardCvv" placeholder="CVV"
                                       class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Titular</label>
                                <input type="text" id="form-checkout_cardholderName" placeholder="Nome do titular"
                                       class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Email</label>
                                <input type="email" id="form-checkout_cardholderEmail" placeholder="seu@email.com"
                                       class="w-full px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg focus:outline-none focus:border-purple-600">
                            </div>

                            <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                                <i class="fas fa-lock mr-2"></i> Pagar R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?>
                            </button>

                            <p class="text-xs text-slate-400 text-center">
                                <i class="fas fa-shield-alt mr-1"></i> Seu pagamento é seguro e criptografado
                            </p>
                        </form>

                        <script>
                            // Inicializar Mercado Pago
                            const mp = new MercadoPago('<?php echo htmlspecialchars($mp_public_key); ?>', {
                                locale: 'pt-BR'
                            });

                            document.getElementById('form-checkout').addEventListener('submit', async (e) => {
                                e.preventDefault();
                                
                                // Aqui você faria a integração real com Mercado Pago
                                // Por enquanto, apenas simulamos o pagamento
                                
                                alert('Pagamento processado! Sua compra foi confirmada.\n\nPedido: <?php echo htmlspecialchars($order['order_number']); ?>');
                                window.location.href = '/pages/confirmacao.php?order_id=<?php echo $order_id; ?>';
                            });
                        </script>
                    <?php else: ?>
                        <div class="p-4 bg-yellow-900/20 border border-yellow-600 rounded-lg text-yellow-400">
                            <i class="fas fa-info-circle mr-2"></i>
                            Pagamento ainda não configurado. Contate o administrador.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
