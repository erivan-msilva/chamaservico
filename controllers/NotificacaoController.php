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
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'lida' => $_GET['lida'] ?? ''
        ];
        
        $notificacoes = $this->model->buscarPorUsuario($userId, 50, $filtros);
        $estatisticas = $this->model->getEstatisticas($userId);
        
        include 'views/notificacoes/index.php';
    }
    
    // API para buscar contador de notificações não lidas
    public function contador() {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::getUserId();
            $naoLidas = $this->model->contarNaoLidas($userId);
            
            echo json_encode([
                'sucesso' => true,
                'contador' => (int)$naoLidas
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    // API para buscar notificações recentes
    public function recentes() {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::getUserId();
            $limit = $_GET['limit'] ?? 5;
            $ultimaVerificacao = $_GET['ultima_verificacao'] ?? null;
            
            $sql = "SELECT * FROM tb_notificacao 
                    WHERE pessoa_id = ?";
            $params = [$userId];
            
            if ($ultimaVerificacao) {
                $sql .= " AND data_notificacao > ?";
                $params[] = $ultimaVerificacao;
            }
            
            $sql .= " ORDER BY data_notificacao DESC LIMIT ?";
            $params[] = (int)$limit;
            
            $stmt = $this->model->db->prepare($sql);
            $stmt->execute($params);
            $notificacoes = $stmt->fetchAll();
            
            echo json_encode([
                'sucesso' => true,
                'notificacoes' => $notificacoes,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function marcarComoLida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $notificacaoId = $_POST['notificacao_id'] ?? 0;
                $userId = Session::getUserId();
                
                $resultado = $this->model->marcarComoLida($notificacaoId, $userId);
                
                echo json_encode([
                    'sucesso' => $resultado,
                    'mensagem' => $resultado ? 'Notificação marcada como lida' : 'Erro ao marcar como lida'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ]);
            }
            exit;
        }
    }
    
    public function marcarTodasComoLidas() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $userId = Session::getUserId();
                $resultado = $this->model->marcarTodasComoLidas($userId);
                
                echo json_encode([
                    'sucesso' => $resultado,
                    'mensagem' => $resultado ? 'Todas as notificações foram marcadas como lidas' : 'Erro ao marcar notificações'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ]);
            }
            exit;
        }
    }
    
    public function deletar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $notificacaoId = $_POST['notificacao_id'] ?? 0;
                $userId = Session::getUserId();
                
                $resultado = $this->model->deletar($notificacaoId, $userId);
                
                echo json_encode([
                    'sucesso' => $resultado,
                    'mensagem' => $resultado ? 'Notificação excluída' : 'Erro ao excluir notificação'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ]);
            }
            exit;
        }
    }
}
?>
