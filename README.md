# Pods Store (PHP + MySQL)

Loja moderna de pods (Cirrago eletrônico) com TailwindCSS, Bootstrap 5, ícones Lucide/FontAwesome, animações AOS/GSAP e tema claro/escuro com toggle.

## Deploy rápido (Hostinger)
1. Crie um banco MySQL e salve as credenciais.
2. Faça upload dos arquivos para `public_html`.
3. Crie o arquivo `.env` na raiz com:
```
APP_NAME=Pods Store
APP_ENV=production
APP_URL=https://seu-dominio.com
DB_HOST=localhost
DB_NAME=seu_banco
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
TIMEZONE=America/Sao_Paulo
```
4. No MySQL, execute `sql/schema.sql` e depois `sql/seeds.sql`.
5. Acesse `/admin/login` com o admin criado (troque a senha no banco após o primeiro acesso).

## Estrutura
- `admin`: painel administrativo (login, dashboard, produtos, categorias, pedidos)
- `api`: endpoints REST (carrinho, checkout)
- `includes`: config/env, PDO, helpers
- `pages`: páginas públicas (home, loja, produto, carrinho, checkout, sucesso)
- `templates`: header, navbar, footer
- `assets`: css/js custom
- `sql`: schema e seeds

## Segurança
- Não commitar `.env`.
- Ajuste CSP conforme necessidades de CDN.
- Ative HTTPS.
