<?php
require_once 'models/Endereco.php';
require_once 'config/session.php';

class EnderecoController {
    private $enderecoModel;
    
    public function __construct() {
        $this->enderecoModel = new Endereco();
        Session::requireClientLogin();
    }
    
    // Listar endereços do usuário
    public function listar() {
        $pessoaId = Session::getUserId();
        
        // Verificar se é requisição AJAX para API
        if ($this->isAjaxRequest() && isset($_GET['action']) && $_GET['action'] === 'api_list') {
            $this->apiListarEnderecos($pessoaId);
            return;
        }
        
        // Buscar endereços
        $enderecos = $this->enderecoModel->buscarPorPessoa($pessoaId);
        
        // Determinar view baseada no tipo de usuário
        $viewPath = $this->getViewPath() . '/enderecos.php';
        
        include $viewPath;
    }
    
    // Processar ações CRUD
    public function processar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToList();
            return;
        }
        
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->respondError('Token de segurança inválido');
            return;
        }
        
        $acao = $_POST['acao'] ?? '';
        $pessoaId = Session::getUserId();
        
        try {
            switch ($acao) {
                case 'adicionar':
                    $this->adicionarEndereco($pessoaId);
                    break;
                    
                case 'editar':
                    $this->editarEndereco($pessoaId);
                    break;
                    
                case 'definir_principal':
                    $this->definirPrincipal($pessoaId);
                    break;
                    
                case 'excluir':
                    $this->excluirEndereco($pessoaId);
                    break;
                    
                default:
                    throw new Exception('Ação não reconhecida');
            }
            
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
    
    // Obter endereço específico (AJAX)
    public function obter() {
        if (!$this->isAjaxRequest() || !isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Requisição inválida']);
            exit;
        }
        
        $enderecoId = $_GET['id'];
        $pessoaId = Session::getUserId();
        
        try {
            $endereco = $this->enderecoModel->buscarPorId($enderecoId);
            
            if (!$endereco || $endereco['pessoa_id'] != $pessoaId) {
                throw new Exception('Endereço não encontrado');
            }
            
            header('Content-Type: application/json');
            echo json_encode($endereco);
            exit;
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    
    // Adicionar endereço
    private function adicionarEndereco($pessoaId) {
        $dados = $this->extrairDadosFormulario($pessoaId);
        $this->validarDados($dados);
        
        $resultado = $this->enderecoModel->adicionar($dados);
        
        if ($resultado) {
            $endereco = $this->enderecoModel->buscarPorId($resultado);
            $this->respondSuccess('Endereço adicionado com sucesso!', $endereco);
        } else {
            throw new Exception('Erro ao adicionar endereço');
        }
    }
    
    // Editar endereço
    private function editarEndereco($pessoaId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        $dados = $this->extrairDadosFormulario($pessoaId);
        $this->validarDados($dados);
        
        // Verificar se o endereço pertence ao usuário
        if (!$this->enderecoModel->pertenceAPessoa($enderecoId, $pessoaId)) {
            throw new Exception('Endereço não encontrado');
        }
        
        $resultado = $this->enderecoModel->editar($enderecoId, $dados);
        
        if ($resultado) {
            $endereco = $this->enderecoModel->buscarPorId($enderecoId);
            $this->respondSuccess('Endereço atualizado com sucesso!', $endereco);
        } else {
            throw new Exception('Erro ao atualizar endereço');
        }
    }
    
    // Definir como principal
    private function definirPrincipal($pessoaId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if (!$this->enderecoModel->pertenceAPessoa($enderecoId, $pessoaId)) {
            throw new Exception('Endereço não encontrado');
        }
        
        $resultado = $this->enderecoModel->definirPrincipal($enderecoId, $pessoaId);
        
        if ($resultado) {
            $enderecos = $this->enderecoModel->buscarPorPessoa($pessoaId);
            $this->respondSuccess('Endereço principal definido com sucesso!', null, $enderecos);
        } else {
            throw new Exception('Erro ao definir endereço principal');
        }
    }
    
    // Excluir endereço
    private function excluirEndereco($pessoaId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if (!$this->enderecoModel->pertenceAPessoa($enderecoId, $pessoaId)) {
            throw new Exception('Endereço não encontrado');
        }
        
        $resultado = $this->enderecoModel->excluir($enderecoId, $pessoaId);
        
        if ($resultado) {
            $this->respondSuccess('Endereço excluído com sucesso!');
        } else {
            throw new Exception('Não é possível excluir o último endereço');
        }
    }
    
    // API listar endereços
    private function apiListarEnderecos($pessoaId) {
        try {
            $enderecos = $this->enderecoModel->buscarPorPessoa($pessoaId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'enderecos' => $enderecos
            ]);
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar endereços: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    // Extrair dados do formulário
    private function extrairDadosFormulario($pessoaId) {
        return [
            'pessoa_id' => $pessoaId,
            'cep' => $this->limparCEP($_POST['cep'] ?? ''),
            'logradouro' => trim($_POST['logradouro'] ?? ''),
            'numero' => trim($_POST['numero'] ?? ''),
            'complemento' => trim($_POST['complemento'] ?? ''),
            'bairro' => trim($_POST['bairro'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => trim($_POST['estado'] ?? ''),
            'principal' => isset($_POST['principal'])
        ];
    }
    
    // Validar dados
    private function validarDados($dados) {
        $required = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'];
        
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new Exception("Campo '{$field}' é obrigatório");
            }
        }
        
        if (!$this->enderecoModel->validarCEP($dados['cep'])) {
            throw new Exception('CEP inválido');
        }
        
        if (strlen($dados['estado']) !== 2) {
            throw new Exception('Estado deve ter 2 caracteres');
        }
    }
    
    // Limpar CEP
    private function limparCEP($cep) {
        return preg_replace('/[^0-9]/', '', $cep);
    }
    
    // Verificar se é requisição AJAX
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    // Determinar caminho da view
    private function getViewPath() {
        if (Session::isPrestador() && !Session::isCliente()) {
            return 'views/prestador/perfil';
        }
        return 'views/cliente/perfil';
    }
    
    // Responder sucesso
    private function respondSuccess($message, $endereco = null, $enderecos = null) {
        if ($this->isAjaxRequest()) {
            $response = ['success' => true, 'message' => $message];
            if ($endereco) $response['endereco'] = $endereco;
            if ($enderecos) $response['enderecos'] = $enderecos;
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        Session::setFlash('success', $message, 'success');
        $this->redirectToList();
    }
    
    // Responder erro
    private function respondError($message) {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        
        Session::setFlash('error', $message, 'danger');
        $this->redirectToList();
    }
    
    // Redirecionar para lista
    private function redirectToList() {
        $redirectPath = Session::isPrestador() && !Session::isCliente() 
            ? 'prestador/perfil/enderecos' 
            : 'cliente/perfil/enderecos';
            
        header("Location: $redirectPath");
        exit;
    }
}
?>
