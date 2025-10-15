<div class="space-y-6">
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    try {
        include '../../includes/config.php';
        include '../../includes/db.php';
        include '../../includes/helpers.php';
        
        $conn = Database::getConnection();
        
        $action = $_GET['action'] ?? null;
        $product_id = $_GET['id'] ?? null;
        
        // Processar formulário
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? null;
            $descricao = $_POST['descricao'] ?? null;
            $preco = $_POST['preco'] ?? null;
            $estoque = $_POST['estoque'] ?? null;
            $categoria_id = $_POST['categoria'] ?? null;
            $imagem = $_POST['imagem'] ?? null;
            
            if ($action === 'add') {
                $stmt = $conn->prepare(
                    "INSERT INTO products (nome, descricao, preco, estoque, categoria_id, imagem) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $result = $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria_id, $imagem]);
                
                if ($result) {
                    echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Produto adicionado com sucesso!</div>';
                    header('Refresh: 2; url=?page=products');
                } else {
                    echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded mb-6">❌ Erro ao adicionar produto</div>';
                }
            } elseif ($action === 'edit' && $product_id) {
                $stmt = $conn->prepare(
                    "UPDATE products SET nome=?, descricao=?, preco=?, estoque=?, categoria_id=?, imagem=? 
                     WHERE id=?"
                );
                $result = $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria_id, $imagem, $product_id]);
                
                if ($result) {
                    echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Produto atualizado com sucesso!</div>';
                    header('Refresh: 2; url=?page=products');
                }
            }
        }
        
        // Processar deleção
        if ($action === 'delete' && $product_id) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$product_id])) {
                echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Produto deletado com sucesso!</div>';
                header('Refresh: 1; url=?page=products');
            }
        }
        
        if ($action === 'add' || $action === 'edit') {
            $product = null;
            if ($action === 'edit' && $product_id) {
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Buscar categorias
            $stmt = $conn->prepare("SELECT id, nome FROM categories ORDER BY nome");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="glass border border-purple-600/30 rounded-lg p-8">
                <h3 class="text-2xl font-black mb-6">
                    <?php echo $action === 'add' ? '➕ Novo Produto' : '✏️ Editar Produto'; ?>
                </h3>

                <form method="POST" class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 mb-2">Nome do Produto</label>
                            <input type="text" name="nome" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ex: Vapor Premium X-01" value="<?php echo htmlspecialchars($product['nome'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="block text-slate-300 mb-2">Categoria</label>
                            <select name="categoria" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($product['categoria_id'] ?? null) == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nome']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-slate-300 mb-2">Preço (R$)</label>
                            <input type="number" name="preco" step="0.01" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="199.90" value="<?php echo $product['preco'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="block text-slate-300 mb-2">Estoque</label>
                            <input type="number" name="estoque" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="10" value="<?php echo $product['estoque'] ?? ''; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Descrição</label>
                        <textarea name="descricao" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Descrição do produto..." rows="4"><?php echo htmlspecialchars($product['descricao'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">URL da Imagem</label>
                        <input type="text" name="imagem" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="https://..." value="<?php echo htmlspecialchars($product['imagem'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                        <?php echo $action === 'add' ? '➕ Adicionar' : '✏️ Atualizar'; ?>
                    </button>
                </form>
            </div>
            <?php
        } else {
            // Lista de Produtos
            $stmt = $conn->prepare(
                "SELECT p.*, c.nome as categoria_nome 
                 FROM products p 
                 LEFT JOIN categories c ON p.categoria_id = c.id 
                 ORDER BY p.criado_em DESC 
                 LIMIT 100"
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-black">📦 Gerenciar Produtos</h3>
                    <a href="?page=products&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Novo Produto
                    </a>
                </div>

                <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-800/50 border-b border-slate-700">
                            <tr>
                                <th class="text-left px-6 py-3">Produto</th>
                                <th class="text-left px-6 py-3">Categoria</th>
                                <th class="text-left px-6 py-3">Preço</th>
                                <th class="text-left px-6 py-3">Estoque</th>
                                <th class="text-left px-6 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $prod): ?>
                            <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                                <td class="px-6 py-4 font-bold"><?php echo htmlspecialchars($prod['nome']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($prod['categoria_nome'] ?? 'Sem categoria'); ?></td>
                                <td class="px-6 py-4">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                                <td class="px-6 py-4"><span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-xs font-bold"><?php echo $prod['estoque']; ?></span></td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="?page=products&action=edit&id=<?php echo $prod['id']; ?>" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</a>
                                    <a href="?page=products&action=delete&id=<?php echo $prod['id']; ?>" class="px-3 py-1 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition" onclick="return confirm('Tem certeza?')">🗑️ Deletar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        }
    } catch (Exception $e) {
        echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded mb-6">Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>
