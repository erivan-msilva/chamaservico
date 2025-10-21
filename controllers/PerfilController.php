<?php
require_once 'models/Perfil.php';
require_once 'config/session.php';

class PerfilController
{
    // Altere para protected para permitir acesso na classe filha
    protected $model;

    public function __construct()
    {
        $this->model = new Perfil();
        Session::requireLogin(); // Permite acesso para ambos
    }

    public function index() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);

        if (!$usuario) {
            Session::setFlash('error', 'Perfil não encontrado!', 'danger');
            header('Location: ' . url('logout'));
            exit;
        }

        // Verifica tipo de usuário e inclui a view correta
        if (Session::isPrestador()) {
            include 'views/prestador/perfil/visualizar.php';
        } else {
            include 'views/cliente/perfil/visualizar.php';
        }
    }

    public function editar()
    {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);

        if (!$usuario) {
            Session::setFlash('error', 'Usuário não encontrado!', 'danger');
            header('Location: ' . url('logout'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: ' . url('perfil/editar'));
                exit;
            }

            $acao = $_POST['acao'] ?? '';

            switch ($acao) {
                case 'dados_pessoais':
                    $this->atualizarDadosPessoais($userId, $usuario);
                    break;
                case 'alterar_senha':
                    $this->alterarSenha($userId);
                    break;
                case 'upload_foto':
                    $this->uploadFotoPerfil($userId);
                    break;
                default:
                    Session::setFlash('error', 'Ação inválida!', 'danger');
            }

            header('Location: ' . url('perfil/editar'));
            exit;
        }

        // CORREÇÃO: Incluir a view correta conforme o tipo de usuário
        if (Session::isPrestador()) {
            include 'views/prestador/perfil/editar.php';
        } else {
            include 'views/cliente/perfil/editar.php';
        }
    }

    // Método enderecos removido por duplicidade.

    protected function atualizarDadosPessoais($userId, $usuarioAtual)
    {
        $dados = [
            'nome' => trim($_POST['nome']),
            'email' => trim($_POST['email']),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'cpf' => null,
            'dt_nascimento' => null
        ];

        // Só atualizar CPF se estiver vazio no banco
        if (empty($usuarioAtual['cpf']) && !empty($_POST['cpf'])) {
            $dados['cpf'] = preg_replace('/\D/', '', $_POST['cpf']);
        } else {
            $dados['cpf'] = $usuarioAtual['cpf'];
        }

        // Só atualizar data de nascimento se estiver vazia no banco
        if (empty($usuarioAtual['dt_nascimento']) && !empty($_POST['dt_nascimento'])) {
            $dados['dt_nascimento'] = $_POST['dt_nascimento'];
        } else {
            $dados['dt_nascimento'] = $usuarioAtual['dt_nascimento'];
        }

        if ($this->model->atualizar($userId, $dados)) {
            Session::setFlash('success', 'Dados atualizados com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao atualizar dados!', 'danger');
        }
    }

    private function alterarSenha($userId)
    {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Validações
        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            Session::setFlash('error', 'Todos os campos são obrigatórios!', 'danger');
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            Session::setFlash('error', 'As senhas não coincidem!', 'danger');
            return;
        }

        if (strlen($novaSenha) < 6) {
            Session::setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres!', 'danger');
            return;
        }

        // Verificar senha atual
        if (!$this->model->verificarSenha($userId, $senhaAtual)) {
            Session::setFlash('error', 'Senha atual incorreta!', 'danger');
            return;
        }

        // Alterar senha
        $dados = ['senha' => password_hash($novaSenha, PASSWORD_DEFAULT)];
        if ($this->model->atualizar($userId, $dados)) {
            Session::setFlash('success', 'Senha alterada com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao alterar senha!', 'danger');
        }
    }

    // Altere de private para protected para permitir acesso nas classes filhas
    protected function uploadFotoPerfil($userId)
    {
        if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Erro no upload da imagem!', 'danger');
            return;
        }

        $arquivo = $_FILES['foto_perfil'];
        $uploadDir = 'uploads/perfil/';

        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validações
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($arquivo['type'], $allowedTypes)) {
            Session::setFlash('error', 'Formato de imagem não permitido! Use JPG ou PNG.', 'danger');
            return;
        }

        if ($arquivo['size'] > $maxFileSize) {
            Session::setFlash('error', 'Imagem muito grande! Máximo 2MB.', 'danger');
            return;
        }

        // Gerar nome único
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'perfil_' . $userId . '_' . time() . '.' . $extensao;
        $caminhoCompleto = $uploadDir . $nomeArquivo;

        // Fazer upload
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            // Remover foto anterior se existir
            $usuario = $this->model->buscarPorId($userId);
            if ($usuario['foto_perfil'] && file_exists($uploadDir . basename($usuario['foto_perfil']))) {
                unlink($uploadDir . basename($usuario['foto_perfil']));
            }

            // Atualizar no banco - salvar apenas o nome do arquivo
            if ($this->model->atualizarFotoPerfil($userId, $nomeArquivo)) {
                Session::setFlash('success', 'Foto atualizada com sucesso!', 'success');
                // Atualizar sessão
                Session::set('foto_perfil', $nomeArquivo);
            } else {
                Session::setFlash('error', 'Erro ao salvar foto no banco!', 'danger');
                // Remover arquivo se erro no banco
                unlink($caminhoCompleto);
            }
        } else {
            Session::setFlash('error', 'Erro ao fazer upload da imagem!', 'danger');
        }
    }

    public function enderecos()
    {
        $userId = Session::getUserId();

        // Use a model Endereco
        require_once 'models/Endereco.php';
        $enderecoModel = new Endereco();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');

                // Para requisições AJAX, retornar JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Token de segurança inválido!']);
                    exit;
                }

                header('Location: ' . url('perfil/enderecos'));
                exit;
            }

            $acao = $_POST['acao'] ?? 'adicionar';

            switch ($acao) {
                case 'editar':
                    $this->editarEndereco($userId, $enderecoModel);
                    break;
                case 'excluir':
                    $this->excluirEndereco($userId, $enderecoModel);
                    break;
                case 'definir_principal':
                    $this->definirEnderecoPrincipal($userId, $enderecoModel);
                    break;
                default:
                    $this->processarAdicionarEndereco($userId, $enderecoModel);
                    break;
            }
        }

        // Buscar endereços apenas para exibição (GET)
        $enderecos = $enderecoModel->buscarPorPessoa($userId);
        include 'views/cliente/perfil/enderecos.php';
    }

    // Adicione este método privado para validação dos dados do endereço
    private function validarDadosEndereco($dados) {
        $required = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                return "Campo '{$field}' é obrigatório";
            }
        }
        if (strlen($dados['estado']) !== 2) {
            return 'Estado deve ter 2 caracteres';
        }
        return null;
    }

    private function processarAdicionarEndereco($userId, $enderecoModel)
    {
        $dados = [
            'pessoa_id' => $userId,
            'cep' => preg_replace('/\D/', '', $_POST['cep']),
            'logradouro' => trim($_POST['logradouro']),
            'numero' => trim($_POST['numero']),
            'complemento' => trim($_POST['complemento'] ?? ''),
            'bairro' => trim($_POST['bairro']),
            'cidade' => trim($_POST['cidade']),
            'estado' => trim($_POST['estado']),
            'principal' => isset($_POST['principal']) ? 1 : 0
        ];

        $erroValidacao = $this->validarDadosEndereco($dados);
        if ($erroValidacao) {
            $message = $erroValidacao;
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
            return;
        }

        $enderecoId = $enderecoModel->criar($dados);

        if ($enderecoId) {
            $message = 'Endereço adicionado com sucesso!';
            $novoEndereco = $enderecoModel->buscarPorId($enderecoId);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => $message,
                    'endereco' => $novoEndereco
                ]);
                exit;
            }
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao adicionar endereço!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
        }

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('perfil/enderecos'));
            exit;
        }
    }

    private function editarEndereco($userId, $enderecoModel)
    {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        $dados = [
            'cep' => preg_replace('/\D/', '', $_POST['cep']),
            'logradouro' => trim($_POST['logradouro']),
            'numero' => trim($_POST['numero']),
            'complemento' => trim($_POST['complemento'] ?? ''),
            'bairro' => trim($_POST['bairro']),
            'cidade' => trim($_POST['cidade']),
            'estado' => trim($_POST['estado']),
            'principal' => isset($_POST['principal']) ? 1 : 0
        ];

        $erroValidacao = $this->validarDadosEndereco($dados);
        if ($erroValidacao) {
            $message = $erroValidacao;
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
            return;
        }

        $result = $enderecoModel->atualizar($enderecoId, $dados);

        if ($result) {
            $message = 'Endereço atualizado com sucesso!';
            $enderecoAtualizado = $enderecoModel->buscarPorId($enderecoId);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => $message,
                    'endereco' => $enderecoAtualizado
                ]);
                exit;
            }
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao atualizar endereço!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
        }

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('perfil/enderecos'));
            exit;
        }
    }

    private function excluirEndereco($userId, $enderecoModel)
    {
        $enderecoId = $_POST['endereco_id'] ?? 0;

        $result = $enderecoModel->deletar($enderecoId);

        if ($result) {
            $message = 'Endereço removido com sucesso!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => true, 'mensagem' => $message, 'endereco_id' => $enderecoId]);
                exit;
            }
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao remover endereço!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
        }

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('perfil/enderecos'));
            exit;
        }
    }

    private function definirEnderecoPrincipal($userId, $enderecoModel)
    {
        $enderecoId = $_POST['endereco_id'] ?? 0;

        $result = $enderecoModel->definirComoPrincipal($enderecoId, $userId);

        if ($result) {
            $message = 'Endereço principal definido com sucesso!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $enderecos = $enderecoModel->buscarPorPessoa($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => $message,
                    'enderecos' => $enderecos
                ]);
                exit;
            }
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao definir endereço principal!';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $message]);
                exit;
            }
            Session::setFlash('error', $message, 'danger');
        }

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('perfil/enderecos'));
            exit;
        }
    }

    /**
     * API para buscar endereço pelo CEP usando ViaCEP
     */
    public function buscarCep()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $cep = $_GET['cep'] ?? '';
            $cep_clean = preg_replace('/\D/', '', $cep);

            if (strlen($cep_clean) !== 8) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'CEP inválido. Informe 8 dígitos.'
                ]);
                exit;
            }

            $url = "https://viacep.com.br/ws/{$cep_clean}/json/";

            // Tentar file_get_contents primeiro
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header' => 'User-Agent: ChamaServico/1.0'
                ]
            ]);
            $response = @file_get_contents($url, false, $context);

            // Fallback para cURL se file_get_contents falhar
            if ($response === false && function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ChamaServico/1.0');
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($response === false) {
                    throw new Exception("Erro cURL: {$curlError}");
                }
            }

            if ($response === false || empty($response)) {
                throw new Exception('Não foi possível conectar ao serviço de CEP.');
            }

            $data = json_decode($response, true);

            if (!$data || isset($data['erro'])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'CEP não encontrado.'
                ]);
                exit;
            }

            $endereco = [
                'cep' => $cep_clean,
                'logradouro' => $data['logradouro'] ?? '',
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['localidade'] ?? '',
                'estado' => $data['uf'] ?? ''
            ];

            echo json_encode([
                'success' => true,
                'endereco' => $endereco,
                'message' => 'CEP encontrado com sucesso!'
            ]);
            exit;

        } catch (Exception $e) {
            error_log("PerfilController::buscarCep - Erro: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar CEP: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
?>


   