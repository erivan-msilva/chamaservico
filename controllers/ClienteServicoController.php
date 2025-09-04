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
            header('Location: ' . url('acesso-negado'));
            exit;
        }
    }

    public function concluidos()
    {
        $clienteId = Session::getUserId();
        
        try {
            // Buscar serviços concluídos do cliente
            $sql = "SELECT s.*, ts.nome as tipo_servico_nome, 
                           p.valor as valor_pago, p.prestador_id,
                           pr.nome as prestador_nome, pr.telefone as prestador_telefone,
                           e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                           a.nota as avaliacao_nota, a.comentario as avaliacao_comentario,
                           a.id as avaliacao_id
                    FROM tb_solicita_servico s
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    JOIN tb_endereco e ON s.endereco_id = e.id
                    JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    LEFT JOIN tb_avaliacao a ON s.id = a.solicitacao_id AND a.avaliador_id = ?
                    WHERE s.cliente_id = ? AND s.status_id = 5
                    ORDER BY s.data_solicitacao DESC";
            
            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $stmt->execute([$clienteId, $clienteId]);
            $servicosConcluidos = $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Erro ao buscar serviços concluídos: " . $e->getMessage());
            $servicosConcluidos = [];
            Session::setFlash('error', 'Erro ao carregar serviços concluídos!', 'danger');
        }

        $title = 'Serviços Concluídos - ChamaServiço';
        include 'views/cliente/servicos/concluidos.php';
    }

    public function avaliar()
    {
        $solicitacaoId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        if (!$solicitacaoId) {
            Session::setFlash('error', 'Serviço não informado!', 'danger');
            header('Location: ' . url('cliente/servicos/concluidos'));
            exit;
        }

        try {
            // Verificar se o serviço existe e está concluído
            $sql = "SELECT s.*, ts.nome as tipo_servico_nome,
                           p.prestador_id, pr.nome as prestador_nome,
                           a.id as ja_avaliado
                    FROM tb_solicita_servico s
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    JOIN tb_proposta p ON s.id = p.solicitacao_id AND p.status = 'aceita'
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    LEFT JOIN tb_avaliacao a ON s.id = a.solicitacao_id AND a.avaliador_id = ?
                    WHERE s.id = ? AND s.cliente_id = ? AND s.status_id = 5";

            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $stmt->execute([$clienteId, $solicitacaoId, $clienteId]);
            $servico = $stmt->fetch();

            if (!$servico) {
                Session::setFlash('error', 'Serviço não encontrado ou não pode ser avaliado!', 'danger');
                header('Location: ' . url('cliente/servicos/concluidos'));
                exit;
            }

            if ($servico['ja_avaliado']) {
                Session::setFlash('error', 'Este serviço já foi avaliado!', 'warning');
                header('Location: ' . url('cliente/servicos/concluidos'));
                exit;
            }

            // Processar avaliação
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processarAvaliacao($solicitacaoId, $clienteId, $servico);
            }

        } catch (Exception $e) {
            error_log("Erro ao buscar serviço para avaliação: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar dados do serviço!', 'danger');
            header('Location: ' . url('cliente/servicos/concluidos'));
            exit;
        }

        $title = 'Avaliar Serviço - ChamaServiço';
        include 'views/cliente/servicos/avaliar.php';
    }

    private function processarAvaliacao($solicitacaoId, $clienteId, $servico)
    {
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: ' . url('cliente/servicos/avaliar?id=' . $solicitacaoId));
            exit;
        }

        $nota = $_POST['nota'] ?? 0;
        $comentario = trim($_POST['comentario'] ?? '');

        // Validações
        if ($nota < 1 || $nota > 5) {
            Session::setFlash('error', 'Nota deve ser entre 1 e 5!', 'danger');
            header('Location: ' . url('cliente/servicos/avaliar?id=' . $solicitacaoId));
            exit;
        }

        try {
            // Inserir avaliação
            $sql = "INSERT INTO tb_avaliacao (solicitacao_id, avaliador_id, avaliado_id, nota, comentario) 
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->solicitacaoModel->db->prepare($sql);
            $resultado = $stmt->execute([
                $solicitacaoId,
                $clienteId,
                $servico['prestador_id'],
                $nota,
                $comentario
            ]);

            if ($resultado) {
                // Criar notificação para o prestador
                require_once 'models/Notificacao.php';
                $titulo = '⭐ Nova Avaliação Recebida!';
                $mensagem = "Você recebeu uma avaliação de {$nota} estrela(s) para o serviço '{$servico['titulo']}'";
                if ($comentario) {
                    $mensagem .= "\n\nComentário: " . $comentario;
                }

                $notificacaoModel = new Notificacao();
                $notificacaoModel->criarNotificacao(
                    $servico['prestador_id'],
                    $titulo,
                    $mensagem,
                    'avaliacao_recebida',
                    $solicitacaoId
                );

                Session::setFlash('success', 'Avaliação enviada com sucesso! Obrigado pelo feedback.', 'success');
                header('Location: ' . url('cliente/servicos/concluidos'));
                exit;
            } else {
                Session::setFlash('error', 'Erro ao enviar avaliação!', 'danger');
            }

        } catch (Exception $e) {
            error_log("Erro ao processar avaliação: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno ao processar avaliação!', 'danger');
        }

        header('Location: ' . url('cliente/servicos/avaliar?id=' . $solicitacaoId));
        exit;
    }

    public function confirmarConclusao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: ' . url('cliente/servicos/concluidos'));
                exit;
            }

            $solicitacaoId = $_POST['solicitacao_id'] ?? 0;
            $clienteId = Session::getUserId();

            try {
                // Verificar se o serviço pertence ao cliente
                $sql = "SELECT id FROM tb_solicita_servico WHERE id = ? AND cliente_id = ? AND status_id = 5";
                $stmt = $this->solicitacaoModel->db->prepare($sql);
                $stmt->execute([$solicitacaoId, $clienteId]);

                if ($stmt->fetch()) {
                    Session::setFlash('success', 'Conclusão do serviço confirmada!', 'success');
                } else {
                    Session::setFlash('error', 'Serviço não encontrado!', 'danger');
                }

            } catch (Exception $e) {
                error_log("Erro ao confirmar conclusão: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno!', 'danger');
            }
        }

        header('Location: ' . url('cliente/servicos/concluidos'));
        exit;
    }
}
?>
        