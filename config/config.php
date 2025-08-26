<?php
// Verificar se as constantes já foram definidas para evitar redefinição
if (!defined('APP_NAME')) {
    // Configurações gerais do sistema
    define('APP_NAME', 'ChamaServiço');
    define('APP_VERSION', '1.0.0');
    define('APP_ENV', 'development'); // development, production

    // URLs do sistema
    define('BASE_URL', 'https://chamaservico.tds104-senac.online/');
    define('ASSETS_URL', BASE_URL . '/assets');
    define('UPLOADS_URL', BASE_URL . '/uploads');

    // Configurações de upload aprimoradas
    define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
    define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
    define('UPLOAD_DIR_PERFIL', __DIR__ . '/../uploads/perfil/');
    define('UPLOAD_DIR_SOLICITACOES', __DIR__ . '/../uploads/solicitacoes/');
    define('UPLOAD_URL_PERFIL', BASE_URL . '/uploads/perfil/');
    define('UPLOAD_URL_SOLICITACOES', BASE_URL . '/uploads/solicitacoes/');

    // Criar diretórios se não existirem
    if (!file_exists(UPLOAD_DIR_PERFIL)) {
        mkdir(UPLOAD_DIR_PERFIL, 0755, true);
    }
    if (!file_exists(UPLOAD_DIR_SOLICITACOES)) {
        mkdir(UPLOAD_DIR_SOLICITACOES, 0755, true);
    }

    // Configurações de sessão
    define('SESSION_TIMEOUT', 1800); // 30 minutos
    define('SESSION_NAME', 'chamaservico_session');

    // Configurações de debug
    define('DEBUG_MODE', APP_ENV === 'development');
    define('LOG_ERRORS', true);

    // Configurações de email (para futuro uso)
    define('MAIL_HOST', 'smtp.gmail.com');
    define('MAIL_PORT', 587);
    define('MAIL_USERNAME', '');
    define('MAIL_PASSWORD', '');
    define('MAIL_FROM_EMAIL', 'noreply@chamaservico.com');
    define('MAIL_FROM_NAME', 'ChamaServiço');

    // Configurações de segurança
    define('CSRF_TOKEN_EXPIRE', 3600); // 1 hora
    define('PASSWORD_MIN_LENGTH', 6);

    // Timezone
    date_default_timezone_set('America/Sao_Paulo');

    // Configurar relatório de erros baseado no ambiente
    if (DEBUG_MODE) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
    }
}

// CONFIGURAÇÃO INTELIGENTE: Detectar ambiente automaticamente
if (!defined('DB_HOST')) {
    // Verificar se estamos em localhost (desenvolvimento)
    $isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8083']);
    
    if ($isLocalhost) {
        // CORREÇÃO: Usar banco real do dump
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'bd_servicos'); // <-- BANCO REAL DO SEU DUMP
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_CHARSET', 'utf8mb4');
        define('DB_PORT', 3306);
        
        error_log("Usando configuração LOCAL do banco de dados: bd_servicos");
    } else {
        // Configurações para servidor hospedado
        define('DB_HOST', 'h63.servidorhh.com');
        define('DB_NAME', 'td187899_chamaservico');
        define('DB_USER', 'td187899_chamaservico');
        define('DB_PASS', 'XHRmnbDHgMVP4sk45N5Z');
        define('DB_CHARSET', 'utf8mb4');
        define('DB_PORT', 3306);
        
        error_log("Usando configuração HOSPEDADA do banco de dados");
    }
}
?>
