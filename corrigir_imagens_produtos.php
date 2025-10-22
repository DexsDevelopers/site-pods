<?php
/**
 * Script para corrigir imagens dos produtos
 * Adiciona imagens padrão para produtos sem imagem
 */

require_once 'includes/config_hostinger.php';
require_once 'includes/db.php';

echo "<h2>🖼️ Corrigindo Imagens dos Produtos</h2>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // Buscar produtos sem imagem ou com imagem inválida
    $stmt = $pdo->query("SELECT id, nome, imagem FROM produtos WHERE ativo = 1");
    $produtos = $stmt->fetchAll();
    
    echo "<h3>📋 Produtos encontrados: " . count($produtos) . "</h3>";
    
    $atualizados = 0;
    $imagensPadrao = [
        'https://images.unsplash.com/photo-1587829191301-a06d4f10f5bb?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1600856062241-98e5dba7214d?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1617638924702-92d37d439220?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1621605815971-fbc98d665079?w=600&h=600&fit=crop&auto=format',
        'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?w=600&h=600&fit=crop&auto=format'
    ];
    
    foreach ($produtos as $i => $produto) {
        $imagemAtual = $produto['imagem'];
        $precisaAtualizar = false;
        $novaImagem = '';
        
        // Verificar se precisa de imagem
        if (empty($imagemAtual)) {
            $precisaAtualizar = true;
            $novaImagem = $imagensPadrao[$i % count($imagensPadrao)];
        } elseif (strpos($imagemAtual, 'http') !== 0 && !file_exists($imagemAtual)) {
            $precisaAtualizar = true;
            $novaImagem = $imagensPadrao[$i % count($imagensPadrao)];
        }
        
        if ($precisaAtualizar) {
            try {
                $stmt = $pdo->prepare("UPDATE produtos SET imagem = ? WHERE id = ?");
                $stmt->execute([$novaImagem, $produto['id']]);
                $atualizados++;
                echo "<p style='color: green;'>✅ Produto '{$produto['nome']}' - Imagem atualizada</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erro ao atualizar '{$produto['nome']}': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Produto '{$produto['nome']}' - Imagem OK</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📊 Resumo:</h3>";
    echo "<p><strong>Total de produtos:</strong> " . count($produtos) . "</p>";
    echo "<p><strong>Produtos atualizados:</strong> " . $atualizados . "</p>";
    echo "<p><strong>Produtos OK:</strong> " . (count($produtos) - $atualizados) . "</p>";
    
    if ($atualizados > 0) {
        echo "<p style='color: green;'><strong>🎉 Imagens corrigidas com sucesso!</strong></p>";
    } else {
        echo "<p style='color: blue;'><strong>ℹ️ Todas as imagens já estavam OK!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Arquivo:</strong> corrigir_imagens_produtos.php</p>";
echo "<p><strong>Você pode apagar este arquivo após usar.</strong></p>";
?>
