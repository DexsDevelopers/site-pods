<?php
require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

try {
    // Adicionar colunas do Mercado Pago se não existirem
    $columns = [
        'mercado_pago_preference_id' => 'VARCHAR(255) NULL',
        'mercado_pago_payment_id' => 'VARCHAR(255) NULL'
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $stmt = $pdo->prepare("ALTER TABLE orders ADD COLUMN $column $definition");
            $stmt->execute();
            echo "✅ Coluna $column adicionada com sucesso<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️ Coluna $column já existe<br>";
            } else {
                echo "❌ Erro ao adicionar coluna $column: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<br>✅ Atualização da tabela concluída!<br>";
    echo "🔗 <a href='admin/pedidos.php'>Ver Pedidos</a><br>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
