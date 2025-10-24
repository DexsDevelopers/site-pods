<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();
session_start();

// Valores padrão
$site_name = 'Wazzy Pods';
$site_phone = '(11) 9999-9999';
$site_email = 'contato@wazzypods.com';
$site_address = 'São Paulo, SP';

try {
    if (!file_exists('includes/config.php')) {
        throw new Exception('Arquivo config.php não encontrado');
    }
    include 'includes/config.php';
    
    if (!file_exists('includes/db.php')) {
        throw new Exception('Arquivo db.php não encontrado');
    }
    include 'includes/db.php';
} catch (Exception $e) {
    error_log('ERRO na home: ' . $e->getMessage());
    $categorias = [];
    $produtos = [];
}

// Buscar configurações do banco
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('site_name', 'site_phone', 'site_email', 'site_address') LIMIT 4");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if (!empty($configs['site_name'])) $site_name = $configs['site_name'];
        if (!empty($configs['site_phone'])) $site_phone = $configs['site_phone'];
        if (!empty($configs['site_email'])) $site_email = $configs['site_email'];
        if (!empty($configs['site_address'])) $site_address = $configs['site_address'];
    } catch (Exception $e) {
        error_log('Erro ao buscar configurações na home: ' . $e->getMessage());
    }
}

try {
    if (isset($pdo)) {
        // Buscar categorias ativas
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE ativo = 1 ORDER BY ordem, nome LIMIT 6");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar produtos destaque ou ativos
        $stmt = $pdo->prepare(
            "SELECT p.*, c.nome as categoria_nome 
             FROM produtos p 
             LEFT JOIN categorias c ON p.categoria_id = c.id 
             WHERE p.ativo = 1
             ORDER BY p.destaque DESC, p.created_at DESC LIMIT 8"
        );
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $categorias = [];
        $produtos = [];
    }
} catch (Exception $e) {
    error_log('ERRO ao buscar dados do banco na home: ' . $e->getMessage());
    $categorias = [];
    $produtos = [];
}

$avaliacoes = [
    ['nome' => 'João Silva', 'texto' => 'Wazzy Pods oferece a melhor qualidade do mercado. Entrega rápida e excelente atendimento!', 'rating' => 5],
    ['nome' => 'Maria Santos', 'texto' => 'Adorei! Produtos premium com entrega no prazo. Voltarei com certeza!', 'rating' => 5],
    ['nome' => 'Pedro Costa', 'texto' => 'Melhor loja de pods que já comprei. Qualidade garantida em tudo!', 'rating' => 5],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wazzy Pods - Pods Premium</title>
    <meta name="description" content="Wazzy Pods - Loja premium de pods com qualidade garantida e entrega rápida.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #a78bfa;
            --secondary: #1f2937;
            --accent: #ec4899;
            --success: #10b981;
        }

        * {
            scroll-behavior: smooth;
        }

        html {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Glassmorphism Moderno */
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
        }

        /* Gradiente de Texto */
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Botão Premium */
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5);
        }

        /* Card Hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(124, 58, 237, 0.25);
        }

        /* Hero Gradient */
        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 50%, #16213e 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-slow 20s infinite;
        }

        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        /* Scroll Top Button */
        .scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 99;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.6);
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-primary {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2) 0%, rgba(236, 72, 153, 0.2) 100%);
            color: #c7d2fe;
            border: 1px solid rgba(124, 58, 237, 0.3);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1f3a;
        }

        ::-webkit-scrollbar-thumb {
            background: #7c3aed;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a78bfa;
        }

        /* Menu Mobile Animado */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        .mobile-menu-overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Animação do hambúrguer */
        .hamburger {
            position: relative;
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        .hamburger span {
            position: absolute;
            width: 100%;
            height: 2px;
            background: #a78bfa;
            left: 0;
            transition: all 0.3s ease;
        }

        .hamburger span:nth-child(1) {
            top: 0;
        }

        .hamburger span:nth-child(2) {
            top: 11px;
        }

        .hamburger span:nth-child(3) {
            top: 22px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg);
            top: 11px;
        }

        .hamburger.active span:nth-child(2) {
                opacity: 0;
            }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg);
            top: 11px;
        }

        /* Dropdown Desktop */
        .nav-dropdown {
            position: relative;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 12px;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .nav-dropdown:hover .dropdown-content {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-content a {
            display: block;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a:hover {
            background: rgba(124, 58, 237, 0.15);
            color: #a78bfa;
            padding-left: 20px;
        }

        /* Badge Carrinho */
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header Premium Responsivo -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-slate-950/95 shadow-lg border-b border-purple-900/30">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="#home" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center group-hover:shadow-lg group-hover:shadow-purple-600/50 transition">
                        <i class="fas fa-skull-crossbones text-white text-lg"></i>
                    </div>
                    <div>
                        <div class="font-black text-lg gradient-text group-hover:drop-shadow-lg transition"><?php echo htmlspecialchars(strtoupper($site_name)); ?></div>
                </div>
                </a>

                <!-- Menu Desktop -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#home" class="text-slate-300 hover:text-purple-300 transition font-medium relative group">
                        Home
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-purple-400 to-pink-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    
                    <div class="nav-dropdown">
                        <button class="text-slate-300 hover:text-purple-300 transition font-medium relative group">
                            Produtos
                            <i class="fas fa-chevron-down text-xs ml-2"></i>
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-purple-400 to-pink-400 group-hover:w-full transition-all duration-300"></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="#produtos"><i class="fas fa-star mr-2 text-yellow-400"></i>Em Destaque</a>
                            <a href="#categorias"><i class="fas fa-tags mr-2 text-purple-400"></i>Categorias</a>
                            <a href="#"><i class="fas fa-fire mr-2 text-red-400"></i>Promoções</a>
                            <a href="#"><i class="fas fa-box mr-2 text-blue-400"></i>Todos os Produtos</a>
                        </div>
                </div>

                    <a href="#avaliacoes" class="text-slate-300 hover:text-purple-300 transition font-medium relative group">
                        Avaliações
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-purple-400 to-pink-400 group-hover:w-full transition-all duration-300"></span>
                    </a>

                    <a href="#" class="text-slate-300 hover:text-purple-300 transition font-medium relative group">
                        Contato
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-purple-400 to-pink-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                </div>

                <!-- Desktop Right Side -->
                <div class="hidden lg:flex items-center gap-4">
                    <button class="relative p-2 hover:bg-slate-800 rounded-lg transition group">
                        <i class="fas fa-search text-purple-400 text-lg group-hover:text-purple-300 transition"></i>
                    </button>
                    <button class="relative p-2 hover:bg-slate-800 rounded-lg transition group" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart text-purple-400 text-lg group-hover:text-purple-300 transition"></i>
                        <span class="cart-badge" id="cart-count">0</span>
                    </button>
                </div>

                <!-- Mobile Controls -->
                <div class="lg:hidden flex items-center gap-4">
                    <button class="relative p-2 hover:bg-slate-800 rounded-lg transition">
                        <i class="fas fa-search text-purple-400 text-lg"></i>
                    </button>
                    <button class="relative p-2 hover:bg-slate-800 rounded-lg transition" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart text-purple-400 text-lg"></i>
                        <span class="cart-badge" id="cart-count-mobile">0</span>
                    </button>
                    <button class="hamburger p-2" id="hamburger-btn" onclick="toggleMobileMenu()">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay fixed inset-0 bg-black/50 lg:hidden z-40" id="menu-overlay" onclick="closeMobileMenu()"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu fixed top-0 left-0 h-full w-64 bg-slate-950 border-r border-purple-900/30 z-40 lg:hidden shadow-2xl" id="mobile-menu">
        <div class="p-6 border-b border-purple-900/20">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-skull-crossbones text-white text-sm"></i>
                </div>
                <div>
                    <div class="font-black text-sm gradient-text">WAZZY</div>
                    <div class="text-xs text-slate-400">PODS</div>
                </div>
            </div>
        </div>

        <div class="flex flex-col p-4 space-y-2">
            <a href="#home" onclick="closeMobileMenu()" class="px-4 py-3 text-slate-300 hover:text-purple-300 hover:bg-slate-800/50 rounded-lg transition font-medium flex items-center gap-2">
                <i class="fas fa-home text-purple-400"></i> Home
            </a>
            
            <div class="px-4 py-3">
                <button class="w-full text-left text-slate-300 hover:text-purple-300 font-medium flex items-center gap-2" onclick="toggleDropdown(this)">
                    <i class="fas fa-box text-purple-400"></i> Produtos
                    <i class="fas fa-chevron-right text-xs ml-auto transition-transform" style="transform: rotate(0deg)"></i>
                </button>
                <div class="hidden flex-col mt-2 ml-6 space-y-2" style="display: none;">
                    <a href="#produtos" onclick="closeMobileMenu()" class="text-slate-400 hover:text-purple-300 transition text-sm py-2 flex items-center gap-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>Em Destaque
                    </a>
                    <a href="#categorias" onclick="closeMobileMenu()" class="text-slate-400 hover:text-purple-300 transition text-sm py-2 flex items-center gap-2">
                        <i class="fas fa-tags text-purple-400 text-xs"></i>Categorias
                    </a>
                    <a href="#" onclick="closeMobileMenu()" class="text-slate-400 hover:text-purple-300 transition text-sm py-2 flex items-center gap-2">
                        <i class="fas fa-fire text-red-400 text-xs"></i>Promoções
                    </a>
                    <a href="#" onclick="closeMobileMenu()" class="text-slate-400 hover:text-purple-300 transition text-sm py-2 flex items-center gap-2">
                        <i class="fas fa-all text-blue-400 text-xs"></i>Todos
                    </a>
                </div>
            </div>

            <a href="#avaliacoes" onclick="closeMobileMenu()" class="px-4 py-3 text-slate-300 hover:text-purple-300 hover:bg-slate-800/50 rounded-lg transition font-medium flex items-center gap-2">
                <i class="fas fa-star text-purple-400"></i> Avaliações
            </a>

            <a href="#" onclick="closeMobileMenu()" class="px-4 py-3 text-slate-300 hover:text-purple-300 hover:bg-slate-800/50 rounded-lg transition font-medium flex items-center gap-2">
                <i class="fas fa-phone text-purple-400"></i> Contato
            </a>

            <div class="border-t border-purple-900/20 mt-4 pt-4">
                <button class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-white flex items-center gap-2 justify-center hover:shadow-lg hover:shadow-purple-600/50 transition">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Controle do Menu Mobile
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('menu-overlay');
            const hamburger = document.getElementById('hamburger-btn');
            
            menu.classList.toggle('active');
            overlay.classList.toggle('active');
            hamburger.classList.toggle('active');
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('menu-overlay');
            const hamburger = document.getElementById('hamburger-btn');
            
            menu.classList.remove('active');
            overlay.classList.remove('active');
            hamburger.classList.remove('active');
        }

        // Toggle Dropdown Mobile
        function toggleDropdown(btn) {
            const submenu = btn.nextElementSibling;
            const icon = btn.querySelector('i:last-child');
            
            submenu.style.display = submenu.style.display === 'none' ? 'flex' : 'none';
            icon.style.transform = submenu.style.display === 'none' ? 'rotate(0deg)' : 'rotate(90deg)';
        }

        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('mobile-menu');
            const hamburger = document.getElementById('hamburger-btn');
            
            if (!menu.contains(e.target) && !hamburger.contains(e.target)) {
                closeMobileMenu();
            }
        });

        function toggleCart() {
            window.location.href = '/pages/cart.php';
        }
    </script>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient pt-32 pb-24 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-7xl mx-auto w-full relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="mb-8">
                        <span class="badge badge-primary">
                            <i class="fas fa-star mr-2"></i> Qualidade Premium
                        </span>
                    </div>
                    
                    <h1 class="text-6xl md:text-7xl font-black mb-6 leading-tight text-slate-50">
                        Wazzy <span class="gradient-text">Pods</span>
                    </h1>
                    
                    <p class="text-xl text-slate-300 mb-12 leading-relaxed">
                        Descubra a melhor seleção de pods premium com qualidade garantida, entrega rápida e atendimento excepcional.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <button class="px-8 py-4 btn-primary rounded-lg font-bold ripple relative z-10 text-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Comprar Agora
                        </button>
                        <button class="px-8 py-4 glass text-purple-300 rounded-lg font-bold ripple relative z-10 text-lg hover:bg-slate-800 transition">
                            <i class="fas fa-arrow-down mr-2"></i>
                            Ver Produtos
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="glass p-4 rounded-lg text-center">
                            <div class="text-2xl font-black gradient-text">500+</div>
                            <div class="text-xs text-gray-500 mt-1">Produtos</div>
                        </div>
                        <div class="glass p-4 rounded-lg text-center">
                            <div class="text-2xl font-black gradient-text">10K+</div>
                            <div class="text-xs text-gray-500 mt-1">Clientes</div>
                        </div>
                        <div class="glass p-4 rounded-lg text-center">
                            <div class="text-2xl font-black gradient-text">5.0★</div>
                            <div class="text-xs text-gray-500 mt-1">Rating</div>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-left" data-aos-duration="1000" class="relative h-96 md:h-full min-h-96">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-100 to-pink-100 rounded-3xl blur-2xl opacity-40"></div>
                    <div class="relative h-full glass rounded-3xl p-8 flex items-center justify-center text-center border-2 border-purple-800/50" style="background-image: url('WhatsApp Image 2025-10-17 at 16.17.27.jpeg'); background-size: cover; background-position: center;">
                        </div>
                    </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Banner -->
    <section class="py-8 px-4 sm:px-6 lg:px-8 bg-slate-900/40 border-b border-purple-900/30">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-truck text-2xl text-purple-400 mb-2"></i>
                    <p class="font-bold text-slate-100">Frete Grátis</p>
                    <p class="text-xs text-slate-400">Acima de R$ 100</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-shield-alt text-2xl text-purple-400 mb-2"></i>
                    <p class="font-bold text-slate-100">100% Seguro</p>
                    <p class="text-xs text-slate-400">Proteção total</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-redo text-2xl text-purple-400 mb-2"></i>
                    <p class="font-bold text-slate-100">Devolução</p>
                    <p class="text-xs text-slate-400">30 dias garantidos</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-headset text-2xl text-purple-400 mb-2"></i>
                    <p class="font-bold text-slate-100">Suporte 24/7</p>
                    <p class="text-xs text-slate-400">Sempre pronto</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section id="categorias" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 text-slate-50">
                    Escolha sua <span class="gradient-text">Categoria</span>
                </h2>
                <p class="text-xl text-slate-400">Explore nossa seleção premium de pods</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                if (empty($categorias)): 
                    echo '<p class="col-span-full text-center text-slate-400">Nenhuma categoria disponível</p>';
                else:
                    foreach ($categorias as $i => $cat):
                        // Contar produtos por categoria
                        try {
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ? AND ativo = 1");
                            $stmt->execute([$cat['id']]);
                            $totalProd = $stmt->fetch()['total'] ?? 0;
                        } catch (Exception $e) {
                            $totalProd = 0;
                        }
                ?>
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-xl transition cursor-pointer border border-purple-800/50" 
                     data-aos="zoom-in" data-aos-delay="<?php echo $i * 100; ?>"
                     style="border-color: <?php echo htmlspecialchars($cat['cor'] ?? '#8B5CF6'); ?>40; cursor: pointer;"
                     onclick="window.location.href='produtos.php?categoria=<?php echo urlencode($cat['slug'] ?? ''); ?>'">
                    <div class="text-5xl mb-4">
                        <i class="<?php echo htmlspecialchars($cat['icone'] ?? 'fas fa-box'); ?>" style="color: <?php echo htmlspecialchars($cat['cor'] ?? '#8B5CF6'); ?>"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-2 text-slate-100"><?php echo htmlspecialchars($cat['nome']); ?></h3>
                    <p class="text-slate-400 mb-4"><?php echo htmlspecialchars($cat['descricao'] ?? 'Confira nossa seleção'); ?></p>
                    <p class="text-sm font-bold gradient-text"><?php echo $totalProd; ?>+ Produtos</p>
                </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Produtos -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-950/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="badge badge-primary mb-4">
                    <i class="fas fa-fire mr-2"></i> Mais Vendidos
                </span>
                <h2 class="text-5xl font-black mb-4 text-slate-50">
                    Produtos em <span class="gradient-text">Destaque</span>
                </h2>
                <p class="text-xl text-slate-400">Seleção premium dos melhores produtos</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($produtos, 0, 8) as $i => $produto): 
                    $precoOriginal = $produto['preco'];
                    $precoFinal = $produto['preco_promocional'] ?? $precoOriginal;
                    $desconto = $precoOriginal > 0 ? round((($precoOriginal - $precoFinal) / $precoOriginal) * 100) : 0;
                    $avaliacao = $produto['avaliacao_media'] ?? 0;
                    $estoque = $produto['estoque'] ?? 0;
                ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group relative border border-purple-800/50" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <?php if ($desconto > 0): ?>
                    <div class="absolute top-4 right-4 badge badge-primary z-10">
                        -<?php echo $desconto; ?>%
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($produto['destaque']): ?>
                    <div class="absolute top-4 left-4 badge bg-yellow-600/80 text-yellow-200 z-10">
                        <i class="fas fa-star mr-1"></i> Destaque
                    </div>
                    <?php else: ?>
                    <div class="absolute top-4 left-4 badge badge-primary">
                            <?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Novo'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="relative h-56 overflow-hidden bg-slate-800">
                        <?php 
                        $imagemProduto = $produto['imagem'] ?? '';
                        $imagemFallback = 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=400&h=300&fit=crop&auto=format';
                        
                        if (empty($imagemProduto)) {
                            $imagemProduto = $imagemFallback;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagemProduto); ?>" 
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500" 
                             onerror="this.src='<?php echo $imagemFallback; ?>'">
                    </div>
                        
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-slate-100 line-clamp-2 mb-3"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="text-slate-400 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($produto['descricao_curta'] ?? $produto['descricao'] ?? '', 0, 80)); ?>...</p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex gap-0.5">
                                <?php for($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star <?php echo $j < round($avaliacao) ? 'text-yellow-400' : 'text-slate-600'; ?> text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-yellow-400 text-xs font-bold"><?php echo number_format($avaliacao, 1, '.', ''); ?></span>
                        </div>

                        <div class="mb-5 pb-5 border-b border-slate-700">
                            <span class="text-2xl font-black gradient-text">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                            <?php if ($desconto > 0): ?>
                            <span class="text-slate-500 line-through ml-2 text-sm">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4 text-xs text-slate-400">
                            <?php if ($estoque > 0): ?>
                                <span class="text-green-400"><i class="fas fa-check-circle mr-1"></i><?php echo $estoque; ?> em estoque</span>
                            <?php else: ?>
                                <span class="text-red-400"><i class="fas fa-times-circle mr-1"></i>Fora de estoque</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex gap-2">
                            <button onclick="event.stopPropagation(); addToCart(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $precoFinal; ?>); return false;" 
                                    class="flex-1 py-3 bg-slate-800/50 border border-purple-800/30 rounded-lg font-bold text-sm transition hover:bg-slate-700/50 hover:border-purple-600/50 <?php echo $estoque <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                    <?php echo $estoque <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus mr-2"></i>
                                Carrinho
                            </button>
                            <button onclick="window.location.href='pages/product-detail.php?id=<?php echo $produto['id']; ?>'" 
                                    class="flex-1 py-3 btn-primary rounded-lg font-bold text-sm transition <?php echo $estoque <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                    <?php echo $estoque <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-bag mr-2"></i>
                                Comprar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="pages/produtos.php" class="inline-block px-10 py-4 glass rounded-lg font-bold text-lg gradient-text hover:shadow-lg transition border border-purple-800/50">
                    <i class="fas fa-box mr-2"></i>
                    Ver Todos os Produtos
                </a>
            </div>
        </div>
    </section>

    <!-- Avaliações -->
    <section id="avaliacoes" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/40">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 text-slate-50">
                    O que nossos <span class="gradient-text">Clientes</span> dizem
                </h2>
                <div class="flex justify-center gap-1 mb-4">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-2xl"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-xl text-slate-400">5.0 ★ em mais de 2.500 avaliações verificadas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($avaliacoes as $i => $avaliacao): ?>
                <div class="glass rounded-2xl p-8 card-hover border border-purple-800/50" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="flex gap-1 mb-4">
                        <?php for($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star text-yellow-400 text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-slate-300 mb-6 italic text-lg">"<?php echo $avaliacao['texto']; ?>"</p>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-100"><?php echo $avaliacao['nome']; ?></p>
                            <p class="text-slate-500 text-sm"><i class="fas fa-check-circle text-green-500 mr-1"></i>Comprador Verificado</p>
                        </div>
                    </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-950/50">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glass rounded-3xl p-12 md:p-16 text-center border-2 border-purple-800/50 relative">
                <h2 class="text-5xl font-black mb-6 text-slate-50">
                    Ganhe <span class="gradient-text">20% OFF</span>
                </h2>
                <p class="text-xl text-slate-300 mb-10">
                    Inscreva-se na newsletter e receba 20% de desconto + acesso exclusivo a promoções!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                    <input type="email" placeholder="seu@email.com" class="flex-1 px-6 py-4 rounded-lg bg-slate-800/50 text-slate-100 border border-purple-800/50 focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder-slate-500 text-base">
                    <button class="px-8 py-4 btn-primary rounded-lg font-bold whitespace-nowrap text-base">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Inscrever
                    </button>
                </div>
                <p class="text-slate-400 text-sm mt-6">📧 Sem spam, apenas promoções!</p>
            </div>
            </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-16 px-4 sm:px-6 lg:px-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div>
                    <div class="text-2xl font-black mb-6 gradient-text flex items-center gap-2">
                        <i class="fas fa-skull-crossbones"></i>
                        <span><?php echo htmlspecialchars($site_name); ?></span>
                    </div>
                    <p class="text-slate-400 mb-6 leading-relaxed">Sua loja premium de pods com qualidade garantida e atendimento excepcional.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:gradient-text transition text-lg"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:gradient-text transition text-lg"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:gradient-text transition text-lg"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="text-slate-400 hover:gradient-text transition text-lg"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-200">Produtos</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="#" class="hover:gradient-text transition">Pods Descartáveis</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Pods Recarregáveis</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Acessórios</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Ofertas Especiais</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-200">Suporte</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="tel:<?php echo urlencode($site_phone); ?>" class="hover:gradient-text transition">WhatsApp: <?php echo htmlspecialchars($site_phone); ?></a></li>
                        <li><a href="#" class="hover:gradient-text transition">FAQ & Dúvidas</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Rastrear Pedido</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Trocas e Devoluções</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-200">Legal</h4>
                    <ul class="space-y-3 text-slate-400 text-sm">
                        <li><a href="#" class="hover:gradient-text transition">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Termos de Uso</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Aviso de Saúde</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Restrição de Idade: 18+</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-slate-400 text-center md:text-left">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. Todos os direitos reservados. | ⚠️ Contém Nicotina</p>
                <div class="flex gap-6">
                    <i class="fab fa-cc-visa text-2xl text-slate-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-slate-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-cc-paypal text-2xl text-slate-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-bitcoin text-2xl text-slate-400 hover:gradient-text transition cursor-pointer"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top -->
    <button id="scroll-top" class="scroll-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        AOS.init({
            duration: 1000,
            once: false,
            mirror: true
        });

        const scrollTopBtn = document.getElementById('scroll-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        gsap.registerPlugin();
        
        gsap.from('h1', {
            duration: 1.2,
            y: 60,
            opacity: 0,
            ease: 'power3.out'
        });

        // ========== CLASSE CART ==========
        class Cart {
            constructor() {
                this.items = this.loadFromStorage();
            }

            loadFromStorage() {
                try {
                    const data = localStorage.getItem('cart');
                    return data ? JSON.parse(data) : [];
                } catch (e) {
                    return [];
                }
            }

            saveToStorage() {
                localStorage.setItem('cart', JSON.stringify(this.items));
                this.updateBadge();
            }

            add(product) {
                const existing = this.items.find(i => i.id === product.id);
                if (existing) {
                    existing.quantity += product.quantity || 1;
                } else {
                    product.quantity = product.quantity || 1;
                    this.items.push(product);
                }
                this.saveToStorage();
            }

            updateBadge() {
                const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
                const badges = document.querySelectorAll('#cart-count, #cart-count-mobile');
                badges.forEach(b => b.textContent = count);
            }
        }

        // Inicializar carrinho
        const cart = new Cart();
        cart.updateBadge();

        function addToCart(id, nome, preco) {
            const item = {
                id: id,
                nome: nome,
                preco_final: preco,
                quantity: 1,
                imagem: 'https://via.placeholder.com/100'
            };
            cart.add(item);
            
            // Mostrar notificação de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Adicionado ao Carrinho!',
                text: nome + ' foi adicionado com sucesso',
                timer: 2000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        }
    </script>

</body>
</html>