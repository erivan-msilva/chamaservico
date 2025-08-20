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
        $sql = "SELECT p.*, s.titulo, s.urgencia, ts.nome as tipo_servico_nome
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
        $sql = "SELECT p.*, s.titulo as solicitacao_titulo, s.descricao as solicitacao_descricao,
                       s.orcamento_estimado, s.urgencia, s.data_atendimento,
                       ts.nome as tipo_servico_nome, c.nome as cliente_nome,
                       pr.nome as prestador_nome, pr.email as prestador_email, 
                       pr.telefone as prestador_telefone, pr.foto_perfil as prestador_foto,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE p.id = ?";

        $params = [$propostaId];

        if ($clienteId) {
            $sql .= " AND s.cliente_id = ?";
            $params[] = $clienteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $proposta = $stmt->fetch();

        if ($proposta) {
            // Verificar se há negociação ativa
            $proposta['tem_negociacao_ativa'] = $this->temNegociacaoAtiva($propostaId);

            // Buscar negociações se houver
            if ($proposta['tem_negociacao_ativa']) {
                $proposta['negociacoes'] = $this->buscarNegociacoesPorProposta($propostaId);
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
            $sqlVerificar = "SELECT p.id, p.status, p.solicitacao_id, s.cliente_id, s.titulo
                            FROM tb_proposta p
                            JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
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
            
            // Verificar se existe a coluna observacoes_cliente
            $sqlCheckColumn = "SHOW COLUMNS FROM tb_proposta LIKE 'observacoes_cliente'";
            $stmtCheckColumn = $this->db->prepare($sqlCheckColumn);
            $stmtCheckColumn->execute();
            $columnExists = $stmtCheckColumn->fetch();
            
            // Aceitar a proposta (com ou sem o campo observacoes_cliente)
            if ($columnExists) {
                $sqlAceitar = "UPDATE tb_proposta SET status = 'aceita', data_aceite = NOW(), observacoes_cliente = ? WHERE id = ?";
                $stmtAceitar = $this->db->prepare($sqlAceitar);
                $resultAceitar = $stmtAceitar->execute([$observacoes, $propostaId]);
            } else {
                $sqlAceitar = "UPDATE tb_proposta SET status = 'aceita', data_aceite = NOW() WHERE id = ?";
                $stmtAceitar = $this->db->prepare($sqlAceitar);
                $resultAceitar = $stmtAceitar->execute([$propostaId]);
            }
            
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
            
            $this->db->getConnection()->commit();
            
            // Log de sucesso
            error_log("Proposta aceita com sucesso: ID=$propostaId, Cliente=$clienteId");
            
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao aceitar proposta: " . $e->getMessage());
            return false;
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
     * @param float $valor Novo valor
     * @param int $prazo Novo prazo
     * @param string $observacoes Observações da resposta
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
        $sql = "SELECT p.*, s.titulo, s.descricao, s.data_atendimento, s.urgencia,
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
        return $stmt->fetchAll();
    }

    public function buscarDetalhesServicoAndamento($propostaId, $prestadorId) {
        $sql = "SELECT p.*, s.titulo, s.descricao, s.orcamento_estimado, s.data_atendimento, s.urgencia,
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
            $sql = "SELECT solicitacao_id FROM tb_proposta WHERE id = ? AND prestador_id = ? AND status = 'aceita'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId, $prestadorId]);
            $proposta = $stmt->fetch();
            
            if (!$proposta) {
                error_log("Proposta não encontrada ou não pertence ao prestador: ID=$propostaId, Prestador=$prestadorId");
                return false;
            }
            
            // Iniciar transação
            $this->db->getConnection()->beginTransaction();
            
            // Mapear status para IDs
            $statusMap = [
                'em_andamento' => 4,
                'concluido' => 5,
                'aguardando_materiais' => 16,
                'suspenso' => 15
            ];
            
            $statusId = $statusMap[$novoStatus] ?? 4;
            
            // Atualizar status da solicitação
            $sqlUpdate = "UPDATE tb_solicita_servico SET status_id = ? WHERE id = ?";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $resultStatus = $stmtUpdate->execute([$statusId, $proposta['solicitacao_id']]);
            
            if (!$resultStatus) {
                $this->db->getConnection()->rollBack();
                error_log("Erro ao atualizar status da solicitação: ID={$proposta['solicitacao_id']}");
                return false;
            }
            
            // Se o status for "concluído", gerar Ordem de Serviço
            if ($novoStatus === 'concluido') {
                // Verificar se existe a classe OrdemServico
                if (class_exists('OrdemServico')) {
                    require_once 'models/OrdemServico.php';
                    $ordemServicoModel = new OrdemServico();
                    $osId = $ordemServicoModel->criarOrdemServico($propostaId, $prestadorId);
                    
                    if ($osId) {
                        // Criar notificação específica para OS
                        $this->criarNotificacaoOrdemServico($proposta['solicitacao_id'], $osId, $observacoes);
                    } else {
                        // Se falhou ao criar OS, continuar mesmo assim e só criar notificação normal
                        error_log("Falha ao criar Ordem de Serviço para proposta: $propostaId");
                        $this->criarNotificacaoStatusServico($proposta['solicitacao_id'], $novoStatus, $observacoes);
                    }
                } else {
                    // Classe OrdemServico não existe, criar apenas notificação normal
                    $this->criarNotificacaoStatusServico($proposta['solicitacao_id'], $novoStatus, $observacoes);
                }
            } else {
                // Criar notificação normal de status
                $this->criarNotificacaoStatusServico($proposta['solicitacao_id'], $novoStatus, $observacoes);
            }
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            // Verificar se há transação ativa antes de fazer rollback
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
                    'concluido' => 'O prestador concluiu o seu serviço',
                    'aguardando_materiais' => 'O serviço está aguardando materiais',
                    'suspenso' => 'O serviço foi temporariamente suspenso'
                ];
                
                $titulo = "Atualização do Serviço";
                $mensagem = $statusMessages[$novoStatus] . " '{$solicitacao['titulo']}'";
                
                if ($observacoes) {
                    $mensagem .= "\n\nObservações: " . $observacoes;
                }
                
                // Verificar se a classe Notificacao existe
                if (class_exists('Notificacao')) {
                    require_once 'models/Notificacao.php';
                    $notificacaoModel = new Notificacao();
                    $notificacaoModel->criarNotificacao(
                        $solicitacao['cliente_id'],
                        $titulo,
                        $mensagem,
                        'status_servico',
                        $solicitacaoId
                    );
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de status: " . $e->getMessage());
        }
    }

    private function criarNotificacaoOrdemServico($solicitacaoId, $osId, $observacoes) {
        try {
            $sql = "SELECT cliente_id, titulo FROM tb_solicita_servico WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$solicitacaoId]);
            $solicitacao = $stmt->fetch();
            
            if ($solicitacao) {
                $titulo = "Serviço Concluído - Ordem de Serviço Gerada";
                $mensagem = "O serviço '{$solicitacao['titulo']}' foi concluído! Uma Ordem de Serviço foi gerada automaticamente. Clique para visualizar, assinar digitalmente e fazer o download.";
                
                if ($observacoes) {
                    $mensagem .= "\n\nObservações do prestador: " . $observacoes;
                }
                
                // Verificar se a classe Notificacao existe
                if (class_exists('Notificacao')) {
                    require_once 'models/Notificacao.php';
                    $notificacaoModel = new Notificacao();
                    $notificacaoModel->criarNotificacao(
                        $solicitacao['cliente_id'],
                        $titulo,
                        $mensagem,
                        'ordem_servico_gerada',
                        $osId
                    );
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de OS: " . $e->getMessage());
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
        $sql = "SELECT p.*, pr.nome as prestador_nome, pr.telefone as prestador_telefone,
                       pr.email as prestador_email, pr.foto_perfil as prestador_foto,
                       s.titulo as solicitacao_titulo, s.descricao as solicitacao_descricao,
                       s.urgencia, s.orcamento_estimado,
                       ts.nome as tipo_servico_nome,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                FROM tb_proposta p
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE p.id = ? AND s.cliente_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId, $clienteId]);
        return $stmt->fetch();
    }
}
?>

