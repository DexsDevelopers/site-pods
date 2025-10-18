<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Buscar produtos
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = '(p.nome LIKE ? OR p.descricao LIKE ? OR p.sku LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($categoria) {
    $where[] = 'p.categoria_id = ?';
    $params[] = $categoria;
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM produtos p WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Buscar produtos com categoria
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

// Buscar categorias para o filtro
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY ordem, nome")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/admin" class="text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span>Wazzy Pods</span>
                </a>
                <span class="text-slate-400">/ Produtos</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-slate-400">Olá, <?php echo $_SESSION['admin_nome'] ?? 'Admin'; ?></span>
                <a href="logout.php" class="btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-950 border-r border-purple-800/30">
            <nav class="p-4">
                <a href="/admin" class="nav-item">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
                <a href="produtos.php" class="nav-item active">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="categorias.php" class="nav-item">
                    <i class="fas fa-tags"></i> Categorias
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold gradient-text">Gerenciar Produtos</h1>
                <button onclick="novoProduto()" class="btn-primary">
                    <i class="fas fa-plus"></i> Novo Produto
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-slate-800/50 rounded-xl p-6 mb-6 backdrop-blur-sm border border-purple-800/30">
                <form method="GET" class="flex gap-4">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar produtos..." 
                           class="flex-1 px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                    
                    <select name="categoria" class="px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </form>
            </div>

            <!-- Tabela de Produtos -->
            <div class="bg-slate-800/50 rounded-xl overflow-hidden backdrop-blur-sm border border-purple-800/30">
                <table class="w-full">
                    <thead class="bg-slate-900/50 border-b border-purple-800/30">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Imagem</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Preço</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Estoque</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-purple-400 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-800/20">
                        <?php if (empty($produtos)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fas fa-box-open text-4xl mb-4"></i>
                                    <p>Nenhum produto encontrado</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $produto): ?>
                                <tr class="hover:bg-slate-900/30 transition">
                                    <td class="px-6 py-4 text-sm">#<?php echo $produto['id']; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="w-12 h-12 bg-slate-700 rounded-lg overflow-hidden">
                                            <?php if ($produto['imagem']): ?>
                                                <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-slate-500">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($produto['nome']); ?></p>
                                            <p class="text-xs text-slate-400">SKU: <?php echo htmlspecialchars($produto['sku'] ?? 'N/A'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 bg-purple-900/30 text-purple-400 rounded-lg text-xs">
                                            <?php echo htmlspecialchars($produto['categoria_nome'] ?? 'Sem categoria'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                            <?php if ($produto['preco_promocional']): ?>
                                                <p class="text-xs text-green-400">R$ <?php echo number_format($produto['preco_promocional'], 2, ',', '.'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 <?php echo $produto['estoque'] > 10 ? 'bg-green-900/30 text-green-400' : ($produto['estoque'] > 0 ? 'bg-yellow-900/30 text-yellow-400' : 'bg-red-900/30 text-red-400'); ?> rounded-lg text-xs">
                                            <?php echo $produto['estoque']; ?> un
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" <?php echo $produto['ativo'] ? 'checked' : ''; ?> 
                                                   onchange="toggleStatus(<?php echo $produto['id']; ?>, this.checked)" 
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button onclick="editarProduto(<?php echo $produto['id']; ?>)" 
                                                    class="p-2 bg-blue-900/30 text-blue-400 rounded-lg hover:bg-blue-900/50 transition">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deletarProduto(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')" 
                                                    class="p-2 bg-red-900/30 text-red-400 rounded-lg hover:bg-red-900/50 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&categoria=<?php echo urlencode($categoria); ?>" 
                           class="px-4 py-2 rounded-lg <?php echo $i == $page ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?> transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #a78bfa;
        }
        
        .nav-item.active {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
            border-left: 3px solid #a78bfa;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
    </style>

    <script>
        function novoProduto() {
            window.location.href = 'produto-form.php';
        }
        
        function editarProduto(id) {
            window.location.href = 'produto-form.php?id=' + id;
        }
        
        function toggleStatus(id, status) {
            fetch('../api/produtos.php', {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, ativo: status})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status atualizado!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        function deletarProduto(id, nome) {
            Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja excluir o produto "${nome}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/produtos.php', {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: id})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Excluído!', 'Produto removido com sucesso.', 'success')
                            .then(() => location.reload());
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
