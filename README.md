# 🚀 TechVapor - Plataforma E-Commerce SaaS

Uma plataforma de e-commerce moderna e robusta para venda de vaporizadores e acessórios, construída com **PHP**, **MySQL**, **TailwindCSS** e **Alpine.js**.

## 📋 Características

✅ **Backend Robusto**
- PDO com prepared statements contra SQL injection
- Autenticação segura com bcrypt
- Separação de camadas (config, database, helpers)
- Sistema de logs estruturado

✅ **Banco de Dados Completo**
- 12 tabelas normalizadas
- Relações bem definidas
- Índices otimizados
- Suporte a transações ACID

✅ **Segurança em Primeiro Lugar**
- Proteção contra CSRF
- Validação de entrada em todos os pontos
- Sanitização de dados
- Logs de auditoria
- `.htaccess` para proteção de diretórios sensíveis

✅ **Estrutura Modular e Escalável**
- Configuração centralizada via `.env`
- Funções auxiliares reutilizáveis
- Classe Database com padrão Singleton
- Organização clara de arquivos

---

## 🛠️ Pré-requisitos

- **PHP** 7.4+ com extensões: `pdo`, `pdo_mysql`, `json`
- **MySQL** 5.7+
- **Servidor web** Apache/Nginx com suporte a `.htaccess`
- **Composer** (opcional, para dependências futuras)

---

## 📦 Instalação

### 1️⃣ Clonar o Repositório

```bash
git clone https://github.com/seu-usuario/site-pods.git
cd site-pods
```

### 2️⃣ Configurar Variáveis de Ambiente

```bash
# Copiar template para seu .env local
cp .env.example .env

# Editar com suas credenciais de banco de dados
nano .env
```

**Exemplo de `.env`:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u853242961_loja_pods
DB_USER=u853242961_pods_saluc
DB_PASSWORD=Lucastav8012@
DB_CHARSET=utf8mb4
APP_ENV=development
APP_DEBUG=true
```

### 3️⃣ Criar Estrutura de Diretórios

```bash
mkdir -p logs uploads admin api templates assets/js assets/css
chmod 755 logs uploads
```

### 4️⃣ Instalar Schema do Banco de Dados

Acesse no navegador:
```
http://localhost/site-pods/tools/install_schema.php
```

Ou execute manualmente:
```bash
mysql -h localhost -u usuario -p banco < sql/schema.sql
mysql -h localhost -u usuario -p banco < sql/seeds.sql
```

### 5️⃣ Testar Conexão

Acesse:
```
http://localhost/site-pods/tools/test_connection.php
```

---

## 📁 Estrutura do Projeto

```
site-pods/
├── .env                      # Configurações (não versionado)
├── .env.example              # Template de configuração
├── .gitignore                # Arquivos ignorados pelo Git
├── README.md                 # Esta documentação
│
├── admin/                    # Painel administrativo
│   ├── index.php
│   ├── products.php
│   ├── orders.php
│   └── ...
│
├── api/                      # Endpoints REST/JSON
│   ├── cart.php
│   ├── checkout.php
│   └── ...
│
├── includes/                 # Camada de lógica
│   ├── config.php           # Carregador de .env
│   ├── db.php               # Classe Database com PDO
│   └── helpers.php          # Funções auxiliares
│
├── templates/               # Componentes reutilizáveis
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── ...
│
├── assets/
│   ├── css/                 # TailwindCSS e estilos
│   │   └── app.css
│   └── js/                  # Alpine.js e scripts
│       └── app.js
│
├── sql/                     # Scripts de banco de dados
│   ├── schema.sql          # Tabelas e estrutura
│   └── seeds.sql           # Dados iniciais
│
├── logs/                    # Logs da aplicação
│   └── .htaccess           # Protege acesso direto
│
├── uploads/                # Uploads de usuários
│   └── .htaccess           # Bloqueia execução PHP
│
├── tools/                  # Ferramentas de desenvolvimento
│   ├── test_connection.php
│   ├── install_schema.php
│   └── ...
│
└── pages/                  # Páginas públicas
    ├── home.php
    ├── shop.php
    ├── product.php
    ├── cart.php
    └── checkout.php
```

---

## 🔑 Credenciais Padrão

**Admin (após instalação do schema):**
```
Email: admin@techvapor.local
Senha: admin123
```

⚠️ **Altere a senha imediatamente em produção!**

---

## 📚 API Endpoints

### Produtos
```
GET  /api/products.php           # Listar todos os produtos
GET  /api/products.php?id=1      # Obter produto específico
```

### Carrinho
```
POST /api/cart.php               # Adicionar ao carrinho
GET  /api/cart.php               # Obter itens do carrinho
DELETE /api/cart.php?item_id=1   # Remover do carrinho
```

### Checkout
```
POST /api/checkout.php           # Finalizar compra
```

---

## 🔐 Segurança

### Prepared Statements
Todas as queries usam prepared statements com PDO:

```php
$stmt = Database::execute(
    "SELECT * FROM products WHERE id = ? AND status = ?",
    [$id, 'active']
);
```

### Hash de Senhas
Senhas são hasheadas com bcrypt (custo 12):

```php
$hash = hashPassword($password);
$verified = verifyPassword($password, $hash);
```

### Token CSRF
Proteção contra CSRF em formulários:

```php
$token = generateCSRFToken();
// No formulário: <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
```

### Logs de Auditoria
Todas as ações importantes são registradas:

```php
logInfo("Usuário {$user_id} criou novo produto", "PRODUCTS");
logError("Falha ao processar pagamento: {$error}", "PAYMENTS");
```

---

## 🧪 Funções Auxiliares Disponíveis

### Logging
```php
logInfo($message, $module)      // Info
logError($message, $module)     // Erro
logWarning($message, $module)   # Aviso
logDebug($message, $module)     # Debug
```

### Validação
```php
validateEmail($email)           # Email válido?
validatePhone($phone)           # Telefone válido?
validateInteger($value)         # Inteiro?
validateFloat($value)           # Float?
```

### Sanitização
```php
sanitize($input)                # Remove tags e caracteres perigosos
```

### Segurança
```php
hashPassword($password)         # Hash bcrypt
verifyPassword($pwd, $hash)    # Verifica hash
generateToken($length)          # Token aleatório
generateCSRFToken()             # Token CSRF
validateCSRFToken($token)       # Valida CSRF
```

### Formatação
```php
formatCurrency($value)          # Formata para BRL: "R$ 123,45"
```

### HTTP/Resposta
```php
jsonResponse($data, $code)      # Retorna JSON
redirect($url)                  # Redireciona
redirectWithSuccess($url, $msg) # Redireciona com mensagem
isAjaxRequest()                 # É AJAX?
isPost()                        # É POST?
isGet()                         # É GET?
```

---

## 🚀 Deployment em Produção

### Checklist de Segurança

- [ ] Alterar `APP_DEBUG` para `false` no `.env`
- [ ] Alterar `APP_ENV` para `production` no `.env`
- [ ] Trocar senhas padrão do admin
- [ ] Configurar HTTPS obrigatório
- [ ] Remover scripts de teste (`tools/`)
- [ ] Configurar backups automáticos
- [ ] Revisar permissões de arquivo (755 para dirs, 644 para files)
- [ ] Habilitar logs e monitorar regularmente
- [ ] Configurar WAF (Web Application Firewall)

### Backup do Banco de Dados

```bash
# Backup
mysqldump -u usuario -p banco > backup_$(date +%Y%m%d).sql

# Restaurar
mysql -u usuario -p banco < backup_20241015.sql
```

---

## 📝 Variáveis de Ambiente Disponíveis

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `DB_HOST` | localhost | Host do banco de dados |
| `DB_PORT` | 3306 | Porta MySQL |
| `DB_NAME` | techvapor_db | Nome do banco |
| `DB_USER` | root | Usuário MySQL |
| `DB_PASSWORD` | - | Senha MySQL |
| `DB_CHARSET` | utf8mb4 | Charset |
| `APP_NAME` | TechVapor | Nome da app |
| `APP_ENV` | production | development ou production |
| `APP_DEBUG` | false | true ou false |
| `APP_URL` | http://localhost | URL da aplicação |
| `SESSION_LIFETIME` | 3600 | Tempo de sessão (segundos) |
| `LOG_LEVEL` | info | info, warning, error, debug |
| `LOG_PATH` | logs/ | Diretório de logs |

---

## 🐛 Troubleshooting

### Erro: "Arquivo .env não encontrado"
```bash
cp .env.example .env
# Editar as variáveis conforme necessário
```

### Erro: "Conexão recusada" (MySQL)
- Verificar se MySQL está rodando
- Validar host, user, password
- Verificar se o banco foi criado

### Erro: "Permissão negada" em logs/uploads
```bash
chmod 755 logs uploads
chmod 644 logs/.htaccess uploads/.htaccess
```

### Erro: "CSRF token inválido"
- Garantir que sessões estão habilitadas
- Verificar se cookies estão funcionando
- Limpar cache do navegador

---

## 📞 Suporte e Contato

- **Email**: contato@techvapor.local
- **Telefone**: (11) 9999-9999
- **GitHub Issues**: [Abrir issue](https://github.com/seu-usuario/site-pods/issues)

---

## 📄 Licença

Este projeto é licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

---

## 🙏 Contribuindo

1. Fork o projeto
2. Crie sua feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

---

**Desenvolvido com ❤️ para TechVapor**
