<?php
// Inclui configurações
require_once 'includes/config.php';
require_once 'includes/db.php';

// Simulação de dados de produtos (em um projeto real, viria do BD)
$produtos = [
    [
        'id' => 1,
        'nome' => 'Vapor Premium X-01',
        'descricao' => 'Design elegante com tecnologia de ponta',
        'preco' => 'R$ 299,90',
        'icone' => 'zap',
        'imagem' => 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=400&h=300&fit=crop',
        'tag' => 'Bestseller',
        'avaliacao' => 4.8
    ],
    [
        'id' => 2,
        'nome' => 'Aero Compact 2024',
        'descricao' => 'Portátil e de alta performance',
        'preco' => 'R$ 199,90',
        'icone' => 'box',
        'imagem' => 'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=400&h=300&fit=crop',
        'tag' => 'Novo',
        'avaliacao' => 4.9
    ],
    [
        'id' => 3,
        'nome' => 'Pro Max Series',
        'descricao' => 'Profissional com vapor intenso',
        'preco' => 'R$ 449,90',
        'icone' => 'flame',
        'imagem' => 'https://images.unsplash.com/photo-1617638924702-92d37d439220?w=400&h=300&fit=crop',
        'tag' => 'Premium',
        'avaliacao' => 5
    ],
    [
        'id' => 4,
        'nome' => 'Caso Ultra Slim',
        'descricao' => 'Proteção elegante e minimalista',
        'preco' => 'R$ 89,90',
        'icone' => 'shield',
        'imagem' => 'https://images.unsplash.com/photo-1582719471384-894fbb16e074?w=400&h=300&fit=crop',
        'tag' => 'Acessório',
        'avaliacao' => 4.7
    ],
    [
        'id' => 5,
        'nome' => 'Kit Limpeza Premium',
        'descricao' => 'Manutenção profissional completa',
        'preco' => 'R$ 49,90',
        'icone' => 'tool',
        'imagem' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=300&fit=crop',
        'tag' => 'Essencial',
        'avaliacao' => 4.6
    ],
    [
        'id' => 6,
        'nome' => 'Bateria 21700 5000mAh',
        'descricao' => 'Alta capacidade e performance',
        'preco' => 'R$ 79,90',
        'icone' => 'battery',
        'imagem' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=400&h=300&fit=crop',
        'tag' => 'Power',
        'avaliacao' => 4.8
    ]
];

$categorias = [
    ['nome' => 'Vaporizadores', 'icon' => 'zap', 'count' => 15],
    ['nome' => 'Acessórios', 'icon' => 'box', 'count' => 32],
    ['nome' => 'Líquidos', 'icon' => 'droplets', 'count' => 48],
    ['nome' => 'Baterias', 'icon' => 'battery', 'count' => 12],
];

$sabores = [
    ['nome' => 'Frutas Tropicais', 'emoji' => '🥭', 'produtos' => 24],
    ['nome' => 'Mint Gelado', 'emoji' => '❄️', 'produtos' => 18],
    ['nome' => 'Doces', 'emoji' => '🍰', 'produtos' => 31],
    ['nome' => 'Bebidas', 'emoji' => '🍹', 'produtos' => 22],
];

$avaliacoes = [
    ['nome' => 'João Silva', 'texto' => 'Melhor loja de vaper que conheço! Produtos de qualidade e entrega rápida!', 'rating' => 5],
    ['nome' => 'Maria Santos', 'texto' => 'Adorei o atendimento, muito atencioso e eficiente!', 'rating' => 5],
    ['nome' => 'Pedro Costa', 'texto' => 'Qualidade excepcional, voltarei com certeza!', 'rating' => 4.5],
];

$blog = [
    ['titulo' => 'Guia Completo: Como Escolher seu Primeiro Vaper', 'data' => '15 de Outubro', 'img' => 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=300&h=200&fit=crop'],
    ['titulo' => '5 Sabores Imprescindíveis para Iniciantes', 'data' => '12 de Outubro', 'img' => 'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=300&h=200&fit=crop'],
    ['titulo' => 'Manutenção Correta do seu Dispositivo', 'data' => '10 de Outubro', 'img' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=200&fit=crop'],
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
    <title>TechVapor - Loja Premium de Vaporizadores e Acessórios</title>
    
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
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Glassmorphism */
        .glass {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(147, 51, 234, 0.2);
            border-radius: 16px;
        }

        body.dark .glass {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(147, 51, 234, 0.3);
        }

        /* Neumorphism - Soft UI */
        .neomorphic {
            background: linear-gradient(145deg, #1a1a2e, #16213e);
            box-shadow: 8px 8px 16px #0a0a0a, -8px -8px 16px #2a2a3e;
            border-radius: 20px;
            border: none;
        }

        body.dark .neomorphic {
            background: linear-gradient(145deg, #0f0f1e, #1a1a2e);
            box-shadow: 8px 8px 16px #000000, -8px -8px 16px #242438;
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
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient Animated */
        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .gradient-animated {
            background: linear-gradient(-45deg, #000000, #9333ea, #c084fc, #000000);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }

        /* Card Hover Effect */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(147, 51, 234, 0.3);
        }

        /* Ripple Effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .ripple:active::after {
            width: 300px;
            height: 300px;
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

        /* Badge Animation */
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
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

        /* Age Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            border: 2px solid #9333ea;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Flash Sale Banner */
        .flash-banner {
            background: linear-gradient(135deg, #ff1744, #d32f2f);
            position: relative;
            overflow: hidden;
        }

        .flash-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Pulse effect for sale items */
        .pulse-sale {
            animation: pulseSale 1s ease-in-out infinite;
        }

        @keyframes pulseSale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Live chat bubble */
        .chat-bubble {
            position: fixed;
            bottom: 80px;
            right: 30px;
            z-index: 98;
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 to-black text-slate-100">

    <!-- Age Verification Modal -->
    <div id="ageModal" class="modal show">
        <div class="modal-content">
            <div class="text-5xl mb-4">⚠️</div>
            <h2 class="text-2xl font-bold mb-4 text-slate-100">Verificação de Idade</h2>
            <p class="text-slate-300 mb-6">Você deve ter 18 anos ou mais para acessar este site. Este site contém produtos que podem conter nicotina.</p>
            <div class="flex gap-4">
                <button onclick="rejectAge()" class="flex-1 px-4 py-3 bg-slate-600 text-white rounded-lg font-semibold hover:bg-slate-700 transition">
                    ❌ Sair
                </button>
                <button onclick="confirmAge()" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold btn-hover">
                    ✅ Confirmo (18+)
                </button>
            </div>
        </div>
    </div>

    <!-- Header/Navbar -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-black/80 shadow-lg">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="gradient-text text-3xl font-bold">
                        <i class="fas fa-cloud"></i> TechVapor
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-slate-300 hover:text-purple-400 transition">Home</a>
                    <a href="#produtos" class="text-slate-300 hover:text-purple-400 transition">Produtos</a>
                    <a href="#sabores" class="text-slate-300 hover:text-purple-400 transition">Sabores</a>
                    <a href="#blog" class="text-slate-300 hover:text-purple-400 transition">Blog</a>
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

    <!-- FLASH SALE BANNER -->
    <div class="flash-banner py-6 mt-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3">
                <i class="fas fa-fire text-white text-2xl pulse-sale"></i>
                <h2 class="text-2xl font-bold text-white">OFERTA IMPERDÍVEL!</h2>
                <i class="fas fa-fire text-white text-2xl pulse-sale"></i>
            </div>
            <p class="text-white/90 mt-2">🎉 Até 40% OFF em produtos selecionados - Apenas HOJE!</p>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="pt-40 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto w-full">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div data-aos="fade-right" data-aos-duration="800">
                    <div class="mb-6">
                        <span class="inline-block px-4 py-2 glass rounded-full text-sm font-semibold gradient-text mb-6">
                            <i class="fas fa-star"></i> Bem-vindo ao TechVapor Premium
                        </span>
                    </div>
                    
                    <h1 class="text-6xl md:text-7xl font-black mb-6 leading-tight">
                        Vapor de <span class="gradient-text">Qualidade</span> Premium
                    </h1>
                    
                    <p class="text-xl text-slate-300 mb-8 leading-relaxed">
                        Descubra a melhor seleção de vaporizadores, acessórios e líquidos premium. Qualidade garantida, entrega rápida e atendimento impecável.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 hover:shadow-xl text-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Comprar Agora
                        </button>
                        <button class="px-8 py-4 glass text-white rounded-lg font-bold btn-hover ripple relative z-10 text-lg">
                            <i class="fas fa-play mr-2"></i>
                            Ver Catálogo
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="flex gap-12 mt-12">
                        <div>
                            <div class="text-4xl font-black gradient-text">500+</div>
                            <div class="text-slate-400">Produtos</div>
                        </div>
                        <div>
                            <div class="text-4xl font-black gradient-text">10K+</div>
                            <div class="text-slate-400">Clientes</div>
                        </div>
                        <div>
                            <div class="text-4xl font-black gradient-text">24/7</div>
                            <div class="text-slate-400">Suporte</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div data-aos="fade-left" data-aos-duration="800" class="relative h-96 md:h-full">
                    <div class="gradient-animated rounded-3xl p-8 h-full flex items-center justify-center text-white text-center float">
                        <div>
                            <i class="fas fa-cloud text-8xl mb-4 opacity-20"></i>
                            <h3 class="text-4xl font-black">VAPOR</h3>
                            <p class="mt-4 opacity-80 text-lg">Premium & Qualidade</p>
                            <p class="text-5xl mt-6">💨</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sabores Section -->
    <section id="sabores" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    Explore nossos <span class="gradient-text">Sabores</span>
                </h2>
                <p class="text-xl text-slate-400">Mais de 150 sabores incríveis para escolher</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($sabores as $i => $sabor): ?>
                <div class="neomorphic p-8 text-center hover:shadow-2xl transition cursor-pointer group" data-aos="zoom-in" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="text-7xl mb-4 group-hover:scale-125 transition">
                        <?php echo $sabor['emoji']; ?>
                    </div>
                    <h3 class="text-2xl font-bold mb-2 text-slate-100"><?php echo $sabor['nome']; ?></h3>
                    <p class="text-slate-400 mb-4 text-lg"><?php echo $sabor['produtos']; ?> produtos</p>
                    <button class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover text-base">
                        Explorar <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Produtos Destaque -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    Nossos <span class="gradient-text">Destaques</span>
                </h2>
                <p class="text-xl text-slate-400">Produtos mais vendidos e bem avaliados</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($produtos as $i => $produto): ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group relative" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <!-- Sale Badge -->
                    <div class="absolute top-4 right-4 bg-red-600 text-white px-3 py-2 rounded-full text-sm font-bold badge-pulse z-10">
                        -30%
                    </div>

                    <!-- Product Image -->
                    <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-900 to-black">
                        <img src="<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 left-4 bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                            <?php echo $produto['tag']; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-<?php echo $produto['icone']; ?> text-purple-400 mr-2 text-xl"></i>
                            <h3 class="text-xl font-bold text-slate-100"><?php echo $produto['nome']; ?></h3>
                        </div>
                        <p class="text-slate-400 text-sm mb-4"><?php echo $produto['descricao']; ?></p>
                        
                        <!-- Rating -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex gap-1">
                                <?php for($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star <?php echo $j < $produto['avaliacao'] ? 'text-yellow-400' : 'text-slate-600'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-yellow-400 font-bold"><?php echo $produto['avaliacao']; ?></span>
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <span class="text-2xl font-black gradient-text"><?php echo $produto['preco']; ?></span>
                            <span class="text-slate-500 line-through ml-2">R$ <?php echo intval(str_replace(['R$', ',90', '.'], '', $produto['preco']) * 1.43); ?>,90</span>
                        </div>

                        <button class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10">
                            <i class="fas fa-cart-plus mr-2"></i>
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    <span class="gradient-text">Blog</span> & Dicas
                </h2>
                <p class="text-xl text-slate-400">Aprenda mais sobre vapor e lifestyle</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($blog as $i => $post): ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group cursor-pointer" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="h-48 overflow-hidden bg-gradient-to-br from-purple-900 to-black">
                        <img src="<?php echo $post['img']; ?>" alt="" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6">
                        <p class="text-purple-400 text-sm font-bold mb-2">
                            <i class="fas fa-calendar mr-2"></i><?php echo $post['data']; ?>
                        </p>
                        <h3 class="text-lg font-bold text-slate-100 group-hover:text-purple-400 transition"><?php echo $post['titulo']; ?></h3>
                        <button class="mt-4 text-purple-400 font-bold group-hover:text-pink-600 transition">
                            Leia mais <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Marcas Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    Marcas <span class="gradient-text">Premium</span>
                </h2>
                <p class="text-xl text-slate-400">Trabalhamos com as melhores marcas do mundo</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <?php foreach ($marcas as $i => $marca): ?>
                <div class="neomorphic p-8 text-center hover:shadow-2xl transition group cursor-pointer" data-aos="zoom-in" data-aos-delay="<?php echo $i * 50; ?>">
                    <div class="text-5xl font-black gradient-text group-hover:scale-125 transition mb-3">
                        <?php echo $marca['logo']; ?>
                    </div>
                    <p class="text-slate-300 font-bold"><?php echo $marca['nome']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Avaliações -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/20 to-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4">
                    O que nossos <span class="gradient-text">Clientes</span> dizem
                </h2>
                <p class="text-xl text-slate-400">4.9 ★ em mais de 1000 avaliações</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($avaliacoes as $i => $avaliacao): ?>
                <div class="glass rounded-2xl p-8 card-hover" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="flex gap-1 mb-4">
                        <?php for($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star text-yellow-400 text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-slate-300 mb-4 italic">"<?php echo $avaliacao['texto']; ?>"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-100"><?php echo $avaliacao['nome']; ?></p>
                            <p class="text-slate-400 text-sm">Cliente verificado</p>
                        </div>
                    </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter CTA -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glass rounded-3xl p-12 md:p-16 text-center border-2 border-purple-600">
                <h2 class="text-5xl font-black mb-6 text-slate-100">
                    Ganhe <span class="gradient-text">15% de Desconto</span>!
                </h2>
                <p class="text-xl text-slate-300 mb-8">
                    Inscreva-se na nossa newsletter e receba 15% OFF + dicas exclusivas!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <input type="email" placeholder="seu@email.com" class="px-6 py-4 rounded-lg bg-slate-800 text-slate-100 border border-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-400 w-full sm:w-auto placeholder-slate-500 text-base">
                    <button class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold btn-hover ripple relative z-10 whitespace-nowrap text-base">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Inscrever
                    </button>
                </div>
            </div>
            </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-slate-300 py-16 px-4 sm:px-6 lg:px-8 border-t border-purple-900/30">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div>
                    <div class="text-3xl font-bold mb-4 text-purple-400">
                        <i class="fas fa-cloud mr-2"></i>TechVapor
                    </div>
                    <p class="text-slate-400 mb-4">Sua loja premium de vaporizadores e acessórios de qualidade.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-lg"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-lg"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition text-lg"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Produtos -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Produtos</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Vaporizadores</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Acessórios</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Líquidos</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Black Friday</a></li>
                    </ul>
                </div>

                <!-- Suporte -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Suporte</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">WhatsApp</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Envios</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Devoluções</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Legal</h4>
                    <ul class="space-y-2 text-slate-400 text-sm">
                        <li><a href="#" class="hover:text-purple-400 transition">Privacidade</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Termos de Uso</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Aviso de Saúde</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">18+ Years Only</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-slate-400">© 2024 TechVapor. Todos os direitos reservados. | ⚠️ Contém Nicotina</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <i class="fab fa-cc-visa text-2xl text-slate-400"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-slate-400"></i>
                    <i class="fab fa-cc-paypal text-2xl text-slate-400"></i>
                    <i class="fab fa-bitcoin text-2xl text-slate-400"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Live Chat Widget -->
    <div class="chat-bubble">
        <button class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-4 rounded-full hover:shadow-lg transition shadow-lg">
            <i class="fas fa-comments text-2xl"></i>
        </button>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scroll-top" class="scroll-top bg-gradient-to-r from-purple-600 to-pink-600 text-white p-4 rounded-full hover:shadow-lg transition">
        <i class="fas fa-arrow-up text-xl"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        // Age Verification
        function confirmAge() {
            localStorage.setItem('ageVerified', 'true');
            document.getElementById('ageModal').classList.remove('show');
        }

        function rejectAge() {
            window.location.href = 'https://www.google.com';
        }

        // Check age verification on load
        window.addEventListener('load', function() {
            if (!localStorage.getItem('ageVerified')) {
                document.getElementById('ageModal').classList.add('show');
            } else {
                document.getElementById('ageModal').classList.remove('show');
            }
        });

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
            duration: 1,
            y: 50,
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
    </script>

</body>
</html>