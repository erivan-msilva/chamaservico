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
            header('Location: prestador/propostas');
            exit;
        }

        $proposta = $this->model->buscarPropostaComNegociacao($propostaId, $prestadorId);

        if (!$proposta) {
            Session::setFlash('error', 'Proposta não encontrada!', 'danger');
            header('Location: prestador/propostas');
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
        header('Location: prestador/propostas');
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
            header('Location: prestador/servicos/andamento');
            exit;
        }

        include 'views/prestador/servicos/detalhes.php';
    }

    // Atualizar status do serviço
    public function atualizarStatus()
    {
        // Implemente a lógica de atualização de status conforme necessário
        // ...
    }
}
