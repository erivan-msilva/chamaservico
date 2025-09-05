<?php
// Router otimizado para produção - CORRIGIDO PARA RAIZ

// HABILITAR ERROS PARA DEBUG
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de início
error_log("=== ROUTER INICIADO ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'undefined'));
error_log("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'undefined'));
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'undefined'));

// Carregar configurações primeiro
try {
    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/config/config.php';
        error_log("Config carregado - BASE_URL: " . BASE_URL);
    }
} catch (Exception $e) {
    error_log("ERRO ao carregar config: " . $e->getMessage());
    die("Erro de configuração: " . $e->getMessage());
}

// Carregar autoloader
try {
    if (!class_exists('Autoloader')) {
        require_once 'core/Autoloader.php';
        Autoloader::register();
        Autoloader::loadDependencies();
        error_log("Autoloader carregado");
    }
} catch (Exception $e) {
    error_log("ERRO no autoloader: " . $e->getMessage());
    die("Erro no autoloader: " . $e->getMessage());
}

class Router {
    private $routes = [];
    private $basePath = '';

    public function __construct() {
        $this->detectBasePath();
    }

    private function detectBasePath() {
        // CORREÇÃO: Para arquivos na raiz do domínio, sempre usar base path vazio
        $this->basePath = '';
        
        error_log("Base path detectado: '" . $this->basePath . "' (Arquivos na raiz do domínio)");
    }

    public function get($path, $controller, $method) {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post($path, $controller, $method) {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remover query string
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        error_log("Processando - Method: $method, Original path: $path");
        
        // Remover base path se existir
        if (!empty($this->basePath) && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
            error_log("Path após remover base: $path");
        }
        
        // Garantir que comece com /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Remover trailing slash (exceto para raiz)
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }
        
        error_log("Path final processado: $path");

        // Buscar rota
        if (isset($this->routes[$method][$path])) {
            error_log("Rota encontrada: {$this->routes[$method][$path]['controller']}::{$this->routes[$method][$path]['method']}");
            $this->executeRoute($this->routes[$method][$path]);
            return;
        }

        error_log("Rota não encontrada para: $method $path");
        $this->showNotFound($path, $method);
    }

    private function executeRoute($route) {
        $controllerName = $route['controller'];
        $methodName = $route['method'];

        try {
            // Middleware para autenticação
            if (method_exists($controllerName, 'middleware')) {
                $controller = new $controllerName();
                if (!$controller->middleware()) {
                    throw new Exception("Acesso negado para esta rota.");
                }
            }

            error_log("Executando: $controllerName::$methodName");
            
            if (!class_exists($controllerName)) {
                throw new Exception("Controller '$controllerName' não encontrado");
            }
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $methodName)) {
                throw new Exception("Método '$methodName' não encontrado no controller '$controllerName'");
            }
            
            $controller->$methodName();
            error_log("Rota executada com sucesso");
            
        } catch (Exception $e) {
            error_log("ERRO na execução da rota: " . $e->getMessage());
            $this->showError("Erro interno: " . $e->getMessage());
        }
    }

    private function showNotFound($path, $method) {
        http_response_code(404);
        
        // CORREÇÃO: URLs sem subdiretório hardcoded
        $loginUrl = BASE_URL . '/login';
        $homeUrl = BASE_URL . '/';
        
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>404 - Página não encontrada</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='card'>
                    <div class='card-body text-center'>
                        <h1 class='display-1 text-muted'>404</h1>
                        <h2>Página não encontrada</h2>
                        <p><strong>Rota solicitada:</strong> $method $path</p>
                        <a href='$homeUrl' class='btn btn-primary'>Página Inicial</a>
                        <a href='$loginUrl' class='btn btn-outline-primary'>Login</a>
                        
                        <hr>
                        <details>
                            <summary>Debug Info</summary>
                            <div class='text-start mt-2'>
                                <p><strong>BASE_URL:</strong> " . BASE_URL . "</p>
                                <p><strong>Ambiente:</strong> " . AMBIENTE . "</p>
                                <p><strong>Base Path:</strong> {$this->basePath}</p>
                                <strong>Rotas disponíveis:</strong>
                                <ul>";
        
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $config) {
                echo "<li>$method $route → {$config['controller']}::{$config['method']}</li>";
            }
        }
        
        echo "              </ul>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }

    private function showError($message) {
        http_response_code(500);
        
        // CORREÇÃO: URL sem subdiretório hardcoded
        $homeUrl = BASE_URL . '/';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Erro 500</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='card'>
                    <div class='card-body text-center'>
                        <h1 class='text-danger'>Erro 500</h1>
                        <p>$message</p>
                        <a href='" . BASE_URL . "' class='btn btn-primary'>Voltar ao Início</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}

// Configurar router
try {
    $router = new Router();

    // ========================================
    // ROTAS PRINCIPAIS - FUNCIONANDO NA RAIZ
    // ========================================
    $router->get('/', 'HomeController', 'index');
    $router->get('/home', 'HomeController', 'index');
    
    // Autenticação
    $router->get('/login', 'AuthController', 'login');
    $router->post('/login', 'AuthController', 'authenticate');
    $router->get('/registro', 'AuthController', 'registro');
    $router->post('/registro', 'AuthController', 'store');
    $router->get('/logout', 'AuthController', 'logout');
    
    // Redefinição de senha
    $router->get('/redefinir-senha', 'AuthController', 'redefinirSenha');
    $router->post('/redefinir-senha', 'AuthController', 'redefinirSenha');
    $router->get('/redefinir-senha-nova', 'AuthController', 'redefinirSenhaNova');
    $router->post('/redefinir-senha-nova', 'AuthController', 'redefinirSenhaNova');
    
    // ========================================
    // ÁREA DO CLIENTE - ROTAS COMPLETAS
    // ========================================
    $router->get('/cliente/dashboard', 'ClienteDashboardController', 'index');
    
    // Solicitações do Cliente
    $router->get('/cliente/solicitacoes', 'SolicitacaoController', 'listar');
    $router->get('/cliente/solicitacoes/criar', 'SolicitacaoController', 'criar');
    $router->post('/cliente/solicitacoes/criar', 'SolicitacaoController', 'criar');
    $router->get('/cliente/solicitacoes/editar', 'SolicitacaoController', 'editar');
    $router->post('/cliente/solicitacoes/editar', 'SolicitacaoController', 'editar');
    $router->get('/cliente/solicitacoes/visualizar', 'SolicitacaoController', 'visualizar');
    $router->post('/cliente/solicitacoes/deletar', 'SolicitacaoController', 'deletar');
    $router->get('/cliente/solicitacoes/baixar-imagens', 'SolicitacaoController', 'baixarImagens');
    
    // Propostas do Cliente
    $router->get('/cliente/propostas/recebidas', 'ClientePropostaController', 'recebidas');
    $router->get('/cliente/propostas/comparar', 'ClientePropostaController', 'comparar');
    $router->get('/cliente/propostas/detalhes', 'ClientePropostaController', 'detalhes');
    $router->post('/cliente/propostas/aceitar', 'ClientePropostaController', 'aceitar');
    $router->post('/cliente/propostas/recusar', 'ClientePropostaController', 'recusar');
    
    // NOVO: Serviços do Cliente
    $router->get('/cliente/servicos/concluidos', 'ClienteServicoController', 'concluidos');
    $router->get('/cliente/servicos/avaliar', 'ClienteServicoController', 'avaliar');
    $router->post('/cliente/servicos/avaliar', 'ClienteServicoController', 'avaliar');
    $router->post('/cliente/servicos/confirmar-conclusao', 'ClienteServicoController', 'confirmarConclusao');

    // ========================================
    // ÁREA DO PRESTADOR - ROTAS COMPLETAS
    // ========================================
    $router->get('/prestador/dashboard', 'PrestadorController', 'dashboard');
    $router->get('/prestador/solicitacoes', 'PrestadorController', 'solicitacoes');
    $router->get('/prestador/solicitacoes/detalhes', 'PrestadorController', 'detalheSolicitacao');
    $router->post('/prestador/solicitacoes/proposta', 'PrestadorController', 'enviarProposta');
    
    // Propostas do Prestador
    $router->get('/prestador/propostas', 'PropostaController', 'minhas');
    $router->get('/prestador/propostas/detalhes', 'PropostaController', 'detalhes');
    $router->post('/prestador/propostas/cancelar', 'PropostaController', 'cancelar');
    $router->post('/prestador/propostas/negociar', 'PropostaController', 'negociar');
    
    // Serviços em Andamento (Prestador)
    $router->get('/prestador/servicos/andamento', 'PropostaController', 'servicosAndamento');
    $router->get('/prestador/servicos/detalhes', 'PropostaController', 'detalhesServico');
    $router->post('/prestador/servicos/atualizar-status', 'PropostaController', 'atualizarStatus');
    
    // ========================================
    // PERFIL E CONFIGURAÇÕES - CORRIGIDO
    // ========================================
    
    // Perfil Geral (funciona para ambos os tipos)
    $router->get('/perfil', 'PerfilController', 'index');
    $router->post('/perfil', 'PerfilController', 'atualizar');
    $router->get('/perfil/editar', 'PerfilController', 'editar');
    $router->post('/perfil/foto', 'PerfilController', 'atualizarFoto');
    
    // ROTAS DE PERFIL ESPECÍFICAS POR TIPO DE USUÁRIO
    
    // Cliente - Perfil
    $router->get('/cliente/perfil', 'PerfilController', 'index');
    $router->post('/cliente/perfil', 'PerfilController', 'atualizar');
    $router->get('/cliente/perfil/editar', 'PerfilController', 'editar');
    $router->post('/cliente/perfil/foto', 'PerfilController', 'atualizarFoto');
    
    // Prestador - Perfil  
    $router->get('/prestador/perfil', 'PerfilController', 'index');
    $router->post('/prestador/perfil', 'PerfilController', 'atualizar');
    $router->get('/prestador/perfil/editar', 'PerfilController', 'editar');
    $router->post('/prestador/perfil/foto', 'PerfilController', 'atualizarFoto');

    // Endereços - CORRIGIDO: Usar o mesmo método para GET e POST
    $router->get('/cliente/perfil/enderecos', 'PerfilController', 'enderecos');
    $router->post('/cliente/perfil/enderecos', 'PerfilController', 'enderecos');
    $router->get('/prestador/perfil/enderecos', 'PerfilController', 'enderecos');
    $router->post('/prestador/perfil/enderecos', 'PerfilController', 'enderecos');
    
    // API para buscar CEP - CORRIGIDO
    $router->get('/perfil/api/buscar-cep', 'PerfilController', 'buscarCep');

    // ========================================
    // NOTIFICAÇÕES
    // ========================================
    $router->get('/notificacoes', 'NotificacaoController', 'index');
    $router->post('/notificacoes/marcar-lida', 'NotificacaoController', 'marcarLida');
    $router->get('/notificacoes/contador', 'NotificacaoController', 'contador');
    
    // ========================================
    // API E AJAX
    // ========================================
    $router->get('/api/cep', 'ApiController', 'buscarCep');
    $router->get('/api/tipos-servico', 'ApiController', 'tiposServico');
    
    // ========================================
    // PÁGINAS ESPECIAIS
    // ========================================
    $router->get('/acesso-negado', 'HomeController', 'acessoNegado');
    
    // ========================================
    // REDIRECIONAMENTOS PARA COMPATIBILIDADE
    // ========================================
    // Redirecionar rotas antigas (sem /cliente ou /prestador)
    $router->get('/solicitacoes', 'SolicitacaoController', 'redirectToClient');
    $router->get('/solicitacoes/criar', 'SolicitacaoController', 'redirectToClient');
    $router->get('/propostas/recebidas', 'ClientePropostaController', 'recebidas');
    
    // ========================================
    // ÁREA ADMINISTRATIVA
    // ========================================
    $router->get('/admin', 'AdminController', 'dashboard');
    $router->get('/admin/dashboard', 'AdminController', 'dashboard');
    $router->get('/admin/usuarios', 'AdminController', 'usuarios');
    $router->get('/admin/solicitacoes', 'AdminController', 'solicitacoes');
    $router->get('/admin/propostas', 'AdminController', 'propostas');
    
    error_log("Rotas configuradas para raiz do domínio, executando router...");
    $router->run();
    
} catch (Exception $e) {
    error_log("ERRO FATAL no router: " . $e->getMessage());
    die("Erro fatal: " . $e->getMessage());
}
?>