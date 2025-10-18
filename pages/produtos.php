<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$preco_min = $_GET['preco_min'] ?? '';
$preco_max = $_GET['preco_max'] ?? '';
$ordenar = $_GET['ordenar'] ?? 'novo';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Construir WHERE
$where = ['p.ativo = 1'];
$params = [];

if (!empty($busca)) {
    $where[] = "(p.nome LIKE ? OR p.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if (!empty($categoria)) {
    $where[] = "p.categoria_id = ?";
    $params[] = intval($categoria);
}

if (!empty($preco_min)) {
    $where[] = "p.preco_promocional >= ?";
    $params[] = floatval($preco_min);
}

if (!empty($preco_max)) {
    $where[] = "p.preco_promocional <= ?";
    $params[] = floatval($preco_max);
}

$whereClause = implode(' AND ', $where);

// Ordenação
$orderBy = 'p.created_at DESC';
if ($ordenar === 'preco_baixo') $orderBy = 'p.preco_promocional ASC';
elseif ($ordenar === 'preco_alto') $orderBy = 'p.preco_promocional DESC';
elseif ($ordenar === 'popular') $orderBy = 'p.vendas DESC';
elseif ($ordenar === 'avaliacao') $orderBy = 'p.avaliacao_media DESC';

// Contar total
$countSql = "SELECT COUNT(*) as total FROM produtos p WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Buscar produtos
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para filtro
$stmtCat = $pdo->prepare("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Produtos - Wazzy Pods</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100" style="background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);">
    <?php include '../header.php'; ?>

    <div class="pt-32 pb-20 px-4">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-5xl font-black mb-2"><span class="gradient-text">Buscar Produtos</span></h1>
            <p class="text-slate-400 mb-8"><?php echo $total; ?> produtos encontrados</p>

            <div class="grid lg:grid-cols-4 gap-8">
                <!-- FILTROS LATERAL -->
                <div class="lg:col-span-1">
                    <form method="GET" class="glass rounded-xl p-6 border border-purple-800/30 space-y-6 sticky top-32">
                        <!-- Busca -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Buscar</label>
                            <input type="text" name="busca" value="<?php echo htmlspecialchars($busca); ?>" 
                                   placeholder="Nome ou descrição..."
                                   class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                        </div>

                        <!-- Categoria -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Categoria</label>
                            <select name="categoria" class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                <option value="">Todas as categorias</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Preço -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Preço (R$)</label>
                            <div class="flex gap-2">
                                <input type="number" name="preco_min" value="<?php echo htmlspecialchars($preco_min); ?>" 
                                       placeholder="Mín" step="0.01"
                                       class="flex-1 px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                <input type="number" name="preco_max" value="<?php echo htmlspecialchars($preco_max); ?>" 
                                       placeholder="Máx" step="0.01"
                                       class="flex-1 px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                            </div>
                        </div>

                        <!-- Ordenação -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Ordenar por</label>
                            <select name="ordenar" class="w-full px-3 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-sm focus:outline-none focus:border-purple-600">
                                <option value="novo" <?php echo $ordenar === 'novo' ? 'selected' : ''; ?>>Mais Novo</option>
                                <option value="preco_baixo" <?php echo $ordenar === 'preco_baixo' ? 'selected' : ''; ?>>Menor Preço</option>
                                <option value="preco_alto" <?php echo $ordenar === 'preco_alto' ? 'selected' : ''; ?>>Maior Preço</option>
                                <option value="popular" <?php echo $ordenar === 'popular' ? 'selected' : ''; ?>>Mais Vendidos</option>
                                <option value="avaliacao" <?php echo $ordenar === 'avaliacao' ? 'selected' : ''; ?>>Melhor Avaliação</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>

                        <?php if (!empty($busca) || !empty($categoria) || !empty($preco_min) || !empty($preco_max)): ?>
                            <a href="produtos.php" class="block text-center py-2 px-3 bg-slate-700/50 rounded-lg text-slate-300 hover:text-slate-100 transition text-sm">
                                <i class="fas fa-times mr-1"></i> Limpar Filtros
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- PRODUTOS -->
                <div class="lg:col-span-3">
                    <?php if (empty($produtos)): ?>
                        <div class="glass rounded-xl p-12 text-center border border-purple-800/30">
                            <i class="fas fa-inbox text-6xl text-slate-600 mb-4"></i>
                            <h2 class="text-2xl font-bold mb-2">Nenhum produto encontrado</h2>
                            <p class="text-slate-400">Tente ajustar seus filtros</p>
                        </div>
                    <?php else: ?>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($produtos as $produto): ?>
                                <div class="glass rounded-xl overflow-hidden border border-purple-800/30 hover:border-purple-600/50 transition group cursor-pointer" onclick="location.href='/pages/product-detail.php?id=<?php echo $produto['id']; ?>'">
                                    <!-- Imagem -->
                                    <div class="relative overflow-hidden h-48 bg-slate-800">
                                        <img src="<?php echo htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/300'); ?>" 
                                             alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                             class="w-full h-full object-cover group-hover:scale-110 transition">
                                        <?php if ($produto['preco_promocional'] < $produto['preco']): ?>
                                            <div class="absolute top-2 right-2 bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                                                -<?php echo round((1 - $produto['preco_promocional'] / $produto['preco']) * 100); ?>%
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Info -->
                                    <div class="p-4">
                                        <p class="text-xs text-purple-400 mb-1"><?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Sem categoria'); ?></p>
                                        <h3 class="font-bold text-sm mb-2 line-clamp-2"><?php echo htmlspecialchars($produto['nome']); ?></h3>

                                        <!-- Avaliação -->
                                        <div class="flex items-center gap-1 mb-3">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="fas fa-star text-xs <?php echo $i < round($produto['avaliacao_media']) ? 'text-yellow-400' : 'text-slate-600'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="text-xs text-slate-400"><?php echo round($produto['avaliacao_media'], 1); ?></span>
                                        </div>

                                        <!-- Preços -->
                                        <div class="mb-3">
                                            <?php if ($produto['preco_promocional'] < $produto['preco']): ?>
                                                <p class="text-xs text-slate-500 line-through">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                                <p class="text-lg font-bold gradient-text">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></p>
                                            <?php else: ?>
                                                <p class="text-lg font-bold gradient-text">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Estoque -->
                                        <?php if ($produto['estoque'] > 0): ?>
                                            <button class="w-full py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-sm hover:shadow-lg transition"
                                                    onclick="addToCart(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['preco_promocional']; ?>); event.stopPropagation();">
                                                <i class="fas fa-shopping-cart mr-1"></i> Adicionar
                                            </button>
                                        <?php else: ?>
                                            <button class="w-full py-2 bg-slate-700 rounded-lg font-bold text-sm" disabled>
                                                Fora de Estoque
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- PAGINAÇÃO -->
                        <?php if ($totalPages > 1): ?>
                            <div class="mt-12 flex justify-center gap-2">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                       class="px-4 py-2 rounded-lg <?php echo $i == $page ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'bg-slate-800/50 text-slate-400 hover:text-slate-100'; ?> transition">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script>
        function addToCart(id, nome, preco) {
            const item = {
                id: id,
                nome: nome,
                preco_final: preco,
                quantity: 1,
                imagem: 'https://via.placeholder.com/100'
            };
            cart.add(item);
            
            Swal.fire({
                icon: 'success',
                title: 'Adicionado!',
                text: nome + ' foi adicionado ao carrinho',
                timer: 2000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        }

        // Carrinho (mesmo do index.php)
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

        const cart = new Cart();
        cart.updateBadge();
    </script>
</body>
</html>
