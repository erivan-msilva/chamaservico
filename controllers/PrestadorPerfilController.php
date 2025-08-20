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
        parent::enderecos();
    }
}
?>