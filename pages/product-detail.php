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
    <script src="https://cdn.tailwindcss.com"></script>
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
    <header class="fixed top-0 w-full z-50 glass backdrop-blur-md bg-black/80 py-4 border-b border-purple-900/30">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
            <a href="../index.php" class="text-2xl font-bold gradient-text">
                <i class="fas fa-cloud mr-2"></i>Loja de Pods
            </a>
            <div class="flex gap-4">
                <a href="javascript:history.back()" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <button onclick="toggleWishlist()" class="px-4 py-2 glass rounded-lg hover:bg-white/10 transition" id="wishlistBtn">
                    <i class="far fa-heart mr-2"></i>Favoritar
                </button>
                <button onclick="addToCart()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg hover:shadow-lg transition font-bold">
                    <i class="fas fa-shopping-cart mr-2"></i>Comprar
                </button>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="pt-24 pb-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 mb-16">
                <!-- Imagens -->
                <div data-aos="fade-right">
                    <div class="bg-slate-800/50 rounded-lg p-4 mb-4 aspect-square flex items-center justify-center overflow-hidden">
                        <?php 
                        // Verificar se a imagem existe e tem um caminho válido
                        $imagemUrl = $product['imagem'] ?? '';
                        $imagemValida = false;
                        
                        if (!empty($imagemUrl)) {
                            // Se é uma URL completa, usar diretamente
                            if (strpos($imagemUrl, 'http') === 0) {
                                $imagemValida = true;
                            }
                            // Se é um caminho relativo, verificar se o arquivo existe
                            elseif (file_exists('../' . $imagemUrl)) {
                                $imagemUrl = '../' . $imagemUrl;
                                $imagemValida = true;
                            }
                            // Se é um caminho absoluto do servidor
                            elseif (file_exists($imagemUrl)) {
                                $imagemValida = true;
                            }
                        }
                        
                        if ($imagemValida): ?>
                            <img id="mainImage" src="<?php echo htmlspecialchars($imagemUrl); ?>" 
                                 class="max-w-full max-h-full object-cover rounded" 
                                 alt="<?php echo htmlspecialchars($product['nome']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="text-slate-500 text-center" style="display: none;">
                                <i class="fas fa-image text-6xl mb-4"></i>
                                <p>Imagem não carregou</p>
                            </div>
                        <?php else: ?>
                            <!-- Imagem padrão usando Unsplash -->
                            <img id="mainImage" src="https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop&auto=format" 
                                 class="max-w-full max-h-full object-cover rounded" 
                                 alt="<?php echo htmlspecialchars($product['nome']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="text-slate-500 text-center" style="display: none;">
                                <i class="fas fa-image text-6xl mb-4"></i>
                                <p>Imagem não carregou</p>
                            </div>
                        <?php endif; ?>
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
                <div data-aos="fade-left">
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-purple-600 text-white text-sm rounded-full"><?php echo htmlspecialchars($product['categoria_nome'] ?? 'Produto'); ?></span>
                    </div>

                    <h1 class="text-4xl font-black mb-4"><?php echo htmlspecialchars($product['nome']); ?></h1>

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
                    <div class="mb-6">
                        <?php 
                        $precoOriginal = $product['preco'];
                        $precoFinal = $product['preco_promocional'] ?? $precoOriginal;
                        $desconto = $precoOriginal > 0 ? round((($precoOriginal - $precoFinal) / $precoOriginal) * 100) : 0;
                        ?>
                        <div class="text-4xl font-black gradient-text mb-2">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                        <?php if ($desconto > 0): ?>
                        <div class="text-slate-400 line-through">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></div>
                        <div class="text-green-400 font-bold mt-2">
                            <i class="fas fa-tag mr-2"></i><?php echo $desconto; ?>% OFF
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Garantia & Frete -->
                    <div class="glass rounded-lg p-6 mb-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt text-green-400 text-xl"></i>
                            <div>
                                <p class="font-bold">Garantia</p>
                                <p class="text-slate-400 text-sm">12 meses garantidos</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-truck text-blue-400 text-xl"></i>
                            <div>
                                <p class="font-bold">Frete Grátis</p>
                                <p class="text-slate-400 text-sm">Acima de R$ 100</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-purple-400 text-xl"></i>
                            <div>
                                <p class="font-bold">100% Autentico</p>
                                <p class="text-slate-400 text-sm">Produto Original Lacrado</p>
                            </div>
                        </div>
                    </div>

                    <!-- Estoque -->
                    <div class="mb-6">
                        <p class="text-sm text-slate-400 mb-2">Disponibilidade</p>
                        <?php 
                        $estoque = $product['estoque'] ?? 0;
                        $percentual = min(($estoque / 50) * 100, 100);
                        ?>
                        <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                            <div class="bg-gradient-to-r from-purple-600 to-pink-600 h-2 rounded-full" style="width: <?php echo $percentual; ?>%;"></div>
                        </div>
                        <p class="text-sm">
                            <?php if ($estoque > 0): ?>
                                <span class="text-green-400 font-bold"><?php echo $estoque; ?></span> 
                                <span class="text-slate-400">unidades em estoque</span>
                            <?php else: ?>
                                <span class="text-red-400 font-bold">Fora de estoque</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Botões -->
                    <div class="flex gap-4">
                        <button onclick="addToCart()" class="flex-1 py-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Adicionar ao Carrinho
                        </button>
                        <button onclick="toggleWishlist()" class="flex-1 py-4 glass rounded-lg font-bold hover:bg-white/20 transition" id="wishlistBtn2">
                            <i class="far fa-heart mr-2"></i>Favoritar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Abas de Informação -->
            <div class="mb-16">
                <div class="flex gap-4 mb-8 border-b border-slate-700">
                    <button onclick="showTab('especificacoes')" class="px-6 py-3 font-bold border-b-2 border-purple-600 text-purple-400">
                        Especificações
                    </button>
                    <button onclick="showTab('ingredientes')" class="px-6 py-3 font-bold text-slate-400 hover:text-white transition">
                        Composição
                    </button>
                    <button onclick="showTab('incluso')" class="px-6 py-3 font-bold text-slate-400 hover:text-white transition">
                        O que Inclui
                    </button>
                </div>

                <div id="especificacoes" class="grid md:grid-cols-2 gap-4">
                    <?php if (!empty($product['caracteristicas'])): ?>
                        <?php foreach ($product['caracteristicas'] as $spec => $valor): ?>
                            <div class="glass p-4 rounded-lg">
                                <p class="text-slate-400 text-sm"><?php echo htmlspecialchars(ucfirst($spec)); ?></p>
                                <p class="font-bold text-lg"><?php echo htmlspecialchars($valor); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 glass p-8 rounded-lg text-center">
                            <i class="fas fa-info-circle text-4xl text-slate-500 mb-4"></i>
                            <p class="text-slate-400">Especificações não disponíveis</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="ingredientes" class="hidden">
                    <div class="glass p-8 rounded-lg">
                        <h3 class="text-xl font-bold mb-4">Descrição do Produto</h3>
                        <p class="text-slate-300 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($product['descricao'] ?? 'Descrição não disponível')); ?>
                        </p>
                    </div>
                </div>

                <div id="incluso" class="hidden space-y-3">
                    <div class="glass p-4 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>Produto original lacrado</span>
                    </div>
                    <div class="glass p-4 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>Garantia de 12 meses</span>
                    </div>
                    <div class="glass p-4 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>Frete grátis acima de R$ 100</span>
                    </div>
                    <div class="glass p-4 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>Suporte técnico especializado</span>
                    </div>
                </div>
            </div>

            <!-- Avaliações -->
            <div class="mb-16">
                <h2 class="text-3xl font-black mb-8">Avaliações de Clientes</h2>
                <div class="space-y-6">
                    <?php foreach ($reviews as $review): ?>
                        <div class="glass p-6 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold"><?php echo $review['nome']; ?></p>
                                        <?php if ($review['verificado']): ?>
                                            <p class="text-xs text-green-400"><i class="fas fa-check-circle mr-1"></i>Comprador Verificado</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i < $review['rating'] ? 'text-yellow-400' : 'text-slate-600'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-slate-300"><?php echo $review['texto']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Produtos Relacionados -->
            <div>
                <h2 class="text-3xl font-black mb-8">Você Também Pode Gostar</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <?php foreach ($relacionados as $prod): ?>
                        <div class="glass rounded-lg overflow-hidden card-hover">
                            <div class="h-48 bg-slate-700"></div>
                            <div class="p-6">
                                <h3 class="font-bold mb-2"><?php echo $prod['nome']; ?></h3>
                                <p class="text-2xl gradient-text font-black mb-4">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></p>
                                <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-center block hover:shadow-lg transition">
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
            document.querySelectorAll('[id="especificacoes"], [id="ingredientes"], [id="incluso"]').forEach(el => el.classList.add('hidden'));
            document.getElementById(tabName).classList.remove('hidden');
            document.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('border-purple-600', 'text-purple-400');
                btn.classList.add('text-slate-400');
            });
            event.target.classList.remove('text-slate-400');
            event.target.classList.add('border-b-2', 'border-purple-600', 'text-purple-400');
        }

        function addToCart() {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const item = {
                id: 1,
                nome: '<?php echo $product["nome"]; ?>',
                preco: <?php echo $product['preco']; ?>,
                qty: 1,
                imagem: '<?php echo $product["imagem"]; ?>'
            };
            cart.push(item);
            localStorage.setItem('cart', JSON.stringify(cart));
            alert('✅ Produto adicionado ao carrinho!');
        }

        function toggleWishlist() {
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            if (wishlist.includes(1)) {
                wishlist = wishlist.filter(id => id !== 1);
                document.getElementById('wishlistBtn').innerHTML = '<i class="far fa-heart mr-2"></i>Favoritar';
            } else {
                wishlist.push(1);
                document.getElementById('wishlistBtn').innerHTML = '<i class="fas fa-heart mr-2"></i>Favoritado';
            }
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
        }
    </script>

</body>
</html>
