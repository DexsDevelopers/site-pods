<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// ========== CONEXÃO DIRETA (sem usar a classe Database) ==========
$project_root = dirname(dirname(__FILE__));
$env_file = $project_root . '/.env';

$conexao_ok = false;
$erro_conexao = 'Desconhecido';
$conn = null;
$tabelas_status = [];
$total_criadas = 0;

// 1. Verificar .env
if (!file_exists($env_file)) {
    $erro_conexao = 'Arquivo .env não encontrado';
} else {
    // 2. Ler .env
    $env_content = file_get_contents($env_file);
    
    // Parse manual do .env
    $env_vars = [];
    $lines = explode("\n", $env_content);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
        }
    }
    
    // 3. Tentar conectar
    try {
        $host = $env_vars['DB_HOST'] ?? 'localhost';
        $port = $env_vars['DB_PORT'] ?? 3306;
        $name = $env_vars['DB_NAME'] ?? '';
        $user = $env_vars['DB_USER'] ?? 'root';
        $pass = $env_vars['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        
        $conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $conexao_ok = true;
    } catch (PDOException $e) {
        $conexao_ok = false;
        $erro_conexao = $e->getMessage();
    }
}

// Tabelas necessárias
$required_tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        telefone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer') DEFAULT 'customer',
        ativo BOOLEAN DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'categories' => "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL UNIQUE,
        descricao TEXT,
        icon VARCHAR(50),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nome (nome)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'products' => "CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10, 2) NOT NULL,
        estoque INT DEFAULT 0,
        categoria_id INT,
        imagem VARCHAR(500),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categories(id) ON DELETE SET NULL,
        INDEX idx_nome (nome),
        INDEX idx_categoria (categoria_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'product_images' => "CREATE TABLE IF NOT EXISTS product_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        product_id INT NOT NULL,
        imagem VARCHAR(500) NOT NULL,
        ordem INT DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        total DECIMAL(10, 2) NOT NULL,
        status ENUM('pendente', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_data (criado_em)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco DECIMAL(10, 2) NOT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_order (order_id),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'addresses' => "CREATE TABLE IF NOT EXISTS addresses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        rua VARCHAR(255) NOT NULL,
        numero VARCHAR(20) NOT NULL,
        complemento VARCHAR(255),
        cidade VARCHAR(255) NOT NULL,
        estado VARCHAR(2) NOT NULL,
        cep VARCHAR(10) NOT NULL,
        principal BOOLEAN DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'audit_logs' => "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        acao VARCHAR(255) NOT NULL,
        tabela VARCHAR(100) NOT NULL,
        registro_id INT,
        dados_antigos JSON,
        dados_novos JSON,
        ip_address VARCHAR(45),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user (user_id),
        INDEX idx_data (criado_em)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'settings' => "CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        chave VARCHAR(255) UNIQUE NOT NULL,
        valor LONGTEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_chave (chave)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'coupons' => "CREATE TABLE IF NOT EXISTS coupons (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(100) UNIQUE NOT NULL,
        desconto DECIMAL(10, 2) NOT NULL,
        tipo ENUM('fixo', 'percentual') DEFAULT 'percentual',
        ativo BOOLEAN DEFAULT 1,
        data_inicio DATE,
        data_fim DATE,
        uso_maximo INT,
        uso_atual INT DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_codigo (codigo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'reviews' => "CREATE TABLE IF NOT EXISTS reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        titulo VARCHAR(255),
        comentario TEXT,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_product (product_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Verificar e criar tabelas
if ($conexao_ok && $conn !== null) {
    foreach ($required_tables as $tabela => $sql) {
        try {
            @$conn->query("SELECT 1 FROM `$tabela` LIMIT 1");
            $tabelas_status[$tabela] = ['existe' => true, 'acao' => 'Já existe'];
        } catch (PDOException $e) {
            try {
                @$conn->exec($sql);
                $tabelas_status[$tabela] = ['existe' => true, 'acao' => 'Criada ✅'];
                $total_criadas++;
            } catch (PDOException $e2) {
                $tabelas_status[$tabela] = ['existe' => false, 'acao' => 'Erro ao criar'];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Banco de Dados - TechVapor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); }
        .glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-100 py-10 px-4">

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h1 class="text-4xl font-black mb-2">
                <i class="fas fa-database text-purple-500 mr-3"></i>Verificador de Banco de Dados
            </h1>
            <p class="text-slate-400">TechVapor - Sistema de Verificação e Criação de Tabelas</p>
        </div>

        <!-- Status da Conexão -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mb-8">
            <h2 class="text-2xl font-black mb-6">
                <i class="fas fa-plug mr-2"></i>Status da Conexão
            </h2>
            
            <?php if ($conexao_ok): ?>
                <div class="bg-green-600/20 border border-green-600 text-green-400 px-6 py-4 rounded-lg flex items-center gap-3">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <div>
                        <p class="font-bold">Conexão com Banco de Dados: ✅ OK</p>
                        <p class="text-sm opacity-80">Host: <?php echo htmlspecialchars($env_vars['DB_HOST'] ?? 'localhost'); ?> | Database: <?php echo htmlspecialchars($env_vars['DB_NAME'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-red-600/20 border border-red-600 text-red-400 px-6 py-4 rounded-lg flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <div>
                        <p class="font-bold">❌ Erro na Conexão</p>
                        <p class="text-sm"><?php echo htmlspecialchars($erro_conexao); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Resumo -->
        <?php if ($conexao_ok): ?>
        <div class="grid md:grid-cols-3 gap-4 mb-8">
            <div class="glass border border-purple-600/30 rounded-lg p-6 text-center">
                <p class="text-slate-400 text-sm">Total de Tabelas</p>
                <p class="text-4xl font-black text-purple-400"><?php echo count($required_tables); ?></p>
            </div>
            <div class="glass border border-purple-600/30 rounded-lg p-6 text-center">
                <p class="text-slate-400 text-sm">Tabelas Criadas</p>
                <p class="text-4xl font-black text-green-400"><?php echo $total_criadas; ?></p>
            </div>
            <div class="glass border border-purple-600/30 rounded-lg p-6 text-center">
                <p class="text-slate-400 text-sm">Tabelas Existentes</p>
                <p class="text-4xl font-black text-blue-400"><?php echo count($tabelas_status) - $total_criadas; ?></p>
            </div>
        </div>

        <!-- Lista de Tabelas -->
        <div class="glass border border-purple-600/30 rounded-lg overflow-hidden">
            <div class="bg-slate-800/50 border-b border-slate-700 px-8 py-4">
                <h3 class="text-xl font-black">📋 Status das Tabelas</h3>
            </div>

            <div class="divide-y divide-slate-700">
                <?php foreach ($tabelas_status as $tabela => $status): ?>
                <div class="px-8 py-4 flex items-center justify-between hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-table text-purple-400 text-lg"></i>
                        <span class="font-mono text-lg font-bold"><?php echo htmlspecialchars($tabela); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($status['existe']): ?>
                            <?php if (strpos($status['acao'], 'Criada') !== false): ?>
                                <span class="px-4 py-2 bg-green-600/20 text-green-400 rounded-lg text-sm font-bold">
                                    <i class="fas fa-check mr-2"></i><?php echo $status['acao']; ?>
                                </span>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-blue-600/20 text-blue-400 rounded-lg text-sm font-bold">
                                    <i class="fas fa-info-circle mr-2"></i><?php echo $status['acao']; ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-red-600/20 text-red-400 rounded-lg text-sm font-bold">
                                <i class="fas fa-times mr-2"></i><?php echo $status['acao']; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="glass border border-purple-600/30 rounded-lg p-8 mt-8">
            <h3 class="text-xl font-black mb-4">
                <i class="fas fa-info-circle text-blue-400 mr-2"></i>Informações do Sistema
            </h3>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">PHP Version</p>
                    <p class="text-green-400 font-bold"><?php echo phpversion(); ?></p>
                </div>
                <div>
                    <p class="text-slate-400">Database Driver</p>
                    <p class="text-green-400 font-bold">PDO MySQL</p>
                </div>
                <div>
                    <p class="text-slate-400">Charset</p>
                    <p class="text-green-400 font-bold">utf8mb4</p>
                </div>
                <div>
                    <p class="text-slate-400">Hora do Sistema</p>
                    <p class="text-green-400 font-bold"><?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="flex gap-4 mt-8 flex-wrap">
            <a href="/" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-home"></i> Ir para Home
            </a>
            <a href="../admin/" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition flex items-center gap-2">
                <i class="fas fa-arrow-right"></i> Ir para Admin
            </a>
            <button onclick="location.reload()" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-bold hover:bg-slate-600 transition flex items-center gap-2">
                <i class="fas fa-sync"></i> Recarregar
            </button>
        </div>

        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center mt-12 text-slate-500">
            <p><i class="fas fa-cloud mr-2"></i>TechVapor Database Manager</p>
        </div>
    </div>

</body>
</html>
