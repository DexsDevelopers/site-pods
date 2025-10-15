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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); 
        }
        .glass { 
            background: rgba(0, 0, 0, 0.1); 
            backdrop-filter: blur(10px); 
        }
        .sidebar-link { 
            padding: 12px 16px;
            border-radius: 8px;
            color: #64748b;
            display: block;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        .sidebar-link:hover { 
            background-color: #9333ea;
            color: white;
        }
        .sidebar-link.active { 
            background-color: #9333ea; 
            color: white;
        }
        .gradient-text {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="text-slate-100 m-0">

    <div class="flex h-screen bg-gradient-to-br from-slate-900 to-slate-800">
        <!-- Sidebar -->
        <aside class="w-64 glass border-r border-purple-600/30 p-6 overflow-y-auto flex flex-col">
            <h1 class="text-2xl font-black mb-8 gradient-text">
                <i class="fas fa-cloud mr-2"></i>TechVapor
            </h1>

            <nav class="space-y-2 flex-1">
                <a href="?page=dashboard" class="sidebar-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line mr-2"></i>Dashboard
                </a>
                <a href="?page=products" class="sidebar-link <?php echo $page === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box mr-2"></i>Produtos
                </a>
                <a href="?page=categories" class="sidebar-link <?php echo $page === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-folder mr-2"></i>Categorias
                </a>
                <a href="?page=orders" class="sidebar-link <?php echo $page === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart mr-2"></i>Pedidos
                </a>
                <a href="?page=customers" class="sidebar-link <?php echo $page === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-2"></i>Clientes
                </a>
            </nav>

            <hr class="border-slate-700 my-6">

            <a href="logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt mr-2"></i>Sair
            </a>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto flex flex-col bg-gradient-to-br from-slate-900 to-slate-800">
            <!-- Top Bar -->
            <div class="glass border-b border-purple-600/30 p-6 sticky top-0 z-10">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black">
                        <?php
                        echo match($page) {
                            'dashboard' => '📊 Dashboard',
                            'products' => '📦 Produtos',
                            'categories' => '📂 Categorias',
                            'orders' => '🛒 Pedidos',
                            'customers' => '👥 Clientes',
                            default => '📊 Admin Panel'
                        };
                        ?>
                    </h2>
                    <div class="flex items-center gap-4">
                        <span class="text-slate-400">Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-6 flex-1 overflow-y-auto">
                <?php
                $pages = ['dashboard', 'products', 'categories', 'orders', 'customers'];
                $page_file = "includes/{$page}.php";
                
                if (in_array($page, $pages) && file_exists($page_file)) {
                    try {
                        include $page_file;
                    } catch (Exception $e) {
                        echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded">';
                        echo '❌ Erro ao carregar página: ' . htmlspecialchars($e->getMessage());
                        echo '</div>';
                    }
                } else {
                    try {
                        include 'includes/dashboard.php';
                    } catch (Exception $e) {
                        echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded">';
                        echo '❌ Erro ao carregar dashboard: ' . htmlspecialchars($e->getMessage());
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </main>
    </div>

</body>
</html>
