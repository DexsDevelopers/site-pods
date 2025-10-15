# 🚀 Guia de Deploy - TechVapor

## 📋 Informações do Servidor

- **Host:** maroon-louse-320109.hostingersite.com
- **Caminho Público:** `/public_html/`
- **Banco de Dados:** u853242961_loja_pods
- **Usuário DB:** u853242961_pods_saluc

---

## 🔧 Opção 1: Deploy via Git (SSH)

Se você tem acesso SSH ao servidor:

```bash
# 1. Conectar ao servidor
ssh user@maroon-louse-320109.hostingersite.com

# 2. Navegar para o diretório
cd public_html/

# 3. Fazer pull das mudanças
git pull origin main

# 4. Ajustar permissões
chmod -R 755 admin api includes tools pages assets
chmod -R 777 logs uploads

# 5. Verificar banco de dados
curl https://maroon-louse-320109.hostingersite.com/tools/verify_database.php
```

---

## 📁 Opção 2: Deploy via SFTP (Recomendado)

### Passo 1: Clonar ou Atualizar Localmente

```bash
# Se ainda não tem o repositório
git clone https://github.com/DexsDevelopers/site-pods.git

# Se já tem, apenas atualizar
git pull origin main
```

### Passo 2: Conectar via SFTP

Usando **FileZilla** ou **WinSCP**:

- **Host:** maroon-louse-320109.hostingersite.com
- **Username:** seu_user_ftp
- **Password:** sua_senha_ftp
- **Protocolo:** SFTP
- **Porta:** 22

### Passo 3: Fazer Upload

1. Conectar ao SFTP
2. Navegar para `/public_html/`
3. Fazer upload de **todos os arquivos** do repositório

```
site-pods/
├── admin/              ← Upload completo
├── api/                ← Upload completo
├── assets/             ← Upload completo
├── includes/           ← Upload completo
├── pages/              ← Upload completo
├── tools/              ← Upload completo
├── sql/                ← Upload completo
├── index.php           ← Upload
├── .gitignore          ← Não é necessário
└── ... outros arquivos
```

### Passo 4: Verificar/Criar .env

⚠️ **IMPORTANTE:** O arquivo `.env` não está no repositório por segurança!

1. Criar arquivo `.env` via cPanel ou SFTP
2. Adicionar o seguinte conteúdo:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u853242961_loja_pods
DB_USER=u853242961_pods_saluc
DB_PASSWORD=Lucastav8012@
DB_CHARSET=utf8mb4

APP_NAME=TechVapor
APP_ENV=production
APP_DEBUG=false
SESSION_LIFETIME=3600
CSRF_TOKEN_LENGTH=32
HASH_ALGORITHM=bcrypt
LOG_LEVEL=warning
LOG_PATH=logs/

UPLOAD_PATH=uploads/
UPLOAD_MAX_SIZE=5242880
```

### Passo 5: Ajustar Permissões

Via cPanel File Manager:

1. Selecionar pasta `admin` → Permissions → `755`
2. Selecionar pasta `api` → Permissions → `755`
3. Selecionar pasta `logs` → Permissions → `777`
4. Selecionar pasta `uploads` → Permissions → `777`

**Ou via Terminal:**

```bash
chmod -R 755 admin api includes tools pages assets
chmod -R 777 logs uploads
```

---

## ✅ Passo 6: Verificar Instalação

Depois de fazer o deploy, execute:

### 1️⃣ Verificador de Banco de Dados

Acesse:
```
https://maroon-louse-320109.hostingersite.com/tools/verify_database.php
```

✅ Você verá o status de todas as tabelas. Se faltar alguma, será criada automaticamente!

### 2️⃣ Teste de Conexão

Acesse:
```
https://maroon-louse-320109.hostingersite.com/tools/test_connection.php
```

✅ Verificará se tudo está funcionando

### 3️⃣ Acessar Admin

Acesse:
```
https://maroon-louse-320109.hostingersite.com/admin/login.php
```

**Credenciais de Demo:**
- Email: `admin@techvapor.com`
- Senha: `admin123`

---

## 🎯 Checklist de Deploy

- [ ] Código atualizado do Git
- [ ] Arquivo `.env` criado com credenciais
- [ ] Diretórios `logs/` e `uploads/` com permissão 777
- [ ] Permissões dos demais diretórios configuradas como 755
- [ ] Verificador BD executado com sucesso
- [ ] Todas as 11 tabelas criadas ✅
- [ ] Admin acessível e funcionando
- [ ] Home carregando produtos do BD

---

## 🔍 Passos de Verificação

### Verificar Tabelas

```
https://maroon-louse-320109.hostingersite.com/tools/verify_database.php
```

Deve mostrar:
- ✅ users
- ✅ categories
- ✅ products
- ✅ orders
- ✅ order_items
- ✅ reviews
- ✅ coupons
- ✅ addresses
- ✅ audit_logs
- ✅ settings
- ✅ product_images

### Verificar Admin

```
https://maroon-louse-320109.hostingersite.com/admin/login.php
```

Deve permitir login com `admin@techvapor.com` / `admin123`

### Verificar Home

```
https://maroon-louse-320109.hostingersite.com/
```

Deve exibir:
- Navbar com Logo TechVapor
- Hero Section
- Produtos carregando do BD
- Sabores
- Blog
- Avaliações
- Newsletter

---

## ⚠️ Possíveis Problemas

### ❌ "Connection refused"
**Solução:** Verifique se o `.env` está correto

### ❌ "Access denied for user"
**Solução:** Confirme as credenciais do BD no `.env`

### ❌ "Table doesn't exist"
**Solução:** Acesse `/tools/verify_database.php` para criar as tabelas

### ❌ "Permission denied"
**Solução:** Ajuste as permissões das pastas conforme descrito

### ❌ "Blank page"
**Solução:** Verifique os logs em `/logs/` via SFTP

---

## 📊 URLs Importantes Pós-Deploy

| URL | Descrição |
|-----|-----------|
| https://maroon-louse-320109.hostingersite.com/ | Home Page |
| https://maroon-louse-320109.hostingersite.com/admin/login.php | Login Admin |
| https://maroon-louse-320109.hostingersite.com/admin/ | Dashboard Admin |
| https://maroon-louse-320109.hostingersite.com/tools/verify_database.php | Verificador BD |
| https://maroon-louse-320109.hostingersite.com/tools/test_connection.php | Teste de Conexão |
| https://maroon-louse-320109.hostingersite.com/pages/product-detail.php | Página de Produto |
| https://maroon-louse-320109.hostingersite.com/pages/cart.php | Carrinho |

---

## 🔄 Atualizações Futuras

Para atualizar o site com novas mudanças:

```bash
# 1. No seu computador, fazer mudanças e commit
git add .
git commit -m "descrição das mudanças"
git push origin main

# 2. No servidor, fazer pull
git pull origin main

# 3. Se criou novas tabelas, rodar verificador
# Acessar: /tools/verify_database.php
```

---

## 📞 Suporte

Se tiver problemas:

1. Verifique os logs em `/logs/`
2. Acesse `/tools/test_connection.php`
3. Acesse `/tools/verify_database.php`
4. Verifique permissões das pastas

---

## ✨ Deploy Bem-Sucedido!

Se chegou até aqui e tudo funciona, parabéns! 🎉

Seu site **TechVapor** está **online e pronto para vender**! 🚀

