<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se cliente está logado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /pages/login-cliente.php?redirect=checkout');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// Buscar carrinho da sessão/localStorage
$carrinho = json_decode($_POST['carrinho'] ?? '[]', true);
if (empty($carrinho)) {
    header('Location: /pages/cart.php');
    exit;
}

// Buscar endereços do cliente
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$user_id]);
$enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($enderecos)) {
    $error = 'Você precisa cadastrar um endereço de entrega antes de continuar!';
}

// Buscar configurações de pagamento
$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave IN ('mercado_pago_public_key', 'pagamento_ativo', 'shipping_fee', 'tax_rate')");
$stmt->execute();
$configs = array_column($stmt->fetchAll(PDO::FETCH_KEY_PAIR), 1, 0);

$pagamento_ativo = ($configs['pagamento_ativo'] ?? 'false') === 'true';
$taxa_envio = floatval($configs['shipping_fee'] ?? 10);
$taxa_imposto = floatval($configs['tax_rate'] ?? 15);

// Calcular totais
$subtotal = 0;
foreach ($carrinho as $item) {
    $subtotal += floatval($item['preco_final']) * intval($item['quantity']);
}

$imposto = ($subtotal * $taxa_imposto) / 100;
$total = $subtotal + $imposto + $taxa_envio;

// Processar checkout (criar pedido)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalizar') {
    $endereco_id = $_POST['endereco_id'] ?? 0;
    
    if (empty($endereco_id)) {
        $error = 'Selecione um endereço de entrega!';
    } else {
        try {
            // Criar pedido
            $order_number = 'ORD-' . date('YmdHis') . '-' . random_int(1000, 9999);
            
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, address_id, subtotal, shipping, tax, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$order_number, $user_id, $endereco_id, $subtotal, $taxa_envio, $imposto, $total]);
            
            $order_id = $pdo->lastInsertId();
            
            // Adicionar itens do pedido
            foreach ($carrinho as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['preco_final']]);
            }
            
            // Redirecionar para pagamento
            $_SESSION['order_id'] = $order_id;
            header('Location: /pages/pagamento.php?order_id=' . $order_id);
            exit;
        } catch (Exception $e) {
            $error = 'Erro ao criar pedido: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wazzy Pods</title>
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
    </style>
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-5xl font-black mb-2"><span class="gradient-text">Checkout</span></h1>
            <p class="text-slate-400 mb-8">Finalize sua compra</p>

            <!-- Erro -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-900/20 border border-red-600 rounded-xl text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Formulário Checkout -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Seleção de Endereço -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h2 class="text-2xl font-bold mb-4 gradient-text">
                            <i class="fas fa-map-marker-alt mr-2"></i> Endereço de Entrega
                        </h2>

                        <form method="POST" id="checkoutForm" class="space-y-4">
                            <input type="hidden" name="action" value="finalizar">
                            <input type="hidden" name="carrinho" value="<?php echo htmlspecialchars(json_encode($carrinho)); ?>">

                            <?php if (empty($enderecos)): ?>
                                <div class="p-4 bg-yellow-900/20 border border-yellow-600 rounded-lg text-yellow-400">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <a href="/pages/enderecoscliente.php" class="underline hover:no-underline">
                                        Clique aqui para cadastrar um endereço
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach ($enderecos as $endereco): ?>
                                        <label class="flex items-start gap-3 p-4 border border-purple-800/30 rounded-lg cursor-pointer hover:bg-slate-800/50 transition" style="border-color: <?php echo $endereco['is_default'] ? '#a78bfa' : '#475569'; ?>;">
                                            <input type="radio" name="endereco_id" value="<?php echo $endereco['id']; ?>" 
                                                   <?php echo $endereco['is_default'] ? 'checked' : ''; ?> 
                                                   class="mt-1" required>
                                            <div class="flex-1">
                                                <p class="font-bold text-lg">
                                                    <?php echo htmlspecialchars($endereco['street'] . ', ' . $endereco['number']); ?>
                                                    <?php if ($endereco['is_default']): ?>
                                                        <span class="text-xs bg-purple-900/30 text-purple-400 px-2 py-1 rounded-full ml-2">⭐</span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-slate-400"><?php echo htmlspecialchars($endereco['neighborhood'] . ', ' . $endereco['city'] . ' - ' . $endereco['state']); ?></p>
                                                <p class="text-slate-500 text-sm">CEP: <?php echo htmlspecialchars($endereco['cep']); ?></p>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <a href="/pages/enderecoscliente.php" class="inline-block text-purple-400 hover:text-purple-300 text-sm">
                                    <i class="fas fa-plus mr-1"></i> Adicionar outro endereço
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Revisão de Produtos -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h2 class="text-2xl font-bold mb-4 gradient-text">
                            <i class="fas fa-boxes mr-2"></i> Itens do Pedido
                        </h2>

                        <div class="space-y-3">
                            <?php foreach ($carrinho as $item): ?>
                                <div class="flex justify-between items-center p-3 bg-slate-800/50 rounded-lg">
                                    <div>
                                        <p class="font-bold"><?php echo htmlspecialchars($item['nome']); ?></p>
                                        <p class="text-sm text-slate-400">Qtd: <?php echo intval($item['quantity']); ?></p>
                                    </div>
                                    <p class="font-bold">R$ <?php echo number_format(floatval($item['preco_final']) * intval($item['quantity']), 2, ',', '.'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Resumo de Pagamento -->
                <div>
                    <div class="glass rounded-xl p-6 border border-purple-800/30 sticky top-32 space-y-4">
                        <h2 class="text-xl font-bold gradient-text">Resumo do Pedido</h2>

                        <div class="space-y-2 border-t border-purple-800/20 pt-4">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Frete:</span>
                                <span>R$ <?php echo number_format($taxa_envio, 2, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Impostos:</span>
                                <span>R$ <?php echo number_format($imposto, 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div class="border-t border-purple-800/20 pt-4">
                            <div class="flex justify-between text-xl font-bold">
                                <span>Total:</span>
                                <span class="gradient-text">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($enderecos)): ?>
                            <button type="submit" form="checkoutForm" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                                <i class="fas fa-credit-card mr-2"></i> Ir para Pagamento
                            </button>
                        <?php else: ?>
                            <button disabled class="w-full py-3 bg-slate-700 rounded-lg font-bold cursor-not-allowed opacity-50">
                                Cadastre um endereço primeiro
                            </button>
                        <?php endif; ?>

                        <a href="/pages/cart.php" class="block text-center py-2 text-slate-400 hover:text-slate-300 transition">
                            ← Voltar ao Carrinho
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
