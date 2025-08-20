<?php
require_once 'models/Perfil.php';
require_once 'config/session.php';

class PerfilController {
    // Altere para protected para permitir acesso na classe filha
    protected $model;
    
    public function __construct() {
        $this->model = new Perfil();
        Session::requireClientLogin();
    }
    
    public function index() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);
        $enderecos = $this->model->buscarEnderecosPorUsuario($userId);
        
        if (!$usuario) {
            Session::setFlash('error', 'Usuário não encontrado!', 'danger');
            header('Location: /chamaservico/logout');
            exit;
        }
        
        // Redirecionar baseado no tipo de usuário
        if (Session::isPrestador() && !Session::isCliente()) {
            header('Location: /chamaservico/prestador/perfil');
            exit;
        } else {
            header('Location: /chamaservico/cliente/perfil');
            exit;
        }
    }
    
    public function editar() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);
        
        if (!$usuario) {
            Session::setFlash('error', 'Usuário não encontrado!', 'danger');
            header('Location: /chamaservico/logout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/perfil/editar');
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
            
            header('Location: /chamaservico/perfil/editar');
            exit;
        }
        
        // Usar a view de edição comum
        include 'views/cliente/perfil/editar.php';
    }
    
    // Método enderecos removido por duplicidade.
    
    private function atualizarDadosPessoais($userId, $usuarioAtual) {
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
    
    private function alterarSenha($userId) {
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
        if ($this->model->alterarSenha($userId, $novaSenha)) {
            Session::setFlash('success', 'Senha alterada com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao alterar senha!', 'danger');
        }
    }
    
    // Altere de private para protected para permitir acesso nas classes filhas
    protected function uploadFotoPerfil($userId) {
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
    
    public function enderecos() {
        $userId = Session::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                
                // Para requisições AJAX, retornar JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Token de segurança inválido!']);
                    exit;
                }
                
                header('Location: /chamaservico/perfil/enderecos');
                exit;
            }
            
            $acao = $_POST['acao'] ?? 'adicionar';
            
            switch ($acao) {
                case 'editar':
                    $this->editarEndereco($userId);
                    break;
                case 'excluir':
                    $this->excluirEndereco($userId);
                    break;
                case 'definir_principal':
                    $this->definirEnderecoPrincipal($userId);
                    break;
                default:
                    $this->processarAdicionarEndereco($userId);
                    break;
            }
        }
        
        // Buscar endereços apenas para exibição (GET)
        $enderecos = $this->model->buscarEnderecosPorUsuario($userId);
        include 'views/cliente/perfil/enderecos.php';
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
        
        // Validações básicas
        if (empty($dados['cep']) || empty($dados['logradouro']) || empty($dados['numero']) || 
            empty($dados['bairro']) || empty($dados['cidade']) || empty($dados['estado'])) {
            
            $message = 'Todos os campos obrigatórios devem ser preenchidos!';
            
            // Para requisições AJAX, retornar JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
            return;
        }
        
        // Verificar se já existe um endereço idêntico para evitar duplicatas
        if ($this->model->verificarEnderecoExistente($userId, $dados)) {
            $message = 'Este endereço já está cadastrado!';
            
            // Para requisições AJAX, retornar JSON
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
            
            // Para requisições AJAX, retornar o novo endereço
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // Buscar o endereço recém-criado
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
            
            // Para requisições AJAX, retornar JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        // Para requisições normais, redirecionar
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /chamaservico/perfil/enderecos');
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
            
            // Para requisições AJAX
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
            
            // Para requisições AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        // Para requisições normais
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /chamaservico/perfil/enderecos');
            exit;
        }
    }
    
    private function excluirEndereco($userId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if ($this->model->excluirEndereco($enderecoId, $userId)) {
            $message = 'Endereço removido com sucesso!';
            
            // Para requisições AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message, 'endereco_id' => $enderecoId]);
                exit;
            }
            
            Session::setFlash('success', $message, 'success');
        } else {
            $message = 'Erro ao remover endereço!';
            
            // Para requisições AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        // Para requisições normais
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /chamaservico/perfil/enderecos');
            exit;
        }
    }
    
    private function definirEnderecoPrincipal($userId) {
        $enderecoId = $_POST['endereco_id'] ?? 0;
        
        if ($this->model->definirEnderecoPrincipal($enderecoId, $userId)) {
            $message = 'Endereço principal definido com sucesso!';
            
            // Para requisições AJAX, retornar todos os endereços atualizados
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
            
            // Para requisições AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('error', $message, 'danger');
        }
        
        // Para requisições normais
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /chamaservico/perfil/enderecos');
            exit;
        }
    }
}
?>
