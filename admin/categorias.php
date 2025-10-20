<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Buscar categorias
$categorias = $pdo->query("
    SELECT c.*, COUNT(p.id) as total_produtos 
    FROM categorias c 
    LEFT JOIN produtos p ON c.id = p.categoria_id 
    GROUP BY c.id 
    ORDER BY c.ordem, c.nome
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-900 text-slate-100">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-purple-800/30 sticky top-0 z-40">
        <div class="px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="sidebarToggle" class="md:hidden text-xl text-slate-400 hover:text-purple-400 transition">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/admin" class="text-lg md:text-2xl font-black gradient-text flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <span class="hidden sm:inline">Wazzy Pods</span>
                </a>
                <span class="text-xs md:text-sm text-slate-400 hidden sm:inline">/ Categorias</span>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <span class="text-xs md:text-sm text-slate-400 hidden sm:inline">Olá, <?php echo $_SESSION['admin_nome'] ?? 'Admin'; ?></span>
                <a href="logout.php" class="btn-danger text-xs md:text-sm px-2 py-1 md:px-4 md:py-2">
                    <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Sair</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex relative">
        <!-- Overlay para mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 hidden md:hidden z-30"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed md:relative w-64 h-screen md:h-auto min-h-screen bg-slate-950 border-r border-purple-800/30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
            <nav class="p-4 space-y-2">
                <a href="/admin" class="nav-item">
                    <i class="fas fa-dashboard w-5"></i> <span>Dashboard</span>
                </a>
                <a href="produtos.php" class="nav-item">
                    <i class="fas fa-box w-5"></i> <span>Produtos</span>
                </a>
                <a href="categorias.php" class="nav-item active">
                    <i class="fas fa-tags w-5"></i> <span>Categorias</span>
                </a>
                <a href="pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart w-5"></i> <span>Pedidos</span>
                </a>
                <a href="clientes.php" class="nav-item">
                    <i class="fas fa-users w-5"></i> <span>Clientes</span>
                </a>
                <a href="integracao.php" class="nav-item">
                    <i class="fas fa-plug w-5"></i> <span>Integrações</span>
                </a>
                <a href="configuracoes.php" class="nav-item">
                    <i class="fas fa-cog w-5"></i> <span>Configurações</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full md:w-auto p-4 md:p-8 overflow-x-hidden">
            <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">Gerenciar Categorias</h1>
                <button onclick="novaCategoria()" class="btn-primary text-xs md:text-sm w-full sm:w-auto">
                    <i class="fas fa-plus"></i> <span>Nova Categoria</span>
                </button>
            </div>

            <!-- Grid de Categorias -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="bg-slate-800/50 rounded-lg md:rounded-xl p-4 md:p-6 backdrop-blur-sm border border-purple-800/30 hover:border-purple-600/50 transition">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-2 md:gap-3 min-w-0">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center flex-shrink-0" 
                                     style="background: <?php echo $categoria['cor']; ?>20; color: <?php echo $categoria['cor']; ?>">
                                    <i class="<?php echo $categoria['icone']; ?> text-lg md:text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm md:text-lg font-bold truncate"><?php echo htmlspecialchars($categoria['nome']); ?></h3>
                                    <p class="text-xs text-slate-400">Ordem: <?php echo $categoria['ordem']; ?></p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" <?php echo $categoria['ativo'] ? 'checked' : ''; ?> 
                                       onchange="toggleCategoriaStatus(<?php echo $categoria['id']; ?>, this.checked)" 
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        
                        <p class="text-xs md:text-sm text-slate-400 mb-4 line-clamp-2">
                            <?php echo htmlspecialchars($categoria['descricao'] ?? 'Sem descrição'); ?>
                        </p>
                        
                        <div class="flex justify-between items-center pt-4 border-t border-slate-700">
                            <span class="text-xs md:text-sm text-purple-400 truncate">
                                <i class="fas fa-box"></i> <?php echo $categoria['total_produtos']; ?> produtos
                            </span>
                            <div class="flex gap-1 md:gap-2 flex-shrink-0">
                                <button onclick="editarCategoria(<?php echo htmlspecialchars(json_encode($categoria)); ?>)" 
                                        class="p-1 md:p-2 bg-blue-900/30 text-blue-400 rounded hover:bg-blue-900/50 transition" title="Editar">
                                    <i class="fas fa-edit text-xs md:text-sm"></i>
                                </button>
                                <button onclick="deletarCategoria(<?php echo $categoria['id']; ?>, '<?php echo htmlspecialchars($categoria['nome']); ?>')" 
                                        class="p-1 md:p-2 bg-red-900/30 text-red-400 rounded hover:bg-red-900/50 transition" title="Deletar">
                                    <i class="fas fa-trash text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modal de Categoria -->
    <div id="categoriaModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-lg md:rounded-xl p-4 md:p-6 w-full max-w-md border border-purple-800/30">
            <h2 class="text-xl md:text-2xl font-bold gradient-text mb-4" id="modalTitle">Nova Categoria</h2>
            
            <form id="categoriaForm" class="space-y-4">
                <input type="hidden" id="categoriaId" name="id">
                
                <div>
                    <label class="block text-xs md:text-sm font-medium text-purple-400 mb-2">Nome *</label>
                    <input type="text" id="categoriaNome" name="nome" required 
                           class="w-full px-3 md:px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                </div>
                
                <div>
                    <label class="block text-xs md:text-sm font-medium text-purple-400 mb-2">Descrição</label>
                    <textarea id="categoriaDescricao" name="descricao" rows="3" 
                              class="w-full px-3 md:px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-purple-400 mb-2">Ícone</label>
                        <input type="text" id="categoriaIcone" name="icone" placeholder="fas fa-box" 
                               class="w-full px-3 md:px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                    </div>
                    
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-purple-400 mb-2">Cor</label>
                        <input type="color" id="categoriaCor" name="cor" value="#8B5CF6" 
                               class="w-full h-9 md:h-10 bg-slate-800/50 border border-purple-800/30 rounded-lg cursor-pointer">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs md:text-sm font-medium text-purple-400 mb-2">Ordem</label>
                    <input type="number" id="categoriaOrdem" name="ordem" value="0" 
                           class="w-full px-3 md:px-4 py-2 bg-slate-800/50 border border-purple-800/30 rounded-lg text-xs md:text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                </div>
                
                <div class="flex gap-3 md:gap-4 pt-4">
                    <button type="button" onclick="fecharModal()" class="flex-1 btn-secondary text-xs md:text-sm">
                        <i class="fas fa-times"></i> <span>Cancelar</span>
                    </button>
                    <button type="submit" class="flex-1 btn-primary text-xs md:text-sm">
                        <i class="fas fa-save"></i> <span>Salvar</span>
                    </button>
                </div>
            </form>
        </div>
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
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            transition: all 0.3s;
            text-decoration: none;
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
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }
        
        .btn-secondary {
            background: rgba(100, 116, 139, 0.2);
            color: #94a3b8;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid rgba(100, 116, 139, 0.3);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            border-radius: 0.5rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
    </style>

    <script>
        // Menu mobile toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        sidebarOverlay.addEventListener('click', closeSidebar);

        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });

        function novaCategoria() {
            document.getElementById('modalTitle').textContent = 'Nova Categoria';
            document.getElementById('categoriaForm').reset();
            document.getElementById('categoriaId').value = '';
            document.getElementById('categoriaModal').classList.remove('hidden');
        }
        
        function editarCategoria(categoria) {
            document.getElementById('modalTitle').textContent = 'Editar Categoria';
            document.getElementById('categoriaId').value = categoria.id;
            document.getElementById('categoriaNome').value = categoria.nome;
            document.getElementById('categoriaDescricao').value = categoria.descricao || '';
            document.getElementById('categoriaIcone').value = categoria.icone;
            document.getElementById('categoriaCor').value = categoria.cor;
            document.getElementById('categoriaOrdem').value = categoria.ordem;
            document.getElementById('categoriaModal').classList.remove('hidden');
        }
        
        function fecharModal() {
            document.getElementById('categoriaModal').classList.add('hidden');
        }
        
        document.getElementById('categoriaForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('../api/categorias.php', {
                    method: data.id ? 'PUT' : 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.id ? 'Categoria atualizada!' : 'Categoria criada!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    location.reload();
                } else {
                    throw new Error(result.message || 'Erro ao salvar categoria');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message
                });
            }
        });
        
        function toggleCategoriaStatus(id, status) {
            fetch('../api/categorias.php', {
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
        
        function deletarCategoria(id, nome) {
            Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja excluir "${nome}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/categorias.php', {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: id})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Excluída!', 'Categoria removida com sucesso.', 'success')
                            .then(() => location.reload());
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
