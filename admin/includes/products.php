<?php
$action = $_GET['action'] ?? null;
$product_id = $_GET['id'] ?? null;
$products = [];
$categories = [];
$product = null;
$conn = null;

try {
    @include '../../includes/config.php';
    @include '../../includes/db.php';
    $conn = Database::getConnection();
} catch (Exception $e) {
    echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px;">❌ Erro de conexão</div>';
    $conn = null;
}

if ($conn) {
    // POST - Adicionar
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
        try {
            $stmt = $conn->prepare("INSERT INTO products (nome, descricao, preco, estoque, categoria_id, imagem) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['nome'] ?? '', $_POST['descricao'] ?? '', $_POST['preco'] ?? 0, $_POST['estoque'] ?? 0, $_POST['categoria'] ?? null, $_POST['imagem'] ?? '']);
            echo '<div style="color: #86efac; background: rgba(34,197,94,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">✅ Adicionado!</div>';
        } catch (Exception $e) {
            echo '<div style="color: #f87171; background: rgba(220,38,38,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">❌ Erro</div>';
        }
    }

    // DELETE
    if ($action === 'delete' && $product_id) {
        try {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            echo '<div style="color: #86efac; background: rgba(34,197,94,0.2); padding: 16px; border-radius: 8px; margin-bottom: 16px;">✅ Deletado!</div>';
        } catch (Exception $e) {}
    }

    // Carregar categorias
    try {
        $stmt = $conn->prepare("SELECT id, nome FROM categories ORDER BY nome");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Carregar para editar
    if ($action === 'edit' && $product_id) {
        try {
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    // Listar produtos
    if (!$action) {
        try {
            $stmt = $conn->prepare("SELECT p.*, c.nome as categoria_nome FROM products p LEFT JOIN categories c ON p.categoria_id = c.id ORDER BY p.id DESC");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}
?>

<?php if ($action === 'add' || $action === 'edit'): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; padding: 24px; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 20px 0;">
        <?php echo $action === 'add' ? '➕ Novo Produto' : '✏️ Editar'; ?>
    </h3>
    
    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Nome</label>
            <input type="text" name="nome" required style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;" value="<?php echo htmlspecialchars($product['nome'] ?? ''); ?>">
        </div>
        
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Categoria</label>
            <select name="categoria" style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;">
                <option value="">Selecione</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product['categoria_id'] ?? null) == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Preço</label>
            <input type="number" name="preco" step="0.01" required style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;" value="<?php echo $product['preco'] ?? ''; ?>">
        </div>
        
        <div>
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Estoque</label>
            <input type="number" name="estoque" required style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;" value="<?php echo $product['estoque'] ?? ''; ?>">
        </div>
        
        <div style="grid-column: 1 / -1;">
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">Descrição</label>
            <textarea name="descricao" style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box; font-family: inherit;" rows="4"><?php echo htmlspecialchars($product['descricao'] ?? ''); ?></textarea>
        </div>
        
        <div style="grid-column: 1 / -1;">
            <label style="display: block; color: #cbd5e1; margin-bottom: 8px;">URL Imagem</label>
            <input type="text" name="imagem" style="width: 100%; padding: 10px; background: #1e293b; border: 1px solid #9333ea; border-radius: 6px; color: white; box-sizing: border-box;" value="<?php echo htmlspecialchars($product['imagem'] ?? ''); ?>">
        </div>
        
        <div style="grid-column: 1 / -1; display: flex; gap: 12px;">
            <button type="submit" style="padding: 12px 24px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">💾 Salvar</button>
            <a href="?page=products" style="padding: 12px 24px; background: #475569; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">❌ Cancelar</a>
        </div>
    </form>
</div>

<?php else: ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="font-size: 20px; font-weight: 900; margin: 0;">📦 Produtos</h3>
    <a href="?page=products&action=add" style="padding: 12px 24px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">➕ Novo</a>
</div>

<?php if (count($products) > 0): ?>

<div style="background: rgba(0,0,0,0.1); border: 1px solid rgba(147,51,234,0.3); border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead style="background: rgba(0,0,0,0.2);">
            <tr>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Produto</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Categoria</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Preço</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Estoque</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid rgba(147,51,234,0.3);">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $prod): ?>
            <tr style="border-bottom: 1px solid rgba(147,51,234,0.15);">
                <td style="padding: 12px;"><?php echo htmlspecialchars($prod['nome']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($prod['categoria_nome'] ?? '-'); ?></td>
                <td style="padding: 12px;">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                <td style="padding: 12px;"><?php echo $prod['estoque']; ?></td>
                <td style="padding: 12px;">
                    <a href="?page=products&action=edit&id=<?php echo $prod['id']; ?>" style="padding: 6px 12px; background: rgba(59,130,246,0.2); color: #93c5fd; text-decoration: none; border-radius: 4px; font-size: 12px;">✏️</a>
                    <a href="?page=products&action=delete&id=<?php echo $prod['id']; ?>" style="padding: 6px 12px; background: rgba(239,68,68,0.2); color: #fca5a5; text-decoration: none; border-radius: 4px; font-size: 12px;" onclick="return confirm('Deletar?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div style="color: #94a3b8; background: rgba(0,0,0,0.1); padding: 40px; border-radius: 8px; text-align: center;">
    <p>Nenhum produto cadastrado</p>
    <a href="?page=products&action=add" style="display: inline-block; margin-top: 12px; padding: 10px 20px; background: linear-gradient(to right, #9333ea, #ec4899); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">➕ Criar Primeiro</a>
</div>

<?php endif; ?>

<?php endif; ?>
