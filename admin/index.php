<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_nome'] = 'Admin';
}

try {
    // Estatísticas reais do banco de dados
    
    // Total de produtos ativos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
    $stats['produtos'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de categorias ativas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE ativo = 1");
    $stats['categorias'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de clientes (usuários)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stats['clientes'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['pedidos'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas de hoje
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) as total 
        FROM orders 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stats['vendas_hoje'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas do mês
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) as total 
        FROM orders 
        WHERE YEAR(created_at) = YEAR(CURDATE()) 
        AND MONTH(created_at) = MONTH(CURDATE())
    ");
    $stats['vendas_mes'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas por dia da semana (últimos 7 dias)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as dia,
            COALESCE(SUM(total_amount), 0) as valor,
            DAYNAME(created_at) as dia_semana
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY created_at ASC
    ");
    $vendas_semana = $stmt->fetchAll();
    
    // Se não houver 7 dias, preencher com zeros
    $dias_semana = ['Monday' => 'Seg', 'Tuesday' => 'Ter', 'Wednesday' => 'Qua', 'Thursday' => 'Qui', 'Friday' => 'Sex', 'Saturday' => 'Sáb', 'Sunday' => 'Dom'];
    $vendas_semana_completa = [];
    for ($i = 6; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-$i days"));
        $dia_nome = date('l', strtotime($data));
        $dia_abrev = $dias_semana[$dia_nome] ?? 'N/A';
        
        $valor = 0;
        foreach ($vendas_semana as $venda) {
            if ($venda['dia'] == $data) {
                $valor = $venda['valor'];
                break;
            }
        }
        
        $vendas_semana_completa[] = [
            'dia' => $dia_abrev,
            'valor' => (float)$valor,
            'data' => $data
        ];
    }
    
    // Produtos mais vendidos (pela coluna 'vendas')
    $stmt = $pdo->query("
        SELECT nome, vendas, estoque
        FROM produtos
        WHERE ativo = 1
        ORDER BY vendas DESC
        LIMIT 4
    ");
    $produtos_populares = $stmt->fetchAll();
    
    // Se não houver produtos, usar dados mockados
    if (empty($produtos_populares)) {
        $produtos_populares = [
            ['nome' => 'Sem dados de vendas ainda', 'vendas' => 0, 'estoque' => 0],
            ['nome' => 'Cadastre produtos para ver estatísticas', 'vendas' => 0, 'estoque' => 0],
            ['nome' => 'Registre pedidos no sistema', 'vendas' => 0, 'estoque' => 0],
            ['nome' => 'Dados aparecerão aqui', 'vendas' => 0, 'estoque' => 0]
        ];
    }
    
    // Pedidos recentes
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.order_number,
            u.name as cliente_nome,
            o.total_amount,
            o.status,
            o.created_at
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 4
    ");
    $pedidos_recentes = $stmt->fetchAll();
    
    // Se não houver pedidos, usar dados mockados
    if (empty($pedidos_recentes)) {
        $pedidos_recentes = [
            ['id' => 0, 'order_number' => 'SEM PEDIDOS', 'cliente_nome' => 'Aguardando primeiro pedido', 'total_amount' => 0, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
        ];
    }
    
} catch (Exception $e) {
    // Fallback para dados mockados em caso de erro
    $stats = [
        'produtos' => 0,
        'categorias' => 0,
        'pedidos' => 0,
        'clientes' => 0,
        'vendas_mes' => 0,
        'vendas_hoje' => 0
    ];
    $vendas_semana_completa = [];
    $produtos_populares = [];
    $pedidos_recentes = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="sidebarToggle" class="md:hidden text-xl text-slate-400 hover:text-purple-400 transition">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="text-lg md:text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span class="hidden sm:inline">Wazzy Pods</span>
                </div>
                <span class="text-xs md:text-sm text-slate-400 hidden sm:inline">Admin Panel</span>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <div class="flex items-center gap-2 text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500"></div>
                    <span class="text-slate-300 hidden sm:inline"><?php echo $_SESSION['admin_nome']; ?></span>
                </div>
                <a href="logout.php" class="btn-danger text-xs md:text-sm px-2 py-1 md:px-4 md:py-2">
                    <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Sair</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex relative">
        <!-- Overlay para mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 hidden md:hidden z-30"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed md:relative w-64 h-screen md:h-auto min-h-screen bg-slate-950 border-r border-purple-800/30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
            <nav class="p-4 space-y-2">
                <a href="/admin" class="nav-item active">
                    <i class="fas fa-dashboard w-5"></i> <span>Dashboard</span>
                </a>
                <a href="produtos.php" class="nav-item">
                    <i class="fas fa-box w-5"></i> <span>Produtos</span>
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags w-5"></i> <span>Categorias</span>
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart w-5"></i> <span>Pedidos</span>
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users w-5"></i> <span>Clientes</span>
                </a>
                <a href="integracao.php" class="nav-item">
                    <i class="fas fa-plug w-5"></i> <span>Integrações</span>
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog w-5"></i> <span>Configurações</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full md:w-auto p-4 md:p-8 overflow-x-hidden">
            <div class="mb-6 md:mb-8">
                <h1 class="text-2xl md:text-3xl font-bold gradient-text mb-2">Dashboard</h1>
                <p class="text-xs md:text-sm text-slate-400">Bem-vindo ao painel administrativo Wazzy Pods</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
                <!-- Vendas Hoje -->
                <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 rounded-lg md:rounded-xl p-4 md:p-6 border border-purple-800/30">
                    <div class="flex justify-between items-start mb-3 md:mb-4">
                        <div>
                            <p class="text-xs md:text-sm text-slate-400">Vendas Hoje</p>
                            <p class="text-xl md:text-2xl font-bold mt-1">R$ <?php echo number_format($stats['vendas_hoje'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-dollar-sign text-purple-400 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 text-xs">
                            <i class="fas fa-arrow-up"></i> Tempo real
                        </span>
                    </div>
                </div>

                <!-- Pedidos -->
                <div class="bg-gradient-to-br from-blue-900/20 to-cyan-900/20 rounded-lg md:rounded-xl p-4 md:p-6 border border-blue-800/30">
                    <div class="flex justify-between items-start mb-3 md:mb-4">
                        <div>
                            <p class="text-xs md:text-sm text-slate-400">Pedidos</p>
                            <p class="text-xl md:text-2xl font-bold mt-1"><?php echo $stats['pedidos']; ?></p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shopping-cart text-blue-400 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs">
                            <i class="fas fa-history"></i> Total
                        </span>
                    </div>
                </div>

                <!-- Produtos -->
                <div class="bg-gradient-to-br from-green-900/20 to-emerald-900/20 rounded-lg md:rounded-xl p-4 md:p-6 border border-green-800/30">
                    <div class="flex justify-between items-start mb-3 md:mb-4">
                        <div>
                            <p class="text-xs md:text-sm text-slate-400">Produtos Ativos</p>
                            <p class="text-xl md:text-2xl font-bold mt-1"><?php echo $stats['produtos']; ?></p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-green-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-box text-green-400 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs">
                            <i class="fas fa-check-circle"></i> Cadastrados
                        </span>
                    </div>
                </div>

                <!-- Clientes -->
                <div class="bg-gradient-to-br from-orange-900/20 to-red-900/20 rounded-lg md:rounded-xl p-4 md:p-6 border border-orange-800/30">
                    <div class="flex justify-between items-start mb-3 md:mb-4">
                        <div>
                            <p class="text-xs md:text-sm text-slate-400">Total Clientes</p>
                            <p class="text-xl md:text-2xl font-bold mt-1"><?php echo number_format($stats['clientes'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-orange-400 text-sm md:text-base"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs">
                            <i class="fas fa-user-check"></i> Ativos
                        </span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
                <!-- Vendas da Semana -->
                <div class="bg-slate-800/50 rounded-lg md:rounded-xl p-4 md:p-6 backdrop-blur-sm border border-purple-800/30 overflow-x-auto">
                    <h2 class="text-lg md:text-xl font-bold mb-4 gradient-text">Vendas da Semana</h2>
                    <div class="flex flex-col gap-4">
                        <?php 
                        $maxValor = max(array_column($vendas_semana_completa, 'valor'));
                        if ($maxValor == 0) $maxValor = 1;
                        ?>
                        <div class="overflow-x-auto">
                            <svg width="100%" height="120" viewBox="0 0 500 120" class="border border-purple-800/20 rounded-lg min-w-full" style="min-width: 300px;">
                                <!-- Grid -->
                                <line x1="40" y1="10" x2="40" y2="100" stroke="#8b5cf6" stroke-width="2"/>
                                <line x1="40" y1="100" x2="500" y2="100" stroke="#8b5cf6" stroke-width="2"/>
                                
                                <!-- Barras -->
                    <?php
                                $totalDias = count($vendas_semana_completa);
                                $espacamento = (500 - 60) / $totalDias;
                                
                                foreach ($vendas_semana_completa as $idx => $dia):
                                    $valor = floatval($dia['valor']);
                                    $altura = ($valor / $maxValor) * 70;
                                    $x = 50 + ($idx * $espacamento);
                                    $y = 100 - $altura;
                                ?>
                                    <!-- Barra -->
                                    <rect x="<?php echo $x; ?>" y="<?php echo $y; ?>" width="<?php echo $espacamento * 0.6; ?>" height="<?php echo $altura; ?>" fill="url(#gradientBarra)" opacity="0.8" rx="4">
                                        <title><?php echo $dia['dia']; ?>: R$ <?php echo number_format($valor, 2, ',', '.'); ?></title>
                                    </rect>
                                    
                                    <!-- Label X -->
                                    <text x="<?php echo $x + ($espacamento * 0.3); ?>" y="115" text-anchor="middle" font-size="10" fill="#94a3b8" font-weight="500">
                                        <?php echo $dia['dia']; ?>
                                    </text>
                                <?php endforeach; ?>
                                
                                <!-- Gradiente -->
                                <defs>
                                    <linearGradient id="gradientBarra" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#a78bfa;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#ec4899;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        
                        <!-- Legenda com valores -->
                        <div class="grid grid-cols-3 md:grid-cols-7 gap-2 text-xs">
                            <?php foreach ($vendas_semana_completa as $dia): ?>
                                <div class="p-2 bg-slate-900/50 rounded text-center border border-purple-800/20">
                                    <p class="font-bold text-purple-400"><?php echo $dia['dia']; ?></p>
                                    <p class="text-slate-400 text-xs">R$ <?php echo number_format(floatval($dia['valor']), 2, ',', '.'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Produtos Populares -->
                <div class="bg-slate-800/50 rounded-lg md:rounded-xl p-4 md:p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-lg md:text-xl font-bold mb-4 gradient-text">Produtos Mais Vendidos</h2>
                    <div class="space-y-3 md:space-y-4 max-h-64 overflow-y-auto">
                        <?php foreach ($produtos_populares as $produto): ?>
                            <div class="flex items-center justify-between gap-2 p-2 rounded bg-slate-900/30 hover:bg-slate-900/50 transition">
                                <div class="flex items-center gap-2 md:gap-3 min-w-0">
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-box text-purple-400 text-xs md:text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs md:text-sm font-medium truncate"><?php echo htmlspecialchars($produto['nome']); ?></p>
                                        <p class="text-xs text-slate-400"><?php echo $produto['vendas']; ?> vendas</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-lg flex-shrink-0 <?php echo $produto['estoque'] < 10 ? 'bg-red-900/30 text-red-400' : 'bg-green-900/30 text-green-400'; ?>">
                                    <?php echo $produto['estoque']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Pedidos Recentes -->
            <div class="bg-slate-800/50 rounded-lg md:rounded-xl overflow-hidden backdrop-blur-sm border border-purple-800/30 mb-6 md:mb-8">
                <div class="p-4 md:p-6 border-b border-purple-800/20">
                    <h2 class="text-lg md:text-xl font-bold gradient-text">Pedidos Recentes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs md:text-sm">
                        <thead class="bg-slate-900/50 border-b border-purple-800/20">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left font-medium text-purple-400 uppercase">Pedido</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left font-medium text-purple-400 uppercase hidden sm:table-cell">Cliente</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left font-medium text-purple-400 uppercase">Valor</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left font-medium text-purple-400 uppercase hidden md:table-cell">Status</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left font-medium text-purple-400 uppercase hidden lg:table-cell">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-purple-800/20">
                            <?php foreach ($pedidos_recentes as $pedido): ?>
                                <tr class="hover:bg-slate-900/30 transition">
                                    <td class="px-3 md:px-6 py-2 md:py-4 font-medium text-xs md:text-sm"><?php echo htmlspecialchars($pedido['order_number']); ?></td>
                                    <td class="px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm hidden sm:table-cell"><?php echo htmlspecialchars($pedido['cliente_nome'] ?? 'Anônimo'); ?></td>
                                    <td class="px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm">R$ <?php echo number_format($pedido['total_amount'], 2, ',', '.'); ?></td>
                                    <td class="px-3 md:px-6 py-2 md:py-4 hidden md:table-cell">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-900/30 text-yellow-400',
                                            'confirmed' => 'bg-blue-900/30 text-blue-400',
                                            'processing' => 'bg-purple-900/30 text-purple-400',
                                            'shipped' => 'bg-cyan-900/30 text-cyan-400',
                                            'delivered' => 'bg-green-900/30 text-green-400',
                                            'cancelled' => 'bg-red-900/30 text-red-400',
                                            'refunded' => 'bg-gray-900/30 text-gray-400'
                                        ];
                                        $statusIcons = [
                                            'pending' => 'fa-clock',
                                            'confirmed' => 'fa-check',
                                            'processing' => 'fa-spinner',
                                            'shipped' => 'fa-truck',
                                            'delivered' => 'fa-check-circle',
                                            'cancelled' => 'fa-ban',
                                            'refunded' => 'fa-undo'
                                        ];
                                        $status = $pedido['status'] ?? 'pending';
                                        ?>
                                        <span class="px-2 py-1 rounded-lg text-xs <?php echo $statusColors[$status] ?? $statusColors['pending']; ?>">
                                            <i class="fas <?php echo $statusIcons[$status] ?? $statusIcons['pending']; ?> mr-1"></i>
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm text-slate-400 hidden lg:table-cell"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-6 md:mt-8">
                <a href="produtos.php" class="quick-action">
                    <i class="fas fa-plus text-xl md:text-2xl mb-1 md:mb-2"></i>
                    <span class="text-xs md:text-sm">Novo Produto</span>
                </a>
                <a href="categorias.php" class="quick-action">
                    <i class="fas fa-tags text-xl md:text-2xl mb-1 md:mb-2"></i>
                    <span class="text-xs md:text-sm">Gerenciar Categorias</span>
                </a>
                <a href="pedidos.php" class="quick-action">
                    <i class="fas fa-list text-xl md:text-2xl mb-1 md:mb-2"></i>
                    <span class="text-xs md:text-sm">Ver Pedidos</span>
                </a>
                <a href="configuracoes.php" class="quick-action">
                    <i class="fas fa-cog text-xl md:text-2xl mb-1 md:mb-2"></i>
                    <span class="text-xs md:text-sm">Configurações</span>
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
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
            text-decoration: none;
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
            border-radius: 0.5rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .quick-action {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 1rem md:1.5rem;
            border-radius: 0.75rem;
            text-align: center;
            color: #a78bfa;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .quick-action:hover {
            background: rgba(139, 92, 246, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.2);
        }
    </style>

    <script>
        // Menu mobile toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        sidebarOverlay.addEventListener('click', closeSidebar);

        // Fechar sidebar ao clicar em um link
        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    closeSidebar();
                }
            });
        });

        // Fechar sidebar ao redimensionar para desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });

        // Versão do gráfico para invalidar cache
        const chartVersion = '<?php echo md5(json_encode($vendas_semana_completa)); ?>';
        const storageKey = 'wazzy_chart_version';
        const lastVersion = sessionStorage.getItem(storageKey);
        
        if (lastVersion !== chartVersion) {
            sessionStorage.setItem(storageKey, chartVersion);
        }
    </script>
</body>
</html>

