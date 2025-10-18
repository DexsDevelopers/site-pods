-- Wazzy Pods - Estrutura completa
-- IMPORTAR DIRETO NO PAINEL OU PHPMYADMIN

-- Tabela Categorias
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

-- Tabela Produtos
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

-- Tabela Administradores
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

-- Tabela Configurações Gerais
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(50) DEFAULT 'text',
    grupo VARCHAR(50) DEFAULT 'geral',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Banners
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

-- Categorias iniciais
INSERT INTO categorias (nome, slug, descricao, icone, cor, ordem) VALUES
('Pods Descartáveis', 'pods-descartaveis', 'Pods descartáveis com diversos sabores', 'fas fa-wind', '#8B5CF6', 1),
('Pods Recarregáveis', 'pods-recarregaveis', 'Pods recarregáveis de alta qualidade', 'fas fa-sync', '#EC4899', 2),
('Líquidos', 'liquidos', 'Líquidos premium para seu pod', 'fas fa-tint', '#06B6D4', 3),
('Acessórios', 'acessorios', 'Acessórios e peças de reposição', 'fas fa-tools', '#10B981', 4),
('Kits Iniciantes', 'kits-iniciantes', 'Kits completos para começar', 'fas fa-gift', '#F59E0B', 5);

-- Admin padrão (senha: admin123)
INSERT INTO administradores (nome, email, senha) VALUES
('Admin', 'admin@wazzypods.com', '$2y$10$SZKnKf/xzwbA4k3vT9Rp8uVURmKAgP/qQIf39WzV8uuvwATkgguZO');

-- Config geral
INSERT INTO configuracoes (chave, valor, tipo, grupo) VALUES
('site_nome', 'Wazzy Pods', 'text', 'geral'),
('site_descricao', 'Sua loja premium de pods', 'textarea', 'geral'),
('site_email', 'contato@wazzypods.com', 'email', 'geral'),
('site_telefone', '(11) 99999-9999', 'text', 'geral'),
('site_endereco', 'São Paulo, SP', 'text', 'geral'),
('produtos_por_pagina', '12', 'number', 'produtos'),
('mostrar_produtos_destaque', 'true', 'boolean', 'produtos'),
('habilitar_promocoes', 'true', 'boolean', 'produtos');
