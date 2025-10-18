<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Buscar pedidos do usuário
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $pedidos = $stmt->fetchAll();

    // Buscar endereços
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
    $stmt->execute([$user_id]);
    $addresses = $stmt->fetchAll();

} catch (Exception $e) {
    $user = null;
    $pedidos = [];
    $addresses = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Header Perfil -->
            <div class="mb-12 glass rounded-xl p-8 border border-purple-800/30">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center text-3xl">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($user['name'] ?? 'Cliente'); ?></h1>
                            <p class="text-slate-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-sm text-slate-500 mt-1">Membro desde <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="?edit=profile" class="px-6 py-2 bg-purple-600/20 border border-purple-600/50 rounded-lg text-purple-400 hover:bg-purple-600/30 transition">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </a>
                        <a href="logout.php" class="px-6 py-2 bg-red-600/20 border border-red-600/50 rounded-lg text-red-400 hover:bg-red-600/30 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i> Sair
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Coluna Principal -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Pedidos Recentes -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h2 class="text-2xl font-bold gradient-text mb-6">Meus Pedidos</h2>
                        
                        <?php if (empty($pedidos)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-shopping-bag text-4xl text-slate-600 mb-2"></i>
                                <p class="text-slate-400">Você ainda não fez nenhum pedido</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($pedidos as $pedido): ?>
                                    <div class="border border-purple-800/30 rounded-lg p-4 hover:bg-slate-800/30 transition cursor-pointer">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <p class="font-bold"><?php echo htmlspecialchars($pedido['order_number']); ?></p>
                                                <p class="text-sm text-slate-400"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></p>
                                            </div>
                                            <span class="px-3 py-1 text-xs rounded-lg <?php 
                                                $statusClass = match($pedido['status']) {
                                                    'pending' => 'bg-yellow-900/30 text-yellow-400',
                                                    'confirmed' => 'bg-blue-900/30 text-blue-400',
                                                    'shipped' => 'bg-cyan-900/30 text-cyan-400',
                                                    'delivered' => 'bg-green-900/30 text-green-400',
                                                    default => 'bg-slate-700/30 text-slate-400'
                                                };
                                                echo $statusClass;
                                            ?>">
                                                <?php echo ucfirst($pedido['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-slate-300 font-bold">R$ <?php echo number_format($pedido['total_amount'], 2, ',', '.'); ?></p>
                                        <p class="text-xs text-slate-500 mt-1"><?php echo $pedido['total_items']; ?> item(ns)</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Endereços -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold gradient-text">Meus Endereços</h2>
                            <button class="px-4 py-2 bg-purple-600 rounded-lg text-white hover:bg-purple-700 transition">
                                <i class="fas fa-plus mr-2"></i> Novo Endereço
                            </button>
                        </div>

                        <?php if (empty($addresses)): ?>
                            <p class="text-slate-400 text-center py-6">Nenhum endereço cadastrado</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="border border-purple-800/30 rounded-lg p-4 <?php echo $addr['is_default'] ? 'bg-purple-900/20' : ''; ?>">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <p class="font-bold"><?php echo ucfirst($addr['type']); ?></p>
                                                <p class="text-sm text-slate-300">
                                                    <?php echo htmlspecialchars($addr['street']); ?>, <?php echo htmlspecialchars($addr['number']); ?>
                                                    <br>
                                                    <?php echo htmlspecialchars($addr['neighborhood']); ?> - <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?>
                                                </p>
                                            </div>
                                            <?php if ($addr['is_default']): ?>
                                                <span class="px-2 py-1 bg-green-900/30 text-green-400 text-xs rounded">Padrão</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Resumo da Conta -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h3 class="text-lg font-bold gradient-text mb-4">Resumo da Conta</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between pb-2 border-b border-slate-700">
                                <span class="text-slate-400">Total Gasto:</span>
                                <span class="font-bold">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b border-slate-700">
                                <span class="text-slate-400">Total Pedidos:</span>
                                <span class="font-bold"><?php echo count($pedidos); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Status:</span>
                                <span class="font-bold text-green-400">Ativo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dados Pessoais -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h3 class="text-lg font-bold gradient-text mb-4">Dados Pessoais</h3>
                        <div class="space-y-2 text-sm">
                            <div>
                                <p class="text-slate-400">Nome</p>
                                <p class="font-bold"><?php echo htmlspecialchars($user['name']); ?></p>
                            </div>
                            <div>
                                <p class="text-slate-400">Email</p>
                                <p class="font-bold"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <p class="text-slate-400">Telefone</p>
                                <p class="font-bold"><?php echo htmlspecialchars($user['phone'] ?? 'Não informado'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Atalhos -->
                    <div class="glass rounded-xl p-6 border border-purple-800/30">
                        <h3 class="text-lg font-bold gradient-text mb-4">Atalhos</h3>
                        <div class="space-y-2">
                            <a href="/pages/cart.php" class="block px-4 py-2 bg-slate-800/50 rounded-lg text-slate-300 hover:text-purple-300 transition text-sm">
                                <i class="fas fa-shopping-cart mr-2"></i> Meu Carrinho
                            </a>
                            <a href="/" class="block px-4 py-2 bg-slate-800/50 rounded-lg text-slate-300 hover:text-purple-300 transition text-sm">
                                <i class="fas fa-heart mr-2"></i> Meus Favoritos
                            </a>
                            <a href="#" class="block px-4 py-2 bg-slate-800/50 rounded-lg text-slate-300 hover:text-purple-300 transition text-sm">
                                <i class="fas fa-cog mr-2"></i> Configurações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <style>
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</body>
</html>
