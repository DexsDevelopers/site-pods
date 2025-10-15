<?php
$action = $_GET['action'] ?? null;
$categories = [];
$category_id = $_GET['id'] ?? null;
$category = null;
$conn = null;

try {
    @include '../../includes/config.php';
    @include '../../includes/db.php';
    $conn = Database::getConnection();
} catch (Exception $e) {
    echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px;">❌ Erro</div>';
}

if ($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (nome, descricao, icon) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['nome'] ?? '', $_POST['descricao'] ?? '', $_POST['icon'] ?? '']);
            echo '<div style="color: #86efac; background: rgba(34,197,94,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">✅ Adicionada!</div>';
        } catch (Exception $e) {}
    }

    if ($action === 'delete' && $category_id) {
        try {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            echo '<div style="color: #86efac; background: rgba(34,197,94,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">✅ Deletada!</div>';
        } catch (Exception $e) {}
    }

    if (!$action) {
        try {
            $stmt = $conn->prepare("SELECT id, nome, descricao, icon FROM categories ORDER BY nome");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}
?>

<?php if ($action === 'add'): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 24px; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 20px 0;">➕ Nova Categoria</h3>
    
    <form method="POST" style="display: grid; gap: 16px;">
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Nome</label>
            <input type="text" name="nome" required style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;">
        </div>
        
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Descrição</label>
            <textarea name="descricao" style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box; font-family: inherit;" rows="3"></textarea>
        </div>
        
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Ícone (emoji)</label>
            <input type="text" name="icon" placeholder="📦" style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;">
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" style="padding: 12px 24px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">💾 Salvar</button>
            <a href="?page=categories" style="padding: 12px 24px; background: #475569; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">❌ Cancelar</a>
        </div>
    </form>
</div>

<?php else: ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0;">📂 Categorias</h3>
    <a href="?page=categories&action=add" style="padding: 12px 24px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">➕ Nova</a>
</div>

<?php if (count($categories) > 0): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: rgba(0,0,0,0.2);">
            <tr>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Nome</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Descrição</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr style="border-bottom: 1px solid rgba(147,51,234,0.15);">
                <td style="padding: 12px;"><?php echo htmlspecialchars($cat['nome']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars(substr($cat['descricao'] ?? '', 0, 50)); ?></td>
                <td style="padding: 12px;">
                    <a href="?page=categories&action=delete&id=<?php echo $cat['id']; ?>" style="padding: 6px 12px; background: rgba(239,68,68,0.2); color: #fca5a5; text-decoration: none; border-radius: 4px; font-size: 12px;" onclick="return confirm('Deletar?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div style="color: #94a3b8; background: rgba(0,0,0,0.1); padding: 40px; border-radius: 8px; text-align: center;">
    <p>Nenhuma categoria cadastrada</p>
    <a href="?page=categories&action=add" style="display: inline-block; margin-top: 12px; padding: 10px 20px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">➕ Criar Primeira</a>
</div>

<?php endif; ?>

<?php endif; ?>
