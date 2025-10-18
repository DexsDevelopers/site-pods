<?php
session_start();

// Verificar se está logado (temporário - aceita qualquer login)
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_nome'] = 'Admin';
}

// Dados mockados para demonstração
$stats = [
    'produtos' => 127,
    'categorias' => 8,
    'pedidos' => 342,
    'clientes' => 1245,
    'vendas_mes' => 15780.90,
    'vendas_hoje' => 2340.50
];

$vendas_semana = [
    ['dia' => 'Seg', 'valor' => 1200],
    ['dia' => 'Ter', 'valor' => 1800],
    ['dia' => 'Qua', 'valor' => 1500],
    ['dia' => 'Qui', 'valor' => 2200],
    ['dia' => 'Sex', 'valor' => 2800],
    ['dia' => 'Sáb', 'valor' => 3200],
    ['dia' => 'Dom', 'valor' => 2340]
];

$produtos_populares = [
    ['nome' => 'Pod Strawberry Ice 5000', 'vendas' => 89, 'estoque' => 12],
    ['nome' => 'Pod Mango Tango 6000', 'vendas' => 67, 'estoque' => 8],
    ['nome' => 'Pod Blue Razz 8000', 'vendas' => 54, 'estoque' => 25],
    ['nome' => 'Kit Pro Max Recarregável', 'vendas' => 42, 'estoque' => 5]
];

$pedidos_recentes = [
    ['id' => '#3421', 'cliente' => 'João Silva', 'valor' => 189.90, 'status' => 'processando'],
    ['id' => '#3420', 'cliente' => 'Maria Santos', 'valor' => 267.80, 'status' => 'enviado'],
    ['id' => '#3419', 'cliente' => 'Pedro Costa', 'valor' => 145.00, 'status' => 'entregue'],
    ['id' => '#3418', 'cliente' => 'Ana Lima', 'valor' => 320.50, 'status' => 'processando']
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span>Wazzy Pods</span>
                </div>
                <span class="text-slate-400">Admin Panel</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500"></div>
                    <span class="text-slate-300"><?php echo $_SESSION['admin_nome']; ?></span>
                </div>
                <a href="logout.php" class="btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-slate-950 border-r border-purple-800/30">
            <nav class="p-4">
                <a href="/admin" class="nav-item active">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
                <a href="produtos.php" class="nav-item">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags"></i> Categorias
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Welcome -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold gradient-text mb-2">Dashboard</h1>
                <p class="text-slate-400">Bem-vindo ao painel administrativo Wazzy Pods</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Vendas Hoje -->
                <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 rounded-xl p-6 border border-purple-800/30">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-slate-400 text-sm">Vendas Hoje</p>
                            <p class="text-2xl font-bold mt-1">R$ <?php echo number_format($stats['vendas_hoje'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-purple-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-arrow-up"></i> 12%
                        </span>
                        <span class="text-slate-500 text-sm">vs ontem</span>
                    </div>
                </div>

                <!-- Pedidos -->
                <div class="bg-gradient-to-br from-blue-900/20 to-cyan-900/20 rounded-xl p-6 border border-blue-800/30">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-slate-400 text-sm">Pedidos</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['pedidos']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-arrow-up"></i> 8%
                        </span>
                        <span class="text-slate-500 text-sm">este mês</span>
                    </div>
                </div>

                <!-- Produtos -->
                <div class="bg-gradient-to-br from-green-900/20 to-emerald-900/20 rounded-xl p-6 border border-green-800/30">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-slate-400 text-sm">Produtos Ativos</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['produtos']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-600/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-green-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-400 text-sm">
                            <i class="fas fa-exclamation-triangle"></i> 5
                        </span>
                        <span class="text-slate-500 text-sm">estoque baixo</span>
                    </div>
                </div>

                <!-- Clientes -->
                <div class="bg-gradient-to-br from-orange-900/20 to-red-900/20 rounded-xl p-6 border border-orange-800/30">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-slate-400 text-sm">Total Clientes</p>
                            <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['clientes'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-600/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-orange-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-arrow-up"></i> 23
                        </span>
                        <span class="text-slate-500 text-sm">novos hoje</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid lg:grid-cols-2 gap-6 mb-8">
                <!-- Vendas da Semana -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Vendas da Semana</h2>
                    <canvas id="salesChart" height="150"></canvas>
                </div>

                <!-- Produtos Populares -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Produtos Mais Vendidos</h2>
                    <div class="space-y-4">
                        <?php foreach ($produtos_populares as $produto): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-900/30 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-purple-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium"><?php echo $produto['nome']; ?></p>
                                        <p class="text-xs text-slate-400"><?php echo $produto['vendas']; ?> vendas</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-lg <?php echo $produto['estoque'] < 10 ? 'bg-red-900/30 text-red-400' : 'bg-green-900/30 text-green-400'; ?>">
                                    <?php echo $produto['estoque']; ?> em estoque
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Pedidos Recentes -->
            <div class="bg-slate-800/50 rounded-xl overflow-hidden backdrop-blur-sm border border-purple-800/30">
                <div class="p-6 border-b border-purple-800/20">
                    <h2 class="text-xl font-bold gradient-text">Pedidos Recentes</h2>
                </div>
                <table class="w-full">
                    <thead class="bg-slate-900/50 border-b border-purple-800/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Pedido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-800/20">
                        <?php foreach ($pedidos_recentes as $pedido): ?>
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-6 py-4 text-sm font-medium"><?php echo $pedido['id']; ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo $pedido['cliente']; ?></td>
                                <td class="px-6 py-4 text-sm">R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColors = [
                                        'processando' => 'bg-yellow-900/30 text-yellow-400',
                                        'enviado' => 'bg-blue-900/30 text-blue-400',
                                        'entregue' => 'bg-green-900/30 text-green-400'
                                    ];
                                    $statusIcons = [
                                        'processando' => 'fa-clock',
                                        'enviado' => 'fa-truck',
                                        'entregue' => 'fa-check-circle'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 rounded-lg text-xs <?php echo $statusColors[$pedido['status']]; ?>">
                                        <i class="fas <?php echo $statusIcons[$pedido['status']]; ?> mr-1"></i>
                                        <?php echo ucfirst($pedido['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="text-purple-400 hover:text-purple-300 transition">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                <a href="produtos.php" class="quick-action">
                    <i class="fas fa-plus text-2xl mb-2"></i>
                    <span>Novo Produto</span>
                </a>
                <a href="categorias.php" class="quick-action">
                    <i class="fas fa-tags text-2xl mb-2"></i>
                    <span>Gerenciar Categorias</span>
                </a>
                <a href="pedidos.php" class="quick-action">
                    <i class="fas fa-list text-2xl mb-2"></i>
                    <span>Ver Pedidos</span>
                </a>
                <a href="configuracoes.php" class="quick-action">
                    <i class="fas fa-cog text-2xl mb-2"></i>
                    <span>Configurações</span>
                </a>
            </div>
        </main>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #a78bfa;
        }
        
        .nav-item.active {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
            border-left: 3px solid #a78bfa;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .quick-action {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 1.5rem;
            border-radius: 0.75rem;
            text-align: center;
            color: #a78bfa;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .quick-action:hover {
            background: rgba(139, 92, 246, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.2);
        }
    </style>

    <script>
        // Gráfico de Vendas
        const ctx = document.getElementById('salesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(167, 139, 250, 0.3)');
        gradient.addColorStop(1, 'rgba(167, 139, 250, 0.01)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($vendas_semana, 'dia')); ?>,
                datasets: [{
                    label: 'Vendas (R$)',
                    data: <?php echo json_encode(array_column($vendas_semana, 'valor')); ?>,
                    borderColor: '#a78bfa',
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#a78bfa',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(value) {
                                return 'R$ ' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
            </div>
        </div>

    </div>

</div>

</body>
</html>

