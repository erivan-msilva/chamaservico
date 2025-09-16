<?php
require_once 'models/Pessoa.php';
require_once 'config/session.php';

class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new Pessoa();
    }

    public function login()
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            exit;
        }

        $title = 'Login - ChamaServiço';
        include 'views/auth/login.php';
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];

            // Validações básicas
            if (empty($email) || empty($senha)) {
                Session::setFlash('error', 'Preencha todos os campos!', 'danger');
                header('Location: ' . url('login'));
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'E-mail inválido!', 'danger');
                header('Location: ' . url('login'));
                exit;
            }

            try {
                // Primeiro, tentar login como usuário normal
                $pessoa = $this->model->verificarSenha($email, $senha);

                if ($pessoa) {
                    if (!$pessoa['ativo']) {
                        Session::setFlash('error', 'Sua conta está desativada.', 'danger');
                        header('Location: ' . url('login'));
                        exit;
                    }

                    $this->model->atualizarUltimoAcesso($pessoa['id']);
                    Session::login($pessoa['id'], $pessoa['nome'], $pessoa['email'], $pessoa['tipo']);

                    if (!empty($pessoa['foto_perfil'])) {
                        Session::set('foto_perfil', basename($pessoa['foto_perfil']));
                    }

                    Session::setFlash('success', 'Login realizado com sucesso!', 'success');
                    $this->redirectToDashboard();
                    exit;
                }

                // Se não encontrou como usuário normal, tentar como admin
                $admin = $this->verificarLoginAdmin($email, $senha);
                if ($admin) {
                    Session::loginAdmin($admin['id'], $admin['nome'], $admin['email'], $admin['nivel']);
                    header('Location: /admin/dashboard');  // CORRIGIDO: URL absoluta
                    exit;
                }

                Session::setFlash('error', 'Email ou senha incorretos!', 'danger');
                header('Location: ' . url('login'));
                exit;

            } catch (Exception $e) {
                error_log("Erro no login: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno do sistema. Tente novamente.', 'danger');
                header('Location: ' . url('login'));
                exit;
            }
        }

        header('Location: ' . url('login'));
        exit;
    }

    private function verificarLoginAdmin($email, $senha)
    {
        try {
            // Usar Database diretamente
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            error_log("Verificando login admin para email: $email");
            
            // Buscar na tabela tb_usuario com todos os campos necessários
            $sql = "SELECT id, nome, email, senha, nivel, ativo, ultimo_acesso FROM tb_usuario WHERE email = ? AND ativo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin) {
                error_log("Admin encontrado: " . $admin['email'] . " (Nível: " . $admin['nivel'] . ")");
                
                if (password_verify($senha, $admin['senha'])) {
                    error_log("✅ Senha correta para admin: " . $admin['email']);
                    return $admin;
                } else {
                    error_log("❌ Senha incorreta para admin: " . $admin['email']);
                }
            } else {
                error_log("❌ Admin não encontrado para email: $email");
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro no login admin: " . $e->getMessage());
            return false;
        }
    }

    private function redirectToDashboard()
    {
        $userType = Session::getUserType();
        
        switch ($userType) {
            case 'prestador':
                header('Location: ' . url('prestador/dashboard'));
                break;
            case 'ambos':
                header('Location: ' . url('cliente/dashboard'));
                break;
            default:
                header('Location: ' . url('cliente/dashboard'));
                break;
        }
    }

    public function logout()
    {
        Session::logout();
        Session::setFlash('success', 'Logout realizado com sucesso!', 'success');
        header('Location: ' . url('login'));
        exit;
    }

    public function registro()
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            exit;
        }

        $title = 'Cadastro - ChamaServiço';
        include 'views/auth/registro.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];
            $senhaConfirmar = $_POST['senha_confirmar'];
            $tipo = $_POST['tipo'] ?? 'cliente';

            $erros = [];

            if (empty($nome)) $erros[] = 'O nome é obrigatório';
            if (empty($email)) {
                $erros[] = 'O email é obrigatório';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'Email inválido';
            } elseif ($this->model->emailExiste($email)) {
                $erros[] = 'Este email já está cadastrado';
            }

            if (empty($senha)) {
                $erros[] = 'A senha é obrigatória';
            } elseif (strlen($senha) < 6) {
                $erros[] = 'A senha deve ter pelo menos 6 caracteres';
            } elseif ($senha !== $senhaConfirmar) {
                $erros[] = 'As senhas não coincidem';
            }

            if (!in_array($tipo, ['cliente', 'prestador', 'ambos'])) {
                $erros[] = 'Tipo de usuário inválido';
            }

            if (!empty($erros)) {
                Session::setFlash('error', implode('<br>', $erros), 'danger');
                header('Location: ' . url('registro'));
                exit;
            }

            $dados = [
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha,
                'tipo' => $tipo
            ];

            $pessoaId = $this->model->criar($dados);

            if ($pessoaId) {
                Session::setFlash('success', 'Cadastro realizado com sucesso! Agora você pode fazer login.', 'success');
                header('Location: ' . url('login'));
                exit;
            } else {
                Session::setFlash('error', 'Erro ao cadastrar. Tente novamente.', 'danger');
                header('Location: ' . url('registro'));
                exit;
            }
        }

        header('Location: ' . url('registro'));
        exit;
    }

    public function redefinirSenha()
    {
        // Se já estiver logado, redirecionar
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                Session::setFlash('error', 'E-mail é obrigatório!', 'danger');
                header('Location: ' . url('redefinir-senha'));
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'E-mail inválido!', 'danger');
                header('Location: ' . url('redefinir-senha'));
                exit;
            }
            
            // Verificar se o e-mail existe
            if (!$this->model->emailExiste($email)) {
                // Por segurança, não revelar se o email existe ou não
                Session::setFlash('success', 'Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.', 'info');
                header('Location: ' . url('login'));
                exit;
            }
            
            // Gerar token de redefinição
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            if ($this->model->criarTokenRedefinicao($email, $token, $expiracao)) {
                // Em produção, aqui você enviaria o e-mail
                // Por enquanto, vamos simular o envio
                $linkRedefinicao = url('redefinir-senha-nova?token=' . $token);
                
                Session::setFlash('success', 'Link de redefinição enviado para seu e-mail! (Simulado: ' . $linkRedefinicao . ')', 'success');
                header('Location: ' . url('login'));
                exit;
            } else {
                Session::setFlash('error', 'Erro ao gerar link de redefinição!', 'danger');
            }
        }
        
        $title = 'Redefinir Senha - ChamaServiço';
        include 'views/auth/redefinir_senha.php';
    }

    public function redefinirSenhaNova()
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        
        if (empty($token)) {
            Session::setFlash('error', 'Token inválido!', 'danger');
            header('Location: ' . url('login'));
            exit;
        }
        
        // Verificar se o token é válido
        $usuario = $this->model->verificarTokenRedefinicao($token);
        
        if (!$usuario) {
            Session::setFlash('error', 'Token inválido ou expirado!', 'danger');
            header('Location: ' . url('login'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: ' . url('redefinir-senha-nova?token=' . $token));
                exit;
            }

            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';
            
            if (empty($novaSenha) || empty($confirmarSenha)) {
                Session::setFlash('error', 'Todos os campos são obrigatórios!', 'danger');
                header('Location: ' . url('redefinir-senha-nova?token=' . $token));
                exit;
            }
            
            if ($novaSenha !== $confirmarSenha) {
                Session::setFlash('error', 'As senhas não coincidem!', 'danger');
                header('Location: ' . url('redefinir-senha-nova?token=' . $token));
                exit;
            }
            
            if (strlen($novaSenha) < 6) {
                Session::setFlash('error', 'A senha deve ter pelo menos 6 caracteres!', 'danger');
                header('Location: ' . url('redefinir-senha-nova?token=' . $token));
                exit;
            }
            
            // Atualizar senha e limpar token
            if ($this->model->atualizarSenhaComToken($token, $novaSenha)) {
                Session::setFlash('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.', 'success');
                header('Location: ' . url('login'));
                exit;
            } else {
                Session::setFlash('error', 'Erro ao redefinir senha!', 'danger');
            }
        }
        
        $title = 'Nova Senha - ChamaServiço';
        include 'views/auth/redefinir_nova.php';
    }
}
?>
?>
