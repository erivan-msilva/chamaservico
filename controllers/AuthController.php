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
            $email = trim($_POST['email'] ?? '');
            if (empty($email)) {
                Session::setFlash('error', 'Informe o e-mail cadastrado!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }

            // Buscar usuário pelo e-mail
            require_once 'models/Pessoa.php';
            $pessoaModel = new Pessoa();
            $usuario = $pessoaModel->buscarPorEmail($email);

            if (!$usuario) {
                Session::setFlash('error', 'E-mail não encontrado!', 'danger');
                header('Location: /chamaservico/redefinir-senha');
                exit;
            }

            // Gerar token de redefinição (simples, para exemplo)
            $token = bin2hex(random_bytes(32));
            // Salve o token no banco (implemente no model se necessário)
            $pessoaModel->salvarTokenRedefinicao($usuario['id'], $token);

            // Envie o e-mail com o link de redefinição (implemente envio real)
            // $link = BASE_URL . "redefinir-senha-nova?token=$token";
            // mail($email, "Redefinição de senha", "Clique no link para redefinir: $link");

            Session::setFlash('success', 'Instruções enviadas para seu e-mail!', 'success');
            header('Location: /chamaservico/login');
            exit;
        }

        include 'views/auth/redefini.php';
    }

    public function redefinirSenhaNova()
    {
        $token = $_GET['token'] ?? '';
        require_once 'models/Pessoa.php';
        $pessoaModel = new Pessoa();
        $usuario = $pessoaModel->buscarPorTokenRedefinicao($token);

        if (!$usuario) {
            Session::setFlash('error', 'Token inválido ou expirado!', 'danger');
            header('Location: /chamaservico/redefinir-senha');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';
            if (empty($novaSenha) || strlen($novaSenha) < 6) {
                Session::setFlash('error', 'A senha deve ter pelo menos 6 caracteres!', 'danger');
            } elseif ($novaSenha !== $confirmarSenha) {
                Session::setFlash('error', 'As senhas não coincidem!', 'danger');
            } else {
                $pessoaModel->alterarSenha($usuario['id'], $novaSenha);
                $pessoaModel->removerTokenRedefinicao($usuario['id']);
                Session::setFlash('success', 'Senha redefinida com sucesso!', 'success');
                header('Location: /chamaservico/login');
                exit;
            }
        }

        include 'views/auth/redefinir_nova.php';
    }
}
?>
