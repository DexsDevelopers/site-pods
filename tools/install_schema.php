<?php
// tools/install_schema.php
// Uso recomendado (apenas uma vez e depois REMOVER o arquivo do servidor):
//   https://SEU-DOMINIO/tools/install_schema.php?ok=1&seed=1

require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (APP_ENV === 'production' && ($_GET['ok'] ?? '') !== '1') {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Confirme com ok=1 na URL para executar em produção.']);
  exit;
}

$statements = [
  // users
  "CREATE TABLE IF NOT EXISTS users (\n\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  name VARCHAR(120) NOT NULL,\n  email VARCHAR(160) NOT NULL UNIQUE,\n  password_hash VARCHAR(255) NOT NULL,\n  is_admin TINYINT(1) NOT NULL DEFAULT 0,\n  is_active TINYINT(1) NOT NULL DEFAULT 1,\n  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

  // categories
  "CREATE TABLE IF NOT EXISTS categories (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  name VARCHAR(120) NOT NULL,\n  is_active TINYINT(1) NOT NULL DEFAULT 1,\n  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

  // products
  "CREATE TABLE IF NOT EXISTS products (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  category_id INT NULL,\n  name VARCHAR(160) NOT NULL,\n  slug VARCHAR(180) NOT NULL UNIQUE,\n  description TEXT NULL,\n  cover_image VARCHAR(500) NULL,\n  price DECIMAL(10,2) NOT NULL,\n  featured TINYINT(1) NOT NULL DEFAULT 0,\n  is_active TINYINT(1) NOT NULL DEFAULT 1,\n  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

  // orders
  "CREATE TABLE IF NOT EXISTS orders (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  customer_name VARCHAR(160) NOT NULL,\n  customer_email VARCHAR(160) NOT NULL,\n  customer_phone VARCHAR(60) NOT NULL,\n  customer_address VARCHAR(255) NOT NULL,\n  total_amount DECIMAL(10,2) NOT NULL,\n  status VARCHAR(40) NOT NULL DEFAULT 'novo',\n  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

  // order_items
  "CREATE TABLE IF NOT EXISTS order_items (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  order_id INT NOT NULL,\n  product_id INT NOT NULL,\n  quantity INT NOT NULL,\n  price DECIMAL(10,2) NOT NULL,\n  subtotal DECIMAL(10,2) NOT NULL,\n  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,\n  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

  // indexes
  "CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)",
  "CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active)",
  "CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at)",
];

try {
  $pdo->beginTransaction();
  foreach ($statements as $sql) {
    $pdo->exec($sql);
  }

  $seed = isset($_GET['seed']) && (string)$_GET['seed'] === '1';
  $seedInfo = null;
  if ($seed) {
    // categorias básicas
    $pdo->exec("INSERT INTO categories (name, is_active) VALUES ('Cirrago Pods',1),('Sabores Frutados',1),('Mentolados',1)\n  ON DUPLICATE KEY UPDATE name=VALUES(name), is_active=VALUES(is_active)");

    // produtos demo (idempotente por slug)
    $insP = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, cover_image, price, featured, is_active)\n      VALUES (:c,:n,:s,:d,:i,:p,:f,:a)\n      ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), cover_image=VALUES(cover_image), price=VALUES(price), featured=VALUES(featured), is_active=VALUES(is_active), category_id=VALUES(category_id)");
    $demo = [
      [1,'Cirrago Pod 600 Puffs - Morango Ice','cirrago-pod-600-morango-ice','Pod descartável sabor Morango com toque gelado.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop',49.90,1,1],
      [2,'Cirrago Pod 600 Puffs - Blueberry','cirrago-pod-600-blueberry','Pod descartável sabor Blueberry.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop',49.90,1,1],
      [3,'Cirrago Pod 600 Puffs - Mint','cirrago-pod-600-mint','Pod descartável sabor Menta refrescante.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop',49.90,0,1],
    ];
    foreach ($demo as $p) {
      $insP->execute([':c'=>$p[0],':n'=>$p[1],':s'=>$p[2],':d'=>$p[3],':i'=>$p[4],':p'=>$p[5],':f'=>$p[6],':a'=>$p[7]]);
    }

    // usuário admin (admin123 / saluc123)
    $hash = password_hash('saluc123', PASSWORD_BCRYPT);
    $insU = $pdo->prepare("INSERT INTO users (name,email,password_hash,is_admin,is_active) VALUES (:n,:e,:h,1,1)\n      ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), is_admin=1, is_active=1");
    $insU->execute([':n'=>'admin123', ':e'=>'admin@pods.local', ':h'=>$hash]);

    $seedInfo = 'Seed executado.';
  }

  $pdo->commit();
  echo json_encode(['success'=>true,'message'=>'Schema instalado com sucesso.', 'seed'=>$seedInfo]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}


