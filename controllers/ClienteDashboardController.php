<?php
require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
require_once 'models/Notificacao.php';
require_once 'config/session.php';

class ClienteDashboardController
{
    private $solicitacaoModel;
    private $propostaModel;
    private $notificacaoModel;

    public function __construct()
    {
        Session::requireClientLogin();
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->propostaModel = new Proposta();
        $this->notificacaoModel = new Notificacao();
    }

    public function index()
    {
        $userId = Session::getUserId();

        $estatisticas = $this->getEstatisticas($userId);
        $solicitacoesRecentes = $this->solicitacaoModel->buscarPorUsuario($userId, ['limit' => 5]);
        $propostasRecentes = $this->propostaModel->buscarPropostasRecebidas($userId, ['limit' => 5]);
        $notificacoesRecentes = $this->notificacaoModel->buscarPorUsuario($userId, ['limit' => 5]);

        include 'views/cliente/dashboard.php';
    }

    public function getEstatisticas($userId)
    {
        $totalSolicitacoes = $this->solicitacaoModel->contarSolicitacoesPorUsuario($userId);
        $aguardandoPropostas = $this->solicitacaoModel->contarSolicitacoesPorUsuarioEStatus($userId, 1);
        $emAndamento = $this->solicitacaoModel->contarSolicitacoesPorUsuarioEStatus($userId, 3) +
            $this->solicitacaoModel->contarSolicitacoesPorUsuarioEStatus($userId, 4);
        $concluidas = $this->solicitacaoModel->contarSolicitacoesPorUsuarioEStatus($userId, 5);
        $propostasPendentes = $this->propostaModel->contarPropostasPorStatusECliente($userId, 'pendente');
        $propostasAceitas = $this->propostaModel->contarPropostasPorStatusECliente($userId, 'aceita');

        return [
            'total_solicitacoes' => $totalSolicitacoes,
            'aguardando_propostas' => $aguardandoPropostas,
            'em_andamento' => $emAndamento,
            'concluidas' => $concluidas,
            'propostas_pendentes' => $propostasPendentes,
            'propostas_aceitas' => $propostasAceitas
        ];
    }

    public function getDashboardData()
    {
        $userId = Session::getUserId();
        $estatisticas = $this->getEstatisticas($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $estatisticas
        ]);
        exit;
    }
}
