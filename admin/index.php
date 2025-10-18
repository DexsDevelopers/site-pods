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
                <a href="integracao.php" class="nav-item">
                    <i class="fas fa-plug"></i> Integrações
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i> Configurações
            </a>
        </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
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
                            <i class="fas fa-arrow-up"></i> Tempo real
                        </span>
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
                        <span class="text-slate-400 text-sm">
                            <i class="fas fa-history"></i> Total
                        </span>
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
                        <span class="text-slate-400 text-sm">
                            <i class="fas fa-check-circle"></i> Cadastrados
                        </span>
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
                        <span class="text-slate-400 text-sm">
                            <i class="fas fa-user-check"></i> Ativos
                        </span>
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
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($produto['nome']); ?></p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-400 uppercase">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-800/20">
                        <?php foreach ($pedidos_recentes as $pedido): ?>
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-6 py-4 text-sm font-medium"><?php echo htmlspecialchars($pedido['order_number']); ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($pedido['cliente_nome'] ?? 'Anônimo'); ?></td>
                                <td class="px-6 py-4 text-sm">R$ <?php echo number_format($pedido['total_amount'], 2, ',', '.'); ?></td>
                                <td class="px-6 py-4">
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
                                <td class="px-6 py-4 text-sm text-slate-400"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
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
        // Gráfico de Vendas com dados reais
        const ctx = document.getElementById('salesChart');
        if (!ctx) {
            console.error('Canvas salesChart não encontrado');
        } else {
            const canvasCtx = ctx.getContext('2d');
            const gradient = canvasCtx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(167, 139, 250, 0.3)');
            gradient.addColorStop(1, 'rgba(167, 139, 250, 0.01)');
            
            // Dados do servidor (PHP)
            let vendas = <?php echo json_encode(array_column($vendas_semana_completa, 'valor')); ?>;
            let labels = <?php echo json_encode(array_column($vendas_semana_completa, 'dia')); ?>;
            
            // Validação de dados
            if (!vendas || vendas.length === 0) {
                console.warn('Nenhum dado de vendas disponível');
                vendas = [0, 0, 0, 0, 0, 0, 0];
            }
            
            // Garantir que vendas é um array de números
            vendas = vendas.map(v => {
                const num = parseFloat(v);
                return isNaN(num) ? 0 : num;
            });
            
            // Calcular escala de forma segura
            const valoresValidos = vendas.filter(v => !isNaN(v) && v >= 0);
            const maxVenda = valoresValidos.length > 0 ? Math.max(...valoresValidos) : 1;
            const escala = Math.max(Math.ceil(maxVenda * 1.2), 10); // Mínimo de 10
            
            console.log('Debug - Vendas:', vendas);
            console.log('Debug - Max Venda:', maxVenda);
            console.log('Debug - Escala:', escala);
            
            // Destruir gráfico anterior se existir
            if (window.chartVendas) {
                window.chartVendas.destroy();
            }
            
            // Criar novo gráfico
            window.chartVendas = new Chart(canvasCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Vendas (R$)',
                        data: vendas,
                        borderColor: '#a78bfa',
                        backgroundColor: gradient,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#a78bfa',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        intersect: false
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                                drawBorder: true
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 12,
                                    weight: 500
                                }
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            min: 0,
                            max: escala,
                            ticks: {
                                stepSize: Math.ceil(escala / 5),
                                color: '#94a3b8',
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                                drawBorder: true
                            }
                        }
                    }
                }
            });
        }
    </script>
    
    <!-- Limpar cache e forçar recarregamento se necessário -->
    <script>
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

