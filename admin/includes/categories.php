<div class="space-y-6">
    <?php
    include '../../includes/db.php';
    $db = Database::getInstance();
    
    $action = $_GET['action'] ?? null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
        $nome = $_POST['nome'] ?? null;
        $descricao = $_POST['descricao'] ?? null;
        $icon = $_POST['icon'] ?? null;
        
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO categories (nome, descricao, icon) VALUES (?, ?, ?)"
        );
        if ($stmt->execute([$nome, $descricao, $icon])) {
            echo '<div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded mb-6">✅ Categoria adicionada com sucesso!</div>';
            header('Refresh: 2; url=?page=categories');
        }
    }
    
    if ($action === 'delete' && $_GET['id']) {
        $stmt = $db->getConnection()->prepare("DELETE FROM categories WHERE id = ?");
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
        $stmt = $db->getConnection()->prepare(
            "SELECT c.*, COUNT(p.id) as total_produtos 
             FROM categories c 
             LEFT JOIN products p ON c.id = p.categoria_id 
             GROUP BY c.id 
             ORDER BY c.nome"
        );
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-black">📂 Gerenciar Categorias</h3>
                <a href="?page=categories&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold">
                    <i class="fas fa-plus mr-2"></i>Nova Categoria
                </a>
            </div>

            <div class="glass border border-purple-600/30 rounded-lg p-6">
                <div class="grid md:grid-cols-3 gap-4">
                    <?php foreach ($categories as $cat): ?>
                    <div class="border border-purple-600/30 rounded-lg p-4 hover:bg-white/5 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold"><?php echo htmlspecialchars($cat['nome']); ?></h4>
                                <p class="text-slate-400 text-sm"><?php echo $cat['total_produtos']; ?> produtos</p>
                            </div>
                            <span class="text-2xl"><?php echo htmlspecialchars($cat['icon'] ?? '📦'); ?></span>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="?page=categories&action=delete&id=<?php echo $cat['id']; ?>" class="flex-1 px-3 py-2 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition" onclick="return confirm('Tem certeza?')">🗑️ Deletar</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
