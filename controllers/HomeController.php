<?php
require_once 'config/session.php';

class HomeController
{
    public function index()
    {
        // Se o usuário estiver logado, redirecionar para o dashboard apropriado
        if (Session::isLoggedIn()) {
            if (Session::isPrestador() && !Session::isCliente()) {
                header('Location: /chamaservico/prestador/dashboard');
                exit;
            } elseif (Session::isCliente()) {
                header('Location: /chamaservico/cliente/dashboard');
                exit;
            }
        }

        $title = 'ChamaServiço - Conectando você aos melhores prestadores';
        include 'views/home/index.php';
    }

    public function acessoNegado()
    {
        // CORRIGIDO: Caminho correto do arquivo
        include 'views/erros/acesso_negado.php';
    }

    public function notFound()
    {
        http_response_code(404);
        include 'views/erros/404.php';
    }

    public function serverError($errorMessage = null)
    {
        http_response_code(500);
        include 'views/erros/500.php';
    }

    public function adminNotFound()
    {
        $title = 'Área Administrativa - Não Disponível';
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-gear text-muted" style="font-size: 6rem;"></i>
                        </div>
                        <h1 class="display-5 fw-bold text-dark mb-3">Área Administrativa</h1>
                        <h2 class="h4 text-muted mb-4">Esta funcionalidade ainda não foi implementada</h2>
                        
                        <div class="alert alert-info border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-info me-3 fs-4"></i>
                                <div class="text-start">
                                    <strong>Em desenvolvimento:</strong>
                                    <p class="mb-0 mt-2">
                                        O painel administrativo está sendo desenvolvido e estará disponível em breve.
                                        Ele incluirá funcionalidades para gerenciar usuários, serviços, relatórios e configurações do sistema.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="/chamaservico/" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>
                                Voltar ao Início
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        include 'views/layouts/app.php';
    }
}
?>

