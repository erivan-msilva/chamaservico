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

                // Iniciar sessão
                Session::login($pessoa['id'], $pessoa['nome'], $pessoa['email'], $pessoa['tipo']);

                // Adicionar outros dados à sessão
                if (!empty($pessoa['foto_perfil'])) {
                    // Garantir que apenas o nome do arquivo seja salvo na sessão
                    Session::set('foto_perfil', basename($pessoa['foto_perfil']));
                }

                // Redirecionar conforme o tipo de usuário
                if ($pessoa['tipo'] === 'prestador') {
                    header('Location: /chamaservico/prestador/dashboard');
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
            $email = $_POST['email'] ?? '';

            require_once 'models/Pessoa.php';
            $pessoaModel = new Pessoa();
            $usuario = $pessoaModel->buscarPorEmail($email);

            if ($usuario) {
                $token = bin2hex(random_bytes(32));
                $pessoaModel->salvarTokenRedefinicao($usuario['id'], $token);

                // Corpo do e-mail
                $link = "http://localhost:8083/chamaservico/redefinir-senha-nova?token=$token";
                $assunto = 'Redefinição de Senha - ChamaServiço';
                $corpo = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                color: #333;
                                background-color: #f9f9f9;
                                padding: 20px;
                            }
                            .email-container {
                                max-width: 600px;
                                margin: 0 auto;
                                background: #ffffff;
                                border: 1px solid #ddd;
                                border-radius: 8px;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                overflow: hidden;
                            }
                            .email-header {
                                background: #283579;
                                color: #ffffff;
                                padding: 20px;
                                text-align: center;
                                font-size: 1.5rem;
                            }
                            .email-body {
                                padding: 20px;
                            }
                            .email-body p {
                                margin: 0 0 15px;
                            }
                            .email-footer {
                                background: #f5f5f5;
                                padding: 15px;
                                text-align: center;
                                font-size: 0.9rem;
                                color: #666;
                            }
                            .btn {
                                display: inline-block;
                                padding: 10px 20px;
                                background: #f5a522;
                                color: #ffffff;
                                text-decoration: none;
                                border-radius: 5px;
                                font-weight: bold;
                                margin-top: 15px;
                            }
                            .btn:hover {
                                background: #d48c00;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='email-container'>
                            <div class='email-header'>
                                Redefinição de Senha
                            </div>
                            <div class='email-body'>
                                <p>Olá, <strong>{$usuario['nome']}</strong>,</p>
                                <p>Recebemos uma solicitação para redefinir sua senha no <strong>ChamaServiço</strong>.</p>
                                <p>Para criar uma nova senha, clique no botão abaixo:</p>
                                <p style='text-align: center;'>
                                    <a href='$link' class='btn'>Redefinir Senha</a>
                                </p>
                                <p>Se você não solicitou a redefinição, ignore este e-mail. Sua senha permanecerá segura.</p>
                            </div>
                            <div class='email-footer'>
                                <p>Equipe ChamaServiço</p>
                                <p><a href='http://localhost:8083/chamaservico' style='color: #283579; text-decoration: none;'>www.chamaservico.com</a></p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                require_once 'core/Email.php';
                $emailService = new Email();
                if ($emailService->enviarEmail($email, $assunto, $corpo)) {
                    Session::setFlash('success', 'E-mail enviado com instruções para redefinir a senha.');
                } else {
                    Session::setFlash('error', 'Erro ao enviar e-mail. Tente novamente mais tarde.');
                }
            } else {
                Session::setFlash('error', 'E-mail não encontrado.');
            }

            header('Location: /chamaservico/redefinir-senha');
            exit;
        }

        require 'views/auth/redefini.php';
    }

    public function redefinirSenhaNova()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            // Validação básica
            if (empty($novaSenha) || empty($confirmarSenha)) {
                Session::setFlash('error', 'Preencha todos os campos.');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            if ($novaSenha !== $confirmarSenha) {
                Session::setFlash('error', 'As senhas não coincidem.');
                header("Location: /chamaservico/redefinir-senha-nova?token=$token");
                exit;
            }

            require_once 'models/Pessoa.php';
            $pessoaModel = new Pessoa();
            $usuario = $pessoaModel->buscarPorTokenRedefinicao($token);

            if ($usuario) {
                if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                    Session::setFlash('error', 'Token de segurança inválido!');
                    header('Location: /chamaservico/redefinir-senha-nova?token=' . urlencode($token));
                    exit;
                }

                $pessoaModel->alterarSenha($usuario['id'], $novaSenha);
                $pessoaModel->removerTokenRedefinicao($usuario['id']);
                Session::setFlash('success', 'Senha redefinida com sucesso!');
                header('Location: /chamaservico/login');
                exit;
            } else {
                Session::setFlash('error', 'Token inválido ou expirado.');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }
        }

        require 'views/auth/redefinir_nova.php';
    }
}
