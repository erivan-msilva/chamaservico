<?php
require_once 'models/Proposta.php';
require_once 'models/Notificacao.php';
require_once 'config/session.php';

class ClientePropostaController {
    private $propostaModel;
    private $notificacaoModel;
    
    public function __construct() {
        $this->propostaModel = new Proposta();
        $this->notificacaoModel = new Notificacao();
        Session::requireClientLogin();
    }
    
    public function recebidas() {
        if (!Session::isCliente()) {
            header('Location: /chamaservico/acesso-negado');
            exit;
        }
        
        $clienteId = Session::getUserId();
        $filtros = [
            'solicitacao_id' => $_GET['solicitacao_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $propostas = $this->propostaModel->buscarPropostasRecebidas($clienteId, $filtros);
        $solicitacoes = $this->propostaModel->buscarSolicitacoesCliente($clienteId);
        $estatisticas = $this->propostaModel->getEstatisticasPropostasCliente($clienteId);
        
        include 'views/cliente/propostas/recebidas.php';
    }
    
    public function comparar() {
        $solicitacaoId = $_GET['solicitacao_id'] ?? 0;
        $clienteId = Session::getUserId();
        
        if (!$solicitacaoId) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        // Verificar se a solicitação pertence ao cliente
        $solicitacao = $this->propostaModel->verificarSolicitacaoCliente($solicitacaoId, $clienteId);
        if (!$solicitacao) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        $propostas = $this->propostaModel->buscarPropostasPorSolicitacao($solicitacaoId);
        $solicitacao = $this->propostaModel->buscarSolicitacaoPorId($solicitacaoId, $clienteId);
        
        include 'views/cliente/propostas/comparar.php';
    }
    
    public function detalhes() {
        $propostaId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        if (!$propostaId) {
            Session::setFlash('error', 'Proposta não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }

        // CORREÇÃO: Buscar proposta apenas se pertence ao cliente logado
        $proposta = $this->propostaModel->buscarDetalheProposta($propostaId, $clienteId);

        if (!$proposta) {
            Session::setFlash('error', 'Proposta não encontrada!', 'danger');
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
            $observacoes = $_POST['observacoes'] ?? '';
            
            try {
                // Buscar dados da proposta antes de aceitar
                $proposta = $this->propostaModel->buscarDetalheProposta($propostaId, $clienteId);
                
                if (!$proposta) {
                    Session::setFlash('error', 'Proposta não encontrada!', 'danger');
                    header('Location: /chamaservico/cliente/propostas/recebidas');
                    exit;
                }
                
                // Aceitar a proposta
                $resultado = $this->propostaModel->aceitarProposta($propostaId, $clienteId, $observacoes);
                
                if ($resultado) {
                    // NOTIFICAÇÃO PARA O PRESTADOR - Garantir que seja criada
                    $this->criarNotificacaoPropostaAceita($proposta, $observacoes);
                    
                    Session::setFlash('success', 'Proposta aceita com sucesso! O prestador foi notificado.', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao aceitar proposta!', 'danger');
                }
                
            } catch (Exception $e) {
                error_log("Erro ao aceitar proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }

    // MÉTODO MELHORADO para criar notificação de proposta aceita
    private function criarNotificacaoPropostaAceita($proposta, $observacoes = '') {
        try {
            // Buscar nome do cliente
            $sql = "SELECT nome FROM tb_pessoa WHERE id = ?";
            $stmt = $this->notificacaoModel->db->prepare($sql);
            $stmt->execute([$proposta['cliente_id'] ?? Session::getUserId()]);
            $cliente = $stmt->fetch();
            $nomeCliente = $cliente['nome'] ?? 'Cliente';
            
            $titulo = '🎉 Proposta Aceita!';
            $mensagem = "Parabéns! Sua proposta de R$ " . number_format($proposta['valor'], 2, ',', '.') . 
                       " para o serviço '{$proposta['solicitacao_titulo']}' foi aceita por {$nomeCliente}!\n\n";
            
            $mensagem .= "📋 Detalhes da Proposta:\n";
            $mensagem .= "• Valor: R$ " . number_format($proposta['valor'], 2, ',', '.') . "\n";
            $mensagem .= "• Prazo: " . ($proposta['prazo_execucao'] ?? 'A combinar') . " dia(s)\n";
            $mensagem .= "• Endereço: {$proposta['logradouro']}, {$proposta['numero']} - {$proposta['bairro']}, {$proposta['cidade']}\n\n";
            
            if (!empty($observacoes)) {
                $mensagem .= "💬 Observações do cliente:\n" . $observacoes . "\n\n";
            }
            
            $mensagem .= "📞 Entre em contato com o cliente para combinar os detalhes do serviço.\n";
            $mensagem .= "✅ O serviço agora está em andamento!";
            
            // Criar notificação
            $resultado = $this->notificacaoModel->criarNotificacao(
                $proposta['prestador_id'],
                $titulo,
                $mensagem,
                'proposta_aceita',
                $proposta['id']
            );
            
            if ($resultado) {
                error_log("Notificação de proposta aceita criada com sucesso para prestador ID: {$proposta['prestador_id']}");
            } else {
                error_log("Falha ao criar notificação de proposta aceita para prestador ID: {$proposta['prestador_id']}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de proposta aceita: " . $e->getMessage());
            return false;
        }
    }
    
    public function recusar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $motivo = $_POST['motivo_recusa'] ?? '';
            $clienteId = Session::getUserId();
            
            try {
                // Buscar dados da proposta antes de recusar
                $proposta = $this->propostaModel->buscarDetalheProposta($propostaId, $clienteId);
                
                if (!$proposta) {
                    Session::setFlash('error', 'Proposta não encontrada!', 'danger');
                    header('Location: /chamaservico/cliente/propostas/recebidas');
                    exit;
                }
                
                // Recusar a proposta
                $resultado = $this->propostaModel->recusarProposta($propostaId, $clienteId, $motivo);
                
                if ($resultado) {
                    // CORREÇÃO: Usar o método correto criarNotificacao
                    $titulo = 'Proposta Recusada';
                    $mensagem = "Sua proposta para '{$proposta['solicitacao_titulo']}' foi recusada pelo cliente.";
                    
                    if (!empty($motivo)) {
                        $mensagem .= "\n\nMotivo: " . $motivo;
                    }
                    
                    $this->notificacaoModel->criarNotificacao(
                        $proposta['prestador_id'],
                        $titulo,
                        $mensagem,
                        'proposta_recusada',
                        $propostaId
                    );
                    
                    Session::setFlash('success', 'Proposta recusada!', 'info');
                } else {
                    Session::setFlash('error', 'Erro ao recusar proposta!', 'danger');
                }
                
            } catch (Exception $e) {
                error_log("Erro ao recusar proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }
    
    // NOVO: Criar contra-proposta
    public function criarContraProposta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/propostas/recebidas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $valor = $_POST['valor'] ?? 0;
            $prazo = $_POST['prazo'] ?? 0;
            $observacoes = $_POST['observacoes'] ?? '';
            $clienteId = Session::getUserId();
            
            try {
                // Verificar se a proposta existe e pertence ao cliente
                $proposta = $this->propostaModel->buscarDetalheProposta($propostaId, $clienteId);
                
                if (!$proposta) {
                    Session::setFlash('error', 'Proposta não encontrada!', 'danger');
                    header('Location: /chamaservico/cliente/propostas/recebidas');
                    exit;
                }
                
                // Criar contra-proposta
                $resultado = $this->propostaModel->criarContraProposta($propostaId, $clienteId, $valor, $prazo, $observacoes);
                
                if ($resultado) {
                    // Notificar prestador sobre a contra-proposta
                    $titulo = 'Nova Contra-Proposta';
                    $mensagem = "O cliente fez uma contra-proposta para '{$proposta['solicitacao_titulo']}':\n\n";
                    $mensagem .= "Valor: R$ " . number_format($valor, 2, ',', '.') . "\n";
                    $mensagem .= "Prazo: {$prazo} dia(s)\n";
                    
                    if (!empty($observacoes)) {
                        $mensagem .= "Observações: {$observacoes}";
                    }
                    
                    $this->notificacaoModel->criarNotificacao(
                        $proposta['prestador_id'],
                        $titulo,
                        $mensagem,
                        'contra_proposta',
                        $propostaId
                    );
                    
                    Session::setFlash('success', 'Contra-proposta enviada com sucesso!', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao enviar contra-proposta!', 'danger');
                }
                
            } catch (Exception $e) {
                error_log("Erro ao criar contra-proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }
}
?>
