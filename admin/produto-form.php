<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$produto = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        header('Location: produtos.php');
        exit;
    }
}

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY ordem, nome")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $produto ? 'Editar' : 'Novo'; ?> Produto - Wazzy Pods Admin</title>
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
                <span class="text-slate-400">/ Produtos / <?php echo $produto ? 'Editar' : 'Novo'; ?></span>
            </div>
            <a href="produtos.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold gradient-text mb-8">
                <?php echo $produto ? 'Editar Produto' : 'Novo Produto'; ?>
            </h1>

            <form id="produtoForm" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $produto['id'] ?? ''; ?>">

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Informações Básicas -->
                    <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                        <h2 class="text-xl font-bold mb-4 gradient-text">Informações Básicas</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Nome do Produto *</label>
                                <input type="text" name="nome" required 
                                       value="<?php echo htmlspecialchars($produto['nome'] ?? ''); ?>"
                                       class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Categoria *</label>
                                <select name="categoria_id" required 
                                        class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 focus:outline-none focus:border-purple-600">
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo ($produto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">SKU</label>
                                <input type="text" name="sku" 
                                       value="<?php echo htmlspecialchars($produto['sku'] ?? ''); ?>"
                                       placeholder="POD-XXX-000"
                                       class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Descrição Curta</label>
                                <textarea name="descricao_curta" rows="3" 
                                          class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600"><?php echo htmlspecialchars($produto['descricao_curta'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Preços e Estoque -->
                    <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                        <h2 class="text-xl font-bold mb-4 gradient-text">Preços e Estoque</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Preço Regular *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400">R$</span>
                                    <input type="number" name="preco" required step="0.01" 
                                           value="<?php echo $produto['preco'] ?? ''; ?>"
                                           class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Preço Promocional</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-slate-400">R$</span>
                                    <input type="number" name="preco_promocional" step="0.01" 
                                           value="<?php echo $produto['preco_promocional'] ?? ''; ?>"
                                           class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-purple-400 mb-2">Estoque *</label>
                                <input type="number" name="estoque" required 
                                       value="<?php echo $produto['estoque'] ?? 0; ?>"
                                       class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="destaque" value="1" 
                                           <?php echo ($produto['destaque'] ?? 0) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-purple-600 bg-slate-900 border-purple-800 rounded focus:ring-purple-600">
                                    <span class="text-sm text-purple-400">Produto em Destaque</span>
                                </label>

                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="ativo" value="1" 
                                           <?php echo ($produto['ativo'] ?? 1) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-purple-600 bg-slate-900 border-purple-800 rounded focus:ring-purple-600">
                                    <span class="text-sm text-purple-400">Produto Ativo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descrição Completa -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Descrição Completa</h2>
                    <textarea name="descricao" rows="6" 
                              class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600"><?php echo htmlspecialchars($produto['descricao'] ?? ''); ?></textarea>
                </div>

                <!-- Imagem -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Imagem do Produto</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-purple-400 mb-2">URL da Imagem</label>
                            <input type="text" name="imagem" 
                                   value="<?php echo htmlspecialchars($produto['imagem'] ?? ''); ?>"
                                   placeholder="/uploads/products/nome-produto.jpg"
                                   class="w-full px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600">
                        </div>
                        
                        <?php if ($produto && $produto['imagem']): ?>
                            <div class="mt-4">
                                <p class="text-sm text-purple-400 mb-2">Preview:</p>
                                <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="Preview" 
                                     class="w-32 h-32 object-cover rounded-lg border border-purple-800/30">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Características (JSON) -->
                <div class="bg-slate-800/50 rounded-xl p-6 backdrop-blur-sm border border-purple-800/30">
                    <h2 class="text-xl font-bold mb-4 gradient-text">Características</h2>
                    <p class="text-sm text-slate-400 mb-4">Adicione características específicas do produto</p>
                    
                    <div id="caracteristicas" class="space-y-3">
                        <!-- Características serão adicionadas dinamicamente -->
                    </div>
                    
                    <button type="button" onclick="adicionarCaracteristica()" class="mt-4 btn-secondary">
                        <i class="fas fa-plus"></i> Adicionar Característica
                    </button>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 justify-end">
                    <a href="produtos.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Produto
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
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.3);
            color: #cbd5e1;
        }
    </style>

    <script>
        // Carregar características existentes
        <?php if ($produto && $produto['caracteristicas']): ?>
            const caracteristicasExistentes = <?php echo $produto['caracteristicas'] ?: '{}'; ?>;
        <?php else: ?>
            const caracteristicasExistentes = {};
        <?php endif; ?>
        
        // Renderizar características existentes
        Object.entries(caracteristicasExistentes).forEach(([key, value]) => {
            adicionarCaracteristica(key, value);
        });
        
        function adicionarCaracteristica(key = '', value = '') {
            const container = document.getElementById('caracteristicas');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="text" placeholder="Nome (ex: Puffs)" value="${key}"
                       class="flex-1 px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600 carac-key">
                <input type="text" placeholder="Valor (ex: 5000)" value="${value}"
                       class="flex-1 px-4 py-2 bg-slate-900/50 border border-purple-800/30 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:border-purple-600 carac-value">
                <button type="button" onclick="this.parentElement.remove()" 
                        class="px-3 py-2 bg-red-900/30 text-red-400 rounded-lg hover:bg-red-900/50 transition">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        // Adicionar algumas características padrão para pods
        if (Object.keys(caracteristicasExistentes).length === 0) {
            adicionarCaracteristica('puffs', '');
            adicionarCaracteristica('nicotina', '');
            adicionarCaracteristica('sabor', '');
        }
        
        // Enviar formulário
        document.getElementById('produtoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Coletar características
            const caracteristicas = {};
            document.querySelectorAll('#caracteristicas > div').forEach(div => {
                const key = div.querySelector('.carac-key').value;
                const value = div.querySelector('.carac-value').value;
                if (key && value) {
                    caracteristicas[key] = value;
                }
            });
            
            // Preparar dados
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.caracteristicas = caracteristicas;
            
            // Converter checkboxes
            data.destaque = data.destaque ? 1 : 0;
            data.ativo = data.ativo ? 1 : 0;
            
            try {
                const response = await fetch('../api/produtos.php', {
                    method: data.id ? 'PUT' : 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.id ? 'Produto atualizado com sucesso!' : 'Produto criado com sucesso!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.location.href = 'produtos.php';
                } else {
                    throw new Error(result.message || 'Erro ao salvar produto');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message
                });
            }
        });
    </script>
</body>
</html>
