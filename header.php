<?php
// Garantir que config e db estão carregados
if (!isset($pdo)) {
    try {
        require_once dirname(__FILE__) . '/includes/config.php';
        require_once dirname(__FILE__) . '/includes/db.php';
    } catch (Exception $e) {
        error_log('Erro ao carregar config no header: ' . $e->getMessage());
    }
}

// Buscar configurações
$site_name = 'Wazzy Pods';
$site_phone = '(11) 9999-9999';
$site_email = 'contato@wazzypods.com';

if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('site_name', 'site_phone', 'site_email') LIMIT 3");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if (!empty($configs['site_name'])) $site_name = $configs['site_name'];
        if (!empty($configs['site_phone'])) $site_phone = $configs['site_phone'];
        if (!empty($configs['site_email'])) $site_email = $configs['site_email'];
    } catch (Exception $e) {
        error_log('Erro ao buscar configurações no header: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?> - Pods Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">

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

    .mobile-menu {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        height: 100vh;
        background: rgba(15, 23, 42, 0.98);
        backdrop-filter: blur(16px);
        transition: right 0.3s ease;
        z-index: 40;
        overflow-y: auto;
    }

    .mobile-menu.active {
        right: 0;
    }

    .hamburger {
        width: 24px;
        height: 24px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .hamburger span {
        display: block;
        width: 100%;
        height: 2px;
        background: #a78bfa;
        margin: 6px 0;
        transition: all 0.3s;
        border-radius: 2px;
    }

    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translateY(12px);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translateY(-12px);
    }

    .nav-dropdown {
        position: relative;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 0.5rem;
        min-width: 200px;
        z-index: 30;
    }

    .nav-dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-menu a {
        display: block;
        padding: 0.75rem 1rem;
        color: #cbd5e1;
        transition: all 0.2s;
    }

    .dropdown-menu a:hover {
        background: rgba(139, 92, 246, 0.2);
        color: #a78bfa;
    }
</style>

<!-- Header Principal -->
<header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center group-hover:shadow-lg transition">
                    <i class="fas fa-skull-crossbones text-white text-lg"></i>
                </div>
                <div>
                    <div class="font-black text-lg gradient-text"><?php echo htmlspecialchars($site_name); ?></div>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-8">
                <a href="/" class="text-slate-400 hover:text-purple-400 transition">Home</a>
                
                <div class="nav-dropdown">
                    <button class="text-slate-400 hover:text-purple-400 transition flex items-center gap-1">
                        Produtos <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/">Todos os Produtos</a>
                        <a href="/">Pods Destaque</a>
                        <a href="/">Promocões</a>
                    </div>
                </div>

                <a href="/" class="text-slate-400 hover:text-purple-400 transition">Contato</a>
                <a href="/pages/profile.php" class="text-slate-400 hover:text-purple-400 transition">Meu Perfil</a>
            </nav>

            <!-- Mobile Controls -->
            <div class="lg:hidden flex items-center gap-4">
                <button onclick="toggleCart()" class="p-2 hover:bg-purple-600/20 rounded-lg transition">
                    <i class="fas fa-shopping-cart text-purple-400"></i>
                    <span id="cart-badge" class="absolute text-xs bg-pink-600 text-white rounded-full w-5 h-5 flex items-center justify-center -mt-2 ml-2" style="display:none;"></span>
                </button>
                <div class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <!-- Desktop Cart and Profile -->
            <div class="hidden lg:flex items-center gap-4">
                <button onclick="toggleCart()" class="p-2 hover:bg-purple-600/20 rounded-lg transition relative">
                    <i class="fas fa-shopping-cart text-purple-400"></i>
                    <span id="cart-badge-desktop" class="absolute text-xs bg-pink-600 text-white rounded-full w-5 h-5 flex items-center justify-center -top-1 -right-1" style="display:none;"></span>
                </button>
                <a href="/pages/profile.php" class="p-2 hover:bg-purple-600/20 rounded-lg transition">
                    <i class="fas fa-user text-purple-400"></i>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="p-6">
        <a href="/" class="block text-lg font-bold gradient-text mb-6 pb-4 border-b border-purple-800/30">Menu</a>
        
        <nav class="space-y-4">
            <a href="/" class="block text-slate-300 hover:text-purple-400 transition">Home</a>
            <div>
                <button onclick="toggleDropdown()" class="w-full text-left text-slate-300 hover:text-purple-400 transition flex justify-between items-center">
                    Produtos <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div id="mobileDropdown" style="display:none;" class="mt-2 pl-4 space-y-2 border-l border-purple-800/30">
                    <a href="/" class="block text-slate-400 hover:text-purple-400 transition">Todos os Produtos</a>
                    <a href="/" class="block text-slate-400 hover:text-purple-400 transition">Pods Destaque</a>
                    <a href="/" class="block text-slate-400 hover:text-purple-400 transition">Promoções</a>
                </div>
            </div>
            <a href="/" class="block text-slate-300 hover:text-purple-400 transition">Contato</a>
            <a href="/pages/profile.php" class="block text-slate-300 hover:text-purple-400 transition">Meu Perfil</a>
        </nav>

        <div class="mt-8 pt-6 border-t border-purple-800/30">
            <p class="text-sm text-slate-400">Contato:</p>
            <p class="text-slate-300 font-semibold"><?php echo htmlspecialchars($site_phone); ?></p>
            <p class="text-slate-400 text-sm"><?php echo htmlspecialchars($site_email); ?></p>
        </div>
    </div>
</div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const hamburger = document.getElementById('hamburger');
        menu.classList.toggle('active');
        hamburger.classList.toggle('active');
    }

    function closeMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const hamburger = document.getElementById('hamburger');
        menu.classList.remove('active');
        hamburger.classList.remove('active');
    }

    function toggleDropdown() {
        const dropdown = document.getElementById('mobileDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const count = cart.length;
        const badge = document.getElementById('cart-badge');
        const badgeDesktop = document.getElementById('cart-badge-desktop');
        
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
            badgeDesktop.textContent = count;
            badgeDesktop.style.display = 'flex';
        } else {
            badge.style.display = 'none';
            badgeDesktop.style.display = 'none';
        }
    }

    function toggleCart() {
        window.location.href = '/pages/cart.php';
    }

    // Atualizar badge ao carregar
    updateCartBadge();
</script>