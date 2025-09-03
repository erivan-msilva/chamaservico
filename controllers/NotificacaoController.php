<?php
require_once 'models/Notificacao.php';
require_once 'config/session.php';

class NotificacaoController
{
    private $model;

    public function __construct()
    {
        $this->model = new Notificacao();
        // CORRIGIDO: Usar o método correto
        Session::requireLogin();
    }

    /**
     * Lista todas as notificações do usuário
     */
    public function index()
    {
        $userId = Session::getUserId();
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        try {
            $notificacoes = $this->model->buscarPorUsuario($userId, $filtros);
            $estatisticas = $this->model->getEstatisticasUsuario($userId);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar notificações: " . $e->getMessage());
            $notificacoes = [];
            $estatisticas = ['total' => 0, 'nao_lidas' => 0, 'lidas' => 0];
            Session::setFlash('error', 'Erro ao carregar notificações!', 'danger');
        }

        $title = 'Notificações - ChamaServiço';
        include 'views/notificacoes/index.php';
    }

    /**
     * API: Contar notificações não lidas
     */
    public function contador()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::getUserId();
            $contador = $this->model->contarNaoLidas($userId);
            
            echo json_encode([
                'sucesso' => true,
                'contador' => $contador
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ]);
        }
        
        exit;
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarComoLida()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        header('Content-Type: application/json');
        
        $notificacaoId = $_POST['notificacao_id'] ?? 0;
        $userId = Session::getUserId();

        try {
            if ($this->model->marcarComoLida($notificacaoId, $userId)) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Falha ao marcar como lida']);
            }
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasComoLidas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/notificacoes');
            exit;
        }

        $userId = Session::getUserId();

        try {
            if ($this->model->marcarTodasComoLidas($userId)) {
                Session::setFlash('success', 'Todas as notificações foram marcadas como lidas!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao marcar notificações como lidas!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao marcar todas como lidas: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        header('Location: /chamaservico/notificacoes');
        exit;
    }

    /**
     * Deletar notificação
     */
    public function deletar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: /chamaservico/notificacoes');
            exit;
        }

        $notificacaoId = $_POST['notificacao_id'] ?? 0;
        $userId = Session::getUserId();

        try {
            if ($this->model->deletar($notificacaoId, $userId)) {
                Session::setFlash('success', 'Notificação removida!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao remover notificação!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        header('Location: /chamaservico/notificacoes');
        exit;
    }
}
?>
