<?php
require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
require_once 'config/session.php';

class PrestadorController {
    private $solicitacaoModel;
    private $propostaModel;
    
    public function __construct() {
        $this->solicitacaoModel = new SolicitacaoServico();
        $this->propostaModel = new Proposta();
        // CORRIGIDO: Usar método correto para verificar acesso do prestador
        Session::requirePrestadorLogin();
    }
    
    public function dashboard() {
        $prestadorId = Session::getUserId();
        
        // Buscar estatísticas
        $stats = $this->getDashboardStats($prestadorId);
        $graficos = $this->getDashboardCharts($prestadorId);
        
        include 'views/prestador/dashboard.php';
    }
    
    private function getDashboardStats($prestadorId) {
        $stats = [];
        
        // Propostas enviadas
        $sql = "SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = ?";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $stats['propostas_enviadas'] = $stmt->fetchColumn();
        
        // Propostas aceitas
        $sql = "SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = ? AND status = 'aceita'";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $stats['propostas_aceitas'] = $stmt->fetchColumn();
        
        // Valor total das propostas aceitas
        $sql = "SELECT COALESCE(SUM(valor), 0) FROM tb_proposta WHERE prestador_id = ? AND status = 'aceita'";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $stats['valor_total_aceitas'] = $stmt->fetchColumn();
        
        // Avaliação média
        $sql = "SELECT COALESCE(AVG(nota), 0) FROM tb_avaliacao WHERE avaliado_id = ?";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $stats['avaliacao_media'] = round($stmt->fetchColumn(), 1);
        
        return $stats;
    }
    
    private function getDashboardCharts($prestadorId) {
        $graficos = [];
        
        // Propostas por mês (últimos 6 meses)
        $sql = "SELECT 
                    DATE_FORMAT(data_proposta, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM tb_proposta 
                WHERE prestador_id = ? 
                    AND data_proposta >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(data_proposta, '%Y-%m')
                ORDER BY mes";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $graficos['propostas_mes'] = $stmt->fetchAll();
        
        // Status das propostas
        $sql = "SELECT status, COUNT(*) as total 
                FROM tb_proposta 
                WHERE prestador_id = ? 
                GROUP BY status";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $graficos['status_propostas'] = $stmt->fetchAll();
        
        return $graficos;
    }
    
    public function solicitacoes() {
        $filtros = [
            'tipo_servico' => $_GET['tipo_servico'] ?? '',
            'urgencia' => $_GET['urgencia'] ?? '',
            'cidade' => $_GET['cidade'] ?? '',
            'orcamento_min' => $_GET['orcamento_min'] ?? '',
            'orcamento_max' => $_GET['orcamento_max'] ?? '',
            'page' => $_GET['page'] ?? 1
        ];
        
        $solicitacoes = $this->solicitacaoModel->buscarSolicitacoesDisponiveis(20, $filtros);
        $tiposServico = $this->solicitacaoModel->getTiposServico();
        $cidades = $this->solicitacaoModel->getCidadesComSolicitacoes();
        
        include 'views/prestador/solicitacoes/listar.php';
    }
    
    public function detalheSolicitacao() {
        $id = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();
        
        $solicitacao = $this->solicitacaoModel->buscarPorId($id);
        if (!$solicitacao || $solicitacao['status_id'] != 1) {
            Session::setFlash('error', 'Solicitação não encontrada ou não disponível!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }
        
        // Verificar se já enviou proposta
        $jaEnviouProposta = $this->propostaModel->verificarPropostaExistente($id, $prestadorId);
        
        // Contar outras propostas
        $outrasPropostas = $this->propostaModel->contarPropostasPorSolicitacao($id);
        
        include 'views/prestador/solicitacoes/detalhes.php';
    }
    
    public function enviarProposta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/solicitacoes');
                exit;
            }
            
            $dados = [
                'solicitacao_id' => $_POST['solicitacao_id'],
                'prestador_id' => Session::getUserId(),
                'valor' => $_POST['valor'],
                'descricao' => $_POST['descricao'],
                'prazo_execucao' => $_POST['prazo_execucao']
            ];
            
            if ($this->propostaModel->enviarProposta($dados)) {
                Session::setFlash('success', 'Proposta enviada com sucesso!', 'success');
                header('Location: /chamaservico/prestador/propostas');
            } else {
                Session::setFlash('error', 'Erro ao enviar proposta!', 'danger');
                header('Location: /chamaservico/prestador/solicitacoes');
            }
            exit;
        }
    }
    
    // NOVO: Método para API do dashboard
    public function getDashboardData() {
        $prestadorId = Session::getUserId();
        
        try {
            $stats = $this->getDashboardStats($prestadorId);
            $graficos = $this->getDashboardCharts($prestadorId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'graficos' => $graficos
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // NOVO: Serviços em andamento
    public function servicosAndamento() {
        $prestadorId = Session::getUserId();
        
        // Buscar propostas aceitas (serviços em andamento)
        $sql = "SELECT p.*, s.titulo, s.descricao, s.data_atendimento,
                       c.nome as cliente_nome, c.telefone as cliente_telefone,
                       e.logradouro, e.numero, e.bairro, e.cidade, e.estado,
                       st.nome as status_nome, st.cor as status_cor
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                WHERE p.prestador_id = ? AND p.status = 'aceita'
                ORDER BY s.data_atendimento ASC";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $servicosAndamento = $stmt->fetchAll();
        
        include 'views/prestador/servicos/andamento.php';
    }
    
    // NOVO: Detalhes do serviço
    public function servicoDetalhes() {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();
        
        // Buscar detalhes do serviço
        $sql = "SELECT p.*, s.titulo, s.descricao, s.data_atendimento, s.orcamento_estimado,
                       c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep,
                       st.nome as status_nome, st.cor as status_cor,
                       ts.nome as tipo_servico_nome
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_status_solicitacao st ON s.status_id = st.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                WHERE p.id = ? AND p.prestador_id = ?";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$propostaId, $prestadorId]);
        $servico = $stmt->fetch();
        
        if (!$servico) {
            Session::setFlash('error', 'Serviço não encontrado!', 'danger');
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }
        
        // Buscar imagens da solicitação
        $servico['imagens'] = $this->solicitacaoModel->buscarImagensPorSolicitacao($servico['solicitacao_id']);
        
        include 'views/prestador/servicos/detalhes.php';
    }
    
    // NOVO: Atualizar status do serviço
    public function atualizarStatusServico() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propostaId = $_POST['proposta_id'] ?? 0;
            $novoStatus = $_POST['novo_status'] ?? '';
            $observacoes = $_POST['observacoes'] ?? '';
            $prestadorId = Session::getUserId();
            
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/servicos/andamento');
                exit;
            }
            
            try {
                // Atualizar status da solicitação
                $sql = "UPDATE tb_solicita_servico s
                        JOIN tb_proposta p ON s.id = p.solicitacao_id
                        SET s.status_id = ?
                        WHERE p.id = ? AND p.prestador_id = ?";
                
                $stmt = $this->propostaModel->db->prepare($sql);
                $resultado = $stmt->execute([$novoStatus, $propostaId, $prestadorId]);
                
                if ($resultado) {
                    // Criar notificação para o cliente
                    require_once 'models/Notificacao.php';
                    $notificacaoModel = new Notificacao();
                    
                    // Buscar dados do cliente
                    $sqlCliente = "SELECT s.cliente_id, s.titulo, st.nome as status_nome
                                   FROM tb_solicita_servico s
                                   JOIN tb_proposta p ON s.id = p.solicitacao_id
                                   JOIN tb_status_solicitacao st ON s.status_id = st.id
                                   WHERE p.id = ?";
                    
                    $stmtCliente = $this->propostaModel->db->prepare($sqlCliente);
                    $stmtCliente->execute([$propostaId]);
                    $dadosCliente = $stmtCliente->fetch();
                    
                    if ($dadosCliente) {
                        $titulo = "Status do serviço atualizado";
                        $mensagem = "O prestador atualizou o status do serviço '{$dadosCliente['titulo']}' para: {$dadosCliente['status_nome']}";
                        
                        if (!empty($observacoes)) {
                            $mensagem .= "\n\nObservações: " . $observacoes;
                        }
                        
                        $notificacaoModel->criarNotificacao(
                            $dadosCliente['cliente_id'],
                            $titulo,
                            $mensagem,
                            'status_atualizado',
                            $propostaId
                        );
                    }
                    
                    Session::setFlash('success', 'Status do serviço atualizado com sucesso!', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao atualizar status do serviço!', 'danger');
                }
            } catch (Exception $e) {
                Session::setFlash('error', 'Erro interno: ' . $e->getMessage(), 'danger');
            }
            
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }
    }
}
?>