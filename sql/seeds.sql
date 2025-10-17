-- ========================================
-- DADOS INICIAIS - LOJA DE PODS
-- ========================================

-- ========================================
-- 1. INSERIR USUÁRIO ADMIN
-- ========================================
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('Administrador', 'admin@lojadepods.local', '11999999999', '$2y$12$K4h/pw9VfCJ0DH9vx8Hote8H2r7nGh3O/kyP.2VH7Vu5A2gF4.K.C', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- ========================================
-- 2. INSERIR CATEGORIAS
-- ========================================
INSERT INTO categories (name, slug, description, status) VALUES
('Pods Descartáveis', 'pods-descartaveis', 'Pods prontos para uso, praticidade máxima', 'active'),
('Pods Recarregáveis', 'pods-recarregaveis', 'Sistemas de pods com cartucho recarregável', 'active'),
('Acessórios', 'acessorios', 'Acessórios e peças de reposição', 'active'),
('Líquidos', 'liquidos', 'E-liquids e aromas premium', 'active'),
('Carregadores e Cabos', 'carregadores-cabos', 'Carregadores, cabos e adaptadores', 'active')
ON DUPLICATE KEY UPDATE slug=VALUES(slug);

-- ========================================
-- 3. INSERIR PRODUTOS
-- ========================================
INSERT INTO products (category_id, name, slug, description, short_description, price, cost_price, stock_quantity, status) VALUES
(1, 'Pod Descartável X-01', 'pod-descartavel-x01', 
 'Pod descartável com até 5000 puffs, bateria integrada e sabores intensos. Pronto para uso, sem manutenção.',
 'Pod descartável com alta autonomia', 79.90, 35.00, 200, 'active'),

(1, 'Pod Descartável Frutas Tropicais', 'pod-descartavel-frutas-tropicais',
 'Blend tropical com notas de manga e maracujá. Experiência refrescante e equilibrada.',
 'Sabor tropical refrescante', 79.90, 35.00, 180, 'active'),

(2, 'Pod Recarregável Pro Kit', 'pod-recarregavel-pro-kit',
 'Sistema de pod recarregável com cartucho substituível, bateria 1000mAh e airflow ajustável.',
 'Pod recarregável de alta performance', 199.90, 95.00, 80, 'active'),

(3, 'Caso de Proteção Ultra Slim', 'caso-protecao-ultra-slim',
 'Case minimalista de silicone resistente com proteção contra quedas e impactos. Design elegante e discreto.',
 'Proteção estilosa para seu pod', 89.90, 30.00, 100, 'active'),

(3, 'Kit Limpeza Premium', 'kit-limpeza-premium',
 'Kit completo com pincéis, solvente e pano de microfibra para manutenção adequada do seu pod recarregável.',
 'Manutenção e limpeza profissional', 49.90, 20.00, 80, 'active'),

(4, 'E-Liquid Clássico - Tabaco Dourado', 'eliquid-tabaco-dourado',
 'Líquido premium com aroma clássico de tabaco de qualidade. Fórmula balanceada 50/50 VG/PG.',
 'Sabor autêntico de tabaco premium', 39.90, 12.00, 150, 'active'),

(4, 'E-Liquid Frutado - Frutas Vermelhas', 'eliquid-frutas-vermelhas',
 'Mistura de frutas vermelhas com sabor intenso e refrescante. Alto VG para vapor denso.',
 'Sabor frutado intenso e refrescante', 44.90, 14.00, 120, 'active'),

(5, 'Carregador Duplo USB-C', 'carregador-duplo-usb-c',
 'Carregador portátil com entrada USB-C de carga rápida. Suporta 2 dispositivos simultaneamente.',
 'Carregamento rápido e prático', 99.90, 40.00, 45, 'active');

-- ========================================
-- 4. INSERIR CONFIGURAÇÕES PADRÃO
-- ========================================
INSERT INTO settings (key_name, key_value, description) VALUES
('site_name', 'Loja de Pods', 'Nome do site'),
('site_description', 'Sua loja de pods descartáveis e recarregáveis', 'Descrição do site'),
('site_email', 'contato@lojadepods.local', 'Email de contato'),
('site_phone', '(11) 9999-9999', 'Telefone de contato'),
('currency', 'BRL', 'Moeda utilizada'),
('tax_rate', '7.00', 'Taxa de imposto padrão (%)'),
('shipping_fee', '15.00', 'Taxa de envio padrão'),
('min_order_value', '50.00', 'Valor mínimo do pedido')
ON DUPLICATE KEY UPDATE key_value=VALUES(key_value);
