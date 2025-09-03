<?php
namespace App\Controllers\Api;

use App\Models\SolicitacaoServico;
use App\Core\Response;

class ServiceController
{
    private $solicitacaoModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacaoServico();
    }

    public function getAllServices()
    {
        $services = $this->solicitacaoModel->buscarSolicitacoesDisponiveis();
        Response::json(['success' => true, 'data' => $services]);
    }
}
