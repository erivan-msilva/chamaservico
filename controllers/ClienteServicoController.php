<?php
require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
require_once 'models/Avaliacao.php';
require_once 'config/session.php';

class ClienteServicoController
{
    private $solicitacaoModel;
    private $propostaModel;
    private $avaliacaoModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->propostaModel = new Proposta();
        $this->avaliacaoModel = new Avaliacao();
        Session::requireClientLogin();
    }

    /**
     * Listar serviços concluídos pendentes de avaliação
     */
    public function servicosConcluidos()
    {
        $clienteId = Session::getUserId();

        try {
            $servicos = $this->buscarServicosConcluidos($clienteId);
            
            error_log("=== DEBUG SERVIÇOS CONCLUÍDOS ===");
            error_log("Cliente ID: " . $clienteId);
            error_log("Serviços encontrados: " . count($servicos));
            
        } catch (Exception $e) {
            error_log("Erro ao buscar serviços concluídos: " . $e->getMessage());
            $servicos = [];
            Session::setFlash('error', 'Erro ao carregar serviços!', 'danger');
        }

        $title = 'Serviços Concluídos - Cliente';
        include 'views/cliente/servicos/concluidos.php';
    }

    /**
     * Buscar serviços concluídos do cliente
     */
    private function buscarServicosConcluidos($clienteId)
    {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                       e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                       pr.nome as prestador_nome, pr.telefone as prestador_telefone,
                       p.valor as valor_aceito, p.data_aceite, p.id as proposta_id,
                       (SELECT COUNT(*) FROM tb_avaliacao WHERE solicitacao_id = s.id AND avaliador_id = ?) as ja_avaliado
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                WHERE s.cliente_id = ? AND s.status_id IN (5, 11, 13)
                ORDER BY s.data_solicitacao DESC";

        $stmt = $this->solicitacaoModel->db->prepare($sql);
        $stmt->execute([$clienteId, $clienteId]);
        return $stmt->fetchAll();
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
            if ($this->solicitacaoModel->atualizarStatus($solicitacaoId, 11, $clienteId)) {
                Session::setFlash('success', 'Conclusão confirmada! Agora você pode avaliar o serviço.', 'success');
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
     * Avaliar serviço concluído
     */
    public function avaliarServico()
    {
        $solicitacaoId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        if (!$solicitacaoId) {
            Session::setFlash('error', 'Serviço não informado!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        try {
            $servico = $this->buscarServicoParaAvaliacao($solicitacaoId, $clienteId);

            if (!$servico) {
                Session::setFlash('error', 'Serviço não encontrado ou não pode ser avaliado!', 'danger');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

            // Verificar se já foi avaliado
            if ($this->avaliacaoModel->jaAvaliou($solicitacaoId, $clienteId)) {
                Session::setFlash('info', 'Este serviço já foi avaliado!', 'info');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao buscar serviço para avaliação: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar dados!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAvaliacao($solicitacaoId, $clienteId, $servico);
        }

        $title = 'Avaliar Serviço - Cliente';
        include 'views/cliente/servicos/avaliar.php';
    }

    /**
     * Buscar dados do serviço para avaliação
     */
    private function buscarServicoParaAvaliacao($solicitacaoId, $clienteId)
    {
        $sql = "SELECT s.*, ts.nome as tipo_servico_nome,
                       e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                       pr.id as prestador_id, pr.nome as prestador_nome, pr.foto_perfil as prestador_foto,
                       p.valor as valor_aceito, p.data_aceite, p.descricao as proposta_descricao
                FROM tb_solicita_servico s
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                WHERE s.id = ? AND s.cliente_id = ? AND s.status_id IN (11, 5)";

        $stmt = $this->solicitacaoModel->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $clienteId]);
        return $stmt->fetch();
    }

    /**
     * Processar avaliação do serviço
     */
    private function processarAvaliacao($solicitacaoId, $clienteId, $servico)
    {
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            return;
        }

        $nota = $_POST['nota'] ?? 0;
        $comentario = trim($_POST['comentario'] ?? '');
        $recomendaria = isset($_POST['recomendaria']) ? 1 : 0;

        if ($nota < 1 || $nota > 5) {
            Session::setFlash('error', 'Selecione uma nota de 1 a 5 estrelas!', 'danger');
            return;
        }

        if (empty($comentario)) {
            Session::setFlash('error', 'O comentário é obrigatório!', 'danger');
            return;
        }

        try {
            $dadosAvaliacao = [
                'solicitacao_id' => $solicitacaoId,
                'avaliador_id' => $clienteId,
                'avaliado_id' => $servico['prestador_id'],
                'nota' => $nota,
                'comentario' => $comentario,
                'recomendaria' => $recomendaria
            ];

            if ($this->avaliacaoModel->criar($dadosAvaliacao)) {
                // Atualizar status da solicitação para "Finalizado" (status 13)
                $this->solicitacaoModel->atualizarStatus($solicitacaoId, 13, $clienteId);

                // Notificar prestador sobre a avaliação
                $this->notificarAvaliacaoRecebida($servico['prestador_id'], $servico['titulo'], $nota);

                Session::setFlash('success', 'Avaliação enviada com sucesso! Obrigado pelo seu feedback.', 'success');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            } else {
                Session::setFlash('error', 'Erro ao salvar avaliação!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao processar avaliação: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno ao salvar avaliação!', 'danger');
        }
    }

    /**
     * Notificar prestador sobre avaliação recebida
     */
    private function notificarAvaliacaoRecebida($prestadorId, $servicoTitulo, $nota)
    {
        try {
            require_once 'models/Notificacao.php';
            Notificacao::criarNotificacaoAutomatica(
                'avaliacao_recebida',
                $prestadorId,
                null,
                ['servico' => $servicoTitulo, 'nota' => $nota]
            );
        } catch (Exception $e) {
            error_log("Erro ao notificar avaliação: " . $e->getMessage());
        }
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
        $motivo = trim($_POST['motivo_revisao'] ?? '');
        $clienteId = Session::getUserId();

        if (empty($motivo)) {
            Session::setFlash('error', 'Informe o motivo da revisão!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        try {
            if ($this->solicitacaoModel->solicitarRevisao($solicitacaoId, $clienteId, $motivo)) {
                $this->notificarRevisaoSolicitada($solicitacaoId, $motivo);
                Session::setFlash('success', 'Revisão solicitada! O prestador foi notificado.', 'success');
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

    private function notificarRevisaoSolicitada($solicitacaoId, $motivo)
    {
        try {
            $sql = "SELECT p.prestador_id, s.titulo
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    WHERE s.id = ? AND p.status = 'aceita'";

            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $stmt->execute([$solicitacaoId]);
            $dados = $stmt->fetch();

            if ($dados) {
                require_once 'models/Notificacao.php';
                Notificacao::criarNotificacaoAutomatica(
                    'revisao_solicitada',
                    $dados['prestador_id'],
                    $solicitacaoId,
                    ['servico' => $dados['titulo']]
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao notificar revisão: " . $e->getMessage());
        }
    }
}
?>
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao notificar revisão: " . $e->getMessage());
        }
    }
}
?>
    