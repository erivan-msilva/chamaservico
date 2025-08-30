<?php
require_once 'config/session.php';

class AdminController {
    
    public function index() {
        // Verificar se já está logado como admin
        if (isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/dashboard');
            exit;
        }
        
        // Redirecionar para login se não estiver logado
        header('Location: /chamaservico/admin/login');
        exit;
    }
    
    public function login() {
        // Se já está logado como admin, redirecionar para dashboard
        if (isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/dashboard');
            exit;
        }
        
        include 'views/admin/login.php';
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            // Verificar credenciais do administrador
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT * FROM tb_usuario WHERE email = ? AND ativo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($senha, $admin['senha'])) {
                // Login bem-sucedido
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nome'] = $admin['nome'];
                $_SESSION['admin_nivel'] = $admin['nivel'];
                $_SESSION['is_admin'] = true;
                
                // Atualizar último acesso
                $sqlUpdate = "UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = ?";
                $stmtUpdate = $db->prepare($sqlUpdate);
                $stmtUpdate->execute([$admin['id']]);
                
                header('Location: /chamaservico/admin/dashboard');
                exit;
            } else {
                // Login falhou
                Session::setFlash('error', 'E-mail ou senha inválidos!', 'danger');
                header('Location: /chamaservico/admin/login');
                exit;
            }
        }
    }
    
    public function dashboard() {
        // Verificar se está logado como admin
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // Buscar estatísticas para o dashboard
        $stats = $this->getDashboardStats();
        
        include 'views/admin/dashboard.php';
    }
    
    public function logout() {
        // Limpar sessão do admin
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nome']);
        unset($_SESSION['admin_nivel']);
        unset($_SESSION['is_admin']);
        
        Session::setFlash('success', 'Logout realizado com sucesso!', 'success');
        header('Location: /chamaservico/admin/login');
        exit;
    }
    
    private function getDashboardStats() {
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $stats = [];
            
            // Total de usuários
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa");
            $stmt->execute();
            $stats['total_usuarios'] = $stmt->fetchColumn();
            
            // Usuários ativos
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1");
            $stmt->execute();
            $stats['usuarios_ativos'] = $stmt->fetchColumn();
            
            // Total de clientes
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('cliente', 'ambos')");
            $stmt->execute();
            $stats['total_clientes'] = $stmt->fetchColumn();
            
            // Total de prestadores
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('prestador', 'ambos')");
            $stmt->execute();
            $stats['total_prestadores'] = $stmt->fetchColumn();
            
            // Total de admins
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_usuario WHERE ativo = 1");
            $stmt->execute();
            $stats['total_admins'] = $stmt->fetchColumn();
            
            // Solicitações hoje
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE DATE(data_solicitacao) = CURDATE()");
            $stmt->execute();
            $stats['solicitacoes_hoje'] = $stmt->fetchColumn();
            
            // Cadastros hoje
            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE DATE(data_cadastro) = CURDATE()");
            $stmt->execute();
            $stats['cadastros_hoje'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            return [
                'total_usuarios' => 0,
                'usuarios_ativos' => 0,
                'total_clientes' => 0,
                'total_prestadores' => 0,
                'total_admins' => 0,
                'solicitacoes_hoje' => 0,
                'cadastros_hoje' => 0
            ];
        }
    }
}
?>