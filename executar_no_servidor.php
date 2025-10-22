<?php
/**
 * SCRIPT PARA EXECUTAR NO SERVIDOR DA HOSTINGER
 * 
 * 1. Acesse: https://seu-site.com/executar_no_servidor.php
 * 2. Execute o script
 * 3. Apague este arquivo após usar
 */

echo "<h2>🖼️ Adicionando Imagens aos Produtos</h2>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Configurações da Hostinger
$host = 'localhost';
$db = 'u853242961_loja_pods';
$user = 'u853242961_pods_saluc';
$pass = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Conexão estabelecida!</p>";
    
    // Buscar produtos
    $stmt = $pdo->query("SELECT id, nome, imagem FROM produtos WHERE ativo = 1");
    $produtos = $stmt->fetchAll();
    
    echo "<p><strong>📋 Produtos encontrados:</strong> " . count($produtos) . "</p>";
    
    $imagens = [
        'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1617638924702-92d37d439220?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1621605815971-fbc98d665079?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?w=600&h=600&fit=crop&auto=format'
    ];
    
    $atualizados = 0;
    
    echo "<ul>";
    foreach ($produtos as $i => $produto) {
        $imagemAtual = $produto['imagem'];
        
        if (empty($imagemAtual)) {
            $novaImagem = $imagens[$i % count($imagens)];
            
            try {
                $stmt = $pdo->prepare("UPDATE produtos SET imagem = ? WHERE id = ?");
                $stmt->execute([$novaImagem, $produto['id']]);
                $atualizados++;
                echo "<li style='color: green;'>✅ {$produto['nome']} - Imagem adicionada</li>";
            } catch (Exception $e) {
                echo "<li style='color: red;'>❌ Erro em {$produto['nome']}: " . $e->getMessage() . "</li>";
            }
        } else {
            echo "<li style='color: blue;'>ℹ️ {$produto['nome']} - Já tem imagem</li>";
        }
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>📊 Resumo:</h3>";
    echo "<p><strong>Total de produtos:</strong> " . count($produtos) . "</p>";
    echo "<p><strong>Produtos atualizados:</strong> " . $atualizados . "</p>";
    echo "<p><strong>Produtos OK:</strong> " . (count($produtos) - $atualizados) . "</p>";
    
    if ($atualizados > 0) {
        echo "<p style='color: green; font-size: 18px;'><strong>🎉 Imagens adicionadas com sucesso!</strong></p>";
        echo "<p><strong>✅ Agora teste:</strong></p>";
        echo "<ul>";
        echo "<li><a href='index.php' target='_blank'>Página Principal</a></li>";
        echo "<li><a href='pages/produtos.php' target='_blank'>Página de Produtos</a></li>";
        echo "<li><a href='pages/product-detail.php?id=1' target='_blank'>Detalhes do Produto</a></li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'><strong>ℹ️ Todas as imagens já estavam OK!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Verifique as credenciais do banco de dados.</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Apague este arquivo após usar por segurança!</p>";
echo "<p><strong>Arquivo:</strong> executar_no_servidor.php</p>";
?>
