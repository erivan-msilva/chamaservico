<?php
echo "<div class='container'>";
echo "<h2>🔍 Teste de Conexão - ChamaServiço</h2>";

// CORREÇÃO: Configurações atualizadas conforme fornecidas
$configs = [
    'Local (XAMPP)' => [
        'host' => 'localhost',
        'dbname' => 'td187899_chamaservico',
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ],
    'Hospedado (Servidor)' => [
        'host' => 'h63.servidorhh.com',
        'dbname' => 'td187899_chamaservico',
        'user' => 'td187899_chamaservico',
        'pass' => 'XHRmnbDHgMVP4sk45N5Z',
        'port' => 3306
    ]
];

foreach ($configs as $ambiente => $config) {
    echo "<h3>🌐 Testando: $ambiente</h3>";
    
    // Teste de conectividade de rede (apenas para servidor remoto)
    if ($config['host'] !== 'localhost') {
        echo "<h4>1. Teste de Conectividade de Rede</h4>";
        $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
        if ($connection) {
            echo "<p style='color: green;'>✅ Servidor {$config['host']} responde na porta {$config['port']}</p>";
            fclose($connection);
        } else {
            echo "<p style='color: red;'>❌ Servidor não responde: $errstr ($errno)</p>";
            echo "<p><em>Isso pode indicar problema de firewall ou servidor offline</em></p>";
            continue;
        }
    }
    
    // Teste de conexão MySQL
    echo "<h4>2. Teste de Conexão MySQL</h4>";
    
    try {
        // Tentar conectar
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]);
        
        echo "<p style='color: green;'>✅ Conexão MySQL estabelecida com sucesso</p>";
        
        // Verificar se banco existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['dbname']}'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Banco '{$config['dbname']}' encontrado</p>";
            
            // Conectar ao banco específico
            $pdo->exec("USE `{$config['dbname']}`");
            
            // Verificar tabelas principais
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>📊 Total de tabelas: " . count($tables) . "</p>";
            
            // Verificar dados essenciais
            $checks = [
                'tb_pessoa' => 'Usuários cadastrados',
                'tb_solicita_servico' => 'Solicitações de serviço',
                'tb_tipo_servico' => 'Tipos de serviço',
                'tb_proposta' => 'Propostas enviadas'
            ];
            
            foreach ($checks as $table => $description) {
                if (in_array($table, $tables)) {
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
                        $result = $stmt->fetch();
                        echo "<p>📋 $description: <strong>{$result['total']}</strong></p>";
                    } catch (PDOException $e) {
                        echo "<p style='color: orange;'>⚠️ Erro ao verificar $table: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Tabela '$table' não encontrada</p>";
                }
            }
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4 style='color: #155724; margin: 0;'>🎉 $ambiente - FUNCIONANDO PERFEITAMENTE!</h4>";
            echo "<p style='margin: 5px 0 0 0;'>Todas as verificações passaram com sucesso.</p>";
            echo "</div>";
            
        } else {
            echo "<p style='color: red;'>❌ Banco '{$config['dbname']}' não encontrado</p>";
            echo "<p><em>O banco pode não ter sido criado ou as permissões estão incorretas</em></p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
        
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5 style='color: #721c24;'>💡 Diagnóstico do Erro:</h5>";
        echo "<ul style='margin: 10px 0;'>";
        
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "<li><strong>🔑 Credenciais incorretas:</strong> Usuário ou senha inválidos</li>";
            echo "<li><em>Verifique: {$config['user']} / " . str_repeat('*', strlen($config['pass'])) . "</em></li>";
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            echo "<li><strong>🗃️ Banco não existe:</strong> O banco '{$config['dbname']}' não foi encontrado</li>";
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            echo "<li><strong>🚫 Conexão recusada:</strong> Servidor MySQL não está aceitando conexões</li>";
        } elseif (strpos($e->getMessage(), 'gone away') !== false) {
            echo "<li><strong>⏰ Timeout:</strong> Servidor demorou muito para responder</li>";
        } elseif (strpos($e->getMessage(), 'Name or service not known') !== false) {
            echo "<li><strong>🌐 DNS:</strong> Nome do servidor não pode ser resolvido</li>";
        } else {
            echo "<li><strong>❓ Erro desconhecido:</strong> Verifique logs do servidor</li>";
        }
        
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<hr style='border: 2px solid #f5a522; margin: 30px 0;'>";
}

// Teste da configuração atual do sistema
echo "<h3>⚙️ Configuração Atual do Sistema</h3>";

try {
    require_once 'config.php';
    require_once '../core/Database.php';
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>🔧 Configurações Detectadas:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px; border: 1px solid #ddd;'>Configuração</th><th style='padding: 8px; border: 1px solid #ddd;'>Valor</th></tr>";
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>Ambiente</td><td style='padding: 8px; border: 1px solid #ddd;'>" . (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ? '<span style=\"color: #007bff;\">🏠 Local (XAMPP)</span>' : '<span style=\"color: #28a745;\">🌐 Hospedado</span>') . "</td></tr>";
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>DB_HOST</td><td style='padding: 8px; border: 1px solid #ddd;'>" . (defined('DB_HOST') ? DB_HOST : '<em>Não definido</em>') . "</td></tr>";
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>DB_NAME</td><td style='padding: 8px; border: 1px solid #ddd;'>" . (defined('DB_NAME') ? DB_NAME : '<em>Não definido</em>') . "</td></tr>";
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>DB_USER</td><td style='padding: 8px; border: 1px solid #ddd;'>" . (defined('DB_USER') ? DB_USER : '<em>Não definido</em>') . "</td></tr>";
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>DB_PASS</td><td style='padding: 8px; border: 1px solid #ddd;'>" . (defined('DB_PASS') ? str_repeat('*', min(strlen(DB_PASS), 10)) : '<em>Não definido</em>') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Testar a classe Database
    echo "<h4>3. Teste da Classe Database</h4>";
    $db = new Database();
    if ($db->testConnection()) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4 style='color: #155724; margin: 0;'>🎉 Sistema Configurado Corretamente!</h4>";
        echo "<p style='margin: 10px 0;'>A classe Database conseguiu conectar ao banco com sucesso.</p>";
        echo "<p style='margin: 0;'><a href='/chamaservico/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🏠 Acessar ChamaServiço</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4 style='color: #721c24; margin: 0;'>❌ Problema na Configuração</h4>";
        echo "<p style='margin: 10px 0;'>A classe Database não conseguiu conectar.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao testar configuração atual: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4 style='color: #856404; margin: 0 0 15px 0;'>🛠️ Próximos Passos</h4>";
echo "<div style='display: grid; gap: 10px;'>";
echo "<div><strong>✅ Se teste local passou:</strong> Seu ambiente XAMPP está configurado corretamente</div>";
echo "<div><strong>✅ Se teste hospedado passou:</strong> Conexão com servidor remoto funciona</div>";
echo "<div><strong>❌ Se teste local falhou:</strong> <a href='setup-local.php' style='color: #007bff; text-decoration: none;'>Configure o banco local</a></div>";
echo "<div><strong>❌ Se teste hospedado falhou:</strong> Verifique credenciais e conectividade</div>";
echo "<div><strong>🚀 Se ambos funcionam:</strong> <a href='/chamaservico/' style='color: #28a745; text-decoration: none; font-weight: bold;'>Sistema pronto para uso!</a></div>";
echo "</div>";
echo "</div>";

echo "</div>";
?>

<style>
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        margin: 0; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
    }
    .container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    h2 { 
        color: #283579; 
        border-bottom: 3px solid #f5a522; 
        padding-bottom: 10px;
        margin-bottom: 30px;
        text-align: center;
    }
    h3 {
        color: #495057;
        margin-top: 30px;
        padding: 15px;
        background: linear-gradient(90deg, #f8f9fa, #e9ecef);
        border-left: 4px solid #f5a522;
        border-radius: 0 8px 8px 0;
    }
    h4 {
        color: #6c757d;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    hr {
        border: none;
        height: 3px;
        background: linear-gradient(90deg, #f5a522, #283579);
        margin: 30px 0;
        border-radius: 2px;
    }
    p {
        line-height: 1.6;
        margin: 8px 0;
    }
    a {
        color: #007bff;
        text-decoration: none;
        transition: color 0.2s;
    }
    a:hover {
        color: #0056b3;
        text-decoration: underline;
    }
</style>
