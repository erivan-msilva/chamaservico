<?php
echo "<h2>🛠️ Configuração do Banco Local - ChamaServiço</h2>";

// Configurações do banco local (XAMPP)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'td187899_chamaservico'; // Mesmo nome do banco de produção

try {
    // Conectar sem especificar banco para criar
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conectado ao MySQL local</p>";
    
    // Criar banco se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p>✅ Banco '$dbname' criado/verificado</p>";
    
    // Conectar ao banco
    $pdo->exec("USE `$dbname`");
    
    // Ler e executar SQL
    $sqlFile = __DIR__ . '/bd_servicos.txt';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Limpar e dividir comandos SQL
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove comentários
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove comentários de bloco
        
        $commands = array_filter(
            array_map('trim', explode(';', $sql)),
            function($cmd) {
                return !empty($cmd) && strlen($cmd) > 10;
            }
        );
        
        $sucessos = 0;
        $erros = 0;
        
        foreach ($commands as $command) {
            if (!empty(trim($command))) {
                try {
                    $pdo->exec($command);
                    $sucessos++;
                } catch (PDOException $e) {
                    if (!strpos($e->getMessage(), 'already exists')) {
                        echo "<p style='color: orange;'>⚠️ " . htmlspecialchars($e->getMessage()) . "</p>";
                        $erros++;
                    }
                }
            }
        }
        
        echo "<p>✅ Estrutura do banco importada ($sucessos comandos executados, $erros erros)</p>";
        
        // Verificar tabelas criadas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>📊 Tabelas criadas: " . count($tables) . "</p>";
        
        // Verificar dados principais
        if (in_array('tb_pessoa', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_pessoa");
            $result = $stmt->fetch();
            echo "<p>👥 Usuários cadastrados: " . $result['total'] . "</p>";
        }
        
        if (in_array('tb_tipo_servico', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_tipo_servico");
            $result = $stmt->fetch();
            echo "<p>🛠️ Tipos de serviço: " . $result['total'] . "</p>";
        }
        
        if (in_array('tb_solicita_servico', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_solicita_servico");
            $result = $stmt->fetch();
            echo "<p>📋 Solicitações: " . $result['total'] . "</p>";
        }
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>🎉 Configuração Concluída!</h3>";
        echo "<p>Banco local configurado com dados de produção:</p>";
        echo "<ul>";
        echo "<li><strong>Host:</strong> localhost</li>";
        echo "<li><strong>Banco:</strong> td187899_chamaservico</li>";
        echo "<li><strong>Usuário:</strong> root</li>";
        echo "<li><strong>Senha:</strong> (vazia)</li>";
        echo "</ul>";
        echo "<p><a href='/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Acessar Sistema</a></p>";
        echo "<p><a href='/config/test-connection.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🔍 Testar Conexão</a></p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Arquivo SQL não encontrado: $sqlFile</p>";
        echo "<p>Certifique-se de que o arquivo bd_servicos.txt está na pasta config/</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Possíveis soluções:</h4>";
    echo "<ul>";
    echo "<li>Verifique se o MySQL está rodando no XAMPP</li>";
    echo "<li>Confirme se as credenciais estão corretas</li>";
    echo "<li>Teste a conexão no phpMyAdmin</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        margin: 20px; 
        background: #f8f9fa;
    }
    h2 { 
        color: #283579; 
        border-bottom: 3px solid #f5a522; 
        padding-bottom: 10px;
    }
    table { 
        width: 100%; 
        border-collapse: collapse;
        margin: 10px 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }
    th, td { 
        padding: 12px; 
        text-align: left; 
        border-bottom: 1px solid #ddd;
    }
    th { 
        background: #283579; 
        color: white;
        font-weight: 600;
    }
    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
</style>
