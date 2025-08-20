<?php
require_once 'models/Proposta.php';
require_once 'config/session.php';

class NegociacaoController {
    private $model;
    
    public function __construct() {
        $this->model = new Proposta();
        Session::requireClientLogin();
    }
    
    public function negociar() {
        $propostaId = $_GET['proposta_id'] ?? 0;
        $userId = Session::getUserId();
        
        $proposta = $this->model->buscarPropostaComNegociacao($propostaId, $userId);
        
        if (!$proposta) {
            Session::setFlash('error', 'Proposta não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/propostas/recebidas');
            exit;
        }
        
        // Verificar se usuário pode negociar
        $isCliente = ($proposta['cliente_id'] == $userId);
        $isPrestador = ($proposta['prestador_id'] == $userId);
        
        if (!$isCliente && !$isPrestador) {
            Session::setFlash('error', 'Acesso não autorizado!', 'danger');
            header('Location: /chamaservico/');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/negociacao/negociar?proposta_id=' . $propostaId);
                exit;
            }
            
            $acao = $_POST['acao'] ?? '';
            
            switch ($acao) {
                case 'contra_proposta':
                    $this->processarContraProposta($propostaId, $userId);
                    break;
                case 'aceitar_contra_proposta':
                    $this->aceitarContraProposta($propostaId, $userId);
                    break;
                case 'recusar_contra_proposta':
                    $this->recusarContraProposta($propostaId, $userId);
                    break;
            }
            
            header('Location: /chamaservico/negociacao/negociar?proposta_id=' . $propostaId);
            exit;
        }
        
        include 'views/negociacao/negociar.php';
    }
    
    private function processarContraProposta($propostaId, $clienteId) {
        $valor = $_POST['valor'] ?? 0;
        $prazo = $_POST['prazo'] ?? 0;
        $observacoes = trim($_POST['observacoes'] ?? '');
        
        if ($valor <= 0 || $prazo <= 0) {
            Session::setFlash('error', 'Valor e prazo são obrigatórios!', 'danger');
            return;
        }
        
        if ($this->model->criarContraProposta($propostaId, $clienteId, $valor, $prazo, $observacoes)) {
            Session::setFlash('success', 'Contra-proposta enviada com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao enviar contra-proposta!', 'danger');
        }
    }
    
    private function aceitarContraProposta($propostaId, $prestadorId) {
        $valor = $_POST['valor'] ?? 0;
        $prazo = $_POST['prazo'] ?? 0;
        $observacoes = trim($_POST['observacoes'] ?? '');
        
        if ($this->model->responderContraProposta($propostaId, $prestadorId, 'resposta_prestador', $valor, $prazo, $observacoes)) {
            Session::setFlash('success', 'Contra-proposta aceita! Sua proposta foi atualizada.', 'success');
        } else {
            Session::setFlash('error', 'Erro ao aceitar contra-proposta!', 'danger');
        }
    }
    
    private function recusarContraProposta($propostaId, $prestadorId) {
        $observacoes = trim($_POST['observacoes'] ?? '');
        
        if ($this->model->recusarContraProposta($propostaId, $prestadorId, $observacoes)) {
            Session::setFlash('success', 'Contra-proposta recusada.', 'info');
        } else {
            Session::setFlash('error', 'Erro ao recusar contra-proposta!', 'danger');
        }
    }
}
?>
