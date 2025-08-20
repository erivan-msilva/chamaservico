<?php
// Configurações do sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configurações primeiro
require_once 'config/config.php';

// Incluir e iniciar sessão
require_once 'config/session.php';
Session::start();
//error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING);

// Verificar timeout da sessão se o usuário estiver logado
if (Session::isLoggedIn()) {
    if (!Session::checkTimeout()) {
        header('Location: /chamaservico/login');
        exit;
    }
}

// Verificar conexão com banco de dados em modo debug
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance(); // Usar getInstance em vez de new
        if (!$db->testConnection()) {
            die('Erro: Não foi possível conectar ao banco de dados. Verifique as configurações.');
        }
    } catch (Exception $e) {
        die('Erro de configuração do banco de dados: ' . $e->getMessage());
    }
}

// Incluir roteador
require_once 'router.php';
?>


