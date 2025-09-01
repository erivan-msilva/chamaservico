<?php
require_once 'controllers/admin/BaseAdminController.php';

class AuthAdminController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        if ($this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/dashboard');
            exit;
        }
        
        header('Location: /chamaservico/admin/login');
        exit;
    }
    
    public function login() {
        if ($this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/dashboard');
            exit;
        }
        
        include 'views/admin/login.php';
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            $erro = 'Email e senha são obrigatórios!';
            include 'views/admin/login.php';
            return;
        }
        
        try {
            $sql = "SELECT * FROM tb_usuario WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($senha, $admin['senha'])) {
                $this->createAdminSession($admin);
                
                // Atualizar último acesso
                $this->updateLastAccess($admin['id']);
                
                header('Location: /chamaservico/admin/dashboard');
                exit;
            } else {
                $erro = 'Email ou senha incorretos!';
            }
        } catch (Exception $e) {
            $erro = 'Erro interno do sistema. Tente novamente.';
            error_log('Erro no login admin: ' . $e->getMessage());
        }
        
        include 'views/admin/login.php';
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nome']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_nivel']);
        unset($_SESSION['admin_login_time']);
        
        header('Location: /chamaservico/admin/login?logout=1');
        exit;
    }
    
    private function isAdminLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']);
    }
    
    private function createAdminSession($admin) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_nivel'] = $admin['nivel'];
        $_SESSION['admin_login_time'] = time();
    }
    
    private function updateLastAccess($adminId) {
        $sql = "UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
    }
}
?>
