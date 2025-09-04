<?php
// test.php - Arquivo para testar configurações do servidor

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Teste de Configuração - ChamaServiço</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header'>
                <h1>🔧 Teste de Configuração do Servidor</h1>
            </div>
            <div class='card-body'>";

// Informações básicas
echo "<h3>📊 Informações do Servidor</h3>
      <div class='row'>
          <div class='col-md-6'>
              <strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'indefinido') . "<br>
              <strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'indefinido') . "<br>
              <strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'indefinido') . "<br>
              <strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'indefinido') . "<br>
          </div>
          <div class='col-md-6'>
              <strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'indefinido') . "<br>
              <strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'indefinido') . "<br>
              <strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'Sim' : 'Não') . "<br>
              <strong>Diretório atual:</strong> " . __DIR__ . "<br>
          </div>
      </div>";

echo "<hr>";

// Verificar arquivos importantes
echo "<h3>📁 Verificação de Arquivos</h3>";

$arquivos = [
    'router.php' => file_exists(__DIR__ . '/router.php'),
    'config/config.php' => file_exists(__DIR__ . '/config/config.php'),
    'core/Autoloader.php' => file_exists(__DIR__ . '/core/Autoloader.php'),
    '.htaccess' => file_exists(__DIR__ . '/.htaccess'),
    'controllers/' => is_dir(__DIR__ . '/controllers'),
    'models/' => is_dir(__DIR__ . '/models')
];

echo "<div class='row'>";
foreach ($arquivos as $arquivo => $existe) {
    $status = $existe ?
        "<span class='badge bg-success'>✓ Existe</span>" :
        "<span class='badge bg-danger'>✗ Não encontrado</span>";

    echo "<div class='col-md-4 mb-2'>
              <strong>$arquivo:</strong> $status
          </div>";
}
echo "</div>";

echo "<hr>";

// Testar configuração
echo "<h3>⚙️ Configuração da Aplicação</h3>";

try {
    if (file_exists(__DIR__ . '/config/config.php')) {
        require_once __DIR__ . '/config/config.php';

        echo "<div class='alert alert-success'>
                  ✓ Configuração carregada com sucesso!<br>
                  <strong>BASE_URL:</strong> " . (defined('BASE_URL') ? BASE_URL : 'indefinido') . "<br>
                  <strong>AMBIENTE:</strong> " . (defined('AMBIENTE') ? AMBIENTE : 'indefinido') . "
              </div>";
    } else {
        echo "<div class='alert alert-danger'>
                  ✗ Arquivo config/config.php não encontrado!
              </div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
              ✗ Erro ao carregar configuração: " . $e->getMessage() . "
          </div>";
}

echo "<hr>";

// Testar autoloader
echo "<h3>🔄 Teste do Autoloader</h3>";

try {
    if (file_exists(__DIR__ . '/core/Autoloader.php')) {
        require_once __DIR__ . '/core/Autoloader.php';
        Autoloader::register();

        echo "<div class='alert alert-success'>
                  ✓ Autoloader carregado com sucesso!
              </div>";
    } else {
        echo "<div class='alert alert-danger'>
                  ✗ Arquivo core/Autoloader.php não encontrado!
              </div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
              ✗ Erro no autoloader: " . $e->getMessage() . "
          </div>";
}

echo "<hr>";

// Verificar mod_rewrite
echo "<h3>🌐 Verificação do Mod Rewrite</h3>";

if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    echo "<div class='alert alert-success'>
              ✓ Mod Rewrite está habilitado!
          </div>";
} else {
    echo "<div class='alert alert-warning'>
              ⚠️ Não foi possível verificar o Mod Rewrite automaticamente.
              <br>Certifique-se de que o mod_rewrite está habilitado no Apache.
          </div>";
}

// Links de teste
if (defined('BASE_URL')) {
    echo "<hr>
          <h3>🔗 Links de Teste</h3>
          <div class='d-grid gap-2 d-md-block'>
              <a href='" . BASE_URL . "' class='btn btn-primary'>Home (Raiz)</a>
              <a href='" . BASE_URL . "/login' class='btn btn-outline-primary'>Login</a>
              <a href='" . BASE_URL . "/registro' class='btn btn-outline-success'>Registro</a>
              <a href='" . BASE_URL . "/teste-inexistente' class='btn btn-outline-danger'>Rota Inexistente (404)</a>
          </div>";
}

echo "        </div>
        </div>
    </div>
</body>
</html>";
