<?php
require_once 'core/Database.php';

class SolicitacaoServico {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function criar($dados) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $sql = "INSERT INTO tb_solicita_servico 
                    (cliente_id, tipo_servico_id, endereco_id, titulo, descricao, 
                     orcamento_estimado, data_atendimento, status_id, urgencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['cliente_id'],
                $dados['tipo_servico_id'],
                $dados['endereco_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['orcamento_estimado'],
                $dados['data_atendimento'],
                $dados['status_id'] ?? 1, // Aguardando Propostas
                $dados['urgencia']
            ]);
            
            if ($resultado) {
                $solicitacaoId = $this->db->lastInsertId();
                $this->db->getConnection()->commit();
                return $solicitacaoId;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return false;
        }
    }
    
    // Novo: Salvar imagens da solicitação
    public function salvarImagem($solicitacaoId, $caminhoImagem) {
        $sql = "INSERT INTO tb_imagem_solicitacao (solicitacao_id, caminho_imagem) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$solicitacaoId, $caminhoImagem]);
    }
    
    // Novo: Buscar imagens da solicitação
    public function buscarImagensPorSolicitacao($solicitacaoId) {
        $sql = "SELECT * FROM tb_imagem_solicitacao WHERE solicitacao_id = ? ORDER BY data_upload ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        return $stmt->fetchAll();
    }
    
    public function buscarPorUsuario($userId, $filtros = []) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                       e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                       (SELECT COUNT(*) FROM tb_imagem_solicitacao WHERE solicitacao_id = s.id) as total_imagens
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE s.cliente_id = ?";
        
        $params = [$userId];
        
        // Aplicar filtros
        if (!empty($filtros['status'])) {
            $sql .= " AND s.status_id = ?";
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['urgencia'])) {
            $sql .= " AND s.urgencia = ?";
            $params[] = $filtros['urgencia'];
        }
        
        if (!empty($filtros['busca'])) {
            $sql .= " AND (s.titulo LIKE ? OR s.descricao LIKE ?)";
            $termoBusca = '%' . $filtros['busca'] . '%';
            $params[] = $termoBusca;
            $params[] = $termoBusca;
        }
        
        $sql .= " ORDER BY s.data_solicitacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function buscarPorId($id, $userId = null) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep,
                       c.nome as cliente_nome
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                WHERE s.id = ?";
        
        if ($userId) {
            $sql .= " AND s.cliente_id = ?";
            $params = [$id, $userId];
        } else {
            $params = [$id];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $solicitacao = $stmt->fetch();
        
        // Buscar imagens se encontrou a solicitação
        if ($solicitacao) {
            $solicitacao['imagens'] = $this->buscarImagensPorSolicitacao($id);
        }
        
        return $solicitacao;
    }
    
    public function atualizar($id, $dados, $userId) {
        $sql = "UPDATE tb_solicita_servico 
                SET tipo_servico_id = ?, titulo = ?, descricao = ?, 
                    orcamento_estimado = ?, data_atendimento = ?, urgencia = ?
                WHERE id = ? AND cliente_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['tipo_servico_id'],
            $dados['titulo'],
            $dados['descricao'],
            $dados['orcamento_estimado'],
            $dados['data_atendimento'],
            $dados['urgencia'],
            $id,
            $userId
        ]);
    }
    
    public function deletar($id, $userId) {
        $sql = "DELETE FROM tb_solicita_servico WHERE id = ? AND cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }
    
    public function getTiposServico() {
        $sql = "SELECT * FROM tb_tipo_servico WHERE ativo = 1 ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getEnderecosPorUsuario($userId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    // Novo: Deletar imagem específica
    public function deletarImagem($imagemId, $solicitacaoId, $clienteId) {
        // Verificar se a imagem pertence à solicitação do cliente
        $sql = "SELECT i.caminho_imagem 
                FROM tb_imagem_solicitacao i
                JOIN tb_solicita_servico s ON i.solicitacao_id = s.id
                WHERE i.id = ? AND i.solicitacao_id = ? AND s.cliente_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imagemId, $solicitacaoId, $clienteId]);
        $imagem = $stmt->fetch();
        
        if ($imagem) {
            // Deletar arquivo físico
            $caminhoArquivo = "uploads/solicitacoes/" . $imagem['caminho_imagem'];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
            
            // Deletar registro do banco
            $sqlDelete = "DELETE FROM tb_imagem_solicitacao WHERE id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            return $stmtDelete->execute([$imagemId]);
        }
        
        return false;
    }
    
    // Novo: Buscar solicitações disponíveis para prestadores
    public function buscarSolicitacoesDisponiveis($limit = 20, $filtros = []) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                       e.logradouro, e.numero, e.bairro, e.cidade, e.estado, e.cep,
                       c.nome as cliente_nome,
                       (SELECT COUNT(*) FROM tb_imagem_solicitacao WHERE solicitacao_id = s.id) as total_imagens,
                       (SELECT COUNT(*) FROM tb_proposta WHERE solicitacao_id = s.id AND status != 'cancelada') as total_propostas
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                WHERE s.status_id = 1"; // Apenas aguardando propostas
        
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['tipo_servico'])) {
            $sql .= " AND s.tipo_servico_id = ?";
            $params[] = $filtros['tipo_servico'];
        }
        
        if (!empty($filtros['urgencia'])) {
            $sql .= " AND s.urgencia = ?";
            $params[] = $filtros['urgencia'];
        }
        
        if (!empty($filtros['orcamento_min'])) {
            $sql .= " AND s.orcamento_estimado >= ?";
            $params[] = $filtros['orcamento_min'];
        }
        
        if (!empty($filtros['orcamento_max'])) {
            $sql .= " AND s.orcamento_estimado <= ?";
            $params[] = $filtros['orcamento_max'];
        }
        
        if (!empty($filtros['cidade'])) {
            $sql .= " AND e.cidade LIKE ?";
            $params[] = '%' . $filtros['cidade'] . '%';
        }
        
        $sql .= " ORDER BY s.data_solicitacao DESC";
        
        // Paginação
        if ($limit > 0) {
            $page = $filtros['page'] ?? 1;
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Novo: Buscar cidades com solicitações disponíveis
    public function getCidadesComSolicitacoes() {
        $sql = "SELECT DISTINCT e.cidade 
                FROM tb_endereco e
                JOIN tb_solicita_servico s ON e.id = s.endereco_id
                WHERE s.status_id = 1
                ORDER BY e.cidade";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function contarSolicitacoesPorUsuario($userId) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function contarSolicitacoesPorUsuarioEStatus($userId, $statusId) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ? AND status_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $statusId]);
        return $stmt->fetchColumn();
    }
}
?>
