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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
            $nome = $_POST['nome'] ?? null;
            $descricao = $_POST['descricao'] ?? null;
            $icon = $_POST['icon'] ?? null;
            
            $stmt = $conn->prepare(
                "INSERT INTO categories (nome, descricao, icon) VALUES (?, ?, ?)"
            );
            if ($stmt->execute([$nome, $descricao, $icon])) {
                echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Categoria adicionada com sucesso!</div>';
                header('Refresh: 2; url=?page=categories');
            }
        }
        
        if ($action === 'delete' && $_GET['id']) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$_GET['id']])) {
                echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Categoria deletada!</div>';
                header('Refresh: 1; url=?page=categories');
            }
        }
        
        if ($action === 'add') {
            ?>
            <div class="glass border border-purple-600/30 rounded-lg p-8">
                <h3 class="text-2xl font-black mb-6">➕ Nova Categoria</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Nome</label>
                        <input type="text" name="nome" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Descrição</label>
                        <textarea name="descricao" rows="3" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded"></textarea>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Ícone (emoji)</label>
                        <input type="text" name="icon" placeholder="☁️" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded">
                    </div>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold">💾 Salvar</button>
                </form>
            </div>
            <?php
        } else {
            $stmt = $conn->prepare("SELECT * FROM categories ORDER BY nome");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black">📂 Categorias</h3>
                <a href="?page=categories&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold">➕ Nova Categoria</a>
            </div>
            
            <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="text-left px-6 py-3">Nome</th>
                            <th class="text-left px-6 py-3">Descrição</th>
                            <th class="text-left px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-bold"><?php echo htmlspecialchars($cat['nome']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars(substr($cat['descricao'] ?? '', 0, 50)); ?></td>
                            <td class="px-6 py-4">
                                <a href="?page=categories&action=delete&id=<?php echo $cat['id']; ?>" class="px-3 py-1 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition" onclick="return confirm('Tem certeza?')">🗑️ Deletar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    } catch (Exception $e) {
        echo '<div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded mb-6">Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>
