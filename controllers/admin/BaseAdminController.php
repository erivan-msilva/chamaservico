<?php
require_once 'config/session.php';

class BaseAdminController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->verificarAutenticacao();
    }
    
    protected function verificarAutenticacao() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // Verificar timeout da sessão (4 horas)
        if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > 14400) {
            $this->logout();
        }
    }
    
    protected function setFlash($type, $message) {
        $_SESSION['admin_flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    protected function redirect($url) {
        header("Location: /chamaservico{$url}");
        exit;
    }
    
    protected function renderView($view, $data = []) {
        extract($data);
        
        // Verificar se o arquivo de view existe
        $viewPath = "views/admin/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Mostrar erro 404 personalizado para admin
            http_response_code(404);
            echo "<!DOCTYPE html>
            <html lang='pt-BR'>
            <head>
                <meta charset='UTF-8'>
                <title>View não encontrada - Admin</title>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
            </head>
            <body class='bg-light'>
                <div class='container mt-5'>
                    <div class='card'>
                        <div class='card-body text-center'>
                            <h1 class='display-4 text-danger'>404</h1>
                            <h3>View não encontrada</h3>
                            <p>A view <code>{$view}.php</code> não foi encontrada.</p>
                            <a href='/chamaservico/admin/dashboard' class='btn btn-primary'>Voltar ao Dashboard</a>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
    
    protected function verificarCSRF() {
        if (!isset($_POST['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
    }
    
    private function logout() {
        session_unset();
        session_destroy();
        header('Location: /chamaservico/admin/login');
        exit;
    }
}
?>
