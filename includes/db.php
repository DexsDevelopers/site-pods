<?php
/**
 * ========================================
 * CLASSE DE CONEXÃO COM BANCO DE DADOS
 * ========================================
 * 
 * Gerencia a conexão PDO com MySQL de forma
 * segura, com suporte a prepared statements.
 */

require_once __DIR__ . '/config_hostinger.php';
require_once __DIR__ . '/helpers.php';

class Database {
    private static ?PDO $connection = null;
    
    /**
     * Obtém a conexão PDO (singleton pattern)
     */
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    DATABASE_URL,
                    DB_USER,
                    DB_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false,
                    ]
                );
                
                // Log da conexão bem-sucedida
                if (function_exists('logInfo')) {
                    logInfo("Conexão com banco de dados estabelecida com sucesso.");
                }
                
            } catch (PDOException $e) {
                if (function_exists('logError')) {
                    logError("Erro ao conectar com o banco de dados: " . $e->getMessage());
                }
                
                if (DEBUG_MODE) {
                    die("❌ Erro de Banco de Dados: " . $e->getMessage());
                } else {
                    die("❌ Erro ao conectar com o banco de dados. Contate o administrador.");
                }
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Executa uma query com prepared statement
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return PDOStatement
     */
    public static function execute(string $sql, array $params = []): PDOStatement {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logError("Erro ao executar query: " . $e->getMessage() . " - SQL: " . $sql);
            throw new Exception("Erro ao executar operação no banco de dados.");
        }
    }
    
    /**
     * Obtém um registro único
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return array|null
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::execute($sql, $params);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Obtém todos os registros
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insere um registro e retorna o ID
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return int ID do registro inserido
     */
    public static function insert(string $sql, array $params = []): int {
        self::execute($sql, $params);
        return (int) self::getConnection()->lastInsertId();
    }
    
    /**
     * Atualiza registros
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return int Número de linhas afetadas
     */
    public static function update(string $sql, array $params = []): int {
        $stmt = self::execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Deleta registros
     * 
     * @param string $sql SQL com placeholders (?)
     * @param array $params Parâmetros para a query
     * @return int Número de linhas afetadas
     */
    public static function delete(string $sql, array $params = []): int {
        $stmt = self::execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Inicia uma transação
     */
    public static function beginTransaction(): void {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public static function commit(): void {
        self::getConnection()->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public static function rollback(): void {
        self::getConnection()->rollBack();
    }
    
    /**
     * Testa a conexão com o banco de dados
     */
    public static function testConnection(): bool {
        try {
            self::getConnection()->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            logError("Falha no teste de conexão: " . $e->getMessage());
            return false;
        }
    }
}

// Compatibilidade com código legado que espera $pdo
// Criar uma instância global para uso direto
try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    // Se falhar, $pdo permanece indefinido
    $pdo = null;
}