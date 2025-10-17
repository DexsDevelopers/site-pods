<?php
// Configuração de erros ANTES de tudo
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar buffer para capturar erros
ob_start();

session_start();

// Tente carregar configurações
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

// Tente buscar dados do banco
try {
    if (isset($db)) {
        $db = Database::getInstance();
        
        // Buscar categorias
        $stmt = $db->getConnection()->prepare("SELECT * FROM categories ORDER BY nome LIMIT 6");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar produtos em destaque (últimos 8)
        $stmt = $db->getConnection()->prepare(
            "SELECT p.*, c.nome as categoria_nome FROM products p 
             LEFT JOIN categories c ON p.categoria_id = c.id 
             ORDER BY p.criado_em DESC LIMIT 8"
        );
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $categorias = [];
        $produtos = [];
    }
} catch (Exception $e) {
    error_log('ERRO ao buscar dados BD na home: ' . $e->getMessage());
    $categorias = [];
    $produtos = [];
}

$sabores = [
    ['nome' => 'Frutas Tropicais', 'emoji' => '🥭', 'produtos' => 24],
    ['nome' => 'Mint Gelado', 'emoji' => '❄️', 'produtos' => 18],
    ['nome' => 'Doces', 'emoji' => '🍰', 'produtos' => 31],
    ['nome' => 'Bebidas', 'emoji' => '🍹', 'produtos' => 22],
];

$avaliacoes = [
    ['nome' => 'João Silva', 'texto' => 'Melhor loja de pods que conheço! Produtos de qualidade e entrega rápida!', 'rating' => 5],
    ['nome' => 'Maria Santos', 'texto' => 'Adorei o atendimento, muito atencioso e eficiente!', 'rating' => 5],
    ['nome' => 'Pedro Costa', 'texto' => 'Qualidade excepcional, voltarei com certeza!', 'rating' => 4.5],
];

$blog = [
    ['titulo' => 'Guia Completo: Como Escolher seu Primeiro Pod', 'data' => '15 de Outubro', 'img' => 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=300&h=200&fit=crop'],
    ['titulo' => '5 Sabores Imprescindíveis para Iniciantes', 'data' => '12 de Outubro', 'img' => 'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=300&h=200&fit=crop'],
    ['titulo' => 'Manutenção Correta do seu Pod Recarregável', 'data' => '10 de Outubro', 'img' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=200&fit=crop'],
];

$marcas = [
    ['nome' => 'VooPoo', 'logo' => 'V'],
    ['nome' => 'Geekvape', 'logo' => 'G'],
    ['nome' => 'Smok', 'logo' => 'S'],
    ['nome' => 'Lost Vape', 'logo' => 'L'],
    ['nome' => 'Eleaf', 'logo' => 'E'],
    ['nome' => 'Innokin', 'logo' => 'I'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Pods Premium - Pods Descartáveis e Recarregáveis</title>
    <meta name="description" content="Loja oficial de pods descartáveis e recarregáveis. Qualidade garantida, entrega rápida. Confira nossa seleção premium.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS - Animate On Scroll -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    
    <!-- GSAP - Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <style>
        :root {
            --primary: #9333ea;
            --primary-dark: #7e22ce;
            --secondary: #1f2937;
            --accent: #c084fc;
            --success: #10b981;
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Premium Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(147, 51, 234, 0.15);
            border-radius: 16px;
        }

        body.dark .glass {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(147, 51, 234, 0.25);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Smooth Transitions */
        .btn-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
            z-index: 0;
        }

        .btn-hover:hover::before {
            left: 100%;
        }

        .btn-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(147, 51, 234, 0.4);
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* Card Hover Effect */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(147, 51, 234, 0.3);
        }

        /* Badge Animation */
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Trust Badge */
        .trust-badge {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Premium Hero */
        .hero-gradient {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(147, 51, 234, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(192, 132, 252, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Scroll to top button */
        .scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 99;
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        /* Smooth scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a2e;
        }

        body.dark ::-webkit-scrollbar-track {
            background: #0a0a0f;
        }

        ::-webkit-scrollbar-thumb {
            background: #9333ea;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #c084fc;
        }

        /* Section Divider */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(147, 51, 234, 0.3), transparent);
        }

        /* Premium Input */
        .premium-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(147, 51, 234, 0.3);
            transition: all 0.3s ease;
        }

        .premium-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(147, 51, 234, 0.6);
            box-shadow: 0 0 20px rgba(147, 51, 234, 0.2);
        }

        /* Product Badge */
        .badge-sale {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #ff1744, #d32f2f);
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            animation: pulse 2s infinite;
            z-index: 10;
        }

        /* Tailwind Dark Mode */
        body.dark {
            background-color: #0a0a0f;
            color: #e0e7ff;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            color: #e0e7ff;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 to-black text-slate-100">

    <!-- Header/Navbar Premium -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-black/80 shadow-lg border-b border-purple-900/30">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="gradient-text text-3xl font-bold flex items-center gap-2">
                        <i class="fas fa-cloud-mist"></i>
                        <span>Loja de Pods</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-slate-300 hover:text-purple-400 transition font-medium">Home</a>
                    <a href="#produtos" class="text-slate-300 hover:text-purple-400 transition font-medium">Produtos</a>
                    <a href="#categorias" class="text-slate-300 hover:text-purple-400 transition font-medium">Categorias</a>
                    <a href="#avaliacoes" class="text-slate-300 hover:text-purple-400 transition font-medium">Avaliações</a>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg glass hover:bg-white/10 transition btn-hover">
                        <i class="fas fa-moon text-purple-400 dark:hidden"></i>
                        <i class="fas fa-sun text-yellow-400 hidden dark:block"></i>
                    </button>

                    <!-- Cart -->
                    <button class="relative p-2 rounded-lg glass hover:bg-white/10 transition btn-hover">
                        <i class="fas fa-shopping-cart text-purple-400"></i>
                        <span class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center badge-pulse">0</span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section Premium -->
    <section id="home" class="hero-gradient pt-32 pb-24 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-7xl mx-auto w-full relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="mb-8">
                        <span class="inline-block px-5 py-2 glass rounded-full text-sm font-bold gradient-text mb-6">
                            <i class="fas fa-star"></i> Premium Quality Pods
                        </span>
                    </div>
                    
                    <h1 class="text-7xl md:text-8xl font-black mb-6 leading-tight">
                        Pods de <span class="gradient-text">Qualidade</span>
                    </h1>
                    
                    <p class="text-xl text-slate-300 mb-12 leading-relaxed">
                        Descubra a melhor seleção de pods descartáveis e recarregáveis. Qualidade garantida, entrega rápida e atendimento excepcional para você.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-5 mb-12">
                        <button class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 hover:shadow-xl text-lg transition-all duration-300">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Comprar Agora
                        </button>
                        <button class="px-8 py-4 glass text-white rounded-lg font-bold btn-hover ripple relative z-10 text-lg hover:bg-white/20 transition-all">
                            <i class="fas fa-arrow-down mr-2"></i>
                            Ver Produtos
                        </button>
                    </div>

                    <!-- Trust Signals -->
                    <div class="grid grid-cols-3 gap-6">
                        <div class="trust-badge p-4 rounded-lg backdrop-blur">
                            <div class="text-2xl font-bold gradient-text">500+</div>
                            <div class="text-xs text-slate-400 mt-1">Produtos</div>
                        </div>
                        <div class="trust-badge p-4 rounded-lg backdrop-blur">
                            <div class="text-2xl font-bold gradient-text">10K+</div>
                            <div class="text-xs text-slate-400 mt-1">Clientes</div>
                        </div>
                        <div class="trust-badge p-4 rounded-lg backdrop-blur">
                            <div class="text-2xl font-bold gradient-text">4.9★</div>
                            <div class="text-xs text-slate-400 mt-1">Avaliação</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div data-aos="fade-left" data-aos-duration="1000" class="relative h-96 md:h-full min-h-96">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-3xl blur-3xl"></div>
                    <div class="relative h-full bg-gradient-to-br from-purple-900/40 to-black rounded-3xl p-8 flex items-center justify-center text-white text-center float border border-purple-500/30">
                        <div>
                            <i class="fas fa-cloud-mist text-8xl mb-6 opacity-30"></i>
                            <h3 class="text-5xl font-black mb-4">PREMIUM</h3>
                            <p class="text-xl opacity-80 mb-2">Pods & Qualidade</p>
                            <p class="text-6xl mt-8">💨</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Banner -->
    <section class="py-8 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/30 to-transparent border-y border-purple-900/20">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-truck text-3xl text-green-400 mb-2"></i>
                    <p class="font-bold">Frete Grátis</p>
                    <p class="text-xs text-slate-400">Acima de R$ 100</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-shield-alt text-3xl text-blue-400 mb-2"></i>
                    <p class="font-bold">100% Seguro</p>
                    <p class="text-xs text-slate-400">Proteção total</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-redo text-3xl text-purple-400 mb-2"></i>
                    <p class="font-bold">Devolução</p>
                    <p class="text-xs text-slate-400">30 dias garantidos</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-headset text-3xl text-pink-400 mb-2"></i>
                    <p class="font-bold">Suporte 24/7</p>
                    <p class="text-xs text-slate-400">Sempre pronto</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias Section -->
    <section id="categorias" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    Escolha sua <span class="gradient-text">Categoria</span>
                </h2>
                <p class="text-xl text-slate-400">Pods descartáveis, recarregáveis e acessórios premium</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer border border-purple-500/20 hover:border-purple-500/50" data-aos="zoom-in" data-aos-delay="0">
                    <div class="text-6xl mb-4">📱</div>
                    <h3 class="text-2xl font-bold mb-2 text-slate-100 group-hover:text-purple-400 transition">Pods Descartáveis</h3>
                    <p class="text-slate-400 mb-4">Pronto para usar, máxima praticidade</p>
                    <p class="text-sm text-purple-400 font-bold">24+ Produtos</p>
                </div>
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer border border-purple-500/20 hover:border-purple-500/50" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-6xl mb-4">🔄</div>
                    <h3 class="text-2xl font-bold mb-2 text-slate-100 group-hover:text-purple-400 transition">Pods Recarregáveis</h3>
                    <p class="text-slate-400 mb-4">Sustentável e econômico</p>
                    <p class="text-sm text-purple-400 font-bold">18+ Produtos</p>
                </div>
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer border border-purple-500/20 hover:border-purple-500/50" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-6xl mb-4">🎁</div>
                    <h3 class="text-2xl font-bold mb-2 text-slate-100 group-hover:text-purple-400 transition">Acessórios</h3>
                    <p class="text-slate-400 mb-4">Tudo que você precisa</p>
                    <p class="text-sm text-purple-400 font-bold">31+ Produtos</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider max-w-7xl mx-auto"></div>

    <!-- Produtos Destaque -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="inline-block px-4 py-2 glass rounded-full text-sm font-bold gradient-text mb-4">
                    <i class="fas fa-fire"></i> Mais Vendidos
                </span>
                <h2 class="text-5xl font-black mb-4">
                    Produtos em <span class="gradient-text">Destaque</span>
                </h2>
                <p class="text-xl text-slate-400">Seleção premium de mais vendidos</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($produtos, 0, 8) as $i => $produto): ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group relative border border-purple-500/20 hover:border-purple-500/50" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <!-- Sale Badge -->
                    <div class="badge-sale">
                        -30%
                    </div>

                    <!-- Product Image -->
                    <div class="relative h-56 overflow-hidden bg-gradient-to-br from-purple-900/50 to-black">
                        <img src="<?php echo htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/400x300?text=' . urlencode($produto['nome'])); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://via.placeholder.com/400x300?text=Produto'">
                        <div class="absolute top-4 left-4 bg-purple-600/80 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold">
                            <?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Novo'); ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <div class="flex items-start gap-2 mb-3">
                            <i class="fas fa-cloud-mist text-purple-400 text-lg flex-shrink-0 mt-0.5"></i>
                            <h3 class="text-lg font-bold text-slate-100 line-clamp-2"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        </div>
                        <p class="text-slate-400 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($produto['descricao'] ?? '', 0, 80)); ?>...</p>
                        
                        <!-- Rating -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex gap-0.5">
                                <?php for($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-yellow-400 text-xs font-bold">5.0</span>
                        </div>

                        <!-- Price -->
                        <div class="mb-5 pb-5 border-b border-slate-700">
                            <span class="text-2xl font-black gradient-text">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <span class="text-slate-500 line-through ml-2 text-sm">R$ <?php echo number_format($produto['preco'] * 1.43, 2, ',', '.'); ?></span>
                        </div>

                        <button onclick="addToCart(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['preco']; ?>)" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 text-sm hover:shadow-lg transition">
                            <i class="fas fa-cart-plus mr-2"></i>
                            Adicionar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <button class="px-10 py-4 glass border border-purple-500/50 rounded-lg font-bold hover:bg-white/10 transition btn-hover text-lg">
                    <i class="fas fa-box mr-2"></i>
                    Ver Todos os Produtos
                </button>
            </div>
        </div>
    </section>

    <!-- Avaliações -->
    <section id="avaliacoes" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/20 to-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    O que nossos <span class="gradient-text">Clientes</span> dizem
                </h2>
                <div class="flex justify-center gap-1 mb-4">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-2xl"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-xl text-slate-400">4.9 ★ em mais de 2.500 avaliações verificadas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($avaliacoes as $i => $avaliacao): ?>
                <div class="glass rounded-2xl p-8 card-hover border border-purple-500/20 hover:border-purple-500/50" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="flex gap-1 mb-4">
                        <?php for($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star text-yellow-400 text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-slate-300 mb-6 italic text-lg">"<?php echo $avaliacao['texto']; ?>"</p>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-700">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-100"><?php echo $avaliacao['nome']; ?></p>
                            <p class="text-slate-400 text-sm"><i class="fas fa-check-circle text-green-400 mr-1"></i>Comprador Verificado</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter CTA Premium -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glass rounded-3xl p-12 md:p-16 text-center border-2 border-purple-600/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-full blur-3xl -z-0"></div>
                
                <div class="relative z-10">
                    <h2 class="text-5xl font-black mb-6 text-slate-100">
                        Ganhe <span class="gradient-text">20% OFF</span>
                    </h2>
                    <p class="text-xl text-slate-300 mb-10">
                        Inscreva-se na newsletter e receba 20% de desconto + acesso exclusivo a promoções!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                        <input type="email" placeholder="seu@email.com" class="flex-1 px-6 py-4 rounded-lg bg-slate-800/50 text-slate-100 border border-purple-600/50 focus:outline-none focus:ring-2 focus:ring-purple-400 placeholder-slate-500 text-base premium-input">
                        <button class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 whitespace-nowrap text-base hover:shadow-xl transition-all">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Inscrever
                        </button>
                    </div>
                    <p class="text-slate-400 text-sm mt-6">📧 Garantido: sem spam, apenas promoções!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Premium -->
    <footer class="bg-black text-slate-300 py-16 px-4 sm:px-6 lg:px-8 border-t border-purple-900/30">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div>
                    <div class="text-3xl font-bold mb-6 text-purple-400 flex items-center gap-2">
                        <i class="fas fa-cloud-mist"></i>
                        <span>Loja de Pods</span>
                    </div>
                    <p class="text-slate-400 mb-6 leading-relaxed">Sua loja premium de pods descartáveis, recarregáveis e acessórios de qualidade superior.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-xl"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-xl"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-xl"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Produtos -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-100">Produtos</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Pods Descartáveis</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Pods Recarregáveis</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Acessórios</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Ofertas Especiais</a></li>
                    </ul>
                </div>

                <!-- Suporte -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-100">Suporte</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">WhatsApp: (11) 9999-9999</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">FAQ & Dúvidas</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Rastrear Pedido</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Trocas e Devoluções</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-slate-100">Legal</h4>
                    <ul class="space-y-3 text-slate-400 text-sm">
                        <li><a href="#" class="hover:text-purple-400 transition">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Termos de Uso</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Aviso de Saúde</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Restrição de Idade: 18+</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-slate-400 text-center md:text-left">© 2024 Loja de Pods. Todos os direitos reservados. | ⚠️ Contém Nicotina</p>
                <div class="flex gap-6">
                    <i class="fab fa-cc-visa text-2xl text-slate-400 hover:text-purple-400 transition cursor-pointer"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-slate-400 hover:text-purple-400 transition cursor-pointer"></i>
                    <i class="fab fa-cc-paypal text-2xl text-slate-400 hover:text-purple-400 transition cursor-pointer"></i>
                    <i class="fab fa-bitcoin text-2xl text-slate-400 hover:text-purple-400 transition cursor-pointer"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scroll-top" class="scroll-top bg-gradient-to-r from-purple-600 to-pink-600 text-white p-4 rounded-full hover:shadow-lg transition">
        <i class="fas fa-arrow-up text-xl"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: false,
            mirror: true
        });

        // Dark Mode Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            html.classList.add('dark');
            document.body.classList.add('dark');
        }

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            document.body.classList.toggle('dark');
            
            const newTheme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
        });

        // Scroll to Top
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

        // Smooth Scroll for Navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // GSAP Animations
        gsap.registerPlugin();
        
        // Hero Title Animation
        gsap.from('h1', {
            duration: 1.2,
            y: 60,
            opacity: 0,
            ease: 'power3.out'
        });

        // Stagger animation on buttons
        gsap.from('button', {
            duration: 0.8,
            y: 30,
            opacity: 0,
            stagger: 0.2,
            ease: 'power2.out'
        });

        // Add to Cart
        function addToCart(id, nome, preco) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const item = { id, nome, preco, qty: 1 };
            cart.push(item);
            localStorage.setItem('cart', JSON.stringify(cart));
            alert('✅ ' + nome + ' adicionado ao carrinho!');
        }
    </script>

</body>
</html>