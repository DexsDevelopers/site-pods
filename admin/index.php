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
    <title>Admin - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100">

<div style="display: flex; height: 100vh;">
    
    <!-- SIDEBAR -->
    <div style="width: 250px; background: rgba(0,0,0,0.3); border-right: 1px solid rgba(147,51,234,0.3); padding: 24px; overflow-y: auto; flex-shrink: 0;">
        <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 32px; background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-cloud" style="margin-right: 8px;"></i>TechVapor
        </h1>

        <nav style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 32px;">
            <a href="?page=dashboard" style="padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?php echo $page === 'dashboard' ? 'white' : '#94a3b8'; ?>; background-color: <?php echo $page === 'dashboard' ? 'rgba(147,51,234,0.8)' : 'transparent'; ?>; transition: all 0.3s;">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i>Dashboard
            </a>
            <a href="?page=products" style="padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?php echo $page === 'products' ? 'white' : '#94a3b8'; ?>; background-color: <?php echo $page === 'products' ? 'rgba(147,51,234,0.8)' : 'transparent'; ?>; transition: all 0.3s;">
                <i class="fas fa-box" style="margin-right: 8px;"></i>Produtos
            </a>
            <a href="?page=categories" style="padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?php echo $page === 'categories' ? 'white' : '#94a3b8'; ?>; background-color: <?php echo $page === 'categories' ? 'rgba(147,51,234,0.8)' : 'transparent'; ?>; transition: all 0.3s;">
                <i class="fas fa-folder" style="margin-right: 8px;"></i>Categorias
            </a>
            <a href="?page=orders" style="padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?php echo $page === 'orders' ? 'white' : '#94a3b8'; ?>; background-color: <?php echo $page === 'orders' ? 'rgba(147,51,234,0.8)' : 'transparent'; ?>; transition: all 0.3s;">
                <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>Pedidos
            </a>
            <a href="?page=customers" style="padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?php echo $page === 'customers' ? 'white' : '#94a3b8'; ?>; background-color: <?php echo $page === 'customers' ? 'rgba(147,51,234,0.8)' : 'transparent'; ?>; transition: all 0.3s;">
                <i class="fas fa-users" style="margin-right: 8px;"></i>Clientes
            </a>
        </nav>

        <hr style="border: none; border-top: 1px solid rgba(71,85,105,0.5); margin: 24px 0;">

        <a href="logout.php" style="display: block; padding: 12px 16px; border-radius: 8px; text-decoration: none; color: #94a3b8; transition: all 0.3s;">
            <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i>Sair
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
        
        <!-- TOP BAR -->
        <div style="background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(147,51,234,0.3); padding: 24px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <h2 style="font-size: 24px; font-weight: 900; margin: 0;">
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
            <div style="display: flex; align-items: center; gap: 16px;">
                <span style="color: #94a3b8;">Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <div style="width: 40px; height: 40px; background: linear-gradient(to right, #9333ea, #ec4899); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="color: white;"></i>
                </div>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div style="flex: 1; overflow-y: auto; padding: 24px;">
            <?php
            $page_file = "includes/{$page}.php";
            
            try {
                if (file_exists($page_file)) {
                    ob_start();
                    include $page_file;
                    $content = ob_get_clean();
                    
                    if (empty($content) || trim($content) === '') {
                        echo '<div style="color: #fbbf24; background: rgba(217,119,6,0.2); padding: 16px; border-radius: 8px; border: 1px solid rgba(217,119,6,0.5);">';
                        echo '⚠️ Arquivo vazio: ' . htmlspecialchars($page_file);
                        echo '</div>';
                    } else {
                        echo $content;
                    }
                } else {
                    echo '<div style="color: red; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px; border: 1px solid rgba(220,38,38,0.5);">';
                    echo '❌ Arquivo não encontrado: ' . htmlspecialchars($page_file);
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px; border: 1px solid rgba(220,38,38,0.5);">';
                echo '❌ Erro: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            ?>
        </div>

    </div>

</div>

</body>
</html>
