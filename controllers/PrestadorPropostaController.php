<?php
require_once 'models/Proposta.php';
require_once 'models/Notificacao.php';
require_once 'config/session.php';

class PrestadorPropostaController {
    private $propostaModel;
    private $notificacaoModel;
    
    public function __construct() {
        $this->propostaModel = new Proposta();
        $this->notificacaoModel = new Notificacao();
        Session::requirePrestadorLogin();
    }
    
    public function enviar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: prestador/solicitacoes');
                exit;
            }
            
            $dados = [
                'solicitacao_id' => $_POST['solicitacao_id'] ?? 0,
                'prestador_id' => Session::getUserId(),
                'valor' => $_POST['valor'] ?? 0,
                'descricao' => $_POST['descricao'] ?? '',
                'prazo_execucao' => $_POST['prazo_execucao'] ?? 1
            ];
            
            try {
                $propostaId = $this->propostaModel->criar($dados);
                
                if ($propostaId) {
                    // Buscar dados da solicitação para notificação
                    require_once 'models/SolicitacaoServico.php';
                    $solicitacaoModel = new SolicitacaoServico();
                    $solicitacao = $solicitacaoModel->buscarPorId($dados['solicitacao_id']);
                    
                    if ($solicitacao) {
                        // Criar notificação para o cliente
                        $titulo = 'Nova Proposta Recebida!';
                        $mensagem = "Você recebeu uma nova proposta para '{$solicitacao['titulo']}'.\n\n";
                        $mensagem .= "Valor: R$ " . number_format($dados['valor'], 2, ',', '.') . "\n";
                        $mensagem .= "Prazo: {$dados['prazo_execucao']} dia(s)\n";
                        $mensagem .= "Descrição: {$dados['descricao']}";
                        
                        $this->notificacaoModel->criarNotificacao(
                            $solicitacao['cliente_id'],
                            $titulo,
                            $mensagem,
                            'nova_proposta',
                            $propostaId
                        );
                    }
                    
                    Session::setFlash('success', 'Proposta enviada com sucesso!', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao enviar proposta!', 'danger');
                }
                
            } catch (Exception $e) {
                error_log("Erro ao enviar proposta: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: prestador/propostas');
        exit;
    }
}
?>
