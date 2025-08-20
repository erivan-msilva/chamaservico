<?php
require_once 'models/Avaliacao.php';
require_once 'config/session.php';

class AvaliacaoController {
    private $model;
    
    public function __construct() {
        $this->model = new Avaliacao();
        Session::requireClientLogin();
    }
    
    public function avaliar() {
        $solicitacaoId = $_GET['solicitacao_id'] ?? 0;
        $userId = Session::getUserId();
        
        // Verificar se pode avaliar
        $servico = $this->model->buscarServicoParaAvaliacao($solicitacaoId, $userId);
        if (!$servico) {
            Session::setFlash('error', 'Serviço não encontrado ou não pode ser avaliado!', 'danger');
            header('Location: /chamaservico/solicitacoes');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/avaliacao/avaliar?solicitacao_id=' . $solicitacaoId);
                exit;
            }
            
            $dados = [
                'solicitacao_id' => $solicitacaoId,
                'avaliador_id' => $userId,
                'avaliado_id' => $servico['prestador_id'],
                'nota' => $_POST['nota'],
                'comentario' => trim($_POST['comentario'] ?? '')
            ];
            
            if ($this->model->criarAvaliacao($dados)) {
                Session::setFlash('success', 'Avaliação enviada com sucesso!', 'success');
                header('Location: /chamaservico/solicitacoes');
                exit;
            } else {
                Session::setFlash('error', 'Erro ao enviar avaliação!', 'danger');
            }
        }
        
        include 'views/avaliacoes/criar.php';
    }
}
?>
