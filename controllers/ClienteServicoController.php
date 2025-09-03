<?php
require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
require_once 'config/session.php';

class ClienteServicoController
{
    private $solicitacaoModel;
    private $propostaModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->propostaModel = new Proposta();
        Session::requireClientLogin();

        // Verificar se é cliente
        if (!Session::isCliente()) {
            header('Location: /chamaservico/acesso-negado');
            exit;
        }
    }

    /**
     * Listar serviços concluídos do cliente
     */
    public function servicosConcluidos()
    {
        $clienteId = Session::getUserId();
        
        try {
            // Buscar serviços com status concluído (status_id = 5)
            $servicosConcluidos = $this->solicitacaoModel->buscarPorUsuario($clienteId, ['status' => 5]);
            
            // Para cada serviço, buscar a proposta aceita
            foreach ($servicosConcluidos as &$servico) {
                $propostas = $this->propostaModel->buscarPropostasRecebidas($clienteId, [
                    'solicitacao_id' => $servico['id'],
                    'status' => 'aceita'
                ]);
                
                if (!empty($propostas)) {
                    $servico['proposta_aceita'] = $propostas[0];
                }
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar serviços concluídos: " . $e->getMessage());
            $servicosConcluidos = [];
            Session::setFlash('error', 'Erro ao carregar serviços concluídos!', 'danger');
        }

        include 'views/cliente/servicos/concluidos.php';
    }

    /**
     * Avaliar um serviço concluído
     */
    public function avaliarServico()
    {
        $servicoId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        if (!$servicoId) {
            Session::setFlash('error', 'Serviço não informado!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/servicos/avaliar?id=' . $servicoId);
                exit;
            }

            // ADICIONADO: Debug da nota recebida
            $nota = $_POST['nota'] ?? 0;
            error_log("DEBUG: Nota recebida = " . $nota);

            $comentario = trim($_POST['comentario'] ?? '');
            $recomendaria = isset($_POST['recomendaria']) ? 1 : 0;

            // Validações
            if (empty($nota) || $nota < 1 || $nota > 5) {
                Session::setFlash('error', 'Selecione uma nota válida de 1 a 5 estrelas!', 'danger');
                header('Location: /chamaservico/cliente/servicos/avaliar?id=' . $servicoId);
                exit;
            }

            if (empty($comentario) || strlen($comentario) < 10) {
                Session::setFlash('error', 'O comentário deve ter pelo menos 10 caracteres!', 'danger');
                header('Location: /chamaservico/cliente/servicos/avaliar?id=' . $servicoId);
                exit;
            }

            try {
                // Buscar dados do serviço
                $sql = "SELECT s.*, p.prestador_id, p.valor as valor_aceito,
                               pr.nome as prestador_nome
                        FROM tb_solicita_servico s
                        JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                        JOIN tb_pessoa pr ON p.prestador_id = pr.id
                        WHERE s.id = ? AND s.cliente_id = ? AND s.status_id = 5";
                
                $stmt = $this->solicitacaoModel->db->prepare($sql);
                $stmt->execute([$servicoId, $clienteId]);
                $servico = $stmt->fetch();

                if (!$servico) {
                    Session::setFlash('error', 'Serviço não encontrado ou não está concluído!', 'danger');
                    header('Location: /chamaservico/cliente/servicos/concluidos');
                    exit;
                }

                // Verificar se já foi avaliado
                require_once 'models/Avaliacao.php';
                $avaliacaoModel = new Avaliacao();
                if ($avaliacaoModel->verificarAvaliacaoExistente($servicoId, $clienteId, $servico['prestador_id'])) {
                    Session::setFlash('info', 'Você já avaliou este serviço!', 'info');
                    header('Location: /chamaservico/cliente/servicos/concluidos');
                    exit;
                }

                // Criar avaliação
                $dadosAvaliacao = [
                    'solicitacao_id' => $servicoId,
                    'avaliador_id' => $clienteId,
                    'avaliado_id' => $servico['prestador_id'],
                    'nota' => floatval($nota), // CORRIGIDO: garantir que seja float
                    'comentario' => $comentario
                ];

                if ($avaliacaoModel->criarAvaliacao($dadosAvaliacao)) {
                    // Criar notificação para o prestador
                    $notasTexto = [
                        1 => 'uma avaliação (⭐)',
                        2 => 'uma avaliação (⭐⭐)',
                        3 => 'uma avaliação (⭐⭐⭐)',
                        4 => 'uma avaliação positiva (⭐⭐⭐⭐)',
                        5 => 'uma avaliação excelente (⭐⭐⭐⭐⭐)'
                    ];

                    require_once 'models/Notificacao.php';
                    $notificacaoModel = new Notificacao();
                    $titulo = $nota >= 4 ? "🌟 Nova Avaliação Positiva!" : "📝 Nova Avaliação Recebida";
                    $mensagem = "Você recebeu {$notasTexto[$nota]} do cliente para o serviço '{$servico['titulo']}'";
                    
                    if ($recomendaria) {
                        $mensagem .= " e foi recomendado!";
                    }

                    $notificacaoModel->criarNotificacao(
                        $servico['prestador_id'],
                        $titulo,
                        $mensagem,
                        'nova_avaliacao',
                        $servicoId
                    );

                    Session::setFlash('success', 'Avaliação enviada com sucesso! Obrigado pelo feedback.', 'success');
                    header('Location: /chamaservico/cliente/servicos/concluidos');
                    exit;
                } else {
                    Session::setFlash('error', 'Erro ao salvar avaliação!', 'danger');
                }

            } catch (Exception $e) {
                error_log("Erro ao processar avaliação: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno ao processar avaliação!', 'danger');
            }
        }

        // Código GET existente...
        try {
            $sql = "SELECT s.*, 
                           p.valor as valor_aceito,
                           pr.nome as prestador_nome,
                           pr.id as prestador_id,
                           ts.nome as tipo_servico_nome,
                           st.nome as status_nome,
                           st.cor as status_cor,
                           e.logradouro, e.numero, e.bairro, e.cidade, e.estado
                    FROM tb_solicita_servico s
                    JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    JOIN tb_status_solicitacao st ON s.status_id = st.id
                    JOIN tb_endereco e ON s.endereco_id = e.id
                    WHERE s.id = ? AND s.cliente_id = ? AND s.status_id = 5";
            
            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $stmt->execute([$servicoId, $clienteId]);
            $servico = $stmt->fetch();

            if (!$servico) {
                Session::setFlash('error', 'Serviço não encontrado ou não está concluído!', 'danger');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

            // Verificar se já foi avaliado
            require_once 'models/Avaliacao.php';
            $avaliacaoModel = new Avaliacao();
            if ($avaliacaoModel->verificarAvaliacaoExistente($servicoId, $clienteId, $servico['prestador_id'])) {
                Session::setFlash('info', 'Você já avaliou este serviço!', 'info');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao carregar serviço para avaliação: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar dados do serviço!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        include 'views/cliente/servicos/avaliar.php';
    }

    /**
     * Confirmar conclusão do serviço
     */
    public function confirmarConclusao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        $solicitacaoId = $_POST['solicitacao_id'] ?? 0;
        $clienteId = Session::getUserId();

        try {
            // Atualizar status para "Finalizado" (status_id = 13)
            if ($this->solicitacaoModel->atualizarStatus($solicitacaoId, 13, $clienteId)) {
                Session::setFlash('success', 'Serviço confirmado como concluído!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao confirmar conclusão!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao confirmar conclusão: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        header('Location: /chamaservico/cliente/servicos/concluidos');
        exit;
    }

    /**
     * Solicitar revisão do serviço
     */
    public function solicitarRevisao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        $solicitacaoId = $_POST['solicitacao_id'] ?? 0;
        $motivo = trim($_POST['motivo'] ?? '');
        $clienteId = Session::getUserId();

        if (!$motivo) {
            Session::setFlash('error', 'Motivo da revisão é obrigatório!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        try {
            if ($this->solicitacaoModel->solicitarRevisao($solicitacaoId, $clienteId, $motivo)) {
                // Criar notificação para o prestador
                $this->notificarRevisao($solicitacaoId, $motivo);
                
                Session::setFlash('success', 'Revisão solicitada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao solicitar revisão!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao solicitar revisão: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        header('Location: /chamaservico/cliente/servicos/concluidos');
        exit;
    }

    /**
     * Salvar avaliação no banco de dados
     */
    private function salvarAvaliacao($solicitacaoId, $avaliadorId, $avaliadoId, $nota, $comentario)
    {
        try {
            $sql = "INSERT INTO tb_avaliacao (solicitacao_id, avaliador_id, avaliado_id, nota, comentario) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->solicitacaoModel->db->prepare($sql);
            return $stmt->execute([$solicitacaoId, $avaliadorId, $avaliadoId, $nota, $comentario]);
        } catch (Exception $e) {
            error_log("Erro ao salvar avaliação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar prestador sobre solicitação de revisão
     */
    private function notificarRevisao($solicitacaoId, $motivo)
    {
        try {
            // Buscar dados do prestador
            $sql = "SELECT p.prestador_id, s.titulo 
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    WHERE p.solicitacao_id = ? AND p.status = 'aceita'";
            
            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $stmt->execute([$solicitacaoId]);
            $dados = $stmt->fetch();

            if ($dados && class_exists('Notificacao')) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Revisão solicitada";
                $mensagem = "O cliente solicitou revisão do serviço '{$dados['titulo']}'. Motivo: {$motivo}";
                
                $notificacaoModel->criarNotificacao(
                    $dados['prestador_id'],
                    $titulo,
                    $mensagem,
                    'revisao_solicitada',
                    $solicitacaoId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao notificar revisão: " . $e->getMessage());
        }
    }
}
?>
