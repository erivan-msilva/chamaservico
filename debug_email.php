<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico do Sistema de E-mail</h1>";

try {
    echo "<h3>📁 Verificando Arquivos:</h3>";
    
    $configFile = __DIR__ . '/config/config.php';
    $emailServiceFile = __DIR__ . '/core/EmailService.php';
    
    echo "<p>Config: " . (file_exists($configFile) ? '✅ Encontrado' : '❌ Não encontrado') . "</p>";
    echo "<p>EmailService: " . (file_exists($emailServiceFile) ? '✅ Encontrado' : '❌ Não encontrado') . "</p>";
    
    if (!file_exists($configFile)) {
        echo "<p>❌ <strong>Erro:</strong> Arquivo config/config.php não encontrado!</p>";
        exit;
    }
    
    require_once $configFile;
    echo "<p>✅ Config carregado com sucesso</p>";
    
    if (!file_exists($emailServiceFile)) {
        echo "<p>❌ <strong>Erro:</strong> Arquivo core/EmailService.php não encontrado!</p>";
        exit;
    }
    
    require_once $emailServiceFile;
    echo "<p>✅ EmailService carregado com sucesso</p>";

} catch (Exception $e) {
    echo "<p>❌ <strong>Erro ao carregar arquivos:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Verificar constantes
echo "<h3>📋 Constantes Definidas:</h3>";
$constants = ['EMAIL_SMTP_HOST', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_USERNAME', 'EMAIL_SMTP_PASSWORD', 'EMAIL_FROM_EMAIL', 'EMAIL_FROM_NAME', 'AMBIENTE', 'BASE_URL'];

foreach ($constants as $const) {
    $value = defined($const) ? constant($const) : 'NÃO DEFINIDA';
    $status = defined($const) ? '✅' : '❌';
    $displayValue = in_array($const, ['EMAIL_SMTP_PASSWORD']) ? '[OCULTA]' : $value;
    echo "<p>$status <strong>$const:</strong> $displayValue</p>";
}

// Testar EmailService
echo "<h3>📧 Teste do EmailService:</h3>";
try {
    $emailService = new EmailService();
    echo "<p>✅ EmailService instanciado com sucesso</p>";
    
    $status = $emailService->getStatusSistema();
    
    echo "<h4>📊 Status do Sistema:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    print_r($status);
    echo "</pre>";
    
    // Teste de envio
    echo "<h3>🚀 Teste de Envio (Simulação):</h3>";
    $result = $emailService->enviarEmailRedefinicao('teste@exemplo.com', 'Usuário Teste', 'token123');
    echo "<p>" . ($result ? '✅ Sucesso na simulação' : '❌ Falha na simulação') . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro no EmailService:</strong> " . $e->getMessage() . "</p>";
    echo "<pre style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

// Verificar logs
echo "<h3>📝 Verificando Logs:</h3>";
$logFile = __DIR__ . '/logs/emails_simulados.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    echo "<h4>📄 Últimas Entradas do Log:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: scroll; border-radius: 5px;'>";
    echo htmlspecialchars(substr($logs, -2000)); // Últimos 2000 caracteres
    echo "</pre>";
} else {
    echo "<p>⚠️ Arquivo de log não encontrado: $logFile</p>";
    echo "<p>🔍 Tentando criar diretório de logs...</p>";
    
    try {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
            echo "<p>✅ Diretório 'logs' criado com sucesso</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro ao criar diretório de logs: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>🔧 Informações do PHP:</h3>";
echo "<p><strong>Versão PHP:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Extensões carregadas:</strong></p>";
echo "<ul>";
echo "<li>OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌') . "</li>";
echo "<li>cURL: " . (extension_loaded('curl') ? '✅' : '❌') . "</li>";
echo "<li>mbstring: " . (extension_loaded('mbstring') ? '✅' : '❌') . "</li>";
echo "</ul>";

echo "<p>✅ <strong>Diagnóstico concluído!</strong></p>";
?>
