-- Usuário admin: senha = admin123 (gere o hash no PHP e substitua abaixo)
-- admin: admin123 / senha: saluc123
-- hash gerado com password_hash('saluc123', PASSWORD_BCRYPT)
INSERT INTO users (name, email, password_hash, is_admin) VALUES
('admin123', 'admin@pods.local', '$2y$10$8ZsKUQy8N0FqjVwC1sT0R.2Lw0yqV4mWJ2vG8Sx7m6nQyOQqYt6yK', 1);

INSERT INTO categories (name, is_active) VALUES
('Cirrago Pods', 1),
('Sabores Frutados', 1),
('Mentolados', 1);

INSERT INTO products (category_id, name, slug, description, cover_image, price, featured, is_active) VALUES
(1,'Cirrago Pod 600 Puffs - Morango Ice','cirrago-pod-600-morango-ice','Pod descartável sabor Morango com toque gelado.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop', 49.90,1,1),
(2,'Cirrago Pod 600 Puffs - Blueberry','cirrago-pod-600-blueberry','Pod descartável sabor Blueberry.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop', 49.90,1,1),
(3,'Cirrago Pod 600 Puffs - Mint','cirrago-pod-600-mint','Pod descartável sabor Menta refrescante.','https://images.unsplash.com/photo-1615634260167-c1013f4c6f75?q=80&w=1200&auto=format&fit=crop', 49.90,0,1);


