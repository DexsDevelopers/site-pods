<?php
require_once '../includes/config_hostinger.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Buscar produto do banco de dados
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: /');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.id = ? AND p.ativo = 1
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: /');
        exit;
    }
    
    // Decodificar JSON fields
    $product['caracteristicas'] = json_decode($product['caracteristicas'] ?? '{}', true);
    $product['galeria'] = json_decode($product['galeria'] ?? '[]', true);
    
    // Se não há galeria, usar a imagem principal
    if (empty($product['galeria']) && !empty($product['imagem'])) {
        $product['galeria'] = [$product['imagem']];
    }
    
} catch (Exception $e) {
    error_log('Erro ao buscar produto: ' . $e->getMessage());
    header('Location: /');
    exit;
}

// Buscar produtos relacionados da mesma categoria
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.categoria_id = ? AND p.id != ? AND p.ativo = 1 
        ORDER BY p.destaque DESC, p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$product['categoria_id'], $product['id']]);
    $relacionados = $stmt->fetchAll();
} catch (Exception $e) {
    $relacionados = [];
}

// Reviews simulados (em produção, viria do banco)
$reviews = [
    ['nome' => 'João Silva', 'rating' => 5, 'texto' => 'Produto excepcional! Chegou rápido e bem embalado.', 'verificado' => true],
    ['nome' => 'Maria Santos', 'rating' => 4, 'texto' => 'Muito bom, recomendo!', 'verificado' => true],
    ['nome' => 'Pedro Costa', 'rating' => 5, 'texto' => 'Melhor vaper que já tive!', 'verificado' => true],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['nome']; ?> - Loja de Pods</title>
    <!-- CSS Moderno e Profissional -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: white;
            line-height: 1.6;
        }
        
        .product-container {
            display: flex;
            gap: 3rem;
            padding: 3rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: 80vh;
        }
        
        .product-image-container {
            flex: 1;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border-radius: 20px;
            padding: 2rem;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid rgba(139, 92, 246, 0.3);
            position: relative;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .product-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 16px !important;
            display: block !important;
            max-width: 100% !important;
            max-height: 100% !important;
            visibility: visible !important;
            opacity: 1 !important;
            transition: transform 0.3s ease;
        }
        
        .product-image:hover {
            transform: scale(1.02);
        }
        
        .product-info {
            flex: 1;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 20px;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .product-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #8b5cf6, #ec4899, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .product-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }
        
        .product-description {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            color: #cbd5e1;
        }
        
        .btn {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        .feature-card {
            background: rgba(30, 41, 59, 0.6);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .rating-stars {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 1rem;
        }
        
        .star {
            color: #fbbf24;
            font-size: 1.2rem;
            text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
        }
        
        .stock-bar {
            width: 100%;
            height: 8px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .stock-fill {
            height: 100%;
            background: linear-gradient(90deg, #8b5cf6, #ec4899);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .tabs-container {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 3rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .tab-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .tab-btn {
            background: none;
            border: none;
            color: #94a3b8;
            padding: 1rem 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }
        
        .tab-btn.active {
            color: #8b5cf6;
            border-bottom-color: #8b5cf6;
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .related-products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .related-card {
            background: rgba(30, 41, 59, 0.6);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(139, 92, 246, 0.2);
            transition: all 0.3s ease;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        /* Menu Responsivo */
        @media (max-width: 768px) {
            .desktop-menu {
                display: none !important;
            }
            
            .mobile-menu {
                display: block !important;
            }
        }
        
        @media (min-width: 769px) {
            .desktop-menu {
                display: flex !important;
            }
            
            .mobile-menu {
                display: none !important;
            }
        }
        
        /* Hover Effects para Menu */
        .desktop-menu a:hover,
        .desktop-menu button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
        }
        
        .mobile-menu button:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
                padding: 1rem;
                gap: 2rem;
            }
            
            .product-image-container {
                height: 400px;
            }
            
            .product-title {
                font-size: 2rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
            
            .product-info {
                padding: 1rem;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .product-container {
                padding: 0.5rem;
                gap: 1.5rem;
            }
            
            .product-image-container {
                height: 300px;
                padding: 1rem;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(147, 51, 234, 0.2); }
        .gradient-text { background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="text-slate-100">

    <!-- Header Simples -->
    <!-- Menu Moderno e Responsivo -->
    <header style="position: fixed; top: 0; width: 100%; z-index: 1000; background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(139, 92, 246, 0.3); padding: 1rem 0;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between;">
            <!-- Logo -->
            <a href="../index.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #ec4899); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-cloud" style="color: white; font-size: 1.2rem;"></i>
                </div>
                <span style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Wazzy Pods</span>
            </a>

            <!-- Menu Desktop -->
            <nav class="desktop-menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="javascript:history.back()" style="display: flex; align-items: center; padding: 0.75rem 1.5rem; background: rgba(255, 255, 255, 0.1); border-radius: 10px; text-decoration: none; color: white; transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                    <span>Voltar</span>
                </a>
                <button onclick="toggleWishlist()" id="wishlistBtn" style="display: flex; align-items: center; padding: 0.75rem 1.5rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; color: white; cursor: pointer; transition: all 0.3s ease;">
                    <i class="far fa-heart" style="margin-right: 0.5rem;"></i>
                    <span>Favoritar</span>
                </button>
                <button type="button" onclick="addToCart(event); return false;" style="display: flex; align-items: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #8b5cf6, #ec4899); border: none; border-radius: 10px; color: white; cursor: pointer; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
                    <i class="fas fa-shopping-cart" style="margin-right: 0.5rem;"></i>
                    <span>Comprar</span>
                </button>
            </nav>

            <!-- Menu Mobile -->
            <div class="mobile-menu" style="display: none;">
                <button id="mobileMenuBtn" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 0.5rem;">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Menu Mobile Dropdown -->
        <div id="mobileMenuDropdown" style="display: none; background: rgba(0, 0, 0, 0.95); backdrop-filter: blur(20px); border-top: 1px solid rgba(139, 92, 246, 0.3); padding: 1rem;">
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="javascript:history.back()" style="display: flex; align-items: center; padding: 1rem; background: rgba(255, 255, 255, 0.1); border-radius: 10px; text-decoration: none; color: white; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <i class="fas fa-arrow-left" style="margin-right: 0.75rem; width: 20px;"></i>
                    <span>Voltar</span>
                </a>
                <button onclick="toggleWishlist()" id="wishlistBtnMobile" style="display: flex; align-items: center; padding: 1rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; color: white; cursor: pointer; text-align: left; width: 100%;">
                    <i class="far fa-heart" style="margin-right: 0.75rem; width: 20px;"></i>
                    <span>Favoritar</span>
                </button>
                <button type="button" onclick="addToCart(event); return false;" style="display: flex; align-items: center; padding: 1rem; background: linear-gradient(135deg, #8b5cf6, #ec4899); border: none; border-radius: 10px; color: white; cursor: pointer; font-weight: 700; text-align: left; width: 100%;">
                    <i class="fas fa-shopping-cart" style="margin-right: 0.75rem; width: 20px;"></i>
                    <span>Comprar</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main style="padding: 6rem 1rem 5rem; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); min-height: 100vh;">
        <div class="product-container">
                <!-- Imagens -->
                    <!-- Container de imagem simplificado -->
                    <div class="product-image-container">
                        <?php
                        // Sistema simplificado de imagens
                        $imagemUrl = $product['imagem'] ?? '';
                        $imagemFallback = 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop&auto=format';

                        // Se não há imagem ou é vazia, usar fallback
                        if (empty($imagemUrl)) {
                            $imagemUrl = $imagemFallback;
                        }
                        ?>

                        <!-- Imagem principal com CSS puro -->
                        <img id="mainImage" 
                             class="product-image"
                             src="<?php echo htmlspecialchars($imagemUrl); ?>" 
                             alt="<?php echo htmlspecialchars($product['nome']); ?>"
                             onerror="console.log('Erro ao carregar imagem:', this.src); this.src='<?php echo $imagemFallback; ?>'"
                             onload="console.log('Imagem carregada com sucesso:', this.src);">
                    </div>
                    <?php if (!empty($product['galeria']) && count($product['galeria']) > 1): ?>
                    <div class="flex gap-2">
                        <?php foreach ($product['galeria'] as $img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" onclick="document.getElementById('mainImage').src='<?php echo htmlspecialchars($img); ?>'" class="w-20 h-20 rounded cursor-pointer border border-purple-600 hover:border-pink-600 transition" alt="">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Detalhes -->
                <div class="product-info">
                    <div style="margin-bottom: 1rem;">
                        <span style="background: #8b5cf6; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;"><?php echo htmlspecialchars($product['categoria_nome'] ?? 'Produto'); ?></span>
                    </div>

                    <h1 class="product-title"><?php echo htmlspecialchars($product['nome']); ?></h1>

                    <!-- Rating -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex gap-1">
                            <?php 
                            $rating = $product['avaliacao_media'] ?? 4.5;
                            for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < round($rating) ? 'text-yellow-400' : 'text-slate-600'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-yellow-400 font-bold"><?php echo number_format($rating, 1); ?></span>
                        <span class="text-slate-400">(<?php echo $product['total_avaliacoes'] ?? 0; ?> avaliações)</span>
                    </div>

                    <!-- Preço -->
                    <div style="margin-bottom: 2rem;">
                        <?php 
                        $precoOriginal = $product['preco'];
                        $precoFinal = $product['preco_promocional'] ?? $precoOriginal;
                        $desconto = $precoOriginal > 0 ? round((($precoOriginal - $precoFinal) / $precoOriginal) * 100) : 0;
                        ?>
                        <div class="product-price">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                        <?php if ($desconto > 0): ?>
                        <div style="color: #94a3b8; text-decoration: line-through;">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></div>
                        <div style="color: #10b981; font-weight: bold; margin-top: 0.5rem;">
                            <i class="fas fa-tag" style="margin-right: 0.5rem;"></i><?php echo $desconto; ?>% OFF
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Frete & Autenticidade -->
                    <div class="feature-card">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <i class="fas fa-truck" style="color: #3b82f6; font-size: 1.5rem;"></i>
                            <div>
                                <p style="font-weight: 700; margin-bottom: 0.25rem;">Frete Grátis</p>
                                <p style="color: #94a3b8; font-size: 0.9rem;">Acima de R$ 100</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #8b5cf6; font-size: 1.5rem;"></i>
                            <div>
                                <p style="font-weight: 700; margin-bottom: 0.25rem;">100% Autentico</p>
                                <p style="color: #94a3b8; font-size: 0.9rem;">Produto Original Lacrado</p>
                            </div>
                        </div>
                    </div>

                    <!-- Estoque -->
                    <div class="feature-card">
                        <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 0.5rem;">Disponibilidade</p>
                        <?php 
                        $estoque = $product['estoque'] ?? 0;
                        $percentual = min(($estoque / 50) * 100, 100);
                        ?>
                        <div class="stock-bar">
                            <div class="stock-fill" style="width: <?php echo $percentual; ?>%;"></div>
                        </div>
                        <p style="font-size: 0.9rem; margin-top: 0.5rem;">
                            <?php if ($estoque > 0): ?>
                                <span style="color: #10b981; font-weight: 700;"><?php echo $estoque; ?></span> 
                                <span style="color: #94a3b8;">unidades em estoque</span>
                            <?php else: ?>
                                <span style="color: #ef4444; font-weight: 700;">Fora de estoque</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Botões -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="button" onclick="addToCart(event); return false;" class="btn" style="flex: 1;">
                            <i class="fas fa-shopping-cart" style="margin-right: 0.5rem;"></i>Adicionar ao Carrinho
                        </button>
                        <button onclick="toggleWishlist()" class="btn btn-secondary" style="flex: 1;" id="wishlistBtn2">
                            <i class="far fa-heart" style="margin-right: 0.5rem;"></i>Favoritar
                        </button>
                    </div>
                    
                    <!-- Botões de Debug (temporários) -->
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                        <button onclick="console.log('🧪 Teste simples funcionando!'); alert('JavaScript está funcionando!');" style="padding: 0.5rem 1rem; background: #f59e0b; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                            🧪 Teste JS
                        </button>
                        <button onclick="addToCart(event); return false;" style="padding: 0.5rem 1rem; background: #8b5cf6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                            🛒 Teste AddCart
                        </button>
                        <button onclick="testCart()" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                            🧪 Testar Carrinho
                        </button>
                        <button onclick="clearCart()" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                            🗑️ Limpar Carrinho
                        </button>
                        <button onclick="window.location.href='cart.php'" style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                            🛒 Ver Carrinho
                        </button>
                    </div>
                </div>
            </div>

            <!-- Abas de Informação -->
            <div class="tabs-container">
                <div class="tab-buttons">
                    <button onclick="showTab('especificacoes')" class="tab-btn active" id="tab-especificacoes">
                        Especificações
                    </button>
                    <button onclick="showTab('ingredientes')" class="tab-btn" id="tab-ingredientes">
                        Composição
                    </button>
                    <button onclick="showTab('incluso')" class="tab-btn" id="tab-incluso">
                        O que Inclui
                    </button>
                </div>

                <div id="especificacoes" class="tab-content active" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <?php if (!empty($product['caracteristicas'])): ?>
                        <?php foreach ($product['caracteristicas'] as $spec => $valor): ?>
                            <div class="feature-card">
                                <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(ucfirst($spec)); ?></p>
                                <p style="font-weight: 700; font-size: 1.1rem; color: #cbd5e1;"><?php echo htmlspecialchars($valor); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="feature-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <i class="fas fa-info-circle" style="font-size: 3rem; color: #64748b; margin-bottom: 1rem;"></i>
                            <p style="color: #94a3b8;">Especificações não disponíveis</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="ingredientes" class="tab-content">
                    <div class="feature-card">
                        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: #8b5cf6;">Descrição do Produto</h3>
                        <p style="color: #cbd5e1; line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($product['descricao'] ?? 'Descrição não disponível')); ?>
                        </p>
                        </div>
                </div>

                <div id="incluso" class="tab-content">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div class="feature-card" style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                            <span style="color: #cbd5e1; font-weight: 500;">Produto original lacrado</span>
                        </div>
                        <div class="feature-card" style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                            <span style="color: #cbd5e1; font-weight: 500;">Garantia de 12 meses</span>
                        </div>
                        <div class="feature-card" style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                            <span style="color: #cbd5e1; font-weight: 500;">Frete grátis acima de R$ 100</span>
                        </div>
                        <div class="feature-card" style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                            <span style="color: #cbd5e1; font-weight: 500;">Suporte técnico especializado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avaliações -->
            <div class="tabs-container">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 2rem; background: linear-gradient(135deg, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Avaliações de Clientes</h2>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($reviews as $review): ?>
                        <div class="feature-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #8b5cf6, #ec4899); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p style="font-weight: 700; color: #cbd5e1;"><?php echo $review['nome']; ?></p>
                                        <?php if ($review['verificado']): ?>
                                            <p style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-check-circle" style="margin-right: 0.25rem;"></i>Comprador Verificado</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="rating-stars">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star star" style="<?php echo $i < $review['rating'] ? 'color: #fbbf24;' : 'color: #64748b;'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p style="color: #cbd5e1; line-height: 1.6;"><?php echo $review['texto']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Produtos Relacionados -->
            <div class="tabs-container">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 2rem; background: linear-gradient(135deg, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Você Também Pode Gostar</h2>
                <div class="related-products">
                    <?php foreach ($relacionados as $prod): ?>
                        <div class="related-card">
                            <div style="height: 12rem; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box" style="font-size: 3rem; color: #64748b;"></i>
                            </div>
                            <div style="padding: 1.5rem;">
                                <h3 style="font-weight: 700; margin-bottom: 0.5rem; color: #cbd5e1;"><?php echo $prod['nome']; ?></h3>
                                <p style="font-size: 1.5rem; font-weight: 800; color: #10b981; margin-bottom: 1rem;">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></p>
                                <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="btn" style="width: 100%; text-align: center; display: block;">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Esconder todas as abas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });
            
            // Mostrar a aba selecionada
            const activeTab = document.getElementById(tabName);
            if (activeTab) {
                activeTab.classList.add('active');
                activeTab.style.display = 'block';
            }
            
            // Atualizar botões
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const activeBtn = document.getElementById(`tab-${tabName}`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
        }

        
        function showNotification(message, type = 'success') {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            // Adicionar CSS da animação
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(notification);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        function updateCartBadge() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => {
                const qty = item.qty || item.quantity || 0;
                return sum + (isNaN(qty) ? 0 : qty);
            }, 0);
            
            // Atualizar badge se existir
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = totalItems;
                badge.style.display = totalItems > 0 ? 'block' : 'none';
            }
        }
        

        function toggleWishlist() {
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            const productId = <?php echo json_encode($product['id']); ?>;
            
            if (wishlist.includes(productId)) {
                wishlist = wishlist.filter(id => id !== productId);
                document.getElementById('wishlistBtn').innerHTML = '<i class="far fa-heart" style="margin-right: 0.5rem;"></i><span>Favoritar</span>';
                if (document.getElementById('wishlistBtnMobile')) {
                    document.getElementById('wishlistBtnMobile').innerHTML = '<i class="far fa-heart" style="margin-right: 0.75rem; width: 20px;"></i><span>Favoritar</span>';
                }
                showNotification('💔 Produto removido dos favoritos!', 'info');
            } else {
                wishlist.push(productId);
                document.getElementById('wishlistBtn').innerHTML = '<i class="fas fa-heart" style="margin-right: 0.5rem;"></i><span>Favoritado</span>';
                if (document.getElementById('wishlistBtnMobile')) {
                    document.getElementById('wishlistBtnMobile').innerHTML = '<i class="fas fa-heart" style="margin-right: 0.75rem; width: 20px;"></i><span>Favoritado</span>';
                }
                showNotification('❤️ Produto adicionado aos favoritos!', 'success');
            }
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
        }
        
        // Menu Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');
            
            if (mobileMenuBtn && mobileMenuDropdown) {
                mobileMenuBtn.addEventListener('click', function() {
                    if (mobileMenuDropdown.style.display === 'none' || mobileMenuDropdown.style.display === '') {
                        mobileMenuDropdown.style.display = 'block';
                        mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
                    } else {
                        mobileMenuDropdown.style.display = 'none';
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            }
            
            // Verificar estado inicial dos favoritos
            checkWishlistStatus();
            
            // Forçar exibição da imagem
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                console.log('Forçando exibição da imagem...');
                mainImage.style.display = 'block';
                mainImage.style.visibility = 'visible';
                mainImage.style.opacity = '1';
                mainImage.style.width = '100%';
                mainImage.style.height = '100%';
                mainImage.style.objectFit = 'cover';
                
                // Se a imagem não carregou, tentar novamente
                if (!mainImage.complete || mainImage.naturalHeight === 0) {
                    console.log('Imagem não carregou, tentando novamente...');
                    const newSrc = mainImage.src;
                    mainImage.src = '';
                    setTimeout(() => {
                        mainImage.src = newSrc;
                    }, 100);
                }
            }
        }
        
        function checkWishlistStatus() {
            const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            const productId = <?php echo $product['id']; ?>;
            
            if (wishlist.includes(productId)) {
                document.getElementById('wishlistBtn').innerHTML = '<i class="fas fa-heart" style="margin-right: 0.5rem;"></i><span>Favoritado</span>';
                if (document.getElementById('wishlistBtnMobile')) {
                    document.getElementById('wishlistBtnMobile').innerHTML = '<i class="fas fa-heart" style="margin-right: 0.75rem; width: 20px;"></i><span>Favoritado</span>';
                }
            }
        });
    </script>

    <!-- Funções de Debug - Escopo Global -->
    <script>
        // Função addToCart no escopo global
        function addToCart(event) {
            console.log('🚀 FUNÇÃO addToCart CHAMADA!');
            console.log('📧 Evento recebido:', event);
            
            // Prevenir qualquer comportamento padrão
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                console.log('🛑 Evento prevenido');
            }
            
            console.log('🛒 Iniciando addToCart...');
            
            try {
                let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                const productId = <?php echo json_encode($product['id']); ?>;
                
                console.log('📦 ID do produto:', productId);
                console.log('🛒 Carrinho atual:', cart);
                
                // Verificar se o produto já está no carrinho
                const existingItem = cart.find(item => item.id === productId);
                
                if (existingItem) {
                    // Se já existe, aumentar a quantidade
                    existingItem.qty = (existingItem.qty || 0) + 1;
                    console.log('➕ Quantidade aumentada para:', existingItem.qty);
                } else {
                    // Se não existe, adicionar novo item
                    const item = {
                        id: productId,
                        nome: <?php echo json_encode($product['nome']); ?>,
                        preco: <?php echo json_encode($product['preco']); ?>,
                        qty: 1,
                        imagem: <?php echo json_encode($product['imagem']); ?>
                    };
                    cart.push(item);
                    console.log('🆕 Novo item adicionado:', item);
                }
                
                console.log('💾 Salvando carrinho:', cart);
                localStorage.setItem('cart', JSON.stringify(cart));
                
                // Verificar se foi salvo
                const savedCart = JSON.parse(localStorage.getItem('cart') || '[]');
                console.log('✅ Carrinho salvo:', savedCart);
                
                // Mostrar notificação moderna
                showNotification('✅ Produto adicionado ao carrinho!', 'success');
                
                // Atualizar contador do carrinho se existir
                updateCartBadge();
                
                // Debug: Mostrar carrinho no console
                console.log('🔍 Carrinho final no localStorage:', localStorage.getItem('cart'));
                
                // Testar imediatamente
                testCart();
                
            } catch (error) {
                console.error('❌ ERRO no addToCart:', error);
                showNotification('❌ Erro ao adicionar ao carrinho!', 'error');
            }
            
            // Retornar false para prevenir qualquer redirecionamento
            return false;
        }
        
        function testCart() {
            console.log('🧪 TESTANDO CARRINHO:');
            console.log('📦 localStorage.getItem("cart"):', localStorage.getItem('cart'));
            
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            console.log('🛒 Carrinho parseado:', cart);
            console.log('📊 Total de itens:', cart.length);
            
            // Calcular total de quantidades (lidando com dados inconsistentes)
            const totalQuantities = cart.reduce((sum, item) => {
                const qty = item.qty || item.quantity || 0;
                return sum + (isNaN(qty) ? 0 : qty);
            }, 0);
            console.log('🔢 Total de quantidades:', totalQuantities);
            
            if (cart.length > 0) {
                console.log('✅ CARRINHO TEM ITENS:');
                cart.forEach((item, index) => {
                    const nome = item.nome || 'Nome não disponível';
                    const preco = item.preco || item.preco_final || 0;
                    const qty = item.qty || item.quantity || 0;
                    console.log(`  ${index + 1}. ${nome} - R$ ${preco} - Qty: ${qty}`);
                });
            } else {
                console.log('❌ CARRINHO VAZIO');
            }
        }
        
        function clearCart() {
            localStorage.removeItem('cart');
            console.log('🗑️ Carrinho limpo!');
            // Atualizar badge se existir
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = '0';
                badge.style.display = 'none';
            }
        }
        
        // Função showNotification no escopo global
        function showNotification(message, type = 'success') {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            // Adicionar CSS da animação
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(notification);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Função updateCartBadge no escopo global
        function updateCartBadge() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => {
                const qty = item.qty || item.quantity || 0;
                return sum + (isNaN(qty) ? 0 : qty);
            }, 0);
            
            // Atualizar badge se existir
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = totalItems;
                badge.style.display = totalItems > 0 ? 'block' : 'none';
            }
        }
    </script>

</body>
</html>
