<div class="space-y-6">
    <?php
    $action = $_GET['action'] ?? null;
    $product_id = $_GET['id'] ?? null;

    if ($action === 'add' || $action === 'edit') {
        // Formulário de Produto
        ?>
        <div class="glass border border-purple-600/30 rounded-lg p-8">
            <h3 class="text-2xl font-black mb-6">
                <?php echo $action === 'add' ? '➕ Novo Produto' : '✏️ Editar Produto'; ?>
            </h3>

            <form method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Nome do Produto</label>
                        <input type="text" name="nome" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ex: Vapor Premium X-01">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Categoria</label>
                        <select name="categoria" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option>Vaporizadores</option>
                            <option>Acessórios</option>
                            <option>Líquidos</option>
                            <option>Baterias</option>
                        </select>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Preço (R$)</label>
                        <input type="number" name="preco" step="0.01" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="299.90">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Estoque</label>
                        <input type="number" name="estoque" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="50">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 mb-2">Descrição</label>
                    <textarea name="descricao" rows="4" required class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Descrição detalhada do produto..."></textarea>
                </div>

                <div>
                    <label class="block text-slate-300 mb-2">URL da Imagem</label>
                    <input type="url" name="imagem" class="w-full px-4 py-2 bg-slate-800 border border-purple-600 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="https://...">
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                        💾 Salvar Produto
                    </button>
                    <a href="?page=products" class="px-8 py-3 bg-slate-700 rounded-lg font-bold hover:bg-slate-600 transition">
                        ❌ Cancelar
                    </a>
                </div>
            </form>
        </div>
        <?php
    } else {
        // Lista de Produtos
        ?>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-black">📦 Gerenciar Produtos</h3>
                <a href="?page=products&action=add" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
                    <i class="fas fa-plus mr-2"></i>Novo Produto
                </a>
            </div>

            <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="text-left px-6 py-3">Produto</th>
                            <th class="text-left px-6 py-3">Categoria</th>
                            <th class="text-left px-6 py-3">Preço</th>
                            <th class="text-left px-6 py-3">Estoque</th>
                            <th class="text-left px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-bold">Vapor Premium X-01</td>
                            <td class="px-6 py-4">Vaporizadores</td>
                            <td class="px-6 py-4">R$ 299,90</td>
                            <td class="px-6 py-4"><span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-xs font-bold">50</span></td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="?page=products&action=edit&id=1" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</a>
                                <a href="?page=products&action=delete&id=1" class="px-3 py-1 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition" onclick="return confirm('Tem certeza?')">🗑️ Deletar</a>
                            </td>
                        </tr>
                        <tr class="border-b border-slate-700 hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-bold">Aero Compact 2024</td>
                            <td class="px-6 py-4">Vaporizadores</td>
                            <td class="px-6 py-4">R$ 199,90</td>
                            <td class="px-6 py-4"><span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-xs font-bold">35</span></td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="?page=products&action=edit&id=2" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</a>
                                <a href="?page=products&action=delete&id=2" class="px-3 py-1 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition" onclick="return confirm('Tem certeza?')">🗑️ Deletar</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    ?>
</div>
