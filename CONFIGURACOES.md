# 🔧 Guia de Configuração - Wazzy Pods

## ⚠️ Problema Detectado

O arquivo `.env` não está configurado. Este arquivo é **essencial** para que a aplicação se conecte ao banco de dados.

---

## 📝 Como Configurar

### 1. Criar arquivo `.env`

Na raiz do projeto, crie um arquivo chamado `.env` com o seguinte conteúdo:

```env
# ========================================
# CONFIGURAÇÕES DO BANCO DE DADOS (HOSTINGER)
# ========================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u853242961_loja_pods
DB_USER=u853242961_pods_salu
DB_PASSWORD=Lucastav8012@
DB_CHARSET=utf8mb4

# ========================================
# CONFIGURAÇÕES DA APLICAÇÃO
# ========================================
APP_NAME=Wazzy Pods
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wazzypods.com

# ========================================
# CONFIGURAÇÕES DE SESSÃO
# ========================================
SESSION_LIFETIME=3600
CSRF_TOKEN_LENGTH=32
HASH_ALGORITHM=bcrypt

# ========================================
# CONFIGURAÇÕES DE LOGS
# ========================================
LOG_LEVEL=info
LOG_PATH=logs/
```

### 2. Para desenvolvimento LOCAL (XAMPP)

Se estiver desenvolvendo localmente, use:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=loja_pods
DB_USER=root
DB_PASSWORD=
```

### 3. Para PRODUÇÃO (Hostinger)

Use as credenciais fornecidas:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u853242961_loja_pods
DB_USER=u853242961_pods_salu
DB_PASSWORD=Lucastav8012@
```

---

## ✅ Verificar Configuração

Depois de criar o `.env`, as configurações carregarão automaticamente:

1. **Header** carregará: nome da loja, telefone, email
2. **Footer** carregará: nome, telefone, email, endereço
3. **Admin** funcionará normalmente

---

## 🔐 Segurança

⚠️ **IMPORTANTE**: O arquivo `.env` está no `.gitignore` por segurança. **Nunca faça commit** deste arquivo no Git!

---

## 🚀 Próximos Passos

Depois de configurar o `.env`:

1. Recarregue a página do admin
2. Vá para **Configurações**
3. Mude o telefone, nome, email
4. Clique em **Salvar**
5. Recarregue a página inicial
6. As mudanças aparecerão no header e footer ✅
