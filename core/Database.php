<?php
// Certifique-se de que as configurações foram carregadas
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

class Database
{
    private static $instance = null;
    private $connection;

    // Configurações do banco
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $port;

    public function __construct()
    {
        // Inicializar com os valores das constantes
        $this->host = 'localhost';      // Exemplo: 'mysql.homehost.com.br' ou o host fornecido pela hospedagem
        $this->dbname = 'td187899_chamaservico';    // Exemplo: 'td187899_chamaservico'
        $this->username = 'td187899_chamaservico';  // Exemplo: 'td187899_chamaservico'
        $this->password = 'XHRmnbDHgMVP4sk45N5Z';  // Exemplo: 'XHRmnbDHgMVP4sk45N5Z'
        $this->charset = 'utf8mb4';
        $this->port = 3306;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_PERSISTENT => false
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Erro na conexão com o banco: " . $e->getMessage());
            die("Erro na conexão com o banco de dados. Verifique as configurações.");
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

