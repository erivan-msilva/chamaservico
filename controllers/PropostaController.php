<?php
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

    // Exibe as propostas do prestador
    public function minhas()
    {
        $prestadorId = Session::getUserId();
        $filtros = [
            'status' => $_GET['status'] ?? ''
        ];
        $propostas = $this->model->buscarPorPrestador($prestadorId, $filtros);
        include 'views/prestador/propostas/minhas.php';
    }

    // Detalhes de uma proposta
    public function detalhes()
    {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();

        if (!$propostaId) {
            Session::setFlash('error', 'Proposta não informada!', 'danger');
            header('Location: /prestador/propostas');
            exit;
        }

        $proposta = $this->model->buscarPropostaComNegociacao($propostaId, $prestadorId);

        if (!$proposta) {
            Session::setFlash('error', 'Proposta não encontrada!', 'danger');
            header('Location: /prestador/propostas');
            exit;
        }

        include 'views/prestador/propostas/detalhes.php';
    }

    // Cancelar proposta
    public function cancelar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propostaId = $_POST['proposta_id'] ?? 0;
            $prestadorId = Session::getUserId();

            if ($this->model->cancelar($propostaId, $prestadorId)) {
                Session::setFlash('success', 'Proposta cancelada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao cancelar proposta!', 'danger');
            }
        }
        header('Location: /prestador/propostas');
        exit;
    }

    // Negociar proposta
    public function negociar()
    {
        // Implemente a lógica de negociação conforme necessário
        // ...
    }

    // Serviços em andamento
    public function servicosAndamento()
    {
        $prestadorId = Session::getUserId();
        $servicos = $this->model->buscarServicosEmAndamento($prestadorId);
        include 'views/prestador/servicos/andamento.php';
    }

    // Detalhes do serviço em andamento
    public function detalhesServico()
    {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();

        $servico = $this->model->buscarDetalhesServicoAndamento($propostaId, $prestadorId);

        if (!$servico) {
            Session::setFlash('error', 'Serviço não encontrado!', 'danger');
            header('Location: /prestador/servicos/andamento');
            exit;
        }

        include 'views/prestador/servicos/detalhes.php';
    }

    // Atualizar status do serviço
    public function atualizarStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propostaId = $_POST['proposta_id'] ?? 0;
            $prestadorId = Session::getUserId();
            $novoStatus = $_POST['novo_status'] ?? ''; // <-- CORRIGIDO: usar 'novo_status' ao invés de 'status'

            if ($this->model->atualizarStatusServico($propostaId, $prestadorId, $novoStatus)) {
                Session::setFlash('success', 'Status do serviço atualizado com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao atualizar status do serviço!', 'danger');
            }
        }
        header('Location: /prestador/servicos/andamento');
        exit;
    }

    // Avaliar serviço
    public function avaliar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propostaId = $_POST['proposta_id'] ?? 0;
            $prestadorId = Session::getUserId();
            $avaliacao = $_POST['avaliacao'] ?? 0;
            $comentario = trim($_POST['comentario'] ?? '');

            if ($avaliacao < 1 || $avaliacao > 5) {
                Session::setFlash('error', 'A avaliação deve ser entre 1 e 5 estrelas.', 'danger');
                header('Location: /prestador/servicos/detalhes?id=' . $propostaId);
                exit;
            }

            // CORREÇÃO: Verifique se o método existe antes de chamar
            if (method_exists($this->model, 'avaliarServico')) {
                if ($this->model->avaliarServico($propostaId, $prestadorId, $avaliacao, $comentario)) {
                    Session::setFlash('success', 'Serviço avaliado com sucesso!', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao avaliar serviço!', 'danger');
                }
            } else {
                Session::setFlash('error', 'Método avaliarServico não implementado no model Proposta.', 'danger');
            }
        }
        header('Location: /prestador/servicos/andamento');
        exit;
    }

}
