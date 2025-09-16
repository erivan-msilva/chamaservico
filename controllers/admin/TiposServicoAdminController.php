<?php
require_once 'controllers/admin/BaseAdminController.php';
require_once 'core/Database.php';

class TiposServicoAdminController extends BaseAdminController {
    private $db;
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index() {
        $filtros = [
            'busca' => $_GET['busca'] ?? '',
            'ativo' => $_GET['ativo'] ?? '',
            'categoria' => $_GET['categoria'] ?? ''
        ];
        
        try {
            $tiposServico = $this->buscarTiposServico($filtros);
            $categorias = $this->obterCategorias();
            $stats = $this->obterEstatisticas();
            
            $this->renderView('tipos-servico/index', compact('tiposServico', 'categorias', 'stats', 'filtros'));
        } catch (Exception $e) {
            error_log("Erro no TiposServicoAdminController::index: " . $e->getMessage());
            
            // Valores padrão em caso de erro
            $tiposServico = [];
            $categorias = [];
            $stats = [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'total_categorias' => 0
            ];
            
            $this->renderView('tipos-servico/index', compact('tiposServico', 'categorias', 'stats', 'filtros'));
        }
    }
    
    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verificarCSRF()) {
                $this->setFlash('error', 'Token de segurança inválido!');
                $this->redirect('admin/tipos-servico');
                return;
            }
            
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'preco_medio' => !empty($_POST['preco_medio']) ? floatval($_POST['preco_medio']) : null,
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ];
            
            if (empty($dados['nome'])) {
                $this->setFlash('error', 'Nome é obrigatório!');
                $this->redirect('admin/tipos-servico');
                return;
            }
            
            if ($this->criarTipoServico($dados)) {
                $this->setFlash('success', 'Tipo de serviço criado com sucesso!');
            } else {
                $this->setFlash('error', 'Erro ao criar tipo de serviço!');
            }
            
            $this->redirect('admin/tipos-servico');
        }
    }
    
    public function editar() {
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verificarCSRF()) {
                $this->setFlash('error', 'Token de segurança inválido!');
                $this->redirect('admin/tipos-servico');
                return;
            }
            
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'preco_medio' => !empty($_POST['preco_medio']) ? floatval($_POST['preco_medio']) : null,
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ];
            
            if (empty($dados['nome'])) {
                $this->setFlash('error', 'Nome é obrigatório!');
                $this->redirect('admin/tipos-servico');
                return;
            }
            
            if ($this->atualizarTipoServico($id, $dados)) {
                $this->setFlash('success', 'Tipo de serviço atualizado com sucesso!');
            } else {
                $this->setFlash('error', 'Erro ao atualizar tipo de serviço!');
            }
            
            $this->redirect('admin/tipos-servico');
        }
        
        $tipoServico = $this->buscarTipoServicoPorId($id);
        if (!$tipoServico) {
            $this->setFlash('error', 'Tipo de serviço não encontrado!');
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        $this->renderView('tipos-servico/editar', compact('tipoServico'));
    }
    
    public function alterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        if (!$this->verificarCSRF()) {
            $this->setFlash('error', 'Token de segurança inválido!');
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        $novoStatus = isset($_POST['ativo']) ? 1 : 0;
        
        if ($this->alterarStatusTipoServico($id, $novoStatus)) {
            $status = $novoStatus ? 'ativado' : 'desativado';
            $this->setFlash('success', "Tipo de serviço {$status} com sucesso!");
        } else {
            $this->setFlash('error', 'Erro ao alterar status do tipo de serviço!');
        }
        
        $this->redirect('admin/tipos-servico');
    }
    
    public function excluir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        if (!$this->verificarCSRF()) {
            $this->setFlash('error', 'Token de segurança inválido!');
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        $id = $_POST['id'] ?? 0;
        
        // Verificar se há solicitações vinculadas
        if ($this->temSolicitacoesVinculadas($id)) {
            $this->setFlash('error', 'Não é possível excluir: existem solicitações vinculadas a este tipo de serviço!');
            $this->redirect('admin/tipos-servico');
            return;
        }
        
        if ($this->excluirTipoServico($id)) {
            $this->setFlash('success', 'Tipo de serviço excluído com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao excluir tipo de serviço!');
        }
        
        $this->redirect('admin/tipos-servico');
    }
    
    public function ordenar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }
        
        $ordens = $_POST['ordem'] ?? [];
        
        if (empty($ordens)) {
            echo json_encode(['success' => false, 'message' => 'Dados de ordenação não fornecidos']);
            exit;
        }
        
        $sucesso = true;
        foreach ($ordens as $id => $ordem) {
            if (!$this->atualizarOrdemTipoServico($id, $ordem)) {
                $sucesso = false;
                break;
            }
        }
        
        echo json_encode([
            'success' => $sucesso,
            'message' => $sucesso ? 'Ordem atualizada com sucesso!' : 'Erro ao atualizar ordem!'
        ]);
        exit;
    }
    
    private function buscarTiposServico($filtros = []) {
        try {
            $sql = "SELECT ts.*, 
                           COUNT(s.id) as total_solicitacoes,
                           COUNT(CASE WHEN s.status_id = 5 THEN 1 END) as servicos_concluidos
                    FROM tb_tipo_servico ts
                    LEFT JOIN tb_solicita_servico s ON ts.id = s.tipo_servico_id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (ts.nome LIKE ? OR ts.descricao LIKE ?)";
                $termoBusca = '%' . $filtros['busca'] . '%';
                $params[] = $termoBusca;
                $params[] = $termoBusca;
            }
            
            if ($filtros['ativo'] !== '') {
                $sql .= " AND ts.ativo = ?";
                $params[] = $filtros['ativo'];
            }
            
            if (!empty($filtros['categoria'])) {
                $sql .= " AND ts.categoria = ?";
                $params[] = $filtros['categoria'];
            }
            
            $sql .= " GROUP BY ts.id ORDER BY ts.nome ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar tipos de serviço: " . $e->getMessage());
            return [];
        }
    }
    
    private function buscarTipoServicoPorId($id) {
        try {
            $sql = "SELECT * FROM tb_tipo_servico WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar tipo de serviço por ID: " . $e->getMessage());
            return false;
        }
    }
    
    private function criarTipoServico($dados) {
        try {
            $sql = "INSERT INTO tb_tipo_servico (nome, descricao, categoria, preco_medio, ativo, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['nome'],
                $dados['descricao'],
                $dados['categoria'],
                $dados['preco_medio'],
                $dados['ativo']
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao criar tipo de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    private function atualizarTipoServico($id, $dados) {
        try {
            $sql = "UPDATE tb_tipo_servico 
                    SET nome = ?, descricao = ?, categoria = ?, preco_medio = ?, ativo = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['nome'],
                $dados['descricao'],
                $dados['categoria'],
                $dados['preco_medio'],
                $dados['ativo'],
                $id
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar tipo de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    private function alterarStatusTipoServico($id, $status) {
        try {
            $sql = "UPDATE tb_tipo_servico SET ativo = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $id]);
            
        } catch (Exception $e) {
            error_log("Erro ao alterar status do tipo de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    private function excluirTipoServico($id) {
        try {
            $sql = "DELETE FROM tb_tipo_servico WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("Erro ao excluir tipo de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    private function temSolicitacoesVinculadas($id) {
        try {
            $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE tipo_servico_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar solicitações vinculadas: " . $e->getMessage());
            return true; // Assumir que tem para evitar exclusão em caso de erro
        }
    }
    
    private function atualizarOrdemTipoServico($id, $ordem) {
        try {
            $sql = "UPDATE tb_tipo_servico SET ordem = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ordem, $id]);
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar ordem do tipo de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    private function obterCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM tb_tipo_servico WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            error_log("Erro ao obter categorias: " . $e->getMessage());
            return [];
        }
    }
    
    private function obterEstatisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                        SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos,
                        COUNT(DISTINCT categoria) as total_categorias
                    FROM tb_tipo_servico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'total_categorias' => 0
            ];
        }
    }
}
?>
