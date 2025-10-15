<?php
/**
 * ========================================
 * SCRIPT INSTALADOR DO BANCO DE DADOS
 * ========================================
 * 
 * Executa o schema.sql automaticamente
 * e valida a instalação.
 * 
 * Acesso: /tools/install_schema.php
 */

// Carrega configurações
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Verificar se é POST para confirmar instalação
$install = $_POST['install'] ?? false;
$confirmed = $_POST['confirmed'] ?? false;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador de Schema - TechVapor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .alert {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .alert.info {
            background: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }
        .alert.success {
            background: #e8f5e9;
            border-color: #4caf50;
            color: #2e7d32;
        }
        .alert.error {
            background: #ffebee;
            border-color: #f44336;
            color: #c62828;
        }
        .alert.warning {
            background: #fff3e0;
            border-color: #ff9800;
            color: #e65100;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background: #f5f5f5;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-success {
            background: #c8e6c9;
            color: #2e7d32;
        }
        .status-error {
            background: #ffcdd2;
            color: #c62828;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .button.primary {
            background: #667eea;
            color: white;
        }
        .button.primary:hover {
            background: #5568d3;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .button.secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .button-group {
            text-align: center;
            margin: 30px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: #333;
            cursor: pointer;
        }
        input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalador de Schema - TechVapor</h1>
        <p class="subtitle">Instale as tabelas do banco de dados automaticamente</p>

        <?php
        // Se for solicitação de instalação
        if ($install && $confirmed) {
            echo '<div class="alert info"><span class="loading"></span> ⏳ Executando instalação...</div>';
            
            try {
                $pdo = Database::getConnection();
                $successCount = 0;
                $errors = [];

                // Lista das queries SQL inline (melhor para servidores compartilhados)
                $tables = [
                    // Users table
                    "CREATE TABLE IF NOT EXISTS users (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        phone VARCHAR(20),
                        password_hash VARCHAR(255) NOT NULL,
                        role ENUM('customer', 'admin') DEFAULT 'customer',
                        status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_email (email),
                        INDEX idx_role (role),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Categories table
                    "CREATE TABLE IF NOT EXISTS categories (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(255) NOT NULL UNIQUE,
                        slug VARCHAR(255) NOT NULL UNIQUE,
                        description TEXT,
                        image_url VARCHAR(255),
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_slug (slug),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Products table
                    "CREATE TABLE IF NOT EXISTS products (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        category_id INT NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) NOT NULL UNIQUE,
                        description TEXT,
                        short_description VARCHAR(500),
                        price DECIMAL(10, 2) NOT NULL,
                        cost_price DECIMAL(10, 2),
                        stock_quantity INT DEFAULT 0,
                        image_url VARCHAR(255),
                        status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                        INDEX idx_category (category_id),
                        INDEX idx_slug (slug),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Product images table
                    "CREATE TABLE IF NOT EXISTS product_images (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        product_id INT NOT NULL,
                        image_url VARCHAR(255) NOT NULL,
                        alt_text VARCHAR(255),
                        is_primary BOOLEAN DEFAULT FALSE,
                        display_order INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        INDEX idx_product (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Cart items table
                    "CREATE TABLE IF NOT EXISTS cart_items (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT,
                        session_id VARCHAR(255),
                        product_id INT NOT NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        INDEX idx_user (user_id),
                        INDEX idx_session (session_id),
                        INDEX idx_product (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Orders table
                    "CREATE TABLE IF NOT EXISTS orders (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        order_number VARCHAR(50) NOT NULL UNIQUE,
                        total_amount DECIMAL(10, 2) NOT NULL,
                        discount_amount DECIMAL(10, 2) DEFAULT 0,
                        tax_amount DECIMAL(10, 2) DEFAULT 0,
                        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
                        payment_method VARCHAR(50),
                        payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                        customer_notes TEXT,
                        admin_notes TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
                        INDEX idx_user (user_id),
                        INDEX idx_order_number (order_number),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Order items table
                    "CREATE TABLE IF NOT EXISTS order_items (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        order_id INT NOT NULL,
                        product_id INT NOT NULL,
                        product_name VARCHAR(255) NOT NULL,
                        product_price DECIMAL(10, 2) NOT NULL,
                        quantity INT NOT NULL,
                        total_price DECIMAL(10, 2) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
                        INDEX idx_order (order_id),
                        INDEX idx_product (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Addresses table
                    "CREATE TABLE IF NOT EXISTS addresses (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        type ENUM('billing', 'shipping', 'both') DEFAULT 'shipping',
                        street VARCHAR(255) NOT NULL,
                        number VARCHAR(20) NOT NULL,
                        complement VARCHAR(255),
                        neighborhood VARCHAR(100) NOT NULL,
                        city VARCHAR(100) NOT NULL,
                        state VARCHAR(2) NOT NULL,
                        postal_code VARCHAR(20) NOT NULL,
                        is_default BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        INDEX idx_user (user_id),
                        INDEX idx_type (type)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Audit logs table
                    "CREATE TABLE IF NOT EXISTS audit_logs (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT,
                        action VARCHAR(100) NOT NULL,
                        module VARCHAR(100) NOT NULL,
                        description TEXT,
                        ip_address VARCHAR(50),
                        user_agent VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                        INDEX idx_action (action),
                        INDEX idx_module (module),
                        INDEX idx_created (created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Settings table
                    "CREATE TABLE IF NOT EXISTS settings (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        key_name VARCHAR(255) NOT NULL UNIQUE,
                        key_value LONGTEXT,
                        description VARCHAR(255),
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_key (key_name)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Coupons table
                    "CREATE TABLE IF NOT EXISTS coupons (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        code VARCHAR(50) NOT NULL UNIQUE,
                        description VARCHAR(255),
                        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
                        discount_value DECIMAL(10, 2) NOT NULL,
                        max_uses INT,
                        current_uses INT DEFAULT 0,
                        min_purchase_amount DECIMAL(10, 2),
                        valid_from DATETIME,
                        valid_until DATETIME,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_code (code),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                    // Reviews table
                    "CREATE TABLE IF NOT EXISTS reviews (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        product_id INT NOT NULL,
                        user_id INT NOT NULL,
                        rating INT CHECK (rating >= 1 AND rating <= 5),
                        title VARCHAR(255),
                        comment TEXT,
                        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                        helpful_count INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        INDEX idx_product (product_id),
                        INDEX idx_user (user_id),
                        INDEX idx_status (status),
                        INDEX idx_rating (rating)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ];

                // Executar cada query
                foreach ($tables as $query) {
                    try {
                        $pdo->exec($query);
                        $successCount++;
                    } catch (PDOException $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                echo '<div class="alert success">✅ Instalação concluída com sucesso!</div>';
                echo '<p>Total de tabelas criadas: <strong>' . $successCount . '/' . count($tables) . '</strong></p>';

                if (!empty($errors)) {
                    echo '<div class="alert warning">⚠️ Aviso: ' . count($errors) . ' aviso(s) (tabelas podem já existir)</div>';
                }

                // Verificar tabelas criadas
                echo '<h2 style="margin-top: 30px; margin-bottom: 20px;">📊 Tabelas Criadas</h2>';

                $tables_query = $pdo->query("SHOW TABLES FROM " . DB_NAME);
                $tables_list = $tables_query->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($tables_list)) {
                    echo '<table class="table">';
                    echo '<thead><tr><th>Tabela</th><th>Status</th></tr></thead><tbody>';
                    foreach ($tables_list as $table) {
                        echo '<tr>';
                        echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
                        echo '<td><span class="status-badge status-success">✓ Criada</span></td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert error">❌ Nenhuma tabela foi criada!</div>';
                }

                echo '<div class="button-group">';
                echo '<a href="test_connection.php" class="button secondary">Voltar ao Teste</a>';
                echo '<a href="../index.php" class="button secondary">Ir para Home</a>';
                echo '</div>';

                logInfo("Schema instalado com sucesso. Total de tabelas: " . $successCount, "INSTALLER");

            } catch (Exception $e) {
                echo '<div class="alert error">❌ Erro na instalação: ' . htmlspecialchars($e->getMessage()) . '</div>';
                logError("Erro ao instalar schema: " . $e->getMessage(), "INSTALLER");
            }
        } else {
            // Verificar status atual
            try {
                $pdo = Database::getConnection();
                $tables_query = $pdo->query("SHOW TABLES FROM " . DB_NAME);
                $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
                $hasExistingTables = !empty($tables);

                if ($hasExistingTables) {
                    echo '<div class="alert warning">⚠️ Seu banco de dados já contém ' . count($tables) . ' tabela(s). A instalação irá atualizar/recriar as estruturas.</div>';
                } else {
                    echo '<div class="alert info">ℹ️ Seu banco de dados está vazio. A instalação criará todas as 12 tabelas necessárias.</div>';
                }

                echo '<h2 style="margin: 30px 0 20px 0;">O que será instalado?</h2>';
                echo '<ul style="line-height: 1.8; color: #555;">';
                echo '<li>✓ Tabela de <strong>Usuários</strong> (clientes e admin)</li>';
                echo '<li>✓ Tabela de <strong>Categorias</strong> de produtos</li>';
                echo '<li>✓ Tabela de <strong>Produtos</strong> com descrições e preços</li>';
                echo '<li>✓ Tabela de <strong>Imagens</strong> de produtos</li>';
                echo '<li>✓ Tabela de <strong>Carrinho</strong> de compras</li>';
                echo '<li>✓ Tabela de <strong>Pedidos</strong> e itens</li>';
                echo '<li>✓ Tabela de <strong>Endereços</strong> de entrega</li>';
                echo '<li>✓ Tabela de <strong>Logs</strong> de auditoria</li>';
                echo '<li>✓ Tabela de <strong>Configurações</strong> da aplicação</li>';
                echo '<li>✓ Tabela de <strong>Cupons</strong> e promoções</li>';
                echo '<li>✓ Tabela de <strong>Avaliações</strong> de produtos</li>';
                echo '</ul>';

                echo '<form method="POST" style="margin-top: 30px;">';
                echo '<div class="form-group">';
                echo '<label>';
                echo '<input type="checkbox" name="confirmed" value="1" required>';
                echo ' Confirmo que desejo instalar/atualizar o schema do banco de dados';
                echo '</label>';
                echo '</div>';
                echo '<div class="button-group">';
                echo '<button type="submit" name="install" value="1" class="button primary">🚀 Instalar Schema</button>';
                echo '<a href="test_connection.php" class="button secondary">Cancelar</a>';
                echo '</div>';
                echo '</form>';

            } catch (Exception $e) {
                echo '<div class="alert error">❌ Erro ao conectar: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
