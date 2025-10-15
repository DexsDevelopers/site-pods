<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Simular produto detalhado (em produção, viria do BD)
$product_id = $_GET['id'] ?? 1;

$product = [
    'id' => 1,
    'nome' => 'Vapor Premium X-01',
    'categoria' => 'Vaporizadores',
    'preco' => 299.90,
    'preco_original' => 429.90,
    'rating' => 4.8,
    'reviews_count' => 147,
    'imagem' => 'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop',
    'imagens' => [
        'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=600&h=600&fit=crop',
        'https://images.unsplash.com/photo-1617638924702-92d37d439220?w=600&h=600&fit=crop',
    ],
    'descricao_curta' => 'Vaporizador de última geração com tecnologia avançada',
    'descricao_completa' => 'Vaporizador Premium X-01 é o topo de linha com tecnologia revolucionária. Bateria de longa duração, design elegante e performance superior.',
    'estoque' => 12,
    'garantia' => '12 meses',
    'frete' => 'Frete grátis acima de R$ 100',
    'especificacoes' => [
        'Bateria' => '5000mAh Integrada',
        'Potência' => '200W Máximo',
        'Bobina' => 'Mesh Dual',
        'Peso' => '185g',
        'Dimensões' => '120 x 60 x 50mm',
        'Compatibilidade' => 'Universal',
    ],
    'ingredientes' => [
        ['nome' => 'Sabor', 'valor' => 'Frutas Tropicais'],
        ['nome' => 'Nicotina', 'valor' => '3mg/mL'],
        ['nome' => 'VG/PG', 'valor' => '70/30'],
        ['nome' => 'Capacidade', 'valor' => '5mL'],
    ],
    'incluso' => [
        'Vaporizador',
        'Carregador USB-C',
        'Bobina de Reposição',
        'Manual em PT-BR',
        'Estojo de Proteção',
    ]
];

$reviews = [
    ['nome' => 'João Silva', 'rating' => 5, 'texto' => 'Produto excepcional! Chegou rápido e bem embalado.', 'verificado' => true],
    ['nome' => 'Maria Santos', 'rating' => 4, 'texto' => 'Muito bom, recomendo!', 'verificado' => true],
    ['nome' => 'Pedro Costa', 'rating' => 5, 'texto' => 'Melhor vaper que já tive!', 'verificado' => true],
];

$relacionados = [
    ['id' => 2, 'nome' => 'Aero Compact 2024', 'preco' => 199.90],
    ['id' => 4, 'nome' => 'Caso Ultra Slim', 'preco' => 89.90],
    ['id' => 5, 'nome' => 'Kit Limpeza Premium', 'preco' => 49.90],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['nome']; ?> - TechVapor</title>
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
                <i class="fas fa-cloud mr-2"></i>TechVapor
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
                        <img id="mainImage" src="<?php echo $product['imagem']; ?>" class="max-w-full max-h-full object-cover rounded" alt="<?php echo $product['nome']; ?>">
                    </div>
                    <div class="flex gap-2">
                        <?php foreach ($product['imagens'] as $img): ?>
                            <img src="<?php echo $img; ?>" onclick="document.getElementById('mainImage').src='<?php echo $img; ?>'" class="w-20 h-20 rounded cursor-pointer border border-purple-600 hover:border-pink-600 transition" alt="">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Detalhes -->
                <div data-aos="fade-left">
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-purple-600 text-white text-sm rounded-full"><?php echo $product['categoria']; ?></span>
                    </div>

                    <h1 class="text-4xl font-black mb-4"><?php echo $product['nome']; ?></h1>

                    <!-- Rating -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex gap-1">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < $product['rating'] ? 'text-yellow-400' : 'text-slate-600'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-yellow-400 font-bold"><?php echo $product['rating']; ?></span>
                        <span class="text-slate-400">(<?php echo $product['reviews_count']; ?> avaliações)</span>
                    </div>

                    <!-- Preço -->
                    <div class="mb-6">
                        <div class="text-4xl font-black gradient-text mb-2">R$ <?php echo number_format($product['preco'], 2, ',', '.'); ?></div>
                        <div class="text-slate-400 line-through">R$ <?php echo number_format($product['preco_original'], 2, ',', '.'); ?></div>
                        <div class="text-green-400 font-bold mt-2">
                            <i class="fas fa-tag mr-2"></i><?php echo round((1 - $product['preco']/$product['preco_original']) * 100); ?>% OFF
                        </div>
                    </div>

                    <!-- Garantia & Frete -->
                    <div class="glass rounded-lg p-6 mb-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt text-green-400 text-xl"></i>
                            <div>
                                <p class="font-bold">Garantia</p>
                                <p class="text-slate-400 text-sm"><?php echo $product['garantia']; ?> Garantidos</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-truck text-blue-400 text-xl"></i>
                            <div>
                                <p class="font-bold">Frete Grátis</p>
                                <p class="text-slate-400 text-sm"><?php echo $product['frete']; ?></p>
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
                        <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                            <div class="bg-gradient-to-r from-purple-600 to-pink-600 h-2 rounded-full" style="width: <?php echo ($product['estoque'] / 50) * 100; ?>%;"></div>
                        </div>
                        <p class="text-sm">
                            <span class="text-green-400 font-bold"><?php echo $product['estoque']; ?></span> 
                            <span class="text-slate-400">unidades em estoque</span>
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
                    <?php foreach ($product['especificacoes'] as $spec => $valor): ?>
                        <div class="glass p-4 rounded-lg">
                            <p class="text-slate-400 text-sm"><?php echo $spec; ?></p>
                            <p class="font-bold text-lg"><?php echo $valor; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="ingredientes" class="hidden grid md:grid-cols-2 gap-4">
                    <?php foreach ($product['ingredientes'] as $ing): ?>
                        <div class="glass p-4 rounded-lg">
                            <p class="text-slate-400 text-sm"><?php echo $ing['nome']; ?></p>
                            <p class="font-bold text-lg"><?php echo $ing['valor']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="incluso" class="hidden space-y-3">
                    <?php foreach ($product['incluso'] as $item): ?>
                        <div class="glass p-4 rounded-lg flex items-center gap-3">
                            <i class="fas fa-check-circle text-green-400"></i>
                            <span><?php echo $item; ?></span>
                        </div>
                    <?php endforeach; ?>
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
