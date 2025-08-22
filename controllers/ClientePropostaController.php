<?php
require_once 'models/Proposta.php';
require_once 'config/session.php';

class ClientePropostaController {
    private $model;
    
    public function __construct() {
        $this->model = new Proposta();
        Session::requireClientLogin();
        
        // Verificar se é cliente
        if (!Session::isCliente()) {
            header('Location: /chamaservico/acesso-negado');
            exit;
        }
    }
    
    public function recebidas() {
        $clienteId = Session::getUserId();
        $filtros = [
            'solicitacao_id' => $_GET['solicitacao_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        try {
            $propostas = $this->model->buscarPropostasRecebidas($clienteId, $filtros);
            
            // Buscar solicitações do cliente para o filtro
            require_once 'models/SolicitacaoServico.php';
            $solicitacaoModel = new SolicitacaoServico();
            $solicitacoes = $solicitacaoModel->buscarPorUsuario($clienteId);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar propostas: " . $e->getMessage());
            $propostas = [];
            $solicitacoes = [];
            Session::setFlash('error', 'Erro ao carregar propostas!', 'danger');
        }
        
        include 'views/cliente/propostas/recebidas.php';
    }
    
    public function comparar() {
        $solicitacaoId = $_GET['solicitacao_id'] ?? 0;
        $clienteId = Session::getUserId();
        
        if (!$solicitacaoId) {
            Session::setFlash('error', 'Solicitação não informada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        try {
            $propostas = $this->model->buscarPropostasRecebidas($clienteId, ['solicitacao_id' => $solicitacaoId]);
            
            if (empty($propostas)) {
                Session::setFlash('error', 'Nenhuma proposta encontrada para esta solicitação!', 'warning');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            // Buscar dados da solicitação
            require_once 'models/SolicitacaoServico.php';
            $solicitacaoModel = new SolicitacaoServico();
            $solicitacao = $solicitacaoModel->buscarPorId($solicitacaoId, $clienteId);
            
        } catch (Exception $e) {
            error_log("Erro ao comparar propostas: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar dados para comparação!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        include 'views/cliente/propostas/comparar.php';
    }
    
    public function detalhes() {
        $propostaId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();
        
        if (!$propostaId) {
            Session::setFlash('error', 'Proposta não informada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        try {
            $proposta = $this->model->buscarPorId($propostaId);
            
            if (!$proposta) {
                Session::setFlash('error', 'Proposta não encontrada!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            // Verificar se a proposta pertence ao cliente logado
            require_once 'models/SolicitacaoServico.php';
            $solicitacaoModel = new SolicitacaoServico();
            $solicitacao = $solicitacaoModel->buscarPorId($proposta['solicitacao_id'], $clienteId);
            
            if (!$solicitacao) {
                Session::setFlash('error', 'Você não tem permissão para ver esta proposta!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da proposta: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar detalhes da proposta!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        include 'views/cliente/propostas/detalhes.php';
    }
    
    public function aceitar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $clienteId = Session::getUserId();
            
            try {
                if ($this->model->aceitarProposta($propostaId, $clienteId)) {
                    // Buscar dados da proposta para notificação
                    $proposta = $this->model->buscarPorId($propostaId);
                    
                    if ($proposta) {
                        // Criar notificação para o prestador
                        require_once 'models/Notificacao.php';
                        Notificacao::criarNotificacaoAutomatica(
                            'proposta_aceita',
                            $proposta['prestador_id'],
                            $propostaId,
                            ['servico' => $proposta['titulo']]
                        );
                        
                        // Recusar outras propostas da mesma solicitação
                        $this->model->recusarOutrasPropostas($proposta['solicitacao_id'], $propostaId);
                    }
                    
                    Session::setFlash('success', 'Proposta aceita com sucesso! O prestador foi notificado.', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao aceitar proposta!', 'danger');
                }
            } catch (Exception $e) {
                error_log("Erro ao aceitar proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno ao aceitar proposta!', 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }
    
    public function recusar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $motivo = trim($_POST['motivo_recusa'] ?? '');
            $clienteId = Session::getUserId();
            
            try {
                if ($this->model->recusarProposta($propostaId, $clienteId)) {
                    // Buscar dados da proposta para notificação
                    $proposta = $this->model->buscarPorId($propostaId);
                    
                    if ($proposta) {
                        // Criar notificação para o prestador
                        require_once 'models/Notificacao.php';
                        $titulo = "Proposta recusada";
                        $mensagem = "Sua proposta para '{$proposta['titulo']}' foi recusada";
                        if ($motivo) {
                            $mensagem .= ". Motivo: " . $motivo;
                        }
                        
                        $notificacaoModel = new Notificacao();
                        $notificacaoModel->criarNotificacao(
                            $proposta['prestador_id'],
                            $titulo,
                            $mensagem,
                            'proposta_recusada',
                            $propostaId
                        );
                    }
                    
                    Session::setFlash('success', 'Proposta recusada!', 'info');
                } else {
                    Session::setFlash('error', 'Erro ao recusar proposta!', 'danger');
                }
            } catch (Exception $e) {
                error_log("Erro ao recusar proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno ao recusar proposta!', 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }
}
?>
