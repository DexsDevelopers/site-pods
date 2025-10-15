-- ========================================
-- DADOS INICIAIS - TECHVAPOR
-- ========================================

-- ========================================
-- 1. INSERIR USUÁRIO ADMIN
-- ========================================
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('Administrador', 'admin@techvapor.local', '11999999999', '$2y$12$K4h/pw9VfCJ0DH9vx8Hote8H2r7nGh3O/kyP.2VH7Vu5A2gF4.K.C', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- ========================================
-- 2. INSERIR CATEGORIAS
-- ========================================
INSERT INTO categories (name, slug, description, status) VALUES
('Vaporizadores', 'vaporizadores', 'Vaporizadores modernos e de alta performance', 'active'),
('Acessórios', 'acessorios', 'Acessórios e peças de reposição', 'active'),
('Líquidos', 'liquidos', 'E-liquids e aromas premium', 'active'),
('Baterias e Carregadores', 'baterias-carregadores', 'Baterias e carregadores de alta qualidade', 'active'),
('Bobinas e Resistências', 'bobinas-resistencias', 'Bobinas de reposição para vaporizadores', 'active')
ON DUPLICATE KEY UPDATE slug=VALUES(slug);

-- ========================================
-- 3. INSERIR PRODUTOS
-- ========================================
INSERT INTO products (category_id, name, slug, description, short_description, price, cost_price, stock_quantity, status) VALUES
(1, 'Vapor Premium X-01', 'vapor-premium-x01', 
 'Vaporizador de última geração com tecnologia avançada, bateria de longa duração e design minimalista. Compatível com todas as bobinas padrão.',
 'Vaporizador premium com tecnologia de ponta', 299.90, 150.00, 50, 'active'),

(1, 'Aero Compact 2024', 'aero-compact-2024',
 'Modelo compacto ideal para portabilidade. Tela OLED, bateria integrada de 2500mAh e controle de temperatura precisso.',
 'Vaporizador compacto e portátil', 199.90, 100.00, 35, 'active'),

(1, 'Pro Max Series', 'pro-max-series',
 'Vaporizador profissional com vapor intenso e dissipador de calor avançado. Ideal para usuários experientes.',
 'Vapor potente e profissional', 449.90, 220.00, 20, 'active'),

(2, 'Caso de Proteção Ultra Slim', 'caso-protecao-ultra-slim',
 'Case minimalista de silicone resistente com proteção contra quedas e impactos. Design elegante e discreto.',
 'Proteção estilosa para seu vaporizador', 89.90, 30.00, 100, 'active'),

(2, 'Kit Limpeza Premium', 'kit-limpeza-premium',
 'Kit completo com pincéis, solvente e pano de microfibra para manutenção adequada do vaporizador.',
 'Manutenção e limpeza profissional', 49.90, 20.00, 80, 'active'),

(3, 'E-Liquid Clássico - Tabaco Dourado', 'eliquid-tabaco-dourado',
 'Líquido premium com aroma clássico de tabaco de qualidade. Fórmula balanceada 50/50 VG/PG.',
 'Sabor autêntico de tabaco premium', 39.90, 12.00, 150, 'active'),

(3, 'E-Liquid Frutado - Frutas Vermelhas', 'eliquid-frutas-vermelhas',
 'Mistura de frutas vermelhas com sabor intenso e refrescante. Alto VG para vapor denso.',
 'Sabor frutado intenso e refrescante', 44.90, 14.00, 120, 'active'),

(4, 'Bateria 21700 5000mAh', 'bateria-21700-5000mah',
 'Bateria de alto desempenho com taxa de descarga segura. Compatível com a maioria dos vaporizadores.',
 'Bateria recarregável de alta capacidade', 79.90, 30.00, 60, 'active'),

(4, 'Carregador Duplo USB-C', 'carregador-duplo-usb-c',
 'Carregador portátil com entrada USB-C de carga rápida. Suporta 2 baterias simultaneamente.',
 'Carregamento rápido e prático', 99.90, 40.00, 45, 'active'),

(5, 'Bobina Mesh 0.15Ω', 'bobina-mesh-015',
 'Bobina mesh de alta performance com melhor produção de vapor e sabor intenso.',
 'Bobina mesh para vapor intenso', 29.90, 8.00, 200, 'active');

-- ========================================
-- 4. INSERIR CONFIGURAÇÕES PADRÃO
-- ========================================
INSERT INTO settings (key_name, key_value, description) VALUES
('site_name', 'TechVapor', 'Nome do site'),
('site_description', 'Sua loja de vaporizadores e acessórios premium', 'Descrição do site'),
('site_email', 'contato@techvapor.local', 'Email de contato'),
('site_phone', '(11) 9999-9999', 'Telefone de contato'),
('currency', 'BRL', 'Moeda utilizada'),
('tax_rate', '7.00', 'Taxa de imposto padrão (%)'),
('shipping_fee', '15.00', 'Taxa de envio padrão'),
('min_order_value', '50.00', 'Valor mínimo do pedido')
ON DUPLICATE KEY UPDATE key_value=VALUES(key_value);
