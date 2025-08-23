<?php
// Certifique-se de que as configurações foram carregadas
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

class Database
{
    private static $instance = null;
    private $connection;

    // CORREÇÃO: Configurações do banco conforme fornecidas
    private $host = 'localhost';
    private $db_name = 'bd_servicos'; 
    private $username = '';
    private $password = '';
    private $charset = 'utf8mb4';
    private $port = 3306;

    public function __construct()
    {
        // MELHORIA: Detectar ambiente e usar configurações apropriadas
        $this->detectEnvironment();
        
        // NOVA ABORDAGEM: Tentar diferentes métodos de conexão
        $this->connection = $this->tryConnection();
    }

    private function detectEnvironment()
    {
        // Verificar se estamos em localhost (desenvolvimento)
        $isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8083']);
        
        if ($isLocalhost) {
            // Configurações para desenvolvimento local (XAMPP)
            $this->host = 'localhost';
            $this->db_name = 'td187899_chamaservico'; // Usar mesmo nome da produção
            $this->username = 'root';
            $this->password = '';
            error_log("Database: Usando configuração LOCAL");
        } else {
            // Configurações para servidor hospedado
            $this->host = 'h63.servidorhh.com';
            $this->db_name = 'td187899_chamaservico';
            $this->username = 'td187899_chamaservico';
            $this->password = 'XHRmnbDHgMVP4sk45N5Z';
            error_log("Database: Usando configuração HOSPEDADA");
        }

        // Sobrescrever com constantes se definidas
        if (defined('DB_HOST')) {
            $this->host = DB_HOST;
            $this->db_name = DB_NAME;
            $this->username = DB_USER;
            $this->password = DB_PASS;
            $this->charset = DB_CHARSET ?? 'utf8mb4';
            $this->port = DB_PORT ?? 3306;
        }
    }

    private function tryConnection()
    {
        $attempts = [
            // Tentativa 1: Conexão padrão
            [
                'dsn' => "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            ],
            // Tentativa 2: Sem SSL (para servidor hospedado)
            [
                'dsn' => "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_TIMEOUT => 15,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                    PDO::MYSQL_ATTR_SSL_CA => null,
                ]
            ],
            // Tentativa 3: Conexão simples sem banco específico
            [
                'dsn' => "mysql:host={$this->host};port={$this->port};charset={$this->charset}",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 20,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                ]
            ]
        ];

        $lastError = null;
        
        foreach ($attempts as $index => $attempt) {
            try {
                error_log("Database: Tentativa " . ($index + 1) . " - {$this->host}/{$this->db_name}");
                
                $pdo = new PDO($attempt['dsn'], $this->username, $this->password, $attempt['options']);
                
                // Se tentativa 3 funcionou, selecionar o banco
                if ($index === 2) {
                    $pdo->exec("USE `{$this->db_name}`");
                }
                
                error_log("Database: Conexão bem-sucedida na tentativa " . ($index + 1));
                return $pdo;
                
            } catch (PDOException $e) {
                $lastError = $e;
                error_log("Database: Tentativa " . ($index + 1) . " falhou: " . $e->getMessage());
                continue;
            }
        }

        // Se chegou aqui, todas as tentativas falharam
        $this->handleConnectionError($lastError);
        return null;
    }

    private function handleConnectionError($exception)
    {
        $errorMessage = $exception->getMessage();
        error_log("Database: ERRO FINAL de conexão: " . $errorMessage);
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $debugInfo = "
            <div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px; font-family: Arial, sans-serif;'>
                <h3>❌ Erro de Conexão com Banco de Dados</h3>
                <p><strong>Erro:</strong> {$errorMessage}</p>
                <p><strong>Host:</strong> {$this->host}:{$this->port}</p>
                <p><strong>Database:</strong> {$this->db_name}</p>
                <p><strong>User:</strong> {$this->username}</p>
                <p><strong>Ambiente:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'desconhecido') . "</p>
                
                <h4>🔧 Diagnóstico:</h4>
                <ul>
                    <li>Verifique se o MySQL está rodando</li>
                    <li>Confirme as credenciais do banco</li>
                    <li>Teste a conectividade de rede</li>
                    <li>Verifique permissões do usuário</li>
                </ul>
                
                <p><a href='/chamaservico/config/test-connection.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🔍 Executar Diagnóstico Completo</a></p>
                <p><a href='/chamaservico/config/setup-local.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-left: 10px;'>🛠️ Configurar Banco Local</a></p>
            </div>";
            die($debugInfo);
        } else {
            die("Erro na conexão com o banco de dados. Entre em contato com o suporte.");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    // Método para verificar se há transação ativa
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }

    // Método para rollback seguro
    public function safeRollback()
    {
        if ($this->connection->inTransaction()) {
            return $this->connection->rollBack();
        }
        return true;
    }

    // Método para commit seguro
    public function safeCommit()
    {
        if ($this->connection->inTransaction()) {
            return $this->connection->commit();
        }
        return true;
    }

    // Método para testar conexão
    public function testConnection()
    {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            error_log("Erro no teste de conexão: " . $e->getMessage());
            return false;
        }
    }

    // Prevenir clonagem
    private function __clone() {}

    // Prevenir desserialização
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
