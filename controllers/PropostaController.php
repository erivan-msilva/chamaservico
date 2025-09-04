<?php
// Proteção contra múltiplas inclusões
if (defined('PROPOSTA_CONTROLLER_LOADED')) {
    return;
}
define('PROPOSTA_CONTROLLER_LOADED', true);

require_once 'models/Proposta.php';
require_once 'config/session.php';

class PropostaController
{
    private $model;

    public function __construct()
    {
        $this->model = new Proposta();
        Session::requirePrestadorLogin();
    }

    /**
     * Listar propostas do prestador
     */
    public function minhas()
    {
        $prestadorId = Session::getUserId();
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'ordenacao' => $_GET['ordenacao'] ?? 'data_desc'
        ];

        try {
            $propostas = $this->model->buscarPorPrestador($prestadorId, $filtros);
            
            // Buscar estatísticas
            $estatisticas = [
                'total' => $this->model->contarPropostasPorPrestador($prestadorId),
                'aceitas' => $this->model->contarPropostasAceitas($prestadorId),
                'pendentes' => count(array_filter($propostas, fn($p) => $p['status'] === 'pendente')),
                'recusadas' => count(array_filter($propostas, fn($p) => $p['status'] === 'recusada'))
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar propostas: " . $e->getMessage());
            $propostas = [];
            $estatisticas = ['total' => 0, 'aceitas' => 0, 'pendentes' => 0, 'recusadas' => 0];
            Session::setFlash('error', 'Erro ao carregar propostas!', 'danger');
        }

        $title = 'Minhas Propostas - Prestador';
        include 'views/prestador/propostas/index.php';
    }

    /**
     * Detalhes da proposta (prestador)
     */
    public function detalhes()
    {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();

        if (!$propostaId) {
            Session::setFlash('error', 'Proposta não informada!', 'danger');
            header('Location: prestador/propostas');
            exit;
        }

        try {
            $proposta = $this->model->buscarPropostaComNegociacao($propostaId, $prestadorId);

            if (!$proposta) {
                Session::setFlash('error', 'Proposta não encontrada ou acesso negado!', 'danger');
                header('Location: prestador/propostas');
                exit;
            }

            // Verificar se o usuário tem permissão (é o prestador da proposta)
            if ($proposta['prestador_id'] != $prestadorId) {
                Session::setFlash('error', 'Acesso negado!', 'danger');
                header('Location: prestador/propostas');
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da proposta: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao carregar detalhes!', 'danger');
            header('Location: prestador/propostas');
            exit;
        }

        $title = 'Detalhes da Proposta - Prestador';
        include 'views/prestador/propostas/detalhes.php';
    }

    /**
     * Cancelar proposta (prestador)
     */
    public function cancelar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: prestador/propostas');
            exit;
        }

        if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de segurança inválido!', 'danger');
            header('Location: prestador/propostas');
            exit;
        }

        $propostaId = $_POST['proposta_id'] ?? 0;
        $prestadorId = Session::getUserId();

        try {
            if ($this->model->cancelar($propostaId, $prestadorId)) {
                Session::setFlash('success', 'Proposta cancelada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao cancelar proposta!', 'danger');
            }
        } catch (Exception $e) {
            error_log("Erro ao cancelar proposta: " . $e->getMessage());
            Session::setFlash('error', 'Erro interno ao cancelar proposta!', 'danger');
        }

        header('Location:prestador/propostas');
        exit;
    }

    /**
     * Redirecionamento baseado no tipo de usuário
     */
    public function redirectToUserType()
    {
        if (Session::isCliente()) {
            $newPath = str_replace('/propostas/', '/cliente/propostas/', $_SERVER['REQUEST_URI']);
        } elseif (Session::isPrestador()) {
            $newPath = str_replace('/propostas/', '/prestador/propostas/', $_SERVER['REQUEST_URI']);
        } else {
            $newPath = '/chamaservico/login';
        }
        
        header("Location: $newPath", true, 301);
        exit;
    }
}
?>