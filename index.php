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

$sabores = [
    ['nome' => 'Frutas Tropicais', 'emoji' => '🥭', 'produtos' => 24],
    ['nome' => 'Mint Gelado', 'emoji' => '❄️', 'produtos' => 18],
    ['nome' => 'Doces', 'emoji' => '🍰', 'produtos' => 31],
    ['nome' => 'Bebidas', 'emoji' => '🍹', 'produtos' => 22],
];

$avaliacoes = [
    ['nome' => 'João Silva', 'texto' => 'Wazzy Vape é incrível! Pods de qualidade e design neon futurista!', 'rating' => 5],
    ['nome' => 'Maria Santos', 'texto' => 'Adorei! Entrega rápida e atendimento excelente. Recomendo muito!', 'rating' => 5],
    ['nome' => 'Pedro Costa', 'texto' => 'Melhor marca que conheço. Voltarei sempre!', 'rating' => 5],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wazzy Vape - Pods Neon Premium</title>
    <meta name="description" content="Wazzy Vape - Pods neon premium com design cyberpunk. Qualidade garantida, entrega rápida.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <style>
        :root {
            --primary: #ff00ff;
            --primary-dark: #cc00cc;
            --secondary: #1a0033;
            --accent: #00ffff;
            --neon-purple: #b000ff;
            --neon-magenta: #ff0080;
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(135deg, #0a0015 0%, #1a0033 50%, #0f0020 100%);
            color: #e0e7ff;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 0, 128, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(176, 0, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Neon Glow Classes */
        .neon-glow {
            text-shadow: 0 0 10px #ff00ff, 0 0 20px #ff00ff, 0 0 30px #ff00ff, 0 0 40px #ff00ff;
            color: #ff00ff;
        }

        .neon-glow-cyan {
            text-shadow: 0 0 10px #00ffff, 0 0 20px #00ffff, 0 0 30px #00ffff;
            color: #00ffff;
        }

        .glow-box {
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.3), inset 0 0 20px rgba(255, 0, 255, 0.1);
            border: 2px solid #ff00ff;
            background: rgba(255, 0, 255, 0.05);
        }

        .glow-box-cyan {
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3), inset 0 0 20px rgba(0, 255, 255, 0.1);
            border: 2px solid #00ffff;
            background: rgba(0, 255, 255, 0.05);
        }

        .glass {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 255, 0.2);
            border-radius: 16px;
        }

        .gradient-neon {
            background: linear-gradient(135deg, #ff00ff 0%, #00ffff 50%, #b000ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-neon {
            background: linear-gradient(135deg, #ff00ff, #b000ff);
            color: white;
            border: 2px solid #ff00ff;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .btn-neon:hover {
            box-shadow: 0 0 40px rgba(255, 0, 255, 0.8), inset 0 0 20px rgba(255, 0, 255, 0.2);
            transform: translateY(-3px);
        }

        .card-hover {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.5);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0a0015 0%, #1a0033 50%, #0f0020 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 0, 255, 0.1) 0%, transparent 70%);
            animation: float 20s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(50px, 50px); }
        }

        @keyframes neon-pulse {
            0%, 100% { 
                text-shadow: 0 0 10px #ff00ff, 0 0 20px #ff00ff;
            }
            50% { 
                text-shadow: 0 0 20px #ff00ff, 0 0 40px #ff00ff, 0 0 60px #ff00ff;
            }
        }

        .neon-pulse {
            animation: neon-pulse 2s ease-in-out infinite;
        }

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

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.4);
        }

        ::-webkit-scrollbar-thumb {
            background: #ff00ff;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #00ffff;
        }
    </style>
</head>
<body class="relative z-10">

    <!-- Header Premium Neon -->
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-black/80 shadow-lg border-b border-purple-900/50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="neon-glow text-4xl font-black">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <div>
                        <div class="neon-glow text-3xl font-black">WAZZY</div>
                        <div class="neon-glow-cyan text-xs font-bold">VAPE</div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-slate-300 hover:neon-glow transition font-bold">HOME</a>
                    <a href="#produtos" class="text-slate-300 hover:neon-glow transition font-bold">PODS</a>
                    <a href="#categorias" class="text-slate-300 hover:neon-glow transition font-bold">CATEGORIAS</a>
                    <a href="#avaliacoes" class="text-slate-300 hover:neon-glow transition font-bold">AVALIAÇÕES</a>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 glass hover:bg-white/10 transition">
                        <i class="fas fa-shopping-cart neon-glow"></i>
                        <span class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center neon-pulse">0</span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section Neon -->
    <section id="home" class="hero-gradient pt-32 pb-24 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-7xl mx-auto w-full relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="mb-8">
                        <span class="inline-block px-5 py-2 glow-box rounded-full text-sm font-bold neon-glow mb-6">
                            <i class="fas fa-bolt"></i> NEON PREMIUM
                        </span>
                    </div>
                    
                    <h1 class="text-8xl md:text-9xl font-black mb-6 leading-tight neon-glow neon-pulse">
                        WAZZY
                    </h1>
                    <h2 class="text-5xl font-black mb-8 gradient-neon">
                        VAPE
                    </h2>
                    
                    <p class="text-xl text-slate-300 mb-12 leading-relaxed font-bold">
                        Pods neon premium com design cyberpunk extraordinário. Qualidade garantida, entrega rápida e atendimento futurista.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-5 mb-12">
                        <button class="px-8 py-4 btn-neon rounded-lg ripple relative z-10 text-lg transition-all duration-300">
                            <i class="fas fa-cart-plus mr-2"></i>
                            COMPRAR AGORA
                        </button>
                        <button class="px-8 py-4 glow-box-cyan text-cyan-300 rounded-lg font-bold ripple relative z-10 text-lg hover:bg-cyan-900/20 transition-all">
                            <i class="fas fa-arrow-down mr-2"></i>
                            VER PRODUTOS
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="glow-box p-4 rounded-lg backdrop-blur text-center">
                            <div class="text-3xl font-bold neon-glow">500+</div>
                            <div class="text-xs text-slate-400 mt-1">PODS</div>
                        </div>
                        <div class="glow-box p-4 rounded-lg backdrop-blur text-center">
                            <div class="text-3xl font-bold neon-glow">10K+</div>
                            <div class="text-xs text-slate-400 mt-1">CLIENTES</div>
                        </div>
                        <div class="glow-box p-4 rounded-lg backdrop-blur text-center">
                            <div class="text-3xl font-bold neon-glow-cyan">5.0★</div>
                            <div class="text-xs text-slate-400 mt-1">RATING</div>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-left" data-aos-duration="1000" class="relative h-96 md:h-full min-h-96">
                    <div class="absolute inset-0 glow-box rounded-3xl blur-3xl"></div>
                    <div class="relative h-full glow-box rounded-3xl p-8 flex items-center justify-center text-white text-center" style="animation: float 3s ease-in-out infinite;">
                        <div>
                            <i class="fas fa-skull text-9xl mb-6 neon-glow" style="text-shadow: 0 0 30px #ff00ff;"></i>
                            <h3 class="text-6xl font-black neon-glow">WAZZY</h3>
                            <p class="text-2xl neon-glow-cyan mt-4">NEON VAPE</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Banner -->
    <section class="py-8 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/30 to-transparent border-y border-purple-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-truck text-3xl neon-glow-cyan mb-2"></i>
                    <p class="font-bold">FRETE GRÁTIS</p>
                    <p class="text-xs text-slate-400">Acima de R$ 100</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-shield-alt text-3xl neon-glow mb-2"></i>
                    <p class="font-bold">100% SEGURO</p>
                    <p class="text-xs text-slate-400">Proteção total</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-redo text-3xl neon-glow mb-2"></i>
                    <p class="font-bold">DEVOLUÇÃO</p>
                    <p class="text-xs text-slate-400">30 dias garantidos</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-headset text-3xl neon-glow-cyan mb-2"></i>
                    <p class="font-bold">SUPORTE 24/7</p>
                    <p class="text-xs text-slate-400">Sempre pronto</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias Section -->
    <section id="categorias" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-900/30">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 neon-glow">ESCOLHA SEU</h2>
                <h2 class="text-5xl font-black neon-glow-cyan">ESTILO</h2>
                <p class="text-xl text-slate-400 mt-4">Pods descartáveis, recarregáveis e acessórios neon</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="group glow-box rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer" data-aos="zoom-in">
                    <div class="text-6xl mb-4">📱</div>
                    <h3 class="text-2xl font-bold mb-2 neon-glow">PODS</h3>
                    <h3 class="text-2xl font-bold mb-2 neon-glow-cyan">DESCARTÁVEIS</h3>
                    <p class="text-slate-400 mb-4">Pronto para usar, máxima praticidade</p>
                    <p class="text-sm neon-glow font-bold">24+ PRODUTOS</p>
                </div>
                <div class="group glow-box rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-6xl mb-4">🔄</div>
                    <h3 class="text-2xl font-bold mb-2 neon-glow">PODS</h3>
                    <h3 class="text-2xl font-bold mb-2 neon-glow-cyan">RECARREGÁVEIS</h3>
                    <p class="text-slate-400 mb-4">Sustentável e econômico</p>
                    <p class="text-sm neon-glow font-bold">18+ PRODUTOS</p>
                </div>
                <div class="group glow-box rounded-2xl p-8 text-center hover:shadow-2xl transition cursor-pointer" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-6xl mb-4">🎁</div>
                    <h3 class="text-2xl font-bold mb-2 neon-glow">ACESSÓRIOS</h3>
                    <h3 class="text-2xl font-bold mb-2 neon-glow-cyan">NEON</h3>
                    <p class="text-slate-400 mb-4">Tudo que você precisa</p>
                    <p class="text-sm neon-glow font-bold">31+ PRODUTOS</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Produtos Destaque -->
    <section id="produtos" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="inline-block px-4 py-2 glow-box rounded-full text-sm font-bold neon-glow mb-4">
                    <i class="fas fa-fire"></i> MAIS VENDIDOS
                </span>
                <h2 class="text-5xl font-black mb-4 neon-glow">PRODUTOS EM</h2>
                <h2 class="text-5xl font-black neon-glow-cyan">DESTAQUE</h2>
                <p class="text-xl text-slate-400 mt-4">Seleção premium de pods neon</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($produtos, 0, 8) as $i => $produto): ?>
                <div class="glow-box rounded-2xl overflow-hidden card-hover group relative" data-aos="flip-left" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="absolute top-4 right-4 px-3 py-2 rounded-full text-sm font-bold neon-glow z-10 glow-box">
                        -30%
                    </div>

                    <div class="relative h-56 overflow-hidden bg-gradient-to-br from-purple-900/50 to-black">
                        <img src="<?php echo htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/400x300?text=' . urlencode($produto['nome'])); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://via.placeholder.com/400x300?text=Produto'">
                        <div class="absolute top-4 left-4 neon-glow-cyan px-3 py-1 rounded-full text-xs font-bold glow-box-cyan">
                            <?php echo htmlspecialchars($produto['categoria_nome'] ?? 'NOVO'); ?>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="text-lg font-bold neon-glow line-clamp-2 mb-3"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="text-slate-400 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($produto['descricao'] ?? '', 0, 80)); ?>...</p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex gap-0.5">
                                <?php for($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star neon-glow text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="neon-glow text-xs font-bold">5.0</span>
                        </div>

                        <div class="mb-5 pb-5 border-b border-slate-700">
                            <span class="text-2xl font-black neon-glow">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            <span class="text-slate-500 line-through ml-2 text-sm">R$ <?php echo number_format($produto['preco'] * 1.43, 2, ',', '.'); ?></span>
                        </div>

                        <button onclick="addToCart(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['preco']; ?>)" class="w-full py-3 btn-neon rounded-lg font-bold text-sm transition">
                            <i class="fas fa-cart-plus mr-2"></i>
                            ADICIONAR
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <button class="px-10 py-4 glow-box rounded-lg font-bold text-lg neon-glow hover:shadow-2xl transition">
                    <i class="fas fa-box mr-2"></i>
                    VER TODOS OS PRODUTOS
                </button>
            </div>
        </div>
    </section>

    <!-- Avaliações -->
    <section id="avaliacoes" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-900/20 to-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-5xl font-black mb-4 neon-glow">O QUE NOSSOS</h2>
                <h2 class="text-5xl font-black neon-glow-cyan">CLIENTES DIZEM</h2>
                <div class="flex justify-center gap-1 mb-4 mt-4">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star neon-glow text-2xl"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-xl text-slate-400">5.0 ★ em mais de 2.500 avaliações verificadas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($avaliacoes as $i => $avaliacao): ?>
                <div class="glow-box rounded-2xl p-8 card-hover" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="flex gap-1 mb-4">
                        <?php for($j = 0; $j < 5; $j++): ?>
                            <i class="fas fa-star neon-glow text-lg"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-slate-300 mb-6 italic text-lg">"<?php echo $avaliacao['texto']; ?>"</p>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-700">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center flex-shrink-0 neon-glow">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-bold neon-glow"><?php echo $avaliacao['nome']; ?></p>
                            <p class="text-slate-400 text-sm"><i class="fas fa-check-circle neon-glow-cyan mr-1"></i>Comprador Verificado</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter CTA Neon -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <div class="glow-box rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-5xl font-black mb-6 neon-glow">GANHE</h2>
                    <h2 class="text-5xl font-black neon-glow-cyan mb-10">20% OFF</h2>
                    <p class="text-xl text-slate-300 mb-10 font-bold">
                        Inscreva-se na newsletter e receba 20% de desconto + acesso exclusivo a promoções neon!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                        <input type="email" placeholder="seu@email.com" class="flex-1 px-6 py-4 rounded-lg bg-slate-800/50 text-slate-100 border border-purple-600/50 focus:outline-none focus:ring-2 focus:ring-purple-400 placeholder-slate-500 text-base">
                        <button class="px-8 py-4 btn-neon rounded-lg whitespace-nowrap text-base">
                            <i class="fas fa-paper-plane mr-2"></i>
                            INSCREVER
                        </button>
                    </div>
                    <p class="text-slate-400 text-sm mt-6">📧 Garantido: sem spam, apenas promoções neon!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Neon -->
    <footer class="bg-black/80 text-slate-300 py-16 px-4 sm:px-6 lg:px-8 border-t border-purple-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div>
                    <div class="text-3xl font-black mb-6 neon-glow flex items-center gap-2">
                        <i class="fas fa-skull-crossbones"></i>
                        <span>WAZZY VAPE</span>
                    </div>
                    <p class="text-slate-400 mb-6 leading-relaxed">Sua loja neon de pods premium com design cyberpunk extraordinário.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:neon-glow transition text-xl"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:neon-glow transition text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:neon-glow transition text-xl"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="text-slate-400 hover:neon-glow transition text-xl"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 neon-glow">PRODUTOS</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="#" class="hover:neon-glow transition">Pods Descartáveis</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Pods Recarregáveis</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Acessórios Neon</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Ofertas Especiais</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 neon-glow-cyan">SUPORTE</h4>
                    <ul class="space-y-3 text-slate-400">
                        <li><a href="#" class="hover:neon-glow-cyan transition">WhatsApp: (11) 9999-9999</a></li>
                        <li><a href="#" class="hover:neon-glow-cyan transition">FAQ & Dúvidas</a></li>
                        <li><a href="#" class="hover:neon-glow-cyan transition">Rastrear Pedido</a></li>
                        <li><a href="#" class="hover:neon-glow-cyan transition">Trocas e Devoluções</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 neon-glow">LEGAL</h4>
                    <ul class="space-y-3 text-slate-400 text-sm">
                        <li><a href="#" class="hover:neon-glow transition">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Termos de Uso</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Aviso de Saúde</a></li>
                        <li><a href="#" class="hover:neon-glow transition">Restrição de Idade: 18+</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-slate-400 text-center md:text-left neon-glow">© 2024 Wazzy Vape. Todos os direitos reservados. | ⚡ NEON VIBES</p>
                <div class="flex gap-6">
                    <i class="fab fa-cc-visa text-2xl text-slate-400 hover:neon-glow transition cursor-pointer"></i>
                    <i class="fab fa-cc-mastercard text-2xl text-slate-400 hover:neon-glow transition cursor-pointer"></i>
                    <i class="fab fa-cc-paypal text-2xl text-slate-400 hover:neon-glow transition cursor-pointer"></i>
                    <i class="fab fa-bitcoin text-2xl text-slate-400 hover:neon-glow transition cursor-pointer"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scroll-top" class="scroll-top btn-neon p-4 rounded-full">
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