<?php
require_once 'controllers/admin/BaseAdminController.php';
require_once 'core/Database.php';

class UsuariosAdminController extends BaseAdminController {
    private $db;
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index() {
        $filtros = [
            'busca' => $_GET['busca'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'ativo' => $_GET['ativo'] ?? '',
            'ordem' => $_GET['ordem'] ?? 'data_cadastro'
        ];
        
        // NOVO: Capturar tipo de visualização
        $tipoVisualizacao = $_GET['view'] ?? 'cards';
        
        $usuarios = $this->buscarUsuarios($filtros);
        $stats = $this->getUsuariosStats();
        
        $this->renderView('usuarios/index', compact('usuarios', 'stats', 'filtros', 'tipoVisualizacao'));
    }
    
    public function visualizar() {
        $id = $_GET['id'] ?? 0;
        $usuario = $this->buscarUsuarioPorId($id);
        
        if (!$usuario) {
            $this->setFlash('error', 'Usuário não encontrado!');
            header('Location: admin/usuarios');
            exit;
        }
        
        $estatisticas = $this->getEstatisticasUsuario($id);
        $enderecos = $this->getEnderecosUsuario($id);
        $solicitacoes = $this->getSolicitacoesUsuario($id);
        
        $this->renderView('usuarios/visualizar', compact('usuario', 'estatisticas', 'enderecos', 'solicitacoes'));
    }
    
    public function ativar() {
        $id = $_POST['id'] ?? 0;
        
        if ($this->alterarStatusUsuario($id, 1)) {
            $this->setFlash('success', 'Usuário ativado com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao ativar usuário!');
        }
        
        header('Location: admin/usuarios');
        exit;
    }
    
    public function desativar() {
        $id = $_POST['id'] ?? 0;
        
        if ($this->alterarStatusUsuario($id, 0)) {
            $this->setFlash('success', 'Usuário desativado com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao desativar usuário!');
        }
        
        header('Location: admin/usuarios');
        exit;
    }
    
    private function buscarUsuarios($filtros = []) {
        try {
            $sql = "SELECT p.*, 
                           COUNT(DISTINCT s.id) as total_solicitacoes,
                           COUNT(DISTINCT pr.id) as total_propostas,
                           COUNT(DISTINCT e.id) as total_enderecos
                    FROM tb_pessoa p
                    LEFT JOIN tb_solicita_servico s ON p.id = s.cliente_id
                    LEFT JOIN tb_proposta pr ON p.id = pr.prestador_id
                    LEFT JOIN tb_endereco e ON p.id = e.pessoa_id
                    WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                if ($filtros['status'] === 'ativo') {
                    $sql .= " AND p.ativo = 1";
                } elseif ($filtros['status'] === 'inativo') {
                    $sql .= " AND p.ativo = 0";
                }
            }
            
            if (!empty($filtros['tipo'])) {
                $sql .= " AND p.tipo = ?";
                $params[] = $filtros['tipo'];
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (p.nome LIKE ? OR p.email LIKE ?)";
                $termoBusca = '%' . $filtros['busca'] . '%';
                $params[] = $termoBusca;
                $params[] = $termoBusca;
            }
            
            $sql .= " GROUP BY p.id ORDER BY p.data_cadastro DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    private function buscarUsuarioPorId($id) {
        try {
            $sql = "SELECT p.*, 
                           COUNT(DISTINCT s.id) as total_solicitacoes,
                           COUNT(DISTINCT pr.id) as total_propostas,
                           COUNT(DISTINCT e.id) as total_enderecos
                    FROM tb_pessoa p
                    LEFT JOIN tb_solicita_servico s ON p.id = s.cliente_id
                    LEFT JOIN tb_proposta pr ON p.id = pr.prestador_id
                    LEFT JOIN tb_endereco e ON p.id = e.pessoa_id
                    WHERE p.id = ?
                    GROUP BY p.id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Buscar endereços do usuário
                $sqlEnderecos = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
                $stmtEnderecos = $this->db->prepare($sqlEnderecos);
                $stmtEnderecos->execute([$id]);
                $usuario['enderecos'] = $stmtEnderecos->fetchAll();
                
                // Buscar últimas solicitações
                $sqlSolicitacoes = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome
                                   FROM tb_solicita_servico s
                                   LEFT JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                                   LEFT JOIN tb_status_solicitacao st ON s.status_id = st.id
                                   WHERE s.cliente_id = ?
                                   ORDER BY s.data_solicitacao DESC
                                   LIMIT 5";
                $stmtSolicitacoes = $this->db->prepare($sqlSolicitacoes);
                $stmtSolicitacoes->execute([$id]);
                $usuario['ultimas_solicitacoes'] = $stmtSolicitacoes->fetchAll();
                
                // Buscar últimas propostas
                $sqlPropostas = "SELECT pr.*, s.titulo as solicitacao_titulo
                                FROM tb_proposta pr
                                LEFT JOIN tb_solicita_servico s ON pr.solicitacao_id = s.id
                                WHERE pr.prestador_id = ?
                                ORDER BY pr.data_proposta DESC
                                LIMIT 5";
                $stmtPropostas = $this->db->prepare($sqlPropostas);
                $stmtPropostas->execute([$id]);
                $usuario['ultimas_propostas'] = $stmtPropostas->fetchAll();
            }
            
            return $usuario;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário por ID: " . $e->getMessage());
            return false;
        }
    }
    
    private function getUsuariosStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo = 'cliente' THEN 1 ELSE 0 END) as clientes,
                    SUM(CASE WHEN tipo = 'prestador' THEN 1 ELSE 0 END) as prestadores,
                    SUM(CASE WHEN tipo = 'ambos' THEN 1 ELSE 0 END) as ambos,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                    SUM(CASE WHEN DATE(data_cadastro) = CURDATE() THEN 1 ELSE 0 END) as novos_hoje
                FROM tb_pessoa";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    private function getEstatisticasUsuario($userId) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ?) as solicitacoes,
                    (SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = ?) as propostas,
                    (SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = ?) as enderecos,
                    (SELECT COUNT(*) FROM tb_avaliacao WHERE avaliado_id = ?) as avaliacoes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $userId]);
        return $stmt->fetch();
    }
    
    private function getEnderecosUsuario($userId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    private function getSolicitacoesUsuario($userId, $limit = 10) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                WHERE s.cliente_id = ?
                ORDER BY s.data_solicitacao DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    private function alterarStatusUsuario($id, $status) {
        try {
            $sql = "UPDATE tb_pessoa SET ativo = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $id]);
            
        } catch (Exception $e) {
            error_log("Erro ao alterar status do usuário: " . $e->getMessage());
            return false;
        }
    }
}
?>
