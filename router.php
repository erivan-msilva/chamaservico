<?php
// Este arquivo está sendo utilizado como o roteador principal do sistema.
// Ele é incluído no final do index.php para definir e executar as rotas do sistema web.
// Não é necessário usar core/Router.php, pois router.php já implementa toda a lógica e configuração das rotas.

require_once 'controllers/AuthController.php';
require_once 'controllers/HomeController.php';
require_once 'controllers/SolicitacaoController.php';
require_once 'controllers/PerfilController.php';
require_once 'controllers/ClientePerfilController.php';
require_once 'controllers/ClientePropostaController.php';
require_once 'controllers/PrestadorPerfilController.php';
require_once 'controllers/PrestadorController.php';
require_once 'controllers/PropostaController.php';
require_once 'controllers/NegociacaoController.php';
require_once 'controllers/NotificacaoController.php';
require_once 'controllers/OrdemServicoController.php';
require_once 'controllers/ClienteDashboardController.php';

// Verificar se AdminController existe antes de incluir
if (file_exists('controllers/AdminController.php')) {
    require_once 'controllers/AdminController.php';
}

class Router {
    private $routes = [];

    public function get($path, $controller, $method) {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post($path, $controller, $method) {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover o prefixo do projeto se estiver presente
        $basePath = '/chamaservico';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Se path está vazio, definir como /
        if (empty($path) || $path === '') {
            $path = '/';
        }

        // Buscar rota
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $controllerName = $route['controller'];
            $methodName = $route['method'];

            try {
                // Verificar se a classe existe
                if (!class_exists($controllerName)) {
                    throw new Exception("Controller '$controllerName' não encontrado");
                }
                
                $controller = new $controllerName();
                
                // Verificar se o método existe
                if (!method_exists($controller, $methodName)) {
                    throw new Exception("Método '$methodName' não encontrado no controller '$controllerName'");
                }
                
                $controller->$methodName();
            } catch (Exception $e) {
                $this->showError("Erro interno: " . $e->getMessage());
            }
        } else {
            $this->showNotFound($path, $method);
        }
    }

    private function showNotFound($path, $method) {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>404 - Página não encontrada</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-body text-center'>
                                <h1 class='display-1 text-muted'>404</h1>
                                <h2>Página não encontrada</h2>
                                <p><strong>Rota solicitada:</strong> $path</p>
                                <p><strong>Método:</strong> $method</p>
                                <a href='/chamaservico/login' class='btn btn-primary'>Ir para Login</a>
                                
                                <hr>
                                <details class='mt-3'>
                                    <summary>Debug Info:</summary>
                                    <div class='text-start mt-2'>
                                        <small><strong>Rotas disponíveis:</strong></small>
                                        <ul class='list-unstyled small'>";
        
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $config) {
                echo "<li>$method $route → {$config['controller']}::{$config['method']}</li>";
            }
        }
        
        echo "                        </ul>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }

    private function showError($message) {
        http_response_code(500);
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
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
                        <a href='/chamaservico' class='btn btn-primary'>Voltar ao Início</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}

// Configurar router
$router = new Router();

// ========================================
// ROTAS PÚBLICAS
// ========================================
$router->get('/', 'HomeController', 'index');
$router->get('/home', 'HomeController', 'index');

// Rotas de autenticação
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'authenticate');
$router->get('/registro', 'AuthController', 'registro');
$router->post('/registro', 'AuthController', 'store');
$router->get('/logout', 'AuthController', 'logout');

// Rotas de redefinição de senha
$router->get('/redefinir-senha', 'AuthController', 'redefinirSenha');
$router->post('/redefinir-senha', 'AuthController', 'redefinirSenha');
$router->get('/redefinir-senha-nova', 'AuthController', 'redefinirSenhaNova');
$router->post('/redefinir-senha-nova', 'AuthController', 'redefinirSenhaNova');

// Rota de acesso negado
$router->get('/acesso-negado', 'HomeController', 'acessoNegado');

// ========================================
// ROTAS DO ADMINISTRADOR
// ========================================

// Verificar se AdminController está disponível antes de definir rotas admin
if (class_exists('AdminController')) {
    // Dashboard do Admin
    $router->get('/admin', 'AdminController', 'redirectToDashboard');
    $router->get('/admin/dashboard', 'AdminController', 'dashboard');
    $router->get('/admin/login', 'AdminController', 'login');
    $router->post('/admin/login', 'AdminController', 'authenticate');
    $router->get('/admin/logout', 'AdminController', 'logout');

    // Gerenciamento de Usuários
    $router->get('/admin/usuarios', 'AdminController', 'usuarios');
    $router->get('/admin/usuarios/dados', 'AdminController', 'getDadosUsuarios');
    $router->post('/admin/usuarios/toggle-status', 'AdminController', 'toggleStatusUsuario');
    $router->post('/admin/usuarios/editar', 'AdminController', 'editarUsuario');
    $router->post('/admin/usuarios/deletar', 'AdminController', 'deletarUsuario');

    // Gerenciamento de Tipos de Serviço
    $router->get('/admin/tipos-servico', 'AdminController', 'tiposServico');
    $router->post('/admin/tipos-servico/criar', 'AdminController', 'criarTipoServico');
    $router->post('/admin/tipos-servico/editar', 'AdminController', 'editarTipoServico');
    $router->post('/admin/tipos-servico/deletar', 'AdminController', 'deletarTipoServico');

    // Gerenciamento de Status de Solicitação
    $router->get('/admin/status-solicitacao', 'AdminController', 'statusSolicitacao');
    $router->post('/admin/status-solicitacao/criar', 'AdminController', 'criarStatusSolicitacao');
    $router->post('/admin/status-solicitacao/editar', 'AdminController', 'editarStatusSolicitacao');
    $router->post('/admin/status-solicitacao/deletar', 'AdminController', 'deletarStatusSolicitacao');

    // Relatórios
    $router->get('/admin/relatorios', 'AdminController', 'relatorios');
    $router->get('/admin/relatorios/dados', 'AdminController', 'getDadosRelatorios');
    $router->get('/admin/relatorios/exportar', 'AdminController', 'exportarRelatorio');

    // Monitoramento do Sistema
    $router->get('/admin/monitor', 'AdminController', 'monitor');
    $router->get('/admin/monitor/dados', 'AdminController', 'getDadosMonitor');

    // API do Admin (para requisições AJAX)
    $router->get('/admin/api/dashboard', 'AdminController', 'apiDashboard');
    $router->get('/admin/api/usuarios', 'AdminController', 'apiUsuarios');
    $router->post('/admin/api/criar-admin', 'AdminController', 'apiCriarAdmin');
    $router->post('/admin/toggle-status-usuario', 'AdminController', 'toggleStatusUsuario');
    $router->get('/admin/api/estatisticas', 'AdminController', 'apiEstatisticas');
} else {
    // Rotas de fallback se AdminController não estiver disponível
    $router->get('/admin', 'HomeController', 'acessoNegado');
    $router->get('/admin/login', 'HomeController', 'acessoNegado');
}

// ========================================
// ROTAS DE PERFIL GENÉRICAS (REDIRECIONAMENTO)
// ========================================
$router->get('/perfil', 'PerfilController', 'index');
$router->get('/perfil/editar', 'PerfilController', 'editar');
$router->post('/perfil/editar', 'PerfilController', 'editar');
$router->get('/perfil/enderecos', 'PerfilController', 'enderecos');
$router->post('/perfil/enderecos', 'PerfilController', 'enderecos');


// ========================================
// ROTAS DO CLIENTE
// ========================================

// Perfil do Cliente
$router->get('/cliente/perfil', 'ClientePerfilController', 'index');
$router->get('/cliente/perfil/editar', 'ClientePerfilController', 'editar');
$router->post('/cliente/perfil/editar', 'ClientePerfilController', 'editar');
$router->get('/cliente/perfil/enderecos', 'ClientePerfilController', 'enderecos');
$router->post('/cliente/perfil/enderecos', 'ClientePerfilController', 'enderecos');

// Solicitações do Cliente
$router->get('/cliente/solicitacoes', 'SolicitacaoController', 'listar');
$router->get('/cliente/solicitacoes/criar', 'SolicitacaoController', 'criar');
$router->post('/cliente/solicitacoes/criar', 'SolicitacaoController', 'criar');
$router->get('/cliente/solicitacoes/editar', 'SolicitacaoController', 'editar');
$router->post('/cliente/solicitacoes/editar', 'SolicitacaoController', 'editar');
$router->get('/cliente/solicitacoes/visualizar', 'SolicitacaoController', 'visualizar');
$router->post('/cliente/solicitacoes/deletar', 'SolicitacaoController', 'deletar');

// Propostas para Clientes
$router->get('/cliente/propostas/recebidas', 'ClientePropostaController', 'recebidas');
$router->get('/cliente/propostas/comparar', 'ClientePropostaController', 'comparar');
$router->get('/cliente/propostas/detalhes', 'ClientePropostaController', 'detalhes');
$router->post('/cliente/propostas/aceitar', 'ClientePropostaController', 'aceitar');
$router->post('/cliente/propostas/recusar', 'ClientePropostaController', 'recusar');

// ========================================
// ROTAS DO CLIENTE - DASHBOARD
// ========================================
$router->get('/cliente/dashboard', 'ClienteDashboardController', 'index');
$router->get('/cliente/dashboard/dados', 'ClienteDashboardController', 'getDashboardData');

// ========================================
// ROTAS DO PRESTADOR
// ========================================

// Perfil do Prestador
$router->get('/prestador/perfil', 'PrestadorPerfilController', 'index');
$router->get('/prestador/perfil/editar', 'PrestadorPerfilController', 'editar');
$router->post('/prestador/perfil/editar', 'PrestadorPerfilController', 'editar');
$router->get('/prestador/perfil/enderecos', 'PrestadorPerfilController', 'enderecos');
$router->post('/prestador/perfil/enderecos', 'PrestadorPerfilController', 'enderecos');

// Dashboard do Prestador
$router->get('/prestador/dashboard', 'PrestadorController', 'dashboard');
$router->get('/prestador/dashboard/dados', 'PrestadorController', 'getDashboardData');

// Solicitações para Prestadores
$router->get('/prestador/solicitacoes', 'PrestadorController', 'solicitacoes');
$router->get('/prestador/solicitacoes/detalhes', 'PrestadorController', 'detalheSolicitacao');

// Propostas do Prestador
$router->get('/prestador/propostas', 'PropostaController', 'minhas');
$router->post('/prestador/propostas/enviar', 'PrestadorController', 'enviarProposta');
$router->post('/prestador/propostas/cancelar', 'PropostaController', 'cancelar');

// Serviços em Andamento (Prestador)
$router->get('/prestador/servicos/andamento', 'PrestadorController', 'servicosAndamento');
$router->get('/prestador/servicos/detalhes', 'PrestadorController', 'servicoDetalhes');
$router->post('/prestador/servicos/atualizar-status', 'PrestadorController', 'atualizarStatusServico');

// ========================================
// ROTAS DE NOTIFICAÇÕES
// ========================================
$router->get('/notificacoes', 'NotificacaoController', 'index');
$router->get('/notificacoes/contador', 'NotificacaoController', 'contador');
$router->post('/notificacoes/marcar-lida', 'NotificacaoController', 'marcarComoLida');
$router->post('/notificacoes/marcar-todas-lidas', 'NotificacaoController', 'marcarTodasComoLidas');
$router->post('/notificacoes/deletar', 'NotificacaoController', 'deletar');

// ========================================
// ROTAS DE NEGOCIAÇÃO
// ========================================
$router->get('/negociacao/negociar', 'NegociacaoController', 'negociar');
$router->post('/negociacao/negociar', 'NegociacaoController', 'negociar');

// ========================================
// ROTAS DE COMPATIBILIDADE (LEGADO)
// ========================================

// Rotas antigas de solicitações (redirecionam para cliente)
$router->get('/solicitacoes', 'SolicitacaoController', 'redirectToClient');
$router->get('/solicitacoes/criar', 'SolicitacaoController', 'redirectToClient');
$router->post('/solicitacoes/criar', 'SolicitacaoController', 'redirectToClient');
$router->get('/solicitacoes/editar', 'SolicitacaoController', 'redirectToClient');
$router->post('/solicitacoes/editar', 'SolicitacaoController', 'redirectToClient');
$router->get('/solicitacoes/visualizar', 'SolicitacaoController', 'redirectToClient');
$router->post('/solicitacoes/deletar', 'SolicitacaoController', 'redirectToClient');

// Rotas antigas de propostas (redirecionam conforme usuário)
$router->get('/propostas/recebidas', 'PropostaController', 'redirectToUserType');
$router->post('/propostas/aceitar', 'PropostaController', 'redirectToUserType');
$router->post('/propostas/recusar', 'PropostaController', 'redirectToUserType');

// Rotas de Ordem de Serviço
$router->get('/ordem-servico/visualizar', 'OrdemServicoController', 'visualizar');
$router->get('/ordem-servico/download', 'OrdemServicoController', 'download');
$router->post('/ordem-servico/enviar-email', 'OrdemServicoController', 'enviarEmail');
$router->post('/ordem-servico/assinar', 'OrdemServicoController', 'assinar');
$router->get('/ordem-servico/listar', 'OrdemServicoController', 'listar');

// Executar roteamento
$router->run();
?>