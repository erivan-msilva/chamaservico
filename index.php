<?php
// Configurações do sistema
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// CARREGAR AUTOLOADER PRIMEIRO
require_once 'core/Autoloader.php';
Autoloader::register();
Autoloader::loadDependencies();

// Iniciar sessão (agora carregada via autoloader)
Session::start();

// Verificar timeout da sessão se o usuário estiver logado
if (Session::isLoggedIn()) {
    if (!Session::checkTimeout()) {
        header('Location: /chamaservico/login');
        exit;
    }
}

// Testar conexão com banco
try {
    $db = Database::getInstance();
    if (!$db->testConnection()) {
        die('Erro: Não foi possível conectar ao banco de dados. Verifique as configurações.');
    }
} catch (Exception $e) {
    die('Erro de configuração do banco de dados: ' . $e->getMessage());
}

// Incluir roteador (agora sem require_once manuais)
require_once 'router.php';
?>
require_once 'router.php';
?>
