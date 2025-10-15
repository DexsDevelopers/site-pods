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
        'tag' => 'Bestseller'
    ],
    [
        'id' => 2,
        'nome' => 'Aero Compact 2024',
        'descricao' => 'Portátil e de alta performance',
        'preco' => 'R$ 199,90',
        'icone' => 'box',
        'imagem' => 'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=400&h=300&fit=crop',
        'tag' => 'Novo'
    ],
    [
        'id' => 3,
        'nome' => 'Pro Max Series',
        'descricao' => 'Profissional com vapor intenso',
        'preco' => 'R$ 449,90',
        'icone' => 'flame',
        'imagem' => 'https://images.unsplash.com/photo-1617638924702-92d37d439220?w=400&h=300&fit=crop',
        'tag' => 'Premium'
    ],
    [
        'id' => 4,
        'nome' => 'Caso Ultra Slim',
        'descricao' => 'Proteção elegante e minimalista',
        'preco' => 'R$ 89,90',
        'icone' => 'shield',
        'imagem' => 'https://images.unsplash.com/photo-1582719471384-894fbb16e074?w=400&h=300&fit=crop',
        'tag' => 'Acessório'
    ],
    [
        'id' => 5,
        'nome' => 'Kit Limpeza Premium',
        'descricao' => 'Manutenção profissional completa',
        'preco' => 'R$ 49,90',
        'icone' => 'tool',
        'imagem' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=300&fit=crop',
        'tag' => 'Essencial'
    ],
    [
        'id' => 6,
        'nome' => 'Bateria 21700 5000mAh',
        'descricao' => 'Alta capacidade e performance',
        'preco' => 'R$ 79,90',
        'icone' => 'battery',
        'imagem' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=400&h=300&fit=crop',
        'tag' => 'Power'
    ]
];

$categorias = [
    ['nome' => 'Vaporizadores', 'icon' => 'zap', 'count' => 15],
    ['nome' => 'Acessórios', 'icon' => 'box', 'count' => 32],
    ['nome' => 'Líquidos', 'icon' => 'droplets', 'count' => 48],
    ['nome' => 'Baterias', 'icon' => 'battery', 'count' => 12],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechVapor - Vaporizadores & Acessórios Premium</title>
    
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
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 to-black text-slate-100">

    <!-- Header/Navbar -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-black/80 shadow-lg">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="gradient-text text-3xl font-bold">
                        <i class="fas fa-bolt"></i> TechVapor
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-slate-300 hover:text-purple-400 transition">Home</a>
                    <a href="#produtos" class="text-slate-300 hover:text-purple-400 transition">Produtos</a>
                    <a href="#categorias" class="text-slate-300 hover:text-purple-400 transition">Categorias</a>
                    <a href="#contato" class="text-slate-300 hover:text-purple-400 transition">Contato</a>
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
                        <span class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center badge-pulse">3</span>
                    </button>

                    <!-- Menu Hamburger -->
                    <button id="menu-toggle" class="md:hidden p-2 rounded-lg glass hover:bg-white/10 transition">
                        <i class="fas fa-bars text-purple-400"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto w-full">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div data-aos="fade-right" data-aos-duration="800">
                    <div class="mb-6">
                        <span class="inline-block px-4 py-2 glass rounded-full text-sm font-semibold gradient-text mb-6">
                            <i class="fas fa-star"></i> Bem-vindo ao TechVapor
                        </span>
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        A Revolução do <span class="gradient-text">Vapor</span> Começa Aqui
                    </h1>
                    
                    <p class="text-xl text-slate-300 mb-8 leading-relaxed">
                        Descubra a melhor seleção de vaporizadores e acessórios premium. Tecnologia de ponta, design elegante e performance superior em um só lugar.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold btn-hover ripple relative z-10 hover:shadow-xl">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Explorar Loja
                        </button>
                        <button class="px-8 py-4 glass text-white rounded-lg font-semibold btn-hover ripple relative z-10">
                            <i class="fas fa-play mr-2"></i>
                            Ver Demo
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="flex gap-8 mt-12">
                        <div class="text-center">
                            <div class="text-3xl font-bold gradient-text">1000+</div>
                            <div class="text-sm text-slate-400">Produtos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold gradient-text">5000+</div>
                            <div class="text-sm text-slate-400">Clientes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold gradient-text">24/7</div>
                            <div class="text-sm text-slate-400">Suporte</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div data-aos="fade-left" data-aos-duration="800" class="relative h-96 md:h-full">
                    <div class="gradient-animated rounded-3xl p-8 h-full flex items-center justify-center text-white text-center float">
                        <div>
                            <i class="fas fa-cube text-8xl mb-4 opacity-20"></i>
                            <h3 class="text-3xl font-bold">Premium Vapor</h3>
                            <p class="mt-4 opacity-80">Tecnologia & Design Inovador</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias Section -->
    <section id="categorias" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Explore Nossas <span class="gradient-text">Categorias</span>
                </h2>
                <p class="text-lg text-slate-400">Tudo que você precisa em um só lugar</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($categorias as $i => $cat): ?>
                <div class="neomorphic p-8 text-center hover:shadow-2xl transition cursor-pointer group" data-aos="zoom-in" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="text-5xl mb-4 group-hover:scale-110 transition text-purple-400">
                        <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-slate-100"><?php echo $cat['nome']; ?></h3>
                    <p class="text-slate-400 mb-4"><?php echo $cat['count']; ?> produtos</p>
                    <button class="w-full py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold btn-hover text-sm">
                        Ver Mais <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Produtos Section -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Produtos <span class="gradient-text">Destaque</span>
                </h2>
                <p class="text-lg text-slate-400">Os mais vendidos e procurados</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($produtos as $i => $produto): ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <!-- Product Image -->
                    <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-900 to-black">
                        <img src="<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 right-4 bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold badge-pulse">
                            <?php echo $produto['tag']; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-<?php echo $produto['icone']; ?> text-purple-400 mr-2"></i>
                            <h3 class="text-xl font-bold text-slate-100"><?php echo $produto['nome']; ?></h3>
                        </div>
                        <p class="text-slate-400 text-sm mb-4"><?php echo $produto['descricao']; ?></p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold gradient-text"><?php echo $produto['preco']; ?></span>
                            <div class="flex gap-1">
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star-half text-yellow-400"></i>
                            </div>
                        </div>

                        <button class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold btn-hover ripple relative z-10">
                            <i class="fas fa-cart-plus mr-2"></i>
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <button class="px-8 py-4 glass rounded-lg font-semibold btn-hover ripple relative z-10 text-slate-100">
                    <i class="fas fa-box mr-2"></i>
                    Ver Todos os Produtos
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/20 to-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Por Que <span class="gradient-text">Nos Escolher?</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="neomorphic p-8 text-center" data-aos="zoom-in">
                    <div class="text-5xl mb-4 inline-block p-4 bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-full">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-100">Entrega Rápida</h3>
                    <p class="text-slate-400">Enviamos seus produtos em 24h com rastreamento completo</p>
                </div>

                <div class="neomorphic p-8 text-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-5xl mb-4 inline-block p-4 bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-full">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-100">100% Seguro</h3>
                    <p class="text-slate-400">Pagamentos criptografados com SSL de confiança</p>
                </div>

                <div class="neomorphic p-8 text-center" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-5xl mb-4 inline-block p-4 bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-full">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-slate-100">Suporte 24/7</h3>
                    <p class="text-slate-400">Nossa equipe está sempre pronta para ajudar você</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glass rounded-3xl p-12 md:p-16 text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-slate-100">
                    Pronto para Começar?
                </h2>
                <p class="text-xl text-slate-300 mb-8">
                    Cadastre-se agora e receba 10% de desconto na primeira compra!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <input type="email" placeholder="Seu e-mail" class="px-6 py-3 rounded-lg bg-slate-800 text-slate-100 border border-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-400 w-full sm:w-auto placeholder-slate-500">
                    <button class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold btn-hover ripple relative z-10 whitespace-nowrap">
                        <i class="fas fa-rocket mr-2"></i>
                        Começar Agora
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
                    <div class="text-2xl font-bold mb-4 text-purple-400">
                        <i class="fas fa-bolt mr-2"></i>TechVapor
                    </div>
                    <p class="text-slate-400">A melhor plataforma para vaporizadores e acessórios premium.</p>
                    <div class="flex gap-4 mt-6">
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:text-purple-400 transition"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <!-- Products -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Produtos</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Vaporizadores</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Acessórios</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Líquidos</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Promoções</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Suporte</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Contato</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Envios</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Devoluções</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-slate-100">Legal</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Privacidade</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Termos</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Cookies</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Sitemap</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-slate-400">© 2024 TechVapor. Todos os direitos reservados.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <i class="fab fa-cc-visa text-2xl text-slate-400"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-slate-400"></i>
                    <i class="fab fa-cc-paypal text-2xl text-slate-400"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scroll-top" class="scroll-top bg-gradient-to-r from-purple-600 to-pink-600 text-white p-4 rounded-full hover:shadow-lg transition">
        <i class="fas fa-arrow-up"></i>
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

        // Check for saved theme preference or default to light
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

        // Mobile Menu Toggle (if needed)
        const menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                // Add mobile menu functionality here
            });
        }

        // Add ripple effect on click
        document.querySelectorAll('.ripple').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripples = this.querySelectorAll('::after');
            });
        });
    </script>

</body>
</html>