<?php
require_once 'models/Pessoa.php';
require_once 'config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/EmailService.php';

class AuthController
{
    private $model;
    private $emailService;

    public function __construct()
    {
        $this->model = new Pessoa();
        $this->emailService = new EmailService();
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
                    header('Location: /admin/dashboard');  
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
            //  Usar Database diretamente em vez de $this->model->db
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = trim($_POST['email'] ?? '');
                
                error_log("🔄 Iniciando redefinição de senha para: $email");
                
                // Validações básicas
                if (empty($email)) {
                    throw new Exception('E-mail é obrigatório');
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('E-mail inválido');
                }
                
                // Verificar se o usuário existe
                if (!$this->model->emailExiste($email)) {
                    // Por segurança, mostrar mensagem genérica
                    error_log("❌ Email não encontrado: $email");
                } else {
                    error_log("✅ Email encontrado: $email");
                    
                    // Gerar token seguro
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    error_log("🔑 Token gerado: $token, expira em: $expiry");
                    
                    // Salvar token no banco
                    if ($this->model->criarTokenRedefinicao($email, $token, $expiry)) {
                        error_log("✅ Token salvo no banco");
                        
                        // Obter dados do usuário
                        $pessoa = $this->model->buscarPorEmail($email);
                        $nome = $pessoa['nome'] ?? 'Usuário';
                        
                        // Enviar e-mail
                        $enviado = $this->emailService->enviarEmailRedefinicao($email, $nome, $token);
                        
                        if ($enviado) {
                            error_log("✅ Email enviado para: $email - Link: " . BASE_URL . "/redefinir-nova?token=" . $token);
                        } else {
                            error_log("❌ Falha ao enviar email para: $email");
                        }
                    } else {
                        error_log("❌ Falha ao salvar token no banco para: $email");
                    }
                }
                
                // Sempre mostrar mensagem de sucesso por segurança
                Session::setFlash('success', 'Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.');
                header('Location: ' . url('esqueci-senha'));
                exit;
                
            } catch (Exception $e) {
                error_log("❌ Erro na redefinição: " . $e->getMessage());
                Session::setFlash('error', $e->getMessage());
                header('Location: ' . url('esqueci-senha'));
                exit;
            }
        }
        
        // GET - Mostrar formulário
        $title = 'Esqueci Minha Senha - ChamaServiço';
        include 'views/auth/redefinir_senha.php';
    }
    
    public function redefinirSenhaNova()
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        
        error_log("🔄 redefinirSenhaNova chamado com token: " . substr($token, 0, 10) . '...');
        
        if (empty($token)) {
            error_log("❌ Token vazio ou não fornecido");
            Session::setFlash('error', 'Token inválido para redefinição de senha.');
            header('Location: ' . url('login'));
            exit;
        }
        
        // Verificar se o token é válido
        $usuario = $this->model->verificarTokenRedefinicao($token);
        
        if (!$usuario) {
            error_log("❌ Token inválido ou expirado: " . substr($token, 0, 10) . '...');
            Session::setFlash('error', 'Token inválido ou expirado. Solicite um novo link de redefinição.');
            header('Location: ' . url('esqueci-senha'));
            exit;
        }
        
        error_log("✅ Token válido para usuário: " . $usuario['email']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $novaSenha = $_POST['nova_senha'] ?? '';
                $confirmarSenha = $_POST['confirmar_senha'] ?? '';
                
                error_log("🔄 POST recebido - nova senha length: " . strlen($novaSenha));
                
                // Validações
                if (empty($novaSenha) || empty($confirmarSenha)) {
                    throw new Exception('Todos os campos são obrigatórios');
                }
                
                if ($novaSenha !== $confirmarSenha) {
                    throw new Exception('As senhas não coincidem');
                }
                
                if (strlen($novaSenha) < 6) {
                    throw new Exception('A senha deve ter pelo menos 6 caracteres');
                }
                
                // Atualizar senha
                error_log("🔄 Tentando atualizar senha...");
                if ($this->model->atualizarSenhaComToken($token, $novaSenha)) {
                    error_log("✅ Senha atualizada com sucesso para: " . $usuario['email']);
                    
                    // Enviar email de confirmação (opcional)
                    try {
                        $this->emailService->enviarEmailConfirmacaoRedefinicao($usuario['email'], $usuario['nome']);
                    } catch (Exception $e) {
                        error_log("⚠️ Erro ao enviar email de confirmação: " . $e->getMessage());
                        // Não falhar por causa do email de confirmação
                    }
                    
                    Session::setFlash('success', 'Senha redefinida com sucesso! Agora você pode fazer login com sua nova senha.');
                    header('Location: ' . url('login'));
                    exit;
                } else {
                    error_log("❌ Falha ao atualizar senha para token: " . substr($token, 0, 10) . '...');
                    throw new Exception('Erro ao redefinir senha. Tente novamente.');
                }
                
            } catch (Exception $e) {
                error_log("❌ Erro no POST de redefinição: " . $e->getMessage());
                Session::setFlash('error', $e->getMessage());
                header('Location: ' . url('redefinir-nova?token=' . urlencode($token)));
                exit;
            }
        }
        
        // GET - Mostrar formulário
        $title = 'Nova Senha - ChamaServiço';
        include 'views/auth/redefinir_nova.php';
    }
}
?>

