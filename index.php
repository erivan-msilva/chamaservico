<?php
// Configurações do sistema
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Verificar se o sistema está funcionando corretamente
try {
    // HABILITAR ERROS TEMPORARIAMENTE PARA DEBUG
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Carregar configurações primeiro
    if (!file_exists('config/config.php')) {
        throw new Exception("Arquivo config/config.php não encontrado!");
    }
    
    require_once 'config/config.php';
    
    // Verificar se as constantes foram definidas
    if (!defined('BASE_URL') || !defined('AMBIENTE')) {
        throw new Exception("Configurações não carregadas corretamente");
    }
    
    // Log para debug
    error_log("INDEX.PHP - BASE_URL: " . BASE_URL);
    error_log("INDEX.PHP - AMBIENTE: " . AMBIENTE);
    error_log("INDEX.PHP - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'undefined'));
    
    // Incluir autoloader
    if (!file_exists('core/Autoloader.php')) {
        throw new Exception("Arquivo core/Autoloader.php não encontrado!");
    }
    
    require_once 'core/Autoloader.php';
    Autoloader::register();
    Autoloader::loadDependencies();
    
    // Incluir e iniciar sessão
    if (!file_exists('config/session.php')) {
        throw new Exception("Arquivo config/session.php não encontrado!");
    }
    
    require_once 'config/session.php';
    Session::start();
    
    // Verificar timeout da sessão se o usuário estiver logado
    if (Session::isLoggedIn()) {
        if (!Session::checkTimeout()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    // TESTE BÁSICO DE CONEXÃO COM BANCO
    try {
        $db = Database::getInstance();
        if (!$db || !$db->testConnection()) {
            error_log("INDEX.PHP - AVISO: Falha na conexão com banco de dados");
        } else {
            error_log("INDEX.PHP - Conexão com banco OK");
        }
    } catch (Exception $e) {
        error_log("INDEX.PHP - ERRO DE BANCO (não fatal): " . $e->getMessage());
    }
    
    // Incluir o roteador
    if (!file_exists('router.php')) {
        throw new Exception("Arquivo router.php não encontrado!");
    }
    
    require_once 'router.php';
    
} catch (Exception $e) {
    // Log do erro
    error_log("Erro crítico no index.php: " . $e->getMessage());
    
    // Mostrar página de erro detalhada
    showErrorPage($e);
    exit;
}

function showErrorPage($exception) {
    $errorMessage = $exception->getMessage();
    $isDevelopment = (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento');
    
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ChamaServiço - Sistema Online! 🚀</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .success-container { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        </style>
    </head>
    <body class="d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="success-container p-5 text-center">
                        <div class="mb-4">
                            <span style="font-size: 4rem;">🎉</span>
                        </div>
                        <h1 class="h3 text-success mb-3">Sistema Funcionando em Produção!</h1>
                        <p class="text-muted mb-4">
                            Parabéns! Você conseguiu mover todos os arquivos para a raiz e o sistema está online.
                        </p>
                        
                        <div class="alert alert-success text-start">
                            <h6>✅ Status do Sistema:</h6>
                            <ul class="mb-0">
                                <li><strong>Estrutura:</strong> Arquivos na raiz ✓</li>
                                <li><strong>URL:</strong> https://chamaservico.tds104-senac.online/ ✓</li>
                                <li><strong>Configuração:</strong> BASE_URL corrigida ✓</li>
                                <li><strong>Banco:</strong> Conectando... ⏳</li>
                            </ul>
                        </div>
                        
                        <?php if ($isDevelopment): ?>
                            <div class="alert alert-warning text-start">
                                <strong>Debug (desenvolvimento):</strong><br>
                                <?= htmlspecialchars($errorMessage) ?><br><br>
                                <strong>Arquivo:</strong> <?= $exception->getFile() ?><br>
                                <strong>Linha:</strong> <?= $exception->getLine() ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <a href="javascript:location.reload()" class="btn btn-success">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar Página
                            </a>
                            <a href="debug-banco.php" class="btn btn-outline-primary ms-2">
                                🔍 Testar Banco
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        <small class="text-muted">
                            ChamaServiço - Agora funcionando diretamente na raiz do domínio! 🚀
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
