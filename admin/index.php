<?php
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
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
        .sidebar-link { @apply px-4 py-3 rounded text-slate-400 hover:bg-purple-600 hover:text-white transition block mb-2; }
        .sidebar-link.active { @apply bg-purple-600 text-white; }
    </style>
</head>
<body class="text-slate-100">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 glass border-r border-purple-600/30 p-6 overflow-y-auto">
            <h1 class="text-2xl font-black mb-8 gradient-text">
                <i class="fas fa-cloud mr-2"></i>TechVapor
            </h1>

            <nav class="space-y-2">
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
        <main class="flex-1 overflow-y-auto">
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
                        <span class="text-slate-400">Bem-vindo, <?php echo $_SESSION['admin_name']; ?></span>
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-6">
                <?php
                $pages = ['dashboard', 'products', 'categories', 'orders', 'customers'];
                if (in_array($page, $pages) && file_exists("includes/{$page}.php")) {
                    include "includes/{$page}.php";
                } else {
                    include 'includes/dashboard.php';
                }
                ?>
            </div>
        </main>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>

</body>
</html>
