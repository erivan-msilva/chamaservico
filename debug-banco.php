<?php
// DIAGNÓSTICO ESPECÍFICO DO BANCO DE DADOS
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🗄️ DIAGNÓSTICO DO BANCO DE DADOS</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Carregar configurações
require_once 'config/config.php';

echo "<h2>⚙️ 1. Configurações do Banco</h2>";
echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Banco:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Usuário:</strong> " . DB_USER . "</p>";
echo "<p><strong>Senha:</strong> " . str_repeat('*', strlen(DB_PASS)) . "</p>";

echo "<h2>🔌 2. Teste de Conexão</h2>";

try {
    // Tentar conexão direta com PDO
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    echo "<p>📡 Tentando conectar com: <code>$dsn</code></p>";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p style='color: green;'>✅ <strong>Conexão estabelecida com sucesso!</strong></p>";
    
    // Testar consulta básica
    $stmt = $pdo->query("SELECT VERSION() as versao_mysql");
    $resultado = $stmt->fetch();
    echo "<p>🐬 <strong>Versão do MySQL:</strong> {$resultado['versao_mysql']}</p>";
    
    echo "<h2>📋 3. Verificando Estrutura do Banco</h2>";
    
    // Listar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Total de tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    
    $tabelasEsperadas = [
        'tb_pessoa',
        'tb_endereco', 
        'tb_solicita_servico',
        'tb_proposta',
        'tb_tipo_servico',
        'tb_status_solicitacao',
        'tb_imagem_solicitacao',
        'tb_avaliacao',
        'tb_notificacao'
    ];
    
    echo "<h3>🔍 Tabelas Principais:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";
    
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelas)) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
                $count = $stmt->fetch()['total'];
                echo "<tr><td>$tabela</td><td style='color: green;'>✅ Existe</td><td>$count registros</td></tr>";
            } catch (Exception $e) {
                echo "<tr><td>$tabela</td><td style='color: orange;'>⚠️ Existe mas erro ao contar</td><td>{$e->getMessage()}</td></tr>";
            }
        } else {
            echo "<tr><td>$tabela</td><td style='color: red;'>❌ Não encontrada</td><td>-</td></tr>";
        }
    }
    echo "</table>";
    
    echo "<h3>📊 Todas as Tabelas no Banco:</h3>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>$tabela</li>";
    }
    echo "</ul>";
    
    // Verificar usuários de teste
    echo "<h2>👥 4. Verificando Usuários de Teste</h2>";
    try {
        $stmt = $pdo->query("SELECT id, nome, email, tipo, ativo FROM tb_pessoa LIMIT 5");
        $usuarios = $stmt->fetchAll();
        
        if (!empty($usuarios)) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Ativo</th></tr>";
            foreach ($usuarios as $user) {
                $status = $user['ativo'] ? '✅ Ativo' : '❌ Inativo';
                echo "<tr><td>{$user['id']}</td><td>{$user['nome']}</td><td>{$user['email']}</td><td>{$user['tipo']}</td><td>$status</td></tr>";
            }
            echo "</table>";
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>🔑 Contas de Teste Disponíveis:</h4>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> teste@sistema.com <strong>Senha:</strong> 123456</li>";
            echo "<li><strong>Email:</strong> contatoerivan.ms@gmail.com <strong>Senha:</strong> 123456</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Nenhum usuário encontrado! O banco pode não ter dados.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao verificar usuários: " . $e->getMessage() . "</p>";
    }
    
    // Verificar tipos de serviço
    echo "<h2>🛠️ 5. Verificando Tipos de Serviço</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_tipo_servico WHERE ativo = 1");
        $totalServicos = $stmt->fetch()['total'];
        echo "<p><strong>Tipos de serviço ativos:</strong> $totalServicos</p>";
        
        if ($totalServicos > 0) {
            $stmt = $pdo->query("SELECT nome FROM tb_tipo_servico WHERE ativo = 1 LIMIT 5");
            $servicos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Exemplos:</strong> " . implode(', ', $servicos) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao verificar tipos de serviço: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px;'>";
    echo "<h3>❌ ERRO DE CONEXÃO</h3>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    
    echo "<h4>🔧 Possíveis Soluções:</h4>";
    echo "<ul>";
    echo "<li>Verifique se as credenciais estão corretas</li>";
    echo "<li>Confirme se o banco de dados foi criado no painel de controle</li>";
    echo "<li>Verifique se o usuário tem permissões adequadas</li>";
    echo "<li>Teste a conectividade de rede com o host</li>";
    echo "<li>Confirme se o banco foi importado corretamente</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;'>";
echo "<h4>📝 Próximos Passos:</h4>";
echo "<ol>";
echo "<li>Se a conexão falhou, verifique as credenciais no painel de controle</li>";
echo "<li>Se as tabelas não existem, importe o arquivo SQL</li>";
echo "<li>Se não há usuários, insira dados de teste</li>";
echo "<li>Após resolver os problemas, delete este arquivo de diagnóstico</li>";
echo "</ol>";
echo "</div>";
?>
