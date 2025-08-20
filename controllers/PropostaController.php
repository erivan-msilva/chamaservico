<?php
require_once 'models/Proposta.php';
require_once 'config/session.php';

class PropostaController {
    private $model;
    
    public function __construct() {
        $this->model = new Proposta();
        Session::requireClientLogin();
    }
    
    // Para clientes
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
        
        $propostas = $this->model->buscarPropostasRecebidas($clienteId, $filtros);
        $solicitacoes = $this->model->buscarSolicitacoesCliente($clienteId);
        
        include 'views/cliente/propostas/recebidas.php';
    }
    
    // Para prestadores - buscar propostas enviadas
    public function minhas() {
        if (!Session::isPrestador()) {
            header('Location: /chamaservico/acesso-negado');
            exit;
        }
        
        $prestadorId = Session::getUserId();
        $filtros = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $propostas = $this->model->buscarPorPrestador($prestadorId, $filtros);
        
        include 'views/prestador/propostas/minhas.php';
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
            $solicitacaoId = $_POST['solicitacao_id'] ?? 0;
            
            if ($this->model->aceitarProposta($propostaId, $clienteId, $solicitacaoId)) {
                // Criar notificação para o prestador
                $this->criarNotificacaoAceite($propostaId);
                
                Session::setFlash('success', 'Proposta aceita com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao aceitar proposta!', 'danger');
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
            $motivo = $_POST['motivo_recusa'] ?? '';
            $clienteId = Session::getUserId();
            
            if ($this->model->recusarProposta($propostaId, $clienteId, $motivo)) {
                // Criar notificação para o prestador
                $this->criarNotificacaoRecusa($propostaId, $motivo);
                
                Session::setFlash('success', 'Proposta recusada!', 'info');
            } else {
                Session::setFlash('error', 'Erro ao recusar proposta!', 'danger');
            }
        }
        
        header('Location: /chamaservico/cliente/propostas/recebidas');
        exit;
    }
    
    private function criarNotificacaoAceite($propostaId) {
        try {
            // Buscar dados da proposta
            $sql = "SELECT p.prestador_id, s.titulo, c.nome as cliente_nome, p.valor
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa c ON s.cliente_id = c.id
                    WHERE p.id = ?";
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Proposta aceita!";
                $mensagem = "Sua proposta de R$ " . number_format($dados['valor'], 2, ',', '.') . " para '{$dados['titulo']}' foi aceita por {$dados['cliente_nome']}
";
                
                $notificacaoModel->criarNotificacao(
                    $dados['prestador_id'],
                    $titulo,
                    $mensagem,
                    'proposta_aceita',
                    $propostaId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de aceite: " . $e->getMessage());
        }
    }
    
    private function criarNotificacaoRecusa($propostaId, $motivo) {
        try {
            // Buscar dados da proposta
            $sql = "SELECT p.prestador_id, s.titulo, c.nome as cliente_nome
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa c ON s.cliente_id = c.id
                    WHERE p.id = ?";
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Proposta recusada";
                $mensagem = "Sua proposta para '{$dados['titulo']}' foi recusada por {$dados['cliente_nome']}";
                if ($motivo) {
                    $mensagem .= ". Motivo: " . $motivo;
                }
                
                $notificacaoModel->criarNotificacao(
                    $dados['prestador_id'],
                    $titulo,
                    $mensagem,
                    'proposta_recusada',
                    $propostaId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação de recusa: " . $e->getMessage());
        }
    }
    
    public function cancelar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/propostas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $prestadorId = Session::getUserId();
            
            if ($this->model->cancelar($propostaId, $prestadorId)) {
                Session::setFlash('success', 'Proposta cancelada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao cancelar proposta!', 'danger');
            }
        }
        
        header('Location: /chamaservico/prestador/propostas');
        exit;
    }
    
    // Novo: Redirecionamento baseado no tipo de usuário
    public function redirectToUserType() {
        if (Session::isCliente()) {
            $currentPath = $_SERVER['REQUEST_URI'];
            $newPath = str_replace('/chamaservico/propostas', '/chamaservico/cliente/propostas', $currentPath);
            header("Location: $newPath", true, 301);
        } elseif (Session::isPrestador()) {
            $currentPath = $_SERVER['REQUEST_URI'];
            $newPath = str_replace('/chamaservico/propostas', '/chamaservico/prestador/propostas', $currentPath);
            header("Location: $newPath", true, 301);
        } else {
            header('Location: /chamaservico/acesso-negado');
        }
        exit;
    }
}
?>
