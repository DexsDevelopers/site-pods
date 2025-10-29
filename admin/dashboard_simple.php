<?php
// Dashboard simplificado para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Configuração simples
$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se está logado
    if (!isset($_SESSION['admin_logged_in'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_nome'] = 'Admin';
    }
    
    // Estatísticas básicas
    $stats = [];
    
    // Total de produtos ativos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
    $stats['produtos'] = $stmt->fetch()['total'] ?? 0;
    
    // Total de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['pedidos'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas de hoje
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['vendas_hoje'] = $stmt->fetch()['total'] ?? 0;
    
    // Vendas do mês
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stats['vendas_mes'] = $stmt->fetch()['total'] ?? 0;
    
} catch (Exception $e) {
    $stats = [
        'produtos' => 0,
        'pedidos' => 0,
        'vendas_hoje' => 0,
        'vendas_mes' => 0
    ];
    $error = $e->getMessage();
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            color: #94a3b8;
            font-size: 1.125rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: 0.875rem;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            color: #fca5a5;
        }
        
        .success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            color: #6ee7b7;
        }
        
        .nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .nav-item {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            color: #a78bfa;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-item:hover {
            background: rgba(139, 92, 246, 0.2);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-skull-crossbones"></i>
                Wazzy Pods
            </div>
            <p class="subtitle">Dashboard Administrativo</p>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="error">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Erro de Conexão:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php else: ?>
        <div class="success">
            <i class="fas fa-check-circle"></i>
            <strong>Conexão estabelecida com sucesso!</strong>
        </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.2); color: #8b5cf6;">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['produtos'], 0, ',', '.'); ?></div>
                <div class="stat-label">Produtos Ativos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['pedidos'], 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pedidos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['vendas_hoje'], 2, ',', '.'); ?></div>
                <div class="stat-label">Vendas Hoje</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['vendas_mes'], 2, ',', '.'); ?></div>
                <div class="stat-label">Vendas do Mês</div>
            </div>
        </div>
        
        <div class="nav">
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
            <a href="configuracoes.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Configurações</span>
            </a>
        </div>
    </div>
</body>
</html>
