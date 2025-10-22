<?php
/**
 * Script de Verificação de Tabelas do Admin
 * Verifica se todas as tabelas necessárias estão criadas no banco de dados
 */

require_once 'includes/config.php';
require_once 'includes/db.php';

// Lista de tabelas necessárias e suas colunas obrigatórias
$tabelasNecessarias = [
    'categorias' => ['id', 'nome', 'slug', 'descricao', 'icone', 'cor', 'ordem', 'ativo', 'created_at'],
    'produtos' => ['id', 'nome', 'slug', 'descricao', 'preco', 'preco_promocional', 'categoria_id', 'sku', 'estoque', 'imagem', 'ativo', 'vendas', 'created_at'],
    'configuracoes' => ['id', 'chave', 'valor', 'tipo', 'descricao'],
    'banners' => ['id', 'titulo', 'imagem', 'link', 'ordem', 'ativo'],
    'administradores' => ['id', 'nome', 'email', 'senha', 'ultimo_acesso'],
    'users' => ['id', 'name', 'email', 'password', 'phone', 'role', 'status', 'created_at'],
    'orders' => ['id', 'order_number', 'user_id', 'total_amount', 'tax_amount', 'shipping_amount', 'status', 'delivery_address_id', 'created_at'],
    'order_items' => ['id', 'order_id', 'product_name', 'product_price', 'quantity', 'subtotal'],
    'addresses' => ['id', 'user_id', 'label', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'zip_code', 'is_default']
];

// Função para verificar se uma tabela existe
function tabelaExiste($pdo, $nomeTabela) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$nomeTabela'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Função para obter colunas de uma tabela
function obterColunas($pdo, $nomeTabela) {
    try {
        $stmt = $pdo->query("DESCRIBE `$nomeTabela`");
        $colunas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $colunas[] = $row['Field'];
        }
        return $colunas;
    } catch (Exception $e) {
        return [];
    }
}

// Função para contar registros
function contarRegistros($pdo, $nomeTabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$nomeTabela`");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Tabelas - Wazzy Pods Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-xl p-6 md:p-8 mb-8 border border-purple-800/30">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-white text-2xl md:text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-4xl font-black gradient-text">Verificação de Tabelas</h1>
                    <p class="text-xs md:text-sm text-slate-400 mt-1">Sistema de Administração Wazzy Pods</p>
                </div>
            </div>
        </div>

        <?php
        $todasCriadas = true;
        $totalTabelas = count($tabelasNecessarias);
        $tabelasCriadas = 0;
        $erros = [];
        $avisos = [];
        ?>

        <!-- Resumo Rápido -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-slate-800/50 rounded-lg p-4 border border-purple-800/30">
                <p class="text-xs text-slate-400 mb-1">Total de Tabelas</p>
                <p class="text-2xl font-bold text-purple-400"><?php echo $totalTabelas; ?></p>
            </div>
            <?php
            // Contar tabelas criadas
            foreach ($tabelasNecessarias as $tabela => $colunas) {
                if (tabelaExiste($pdo, $tabela)) {
                    $tabelasCriadas++;
                }
            }
            ?>
            <div class="bg-slate-800/50 rounded-lg p-4 border border-<?php echo $tabelasCriadas === $totalTabelas ? 'green' : 'yellow'; ?>-800/30">
                <p class="text-xs text-slate-400 mb-1">Tabelas Criadas</p>
                <p class="text-2xl font-bold text-<?php echo $tabelasCriadas === $totalTabelas ? 'green' : 'yellow'; ?>-400"><?php echo $tabelasCriadas; ?></p>
            </div>
            <div class="bg-slate-800/50 rounded-lg p-4 border border-<?php echo $tabelasCriadas === $totalTabelas ? 'green' : 'red'; ?>-800/30">
                <p class="text-xs text-slate-400 mb-1">Status Geral</p>
                <p class="text-sm font-bold text-<?php echo $tabelasCriadas === $totalTabelas ? 'green' : 'red'; ?>-400">
                    <?php echo $tabelasCriadas === $totalTabelas ? '✅ COMPLETO' : '❌ INCOMPLETO'; ?>
                </p>
            </div>
        </div>

        <!-- Verificação Detalhada -->
        <div class="space-y-4">
            <?php foreach ($tabelasNecessarias as $nomeTabela => $colunasObrigatorias): ?>
                <?php
                $existe = tabelaExiste($pdo, $nomeTabela);
                $colunasEncontradas = [];
                $colunasFaltando = [];
                $registros = 0;

                if ($existe) {
                    $colunasEncontradas = obterColunas($pdo, $nomeTabela);
                    $colunasFaltando = array_diff($colunasObrigatorias, $colunasEncontradas);
                    $registros = contarRegistros($pdo, $nomeTabela);
                } else {
                    $todasCriadas = false;
                    $colunasFaltando = $colunasObrigatorias;
                }
                ?>

                <div class="bg-slate-800/50 rounded-lg border border-<?php echo $existe && empty($colunasFaltando) ? 'green' : ($existe ? 'yellow' : 'red'); ?>-800/30 overflow-hidden">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center <?php echo $existe && empty($colunasFaltando) ? 'bg-green-900/30' : ($existe ? 'bg-yellow-900/30' : 'bg-red-900/30'); ?>">
                                    <i class="fas fa-<?php echo $existe && empty($colunasFaltando) ? 'check-circle text-green-400' : ($existe ? 'exclamation-triangle text-yellow-400' : 'times-circle text-red-400'); ?> text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg md:text-xl font-bold"><?php echo htmlspecialchars($nomeTabela); ?></h3>
                                    <p class="text-xs text-slate-400">
                                        <?php if ($existe): ?>
                                            <?php echo count($colunasEncontradas); ?> colunas · <?php echo $registros; ?> registros
                                        <?php else: ?>
                                            Tabela não encontrada
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $existe && empty($colunasFaltando) ? 'bg-green-900/30 text-green-400' : ($existe ? 'bg-yellow-900/30 text-yellow-400' : 'bg-red-900/30 text-red-400'); ?>">
                                <?php 
                                if ($existe && empty($colunasFaltando)) {
                                    echo '✅ OK';
                                } elseif ($existe) {
                                    echo '⚠️ ATENÇÃO';
                                } else {
                                    echo '❌ FALTANDO';
                                }
                                ?>
                            </span>
                        </div>

                        <?php if (!$existe): ?>
                            <div class="bg-red-900/20 border border-red-800/50 rounded-lg p-3 md:p-4 mb-4">
                                <p class="text-sm text-red-400">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>Tabela não existe!</strong> Execute o script de instalação para criar esta tabela.
                                </p>
                            </div>
                        <?php elseif (!empty($colunasFaltando)): ?>
                            <div class="bg-yellow-900/20 border border-yellow-800/50 rounded-lg p-3 md:p-4 mb-4">
                                <p class="text-sm text-yellow-400 mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Colunas faltando:</strong>
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($colunasFaltando as $coluna): ?>
                                        <span class="px-2 py-1 bg-yellow-900/30 rounded text-xs text-yellow-300 font-mono"><?php echo htmlspecialchars($coluna); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Colunas Encontradas -->
                        <?php if ($existe && !empty($colunasEncontradas)): ?>
                            <details class="cursor-pointer">
                                <summary class="text-sm text-purple-400 hover:text-purple-300 transition">
                                    <i class="fas fa-chevron-right mr-2"></i> Ver colunas (<?php echo count($colunasEncontradas); ?>)
                                </summary>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php foreach ($colunasEncontradas as $coluna): ?>
                                        <span class="px-2 py-1 bg-slate-900/50 rounded text-xs text-slate-300 font-mono border border-purple-800/20">
                                            <?php echo htmlspecialchars($coluna); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ações -->
        <div class="mt-8 bg-slate-800/50 rounded-lg p-6 border border-purple-800/30">
            <h3 class="text-lg font-bold mb-4 text-purple-400">
                <i class="fas fa-tools mr-2"></i> Ações Disponíveis
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="criar_tabelas.php" class="block p-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg hover:shadow-lg transition text-center">
                    <i class="fas fa-hammer text-2xl mb-2"></i>
                    <p class="font-bold">Criar Tabelas</p>
                    <p class="text-xs opacity-80">Executar instalação completa</p>
                </a>
                <a href="visualizar_tabelas.php" class="block p-4 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition text-center border border-purple-800/30">
                    <i class="fas fa-eye text-2xl mb-2"></i>
                    <p class="font-bold">Visualizar Banco</p>
                    <p class="text-xs opacity-80">Ver todas as tabelas</p>
                </a>
                <a href="admin/" class="block p-4 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition text-center border border-purple-800/30">
                    <i class="fas fa-dashboard text-2xl mb-2"></i>
                    <p class="font-bold">Ir para Admin</p>
                    <p class="text-xs opacity-80">Painel administrativo</p>
                </a>
                <button onclick="location.reload()" class="block w-full p-4 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition text-center border border-purple-800/30">
                    <i class="fas fa-sync text-2xl mb-2"></i>
                    <p class="font-bold">Atualizar Página</p>
                    <p class="text-xs opacity-80">Verificar novamente</p>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500">
            <p>
                <i class="fas fa-info-circle mr-1"></i>
                Verificação automática de integridade do banco de dados
            </p>
            <p class="mt-1">Wazzy Pods Admin · <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        details summary {
            list-style: none;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
        details[open] summary i {
            transform: rotate(90deg);
        }
        details summary i {
            display: inline-block;
            transition: transform 0.3s;
        }
    </style>

    <script>
        // Auto-reload a cada 30 segundos se houver erros
        <?php if ($tabelasCriadas < $totalTabelas): ?>
            // setTimeout(() => location.reload(), 30000);
        <?php endif; ?>
    </script>
</body>
</html>

