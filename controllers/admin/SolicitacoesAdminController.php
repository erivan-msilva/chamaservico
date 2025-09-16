<?php
require_once 'controllers/admin/BaseAdminController.php';
require_once 'core/Database.php';

class SolicitacoesAdminController extends BaseAdminController {
    private $db;
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index() {
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'urgencia' => $_GET['urgencia'] ?? '',
            'tipo_servico' => $_GET['tipo_servico'] ?? '',
            'busca' => $_GET['busca'] ?? ''
        ];
        
        // NOVO: Tipo de visualização
        $tipoVisualizacao = $_GET['view'] ?? 'cards';
        
        try {
            $solicitacoes = $this->buscarSolicitacoes($filtros);
            $estatisticas = $this->obterEstatisticas();
            $statusList = $this->obterStatusSolicitacao();
            $tiposServico = $this->obterTiposServico();
            
            $this->renderView('solicitacoes/index', compact(
                'solicitacoes', 
                'estatisticas', 
                'statusList', 
                'tiposServico', 
                'filtros',
                'tipoVisualizacao'
            ));
        } catch (Exception $e) {
            error_log("Erro no SolicitacoesAdminController::index: " . $e->getMessage());
            
            // Valores padrão em caso de erro
            $solicitacoes = [];
            $estatisticas = [
                'total_solicitacoes' => 0,
                'aguardando_propostas' => 0,
                'concluidas' => 0,
                'canceladas' => 0
            ];
            $statusList = [];
            $tiposServico = [];
            
            $this->renderView('solicitacoes/index', compact(
                'solicitacoes', 
                'estatisticas', 
                'statusList', 
                'tiposServico', 
                'filtros',
                'tipoVisualizacao'
            ));
        }
    }
    
    public function visualizar() {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $this->setFlash('error', 'Solicitação não encontrada!');
            $this->redirect('admin/solicitacoes');
            return;
        }
        
        $solicitacao = $this->buscarSolicitacaoPorId($id);
        
        if (!$solicitacao) {
            $this->setFlash('error', 'Solicitação não encontrada!');
            $this->redirect('admin/solicitacoes');
            return;
        }
        
        $this->renderView('solicitacoes/visualizar', compact('solicitacao'));
    }
    
    public function alterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/solicitacoes');
            return;
        }
        
        if (!$this->verificarCSRF()) {
            $this->setFlash('error', 'Token de segurança inválido!');
            $this->redirect('admin/solicitacoes');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $novoStatus = $_POST['status'] ?? 0;
        $observacoes = $_POST['observacoes'] ?? '';
        
        if ($this->atualizarStatusSolicitacao($id, $novoStatus, $observacoes)) {
            $this->setFlash('success', 'Status da solicitação atualizado com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao atualizar status da solicitação!');
        }
        
        $this->redirect('admin/solicitacoes');
    }
    
    public function estatisticas() {
        $estatisticasGerais = $this->obterEstatisticasGerais();
        $estatisticasPorPeriodo = $this->obterEstatisticasPorPeriodo();
        $estatisticasPorTipo = $this->obterEstatisticasPorTipo();
        $estatisticasPorStatus = $this->obterEstatisticasPorStatus();
        
        $this->renderView('solicitacoes/estatisticas', compact(
            'estatisticasGerais',
            'estatisticasPorPeriodo', 
            'estatisticasPorTipo',
            'estatisticasPorStatus'
        ));
    }
    
    private function buscarSolicitacoes($filtros = []) {
        try {
            $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                           p.nome as cliente_nome, p.email as cliente_email,
                           e.cidade, e.estado,
                           COUNT(DISTINCT pr.id) as total_propostas,
                           COUNT(DISTINCT img.id) as total_imagens
                    FROM tb_solicita_servico s
                    LEFT JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    LEFT JOIN tb_status_solicitacao st ON s.status_id = st.id
                    LEFT JOIN tb_pessoa p ON s.cliente_id = p.id
                    LEFT JOIN tb_endereco e ON s.endereco_id = e.id
                    LEFT JOIN tb_proposta pr ON s.id = pr.solicitacao_id
                    LEFT JOIN tb_imagem_solicitacao img ON s.id = img.solicitacao_id
                    WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $sql .= " AND s.status_id = ?";
                $params[] = $filtros['status'];
            }
            
            if (!empty($filtros['urgencia'])) {
                $sql .= " AND s.urgencia = ?";
                $params[] = $filtros['urgencia'];
            }
            
            if (!empty($filtros['tipo_servico'])) {
                $sql .= " AND s.tipo_servico_id = ?";
                $params[] = $filtros['tipo_servico'];
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (s.titulo LIKE ? OR s.descricao LIKE ? OR p.nome LIKE ?)";
                $termoBusca = '%' . $filtros['busca'] . '%';
                $params[] = $termoBusca;
                $params[] = $termoBusca;
                $params[] = $termoBusca;
            }
            
            $sql .= " GROUP BY s.id ORDER BY s.data_solicitacao DESC LIMIT 50";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações: " . $e->getMessage());
            return [];
        }
    }
    
    private function buscarSolicitacaoPorId($id) {
        try {
            $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                           p.nome as cliente_nome, p.email as cliente_email, p.telefone as cliente_telefone,
                           e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                    FROM tb_solicita_servico s
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    JOIN tb_status_solicitacao st ON s.status_id = st.id
                    JOIN tb_pessoa p ON s.cliente_id = p.id
                    JOIN tb_endereco e ON s.endereco_id = e.id
                    WHERE s.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $solicitacao = $stmt->fetch();
            
            if ($solicitacao) {
                // Buscar imagens
                $sqlImagens = "SELECT * FROM tb_imagem_solicitacao WHERE solicitacao_id = ? ORDER BY data_upload ASC";
                $stmtImagens = $this->db->prepare($sqlImagens);
                $stmtImagens->execute([$id]);
                $solicitacao['imagens'] = $stmtImagens->fetchAll();
                
                // Buscar propostas
                $sqlPropostas = "SELECT pr.*, p.nome as prestador_nome, p.email as prestador_email
                                FROM tb_proposta pr
                                JOIN tb_pessoa p ON pr.prestador_id = p.id
                                WHERE pr.solicitacao_id = ?
                                ORDER BY pr.data_proposta DESC";
                $stmtPropostas = $this->db->prepare($sqlPropostas);
                $stmtPropostas->execute([$id]);
                $solicitacao['propostas'] = $stmtPropostas->fetchAll();
            }
            
            return $solicitacao;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitação por ID: " . $e->getMessage());
            return false;
        }
    }
    
    private function obterEstatisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_solicitacoes,
                        SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as aguardando_propostas,
                        SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as concluidas,
                        SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as canceladas,
                        SUM(CASE WHEN data_solicitacao >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as ultimos_30_dias
                    FROM tb_solicita_servico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'total_solicitacoes' => 0,
                'aguardando_propostas' => 0,
                'concluidas' => 0,
                'canceladas' => 0,
                'ultimos_30_dias' => 0
            ];
        }
    }
    
    private function obterStatusSolicitacao() {
        try {
            $sql = "SELECT * FROM tb_status_solicitacao ORDER BY id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao obter status: " . $e->getMessage());
            return [];
        }
    }
    
    private function obterTiposServico() {
        try {
            $sql = "SELECT * FROM tb_tipo_servico WHERE ativo = 1 ORDER BY nome";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao obter tipos de serviço: " . $e->getMessage());
            return [];
        }
    }
    
    private function atualizarStatusSolicitacao($id, $novoStatus, $observacoes = '') {
        try {
            $sql = "UPDATE tb_solicita_servico SET status_id = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$novoStatus, $id]);
            
            // Log da alteração (opcional)
            if ($resultado && !empty($observacoes)) {
                $this->registrarLogAlteracao($id, $novoStatus, $observacoes);
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da solicitação: " . $e->getMessage());
            return false;
        }
    }
    
    private function registrarLogAlteracao($solicitacaoId, $novoStatus, $observacoes) {
        // Implementar sistema de log se necessário
        try {
            $adminId = $_SESSION['admin_id'] ?? 0;
            error_log("Admin $adminId alterou status da solicitação $solicitacaoId para $novoStatus. Obs: $observacoes");
        } catch (Exception $e) {
            // Log silencioso em caso de erro
        }
    }
    
    private function obterEstatisticasGerais() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    AVG(CASE WHEN orcamento_estimado > 0 THEN orcamento_estimado END) as valor_medio,
                    MIN(data_solicitacao) as primeira_solicitacao,
                    MAX(data_solicitacao) as ultima_solicitacao
                FROM tb_solicita_servico";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    private function obterEstatisticasPorPeriodo() {
        $sql = "SELECT DATE_FORMAT(data_solicitacao, '%Y-%m') as periodo, COUNT(*) as total
                FROM tb_solicita_servico 
                WHERE data_solicitacao >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_solicitacao, '%Y-%m')
                ORDER BY periodo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function obterEstatisticasPorTipo() {
        $sql = "SELECT ts.nome as tipo, COUNT(*) as total
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                GROUP BY s.tipo_servico_id, ts.nome
                ORDER BY total DESC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function obterEstatisticasPorStatus() {
        $sql = "SELECT st.nome as status, COUNT(*) as total, st.cor
                FROM tb_solicita_servico s
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                GROUP BY s.status_id, st.nome, st.cor
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
