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

/**
 * Função para incluir arquivos de forma segura (para compatibilidade com EmailService)
 * @param string $path Caminho relativo do arquivo
 */
function includeFile($path) {
    $baseDir = dirname(__DIR__); // Diretório raiz do projeto
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    
    if (file_exists($fullPath)) {
        require_once $fullPath;
        return true;
    }
    throw new Exception("Arquivo não encontrado: {$fullPath}");
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

// === CONFIGURAÇÕES DE EMAIL ===
if (!defined('EMAIL_SMTP_HOST')) {
    define('EMAIL_SMTP_HOST', 'h63.servidorhh.com');
    define('EMAIL_SMTP_PORT', 587);
    define('EMAIL_SMTP_USERNAME', 'chamaservico@tds104-senac.online');
    define('EMAIL_SMTP_PASSWORD', 'Chama@Servico123');
    define('EMAIL_FROM_EMAIL', 'chamaservico@tds104-senac.online');
    define('EMAIL_FROM_NAME', 'ChamaServiço - Sistema');
}

// === CONFIGURAÇÕES DE AMBIENTE ===
if (!defined('AMBIENTE')) {
    define('AMBIENTE', 'desenvolvimento'); // Mudar para 'producao' quando for para produção
}

// === CONFIGURAÇÕES DE BASE URL ===
if (!defined('BASE_URL')) {
    if (AMBIENTE === 'producao') {
        define('BASE_URL', 'https://chamaservico.tds104-senac.online');
    } else {
        define('BASE_URL', 'http://localhost/chamaservico');
    }
}

// === CONFIGURAÇÕES DE SEGURANÇA ===
if (!defined('SECURE_COOKIES')) {
    define('SECURE_COOKIES', AMBIENTE === 'producao');
}

if (!defined('FORCE_HTTPS')) {
    define('FORCE_HTTPS', AMBIENTE === 'producao');
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Log da configuração
error_log("Config carregada - BASE_URL: " . BASE_URL . " - Ambiente: " . AMBIENTE);

// Carregar funções auxiliares
require_once __DIR__ . '/functions.php';
?>


