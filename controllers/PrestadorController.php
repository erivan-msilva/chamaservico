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
        Session::requirePrestadorAccess();
    }
    
    public function dashboard() {
        $prestadorId = Session::getUserId();
        
        // Buscar estatísticas do prestador
        $stats = [
            'propostas_enviadas' => $this->propostaModel->contarPropostasPorPrestador($prestadorId),
            'propostas_aceitas' => $this->propostaModel->contarPropostasAceitas($prestadorId),
            'servicos_concluidos' => $this->propostaModel->contarServicosConcluidos($prestadorId),
            'avaliacao_media' => $this->propostaModel->obterAvaliacaoMedia($prestadorId),
            'valor_total_aceitas' => $this->calcularValorTotalAceitas($prestadorId),
            'valor_medio_aceitas' => $this->calcularValorMedioAceitas($prestadorId),
            'taxa_conversao' => $this->calcularTaxaConversao($prestadorId)
        ];
        
        // Buscar últimas propostas
        $ultimasPropostas = $this->propostaModel->buscarUltimasPropostas($prestadorId, 5);
        
        // Buscar serviços em andamento
        $servicosAndamento = $this->propostaModel->buscarServicosEmAndamento($prestadorId, 3);
        
        // Dados para gráficos (simulados por enquanto)
        $graficos = [
            'propostas_mes' => $this->getDadosPropostasPorMes($prestadorId),
            'status_propostas' => $this->getDadosStatusPropostas($prestadorId),
            'valores_mes' => $this->getDadosValoresPorMes($prestadorId),
            'conversao_mes' => $this->getDadosConversaoMes($prestadorId),
            'comparacao_mercado' => [],
            'tipos_servico' => []
        ];
        
        // Alertas para o dashboard
        $alertas = $this->gerarAlertas($prestadorId, $stats);
        
        include 'views/prestador/dashboard.php';
    }
    
    private function calcularValorTotalAceitas($prestadorId) {
        $sql = "SELECT SUM(valor) FROM tb_proposta WHERE prestador_id = ? AND status = 'aceita'";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    private function calcularValorMedioAceitas($prestadorId) {
        $sql = "SELECT AVG(valor) FROM tb_proposta WHERE prestador_id = ? AND status = 'aceita'";
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    private function calcularTaxaConversao($prestadorId) {
        $total = $this->propostaModel->contarPropostasPorPrestador($prestadorId);
        $aceitas = $this->propostaModel->contarPropostasAceitas($prestadorId);
        
        if ($total > 0) {
            return ($aceitas / $total) * 100;
        }
        return 0;
    }
    
    private function getDadosPropostasPorMes($prestadorId) {
        $sql = "SELECT DATE_FORMAT(data_proposta, '%Y-%m') as mes, COUNT(*) as total
                FROM tb_proposta 
                WHERE prestador_id = ? AND data_proposta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_proposta, '%Y-%m')
                ORDER BY mes";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchAll();
    }
    
    private function getDadosStatusPropostas($prestadorId) {
        $sql = "SELECT status, COUNT(*) as quantidade
                FROM tb_proposta 
                WHERE prestador_id = ?
                GROUP BY status";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchAll();
    }
    
    private function getDadosValoresPorMes($prestadorId) {
        $sql = "SELECT DATE_FORMAT(data_proposta, '%Y-%m') as mes, SUM(valor) as total
                FROM tb_proposta 
                WHERE prestador_id = ? AND status = 'aceita' AND data_proposta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_proposta, '%Y-%m')
                ORDER BY mes";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchAll();
    }
    
    private function getDadosConversaoMes($prestadorId) {
        $sql = "SELECT 
                    DATE_FORMAT(data_proposta, '%Y-%m') as mes,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'aceita' THEN 1 ELSE 0 END) as aceitas,
                    ROUND((SUM(CASE WHEN status = 'aceita' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as taxa
                FROM tb_proposta 
                WHERE prestador_id = ? AND data_proposta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_proposta, '%Y-%m')
                ORDER BY mes";
        
        $stmt = $this->propostaModel->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchAll();
    }
    
    private function gerarAlertas($prestadorId, $stats) {
        $alertas = [];
        
        // Verificar se há poucas propostas
        if ($stats['propostas_enviadas'] < 5) {
            $alertas[] = [
                'tipo' => 'warning',
                'icone' => 'exclamation-triangle',
                'titulo' => 'Poucas Propostas',
                'mensagem' => 'Você enviou poucas propostas. Explore mais oportunidades para aumentar seus ganhos!'
            ];
        }
        
        // Verificar taxa de conversão baixa
        if ($stats['taxa_conversao'] < 20 && $stats['propostas_enviadas'] > 5) {
            $alertas[] = [
                'tipo' => 'info',
                'icone' => 'lightbulb',
                'titulo' => 'Melhore sua Taxa de Conversão',
                'mensagem' => 'Sua taxa de conversão está baixa. Tente melhorar suas propostas com descrições mais detalhadas.'
            ];
        }
        
        // Verificar se há serviços em andamento
        if (!empty($servicosAndamento)) {
            $alertas[] = [
                'tipo' => 'success',
                'icone' => 'check-circle',
                'titulo' => 'Serviços em Andamento',
                'mensagem' => 'Você tem ' . count($servicosAndamento) . ' serviço(s) em andamento. Continue o bom trabalho!'
            ];
        }
        
        return $alertas;
    }
    
    public function solicitacoes() {
        $filtros = [
            'tipo_servico' => $_GET['tipo_servico'] ?? '',
            'urgencia' => $_GET['urgencia'] ?? '',
            'orcamento_min' => $_GET['orcamento_min'] ?? '',
            'orcamento_max' => $_GET['orcamento_max'] ?? '',
            'cidade' => $_GET['cidade'] ?? '',
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
        
        // Buscar detalhes da solicitação
        $solicitacao = $this->solicitacaoModel->buscarPorId($id);
        if (!$solicitacao) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: /chamaservico/prestador/solicitacoes');
            exit;
        }
        
        // Verificar se já enviou proposta
        $jaEnviouProposta = $this->propostaModel->verificarPropostaExistente($id, $prestadorId);
        
        // Contar outras propostas
        $outrasPropostas = $this->propostaModel->contarOutrasPropostas($id, $prestadorId);
        
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
                'descricao' => trim($_POST['descricao']),
                'prazo_execucao' => $_POST['prazo_execucao']
            ];
            
            if ($this->propostaModel->criar($dados)) {
                // Criar notificação para o cliente
                $this->criarNotificacaoProposta($dados['solicitacao_id'], $dados['prestador_id']);
                
                Session::setFlash('success', 'Proposta enviada com sucesso!', 'success');
                header('Location: /chamaservico/prestador/propostas');
            } else {
                Session::setFlash('error', 'Erro ao enviar proposta!', 'danger');
                header('Location: /chamaservico/prestador/solicitacoes');
            }
            exit;
        }
    }
    
    private function criarNotificacaoProposta($solicitacaoId, $prestadorId) {
        try {
            // Buscar dados da solicitação e cliente
            $sql = "SELECT s.cliente_id, s.titulo, p.nome as prestador_nome 
                    FROM tb_solicita_servico s 
                    JOIN tb_pessoa p ON p.id = ? 
                    WHERE s.id = ?";
            $stmt = $this->propostaModel->db->prepare($sql);
            $stmt->execute([$prestadorId, $solicitacaoId]);
            $dados = $stmt->fetch();
            
            if ($dados) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();
                
                $titulo = "Nova proposta recebida";
                $mensagem = "Você recebeu uma nova proposta de {$dados['prestador_nome']} para '{$dados['titulo']}'";
                
                $notificacaoModel->criarNotificacao(
                    $dados['cliente_id'],
                    $titulo,
                    $mensagem,
                    'nova_proposta',
                    $solicitacaoId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
        }
    }
    
    public function minhasPropostas() {
        $prestadorId = Session::getUserId();
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'page' => $_GET['page'] ?? 1
        ];
        
        $propostas = $this->propostaModel->buscarPorPrestador($prestadorId, $filtros);
        
        include 'views/prestador/propostas/minhas.php';
    }
    
    public function cancelarProposta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/propostas');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $prestadorId = Session::getUserId();
            
            if ($this->propostaModel->cancelar($propostaId, $prestadorId)) {
                Session::setFlash('success', 'Proposta cancelada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao cancelar proposta!', 'danger');
            }
        }
        
        header('Location: /chamaservico/prestador/propostas');
        exit;
    }
    
    public function servicosAndamento() {
        $prestadorId = Session::getUserId();
        $servicosAndamento = $this->propostaModel->buscarServicosEmAndamento($prestadorId);
        
        include 'views/prestador/servicos/andamento.php';
    }
    
    public function servicoDetalhes() {
        $propostaId = $_GET['id'] ?? 0;
        $prestadorId = Session::getUserId();
        
        $servico = $this->propostaModel->buscarDetalhesServicoAndamento($propostaId, $prestadorId);
        
        if (!$servico) {
            Session::setFlash('error', 'Serviço não encontrado!', 'danger');
            header('Location: /chamaservico/prestador/servicos/andamento');
            exit;
        }
        
        include 'views/prestador/servicos/detalhes.php';
    }
    
    public function atualizarStatusServico() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/prestador/servicos/andamento');
                exit;
            }
            
            $propostaId = $_POST['proposta_id'] ?? 0;
            $novoStatus = $_POST['status'] ?? '';
            $observacoes = $_POST['observacoes'] ?? '';
            $prestadorId = Session::getUserId();
            
            // Validar dados de entrada
            if (empty($propostaId) || empty($novoStatus)) {
                Session::setFlash('error', 'Dados inválidos fornecidos!', 'danger');
                header('Location: /chamaservico/prestador/servicos/andamento');
                exit;
            }
            
            // Validar status permitidos
            $statusPermitidos = ['em_andamento', 'concluido', 'aguardando_materiais', 'suspenso'];
            if (!in_array($novoStatus, $statusPermitidos)) {
                Session::setFlash('error', 'Status inválido!', 'danger');
                header('Location: /chamaservico/prestador/servicos/andamento');
                exit;
            }
            
            try {
                if ($this->propostaModel->atualizarStatusServico($propostaId, $prestadorId, $novoStatus, $observacoes)) {
                    $mensagens = [
                        'em_andamento' => 'Serviço marcado como em andamento!',
                        'concluido' => 'Serviço marcado como concluído! Uma Ordem de Serviço foi gerada.',
                        'aguardando_materiais' => 'Status atualizado para aguardando materiais.',
                        'suspenso' => 'Serviço marcado como suspenso temporariamente.'
                    ];
                    
                    Session::setFlash('success', $mensagens[$novoStatus] ?? 'Status atualizado com sucesso!', 'success');
                } else {
                    Session::setFlash('error', 'Erro ao atualizar status do serviço! Verifique se você tem permissão para esta ação.', 'danger');
                }
            } catch (Exception $e) {
                error_log("Erro no controller ao atualizar status: " . $e->getMessage());
                Session::setFlash('error', 'Erro interno do sistema. Tente novamente.', 'danger');
            }
        }
        
        header('Location: /chamaservico/prestador/servicos/andamento');
        exit;
    }
}
?>