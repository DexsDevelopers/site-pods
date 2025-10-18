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

// Buscar pedido com detalhes
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, a.street, a.number, a.neighborhood, a.city, a.state, a.cep 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      JOIN addresses a ON o.address_id = a.id 
                      WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: /');
    exit;
}

// Buscar itens do pedido
$stmt = $pdo->prepare("SELECT oi.*, p.nome FROM order_items oi JOIN produtos p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4 min-h-screen flex items-center justify-center">
        <div class="max-w-4xl mx-auto w-full">
            <!-- Sucesso -->
            <div class="text-center mb-12 animate-slide-in">
                <div class="inline-block mb-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-600 to-green-500 flex items-center justify-center">
                        <i class="fas fa-check text-white text-4xl"></i>
                    </div>
                </div>
                <h1 class="text-5xl font-black mb-2 gradient-text">Pedido Confirmado!</h1>
                <p class="text-slate-400 text-xl">Obrigado por sua compra</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Informações do Pedido -->
                <div class="glass rounded-xl p-6 border border-purple-800/30 animate-slide-in">
                    <h2 class="text-xl font-bold mb-4 gradient-text">
                        <i class="fas fa-receipt mr-2"></i> Informações do Pedido
                    </h2>

                    <div class="space-y-3 text-slate-300">
                        <div class="flex justify-between pb-3 border-b border-purple-800/20">
                            <span>Número do Pedido:</span>
                            <span class="font-mono font-bold"><?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>

                        <div class="flex justify-between pb-3 border-b border-purple-800/20">
                            <span>Data:</span>
                            <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>

                        <div class="flex justify-between pb-3 border-b border-purple-800/20">
                            <span>Status:</span>
                            <span class="px-3 py-1 bg-yellow-900/30 text-yellow-400 rounded-full text-sm">
                                <i class="fas fa-hourglass-start mr-1"></i> Pendente
                            </span>
                        </div>

                        <div class="flex justify-between pb-3 border-b border-purple-800/20">
                            <span>Total:</span>
                            <span class="text-2xl font-bold gradient-text">R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="glass rounded-xl p-6 border border-purple-800/30 animate-slide-in" style="animation-delay: 0.1s;">
                    <h2 class="text-xl font-bold mb-4 gradient-text">
                        <i class="fas fa-map-marker-alt mr-2"></i> Endereço de Entrega
                    </h2>

                    <div class="text-slate-300 space-y-1">
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($order['street'] . ', ' . $order['number']); ?></p>
                        <p><?php echo htmlspecialchars($order['neighborhood'] . ', ' . $order['city'] . ' - ' . $order['state']); ?></p>
                        <p class="font-mono text-purple-400">CEP: <?php echo htmlspecialchars($order['cep']); ?></p>
                    </div>
                </div>

                <!-- Itens Comprados -->
                <div class="lg:col-span-2 glass rounded-xl p-6 border border-purple-800/30 animate-slide-in" style="animation-delay: 0.2s;">
                    <h2 class="text-xl font-bold mb-4 gradient-text">
                        <i class="fas fa-boxes mr-2"></i> Itens Comprados
                    </h2>

                    <div class="space-y-3">
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-slate-800/50 rounded-lg">
                                <div>
                                    <p class="font-bold"><?php echo htmlspecialchars($item['nome']); ?></p>
                                    <p class="text-sm text-slate-400">Qtd: <?php echo intval($item['quantity']); ?></p>
                                </div>
                                <p class="font-bold">R$ <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div class="lg:col-span-2 glass rounded-xl p-6 border border-purple-800/30 animate-slide-in" style="animation-delay: 0.3s;">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Resumo do Pagamento</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
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

                        <div class="flex items-center justify-end">
                            <div>
                                <p class="text-slate-400 text-sm mb-1">Valor Total</p>
                                <p class="text-3xl font-bold gradient-text">R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email de Confirmação -->
                <div class="lg:col-span-2 glass rounded-xl p-6 border border-purple-800/30 animate-slide-in" style="animation-delay: 0.4s;">
                    <div class="flex items-start gap-4">
                        <i class="fas fa-envelope text-2xl text-purple-400 mt-1"></i>
                        <div>
                            <h3 class="font-bold mb-1">Confirmação por Email</h3>
                            <p class="text-slate-400">Um email de confirmação foi enviado para <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong></p>
                            <p class="text-slate-400 text-sm mt-2">Você receberá atualizações sobre o status do seu pedido.</p>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="lg:col-span-2 flex gap-4 animate-slide-in" style="animation-delay: 0.5s;">
                    <a href="/pages/profile.php" class="flex-1 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-center hover:shadow-lg transition">
                        <i class="fas fa-user mr-2"></i> Meu Perfil
                    </a>
                    <a href="/" class="flex-1 py-3 glass border border-purple-800/30 rounded-lg font-bold text-center hover:bg-slate-800/50 transition">
                        <i class="fas fa-home mr-2"></i> Voltar à Loja
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
