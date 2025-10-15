<?php
require_once '../includes/config.php';
require_once '../includes/helpers.php';

// Dados simulados de admin
$metrics = [
    'vendas_hoje' => 15,
    'receita_mes' => 28500.00,
    'pedidos_pendentes' => 8,
    'clientes_totais' => 1203,
];

$pedidos = [
    ['numero' => '#001', 'cliente' => 'João Silva', 'valor' => 299.90, 'status' => 'entregue', 'data' => '15/10/2025'],
    ['numero' => '#002', 'cliente' => 'Maria Santos', 'valor' => 449.90, 'status' => 'enviado', 'data' => '14/10/2025'],
    ['numero' => '#003', 'cliente' => 'Pedro Costa', 'valor' => 199.90, 'status' => 'pendente', 'data' => '15/10/2025'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100">

    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="flex justify-between items-center mb-12">
            <h1 class="text-4xl font-black">📊 Painel Administrativo</h1>
            <a href="../index.php" class="px-6 py-3 glass rounded-lg hover:bg-white/10 transition">
                ← Voltar à Loja
            </a>
        </div>

        <!-- Métricas -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="glass p-6 rounded-lg">
                <p class="text-slate-400 mb-2">Vendas Hoje</p>
                <p class="text-3xl font-black"><?php echo $metrics['vendas_hoje']; ?></p>
            </div>
            <div class="glass p-6 rounded-lg">
                <p class="text-slate-400 mb-2">Receita Mês</p>
                <p class="text-3xl font-black">R$ <?php echo number_format($metrics['receita_mes'], 2, ',', '.'); ?></p>
            </div>
            <div class="glass p-6 rounded-lg">
                <p class="text-slate-400 mb-2">Pedidos Pendentes</p>
                <p class="text-3xl font-black text-orange-400"><?php echo $metrics['pedidos_pendentes']; ?></p>
            </div>
            <div class="glass p-6 rounded-lg">
                <p class="text-slate-400 mb-2">Clientes</p>
                <p class="text-3xl font-black text-purple-400"><?php echo $metrics['clientes_totais']; ?></p>
            </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="glass rounded-lg p-8">
            <h2 class="text-2xl font-black mb-6">Pedidos Recentes</h2>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-600">
                        <th class="text-left py-3">Pedido</th>
                        <th class="text-left py-3">Cliente</th>
                        <th class="text-left py-3">Valor</th>
                        <th class="text-left py-3">Status</th>
                        <th class="text-left py-3">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                            <td class="py-4 font-bold"><?php echo $pedido['numero']; ?></td>
                            <td class="py-4"><?php echo $pedido['cliente']; ?></td>
                            <td class="py-4">R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-<?php
                                    echo $pedido['status'] === 'entregue' ? 'green' : 
                                         ($pedido['status'] === 'enviado' ? 'blue' : 'orange');
                                ?>-600/20 text-<?php
                                    echo $pedido['status'] === 'entregue' ? 'green' : 
                                         ($pedido['status'] === 'enviado' ? 'blue' : 'orange');
                                ?>-400">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-slate-400"><?php echo $pedido['data']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
