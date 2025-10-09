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
        // Inclua sua view de erro 404 personalizada
        include __DIR__ . '/views/erros/404.php';
        exit;
    }
     
    private function showError($message) {
        http_response_code(500);
        // Inclua sua view de erro 500 personalizada ou apenas exiba a mensagem
        echo "<h1>Erro 500</h1><p>$message</p>";
        exit;
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
    
    // Redefinição de senha - ROTAS CORRETAS
    $router->get('/esqueci-senha', 'AuthController', 'redefinirSenha');
    $router->post('/esqueci-senha', 'AuthController', 'redefinirSenha');
    $router->get('/redefinir-nova', 'AuthController', 'redefinirSenhaNova');
    $router->post('/redefinir-nova', 'AuthController', 'redefinirSenhaNova');
    
    // Rotas alternativas para compatibilidade
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
    $router->get('/cliente/propostas/aceitar', 'ClientePropostaController', 'aceitar');
    $router->post('/cliente/propostas/aceitar', 'ClientePropostaController', 'aceitar');
    $router->post('/cliente/propostas/recusar', 'ClientePropostaController', 'recusar');
    
    // Rotas do cliente - serviços
    $router->get('/cliente/servicos', 'ClienteServicoController', 'index');
    $router->get('/cliente/servicos/andamento', 'ClienteServicoController', 'andamento');
    $router->get('/cliente/servicos/concluidos', 'ClienteServicoController', 'concluidos');
    $router->get('/cliente/servicos/cancelados', 'ClienteServicoController', 'cancelados');
    $router->get('/cliente/servicos/detalhes', 'ClienteServicoController', 'detalhes');
    $router->get('/cliente/servicos/avaliar', 'ClienteServicoController', 'avaliar');
    $router->post('/cliente/servicos/avaliar', 'ClienteServicoController', 'avaliar');

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
    $router->post('/cliente/perfil/editar', 'PerfilController', 'editar'); // <-- ADICIONADO
    $router->post('/cliente/perfil/foto', 'PerfilController', 'atualizarFoto');
    
    // Prestador - Perfil  
    $router->get('/prestador/perfil', 'PerfilController', 'index');
    $router->post('/prestador/perfil', 'PerfilController', 'atualizar');
    $router->get('/prestador/perfil/editar', 'PerfilController', 'editar');
    $router->post('/prestador/perfil/editar', 'PerfilController', 'editar');
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
    $router->post('/notificacoes', 'NotificacaoController', 'index');
    $router->get('/notificacoes/marcar-lida', 'NotificacaoController', 'marcarComoLida');
    $router->post('/notificacoes/marcar-lida', 'NotificacaoController', 'marcarComoLida');
    $router->post('/notificacoes/deletar', 'NotificacaoController', 'deletar');
    $router->get('/notificacoes/contador', 'NotificacaoController', 'contador');
    $router->post('/notificacoes/marcar-todas-lidas', 'NotificacaoController', 'marcarTodasComoLidas');
    $router->get('/notificacoes/marcar-todas-lidas', 'NotificacaoController', 'marcarTodasComoLidas'); 

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
    // ÁREA ADMINISTRATIVA - ROTAS COMPLETAS
    // ========================================
    
    // Login Administrativo - Seu AuthAdminController
    $router->get('/admin/login', 'AuthAdminController', 'login');
    $router->post('/admin/login', 'AuthAdminController', 'authenticate');
    $router->get('/admin/logout', 'AuthAdminController', 'logout');
    
    // Dashboard - Redirecionar para AdminController simples
    $router->get('/admin', 'AdminController', 'dashboard');
    $router->get('/admin/dashboard', 'AdminController', 'dashboard');
    
    // Páginas Admin - Usando AdminController simples que chama suas views
    $router->get('/admin/usuarios', 'AdminController', 'usuarios');
    $router->get('/admin/usuarios/visualizar', 'AdminController', 'usuariosVisualizar'); // NOVO
    $router->post('/admin/usuarios/alterar-status', 'AdminController', 'usuariosAlterarStatus'); // NOVO
    $router->post('/admin/usuarios/ativar', 'AdminController', 'usuariosAtivar'); // NOVO
    $router->post('/admin/usuarios/desativar', 'AdminController', 'usuariosDesativar'); // NOVO
    
    $router->get('/admin/solicitacoes', 'AdminController', 'solicitacoes');
    $router->get('/admin/solicitacoes/visualizar', 'AdminController', 'solicitacoesVisualizar'); // NOVO
    $router->post('/admin/solicitacoes/alterar-status', 'AdminController', 'solicitacoesAlterarStatus'); // NOVO
    
    $router->get('/admin/propostas', 'AdminController', 'propostas');
    $router->get('/admin/configuracoes', 'AdminController', 'configuracoes');
    $router->post('/admin/configuracoes/salvar', 'AdminController', 'salvarConfiguracoes'); // NOVO
    $router->post('/admin/configuracoes/testar-email', 'AdminController', 'testarEmail'); // NOVO
    $router->post('/admin/configuracoes/gerar-backup', 'AdminController', 'gerarBackup'); // NOVO
    $router->get('/admin/tipos-servico', 'AdminController', 'tiposServico');
    $router->post('/admin/tipos-servico/criar', 'AdminController', 'tiposServicoSalvar'); // <-- CORRIGIDO: usar AdminController e método tiposServicoSalvar
    $router->post('/admin/tipos-servico/ativar', 'AdminController', 'tiposServicoAtivar'); // NOVO
    $router->post('/admin/tipos-servico/desativar', 'AdminController', 'tiposServicoDesativar'); // NOVO
    $router->get('/admin/relatorios', 'AdminController', 'relatorios');

    error_log("Rotas configuradas para raiz do domínio, executando router...");
    $router->run();
    
} catch (Exception $e) {
    error_log("ERRO FATAL no router: " . $e->getMessage());
    die("Erro fatal: " . $e->getMessage());
}
?>