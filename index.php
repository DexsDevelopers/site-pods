<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();
session_start();

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

try {
    if (isset($db)) {
        $db = Database::getInstance();
        
        $stmt = $db->getConnection()->prepare("SELECT * FROM categories ORDER BY nome LIMIT 6");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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

$avaliacoes = [
    ['nome' => 'João Silva', 'texto' => 'Wazzy Vape oferece a melhor qualidade do mercado. Entrega rápida e excelente atendimento!', 'rating' => 5],
    ['nome' => 'Maria Santos', 'texto' => 'Adorei! Produtos premium com entrega no prazo. Voltarei com certeza!', 'rating' => 5],
    ['nome' => 'Pedro Costa', 'texto' => 'Melhor loja de pods que já comprei. Qualidade garantida em tudo!', 'rating' => 5],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wazzy Vape - Pods Premium</title>
    <meta name="description" content="Wazzy Vape - Loja premium de pods com qualidade garantida e entrega rápida.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
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
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            color: #1f2937;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Glassmorphism Moderno */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(124, 58, 237, 0.1);
            border-radius: 16px;
        }

        /* Gradiente de Texto */
        .gradient-text {
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
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
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.35);
        }

        /* Card Hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(124, 58, 237, 0.15);
        }

        /* Hero Gradient */
        .hero-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 50%, #f3f4f6 100%);
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
            background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, transparent 70%);
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
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
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
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);
            color: #7c3aed;
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        ::-webkit-scrollbar-thumb {
            background: #7c3aed;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6d28d9;
        }
    </style>
</head>
<body>

    <!-- Header Premium -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-white/95 shadow-sm border-b border-gray-100">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                        <i class="fas fa-skull-crossbones text-white text-lg"></i>
                    </div>
                    <div>
                        <div class="font-black text-xl gradient-text">WAZZY</div>
                        <div class="text-xs font-bold text-gray-500">VAPE</div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-gray-600 hover:gradient-text transition font-medium">Home</a>
                    <a href="#produtos" class="text-gray-600 hover:gradient-text transition font-medium">Produtos</a>
                    <a href="#categorias" class="text-gray-600 hover:gradient-text transition font-medium">Categorias</a>
                    <a href="#avaliacoes" class="text-gray-600 hover:gradient-text transition font-medium">Avaliações</a>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 hover:bg-gray-100 rounded-lg transition">
                        <i class="fas fa-shopping-cart text-purple-600 text-lg"></i>
                        <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">0</span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

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
                    
                    <h1 class="text-6xl md:text-7xl font-black mb-6 leading-tight text-gray-900">
                        Wazzy <span class="gradient-text">Vape</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-12 leading-relaxed">
                        Descubra a melhor seleção de pods premium com qualidade garantida, entrega rápida e atendimento excepcional.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <button class="px-8 py-4 btn-primary rounded-lg font-bold ripple relative z-10 text-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Comprar Agora
                        </button>
                        <button class="px-8 py-4 glass text-purple-600 rounded-lg font-bold ripple relative z-10 text-lg hover:bg-gray-50 transition">
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
                    <div class="relative h-full glass rounded-3xl p-8 flex items-center justify-center text-center border-2 border-purple-200">
                        <div>
                            <i class="fas fa-skull text-6xl gradient-text mb-4" style="display: block;"></i>
                            <h3 class="text-5xl font-black gradient-text mb-2">WAZZY</h3>
                            <p class="text-lg font-bold text-purple-600">Premium Vape Experience</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Banner -->
    <section class="py-8 px-4 sm:px-6 lg:px-8 bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-truck text-2xl gradient-text mb-2"></i>
                    <p class="font-bold text-gray-900">Frete Grátis</p>
                    <p class="text-xs text-gray-500">Acima de R$ 100</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-shield-alt text-2xl gradient-text mb-2"></i>
                    <p class="font-bold text-gray-900">100% Seguro</p>
                    <p class="text-xs text-gray-500">Proteção total</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-redo text-2xl gradient-text mb-2"></i>
                    <p class="font-bold text-gray-900">Devolução</p>
                    <p class="text-xs text-gray-500">30 dias garantidos</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-headset text-2xl gradient-text mb-2"></i>
                    <p class="font-bold text-gray-900">Suporte 24/7</p>
                    <p class="text-xs text-gray-500">Sempre pronto</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section id="categorias" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 text-gray-900">
                    Escolha sua <span class="gradient-text">Categoria</span>
                </h2>
                <p class="text-xl text-gray-600">Explore nossa seleção premium de pods</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-xl transition cursor-pointer border border-purple-100" data-aos="zoom-in">
                    <div class="text-5xl mb-4">📱</div>
                    <h3 class="text-2xl font-bold mb-2 text-gray-900">Pods Descartáveis</h3>
                    <p class="text-gray-600 mb-4">Pronto para usar, máxima praticidade</p>
                    <p class="text-sm font-bold gradient-text">24+ Produtos</p>
                </div>
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-xl transition cursor-pointer border border-purple-100" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-5xl mb-4">🔄</div>
                    <h3 class="text-2xl font-bold mb-2 text-gray-900">Pods Recarregáveis</h3>
                    <p class="text-gray-600 mb-4">Sustentável e econômico</p>
                    <p class="text-sm font-bold gradient-text">18+ Produtos</p>
                </div>
                <div class="group glass rounded-2xl p-8 text-center hover:shadow-xl transition cursor-pointer border border-purple-100" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-5xl mb-4">🎁</div>
                    <h3 class="text-2xl font-bold mb-2 text-gray-900">Acessórios</h3>
                    <p class="text-gray-600 mb-4">Tudo que você precisa</p>
                    <p class="text-sm font-bold gradient-text">31+ Produtos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Produtos -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="badge badge-primary mb-4">
                    <i class="fas fa-fire mr-2"></i> Mais Vendidos
                </span>
                <h2 class="text-5xl font-black mb-4 text-gray-900">
                    Produtos em <span class="gradient-text">Destaque</span>
                </h2>
                <p class="text-xl text-gray-600">Seleção premium dos melhores produtos</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($produtos, 0, 8) as $i => $produto): ?>
                <div class="glass rounded-2xl overflow-hidden card-hover group relative border border-purple-100" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="absolute top-4 right-4 badge badge-primary z-10">
                        -30%
                    </div>

                    <div class="relative h-56 overflow-hidden bg-gray-100">
                        <img src="<?php echo htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/400x300?text=' . urlencode($produto['nome'])); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://via.placeholder.com/400x300?text=Produto'">
                        <div class="absolute top-4 left-4 badge badge-primary">
                            <?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Novo'); ?>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-2 mb-3"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($produto['descricao'] ?? '', 0, 80)); ?>...</p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex gap-0.5">
                                <?php for($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-yellow-600 text-xs font-bold">5.0</span>
                        </div>

                        <div class="mb-5 pb-5 border-b border-gray-200">
                            <span class="text-2xl font-black gradient-text">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <span class="text-gray-400 line-through ml-2 text-sm">R$ <?php echo number_format($produto['preco'] * 1.43, 2, ',', '.'); ?></span>
                        </div>

                        <button onclick="addToCart(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['preco']; ?>)" class="w-full py-3 btn-primary rounded-lg font-bold text-sm transition">
                            <i class="fas fa-cart-plus mr-2"></i>
                            Adicionar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <button class="px-10 py-4 glass rounded-lg font-bold text-lg gradient-text hover:shadow-lg transition border border-purple-100">
                    <i class="fas fa-box mr-2"></i>
                    Ver Todos os Produtos
                </button>
            </div>
        </div>
    </section>

    <!-- Avaliações -->
    <section id="avaliacoes" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 text-gray-900">
                    O que nossos <span class="gradient-text">Clientes</span> dizem
                </h2>
                <div class="flex justify-center gap-1 mb-4">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-2xl"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-xl text-gray-600">5.0 ★ em mais de 2.500 avaliações verificadas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($avaliacoes as $i => $avaliacao): ?>
                <div class="glass rounded-2xl p-8 card-hover border border-purple-100" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="flex gap-1 mb-4">
                        <?php for($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star text-yellow-400 text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 mb-6 italic text-lg">"<?php echo $avaliacao['texto']; ?>"</p>
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900"><?php echo $avaliacao['nome']; ?></p>
                            <p class="text-gray-500 text-sm"><i class="fas fa-check-circle text-green-500 mr-1"></i>Comprador Verificado</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glass rounded-3xl p-12 md:p-16 text-center border-2 border-purple-200 relative">
                <h2 class="text-5xl font-black mb-6 text-gray-900">
                    Ganhe <span class="gradient-text">20% OFF</span>
                </h2>
                <p class="text-xl text-gray-600 mb-10">
                    Inscreva-se na newsletter e receba 20% de desconto + acesso exclusivo a promoções!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                    <input type="email" placeholder="seu@email.com" class="flex-1 px-6 py-4 rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-600 placeholder-gray-500 text-base">
                    <button class="px-8 py-4 btn-primary rounded-lg font-bold whitespace-nowrap text-base">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Inscrever
                    </button>
                </div>
                <p class="text-gray-500 text-sm mt-6">📧 Sem spam, apenas promoções!</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-16 px-4 sm:px-6 lg:px-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div>
                    <div class="text-2xl font-black mb-6 gradient-text flex items-center gap-2">
                        <i class="fas fa-skull-crossbones"></i>
                        <span>WAZZY VAPE</span>
                    </div>
                    <p class="text-gray-400 mb-6 leading-relaxed">Sua loja premium de pods com qualidade garantida e atendimento excepcional.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:gradient-text transition text-lg"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:gradient-text transition text-lg"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:gradient-text transition text-lg"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="text-gray-400 hover:gradient-text transition text-lg"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Produtos</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:gradient-text transition">Pods Descartáveis</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Pods Recarregáveis</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Acessórios</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Ofertas Especiais</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Suporte</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:gradient-text transition">WhatsApp: (11) 9999-9999</a></li>
                        <li><a href="#" class="hover:gradient-text transition">FAQ & Dúvidas</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Rastrear Pedido</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Trocas e Devoluções</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Legal</h4>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="#" class="hover:gradient-text transition">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Termos de Uso</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Aviso de Saúde</a></li>
                        <li><a href="#" class="hover:gradient-text transition">Restrição de Idade: 18+</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-gray-400 text-center md:text-left">© 2024 Wazzy Vape. Todos os direitos reservados. | ⚠️ Contém Nicotina</p>
                <div class="flex gap-6">
                    <i class="fab fa-cc-visa text-2xl text-gray-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-gray-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-cc-paypal text-2xl text-gray-400 hover:gradient-text transition cursor-pointer"></i>
                    <i class="fab fa-bitcoin text-2xl text-gray-400 hover:gradient-text transition cursor-pointer"></i>
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