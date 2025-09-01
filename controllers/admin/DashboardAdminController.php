<?php
require_once 'controllers/admin/BaseAdminController.php';
require_once 'core/Database.php';

class DashboardAdminController extends BaseAdminController {
    private $db;
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $stats = $this->getDashboardStats();
            $atividadesRecentes = $this->getAtividadesRecentes();
            $alertas = $this->getAlertas();
            $graficos = $this->getGraficos();
            
            $this->renderView('dashboard', compact('stats', 'atividadesRecentes', 'alertas', 'graficos'));
        } catch (Exception $e) {
            $erro = 'Erro ao carregar dashboard: ' . $e->getMessage();
            $this->renderView('dashboard', compact('erro'));
        }
    }
    
    public function getDashboardData() {
        header('Content-Type: application/json');
        
        try {
            $data = [
                'stats' => $this->getDashboardStats(),
                'atividades' => $this->getAtividadesRecentes(5),
                'alertas' => $this->getAlertas()
            ];
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    private function getDashboardStats() {
        try {
            // Total de usuários
            $sql = "SELECT COUNT(*) as total FROM tb_pessoa WHERE ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalUsuarios = $stmt->fetchColumn();
            
            // Total de solicitações
            $sql = "SELECT COUNT(*) as total FROM tb_solicita_servico";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalSolicitacoes = $stmt->fetchColumn();
            
            // Total de propostas
            $sql = "SELECT COUNT(*) as total FROM tb_proposta";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalPropostas = $stmt->fetchColumn();
            
            // Usuários ativos (último acesso nos últimos 30 dias)
            $sql = "SELECT COUNT(*) as total FROM tb_pessoa 
                    WHERE ativo = 1 AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $usuariosAtivos = $stmt->fetchColumn();
            
            // Serviços concluídos
            $sql = "SELECT COUNT(*) as total FROM tb_solicita_servico WHERE status_id = 5";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $servicosConcluidos = $stmt->fetchColumn();
            
            // Valor total transacionado
            $sql = "SELECT COALESCE(SUM(valor), 0) as total FROM tb_proposta WHERE status = 'aceita'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $valorTransacionado = $stmt->fetchColumn();
            
            // Novos usuários hoje
            $sql = "SELECT COUNT(*) as total FROM tb_pessoa WHERE DATE(data_cadastro) = CURDATE()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $novosUsuariosHoje = $stmt->fetchColumn();
            
            // Solicitações pendentes
            $sql = "SELECT COUNT(*) as total FROM tb_solicita_servico WHERE status_id = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $solicitacoesPendentes = $stmt->fetchColumn();
            
            return [
                'total_usuarios' => (int)$totalUsuarios,
                'total_solicitacoes' => (int)$totalSolicitacoes,
                'total_propostas' => (int)$totalPropostas,
                'usuarios_ativos' => (int)$usuariosAtivos,
                'servicos_concluidos' => (int)$servicosConcluidos,
                'valor_transacionado' => (float)$valorTransacionado,
                'novos_usuarios_hoje' => (int)$novosUsuariosHoje,
                'solicitacoes_pendentes' => (int)$solicitacoesPendentes
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas do dashboard: " . $e->getMessage());
            // Retornar valores padrão em caso de erro
            return [
                'total_usuarios' => 0,
                'total_solicitacoes' => 0,
                'total_propostas' => 0,
                'usuarios_ativos' => 0,
                'servicos_concluidos' => 0,
                'valor_transacionado' => 0.0,
                'novos_usuarios_hoje' => 0,
                'solicitacoes_pendentes' => 0
            ];
        }
    }
    
    private function getAtividadesRecentes($limit = 10) {
        try {
            // Buscar atividades mais recentes de forma mais simples
            $atividades = [];
            
            // Últimas solicitações
            $sql = "SELECT 'nova_solicitacao' as tipo, s.titulo as descricao, p.nome as usuario, 
                           s.data_solicitacao as data_atividade
                    FROM tb_solicita_servico s 
                    JOIN tb_pessoa p ON s.cliente_id = p.id 
                    ORDER BY s.data_solicitacao DESC LIMIT 3";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $solicitacoes = $stmt->fetchAll();
            
            // Últimas propostas
            $sql = "SELECT 'nova_proposta' as tipo, 
                           CONCAT('Proposta para: ', s.titulo) as descricao, 
                           p.nome as usuario, pr.data_proposta as data_atividade
                    FROM tb_proposta pr 
                    JOIN tb_solicita_servico s ON pr.solicitacao_id = s.id
                    JOIN tb_pessoa p ON pr.prestador_id = p.id 
                    ORDER BY pr.data_proposta DESC LIMIT 3";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $propostas = $stmt->fetchAll();
            
            // Últimos usuários
            $sql = "SELECT 'novo_usuario' as tipo, 
                           CONCAT('Novo usuário: ', tipo) as descricao, 
                           nome as usuario, data_cadastro as data_atividade
                    FROM tb_pessoa 
                    ORDER BY data_cadastro DESC LIMIT 4";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll();
            
            // Combinar e ordenar todas as atividades
            $todasAtividades = array_merge($solicitacoes, $propostas, $usuarios);
            
            // Ordenar por data
            usort($todasAtividades, function($a, $b) {
                return strtotime($b['data_atividade']) - strtotime($a['data_atividade']);
            });
            
            return array_slice($todasAtividades, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Erro ao obter atividades recentes: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAlertas() {
        try {
            $alertas = [];
            
            // Solicitações sem propostas há mais de 3 dias
            $sql = "SELECT COUNT(*) as total FROM tb_solicita_servico s
                    WHERE s.status_id = 1 
                    AND s.data_solicitacao < DATE_SUB(NOW(), INTERVAL 3 DAY)
                    AND NOT EXISTS (SELECT 1 FROM tb_proposta p WHERE p.solicitacao_id = s.id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $semPropostas = $stmt->fetchColumn();
            
            if ($semPropostas > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Solicitações sem Propostas',
                    'mensagem' => "{$semPropostas} solicitações há mais de 3 dias sem receber propostas.",
                    'icone' => 'bi-exclamation-triangle'
                ];
            }
            
            // Usuários inativos
            $sql = "SELECT COUNT(*) as total FROM tb_pessoa 
                    WHERE ativo = 1 AND (ultimo_acesso IS NULL OR ultimo_acesso < DATE_SUB(NOW(), INTERVAL 90 DAY))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $usuariosInativos = $stmt->fetchColumn();
            
            if ($usuariosInativos > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'Usuários Inativos',
                    'mensagem' => "{$usuariosInativos} usuários não acessam há mais de 90 dias.",
                    'icone' => 'bi-person-x'
                ];
            }
            
            // Propostas pendentes há mais de 7 dias
            $sql = "SELECT COUNT(*) as total FROM tb_proposta 
                    WHERE status = 'pendente' AND data_proposta < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $propostasPendentes = $stmt->fetchColumn();
            
            if ($propostasPendentes > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Propostas Pendentes',
                    'mensagem' => "{$propostasPendentes} propostas aguardando resposta há mais de 7 dias.",
                    'icone' => 'bi-clock-history'
                ];
            }
            
            return $alertas;
            
        } catch (Exception $e) {
            error_log("Erro ao obter alertas: " . $e->getMessage());
            return [];
        }
    }
    
    private function getGraficos() {
        try {
            // Usuários por mês (últimos 6 meses)
            $sql = "SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes, COUNT(*) as total
                    FROM tb_pessoa 
                    WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
                    ORDER BY mes ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $usuariosPorMes = $stmt->fetchAll();
            
            // Solicitações por status
            $sql = "SELECT st.nome as status, COUNT(*) as total, st.cor
                    FROM tb_solicita_servico s
                    JOIN tb_status_solicitacao st ON s.status_id = st.id
                    GROUP BY s.status_id, st.nome, st.cor
                    ORDER BY total DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $solicitacoesPorStatus = $stmt->fetchAll();
            
            // Tipos de serviço mais solicitados
            $sql = "SELECT ts.nome as tipo, COUNT(*) as total
                    FROM tb_solicita_servico s
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    GROUP BY s.tipo_servico_id, ts.nome
                    ORDER BY total DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tiposServicoPopulares = $stmt->fetchAll();
            
            return [
                'usuarios_por_mes' => $usuariosPorMes,
                'solicitacoes_por_status' => $solicitacoesPorStatus,
                'tipos_servico_populares' => $tiposServicoPopulares
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao obter dados dos gráficos: " . $e->getMessage());
            return [
                'usuarios_por_mes' => [],
                'solicitacoes_por_status' => [],
                'tipos_servico_populares' => []
            ];
        }
    }
    
    private function getEstatisticasPorPeriodo() {
        $sql = "SELECT DATE_FORMAT(data_solicitacao, '%Y-%m') as periodo, COUNT(*) as total 
                FROM tb_solicita_servico 
                WHERE data_solicitacao >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                GROUP BY DATE_FORMAT(data_solicitacao, '%Y-%m') 
                ORDER BY periodo ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getEstatisticasPorUrgencia() {
        $sql = "SELECT urgencia, COUNT(*) as total 
                FROM tb_solicita_servico 
                GROUP BY urgencia 
                ORDER BY FIELD(urgencia, 'alta', 'media', 'baixa')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getEstatisticasValores() {
        $sql = "SELECT COUNT(*) as total_com_orcamento, 
                       MIN(orcamento_estimado) as valor_minimo, 
                       MAX(orcamento_estimado) as valor_maximo, 
                       AVG(orcamento_estimado) as valor_medio, 
                       SUM(orcamento_estimado) as valor_total 
                FROM tb_solicita_servico 
                WHERE orcamento_estimado > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
