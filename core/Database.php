<?php
// Configurações do banco de dados - VERSÃO CORRIGIDA PARA PRODUÇÃO

// Incluir configurações se ainda não estiverem carregadas
if (!defined('CHAMASERVICO_CONFIG_LOADED')) {
    require_once __DIR__ . '/../config/config.php';
}

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->connection = $this->createConnection();
    }

    private function createConnection()
    {
        // CORREÇÃO: Usar sempre as constantes definidas no config
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;
        $charset = 'utf8mb4';
        $port = 3306;
        
        error_log("DATABASE: Tentando conectar - Host: $host, DB: $dbname, User: $username");

        // Lista de tentativas simplificada para produção
        $attempts = [
            // Tentativa 1: Conexão básica sem SSL
            [
                'dsn' => "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset",
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_TIMEOUT => 15,
                ]
            ],
            // Tentativa 2: Conexão com timeout maior
            [
                'dsn' => "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 30,
                ]
            ],
            // Tentativa 3: Sem especificar porta
            [
                'dsn' => "mysql:host=$host;dbname=$dbname;charset=$charset",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 45,
                ]
            ]
        ];

        $lastError = null;

        foreach ($attempts as $index => $attempt) {
            try {
                error_log("DATABASE: Tentativa " . ($index + 1) . " - DSN: {$attempt['dsn']}");

                $pdo = new PDO($attempt['dsn'], $username, $password, $attempt['options']);

                // Teste rápido de consulta
                $stmt = $pdo->query("SELECT 1");
                if ($stmt === false) {
                    throw new PDOException("Falha no teste de consulta");
                }

                error_log("DATABASE: ✅ Conexão bem-sucedida na tentativa " . ($index + 1));
                return $pdo;
                
            } catch (PDOException $e) {
                $lastError = $e;
                error_log("DATABASE: ❌ Tentativa " . ($index + 1) . " falhou: " . $e->getMessage());
                continue;
            }
        }

        // Se chegou aqui, todas as tentativas falharam
        $this->handleConnectionError($lastError, $host, $dbname, $username);
        return null;
    }

    private function handleConnectionError($exception, $host, $dbname, $username)
    {
        $errorMessage = $exception ? $exception->getMessage() : 'Erro desconhecido';
        error_log("DATABASE: 🚨 ERRO FINAL de conexão: " . $errorMessage);
        
        // CORREÇÃO: Não matar a aplicação imediatamente, deixar o index.php lidar
        if (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento') {
            // Em desenvolvimento, mostrar detalhes
            $debugInfo = "
            <div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px; font-family: Arial, sans-serif;'>
                <h3>❌ Erro de Conexão com Banco de Dados</h3>
                <p><strong>Erro:</strong> $errorMessage</p>
                <p><strong>Host:</strong> $host</p>
                <p><strong>Database:</strong> $dbname</p>
                <p><strong>User:</strong> $username</p>
                <p><strong>Ambiente:</strong> " . AMBIENTE . "</p>
                
                <h4>🔧 Soluções:</h4>
                <ul>
                    <li>Verifique se o servidor MySQL está online</li>
                    <li>Confirme as credenciais no config.php</li>
                    <li>Teste conectividade de rede com o host</li>
                    <li>Verifique permissões do usuário no banco</li>
                </ul>
                
                <p><a href='debug.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🔍 Executar Diagnóstico</a></p>
            </div>";
            die($debugInfo);
        } else {
            // Em produção, gerar exceção para ser capturada pelo index.php
            throw new Exception("Falha na conexão com banco de dados: " . $errorMessage);
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
        if (!$this->connection) {
            throw new Exception("Conexão com banco de dados não estabelecida");
        }
        return $this->connection->prepare($sql);
    }

    public function lastInsertId()
    {
        if (!$this->connection) {
            throw new Exception("Conexão com banco de dados não estabelecida");
        }
        return $this->connection->lastInsertId();
    }

    public function testConnection()
    {
        try {
            if (!$this->connection) {
                return false;
            }
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
?>
