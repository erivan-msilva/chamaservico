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

    // Configurações de upload
    define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
    define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

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

// Definindo constantes de banco de dados para manter compatibilidade
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'td187899_chamaservico');
    define('DB_USER', 'td187899_chamaservico');
    define('DB_PASS', 'XHRmnbDHgMVP4sk45N5Z');
    define('DB_CHARSET', 'utf8mb4');
    define('DB_PORT', 3306);
}
?>
