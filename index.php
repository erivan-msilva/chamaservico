<?php
// Configurações do sistema
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// Incluir configurações primeiro
require_once 'config/config.php';

// Incluir e iniciar sessão
require_once 'config/session.php';
Session::start();

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
        $db = new Database();
        if (!$db->testConnection()) {
            die('<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px;">
                <h3>❌ Erro de Conexão</h3>
                <p>Não foi possível conectar ao banco de dados hospedado.</p>
                <p><strong>Servidor:</strong> h63.servidorhh.com</p>
                <p><strong>Banco:</strong> td187899_chamaservico</p>
                <hr>
                <p><strong>Verificações necessárias:</strong></p>
                <ul>
                    <li>Servidor permite conexões externas?</li>
                    <li>Credenciais estão corretas?</li>
                    <li>Firewall está bloqueando a porta 3306?</li>
                </ul>
                <p><a href="/chamaservico/config/test-connection.php" style="background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">🔍 Testar Conexão</a></p>
                </div>');
        }
    } catch (Exception $e) {
        die('<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px;">
            <h3>❌ Erro de Configuração</h3>
            <p><strong>Erro:</strong> ' . $e->getMessage() . '</p>
            <p>Verifique as configurações de conexão com o banco hospedado.</p>
            </div>');
    }
}

// MELHORIA: Verificação de extensões necessárias
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Extensões PHP necessárias não encontradas: ' . implode(', ', $missing_extensions));
}

// Incluir roteador
require_once 'router.php';
?>


