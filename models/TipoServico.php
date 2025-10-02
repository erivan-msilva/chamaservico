<?php
require_once 'core/Database.php';

class TipoServico {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function buscarTodos($filtros = []) {
        $sql = "SELECT ts.*, 
                       COUNT(s.id) as total_solicitacoes
                FROM tb_tipo_servico ts
                LEFT JOIN tb_solicita_servico s ON ts.id = s.tipo_servico_id
                WHERE 1=1";
        
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'ativo') {
                $sql .= " AND ts.ativo = 1";
            } elseif ($filtros['status'] === 'inativo') {
                $sql .= " AND ts.ativo = 0";
            }
        }
        
        if (!empty($filtros['categoria'])) {
            $sql .= " AND ts.categoria = ?";
            $params[] = $filtros['categoria'];
        }
        
        if (!empty($filtros['busca'])) {
            $sql .= " AND (ts.nome LIKE ? OR ts.descricao LIKE ?)";
            $termoBusca = '%' . $filtros['busca'] . '%';
            $params[] = $termoBusca;
            $params[] = $termoBusca;
        }
        
        $sql .= " GROUP BY ts.id ORDER BY ts.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM tb_tipo_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO tb_tipo_servico (nome, descricao, categoria, ativo, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['descricao'],
            $dados['categoria'],
            $dados['ativo']
        ]);
    }
    
    public function atualizar($id, $dados) {
        $sql = "UPDATE tb_tipo_servico 
                SET nome = ?, descricao = ?, categoria = ?, ativo = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['descricao'],
            $dados['categoria'],
            $dados['ativo'],
            $id
        ]);
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM tb_tipo_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function alterarStatus($id, $status) {
        $sql = "UPDATE tb_tipo_servico SET ativo = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    public function verificarNomeExistente($nome, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM tb_tipo_servico WHERE nome = ?";
        $params = [$nome];
        
        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    public function temSolicitacoesVinculadas($id) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE tipo_servico_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function buscarCategorias() {
        $sql = "SELECT DISTINCT categoria FROM tb_tipo_servico WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function obterEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                    SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos,
                    COUNT(DISTINCT categoria) as total_categorias
                FROM tb_tipo_servico";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function atualizarOrdem($ordem) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            foreach ($ordem as $posicao => $id) {
                $sql = "UPDATE tb_tipo_servico SET ordem = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$posicao + 1, $id]);
            }
            
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return false;
        }
    }
}
?>
    }
}
?>
