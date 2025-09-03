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
        $solicitacaoId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        if (!$solicitacaoId) {
            Session::setFlash('error', 'Serviço não informado!', 'danger');
            header('Location: /chamaservico/cliente/servicos/concluidos');
            exit;
        }

        try {
            // Verificar se o serviço pertence ao cliente e está concluído
            $servico = $this->solicitacaoModel->buscarPorId($solicitacaoId, $clienteId);
            
            if (!$servico || $servico['status_id'] != 5) {
                Session::setFlash('error', 'Serviço não encontrado ou não está concluído!', 'danger');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

            // Buscar proposta aceita
            $propostas = $this->propostaModel->buscarPropostasRecebidas($clienteId, [
                'solicitacao_id' => $solicitacaoId,
                'status' => 'aceita'
            ]);

            if (empty($propostas)) {
                Session::setFlash('error', 'Nenhuma proposta aceita encontrada!', 'danger');
                header('Location: /chamaservico/cliente/servicos/concluidos');
                exit;
            }

            $proposta = $propostas[0];

            // Processar formulário de avaliação
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                    Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                    header('Location: /chamaservico/cliente/servicos/avaliar?id=' . $solicitacaoId);
                    exit;
                }

                $nota = $_POST['nota'] ?? 0;
                $comentario = trim($_POST['comentario'] ?? '');

                if ($nota < 1 || $nota > 5) {
                    Session::setFlash('error', 'Nota deve estar entre 1 e 5!', 'danger');
                    header('Location: /chamaservico/cliente/servicos/avaliar?id=' . $solicitacaoId);
                    exit;
                }

                // Salvar avaliação
                if ($this->salvarAvaliacao($solicitacaoId, $clienteId, $proposta['prestador_id'], $nota, $comentario)) {
                    Session::setFlash('success', 'Avaliação salva com sucesso!', 'success');
                    header('Location: /chamaservico/cliente/servicos/concluidos');
                    exit;
                } else {
                    Session::setFlash('error', 'Erro ao salvar avaliação!', 'danger');
                }
            }

        } catch (Exception $e) {
            error_log("Erro ao avaliar serviço: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
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
