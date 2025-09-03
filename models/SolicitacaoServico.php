<?php
require_once 'core/Database.php';

class SolicitacaoServico
{
    public $db; // MUDANÇA: private para public

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function criar($dados)
    {
        try {
            error_log("=== MODELO: Iniciando criação da solicitação ===");
            error_log("Dados recebidos: " . print_r($dados, true));
            
            $this->db->getConnection()->beginTransaction();

            $sql = "INSERT INTO tb_solicita_servico 
                    (cliente_id, tipo_servico_id, endereco_id, titulo, descricao, 
                     orcamento_estimado, data_atendimento, status_id, urgencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $dados['cliente_id'],
                $dados['tipo_servico_id'],
                $dados['endereco_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['orcamento_estimado'],
                $dados['data_atendimento'],
                $dados['status_id'] ?? 1, // Aguardando Propostas
                $dados['urgencia']
            ];
            
            error_log("SQL: $sql");
            error_log("Parâmetros: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute($params);

            if ($resultado) {
                $solicitacaoId = $this->db->lastInsertId();
                $this->db->getConnection()->commit();
                
                error_log("Solicitação criada com sucesso! ID: $solicitacaoId");
                return $solicitacaoId;
            } else {
                $this->db->getConnection()->rollBack();
                $errorInfo = $stmt->errorInfo();
                error_log("Erro ao executar SQL: " . print_r($errorInfo, true));
                return false;
            }
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("EXCEÇÃO no modelo: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw para o controller capturar
        }
    }

    // Novo: Salvar imagens da solicitação
    public function salvarImagem($solicitacaoId, $caminhoImagem)
    {
        try {
            $sql = "INSERT INTO tb_imagem_solicitacao (solicitacao_id, caminho_imagem) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$solicitacaoId, $caminhoImagem]);
            
            if (!$resultado) {
                error_log("Erro ao salvar imagem: " . print_r($stmt->errorInfo(), true));
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Exceção ao salvar imagem: " . $e->getMessage());
            return false;
        }
    }

    // Novo: Buscar imagens da solicitação
    public function buscarImagensPorSolicitacao($solicitacaoId) {
        $sql = "SELECT * FROM tb_imagem_solicitacao WHERE solicitacao_id = ? ORDER BY data_upload ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        return $stmt->fetchAll();
    }

    public function buscarPorUsuario($userId, $filtros = [])
    {
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

    public function buscarPorId($id, $userId = null)
    {
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

    public function atualizar($id, $dados, $userId)
    {
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

    public function deletar($id, $userId)
    {
        $sql = "DELETE FROM tb_solicita_servico WHERE id = ? AND cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }

    public function getTiposServico()
    {
        $sql = "SELECT * FROM tb_tipo_servico WHERE ativo = 1 ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEnderecosPorUsuario($userId)
    {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Novo: Deletar imagem específica
    public function deletarImagem($imagemId, $solicitacaoId, $clienteId)
    {
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
    public function buscarSolicitacoesDisponiveis($limit = 20, $filtros = [])
    {
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
    public function getCidadesComSolicitacoes()
    {
        $sql = "SELECT DISTINCT e.cidade 
                FROM tb_endereco e
                JOIN tb_solicita_servico s ON e.id = s.endereco_id
                WHERE s.status_id = 1
                ORDER BY e.cidade";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function contarSolicitacoesPorUsuario($userId)
    {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function contarSolicitacoesPorUsuarioEStatus($userId, $statusId)
    {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ? AND status_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $statusId]);
        return $stmt->fetchColumn();
    }

    // Novo método: Contar solicitações por cliente
    public function contarSolicitacoesPorCliente($clienteId) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return (int) $stmt->fetchColumn();
    }

    // Novo método: Contar serviços concluídos
    public function contarServicosConcluidos($clienteId) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ? AND status_id = 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return (int) $stmt->fetchColumn();
    }

    // Novo método: Calcular valor total investido
    public function calcularValorTotalInvestido($clienteId) {
        $sql = "SELECT COALESCE(SUM(p.valor), 0) 
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                WHERE s.cliente_id = ? AND p.status = 'aceita'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return (float) $stmt->fetchColumn();
    }

    // Novo método: Buscar últimas solicitações
    public function buscarUltimasSolicitacoes($clienteId, $limit = 5) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                WHERE s.cliente_id = ?
                ORDER BY s.data_solicitacao DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Atualizar status da solicitação
     */
    public function atualizarStatus($solicitacaoId, $novoStatus, $clienteId)
    {
        try {
            $sql = "UPDATE tb_solicita_servico SET status_id = ? WHERE id = ? AND cliente_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$novoStatus, $solicitacaoId, $clienteId]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Solicitar revisão do serviço
     */
    public function solicitarRevisao($solicitacaoId, $clienteId, $motivo)
    {
        try {
            // Atualizar status para "Em Revisão" (assumindo status_id = 12)
            $sql = "UPDATE tb_solicita_servico SET status_id = 12 WHERE id = ? AND cliente_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$solicitacaoId, $clienteId]);
        } catch (Exception $e) {
            error_log("Erro ao solicitar revisão: " . $e->getMessage());
            return false;
        }
    }
}

