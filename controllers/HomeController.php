<?php
require_once 'config/session.php';

class HomeController {
    public function index() {
        // Se o usuário está logado, redirecionar para seu dashboard apropriado
        if (Session::isLoggedIn()) {
            if (Session::isPrestador() && !Session::isCliente()) {
                header('Location: /chamaservico/prestador/dashboard');
                exit;
            } else if (Session::isCliente()) {
                header('Location: /chamaservico/cliente/dashboard');
                exit;
            }
        }
        
        // Se não está logado ou não se encaixa nas condições acima, mostrar a página inicial
        include 'views/public/HomePage.php';
    }
    
    public function acessoNegado() {
        include 'views/erros/acesso_negado.php';
    }
    
    public function adminNotFound() {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Admin - Não Encontrado</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-body text-center'>
                                <i class='bi bi-shield-x' style='font-size: 4rem; color: #dc3545;'></i>
                                <h2 class='mt-3'>Painel Admin Não Configurado</h2>
                                <p class='text-muted'>O controlador AdminController não foi encontrado.</p>
                                <p><small>Verifique se o arquivo <code>controllers/AdminController.php</code> existe.</small></p>
                                <a href='/chamaservico/' class='btn btn-primary'>Voltar ao Início</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>

