<?php
/**
 * Arquivo de Configuração Principal do Sistema ChamaServiço
 * 
 * Este arquivo deve ser incluído apenas uma vez por execução.
 * Proteção contra múltiplas inclusões implementada.
 */

// =====================================
// PROTEÇÃO CONTRA MÚLTIPLAS INCLUSÕES
// =====================================

// Se as configurações já foram carregadas, não recarregar
if (defined('CHAMASERVICO_CONFIG_LOADED')) {
    return;
}

// Marcar que as configurações foram carregadas
define('CHAMASERVICO_CONFIG_LOADED', true);

// =====================================
// CONFIGURAÇÕES DE AMBIENTE
// =====================================

// Ambiente (production, development, testing)
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'development');
}

// Debug mode - só definir se não existir
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', ENVIRONMENT === 'development');
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// =====================================
// CONFIGURAÇÕES DE ERRO BASEADAS NO AMBIENTE
// =====================================

if (ENVIRONMENT === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Garantir que o diretório de logs existe
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// =====================================
// CONFIGURAÇÕES DE SESSÃO PARA ALTA CONCORRÊNCIA
// =====================================

if (!defined('SESSION_CONFIG_SET')) {
    // Configurações de sessão para múltiplas sessões simultâneas
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 1000);
    ini_set('session.gc_maxlifetime', 7200);

    // Configurações de upload otimizadas
    ini_set('upload_max_filesize', '10M');
    ini_set('post_max_size', '50M');
    ini_set('max_file_uploads', 20);
    ini_set('max_execution_time', 300); // 5 minutos para uploads

    // Configurações de memória
    ini_set('memory_limit', '256M');
    
    define('SESSION_CONFIG_SET', true);
}

// =====================================
// CONFIGURAÇÕES DE BANCO DE DADOS
// =====================================

if (!defined('DB_CONFIG')) {
    define('DB_CONFIG', [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'dbname' => $_ENV['DB_NAME'] ?? 'td187899_bd_servicos',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true, // Conexões persistentes para melhor performance
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_TIMEOUT => 30, // Timeout de conexão
        ]
    ]);
}

// =====================================
// CONFIGURAÇÕES DE CONEXÃO
// =====================================

// Pool de conexões - só definir se não existir
if (!defined('MAX_CONNECTIONS')) {
    define('MAX_CONNECTIONS', 50);
}

if (!defined('CONNECTION_TIMEOUT')) {
    define('CONNECTION_TIMEOUT', 30);
}

// =====================================
// CONFIGURAÇÕES DE CACHE
// =====================================

if (!defined('CACHE_ENABLED')) {
    define('CACHE_ENABLED', ENVIRONMENT === 'production');
}

if (!defined('CACHE_TTL')) {
    define('CACHE_TTL', 3600); // 1 hora
}

// =====================================
// CONFIGURAÇÕES DE SEGURANÇA
// =====================================

if (!defined('SECURITY_CONFIG')) {
    define('SECURITY_CONFIG', [
        'csrf_token_lifetime' => 3600, // 1 hora
        'session_timeout' => 7200, // 2 horas
        'admin_session_timeout' => 3600, // 1 hora
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
        'bcrypt_cost' => 12
    ]);
}

// =====================================
// CONFIGURAÇÕES DE EMAIL
// =====================================

if (!defined('EMAIL_CONFIG')) {
    define('EMAIL_CONFIG', [
        'smtp_host' => $_ENV['SMTP_HOST'] ?? 'localhost',
        'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
        'smtp_user' => $_ENV['SMTP_USER'] ?? '',
        'smtp_pass' => $_ENV['SMTP_PASS'] ?? '',
        'smtp_secure' => $_ENV['SMTP_SECURE'] ?? 'tls',
        'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@chamaservico.com',
        'from_name' => $_ENV['FROM_NAME'] ?? 'ChamaServiço'
    ]);
}

// Configurações de Email
define('EMAIL_SMTP_HOST', 'smtp.gmail.com');
define('EMAIL_SMTP_PORT', 587);
define('EMAIL_SMTP_USERNAME', 'seu_email@gmail.com'); // ⚠️ CONFIGURE AQUI
define('EMAIL_SMTP_PASSWORD', 'sua_senha_de_app');    // ⚠️ CONFIGURE AQUI
define('EMAIL_FROM_NAME', 'ChamaServiço');
define('EMAIL_FROM_EMAIL', 'noreply@chamaservico.com');

// Ambiente (desenvolvimento/producao)
define('AMBIENTE', 'desenvolvimento');

// Configurações de Segurança
define('MAX_TENTATIVAS_REDEFINICAO', 3);
define('TEMPO_BLOQUEIO_TENTATIVAS', 15); // minutos
define('TEMPO_EXPIRACAO_TOKEN', 60); // minutos

// =====================================
// CONFIGURAÇÕES DE ARQUIVOS
// =====================================

if (!defined('UPLOAD_CONFIG')) {
    define('UPLOAD_CONFIG', [
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'upload_path' => __DIR__ . '/../uploads/',
        'temp_path' => sys_get_temp_dir() . '/chamaservico_temp/'
    ]);
}

// =====================================
// URLs BASE
// =====================================

if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/chamaservico');
}

if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '/assets');
}

if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . '/uploads');
}

// =====================================
// CONFIGURAÇÕES DE LOG
// =====================================

if (!defined('LOG_CONFIG')) {
    define('LOG_CONFIG', [
        'enabled' => true,
        'level' => ENVIRONMENT === 'production' ? 'error' : 'debug',
        'path' => __DIR__ . '/../logs/',
        'max_size' => 10 * 1024 * 1024, // 10MB
        'max_files' => 10
    ]);
}

// =====================================
// CRIAÇÃO DE DIRETÓRIOS NECESSÁRIOS
// =====================================

if (!defined('DIRECTORIES_CREATED')) {
    $directories = [
        __DIR__ . '/../logs',
        __DIR__ . '/../uploads/perfil',
        __DIR__ . '/../uploads/solicitacoes',
        UPLOAD_CONFIG['temp_path']
    ];

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    
    define('DIRECTORIES_CREATED', true);
}

// =====================================
// CONFIGURAÇÕES ESPECÍFICAS PARA PRODUÇÃO
// =====================================

if (ENVIRONMENT === 'production' && !defined('PRODUCTION_CONFIG_SET')) {
    // Configurações de segurança adicionais
    ini_set('expose_php', 0);
    
    // Headers de segurança
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // CSP básico
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' cdn.jsdelivr.net;");
    }
    
    define('PRODUCTION_CONFIG_SET', true);
}

// =====================================
// FUNÇÕES UTILITÁRIAS
// =====================================

if (!function_exists('secureLog')) {
    /**
     * Função para log seguro
     * @param string $message Mensagem a ser logada
     * @param string $level Nível do log (info, warning, error)
     */
    function secureLog($message, $level = 'info') {
        if (!LOG_CONFIG['enabled']) return;
        
        $logFile = LOG_CONFIG['path'] . 'system_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = "[$timestamp] [$level] [IP: $ip] $message" . PHP_EOL;
        
        // Rotacionar logs se necessário
        if (file_exists($logFile) && filesize($logFile) > LOG_CONFIG['max_size']) {
            $backupFile = LOG_CONFIG['path'] . 'system_' . date('Y-m-d') . '_' . time() . '.log';
            rename($logFile, $backupFile);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('checkRateLimit')) {
    /**
     * Rate limiting simples baseado em IP
     * @param string $action Ação sendo realizada
     * @param int $maxAttempts Máximo de tentativas
     * @param int $timeWindow Janela de tempo em segundos
     * @return bool True se permitido, false se bloqueado
     */
    function checkRateLimit($action = 'general', $maxAttempts = 60, $timeWindow = 3600) {
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = $ip . '_' . $action;
        $now = time();
        
        // Limpar entradas antigas
        $_SESSION['rate_limits'] = array_filter($_SESSION['rate_limits'], function($data) use ($now, $timeWindow) {
            return ($now - $data['first_attempt']) < $timeWindow;
        });
        
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
            return true;
        }
        
        $rateData = $_SESSION['rate_limits'][$key];
        
        // Se passou do tempo, resetar
        if (($now - $rateData['first_attempt']) >= $timeWindow) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
            return true;
        }
        
        // Incrementar contador
        $_SESSION['rate_limits'][$key]['count']++;
        $_SESSION['rate_limits'][$key]['last_attempt'] = $now;
        
        return $_SESSION['rate_limits'][$key]['count'] <= $maxAttempts;
    }
}

// =====================================
// AUTOLOADER PERSONALIZADO
// =====================================

if (!defined('AUTOLOADER_REGISTERED')) {
    spl_autoload_register(function ($className) {
        $paths = [
            __DIR__ . '/../models/',
            __DIR__ . '/../controllers/',
            __DIR__ . '/../core/',
            __DIR__ . '/../config/'
        ];
        
        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    });
    
    define('AUTOLOADER_REGISTERED', true);
}

// =====================================
// LOG DE INICIALIZAÇÃO
// =====================================

if (LOG_CONFIG['enabled'] && !defined('INIT_LOGGED')) {
    secureLog("Sistema inicializado - Ambiente: " . ENVIRONMENT . ", IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    define('INIT_LOGGED', true);
}

// =====================================
// VERIFICAÇÕES DE INTEGRIDADE
// =====================================

if (DEBUG_MODE && !defined('INTEGRITY_CHECKED')) {
    // Verificar se extensões necessárias estão instaladas
    $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    if (!empty($missingExtensions)) {
        die('Extensões PHP obrigatórias não encontradas: ' . implode(', ', $missingExtensions));
    }
    
    define('INTEGRITY_CHECKED', true);
}

// =====================================
// CONFIGURAÇÕES FINAIS
// =====================================

// Definir constante indicando que a configuração foi completamente carregada
if (!defined('CHAMASERVICO_CONFIG_COMPLETE')) {
    define('CHAMASERVICO_CONFIG_COMPLETE', true);
}

?>
