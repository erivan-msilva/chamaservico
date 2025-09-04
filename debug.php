<?php
// ARQUIVO DE DEPURAÇÃO TEMPORÁRIO - DELETAR APÓS O DEBUG
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 DIAGNÓSTICO DO SISTEMA</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Teste básico do PHP
echo "<h2>✅ 1. PHP está funcionando</h2>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// 2. Verificar se o arquivo de configuração existe
echo "<h2>📁 2. Verificando arquivos principais</h2>";
$arquivos = [
    'config/config.php' => 'Configurações',
    'core/Database.php' => 'Classe Database',
    'controllers/AuthController.php' => 'Controller de Autenticação',
    'controllers/HomeController.php' => 'Controller Home',
    'router.php' => 'Roteador',
    'index.php' => 'Arquivo principal'
];

foreach ($arquivos as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "<p style='color: green;'>✅ $arquivo ($descricao) - EXISTE</p>";
    } else {
        echo "<p style='color: red;'>❌ $arquivo ($descricao) - NÃO ENCONTRADO</p>";
    }
}

// 3. Teste de inclusão do config
echo "<h2>⚙️ 3. Testando configuração</h2>";
try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "<p style='color: green;'>✅ Arquivo config.php carregado com sucesso</p>";
        
        // Verificar constantes
        $constantes = ['BASE_URL', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'AMBIENTE'];
        foreach ($constantes as $const) {
            if (defined($const)) {
                $valor = constant($const);
                if ($const === 'DB_PASS') {
                    $valor = str_repeat('*', strlen($valor)); // Ocultar senha
                }
                echo "<p>📋 $const = '$valor'</p>";
            } else {
                echo "<p style='color: red;'>❌ $const não definida</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ config.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao carregar config: " . $e->getMessage() . "</p>";
}

// 4. Teste de conexão com banco
echo "<h2>🗄️ 4. Testando conexão com banco</h2>";
try {
    if (defined('DB_HOST')) {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        
        if ($db->testConnection()) {
            echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
        } else {
            echo "<p style='color: red;'>❌ Falha na conexão com banco</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Constantes de banco não definidas</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro de banco: " . $e->getMessage() . "</p>";
    echo "<p><strong>Detalhes:</strong> " . $e->getFile() . " linha " . $e->getLine() . "</p>";
}

// 5. Teste do autoloader
echo "<h2>🔄 5. Testando autoloader</h2>";
try {
    if (file_exists('core/Autoloader.php')) {
        require_once 'core/Autoloader.php';
        Autoloader::register();
        echo "<p style='color: green;'>✅ Autoloader registrado</p>";
    } else {
        echo "<p style='color: red;'>❌ Autoloader não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no autoloader: " . $e->getMessage() . "</p>";
}

// 6. Informações do servidor
echo "<h2>🖥️ 6. Informações do servidor</h2>";
echo "<p><strong>Sistema:</strong> " . php_uname() . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script atual:</strong> " . __FILE__ . "</p>";
echo "<p><strong>URL atual:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

// 7. Extensões PHP necessárias
echo "<h2>🔧 7. Extensões PHP</h2>";
$extensoes = ['pdo', 'pdo_mysql', 'openssl', 'curl', 'gd', 'zip'];
foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ $ext</p>";
    } else {
        echo "<p style='color: red;'>❌ $ext (NÃO INSTALADA)</p>";
    }
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Delete este arquivo após o diagnóstico!</p>";
?>
