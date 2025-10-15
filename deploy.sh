#!/bin/bash

# =========================================
# TechVapor Deploy Script
# =========================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🚀 Iniciando Deploy do TechVapor...${NC}"
echo ""

# 1. Pull das mudanças
echo -e "${YELLOW}📥 Puxando mudanças do Git...${NC}"
git fetch origin
git pull origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Git pull realizado com sucesso${NC}"
else
    echo -e "${RED}❌ Erro ao fazer pull${NC}"
    exit 1
fi

echo ""

# 2. Verificar permissões
echo -e "${YELLOW}🔐 Verificando permissões...${NC}"
chmod -R 755 admin api includes tools pages assets
chmod -R 777 logs uploads

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Permissões atualizadas${NC}"
else
    echo -e "${RED}❌ Erro ao atualizar permissões${NC}"
fi

echo ""

# 3. Verificar .env
echo -e "${YELLOW}🔍 Verificando arquivo .env...${NC}"
if [ -f .env ]; then
    echo -e "${GREEN}✅ Arquivo .env encontrado${NC}"
else
    echo -e "${RED}❌ Arquivo .env NÃO encontrado!${NC}"
    echo -e "${YELLOW}⚠️  Crie o arquivo .env com as configurações do banco de dados${NC}"
fi

echo ""

# 4. Limpar cache (se houver)
echo -e "${YELLOW}🧹 Limpando cache...${NC}"
rm -rf /tmp/techvapor_* 2>/dev/null

echo ""

# 5. Deploy completo
echo -e "${GREEN}✅ Deploy realizado com sucesso!${NC}"
echo ""
echo -e "${YELLOW}📋 Resumo do Deploy:${NC}"
echo "   - ✅ Código atualizado"
echo "   - ✅ Permissões configuradas"
echo "   - ✅ Diretórios verificados"
echo ""
echo -e "${YELLOW}🔗 URLs Importantes:${NC}"
echo "   - Home: https://maroon-louse-320109.hostingersite.com/"
echo "   - Admin: https://maroon-louse-320109.hostingersite.com/admin/login.php"
echo "   - Verificador BD: https://maroon-louse-320109.hostingersite.com/tools/verify_database.php"
echo ""
echo -e "${GREEN}✨ Deploy concluído! Sistema pronto para uso.${NC}"
