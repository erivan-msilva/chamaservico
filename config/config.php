<?php
/**
 * Arquivo de Configuração Principal do Sistema ChamaServiço
 */

// =====================================
// PROTEÇÃO CONTRA MÚLTIPLAS INCLUSÕES
// =====================================
if (defined('CHAMASERVICO_CONFIG_LOADED')) {
    return;
}
define('CHAMASERVICO_CONFIG_LOADED', true);

// HABILITAR ERROS PARA DEBUG (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =====================================
// SISTEMA DE URL DINÂMICA
// =====================================

/**
 * Detectar automaticamente a URL base do projeto
 */
function detectBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = rtrim(str_replace('\\', '/', $scriptPath), '/');
    
    if ($basePath === '/' || $basePath === '') {
        $basePath = '';
    }
    
    return $protocol . $host . $basePath;
}

// Definir BASE_URL automaticamente
if (!defined('BASE_URL')) {
    define('BASE_URL', detectBaseUrl());
}

/**
 * Helper para gerar URLs dinâmicas
 */
function url($path = '') {
    // Remove barras duplas e triplas do início
    $path = ltrim($path, '/');
    
    // Se não há path, retorna apenas BASE_URL
    if (empty($path)) {
        return BASE_URL;
    }
    
    // Constrói a URL final
    $fullUrl = BASE_URL . '/' . $path;
    
    // Remove barras duplas, mas preserva :// nos protocolos
    $fullUrl = preg_replace('#(?<!:)//+#', '/', $fullUrl);
    
    return $fullUrl;
}

// =====================================
// DETECÇÃO DE AMBIENTE
// =====================================

$isLocal = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' || 
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

// =====================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// =====================================

if ($isLocal) {
    // DESENVOLVIMENTO LOCAL
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'bd_servicos');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('AMBIENTE', 'desenvolvimento');
} else {
    // PRODUÇÃO
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'td187899_bd_servicos');
    define('DB_USER', 'td187899_bd_servicos');
    define('DB_PASS', 'pdSNPX6rm2MJE8XM4rTq');
    define('AMBIENTE', 'producao');
}

// =====================================
// OUTRAS CONFIGURAÇÕES
// =====================================

// Configurações de Sessão
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600);
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'chamaservico_session');

// Configurações de Segurança
if (!defined('CSRF_TOKEN_EXPIRY')) define('CSRF_TOKEN_EXPIRY', 3600);
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('LOCKOUT_TIME')) define('LOCKOUT_TIME', 900);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Log da configuração
error_log("Config carregada - BASE_URL: " . BASE_URL . " - Ambiente: " . AMBIENTE);
?>


