<?php
require_once 'models/Perfil.php';
require_once 'config/session.php';
require_once 'controllers/PerfilController.php';
require_once 'models/SolicitacaoServico.php';

class PrestadorPerfilController extends PerfilController {
    private $solicitacaoModel;
    // Declaração explícita da propriedade model
    protected $model;
    
    public function __construct() {
        parent::__construct();
        Session::requirePrestadorAccess();
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->model = new Perfil(); // Inicialização
    }
    
    public function index() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);
        
        if (!$usuario) {
            Session::setFlash('error', 'Perfil não encontrado!', 'danger');
            header('Location: /chamaservico/logout');
            exit;
        }
        
        include 'views/prestador/perfil/visualizar.php';
    }
    
    public function editar() {
        $userId = Session::getUserId();
        $usuario = $this->model->buscarPorId($userId);
        
        if (!$usuario) {
            Session::setFlash('error', 'Perfil não encontrado!', 'danger');
            header('Location: /chamaservico/logout');
            exit;
        }
        
        // Buscar tipos de serviço disponíveis no sistema
        $tiposServico = $this->solicitacaoModel->getTiposServico();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/perfil/editar');
                exit;
            }
            
            $acao = $_POST['acao'] ?? '';
            
            switch ($acao) {
                case 'dados_pessoais':
                    $this->atualizarDadosPessoais($userId, $usuario);
                    break;
                    
                case 'dados_profissionais':
                    $this->atualizarDadosProfissionais($userId);
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
            
            header('Location: /chamaservico/prestador/perfil/editar');
            exit;
        }
        
        include 'views/prestador/perfil/editar.php';
    }
    
    private function atualizarDadosProfissionais($userId) {
        // Processar especialidades selecionadas - usando a tabela tb_tipo_servico existente
        $especialidades = isset($_POST['especialidades']) ? $_POST['especialidades'] : [];
        
        // Adicionar especialidade personalizada se marcada
        if (isset($_POST['especialidade_outro']) && $_POST['especialidade_outro'] == 1 && !empty($_POST['especialidade_outro_texto'])) {
            $especialidades[] = trim($_POST['especialidade_outro_texto']);
        }
        
        $especialidadesStr = implode(',', $especialidades);
        
        $dados = [
            'especialidades' => $especialidadesStr,
            'area_atuacao' => trim($_POST['area_atuacao'] ?? ''),
            'descricao_profissional' => trim($_POST['descricao_profissional'] ?? '')
        ];
        
        if ($this->model->atualizarDadosProfissionais($userId, $dados)) {
            Session::setFlash('success', 'Informações profissionais atualizadas com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao atualizar informações profissionais!', 'danger');
        }
    }

    // Método para alterar senha do usuário
    protected function alterarSenha($userId) {
        if (empty($_POST['senha_atual']) || empty($_POST['nova_senha']) || empty($_POST['confirmar_senha'])) {
            Session::setFlash('error', 'Preencha todos os campos de senha!', 'danger');
            return;
        }

        $senhaAtual = $_POST['senha_atual'];
        $novaSenha = $_POST['nova_senha'];
        $confirmarSenha = $_POST['confirmar_senha'];

        // Validar força da nova senha
        if (strlen($novaSenha) < 6) {
            Session::setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres!', 'danger');
            return;
        }

        // Buscar usuário
        $usuario = $this->model->buscarPorId($userId);
        if (!$usuario) {
            Session::setFlash('error', 'Usuário não encontrado!', 'danger');
            return;
        }

        // Verificar senha atual
        if (!password_verify($senhaAtual, $usuario['senha'])) {
            Session::setFlash('error', 'Senha atual incorreta!', 'danger');
            return;
        }

        // Verificar confirmação
        if ($novaSenha !== $confirmarSenha) {
            Session::setFlash('error', 'A nova senha e a confirmação não coincidem!', 'danger');
            return;
        }

        // Verificar se a nova senha é diferente da atual
        if (password_verify($novaSenha, $usuario['senha'])) {
            Session::setFlash('error', 'A nova senha deve ser diferente da senha atual!', 'danger');
            return;
        }

        // Atualizar senha
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        if ($this->model->atualizarSenha($userId, $novaSenhaHash)) {
            Session::setFlash('success', 'Senha alterada com sucesso!', 'success');
            
            // Log de segurança
            error_log("Senha alterada para usuário ID: $userId pelo próprio usuário");
        } else {
            Session::setFlash('error', 'Erro ao alterar senha! Tente novamente.', 'danger');
        }
    }
    
    // Implementar método para verificar se existe tabela de perfil profissional
    private function verificarTabelaPerfilProfissional() {
        try {
            $sql = "SHOW TABLES LIKE 'tb_perfil_profissional'";
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar tabela: " . $e->getMessage());
            return false;
        }
    }
    
    // Implementar método para criar tabela se não existir
    private function criarTabelaPerfilProfissional() {
        try {
            if (!$this->verificarTabelaPerfilProfissional()) {
                $sql = "CREATE TABLE IF NOT EXISTS `tb_perfil_profissional` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `pessoa_id` int(11) NOT NULL,
                  `especialidades` varchar(255) DEFAULT NULL COMMENT 'Lista de especialidades separadas por vírgula',
                  `area_atuacao` varchar(255) DEFAULT NULL COMMENT 'Área geográfica de atendimento',
                  `descricao` text DEFAULT NULL COMMENT 'Descrição profissional',
                  `disponibilidade` varchar(100) DEFAULT NULL COMMENT 'Dias/horários disponíveis',
                  `experiencia_anos` int(11) DEFAULT NULL COMMENT 'Anos de experiência',
                  `ultima_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `pessoa_id` (`pessoa_id`),
                  CONSTRAINT `fk_perfil_profissional_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `tb_pessoa` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
                
                $stmt = $this->model->db->prepare($sql);
                return $stmt->execute();
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela: " . $e->getMessage());
            return false;
        }
    }
    
    public function enderecos() {
        // Verificar se é uma requisição AJAX para API
        if (isset($_GET['action']) && $_GET['action'] === 'api_list') {
            $this->apiListarEnderecos();
            return;
        }
        
        // Verificar se é uma requisição para obter um endereço específico
        if (isset($_GET['acao']) && $_GET['acao'] === 'obter' && isset($_GET['id'])) {
            $this->apiObterEndereco();
            return;
        }
        
        // Processar ações POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAcaoEndereco();
            return;
        }
        
        // Incluir modelo necessário
        require_once 'models/Endereco.php';
        
        // Exibir página de endereços
        $modeloEndereco = new Endereco();
        $prestadorId = Session::getUserId();
        $enderecos = $modeloEndereco->buscarPorPessoa($prestadorId);
        
        // CORREÇÃO: Usar o arquivo correto na pasta prestador/perfil
        include 'views/prestador/perfil/enderecos.php';
    }

    private function apiListarEnderecos() {
        header('Content-Type: application/json');
        
        try {
            require_once 'models/Endereco.php';
            $modeloEndereco = new Endereco();
            $prestadorId = Session::getUserId();
            
            $enderecos = $modeloEndereco->buscarPorPessoa($prestadorId);
            
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
            $prestadorId = Session::getUserId();
            $enderecoId = $_GET['id'];
            
            $endereco = $modeloEndereco->buscarPorId($enderecoId);
            
            // Verificar se o endereço pertence ao prestador
            if (!$endereco || $endereco['pessoa_id'] != $prestadorId) {
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
            header('Location: /chamaservico/prestador/perfil/enderecos');
            exit;
        }
        
        require_once 'models/Endereco.php';
        $modeloEndereco = new Endereco();
        $prestadorId = Session::getUserId();
        $acao = $_POST['acao'] ?? '';
        
        try {
            switch ($acao) {
                case 'adicionar':
                    $dados = [
                        'pessoa_id' => $prestadorId,
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
                        'pessoa_id' => $prestadorId,
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
                    $resultado = $modeloEndereco->definirPrincipal($enderecoId, $prestadorId);
                    
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
                    $resultado = $modeloEndereco->excluir($enderecoId, $prestadorId);
                    
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
        
        header('Location: /chamaservico/prestador/perfil/enderecos');
        exit;
    }
}
?>