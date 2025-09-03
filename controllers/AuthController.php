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
            header('Location: /chamaservico/');
            exit;
        }

        include 'views/auth/login.php';
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];

            $pessoa = $this->model->verificarSenha($email, $senha);

            if ($pessoa) {
                // Verificar se está ativo
                if (!$pessoa['ativo']) {
                    Session::setFlash('error', 'Sua conta está desativada. Entre em contato com o suporte.', 'danger');
                    header('Location: /chamaservico/login');
                    exit;
                }

                // Atualizar último acesso
                $this->model->atualizarUltimoAcesso($pessoa['id']);

                // CORRIGIDO: Usar o método login implementado
                Session::login($pessoa['id'], $pessoa['nome'], $pessoa['email'], $pessoa['tipo']);

                // Adicionar outros dados à sessão
                if (!empty($pessoa['foto_perfil'])) {
                    // Garantir que apenas o nome do arquivo seja salvo na sessão
                    Session::set('foto_perfil', basename($pessoa['foto_perfil']));
                }

                // Redirecionar conforme o tipo de usuário
                if ($pessoa['tipo'] === 'prestador') {
                    header('Location: /chamaservico/prestador/dashboard');
                } elseif ($pessoa['tipo'] === 'cliente') {
                    header('Location: /chamaservico/cliente/dashboard');
                } else {
                    header('Location: /chamaservico/');
                }
                exit;
            } else {
                Session::setFlash('error', 'Email ou senha incorretos!', 'danger');
                header('Location: /chamaservico/login');
                exit;
            }
        }

        header('Location: /chamaservico/login');
        exit;
    }

    public function logout()
    {
        // CORRIGIDO: Usar o método logout implementado
        Session::logout();
        header('Location: /chamaservico/login');
        exit;
    }

    public function registro()
    {
        if (Session::isLoggedIn()) {
            header('Location: /chamaservico/');
            exit;
        }

        include 'views/auth/registro.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];
            // Corrigido: nome do campo igual ao formulário
            $senhaConfirmar = $_POST['senha_confirmar'];
            $tipo = $_POST['tipo'] ?? 'cliente';

            // Validações
            $erros = [];

            if (empty($nome)) {
                $erros[] = 'O nome é obrigatório';
            }

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

            // Se houver erros, redirecionar de volta com mensagens
            if (!empty($erros)) {
                Session::setFlash('error', implode('<br>', $erros), 'danger');
                header('Location: /chamaservico/registro');
                exit;
            }

            // Criar o usuário
            $dados = [
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha,
                'tipo' => $tipo
            ];

            $pessoaId = $this->model->criar($dados);

            if ($pessoaId) {
                Session::setFlash('success', 'Cadastro realizado com sucesso! Agora você pode fazer login.', 'success');
                header('Location: /chamaservico/login');
                exit;
            } else {
                Session::setFlash('error', 'Erro ao cadastrar. Tente novamente.', 'danger');
                header('Location: /chamaservico/registro');
                exit;
            }
        }

        header('Location: /chamaservico/registro');
        exit;
    }

    public function redefinirSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }

            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                Session::setFlash('error', 'Informe o e-mail cadastrado!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'E-mail inválido!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }

            try {
                // Buscar usuário pelo e-mail
                $usuario = $this->model->buscarPorEmail($email);

                if (!$usuario) {
                    // Por segurança, sempre mostramos sucesso mesmo se email não existir
                    Session::setFlash('success', 'Se o e-mail estiver cadastrado, você receberá as instruções em alguns minutos.', 'info');
                    header('Location: /chamaservico/redefinir-senha');
                    exit;
                }

                // Gerar token seguro
                $token = bin2hex(random_bytes(32));
                $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Salvar token no banco
                if ($this->model->salvarTokenRedefinicao($usuario['id'], $token, $expiracao)) {
                    // Tentar enviar e-mail usando o EmailService
                    require_once 'core/EmailService.php';
                    $emailService = new EmailService();
                    
                    if ($emailService->enviarEmailRedefinicao($email, $usuario['nome'], $token)) {
                        Session::setFlash('success', '📧 Instruções de redefinição enviadas para seu email! Verifique sua caixa de entrada.', 'success');
                    } else {
                        // Fallback: mostrar link direto em desenvolvimento
                        if (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento') {
                            $linkRedefinicao = "http://localhost:8083/chamaservico/redefinir-senha-nova?token=" . $token;
                            Session::setFlash('warning', 
                                "⚠️ Problema no envio do email. <strong>Link temporário para desenvolvimento:</strong><br><a href='$linkRedefinicao' target='_blank' class='btn btn-sm btn-outline-primary mt-2'>🔑 Redefinir Senha</a>", 
                                'warning'
                            );
                        } else {
                            Session::setFlash('error', 'Erro temporário no sistema de e-mail. Tente novamente em alguns minutos.', 'danger');
                        }
                    }
                } else {
                    Session::setFlash('error', 'Erro interno. Tente novamente.', 'danger');
                }

            } catch (Exception $e) {
                error_log("Erro na redefinição de senha: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno no sistema. Tente novamente.', 'danger');
            }

            header('Location: /chamaservico/redefinir-senha');
            exit;
        }

        $title = 'Redefinir Senha - ChamaServiço';
        include 'views/auth/redefini.php';
    }

    public function redefinirSenhaNova()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            Session::setFlash('error', 'Link inválido ou expirado!', 'danger');
            header('Location: /chamaservico/redefinir-senha');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';
            
            // Validações
            if (empty($novaSenha) || empty($confirmarSenha)) {
                Session::setFlash('error', 'Preencha todos os campos!', 'danger');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            if (strlen($novaSenha) < 6) {
                Session::setFlash('error', 'A senha deve ter pelo menos 6 caracteres!', 'danger');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            if ($novaSenha !== $confirmarSenha) {
                Session::setFlash('error', 'As senhas não coincidem!', 'danger');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            try {
                if ($this->model->redefinirSenhaComToken($token, $novaSenha)) {
                    // Enviar email de confirmação
                    $this->enviarEmailConfirmacaoRedefinicao($token);
                    
                    Session::setFlash('success', '✅ Senha redefinida com sucesso! Faça login com sua nova senha.', 'success');
                    header('Location: /chamaservico/login');
                    exit;
                } else {
                    Session::setFlash('error', 'Link inválido ou expirado!', 'danger');
                    header('Location: /chamaservico/redefinir-senha');
                    exit;
                }
            } catch (Exception $e) {
                error_log("Erro ao redefinir senha: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno. Tente novamente.', 'danger');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }
        }

        // Verificar se o token é válido antes de mostrar o formulário
        try {
            $usuario = $this->model->verificarTokenRedefinicao($token);
            if (!$usuario) {
                Session::setFlash('error', 'Link inválido ou expirado!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao verificar token!', 'danger');
            header('Location: /chamaservico/redefinir-senha');
            exit;
        }

        $title = 'Nova Senha - ChamaServiço';
        include 'views/auth/redefinir_nova.php';
    }

    private function verificarLimiteTentativas($userId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM tb_tentativa_redefinicao 
                    WHERE usuario_id = ? AND data_tentativa > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() >= 3; // Máximo 3 tentativas em 15 minutos
        } catch (Exception $e) {
            return false;
        }
    }

    private function registrarTentativaRedefinicao($userId)
    {
        try {
            $sql = "INSERT INTO tb_tentativa_redefinicao (usuario_id, data_tentativa, ip_address) 
                    VALUES (?, NOW(), ?)";
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (Exception $e) {
            error_log("Erro ao registrar tentativa: " . $e->getMessage());
        }
    }

    private function enviarEmailConfirmacaoRedefinicao($token)
    {
        try {
            $usuario = $this->model->verificarTokenRedefinicao($token);
            if ($usuario) {
                require_once 'core/EmailService.php';
                $emailService = new EmailService();
                $emailService->enviarEmailConfirmacaoRedefinicao($usuario['email'], $usuario['nome']);
            }
        } catch (Exception $e) {
            error_log("Erro ao enviar confirmação: " . $e->getMessage());
        }
    }

    // ...existing code...
}
?>
             