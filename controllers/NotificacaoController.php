<?php
require_once 'models/Notificacao.php';
require_once 'config/session.php';

class NotificacaoController {
    private $model;
    
    public function __construct() {
        $this->model = new Notificacao();
        Session::requireClientLogin();
    }
    
    public function index() {
        $userId = Session::getUserId();
        $notificacoes = $this->model->buscarPorUsuario($userId);
        
        include 'views/notificacoes/index.php';
    }
    
    public function contador() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $userId = Session::getUserId();
                $contador = $this->model->contarNaoLidas($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'contador_nao_lidas' => $contador
                ]);
                exit;
            } catch (Exception $e) {
                error_log("Erro ao buscar contador: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Erro interno']);
                exit;
            }
        }
    }
    
    public function marcarComoLida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificacaoId = $_POST['id'] ?? 0;
            $userId = Session::getUserId();
            
            $success = $this->model->marcarComoLida($notificacaoId, $userId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        }
    }
    
    public function marcarTodasComoLidas() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = Session::getUserId();
                error_log("Marcando todas as notificações como lidas para usuário $userId");
                
                if ($this->model->marcarTodasComoLidas($userId)) {
                    error_log("Todas as notificações marcadas como lidas");
                    Session::setFlash('success', 'Todas as notificações foram marcadas como lidas!', 'success');
                } else {
                    error_log("Falha ao marcar todas como lidas");
                    Session::setFlash('error', 'Erro ao marcar notificações como lidas!', 'danger');
                }
            } catch (Exception $e) {
                error_log("Erro ao marcar todas como lidas: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno do sistema!', 'danger');
            }
            
            header('Location: /chamaservico/notificacoes');
            exit;
        }
    }
    
    public function deletar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $notificacaoId = $_POST['notificacao_id'] ?? 0;
                $userId = Session::getUserId();
                error_log("Tentando deletar notificação $notificacaoId para usuário $userId");
                
                if ($this->model->deletarNotificacao($notificacaoId, $userId)) {
                    // Buscar novo contador
                    $novoContador = $this->model->contarNaoLidas($userId);
                    error_log("Notificação deletada. Novo contador: $novoContador");
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'contador_nao_lidas' => $novoContador
                    ]);
                } else {
                    error_log("Falha ao deletar notificação");
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Falha ao deletar']);
                }
            } catch (Exception $e) {
                error_log("Erro ao deletar notificação: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Erro interno']);
            }
            exit;
        }
    }
}
?>
        