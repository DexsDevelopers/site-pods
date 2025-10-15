<div class="space-y-6">
    <!-- Métricas -->
    <div class="grid md:grid-cols-4 gap-6">
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Vendas Hoje</p>
                    <p class="text-3xl font-black">15</p>
                </div>
                <i class="fas fa-shopping-bag text-purple-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Receita Mês</p>
                    <p class="text-3xl font-black">R$ 28.500</p>
                </div>
                <i class="fas fa-chart-line text-green-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Pedidos Pendentes</p>
                    <p class="text-3xl font-black text-orange-400">8</p>
                </div>
                <i class="fas fa-clock text-orange-500 text-3xl opacity-20"></i>
            </div>
        </div>
        <div class="glass border border-purple-600/30 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Produtos</p>
                    <p class="text-3xl font-black">142</p>
                </div>
                <i class="fas fa-box text-blue-500 text-3xl opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="glass border border-purple-600/30 rounded-lg p-6">
        <h3 class="text-xl font-black mb-4">Ações Rápidas</h3>
        <div class="grid md:grid-cols-4 gap-4">
            <a href="?page=products&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold text-center hover:shadow-lg transition">
                <i class="fas fa-plus mr-2"></i>Novo Produto
            </a>
            <a href="?page=categories&action=add" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-plus mr-2"></i>Nova Categoria
            </a>
            <a href="?page=orders" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-eye mr-2"></i>Ver Pedidos
            </a>
            <a href="?page=customers" class="px-6 py-3 bg-slate-700 rounded-lg font-bold text-center hover:bg-slate-600 transition">
                <i class="fas fa-users mr-2"></i>Ver Clientes
            </a>
        </div>
    </div>

    <!-- Últimos Pedidos -->
    <div class="glass border border-purple-600/30 rounded-lg p-6">
        <h3 class="text-xl font-black mb-4">Últimos Pedidos</h3>
        <table class="w-full text-sm">
            <thead class="border-b border-slate-700">
                <tr>
                    <th class="text-left py-2">Pedido</th>
                    <th class="text-left py-2">Cliente</th>
                    <th class="text-left py-2">Valor</th>
                    <th class="text-left py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                    <td class="py-3">#001</td>
                    <td>João Silva</td>
                    <td>R$ 299,90</td>
                    <td><span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded">Entregue</span></td>
                </tr>
                <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                    <td class="py-3">#002</td>
                    <td>Maria Santos</td>
                    <td>R$ 449,90</td>
                    <td><span class="px-2 py-1 bg-blue-600/20 text-blue-400 text-xs rounded">Enviado</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
