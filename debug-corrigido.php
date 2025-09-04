<?php
// ARQUIVO DE DIAGNÓSTICO CORRIGIDO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 DIAGNÓSTICO CORRIGIDO</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Teste básico do PHP
echo "<h2>✅ 1. PHP está funcionando</h2>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// 2. Teste de configuração
echo "<h2>⚙️ 2. Testando configuração corrigida</h2>";
try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "<p style='color: green;'>✅ Arquivo config.php carregado com sucesso</p>";
        
        echo "<p>📋 <strong>BASE_URL:</strong> " . BASE_URL . "</p>";
        echo "<p>📋 <strong>AMBIENTE:</strong> " . AMBIENTE . "</p>";
        echo "<p>📋 <strong>DB_HOST:</strong> " . DB_HOST . "</p>";
        echo "<p>📋 <strong>DB_NAME:</strong> " . DB_NAME . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ config.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao carregar config: " . $e->getMessage() . "</p>";
}

// 3. Teste de conexão com banco CORRIGIDO
echo "<h2>🗄️ 3. Testando conexão com banco (CORRIGIDO)</h2>";
try {
    if (defined('DB_HOST')) {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        
        if ($db && $db->testConnection()) {
            echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
            
            // Testar uma consulta simples
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_pessoa");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "<p style='color: green;'>✅ Consulta teste: {$result['total']} usuários cadastrados</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Falha na conexão com banco</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Constantes de banco não definidas</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro de banco: " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}

// 4. Verificar constantes PDO
echo "<h2>🔧 4. Verificando constantes PDO</h2>";
$constantes_ssl = [
    'PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT',
    'PDO::MYSQL_ATTR_SSL_CA',
    'PDO::MYSQL_ATTR_SSL_CERT',
    'PDO::MYSQL_ATTR_SSL_KEY'
];

foreach ($constantes_ssl as $const) {
    if (defined($const)) {
        echo "<p style='color: green;'>✅ $const disponível</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ $const não disponível (servidor não suporta)</p>";
    }
}

// 5. Teste do sistema de rotas
echo "<h2>🛣️ 5. Sistema de Rotas</h2>";
echo "<p><strong>URL atual:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>BASE_URL configurada:</strong> " . BASE_URL . "</p>";
echo "<p><strong>Script atual:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// 6. Links de teste
echo "<h2>🔗 6. Links de Teste</h2>";
echo "<p><a href='" . BASE_URL . "' class='btn btn-primary' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🏠 Página Inicial</a></p>";
echo "<p><a href='" . BASE_URL . "/login' class='btn btn-success' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-left: 10px;'>🔑 Página de Login</a></p>";

echo "<hr>";
echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>⚠️ CORREÇÕES APLICADAS:</strong>";
echo "<ul>";
echo "<li>✅ BASE_URL corrigida para raiz do domínio</li>";
echo "<li>✅ Conexão MySQL sem constantes SSL problemáticas</li>";
echo "<li>✅ Router configurado para raiz</li>";
echo "<li>✅ .htaccess corrigido para RewriteBase /</li>";
echo "</ul>";
echo "<p><strong>Próximo passo:</strong> Delete este arquivo e teste o sistema!</p>";
echo "</div>";
?>
