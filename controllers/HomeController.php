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
}
?>

