<?php
// Controlador responsável APENAS pela autenticação de administradores

class AuthAdminController
{
    private $db;

    public function __construct()
    {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
        }
    }

    public function login()
    {
        // Se já estiver logado, redirecionar para dashboard
        if ($this->isAdminLoggedIn()) {
            header('Location: ' . url('admin/dashboard'));
            exit;
        }

        // Exibir página de login
        include 'views/admin/login.php';
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/login'));
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
            // Verificar credenciais na tabela tb_usuario
            $sql = "SELECT * FROM tb_usuario WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($senha, $admin['senha'])) {
                // Login bem-sucedido
                $this->createAdminSession($admin);
                $this->updateLastAccess($admin['id']);

                // Redirecionar para dashboard (AdminController)
                header('Location: ' . url('admin/dashboard'));
                exit;
            } else {
                $erro = 'Email ou senha incorretos!';
            }
        } catch (Exception $e) {
            $erro = 'Erro interno do sistema. Tente novamente.';
            error_log('Erro no login admin: ' . $e->getMessage());
        }

        // Em caso de erro, mostrar página de login com erro
        include 'views/admin/login.php';
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpar sessão administrativa
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nome']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_nivel']);
        unset($_SESSION['admin_login_time']);

        // Redirecionar para login com mensagem
        header('Location: ' . url('admin/login?logout=1'));
        exit;
    }

    private function isAdminLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']);
    }

    private function createAdminSession($admin)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_nivel'] = $admin['nivel'];
        $_SESSION['admin_login_time'] = time();

        error_log("Sessão admin criada para: " . $admin['email']);
    }

    private function updateLastAccess($adminId)
    {
        $sql = "UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
    }
}
?>
