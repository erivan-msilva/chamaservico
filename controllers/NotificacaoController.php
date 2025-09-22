<?php
// Inclui as dependências necessárias
require_once 'models/Notificacao.php';
require_once 'config/session.php';

// Controlador para gerenciar notificações
class NotificacaoController
{
    // Instância do modelo Notificacao
    private $model;

    // Construtor da classe
    public function __construct()
    {
        // Inicializa o modelo
        $this->model = new Notificacao();
        // Garante que o usuário esteja logado
        Session::requireLogin();
    }

    /**
     * Lista todas as notificações do usuário
     * @return void
     */
    public function index()
    {
        // Obtém o ID do usuário da sessão
        $userId = Session::getUserId();
        // Define filtros para busca de notificações
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        try {
            // Busca notificações e estatísticas do usuário
            $notificacoes = $this->model->buscarPorUsuario($userId, $filtros);
            $estatisticas = $this->model->getEstatisticasUsuario($userId);
        } catch (Exception $e) {
            // Registra erro e define valores padrão
            error_log("Erro ao buscar notificações: " . $e->getMessage());
            $notificacoes = [];
            $estatisticas = ['total' => 0, 'nao_lidas' => 0, 'lidas' => 0];
            Session::setFlash('error', 'Erro ao carregar notificações!', 'danger');
        }

        // Define o título da página e inclui a view
        $title = 'Notificações - ChamaServiço';
        include 'views/notificacoes/index.php';
    }

    /**
     * API: Conta notificações não lidas do usuário
     * @return void
     */
    public function contador()
    {
        // Define o tipo de conteúdo da resposta como JSON
        header('Content-Type: application/json');

        try {
            // Obtém o ID do usuário e conta notificações não lidas
            $userId = Session::getUserId();
            $contador = $this->model->contarNaoLidas($userId);

            // Retorna resposta de sucesso
            echo json_encode([
                'sucesso' => true,
                'contador' => $contador
            ]);
        } catch (Exception $e) {
            // Retorna resposta de erro
            echo json_encode([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Marca uma notificação como lida via AJAX
     * @return void
     */
    public function marcarComoLida()
    {
        // Define o tipo de conteúdo da resposta como JSON
        header('Content-Type: application/json');

        // Valida o método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
            exit;
        }

        // Valida o token CSRF
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Token de segurança inválido ou expirado.']);
            exit;
        }

        // Obtém dados da requisição
        $notificacaoId = $_POST['notificacao_id'] ?? 0;
        $userId = Session::getUserId();

        // Valida os dados recebidos
        if (!$notificacaoId || !$userId) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos.']);
            exit;
        }

        try {
            // Tenta marcar a notificação como lida
            if ($this->model->marcarComoLida($notificacaoId, $userId)) {
                echo json_encode(['sucesso' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['sucesso' => false, 'erro' => 'A notificação não foi encontrada ou pertence a outro usuário.']);
            }
        } catch (Exception $e) {
            // Registra erro e retorna resposta de erro
            error_log("Erro em marcarComoLida: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Ocorreu um erro interno no servidor.']);
        }

        exit;
    }

    /**
     * Marca todas as notificações do usuário como lidas
     * @return void
     */
    public function marcarTodasComoLidas()
    {
        // Valida o método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        // Valida o token CSRF
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: notificacoes');
            exit;
        }

        // Obtém o ID do usuário
        $userId = Session::getUserId();

        try {
            // Tenta marcar todas as notificações como lidas
            if ($this->model->marcarTodasComoLidas($userId)) {
                Session::setFlash('success', 'Todas as notificações foram marcadas como lidas!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao marcar notificações como lidas!', 'danger');
            }
        } catch (Exception $e) {
            // Registra erro e define mensagem de erro
            error_log("Erro ao marcar todas como lidas: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno!', 'danger');
        }

        // Redireciona para a página de notificações
        header('Location: notificacoes');
        exit;
    }

    /**
     * Deleta uma notificação via AJAX
     * @return void
     */
    public function deletar()
    {
        // Define o tipo de conteúdo da resposta como JSON
        header('Content-Type: application/json');

        // Valida o método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
            exit;
        }

        // Valida o token CSRF
        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Token de segurança inválido ou expirado.']);
            exit;
        }

        // Obtém dados da requisição
        $notificacaoId = $_POST['notificacao_id'] ?? 0;
        $userId = Session::getUserId();

        // Valida os dados recebidos
        if (!$notificacaoId || !$userId) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos.']);
            exit;
        }

        try {
            // Tenta deletar a notificação
            if ($this->model->deletar($notificacaoId, $userId)) {
                echo json_encode(['sucesso' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['sucesso' => false, 'erro' => 'A notificação não foi encontrada ou pertence a outro usuário.']);
            }
        } catch (Exception $e) {
            // Registra erro e retorna resposta de erro
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Ocorreu um erro interno no servidor.']);
        }

        exit;
    }

    /**
     * Marca todas as notificações como lidas (método legado)
     * @return void
     */
    public function marcarTodasLidas()
    {
        // Obtém o ID do usuário da sessão
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId) {
            // Instancia o modelo e marca todas as notificações como lidas
            $notificacao = new Notificacao();
            $notificacao->marcarTodasComoLidas($usuarioId);
        }

        // Redireciona para a página de notificações
        header('Location: /notificacoes');
        exit;
    }
}
