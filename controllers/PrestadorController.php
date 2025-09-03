<?php
// Proteção contra múltiplas inclusões
if (defined('PRESTADOR_CONTROLLER_LOADED')) {
    return;
}
define('PRESTADOR_CONTROLLER_LOADED', true);

require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
require_once 'config/session.php';

class PrestadorController
{
    private $solicitacaoModel;
    private $propostaModel;

    public function __construct()
    {
        // CORRIGIDO: Usar o método correto
        Session::requirePrestadorLogin();
        
        // Inicializar models
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->propostaModel = new Proposta();
    }

    /**
     * Dashboard do prestador
     */
    public function dashboard()
    {
        $prestadorId = Session::getUserId();
        
        try {
            // Estatísticas gerais
            $estatisticas = [
                'total_propostas' => $this->propostaModel->contarPropostasPorPrestador($prestadorId),
                'propostas_aceitas' => $this->propostaModel->contarPropostasAceitas($prestadorId),
                'servicos_concluidos' => $this->propostaModel->contarServicosConcluidos($prestadorId),
                'avaliacao_media' => $this->propostaModel->obterAvaliacaoMedia($prestadorId)
            ];
            
            // Últimas propostas
            $ultimasPropostas = $this->propostaModel->buscarUltimasPropostas($prestadorId, 5);
            
            // Serviços em andamento
            $servicosAndamento = $this->propostaModel->buscarServicosEmAndamento($prestadorId, 3);
            
        } catch (Exception $e) {
            error_log("Erro no dashboard do prestador: " . $e->getMessage());
            $estatisticas = ['total_propostas' => 0, 'propostas_aceitas' => 0, 'servicos_concluidos' => 0, 'avaliacao_media' => '0.0'];
            $ultimasPropostas = [];
            $servicosAndamento = [];
            Session::setFlash('error', 'Erro ao carregar dados do dashboard!', 'danger');
        }

        $title = 'Dashboard - Prestador';
        include 'views/prestador/dashboard.php';
    }

    /**
     * API: Obter dados do dashboard via AJAX
     */
    public function getDashboardData()
    {
        header('Content-Type: application/json');
        
        $prestadorId = Session::getUserId();
        
        try {
            $data = [
                'success' => true,
                'estatisticas' => [
                    'total_propostas' => $this->propostaModel->contarPropostasPorPrestador($prestadorId),
                    'propostas_aceitas' => $this->propostaModel->contarPropostasAceitas($prestadorId),
                    'servicos_concluidos' => $this->propostaModel->contarServicosConcluidos($prestadorId),
                    'avaliacao_media' => $this->propostaModel->obterAvaliacaoMedia($prestadorId)
                ],
                'ultimas_propostas' => $this->propostaModel->buscarUltimasPropostas($prestadorId, 5),
                'servicos_andamento' => $this->propostaModel->buscarServicosEmAndamento($prestadorId, 3)
            ];
            
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar dados']);
        }
        
        exit;
    }

    /**
     * Listar solicitações disponíveis
     */
    public function solicitacoes()
    {
        $prestadorId = Session::getUserId();
        
        // Capturar filtros da URL
        $filtros = [
            'tipo_servico' => $_GET['tipo_servico'] ?? '',
            'urgencia' => $_GET['urgencia'] ?? '',
            'orcamento_min' => $_GET['orcamento_min'] ?? '',
            'orcamento_max' => $_GET['orcamento_max'] ?? '',
            'cidade' => $_GET['cidade'] ?? '',
            'page' => $_GET['page'] ?? 1
        ];

        try {
            // Buscar solicitações disponíveis
            $solicitacoes = $this->solicitacaoModel->buscarSolicitacoesDisponiveis(20, $filtros);
            
            // Para cada solicitação, verificar se já enviou proposta
            foreach ($solicitacoes as &$solicitacao) {
                $solicitacao['ja_enviou_proposta'] = $this->propostaModel->verificarPropostaExistente(
                    $solicitacao['id'], 
                    $prestadorId
                );
                $solicitacao['outras_propostas'] = $this->propostaModel->contarOutrasPropostas(
                    $solicitacao['id'], 
                    $prestadorId
                );
            }
            
            // Dados para filtros
            $tiposServico = $this->solicitacaoModel->getTiposServico();
            $cidades = $this->solicitacaoModel->getCidadesComSolicitacoes();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações: " . $e->getMessage());
            $solicitacoes = [];
            $tiposServico = [];
            $cidades = [];
            Session::setFlash('error', 'Erro ao carregar solicitações!', 'danger');
        }

        $title = 'Buscar Serviços - Prestador';
        include 'views/prestador/solicitacoes/listar.php';
    }

    /**
     * Visualizar detalhes de uma solicitação
     */
    public function detalheSolicitacao()
    {
        $solicitacaoId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();

        if (!$solicitacaoId) {
            Session::setFlash('error', 'Solicitação não informada!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }

        try {
            $solicitacao = $this->solicitacaoModel->buscarPorId($solicitacaoId);

            if (!$solicitacao || $solicitacao['status_id'] != 1) {
                Session::setFlash('error', 'Solicitação não encontrada ou não está mais disponível!', 'danger');
                header('Location: /chamaservico/prestador/solicitacoes');
                exit;
            }

            // Verificar se já enviou proposta
            $jaEnviouProposta = $this->propostaModel->verificarPropostaExistente($solicitacaoId, $prestadorId);
            $outrasPropostas = $this->propostaModel->contarOutrasPropostas($solicitacaoId, $prestadorId);

        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da solicitação: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar detalhes!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }

        $title = 'Detalhes da Solicitação - Prestador';
        include 'views/prestador/solicitacoes/detalhes.php';
    }

    /**
     * Enviar proposta para uma solicitação
     */
    public function enviarProposta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }

        $prestadorId = Session::getUserId();
        $solicitacaoId = $_POST['solicitacao_id'] ?? 0;
        $valor = $_POST['valor'] ?? 0;
        $descricao = trim($_POST['descricao'] ?? '');
        $prazoExecucao = $_POST['prazo_execucao'] ?? null;

        // Validações
        if (!$solicitacaoId || !$valor || !$descricao) {
            Session::setFlash('error', 'Preencha todos os campos obrigatórios!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes/detalhes?id=' . $solicitacaoId);
            exit;
        }

        if ($valor <= 0) {
            Session::setFlash('error', 'O valor deve ser maior que zero!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes/detalhes?id=' . $solicitacaoId);
            exit;
        }

        try {
            // Verificar se já enviou proposta
            if ($this->propostaModel->verificarPropostaExistente($solicitacaoId, $prestadorId)) {
                Session::setFlash('error', 'Você já enviou uma proposta para esta solicitação!', 'warning');
                header('Location: /chamaservico/prestador/solicitacoes/detalhes?id=' . $solicitacaoId);
                exit;
            }

            $dados = [
                'solicitacao_id' => $solicitacaoId,
                'prestador_id' => $prestadorId,
                'valor' => $valor,
                'descricao' => $descricao,
                'prazo_execucao' => $prazoExecucao
            ];

            if ($this->propostaModel->criar($dados)) {
                // Criar notificação para o cliente
                $this->criarNotificacaoNovaProposta($solicitacaoId, $prestadorId);
                
                Session::setFlash('success', 'Proposta enviada com sucesso!', 'success');
                header('Location: /chamaservico/prestador/propostas');
                exit;
            } else {
                Session::setFlash('error', 'Erro ao enviar proposta!', 'danger');
            }

        } catch (Exception $e) {
            error_log("Erro ao enviar proposta: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno ao enviar proposta!', 'danger');
        }

        header('Location: /chamaservico/prestador/solicitacoes/detalhes?id=' . $solicitacaoId);
        exit;
    }

    /**
     * Listar serviços em andamento
     */
    public function servicosAndamento()
    {
        $prestadorId = Session::getUserId();

        try {
            $servicos = $this->propostaModel->buscarServicosEmAndamento($prestadorId);
        } catch (Exception $e) {
            error_log("Erro ao buscar serviços em andamento: " . $e->getMessage());
            $servicos = [];
            Session::setFlash('error', 'Erro ao carregar serviços!', 'danger');
        }

        $title = 'Serviços em Andamento - Prestador';
        include 'views/prestador/servicos/andamento.php';
    }

    /**
     * Visualizar detalhes de um serviço em andamento
     */
    public function servicoDetalhes()
    {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();

        if (!$propostaId) {
            Session::setFlash('error', 'Serviço não informado!', 'danger');
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }

        try {
            $servico = $this->propostaModel->buscarDetalhesServicoAndamento($propostaId, $prestadorId);

            if (!$servico) {
                Session::setFlash('error', 'Serviço não encontrado ou você não tem permissão!', 'danger');
                header('Location: /chamaservico/prestador/servicos/andamento');
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes do serviço: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar detalhes!', 'danger');
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }

        $title = 'Detalhes do Serviço - Prestador';
        include 'views/prestador/servicos/detalhes.php';
    }

    /**
     * Atualizar status de um serviço
     */
    public function atualizarStatusServico()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }

        $propostaId = $_POST['proposta_id'] ?? 0;
        $novoStatus = $_POST['novo_status'] ?? '';
        $observacoes = trim($_POST['observacoes'] ?? '');
        $prestadorId = Session::getUserId();

        try {
            // USAR O MÉTODO DO MODELO PROPOSTA
            if ($this->propostaModel->atualizarStatusServico($propostaId, $prestadorId, $novoStatus, $observacoes)) {
                $statusMessages = [
                    'em_andamento' => 'Status atualizado para "Em Andamento"',
                    'concluido' => 'Serviço marcado como concluído! O cliente será notificado.',
                    'aguardando_materiais' => 'Status atualizado para "Aguardando Materiais"',
                    'suspenso' => 'Serviço suspenso temporariamente'
                ];

                $message = $statusMessages[$novoStatus] ?? 'Status atualizado com sucesso!';
                Session::setFlash('success', $message, 'success');
                
                // Se marcou como concluído, criar notificação para o cliente
                if ($novoStatus === 'concluido') {
                    $this->notificarConclusaoServico($propostaId, $observacoes);
                }
            } else {
                Session::setFlash('error', 'Erro ao atualizar status!', 'danger');
            }

        } catch (Exception $e) {
            error_log("Erro ao atualizar status do serviço: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        header('Location: /chamaservico/prestador/servicos/detalhes?id=' . $propostaId);
        exit;
    }

    /**
     * Notificar cliente sobre conclusão do serviço
     */
    private function notificarConclusaoServico($propostaId, $observacoes = '')
    {
        try {
            // Buscar dados da proposta
            $sql = "SELECT s.cliente_id, s.titulo, s.id as solicitacao_id
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    WHERE p.id = ?";
            
            $stmt = $this->propostaModel->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "✅ Serviço Concluído";
                $mensagem = "O prestador concluiu o serviço '{$dados['titulo']}'. Confirme a conclusão e avalie o trabalho realizado.";
                
                if ($observacoes) {
                    $mensagem .= "\n\nObservações: " . $observacoes;
                }
                
                $notificacaoModel->criarNotificacao(
                    $dados['cliente_id'],
                    $titulo,
                    $mensagem,
                    'servico_concluido',
                    $dados['solicitacao_id']
                );
                
                error_log("Notificação de conclusão criada para cliente: {$dados['cliente_id']}");
            }
            
        } catch (Exception $e) {
            error_log("Erro ao notificar conclusão: " . $e->getMessage());
        }
    }

    /**
     * Criar notificação de nova proposta para o cliente
     */
    private function criarNotificacaoNovaProposta($solicitacaoId, $prestadorId)
    {
        try {
            // Buscar dados da solicitação e prestador
            $sql = "SELECT s.cliente_id, s.titulo, p.nome as prestador_nome
                    FROM tb_solicita_servico s
                    JOIN tb_pessoa p ON p.id = ?
                    WHERE s.id = ?";

            $stmt = $this->propostaModel->db->prepare($sql);
            $stmt->execute([$prestadorId, $solicitacaoId]);
            $dados = $stmt->fetch();

            if ($dados && class_exists('Notificacao')) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();

                $titulo = "Nova proposta recebida!";
                $mensagem = "O prestador {$dados['prestador_nome']} enviou uma proposta para '{$dados['titulo']}'";

                $notificacaoModel->criarNotificacao(
                    $dados['cliente_id'],
                    $titulo,
                    $mensagem,
                    'nova_proposta',
                    $solicitacaoId
                );
            }

        } catch (Exception $e) {
            error_log("Erro ao criar notificação de nova proposta: " . $e->getMessage());
        }
    }
}
?>