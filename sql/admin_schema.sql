-- ========================================
-- WAZZY PODS - ADMIN DATABASE SCHEMA
-- ========================================

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50) DEFAULT 'fas fa-box',
    cor VARCHAR(7) DEFAULT '#8B5CF6',
    ativo BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    descricao_curta VARCHAR(500),
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2),
    imagem VARCHAR(500),
    galeria JSON,
    estoque INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    caracteristicas JSON,
    tags VARCHAR(500),
    visualizacoes INT DEFAULT 0,
    vendas INT DEFAULT 0,
    avaliacao_media DECIMAL(2,1) DEFAULT 0,
    total_avaliacoes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_destaque (destaque),
    INDEX idx_ativo (ativo),
    INDEX idx_categoria (categoria_id),
    INDEX idx_preco (preco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de configurações do site
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(50) DEFAULT 'text',
    grupo VARCHAR(50) DEFAULT 'geral',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de banners/slides
CREATE TABLE IF NOT EXISTS banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255),
    subtitulo VARCHAR(500),
    imagem VARCHAR(500) NOT NULL,
    link VARCHAR(500),
    botao_texto VARCHAR(100),
    posicao VARCHAR(50) DEFAULT 'hero',
    ativo BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de administradores
CREATE TABLE IF NOT EXISTS administradores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    avatar VARCHAR(500),
    ultimo_acesso TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir categorias padrão
INSERT INTO categorias (nome, slug, descricao, icone, cor, ordem) VALUES
('Pods Descartáveis', 'pods-descartaveis', 'Pods descartáveis com diversos sabores', 'fas fa-wind', '#8B5CF6', 1),
('Pods Recarregáveis', 'pods-recarregaveis', 'Pods recarregáveis de alta qualidade', 'fas fa-sync', '#EC4899', 2),
('Líquidos', 'liquidos', 'Líquidos premium para seu pod', 'fas fa-tint', '#06B6D4', 3),
('Acessórios', 'acessorios', 'Acessórios e peças de reposição', 'fas fa-tools', '#10B981', 4),
('Kits Iniciantes', 'kits-iniciantes', 'Kits completos para começar', 'fas fa-gift', '#F59E0B', 5);

-- Inserir produtos de exemplo
INSERT INTO produtos (categoria_id, nome, slug, descricao, descricao_curta, preco, preco_promocional, imagem, estoque, sku, destaque, caracteristicas) VALUES
(1, 'Pod Descartável Strawberry Ice 5000 Puffs', 'pod-strawberry-ice-5000', 'Pod descartável sabor morango com toque gelado. 5000 puffs de pura satisfação.', 'Sabor morango gelado com 5000 puffs', 89.90, 79.90, '/uploads/products/strawberry-ice.jpg', 50, 'POD-STR-5000', TRUE, '{"puffs": "5000", "nicotina": "5%", "bateria": "1500mAh", "sabor": "Strawberry Ice"}'),
(1, 'Pod Descartável Mango Tango 6000 Puffs', 'pod-mango-tango-6000', 'Pod descartável sabor manga tropical. 6000 puffs de sabor intenso.', 'Sabor manga tropical com 6000 puffs', 99.90, NULL, '/uploads/products/mango-tango.jpg', 30, 'POD-MNG-6000', TRUE, '{"puffs": "6000", "nicotina": "5%", "bateria": "1800mAh", "sabor": "Mango"}'),
(2, 'Pod Recarregável Pro Max', 'pod-recarregavel-pro-max', 'Pod recarregável com bateria de longa duração e tanque de 4ml.', 'Pod recarregável profissional', 199.90, 179.90, '/uploads/products/pro-max.jpg', 25, 'POD-PRO-MAX', TRUE, '{"bateria": "2000mAh", "tanque": "4ml", "potencia": "25W", "carregamento": "Type-C"}'),
(3, 'Líquido Premium Mint Fresh 30ml', 'liquido-mint-fresh-30ml', 'Líquido premium sabor menta refrescante. Fórmula balanceada com 50/50 VG/PG.', 'Líquido sabor menta 30ml', 39.90, NULL, '/uploads/products/mint-liquid.jpg', 100, 'LIQ-MINT-30', FALSE, '{"volume": "30ml", "vg_pg": "50/50", "nicotina": "3mg", "sabor": "Mint"}'),
(4, 'Carregador Turbo USB-C', 'carregador-turbo-usbc', 'Carregador rápido USB-C para todos os modelos de pods recarregáveis.', 'Carregador rápido USB-C', 49.90, NULL, '/uploads/products/charger.jpg', 40, 'ACC-CHRG-USBC', FALSE, '{"tipo": "USB-C", "potencia": "25W", "cabo": "1.5m"}');

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, tipo, grupo) VALUES
('site_nome', 'Wazzy Pods', 'text', 'geral'),
('site_descricao', 'Sua loja premium de pods', 'textarea', 'geral'),
('site_email', 'contato@wazzypods.com', 'email', 'geral'),
('site_telefone', '(11) 99999-9999', 'text', 'geral'),
('site_endereco', 'São Paulo, SP', 'text', 'geral'),
('produtos_por_pagina', '12', 'number', 'produtos'),
('mostrar_produtos_destaque', 'true', 'boolean', 'produtos'),
('habilitar_promocoes', 'true', 'boolean', 'produtos');

-- Inserir admin padrão (senha: admin123)
INSERT INTO administradores (nome, email, senha) VALUES
('Admin', 'admin@wazzypods.com', '$2y$10$YpVKJL8H.iovPZHNqq8LNOp7ITaX4N8YGZJZm8yH5EZPNp4FqRH9.');
