<?php
require_once 'core/Database.php';

class Proposta {
    public $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Métodos para dashboard do prestador
    public function contarPropostasPorPrestador($prestadorId) {
        $sql = "SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = ? AND status != 'cancelada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return (int) $stmt->fetchColumn();
    }

    public function contarPropostasAceitas($prestadorId) {
        $sql = "SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = ? AND status = 'aceita'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return (int) $stmt->fetchColumn();
    }

    public function contarServicosConcluidos($prestadorId) {
        $sql = "SELECT COUNT(*) FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                WHERE p.prestador_id = ? AND p.status = 'aceita' AND s.status_id = 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return (int) $stmt->fetchColumn();
    }

    public function obterAvaliacaoMedia($prestadorId) {
        $sql = "SELECT AVG(a.nota) FROM tb_avaliacao a
                JOIN tb_solicita_servico s ON a.solicitacao_id = s.id
                JOIN tb_proposta p ON s.id = p.solicitacao_id
                WHERE p.prestador_id = ? AND a.avaliado_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId, $prestadorId]);
        $result = $stmt->fetchColumn();
        return $result ? number_format($result, 1) : '0.0';
    }

    public function buscarUltimasPropostas($prestadorId, $limit = 5) {
        $sql = "SELECT p.*, s.titulo as solicitacao_titulo, s.urgencia, ts.nome as tipo_servico_nome
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                WHERE p.prestador_id = ?
                ORDER BY p.data_proposta DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId, $limit]);
        return $stmt->fetchAll();
    }

    // Verificar proposta existente
    public function verificarPropostaExistente($solicitacaoId, $prestadorId) {
        $sql = "SELECT COUNT(*) FROM tb_proposta 
                WHERE solicitacao_id = ? AND prestador_id = ? AND status != 'cancelada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $prestadorId]);
        return $stmt->fetchColumn() > 0;
    }

    // Contar outras propostas
    public function contarOutrasPropostas($solicitacaoId, $prestadorId) {
        $sql = "SELECT COUNT(*) FROM tb_proposta 
                WHERE solicitacao_id = ? AND prestador_id != ? AND status != 'cancelada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $prestadorId]);
        return (int) $stmt->fetchColumn();
    }

    // Enviar proposta
    public function enviar($dados) {
        $sql = "INSERT INTO tb_proposta (solicitacao_id, prestador_id, valor, descricao, prazo_execucao) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['solicitacao_id'],
            $dados['prestador_id'],
            $dados['valor'],
            $dados['descricao'],
            $dados['prazo_execucao']
        ]);
    }

    // Criar proposta
    public function criar($dados) {
        try {
            $sql = "INSERT INTO tb_proposta 
                    (solicitacao_id, prestador_id, valor, descricao, prazo_execucao, status) 
                    VALUES (?, ?, ?, ?, ?, 'pendente')";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['solicitacao_id'],
                $dados['prestador_id'],
                $dados['valor'],
                $dados['descricao'],
                $dados['prazo_execucao']
            ]);

            if ($resultado) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar proposta: " . $e->getMessage());
            return false;
        }
    }

    // Buscar propostas por prestador
    public function buscarPorPrestador($prestadorId, $filtros = []) {
        $sql = "SELECT p.*, s.titulo as solicitacao_titulo, s.descricao as solicitacao_descricao,
                       ts.nome as tipo_servico_nome, c.nome as cliente_nome,
                       e.cidade, e.estado
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE p.prestador_id = ?";

        $params = [$prestadorId];

        if (!empty($filtros['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filtros['status'];
        }

        $sql .= " ORDER BY p.data_proposta DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Buscar propostas recebidas (para clientes)
    public function buscarPropostasRecebidas($clienteId, $filtros = []) {
        $sql = "SELECT p.*, ss.titulo as solicitacao_titulo, ss.status_id as solicitacao_status_id,
                  pe.nome as prestador_nome, ts.nome as tipo_servico_nome
           FROM tb_proposta p
           JOIN tb_solicita_servico ss ON p.solicitacao_id = ss.id
           JOIN tb_pessoa pe ON p.prestador_id = pe.id
           JOIN tb_tipo_servico ts ON ss.tipo_servico_id = ts.id
           WHERE ss.cliente_id = ?";

        $params = [$clienteId];

        // Filtrar por status se necessário
        if (!empty($filtros['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filtros['status'];
        }

        $sql .= " ORDER BY p.data_proposta DESC";

        // Aplicar limite se necessário
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT " . (int)$filtros['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarSolicitacoesCliente($clienteId) {
        $sql = "SELECT id, titulo FROM tb_solicita_servico WHERE cliente_id = ? ORDER BY data_solicitacao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function getEstatisticasPropostasCliente($clienteId) {
        $sql = "SELECT 
                    COUNT(p.id) as total_propostas,
                    SUM(CASE WHEN p.status = 'pendente' THEN 1 ELSE 0 END) as propostas_pendentes,
                    SUM(CASE WHEN p.status = 'aceita' THEN 1 ELSE 0 END) as propostas_aceitas,
                    SUM(CASE WHEN p.status = 'recusada' THEN 1 ELSE 0 END) as propostas_recusadas,
                    AVG(p.valor) as valor_medio
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                WHERE s.cliente_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return $stmt->fetch();
    }

    public function verificarSolicitacaoCliente($solicitacaoId, $clienteId) {
        $sql = "SELECT * FROM tb_solicita_servico WHERE id = ? AND cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $clienteId]);
        return $stmt->fetch();
    }

    public function buscarDetalheProposta($propostaId, $clienteId = null) {
        $sql = "SELECT p.*, 
                   pr.nome as prestador_nome, 
                   pr.telefone as prestador_telefone,
                   pr.email as prestador_email, 
                   pr.foto_perfil as prestador_foto,
                   s.id as solicitacao_id,
                   s.titulo as solicitacao_titulo, 
                   s.descricao as solicitacao_descricao,
                   s.urgencia, 
                   s.orcamento_estimado,
                   ts.nome as tipo_servico_nome,
                   e.logradouro, 
                   e.numero, 
                   e.complemento, 
                   e.bairro, 
                   e.cidade, 
                   e.estado, 
                   e.cep,
                   -- Estatísticas do prestador
                   (SELECT COUNT(*) FROM tb_proposta p2 
                    JOIN tb_solicita_servico s2 ON p2.solicitacao_id = s2.id 
                    WHERE p2.prestador_id = pr.id AND p2.status = 'aceita' AND s2.status_id = 5) as prestador_servicos_concluidos,
                   (SELECT AVG(a.nota) FROM tb_avaliacao a
                    JOIN tb_solicita_servico s3 ON a.solicitacao_id = s3.id
                    JOIN tb_proposta p3 ON s3.id = p3.solicitacao_id
                    WHERE p3.prestador_id = pr.id AND a.avaliado_id = pr.id) as prestador_avaliacao,
                   (SELECT COUNT(*) FROM tb_avaliacao a
                    JOIN tb_solicita_servico s4 ON a.solicitacao_id = s4.id
                    JOIN tb_proposta p4 ON s4.id = p4.solicitacao_id
                    WHERE p4.prestador_id = pr.id AND a.avaliado_id = pr.id) as prestador_total_avaliacoes,
                   -- Anos de experiência
                   TIMESTAMPDIFF(YEAR, pr.data_cadastro, NOW()) as prestador_anos_experiencia
            FROM tb_proposta p
            JOIN tb_pessoa pr ON p.prestador_id = pr.id
            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
            JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
            JOIN tb_endereco e ON s.endereco_id = e.id
            WHERE p.id = ?";
    
    $params = [$propostaId];
    
    // Se clienteId for fornecido, verificar se a proposta pertence ao cliente
    if ($clienteId) {
        $sql .= " AND s.cliente_id = ?";
        $params[] = $clienteId;
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $proposta = $stmt->fetch();
    
    if ($proposta) {
        // Garantir que valores nulos sejam tratados adequadamente
        $proposta['prestador_avaliacao'] = $proposta['prestador_avaliacao'] ? round($proposta['prestador_avaliacao'], 1) : 0;
        $proposta['prestador_servicos_concluidos'] = $proposta['prestador_servicos_concluidos'] ?? 0;
        $proposta['prestador_total_avaliacoes'] = $proposta['prestador_total_avaliacoes'] ?? 0;
        $proposta['prestador_anos_experiencia'] = $proposta['prestador_anos_experiencia'] ?? 0;
        
        // Verificar se é um telefone válido
        if (empty($proposta['prestador_telefone']) || strlen($proposta['prestador_telefone']) < 8) {
            $proposta['prestador_telefone'] = 'Não informado';
        }
        
        // Verificar se é um email válido  
        if (empty($proposta['prestador_email']) || !filter_var($proposta['prestador_email'], FILTER_VALIDATE_EMAIL)) {
            $proposta['prestador_email'] = 'Não informado';
        }
    }
    
    return $proposta;
}

    // Método que estava faltando
    public function temNegociacaoAtiva($propostaId) {
        $sql = "SELECT COUNT(*) FROM tb_negociacao_proposta 
                WHERE proposta_id = ? AND tipo IN ('contra_proposta', 'resposta_prestador')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId]);
        return $stmt->fetchColumn() > 0;
    }

    public function buscarNegociacoesPorProposta($propostaId) {
        $sql = "SELECT * FROM tb_negociacao_proposta 
                WHERE proposta_id = ? 
                ORDER BY data_negociacao ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId]);
        return $stmt->fetchAll();
    }

    public function buscarPropostaComNegociacao($propostaId, $userId = null) {
        $sql = "SELECT p.*, s.titulo as solicitacao_titulo, s.descricao as solicitacao_descricao,
                   s.cliente_id as cliente_id, -- ADICIONE ESTA LINHA
                   ts.nome as tipo_servico_nome, c.nome as cliente_nome,
                   pr.nome as prestador_nome, pr.email as prestador_email, 
                   pr.telefone as prestador_telefone,
                   e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
            FROM tb_proposta p
            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
            JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
            JOIN tb_pessoa c ON s.cliente_id = c.id
            JOIN tb_pessoa pr ON p.prestador_id = pr.id
            JOIN tb_endereco e ON s.endereco_id = e.id
            WHERE p.id = ?";
        $params = [$propostaId];
        if ($userId) {
            $sql .= " AND (s.cliente_id = ? OR p.prestador_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $proposta = $stmt->fetch();
        if ($proposta) {
            $proposta['negociacoes'] = $this->buscarNegociacoesPorProposta($propostaId);
        }
        return $proposta;
    }

    private function criarNotificacaoPropostaAceita($prestadorId, $solicitacaoId) {
        // Buscar título da solicitação
        $sql = "SELECT titulo FROM tb_solicita_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        $solicitacao = $stmt->fetch();

        if ($solicitacao) {
            $titulo = 'Proposta Aceita!';
            $mensagem = 'Sua proposta para "' . $solicitacao['titulo'] . '" foi aceita pelo cliente!';

            $sql = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                    VALUES (?, ?, ?, 'proposta_aceita', ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$prestadorId, $titulo, $mensagem, $solicitacaoId]);
        }
    }

    private function criarNotificacaoPropostaRecusada($prestadorId, $solicitacaoId, $motivo) {
        // Buscar título da solicitação
        $sql = "SELECT titulo FROM tb_solicita_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        $solicitacao = $stmt->fetch();

        if ($solicitacao) {
            $titulo = 'Proposta Recusada';
            $mensagem = 'Sua proposta para "' . $solicitacao['titulo'] . '" foi recusada pelo cliente.';
            if ($motivo) {
                $mensagem .= ' Motivo: ' . $motivo;
            }

            $sql = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                    VALUES (?, ?, ?, 'proposta_recusada', ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$prestadorId, $titulo, $mensagem, $solicitacaoId]);
        }
    }

    // Métodos adicionais para gestão completa de propostas
    public function buscarSolicitacaoPorId($solicitacaoId, $clienteId) {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE s.id = ? AND s.cliente_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $clienteId]);
        return $stmt->fetch();
    }

    // Buscar propostas por solicitação (usado em comparar)
    public function buscarPropostasPorSolicitacao($solicitacaoId) {
        $sql = "SELECT p.*, pr.nome as prestador_nome, pr.foto_perfil as prestador_foto,
                       pr.email as prestador_email, pr.telefone as prestador_telefone
                FROM tb_proposta p
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                WHERE p.solicitacao_id = ?
                ORDER BY p.data_proposta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        return $stmt->fetchAll();
    }

    // Buscar proposta específica (usado em detalhes)
    public function buscarPorId($propostaId) {
        $sql = "SELECT * FROM tb_proposta WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId]);
        return $stmt->fetch();
    }

    // Recusar proposta
    public function recusarProposta($propostaId, $clienteId, $motivo = '') {
        try {
            $sql = "UPDATE tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    SET p.status = 'recusada', p.data_recusa = NOW()
                    WHERE p.id = ? AND s.cliente_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$propostaId, $clienteId]);
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao recusar proposta: " . $e->getMessage());
            return false;
        }
    }

    // Cancelar proposta (prestador)
    public function cancelar($propostaId, $prestadorId) {
        try {
            $sql = "UPDATE tb_proposta SET status = 'cancelada'
                    WHERE id = ? AND prestador_id = ? AND status = 'pendente'";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$propostaId, $prestadorId]);
        } catch (Exception $e) {
            error_log("Erro ao cancelar proposta: " . $e->getMessage());
            return false;
        }
    }

    // Buscar propostas para uma solicitação do cliente
    public function buscarPropostasParaSolicitacao($solicitacaoId, $clienteId) {
        $sql = "SELECT p.*, pr.nome as prestador_nome, pr.telefone as prestador_telefone,
                       pr.email as prestador_email
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                WHERE p.solicitacao_id = ? AND s.cliente_id = ?
                ORDER BY 
                    CASE p.status 
                        WHEN 'pendente' THEN 1 
                        WHEN 'aceita' THEN 2 
                        WHEN 'recusada' THEN 3 
                        WHEN 'cancelada' THEN 4 
                    END,
                    p.valor ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $clienteId]);
        return $stmt->fetchAll();
    }

    // Iniciar negociação
    public function iniciarNegociacao($propostaId, $tipo, $dados = []) {
        try {
            $sql = "INSERT INTO tb_negociacao_proposta 
                    (proposta_id, tipo, valor, prazo, observacoes) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $propostaId,
                $tipo,
                $dados['valor'] ?? null,
                $dados['prazo'] ?? null,
                $dados['observacoes'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Erro ao iniciar negociação: " . $e->getMessage());
            return false;
        }
    }

    // Finalizar negociação
    public function finalizarNegociacao($propostaId, $novoValor, $novoPrazo) {
        try {
            $sql = "UPDATE tb_proposta 
                    SET valor = ?, prazo_execucao = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$novoValor, $novoPrazo, $propostaId]);
        } catch (Exception $e) {
            error_log("Erro ao finalizar negociação: " . $e->getMessage());
            return false;
        }
    }

    public function aceitarProposta($propostaId, $clienteId, $observacoes = '') {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Primeiro, verificar se a proposta existe e os dados estão corretos
            $sqlVerificar = "SELECT p.id, p.status, p.solicitacao_id, p.prestador_id, p.valor, 
                                   s.cliente_id, s.titulo, pr.nome as prestador_nome
                            FROM tb_proposta p
                            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                            JOIN tb_pessoa pr ON p.prestador_id = pr.id
                            WHERE p.id = ? AND s.cliente_id = ?";
            
            $stmtVerificar = $this->db->prepare($sqlVerificar);
            $stmtVerificar->execute([$propostaId, $clienteId]);
            $proposta = $stmtVerificar->fetch();
            
            if (!$proposta) {
                $this->db->getConnection()->rollBack();
                error_log("Proposta não encontrada ou não pertence ao cliente: ID=$propostaId, Cliente=$clienteId");
                return false;
            }
            
            // Verificar se a proposta está pendente
            if ($proposta['status'] !== 'pendente') {
                $this->db->getConnection()->rollBack();
                error_log("Proposta não está pendente: ID=$propostaId, Status={$proposta['status']}");
                return false;
            }
            
            // Aceitar a proposta
            $sqlAceitar = "UPDATE tb_proposta SET status = 'aceita', data_aceite = NOW() WHERE id = ?";
            $stmtAceitar = $this->db->prepare($sqlAceitar);
            $resultAceitar = $stmtAceitar->execute([$propostaId]);
            
            if (!$resultAceitar) {
                $this->db->getConnection()->rollBack();
                error_log("Erro ao atualizar status da proposta: ID=$propostaId");
                return false;
            }
            
            // Recusar outras propostas da mesma solicitação
            $sqlRecusar = "UPDATE tb_proposta SET status = 'recusada', data_recusa = NOW() 
                          WHERE solicitacao_id = ? AND id != ? AND status = 'pendente'";
            $stmtRecusar = $this->db->prepare($sqlRecusar);
            $stmtRecusar->execute([$proposta['solicitacao_id'], $propostaId]);
            
            // Atualizar status da solicitação para "Em andamento" (status_id = 4)
            $sqlStatus = "UPDATE tb_solicita_servico SET status_id = 4 WHERE id = ?";
            $stmtStatus = $this->db->prepare($sqlStatus);
            $resultStatus = $stmtStatus->execute([$proposta['solicitacao_id']]);
            
            if (!$resultStatus) {
                $this->db->getConnection()->rollBack();
                error_log("Erro ao atualizar status da solicitação: ID={$proposta['solicitacao_id']}");
                return false;
            }
            
            // CRIAR NOTIFICAÇÃO DIRETAMENTE AQUI TAMBÉM (backup)
            $this->criarNotificacaoBackup($proposta, $observacoes);
            
            $this->db->getConnection()->commit();
            
            // Log de sucesso
            error_log("Proposta aceita com sucesso: ID=$propostaId, Cliente=$clienteId, Prestador={$proposta['prestador_id']}");
            
            return true;
            
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao aceitar proposta: " . $e->getMessage());
            return false;
        }
    }

    // MÉTODO BACKUP para garantir que a notificação seja criada
    private function criarNotificacaoBackup($proposta, $observacoes = '') {
        try {
            // Inserir notificação diretamente
            $titulo = '🎉 Proposta Aceita!';
            $mensagem = "Sua proposta de R$ " . number_format($proposta['valor'], 2, ',', '.') . 
                       " para '{$proposta['titulo']}' foi aceita!";
            
            if (!empty($observacoes)) {
                $mensagem .= "\n\nObservações: " . $observacoes;
            }
            
            $sqlNotificacao = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                              VALUES (?, ?, ?, 'proposta_aceita', ?)";
            $stmtNotificacao = $this->db->prepare($sqlNotificacao);
            $resultNotificacao = $stmtNotificacao->execute([
                $proposta['prestador_id'],
                $titulo,
                $mensagem,
                $proposta['id']
            ]);
            
            if ($resultNotificacao) {
                error_log("Notificação backup criada com sucesso para prestador: {$proposta['prestador_id']}");
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar notificação backup: " . $e->getMessage());
        }
    }

    public function criarContraProposta($propostaId, $userId, $valor, $prazo, $observacoes = '') {
        try {
            $sql = "INSERT INTO tb_negociacao_proposta 
                    (proposta_id, tipo, valor, prazo, observacoes) 
                    VALUES (?, 'contra_proposta', ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $propostaId,
                $valor,
                $prazo,
                $observacoes
            ]);
        } catch (Exception $e) {
            error_log("Erro ao criar contra-proposta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Responder a uma contra-proposta (aceitar, modificar)
     * @param int $propostaId ID da proposta
     * @param int $prestadorId ID do prestador
     * @param string $tipo Tipo de resposta ('resposta_prestador')
     * @param float $valor Novo valor
     * @param int $prazo Novo prazo
     * @param string $observacoes Observações da resposta
     * @return bool Sucesso da operação
     */
    public function responderContraProposta($propostaId, $prestadorId, $tipo, $valor, $prazo, $observacoes = '') {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Verificar se a proposta pertence ao prestador
            $sql = "SELECT * FROM tb_proposta WHERE id = ? AND prestador_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId, $prestadorId]);
            $proposta = $stmt->fetch();
            
            if (!$proposta) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Inserir a resposta na negociação
            $sqlNegociacao = "INSERT INTO tb_negociacao_proposta 
                              (proposta_id, tipo, valor, prazo, observacoes) 
                              VALUES (?, ?, ?, ?, ?)";
            $stmtNegociacao = $this->db->prepare($sqlNegociacao);
            $resultNegociacao = $stmtNegociacao->execute([
                $propostaId,
                $tipo,
                $valor,
                $prazo,
                $observacoes
            ]);
            
            if (!$resultNegociacao) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Atualizar a proposta com os novos valores
            $sqlProposta = "UPDATE tb_proposta SET valor = ?, prazo_execucao = ? WHERE id = ?";
            $stmtProposta = $this->db->prepare($sqlProposta);
            $resultProposta = $stmtProposta->execute([$valor, $prazo, $propostaId]);
            
            if (!$resultProposta) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Criar notificação para o cliente
            $this->criarNotificacaoResposta($propostaId, $valor, $prazo, $observacoes);
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao responder contra-proposta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recusar uma contra-proposta
     * @param int $propostaId ID da proposta
     * @param int $prestadorId ID do prestador
     * @param string $observacoes Motivo da recusa
     * @return bool Sucesso da operação
     */
    public function recusarContraProposta($propostaId, $prestadorId, $observacoes = '') {
        try {
            // Verificar se a proposta pertence ao prestador
            $sql = "SELECT * FROM tb_proposta p 
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id 
                    WHERE p.id = ? AND p.prestador_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId, $prestadorId]);
            $proposta = $stmt->fetch();
            
            if (!$proposta) {
                return false;
            }
            
            // Inserir recusa na negociação
            $sqlNegociacao = "INSERT INTO tb_negociacao_proposta 
                              (proposta_id, tipo, observacoes) 
                              VALUES (?, 'recusa', ?)";
            $stmtNegociacao = $this->db->prepare($sqlNegociacao);
            $result = $stmtNegociacao->execute([$propostaId, $observacoes]);
            
            if ($result) {
                // Criar notificação para o cliente
                $this->criarNotificacaoRecusaNegociacao($propostaId, $observacoes);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erro ao recusar contra-proposta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar notificação de resposta à contra-proposta
     * @param int $propostaId ID da proposta
     * @param int $prestadorId ID do prestador
     * @param string $tipo Tipo de resposta ('resposta_prestador')
     * @param float $valor Novo valor
     * @param int $prazo Novo prazo
     * @param string $observacoes Observações da resposta
     * @return bool Sucesso da operação
     */
    private function criarNotificacaoResposta($propostaId, $valor, $prazo, $observacoes = '') {
        try {
            // Buscar dados da proposta
            $sql = "SELECT p.id, p.solicitacao_id, s.cliente_id, s.titulo, pr.nome as prestador_nome
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Resposta à sua contra-proposta";
                $mensagem = "O prestador {$dados['prestador_nome']} respondeu à sua contra-proposta para '{$dados['titulo']}' com os seguintes termos:\n\n";
                $mensagem .= "Valor: R$ " . number_format($valor, 2, ',', '.') . "\n";
                $mensagem .= "Prazo: {$prazo} dia(s)\n";
                
                if ($observacoes) {
                    $mensagem .= "Observações: {$observacoes}";
                }
                
                $notificacaoModel->criarNotificacao(
                    $dados['cliente_id'],
                    $titulo,
                    $mensagem,
                    'resposta_negociacao',
                    $propostaId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de resposta: " . $e->getMessage());
        }
    }

    /**
     * Criar notificação de recusa de contra-proposta
     * @param int $propostaId ID da proposta
     * @param string $observacoes Motivo da recusa
     */
    private function criarNotificacaoRecusaNegociacao($propostaId, $observacoes = '') {
        try {
            // Buscar dados da proposta
            $sql = "SELECT p.id, p.solicitacao_id, s.cliente_id, s.titulo, pr.nome as prestador_nome
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Contra-proposta recusada";
                $mensagem = "O prestador {$dados['prestador_nome']} recusou sua contra-proposta para '{$dados['titulo']}'";
                
                if ($observacoes) {
                    $mensagem .= ". Motivo: {$observacoes}";
                }
                
                $notificacaoModel->criarNotificacao(
                    $dados['cliente_id'],
                    $titulo,
                    $mensagem,
                    'recusa_negociacao',
                    $propostaId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de recusa: " . $e->getMessage());
        }
    }

    public function buscarServicosEmAndamento($prestadorId, $limit = null) {
        $sql = "SELECT p.*, s.titulo, s.descricao, s.data_atendimento, s.urgencia, s.status_id,
                   ts.nome as tipo_servico_nome, c.nome as cliente_nome, c.telefone as cliente_telefone,
                   e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                   st.nome as status_nome, st.cor as status_cor
            FROM tb_proposta p
            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
            JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
            JOIN tb_pessoa c ON s.cliente_id = c.id
            JOIN tb_endereco e ON s.endereco_id = e.id
            JOIN tb_status_solicitacao st ON s.status_id = st.id
            WHERE p.prestador_id = ? AND p.status = 'aceita'
            ORDER BY s.data_atendimento ASC, p.data_aceite DESC";
    
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $servicos = $stmt->fetchAll();
        
        // Garantir que todos os campos necessários estejam presentes
        foreach ($servicos as &$servico) {
            $servico['status_id'] = $servico['status_id'] ?? 0;
            $servico['status_nome'] = $servico['status_nome'] ?? 'Status não definido';
            $servico['status_cor'] = $servico['status_cor'] ?? '#6c757d';
        }
        
        return $servicos;
    }

    public function buscarDetalhesServicoAndamento($propostaId, $prestadorId) {
        $sql = "SELECT p.*, s.titulo, s.descricao, s.orcamento_estimado, s.data_atendimento, s.urgencia,
                   s.status_id, s.id as solicitacao_id,
                   ts.nome as tipo_servico_nome, c.nome as cliente_nome, c.email as cliente_email, 
                   c.telefone as cliente_telefone,
                   e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep,
                   st.nome as status_nome, st.cor as status_cor
            FROM tb_proposta p
            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
            JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
            JOIN tb_pessoa c ON s.cliente_id = c.id
            JOIN tb_endereco e ON s.endereco_id = e.id
            JOIN tb_status_solicitacao st ON s.status_id = st.id
            WHERE p.id = ? AND p.prestador_id = ? AND p.status = 'aceita'";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId, $prestadorId]);
        $servico = $stmt->fetch();
        
        if ($servico) {
            // Buscar imagens da solicitação
            $servico['imagens'] = $this->buscarImagensServico($servico['solicitacao_id']);
            
            // Garantir que todos os campos necessários estejam presentes
            $servico['status_id'] = $servico['status_id'] ?? 0;
            $servico['solicitacao_id'] = $servico['solicitacao_id'] ?? $servico['id'];
        }
        
        return $servico;
    }

    public function buscarImagensServico($solicitacaoId) {
        $sql = "SELECT * FROM tb_imagem_solicitacao WHERE solicitacao_id = ? ORDER BY data_upload ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        return $stmt->fetchAll();
    }

    public function atualizarStatusServico($propostaId, $prestadorId, $novoStatus, $observacoes = '') {
        try {
            // Verificar se a proposta pertence ao prestador
            $sqlVerificar = "SELECT p.id, p.solicitacao_id, p.prestador_id, p.status, 
                                   s.id as solicitacao_id_confirmado, s.titulo, s.status_id as status_atual_solicitacao
                            FROM tb_proposta p
                            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                            WHERE p.id = ? AND p.prestador_id = ? AND p.status = 'aceita'";
            
            $stmtVerificar = $this->db->prepare($sqlVerificar);
            $stmtVerificar->execute([$propostaId, $prestadorId]);
            $proposta = $stmtVerificar->fetch();
            
            if (!$proposta) {
                return false;
            }
            
            // Iniciar transação
            $this->db->getConnection()->beginTransaction();
            
            // Mapear status string para IDs da tabela tb_status_solicitacao
            $statusMap = [
                'em_andamento' => 4,      // Em Andamento
                'concluido' => 5,         // Concluído
                'aguardando_materiais' => 16,
                'suspenso' => 15
            ];
            
            $novoStatusId = $statusMap[$novoStatus] ?? 4;
            
            // Atualizar status da solicitação na tabela tb_solicita_servico
            $sqlUpdateSolicitacao = "UPDATE tb_solicita_servico SET status_id = ? WHERE id = ?";
            $stmtUpdateSolicitacao = $this->db->prepare($sqlUpdateSolicitacao);
            $resultadoUpdate = $stmtUpdateSolicitacao->execute([$novoStatusId, $proposta['solicitacao_id']]);
            
            if (!$resultadoUpdate || $stmtUpdateSolicitacao->rowCount() === 0) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Verificar se realmente atualizou
            $sqlVerificarUpdate = "SELECT status_id FROM tb_solicita_servico WHERE id = ?";
            $stmtVerificarUpdate = $this->db->prepare($sqlVerificarUpdate);
            $stmtVerificarUpdate->execute([$proposta['solicitacao_id']]);
            $novoStatusAtual = $stmtVerificarUpdate->fetchColumn();
            
            if ($novoStatusAtual != $novoStatusId) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Se o status for "concluído", criar notificação
            if ($novoStatus === 'concluido') {
                $this->criarNotificacaoStatusServico($proposta['solicitacao_id'], $novoStatus, $observacoes);
            }
            
            // Commit da transação
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao atualizar status do serviço: " . $e->getMessage());
            return false;
        }
    }

    private function criarNotificacaoStatusServico($solicitacaoId, $novoStatus, $observacoes) {
        try {
            $sql = "SELECT cliente_id, titulo FROM tb_solicita_servico WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$solicitacaoId]);
            $solicitacao = $stmt->fetch();
            
            if ($solicitacao) {
                $statusMessages = [
                    'em_andamento' => 'O prestador iniciou o seu serviço',
                    'concluido' => '✅ O prestador concluiu o seu serviço! Confirme a conclusão e avalie o trabalho realizado.',
                    'aguardando_materiais' => 'O serviço está aguardando materiais',
                    'suspenso' => 'O serviço foi temporariamente suspenso'
                ];
                
                $titulo = $novoStatus === 'concluido' ? "🎉 Serviço Concluído!" : "📋 Atualização do Serviço";
                $mensagem = $statusMessages[$novoStatus] . " '{$solicitacao['titulo']}'";
                
                if ($observacoes) {
                    $mensagem .= "\n\n💬 Observações do prestador: " . $observacoes;
                }
                
                // Usar notificação automática se disponível
                if (class_exists('Notificacao')) {
                    require_once 'models/Notificacao.php';
                    Notificacao::criarNotificacaoAutomatica(
                        'servico_concluido',
                        $solicitacao['cliente_id'],
                        $solicitacaoId,
                        [
                            'servico' => $solicitacao['titulo'],
                            'observacoes' => $observacoes
                        ]
                    );
                } else {
                    // Fallback: inserir diretamente
                    $sqlNotif = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                                VALUES (?, ?, ?, 'servico_concluido', ?)";
                    $stmtNotif = $this->db->prepare($sqlNotif);
                    $stmtNotif->execute([$solicitacao['cliente_id'], $titulo, $mensagem, $solicitacaoId]);
                }
                
                error_log("Notificação criada para cliente: {$solicitacao['cliente_id']}");
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de status: " . $e->getMessage());
        }
    }

    public function contarPropostasPorStatusECliente($clienteId, $status) {
        $sql = "SELECT COUNT(*) 
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                WHERE s.cliente_id = ? AND p.status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId, $status]);
        return $stmt->fetchColumn();
    }

    public function buscarPropostaDetalhada($propostaId, $clienteId) {
        $sql = "SELECT p.*, 
                       pr.nome as prestador_nome, 
                       pr.telefone as prestador_telefone,
                       pr.email as prestador_email, 
                       pr.foto_perfil as prestador_foto,
                       pr.whatsapp as prestador_whatsapp,
                       s.id as solicitacao_id,
                       s.titulo as solicitacao_titulo, 
                       s.descricao as solicitacao_descricao,
                       s.urgencia, 
                       s.orcamento_estimado,
                       ts.nome as tipo_servico_nome,
                       e.logradouro, 
                       e.numero, 
                       e.complemento, 
                       e.bairro, 
                       e.cidade, 
                       e.estado, 
                       e.cep,
                       -- Buscar estatísticas do prestador
                       (SELECT COUNT(*) FROM tb_proposta p2 
                        JOIN tb_solicita_servico s2 ON p2.solicitacao_id = s2.id 
                        WHERE p2.prestador_id = pr.id AND p2.status = 'aceita' AND s2.status_id = 5) as prestador_servicos_concluidos,
                       (SELECT AVG(a.nota) FROM tb_avaliacao a
                        JOIN tb_solicita_servico s3 ON a.solicitacao_id = s3.id
                        JOIN tb_proposta p3 ON s3.id = p3.solicitacao_id
                        WHERE p3.prestador_id = pr.id AND a.avaliado_id = pr.id) as prestador_avaliacao,
                       (SELECT COUNT(*) FROM tb_avaliacao a
                        JOIN tb_solicita_servico s4 ON a.solicitacao_id = s4.id
                        JOIN tb_proposta p4 ON s4.id = p4.solicitacao_id
                        WHERE p4.prestador_id = pr.id AND a.avaliado_id = pr.id) as prestador_total_avaliacoes,
                       -- Anos de experiência (baseado na data de cadastro)
                       TIMESTAMPDIFF(YEAR, pr.data_cadastro, NOW()) as prestador_anos_experiencia
                FROM tb_proposta p
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE p.id = ? AND s.cliente_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId, $clienteId]);
        $proposta = $stmt->fetch();
        
        if ($proposta) {
            // Garantir que valores nulos sejam tratados adequadamente
            $proposta['prestador_avaliacao'] = $proposta['prestador_avaliacao'] ? round($proposta['prestador_avaliacao'], 1) : 0;
            $proposta['prestador_servicos_concluidos'] = $proposta['prestador_servicos_concluidos'] ?? 0;
            $proposta['prestador_total_avaliacoes'] = $proposta['prestador_total_avaliacoes'] ?? 0;
            $proposta['prestador_anos_experiencia'] = $proposta['prestador_anos_experiencia'] ?? 0;
        }
        
        return $proposta;
    }

    /**
     * Contar propostas por solicitação
     */
    public function contarPropostasPorSolicitacao($solicitacaoId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM tb_proposta WHERE solicitacao_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$solicitacaoId]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erro ao contar propostas: " . $e->getMessage());
            return 0;
        }
    }

    // Novo método: Enviar proposta
    public function enviarProposta($solicitacaoId, $prestadorId, $valor, $descricao, $prazoExecucao = null)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO tb_proposta (solicitacao_id, prestador_id, valor, descricao, prazo_execucao, status, data_proposta)
                VALUES (?, ?, ?, ?, ?, 'pendente', NOW())";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $solicitacaoId,
            $prestadorId,
            $valor,
            $descricao,
            $prazoExecucao
        ]);
    }

    // Novo método: Contar propostas recebidas por cliente
    public function contarPropostasRecebidas($clienteId) {
        $sql = "SELECT COUNT(*) 
            FROM tb_proposta p
            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
            WHERE s.cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Recusar automaticamente outras propostas quando uma é aceita
     * @param int $solicitacaoId ID da solicitação
     * @param int $propostaAceitaId ID da proposta que foi aceita (não será recusada)
     * @return bool Sucesso da operação
     */
    public function recusarOutrasPropostas($solicitacaoId, $propostaAceitaId) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Buscar outras propostas pendentes da mesma solicitação
            $sqlBuscar = "SELECT p.id, p.prestador_id, p.valor, s.titulo as servico_titulo
                         FROM tb_proposta p
                         JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                         WHERE p.solicitacao_id = ? AND p.id != ? AND p.status = 'pendente'";
            
            $stmtBuscar = $this->db->prepare($sqlBuscar);
            $stmtBuscar->execute([$solicitacaoId, $propostaAceitaId]);
            $outrasPropostas = $stmtBuscar->fetchAll();
            
            if (empty($outrasPropostas)) {
                $this->db->getConnection()->commit();
                return true; // Não há outras propostas para recusar
            }
            
            // Recusar todas as outras propostas
            $sqlRecusar = "UPDATE tb_proposta 
                          SET status = 'recusada', data_recusa = NOW() 
                          WHERE solicitacao_id = ? AND id != ? AND status = 'pendente'";
            
            $stmtRecusar = $this->db->prepare($sqlRecusar);
            $resultRecusar = $stmtRecusar->execute([$solicitacaoId, $propostaAceitaId]);
            
            if (!$resultRecusar) {
                $this->db->getConnection()->rollBack();
                error_log("Erro ao recusar outras propostas da solicitação: $solicitacaoId");
                return false;
            }
            
            // Criar notificações para cada prestador que teve proposta recusada
            foreach ($outrasPropostas as $proposta) {
                $this->criarNotificacaoRecusaAutomatica(
                    $proposta['prestador_id'],
                    $proposta['servico_titulo'],
                    $proposta['id']
                );
            }
            
            $this->db->getConnection()->commit();
            
            $totalRecusadas = count($outrasPropostas);
            error_log("Recusadas automaticamente $totalRecusadas propostas da solicitação: $solicitacaoId");
            
            return true;
            
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao recusar outras propostas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar notificação de recusa automática
     * @param int $prestadorId ID do prestador
     * @param string $servicoTitulo Título do serviço
     * @param int $propostaId ID da proposta recusada
     */
    private function criarNotificacaoRecusaAutomatica($prestadorId, $servicoTitulo, $propostaId) {
        try {
            // Usar a função estática que criamos
            require_once 'models/Notificacao.php';
            Notificacao::criarNotificacaoAutomatica(
                'proposta_recusada',
                $prestadorId,
                $propostaId,
                ['servico' => $servicoTitulo]
            );
            
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de recusa automática: " . $e->getMessage());
        }
    }

    /**
     * Avaliar serviço - grava avaliação do cliente para o serviço prestado
     * @param int $propostaId
     * @param int $prestadorId
     * @param int $avaliacao (nota de 1 a 5)
     * @param string $comentario
     * @return bool
     */
    public function avaliarServico($propostaId, $prestadorId, $avaliacao, $comentario = '')
    {
        try {
            // Buscar a solicitação relacionada à proposta
            $sqlSolicitacao = "SELECT s.id as solicitacao_id, s.cliente_id
                               FROM tb_proposta p
                               JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                               WHERE p.id = ? AND p.prestador_id = ?";
            $stmt = $this->db->prepare($sqlSolicitacao);
            $stmt->execute([$propostaId, $prestadorId]);
            $dados = $stmt->fetch();

            if (!$dados) {
                return false;
            }

            // Verifica se já existe avaliação para esta solicitação e prestador
            $sqlExiste = "SELECT COUNT(*) FROM tb_avaliacao WHERE solicitacao_id = ? AND avaliado_id = ?";
            $stmtExiste = $this->db->prepare($sqlExiste);
            $stmtExiste->execute([$dados['solicitacao_id'], $prestadorId]);
            if ($stmtExiste->fetchColumn() > 0) {
                return false; // Já avaliado
            }

            // Insere avaliação
            $sql = "INSERT INTO tb_avaliacao (solicitacao_id, avaliado_id, nota, comentario, data_avaliacao)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['solicitacao_id'],
                $prestadorId,
                $avaliacao,
                $comentario
            ]);
        } catch (Exception $e) {
            error_log("Erro ao avaliar serviço: " . $e->getMessage());
            return false;
        }
    }
}
?>