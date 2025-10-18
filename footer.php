<?php
// Garantir que config e db estão carregados
if (!isset($pdo)) {
    try {
        require_once dirname(__FILE__) . '/includes/config.php';
        require_once dirname(__FILE__) . '/includes/db.php';
    } catch (Exception $e) {
        error_log('Erro ao carregar config no footer: ' . $e->getMessage());
    }
}

// Valores padrão
$site_name = 'Wazzy Pods';
$site_phone = '(11) 9999-9999';
$site_email = 'contato@wazzypods.com';
$site_address = 'São Paulo, SP';

// Buscar configurações do banco
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('site_name', 'site_phone', 'site_email', 'site_address') LIMIT 4");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if (!empty($configs['site_name'])) $site_name = $configs['site_name'];
        if (!empty($configs['site_phone'])) $site_phone = $configs['site_phone'];
        if (!empty($configs['site_email'])) $site_email = $configs['site_email'];
        if (!empty($configs['site_address'])) $site_address = $configs['site_address'];
    } catch (Exception $e) {
        error_log('Erro ao buscar configurações no footer: ' . $e->getMessage());
    }
}
?>

<footer class="bg-slate-950 border-t border-purple-800/30 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <!-- Sobre -->
            <div>
                <h3 class="text-lg font-bold gradient-text mb-4 flex items-center gap-2">
                    <i class="fas fa-skull-crossbones"></i>
                    <?php echo htmlspecialchars($site_name); ?>
                </h3>
                <p class="text-slate-400 text-sm">Pods premium com qualidade garantida e design extraordinário.</p>
            </div>

            <!-- Links Rápidos -->
            <div>
                <h4 class="text-sm font-bold text-purple-400 mb-4">Links Rápidos</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="/" class="hover:text-purple-400 transition">Home</a></li>
                    <li><a href="/" class="hover:text-purple-400 transition">Produtos</a></li>
                    <li><a href="/pages/profile.php" class="hover:text-purple-400 transition">Meu Perfil</a></li>
                    <li><a href="/pages/cart.php" class="hover:text-purple-400 transition">Carrinho</a></li>
                </ul>
            </div>

            <!-- Contato -->
            <div>
                <h4 class="text-sm font-bold text-purple-400 mb-4">Contato</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone text-purple-400"></i>
                        <span><?php echo htmlspecialchars($site_phone); ?></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-purple-400"></i>
                        <a href="mailto:<?php echo htmlspecialchars($site_email); ?>" class="hover:text-purple-400 transition">
                            <?php echo htmlspecialchars($site_email); ?>
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-purple-400"></i>
                        <span><?php echo htmlspecialchars($site_address); ?></span>
                    </li>
                </ul>
            </div>

            <!-- Redes Sociais -->
            <div>
                <h4 class="text-sm font-bold text-purple-400 mb-4">Redes Sociais</h4>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 rounded-full bg-purple-600/20 border border-purple-600/50 flex items-center justify-center text-purple-400 hover:bg-purple-600/40 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-purple-600/20 border border-purple-600/50 flex items-center justify-center text-purple-400 hover:bg-purple-600/40 transition">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-purple-600/20 border border-purple-600/50 flex items-center justify-center text-purple-400 hover:bg-purple-600/40 transition">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-purple-600/20 border border-purple-600/50 flex items-center justify-center text-purple-400 hover:bg-purple-600/40 transition">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Divisor -->
        <div class="border-t border-purple-800/30 pt-8">
            <div class="grid md:grid-cols-2 gap-4 text-center md:text-left text-sm text-slate-400">
                <div>
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. Todos os direitos reservados.</p>
                </div>
                <div class="flex gap-4 md:justify-end justify-center text-xs">
                    <a href="#" class="hover:text-purple-400 transition">Política de Privacidade</a>
                    <a href="#" class="hover:text-purple-400 transition">Termos de Serviço</a>
                    <a href="#" class="hover:text-purple-400 transition">Contato</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .gradient-text {
        background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>