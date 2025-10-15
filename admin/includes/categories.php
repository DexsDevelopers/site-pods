<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black">📂 Gerenciar Categorias</h3>
        <button onclick="openCategoryForm()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>Nova Categoria
        </button>
    </div>

    <div class="glass border border-purple-600/30 rounded-lg p-6">
        <div class="grid md:grid-cols-3 gap-4">
            <div class="border border-purple-600/30 rounded-lg p-4 hover:bg-white/5 transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold">Vaporizadores</h4>
                        <p class="text-slate-400 text-sm">15 produtos</p>
                    </div>
                    <span class="text-2xl">☁️</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</button>
                    <button class="flex-1 px-3 py-2 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition">🗑️ Deletar</button>
                </div>
            </div>

            <div class="border border-purple-600/30 rounded-lg p-4 hover:bg-white/5 transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold">Acessórios</h4>
                        <p class="text-slate-400 text-sm">32 produtos</p>
                    </div>
                    <span class="text-2xl">📦</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</button>
                    <button class="flex-1 px-3 py-2 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition">🗑️ Deletar</button>
                </div>
            </div>

            <div class="border border-purple-600/30 rounded-lg p-4 hover:bg-white/5 transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold">Líquidos</h4>
                        <p class="text-slate-400 text-sm">48 produtos</p>
                    </div>
                    <span class="text-2xl">🧴</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 bg-blue-600/20 text-blue-400 rounded text-xs hover:bg-blue-600/40 transition">✏️ Editar</button>
                    <button class="flex-1 px-3 py-2 bg-red-600/20 text-red-400 rounded text-xs hover:bg-red-600/40 transition">🗑️ Deletar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openCategoryForm() {
        alert('✅ Formulário de categoria (será implementado)');
    }
</script>
