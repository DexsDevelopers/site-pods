<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$pages = ['dashboard', 'products', 'categories', 'orders', 'customers'];

// Validar página
if (!in_array($page, $pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Loja de Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #9333ea;
            --primary-dark: #7e22ce;
            --secondary: #1f2937;
            --accent: #c084fc;
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            color: #e0e7ff;
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(147, 51, 234, 0.15);
            border-radius: 16px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-link {
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #9333ea, #c084fc);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-link.active::before {
            opacity: 1;
        }

        .sidebar-link.active {
            background: rgba(147, 51, 234, 0.15);
            color: #c084fc;
        }

        .sidebar-link:hover {
            background: rgba(147, 51, 234, 0.1);
            transform: translateX(4px);
        }

        .stat-card {
            background: rgba(147, 51, 234, 0.05);
            border: 1px solid rgba(147, 51, 234, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(147, 51, 234, 0.1);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(147, 51, 234, 0.2);
        }

        .table-row:hover {
            background: rgba(147, 51, 234, 0.08);
        }

        .btn-primary {
            background: linear-gradient(135deg, #9333ea, #c084fc);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(147, 51, 234, 0.3);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .sidebar {
            background: rgba(0, 0, 0, 0.2);
            border-right: 1px solid rgba(147, 51, 234, 0.2);
        }

        .topbar {
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(147, 51, 234, 0.2);
            backdrop-filter: blur(20px);
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(147, 51, 234, 0.4);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(147, 51, 234, 0.6);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 to-black">

<div class="flex h-screen">
    
    <!-- SIDEBAR -->
    <div class="sidebar w-64 overflow-y-auto flex-shrink-0 py-6 px-4 flex flex-col">
        <!-- Logo -->
        <div class="mb-8 px-4">
            <div class="gradient-text text-2xl font-black flex items-center gap-2">
                <i class="fas fa-skull-crossbones text-2xl"></i>
                <span>Wazzy Vape</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">Panel Administrativo</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-2 mb-8">
            <a href="?page=dashboard" class="sidebar-link <?php echo $page === 'dashboard' ? 'active' : ''; ?> block px-4 py-3 rounded-lg text-slate-300 font-medium">
                <i class="fas fa-chart-line w-5 mr-3"></i>Dashboard
            </a>
            <a href="?page=products" class="sidebar-link <?php echo $page === 'products' ? 'active' : ''; ?> block px-4 py-3 rounded-lg text-slate-300 font-medium">
                <i class="fas fa-box w-5 mr-3"></i>Produtos
            </a>
            <a href="?page=categories" class="sidebar-link <?php echo $page === 'categories' ? 'active' : ''; ?> block px-4 py-3 rounded-lg text-slate-300 font-medium">
                <i class="fas fa-folder w-5 mr-3"></i>Categorias
            </a>
            <a href="?page=orders" class="sidebar-link <?php echo $page === 'orders' ? 'active' : ''; ?> block px-4 py-3 rounded-lg text-slate-300 font-medium">
                <i class="fas fa-shopping-cart w-5 mr-3"></i>Pedidos
            </a>
            <a href="?page=customers" class="sidebar-link <?php echo $page === 'customers' ? 'active' : ''; ?> block px-4 py-3 rounded-lg text-slate-300 font-medium">
                <i class="fas fa-users w-5 mr-3"></i>Clientes
            </a>
        </nav>

        <!-- Divider -->
        <div class="border-t border-slate-700 py-4 px-4 space-y-2">
            <a href="logout.php" class="sidebar-link block px-4 py-3 rounded-lg text-slate-400 font-medium hover:text-red-400">
                <i class="fas fa-sign-out-alt w-5 mr-3"></i>Sair
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- TOP BAR -->
        <div class="topbar px-8 py-4 flex justify-between items-center flex-shrink-0">
            <div>
                <h2 class="text-2xl font-black">
                    <?php
                    echo match($page) {
                        'dashboard' => '📊 Dashboard',
                        'products' => '📦 Produtos',
                        'categories' => '📂 Categorias',
                        'orders' => '🛒 Pedidos',
                        'customers' => '👥 Clientes',
                        default => '📊 Admin'
                    };
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm text-slate-400">Bem-vindo!</p>
                    <p class="font-bold text-slate-100"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrador'); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <div class="max-w-7xl">
                <?php
                $page_file = "includes/{$page}.php";
                
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    echo '<div class="glass p-6 rounded-lg border border-red-500/30 bg-red-500/5">';
                    echo '<i class="fas fa-exclamation-circle text-red-400 mr-3"></i>';
                    echo '<strong>Erro:</strong> Página não encontrada: ' . htmlspecialchars($page_file);
                    echo '</div>';
                }
                ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>
