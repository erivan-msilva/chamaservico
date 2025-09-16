<?php
require_once 'models/Perfil.php';
require_once 'config/session.php';

class ClientePerfilController {
    private $model;
    
    public function __construct() {
        $this->model = new Perfil();
        Session::requireClientLogin();
        
        if (!Session::isCliente()) {
            header('Location: ' . url('acesso-negado'));
            exit;
        }
    }
    
    public function index() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);
        $enderecos = $this->model->buscarEnderecosPorUsuario($userId);
        
        if (!$usuario) {
            Session::setFlash('error', 'Usuário não encontrado!', 'danger');
            header('Location: ' . url('logout'));
            exit;
        }
        
        include 'views/cliente/perfil/visualizar.php';
    }
    
    public function editar() {
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
                header('Location: ' . url('cliente/perfil/editar'));
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
            
            header('Location: ' . url('cliente/perfil/editar'));
            exit;
        }
        
        include 'views/cliente/perfil/editar.php';
    }
    
    public function enderecos() {
        Session::requireClientLogin();
        $userId = Session::getUserId();

        // API para listar endereços (usado pelo AJAX)
        if ($_GET['action'] ?? '' === 'api_list') {
            require_once 'models/ClientePerfil.php';
            $model = new ClientePerfil();
            $enderecos = $model->buscarEnderecos($userId);
            echo json_encode(['sucesso' => true, 'enderecos' => $enderecos]);
            exit;
        }

        // Cadastro via AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
            $dados = [
                'pessoa_id' => $userId,
                'cep' => $_POST['cep'] ?? '',
                'logradouro' => $_POST['logradouro'] ?? '',
                'numero' => $_POST['numero'] ?? '',
                'complemento' => $_POST['complemento'] ?? '',
                'bairro' => $_POST['bairro'] ?? '',
                'cidade' => $_POST['cidade'] ?? '',
                'estado' => $_POST['estado'] ?? '',
                'principal' => !empty($_POST['principal']) ? 1 : 0
            ];
            require_once 'models/ClientePerfil.php';
            $model = new ClientePerfil();
            $sucesso = $model->criarEndereco($dados);

            // Se for AJAX, retorna JSON e não redireciona
            if (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_POST['ajax']) && $_POST['ajax'] == '1')
            ) {
                if ($sucesso) {
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Endereço cadastrado com sucesso!']);
                } else {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar endereço!']);
                }
                exit;
            }

            // Fluxo normal (form padrão)
            if ($sucesso) {
                Session::setFlash('success', 'Endereço cadastrado com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao cadastrar endereço!', 'danger');
            }
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }

        // Exclusão via AJAX ou formulário
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
            $enderecoId = $_POST['endereco_id'] ?? 0;
            require_once 'models/ClientePerfil.php';
            $model = new ClientePerfil();

            // Não permitir excluir se for o único endereço principal
            $enderecos = $model->buscarEnderecos($userId);
            $principalCount = 0;
            foreach ($enderecos as $end) {
                if ($end['principal']) $principalCount++;
            }
            $endereco = null;
            foreach ($enderecos as $end) {
                if ($end['id'] == $enderecoId) $endereco = $end;
            }
            if ($endereco && $endereco['principal'] && $principalCount <= 1) {
                $msg = 'Não é possível excluir o único endereço principal.';
                if (
                    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                    || (isset($_POST['ajax']) && $_POST['ajax'] == '1')
                ) {
                    echo json_encode(['sucesso' => false, 'mensagem' => $msg]);
                    exit;
                }
                Session::setFlash('error', $msg, 'danger');
                header('Location: ' . url('cliente/perfil/enderecos'));
                exit;
            }

            $sucesso = $model->excluirEndereco($enderecoId, $userId);

            if (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_POST['ajax']) && $_POST['ajax'] == '1')
            ) {
                echo json_encode(['sucesso' => $sucesso]);
                exit;
            }

            if ($sucesso) {
                Session::setFlash('success', 'Endereço excluído com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao excluir endereço!', 'danger');
            }
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }

        // Incluir modelo necessário
        require_once 'models/Endereco.php';
        
        // Exibir página de endereços
        $modeloEndereco = new Endereco();
        $clienteId = Session::getUserId();
        $enderecos = $modeloEndereco->buscarPorPessoa($clienteId);
        
        // CORREÇÃO: Usar o arquivo correto na pasta cliente/perfil
        include 'views/cliente/perfil/enderecos.php';
    }
    
    private function apiListarEnderecos() {
        header('Content-Type: application/json');
        
        try {
            require_once 'models/Endereco.php';
            $modeloEndereco = new Endereco();
            $clienteId = Session::getUserId();
            
            $enderecos = $modeloEndereco->buscarPorPessoa($clienteId);
            
            echo json_encode([
                'sucesso' => true,
                'enderecos' => $enderecos
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao carregar endereços: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    private function apiObterEndereco() {
        header('Content-Type: application/json');
        
        try {
            require_once 'models/Endereco.php';
            $modeloEndereco = new Endereco();
            $clienteId = Session::getUserId();
            $enderecoId = $_GET['id'];
            
            $endereco = $modeloEndereco->buscarPorId($enderecoId);
            
            // Verificar se o endereço pertence ao cliente
            if (!$endereco || $endereco['pessoa_id'] != $clienteId) {
                throw new Exception('Endereço não encontrado');
            }
            
            echo json_encode($endereco);
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    private function processarAcaoEndereco() {
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Token de segurança inválido']);
                exit;
            }
            Session::setFlash('erro', 'Token de segurança inválido!', 'danger');
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }
        
        require_once 'models/Endereco.php';
        $modeloEndereco = new Endereco();
        $clienteId = Session::getUserId();
        $acao = $_POST['acao'] ?? '';
        
        try {
            switch ($acao) {
                case 'adicionar':
                    $dados = [
                        'pessoa_id' => $clienteId,
                        'cep' => $_POST['cep'] ?? '',
                        'logradouro' => $_POST['logradouro'] ?? '',
                        'numero' => $_POST['numero'] ?? '',
                        'complemento' => $_POST['complemento'] ?? '',
                        'bairro' => $_POST['bairro'] ?? '',
                        'cidade' => $_POST['cidade'] ?? '',
                        'estado' => $_POST['estado'] ?? '',
                        'principal' => isset($_POST['principal'])
                    ];
                    
                    $resultado = $modeloEndereco->adicionar($dados);
                    
                    if ($resultado) {
                        $mensagem = 'Endereço adicionado com sucesso!';
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
                            exit;
                        }
                        Session::setFlash('sucesso', $mensagem, 'success');
                    } else {
                        throw new Exception('Erro ao adicionar endereço');
                    }
                    break;
                    
                case 'editar':
                    $enderecoId = $_POST['endereco_id'] ?? 0;
                    $dados = [
                        'pessoa_id' => $clienteId,
                        'cep' => $_POST['cep'] ?? '',
                        'logradouro' => $_POST['logradouro'] ?? '',
                        'numero' => $_POST['numero'] ?? '',
                        'complemento' => $_POST['complemento'] ?? '',
                        'bairro' => $_POST['bairro'] ?? '',
                        'cidade' => $_POST['cidade'] ?? '',
                        'estado' => $_POST['estado'] ?? '',
                        'principal' => isset($_POST['principal'])
                    ];
                    
                    $resultado = $modeloEndereco->editar($enderecoId, $dados);
                    
                    if ($resultado) {
                        $mensagem = 'Endereço atualizado com sucesso!';
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
                            exit;
                        }
                        Session::setFlash('sucesso', $mensagem, 'success');
                    } else {
                        throw new Exception('Erro ao atualizar endereço');
                    }
                    break;
                    
                case 'definir_principal':
                    $enderecoId = $_POST['endereco_id'] ?? 0;
                    $resultado = $modeloEndereco->definirPrincipal($enderecoId, $clienteId);
                    
                    if ($resultado) {
                        $mensagem = 'Endereço principal definido com sucesso!';
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
                            exit;
                        }
                        Session::setFlash('sucesso', $mensagem, 'success');
                    } else {
                        throw new Exception('Erro ao definir endereço principal');
                    }
                    break;
                    
                case 'excluir':
                    $enderecoId = $_POST['endereco_id'] ?? 0;
                    $resultado = $modeloEndereco->excluir($enderecoId, $clienteId);
                    
                    if ($resultado) {
                        $mensagem = 'Endereço excluído com sucesso!';
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
                            exit;
                        }
                        Session::setFlash('sucesso', $mensagem, 'success');
                    } else {
                        throw new Exception('Erro ao excluir endereço');
                    }
                    break;
                    
                default:
                    throw new Exception('Ação não reconhecida');
            }
            
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
                exit;
            }
            Session::setFlash('erro', $e->getMessage(), 'danger');
        }
        
        header('Location: ' . url('cliente/perfil/enderecos'));
        exit;
    }
    
    // Métodos privados idênticos ao PerfilController
    private function atualizarDadosPessoais($userId, $usuarioAtual) {
        $dados = [
            'nome' => trim($_POST['nome']),
            'email' => trim($_POST['email']),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'cpf' => null,
            'dt_nascimento' => null
        ];
        
        if (empty($usuarioAtual['cpf']) && !empty($_POST['cpf'])) {
            $dados['cpf'] = preg_replace('/\D/', '', $_POST['cpf']);
        } else {
            $dados['cpf'] = $usuarioAtual['cpf'];
        }
        
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
    
    private function alterarSenha($userId) {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
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
        
        if (!$this->model->verificarSenha($userId, $senhaAtual)) {
            Session::setFlash('error', 'Senha atual incorreta!', 'danger');
            return;
        }
        
        if ($this->model->atualizarSenha($userId, $novaSenha)) {
            Session::setFlash('success', 'Senha alterada com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao alterar senha!', 'danger');
        }
    }
    
    private function uploadFotoPerfil($userId)
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
    
    private function processarAdicionarEndereco($userId) {
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
        
        if (empty($dados['cep']) || empty($dados['logradouro']) || empty($dados['numero']) || 
            empty($dados['bairro']) || empty($dados['cidade']) || empty($dados['estado'])) {
            
            $message = 'Todos os campos obrigatórios devem ser preenchidos!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
            return;
        }
        
        if ($this->model->verificarEnderecoExistente($userId, $dados)) {
            $message = 'Este endereço já está cadastrado!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'warning');
            return;
        }
        
        $enderecoId = $this->model->adicionarEndereco($dados);
        
        if ($enderecoId) {
            $message = 'Endereço adicionado com sucesso!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $novoEndereco = $this->model->buscarEnderecoPorId($enderecoId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'endereco' => $novoEndereco
                ]);
                exit;
            }
            
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao adicionar endereço!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }
    }
    
    private function editarEndereco($userId) {
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
        
        if ($this->model->editarEndereco($enderecoId, $userId, $dados)) {
            $message = 'Endereço atualizado com sucesso!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $enderecoAtualizado = $this->model->buscarEnderecoPorId($enderecoId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'endereco' => $enderecoAtualizado
                ]);
                exit;
            }
            
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao atualizar endereço!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }
    }
    
    private function excluirEndereco($userId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if ($this->model->excluirEndereco($enderecoId, $userId)) {
            $message = 'Endereço removido com sucesso!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message, 'endereco_id' => $enderecoId]);
                exit;
            }
            
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao remover endereço!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }
    }
    
    private function definirEnderecoPrincipal($userId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if ($this->model->definirEnderecoPrincipal($enderecoId, $userId)) {
            $message = 'Endereço principal definido com sucesso!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $enderecos = $this->model->buscarEnderecosPorUsuario($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'enderecos' => $enderecos
                ]);
                exit;
            }
            
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao definir endereço principal!';
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: ' . url('cliente/perfil/enderecos'));
            exit;
        }
    }
    
    public function buscarPorCEP() {
        error_log("ClientePerfilController::buscarPorCEP - inicio");
        header('Content-Type: application/json; charset=utf-8');

        try {
            $cep = $_GET['cep'] ?? '';
            $cep_clean = preg_replace('/\D/', '', $cep);
            error_log("CEP recebido: {$cep} -> limpo: {$cep_clean}");

            if (strlen($cep_clean) !== 8) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'CEP inválido. Informe 8 dígitos.']);
                exit;
            }

            $url = "https://viacep.com.br/ws/{$cep_clean}/json/";

            // Tentar file_get_contents
            $context = stream_context_create(['http' => ['timeout' => 8, 'header' => 'User-Agent: ChamaServico/1.0']]);
            $response = @file_get_contents($url, false, $context);

            // Fallback para cURL se necessário
            if ($response === false && function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ChamaServico/1.0');
                $response = curl_exec($ch);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if ($response === false) {
                    error_log("ClientePerfilController::buscarPorCEP - cURL erro: {$curlErr}");
                }
            }

            if ($response === false || empty($response)) {
                throw new Exception('Erro ao consultar CEP. Verifique conexão com a API externa.');
            }

            $data = json_decode($response, true);
            error_log("ClientePerfilController::buscarPorCEP - ViaCEP resposta: " . substr($response, 0, 500));

            if (!$data || isset($data['erro'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'CEP não encontrado.']);
                exit;
            }

            $endereco = [
                'cep' => $cep_clean,
                'logradouro' => $data['logradouro'] ?? '',
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['localidade'] ?? '',
                'estado' => $data['uf'] ?? ''
            ];

            echo json_encode(['success' => true, 'endereco' => $endereco, 'message' => 'CEP encontrado']);
            exit;
        } catch (Exception $e) {
            error_log("ClientePerfilController::buscarPorCEP - Exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
?>