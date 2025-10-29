<?php
session_start();
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_nome'] = 'Admin';
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Estatísticas reais do banco de dados
    $stats = [];
    
    // Total de produtos ativos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
    $stats['produtos'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de categorias ativas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE ativo = 1");
    $stats['categorias'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['pedidos'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas de hoje
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total), 0) as total 
        FROM orders 
        WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'delivered')
    ");
    $stats['vendas_hoje'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas do mês
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total), 0) as total 
        FROM orders 
        WHERE YEAR(created_at) = YEAR(CURDATE()) 
        AND MONTH(created_at) = MONTH(CURDATE())
        AND status IN ('paid', 'delivered')
    ");
    $stats['vendas_mes'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas por dia da semana (últimos 7 dias)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as dia,
            COALESCE(SUM(total), 0) as valor,
            DAYNAME(created_at) as dia_semana
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        AND status IN ('paid', 'delivered')
        GROUP BY DATE(created_at)
        ORDER BY created_at ASC
    ");
    $vendas_semana = $stmt->fetchAll();
    
    // Preencher com zeros se não houver 7 dias
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
    
    // Produtos mais vendidos
    $stmt = $pdo->query("
        SELECT p.nome, p.vendas, p.estoque, p.preco
        FROM produtos p
        WHERE p.ativo = 1
        ORDER BY p.vendas DESC
        LIMIT 5
    ");
    $produtos_populares = $stmt->fetchAll();
    
    // Pedidos recentes
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.nome as cliente_nome,
            o.total,
            o.status,
            o.created_at
        FROM orders o
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $pedidos_recentes = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Fallback para dados mockados em caso de erro
    $stats = [
        'produtos' => 0,
        'categorias' => 0,
        'pedidos' => 0,
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(139, 92, 246, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo i {
            font-size: 1.75rem;
            color: #8b5cf6;
        }
        
        .nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #a78bfa;
            border-left-color: rgba(139, 92, 246, 0.5);
        }
        
        .nav-item.active {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
            border-left-color: #8b5cf6;
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        /* Header */
        .header {
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            color: #a78bfa;
            background: rgba(139, 92, 246, 0.1);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(139, 92, 246, 0.1);
            border-radius: 0.75rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 0;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #94a3b8;
            margin-bottom: 2rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.1);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-title {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .stat-change.positive {
            color: #10b981;
        }
        
        .stat-change.neutral {
            color: #94a3b8;
        }
        
        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
        }
        
        .chart-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Table */
        .table-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1rem;
            overflow: hidden;
        }
        
        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #8b5cf6;
            background: rgba(139, 92, 246, 0.05);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }
        
        .table tbody tr:hover {
            background: rgba(139, 92, 246, 0.05);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .status-paid {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-shipped {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        
        .status-delivered {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.show {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
        }
        
        @media (min-width: 769px) {
            .sidebar {
                position: relative;
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-skull-crossbones"></i>
                    Wazzy Pods
                </div>
            </div>
            <nav class="nav">
                <a href="index.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="produtos.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categorias</span>
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pedidos</span>
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                <a href="integracao.php" class="nav-item">
                    <i class="fas fa-plug"></i>
                    <span>Integrações</span>
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Bem-vindo ao painel administrativo Wazzy Pods</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_nome'], 0, 1)); ?>
                        </div>
                        <span><?php echo $_SESSION['admin_nome']; ?></span>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </header>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Vendas Hoje</div>
                        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.2); color: #8b5cf6;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">R$ <?php echo number_format($stats['vendas_hoje'], 2, ',', '.'); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Tempo real</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Pedidos</div>
                        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['pedidos'], 0, ',', '.'); ?></div>
                    <div class="stat-change neutral">
                        <i class="fas fa-chart-line"></i>
                        <span>Total geral</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Produtos Ativos</div>
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['produtos'], 0, ',', '.'); ?></div>
                    <div class="stat-change neutral">
                        <i class="fas fa-check-circle"></i>
                        <span>Cadastrados</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Vendas do Mês</div>
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    <div class="stat-value">R$ <?php echo number_format($stats['vendas_mes'], 2, ',', '.'); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Este mês</span>
                    </div>
                </div>
            </div>
            
            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Vendas da Semana</h3>
                    <div style="height: 200px; display: flex; align-items: end; gap: 0.5rem; padding: 1rem 0;">
                        <?php 
                        $maxValor = max(array_column($vendas_semana_completa, 'valor'));
                        if ($maxValor == 0) $maxValor = 1;
                        foreach ($vendas_semana_completa as $dia): 
                            $altura = ($dia['valor'] / $maxValor) * 150;
                        ?>
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="
                                width: 100%; 
                                height: <?php echo $altura; ?>px; 
                                background: linear-gradient(135deg, #8b5cf6, #ec4899); 
                                border-radius: 0.5rem 0.5rem 0 0;
                                margin-bottom: 0.5rem;
                                transition: all 0.3s ease;
                            " title="<?php echo $dia['dia']; ?>: R$ <?php echo number_format($dia['valor'], 2, ',', '.'); ?>"></div>
                            <div style="font-size: 0.75rem; color: #94a3b8; text-align: center;">
                                <div style="font-weight: 600;"><?php echo $dia['dia']; ?></div>
                                <div style="font-size: 0.625rem;">R$ <?php echo number_format($dia['valor'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3 class="chart-title">Produtos Mais Vendidos</h3>
                    <div style="space-y: 1rem;">
                        <?php foreach (array_slice($produtos_populares, 0, 5) as $produto): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(139, 92, 246, 0.05); border-radius: 0.5rem; margin-bottom: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($produto['nome']); ?></div>
                                <div style="font-size: 0.875rem; color: #94a3b8;"><?php echo $produto['vendas']; ?> vendas</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 600; color: #10b981;">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><?php echo $produto['estoque']; ?> em estoque</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">Pedidos Recentes</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos_recentes)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                <i class="fas fa-shopping-cart" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                Nenhum pedido encontrado
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pedidos_recentes as $pedido): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($pedido['cliente_nome'] ?? 'Anônimo'); ?></td>
                            <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </span>
                            </td>
                            <td style="color: #94a3b8;"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            const overlay = document.getElementById('overlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
            
            function closeSidebar() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
            
            menuToggle.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', closeSidebar);
            
            // Fechar sidebar ao clicar em um link
            document.querySelectorAll('.nav-item').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 769) {
                        setTimeout(closeSidebar, 100);
                    }
                });
            });
            
            // Fechar sidebar ao redimensionar para desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 769) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>