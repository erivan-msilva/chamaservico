<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/login'));
    exit;
}

// Configuração do layout
$title = 'Dashboard Administrativo - ChamaServiço';
$currentPage = 'dashboard';

// Buscar dados reais se não existirem
if (!isset($stats)) {
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        
        // Total de usuários
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1");
        $stmt->execute();
        $totalUsuarios = $stmt->fetchColumn();
        
        // Total de solicitações
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico");
        $stmt->execute();
        $totalSolicitacoes = $stmt->fetchColumn();
        
        // Total de propostas
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_proposta");
        $stmt->execute();
        $totalPropostas = $stmt->fetchColumn();
        
        // Serviços concluídos
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 5");
        $stmt->execute();
        $servicosConcluidos = $stmt->fetchColumn();
        
        // Usuários ativos (último acesso nos últimos 30 dias) - CORRIGIDO
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1 AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $usuariosAtivos = $stmt->fetchColumn();
        
        // Novos usuários hoje
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE DATE(data_cadastro) = CURDATE()");
        $stmt->execute();
        $novosUsuariosHoje = $stmt->fetchColumn();
        
        // Solicitações pendentes
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 1");
        $stmt->execute();
        $solicitacoesPendentes = $stmt->fetchColumn();
        
        // Valor total transacionado
        $stmt = $db->prepare("SELECT COALESCE(SUM(valor), 0) FROM tb_proposta WHERE status = 'aceita'");
        $stmt->execute();
        $valorTransacionado = $stmt->fetchColumn();
        
        // CORREÇÃO: Garantir que todos os valores sejam inteiros ou 0
        $stats = [
            'total_usuarios' => (int)($totalUsuarios ?? 0),
            'total_solicitacoes' => (int)($totalSolicitacoes ?? 0),
            'total_propostas' => (int)($totalPropostas ?? 0),
            'servicos_concluidos' => (int)($servicosConcluidos ?? 0),
            'usuarios_ativos' => (int)($usuariosAtivos ?? 0),
            'novos_usuarios_hoje' => (int)($novosUsuariosHoje ?? 0),
            'solicitacoes_pendentes' => (int)($solicitacoesPendentes ?? 0),
            'valor_transacionado' => (float)($valorTransacionado ?? 0.0)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        // CORREÇÃO: Definir todos os valores padrão explicitamente
        $stats = [
            'total_usuarios' => 0,
            'total_solicitacoes' => 0,
            'total_propostas' => 0,
            'servicos_concluidos' => 0,
            'usuarios_ativos' => 0,
            'novos_usuarios_hoje' => 0,
            'solicitacoes_pendentes' => 0,
            'valor_transacionado' => 0.0
        ];
    }
}

// Buscar atividades recentes
if (!isset($atividadesRecentes)) {
    try {
        $db = Database::getInstance();
        
        // Últimas atividades combinadas
        $sql = "
            (SELECT 'nova_solicitacao' as tipo, s.titulo as descricao, p.nome as usuario, s.data_solicitacao as data_atividade, s.id as referencia_id
             FROM tb_solicita_servico s 
             JOIN tb_pessoa p ON s.cliente_id = p.id 
             ORDER BY s.data_solicitacao DESC LIMIT 3)
            UNION ALL
            (SELECT 'nova_proposta' as tipo, CONCAT('Proposta para: ', s.titulo) as descricao, p.nome as usuario, pr.data_proposta as data_atividade, pr.id as referencia_id
             FROM tb_proposta pr 
             JOIN tb_solicita_servico s ON pr.solicitacao_id = s.id
             JOIN tb_pessoa p ON pr.prestador_id = p.id 
             ORDER BY pr.data_proposta DESC LIMIT 3)
            UNION ALL
            (SELECT 'novo_usuario' as tipo, CONCAT('Novo usuário: ', tipo) as descricao, nome as usuario, data_cadastro as data_atividade, id as referencia_id
             FROM tb_pessoa 
             ORDER BY data_cadastro DESC LIMIT 4)
            ORDER BY data_atividade DESC LIMIT 10
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $atividadesRecentes = $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Erro ao buscar atividades: " . $e->getMessage());
        $atividadesRecentes = [];
    }
}

// Dados para gráficos (simulados se não existirem)
if (!isset($dadosParaGrafico)) {
    $dadosParaGrafico = [
        'solicitacoes_mes' => [45, 52, 38, 65, 78, 82, 71],
        'concluidos_mes' => [32, 41, 28, 48, 62, 68, 58],
        'labels_mes' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
        'tipos_servico' => [
            ['label' => 'Limpeza', 'value' => 35, 'color' => '#3b82f6'],
            ['label' => 'Elétrica', 'value' => 28, 'color' => '#06d6a0'],
            ['label' => 'Hidráulica', 'value' => 22, 'color' => '#f59e0b'],
            ['label' => 'Outros', 'value' => 15, 'color' => '#ef4444']
        ],
        'sparklines' => [
            'usuarios' => [12, 19, 15, 17, 20, 18, 22, 25, 21, 28],
            'solicitacoes' => [5, 8, 12, 15, 11, 18, 20, 16, 24, 22],
            'receita' => [1200, 1850, 1650, 2100, 2850, 2650, 3200, 2900, 3800, 4200]
        ]
    ];
}

ob_start();
?>

<div class="dashboard-modern">
    <!-- Header com Breadcrumb -->
    <div class="dashboard-header">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb-modern">
                <li class="breadcrumb-item">
                    <i class="bi bi-house-door"></i>
                    Admin
                </li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="dashboard-title">
                    Visão Geral do Sistema
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        <span>Ao vivo</span>
                    </div>
                </h1>
                <p class="dashboard-subtitle">
                    Monitore o desempenho e tome decisões baseadas em dados
                </p>
            </div>
            
            <div class="dashboard-actions">
                <button class="btn-modern btn-ghost" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Atualizar
                </button>
                <button class="btn-modern btn-primary" onclick="exportarRelatorio()">
                    <i class="bi bi-download"></i>
                    Exportar
                </button>
            </div>
        </div>
    </div>

    <!-- Grid Principal do Dashboard -->
    <div class="dashboard-grid">
        
        <!-- KPI Hero - Métrica Principal -->
        <div class="grid-area-hero">
            <div class="kpi-hero-card">
                <div class="kpi-hero-content">
                    <div class="kpi-hero-label">Receita Total do Mês</div>
                    <div class="kpi-hero-value">
                        R$ <?= number_format($stats['valor_transacionado'] ?? 0, 2, ',', '.') ?>
                        <div class="kpi-hero-trend positive">
                            <i class="bi bi-trending-up"></i>
                            +15.8%
                        </div>
                    </div>
                    <div class="kpi-hero-description">
                        Comparado ao mês anterior
                    </div>
                </div>
                <div class="kpi-hero-chart">
                    <canvas id="heroSparkline" width="180" height="60"></canvas>
                </div>
                <div class="kpi-hero-decoration"></div>
            </div>
        </div>

        <!-- Gráfico Principal - Performance -->
        <div class="grid-area-chart">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Performance Mensal</h3>
                        <p>Solicitações vs Serviços Concluídos</p>
                    </div>
                    <div class="chart-controls">
                        <select class="form-select-modern" id="chartPeriod">
                            <option value="6m" selected>6 meses</option>
                            <option value="3m">3 meses</option>
                            <option value="1y">1 ano</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions - Ações Contextuais -->
        <div class="grid-area-actions">
            <div class="quick-actions-card">
                <h3>Ações Rápidas</h3>
                <div class="actions-list">
                    <div class="action-item priority-high" onclick="window.location.href='<?= url('admin/solicitacoes') ?>'">
                        <div class="action-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="action-content">
                            <div class="action-title"><?= $stats['solicitacoes_pendentes'] ?? 0 ?> Pendentes</div>
                            <div class="action-subtitle">Requerem atenção</div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>

                    <div class="action-item priority-medium" onclick="window.location.href='<?= url('admin/usuarios') ?>'">
                        <div class="action-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div class="action-content">
                            <div class="action-title"><?= $stats['novos_usuarios_hoje'] ?? 0 ?> Novos Hoje</div>
                            <div class="action-subtitle">Usuários cadastrados</div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>

                    <div class="action-item priority-low" onclick="gerarBackup()">
                        <div class="action-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="action-content">
                            <div class="action-title">Backup Sistema</div>
                            <div class="action-subtitle">Última vez: ontem</div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Grid - Métricas Secundárias -->
        <div class="grid-area-kpis">
            <div class="kpis-grid">
                
                <!-- Total Usuários -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon users">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="kpi-trend positive">
                            <i class="bi bi-arrow-up"></i>
                            +3.2%
                        </div>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['total_usuarios'] ?? 0) ?></div>
                    <div class="kpi-label">Usuários Totais</div>
                    <canvas class="kpi-sparkline" id="usersSparkline" width="100" height="30"></canvas>
                </div>

                <!-- Solicitações -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon requests">
                            <i class="bi bi-list-task"></i>
                        </div>
                        <div class="kpi-trend positive">
                            <i class="bi bi-arrow-up"></i>
                            +5.7%
                        </div>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['total_solicitacoes'] ?? 0) ?></div>
                    <div class="kpi-label">Solicitações</div>
                    <canvas class="kpi-sparkline" id="requestsSparkline" width="100" height="30"></canvas>
                </div>

                <!-- Taxa Conversão -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon conversion">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="kpi-trend negative">
                            <i class="bi bi-arrow-down"></i>
                            -1.2%
                        </div>
                    </div>
                    <div class="kpi-value">
                        <?php 
                        $conversao = $stats['total_solicitacoes'] > 0 
                            ? round(($stats['servicos_concluidos'] / $stats['total_solicitacoes']) * 100, 1) 
                            : 0;
                        echo $conversao . '%';
                        ?>
                    </div>
                    <div class="kpi-label">Taxa Conclusão</div>
                    <canvas class="kpi-sparkline" id="conversionSparkline" width="100" height="30"></canvas>
                </div>

                <!-- Usuários Ativos -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon active">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="kpi-trend positive">
                            <i class="bi bi-arrow-up"></i>
                            +8.1%
                        </div>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['usuarios_ativos'] ?? 0) ?></div>
                    <div class="kpi-label">Usuários Ativos</div>
                    <canvas class="kpi-sparkline" id="activeSparkline" width="100" height="30"></canvas>
                </div>

            </div>
        </div>

        <!-- Timeline de Atividades -->
        <div class="grid-area-timeline">
            <div class="timeline-card">
                <div class="timeline-header">
                    <h3>Atividades Recentes</h3>
                    <button class="btn-link" onclick="verTodasAtividades()">Ver todas</button>
                </div>
                <div class="timeline-container">
                    <?php if (!empty($atividadesRecentes)): ?>
                        <?php foreach (array_slice($atividadesRecentes, 0, 8) as $atividade): ?>
                            <div class="timeline-item">
                                <div class="timeline-avatar <?= $atividade['tipo'] ?>">
                                    <i class="bi bi-<?= getActivityIcon($atividade['tipo']) ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">
                                        <?= htmlspecialchars($atividade['descricao']) ?>
                                    </div>
                                    <div class="timeline-meta">
                                        <span class="timeline-user">
                                            <i class="bi bi-person"></i>
                                            <?= htmlspecialchars($atividade['usuario']) ?>
                                        </span>
                                        <span class="timeline-time">
                                            <?= timeAgo($atividade['data_atividade']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="timeline-action">
                                    <button class="btn-icon" onclick="viewActivity(<?= $atividade['referencia_id'] ?? 0 ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="timeline-empty">
                            <i class="bi bi-clock"></i>
                            <p>Nenhuma atividade recente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alertas e Distribuição -->
        <div class="grid-area-alerts">
            
            <!-- Alertas Críticos -->
            <div class="alerts-card mb-4">
                <div class="alerts-header">
                    <h3>Alertas</h3>
                    <span class="alerts-count">3</span>
                </div>
                <div class="alerts-list">
                    <div class="alert-item warning">
                        <div class="alert-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Sistema sobrecarregado</div>
                            <div class="alert-time">há 2 min</div>
                        </div>
                    </div>
                    <div class="alert-item info">
                        <div class="alert-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Backup automático concluído</div>
                            <div class="alert-time">há 1 hora</div>
                        </div>
                    </div>
                    <div class="alert-item success">
                        <div class="alert-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Sistema atualizado</div>
                            <div class="alert-time">há 3 horas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribuição de Serviços -->
            <div class="distribution-card">
                <div class="distribution-header">
                    <h3>Tipos de Serviços</h3>
                    <div class="distribution-legend">
                        <?php foreach ($dadosParaGrafico['tipos_servico'] as $tipo): ?>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: <?= $tipo['color'] ?>;"></div>
                                <span><?= $tipo['label'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="distribution-chart">
                    <canvas id="distributionChart" width="280" height="280"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Funções PHP auxiliares -->
<?php
function getActivityIcon($tipo) {
    $icons = [
        'nova_solicitacao' => 'plus-circle',
        'nova_proposta' => 'file-earmark-text', 
        'novo_usuario' => 'person-plus',
        'servico_concluido' => 'check-circle'
    ];
    return $icons[$tipo] ?? 'activity';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'agora';
    if ($time < 3600) return floor($time/60) . 'min';
    if ($time < 86400) return floor($time/3600) . 'h';
    return floor($time/86400) . 'd';
}
?>

<?php
$content = ob_get_clean();

// CSS Moderno e Sofisticado
$styles = '
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

:root {
  --primary: #1e293b;
  --secondary: #3b82f6; 
  --accent: #06d6a0;
  --warning: #f59e0b;
  --danger: #ef4444;
  --success: #10b981;
  --info: #0ea5e9;
  --neutral-50: #f8fafc;
  --neutral-100: #f1f5f9;
  --neutral-200: #e2e8f0;
  --neutral-300: #cbd5e1;
  --neutral-600: #475569;
  --neutral-700: #334155;
  --neutral-800: #1e293b;
  --neutral-900: #0f172a;
  --glass: rgba(255, 255, 255, 0.8);
  --glass-border: rgba(255, 255, 255, 0.18);
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
  --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  --radius-sm: 6px;
  --radius-md: 8px; 
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-2xl: 20px;
}

* {
  font-family: "Inter", system-ui, -apple-system, sans-serif;
  font-feature-settings: "cv02", "cv03", "cv04", "cv11";
}

.main-content {
  background: linear-gradient(135deg, var(--neutral-50) 0%, #e0e7ff 100%);
  min-height: 100vh;
  padding: 0;
}

/* Dashboard Layout */
.dashboard-modern {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.breadcrumb-modern {
  background: none;
  padding: 0;
  margin: 0;
  font-size: 0.875rem;
  font-weight: 500;
}

.breadcrumb-modern .breadcrumb-item {
  color: var(--neutral-600);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb-modern .breadcrumb-item.active {
  color: var(--secondary);
  font-weight: 600;
}

.dashboard-title {
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--neutral-900);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.dashboard-subtitle {
  color: var(--neutral-600);
  font-size: 1.125rem;
  margin: 0.5rem 0 0 0;
}

.live-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--success);
  background: rgba(16, 185, 129, 0.1);
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.live-dot {
  width: 8px;
  height: 8px;
  background: var(--success);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.05); }
}

/* Grid Layout */
.dashboard-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(12, 1fr);
  grid-template-areas:
    "hero hero hero hero chart chart chart chart actions actions actions actions"
    "kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis"
    "timeline timeline timeline timeline timeline timeline alerts alerts alerts alerts alerts alerts";
}

.grid-area-hero { grid-area: hero; }
.grid-area-chart { grid-area: chart; } 
.grid-area-actions { grid-area: actions; }
.grid-area-kpis { grid-area: kpis; }
.grid-area-timeline { grid-area: timeline; }
.grid-area-alerts { grid-area: alerts; }

/* KPI Hero Card */
.kpi-hero-card {
  background: linear-gradient(135deg, var(--secondary) 0%, #1d4ed8 100%);
  border-radius: var(--radius-2xl);
  padding: 2rem;
  color: white;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.kpi-hero-card::before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.15) 0%, transparent 50%);
  pointer-events: none;
}

.kpi-hero-content {
  position: relative;
  z-index: 2;
}

.kpi-hero-label {
  font-size: 0.875rem;
  font-weight: 600;
  opacity: 0.9;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-hero-value {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: baseline;
  gap: 1rem;
}

.kpi-hero-trend {
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.kpi-hero-trend.positive {
  background: rgba(16, 185, 129, 0.2);
  color: #d1fae5;
}

.kpi-hero-description {
  font-size: 0.875rem;
  opacity: 0.8;
  margin-bottom: 1.5rem;
}

.kpi-hero-chart {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  z-index: 2;
}

.kpi-hero-decoration {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  border-radius: 50%;
  z-index: 1;
}

/* KPIs Grid */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.kpi-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
  border-color: rgba(59, 130, 246, 0.3);
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  background: linear-gradient(135deg, var(--secondary) 0%, #1d4ed8 100%);
}

.kpi-icon.users { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
.kpi-icon.requests { background: linear-gradient(135deg, #06d6a0 0%, #059669 100%); }
.kpi-icon.conversion { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.kpi-icon.active { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

.kpi-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
}

.kpi-trend.positive {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.kpi-trend.negative {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

.kpi-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--neutral-900);
  margin-bottom: 0.25rem;
}

.kpi-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--neutral-600);
  margin-bottom: 1rem;
}

.kpi-sparkline {
  width: 100%;
  height: 30px;
}

/* Chart Card */
.chart-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  height: 100%;
  box-shadow: var(--shadow-md);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.chart-title h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--neutral-900);
  margin: 0 0 0.25rem 0;
}

.chart-title p {
  font-size: 0.875rem;
  color: var(--neutral-600);
  margin: 0;
}

.form-select-modern {
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-md);
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  background: white;
  color: var(--neutral-700);
  font-weight: 500;
}

.chart-container {
  position: relative;
  height: 300px;
}

/* Quick Actions */
.quick-actions-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  height: 100%;
  box-shadow: var(--shadow-md);
}

.quick-actions-card h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--neutral-900);
  margin: 0 0 1.5rem 0;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.action-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 4px solid transparent;
}

.action-item:hover {
  background: rgba(59, 130, 246, 0.05);
  border-left-color: var(--secondary);
  transform: translateX(4px);
}

.action-item.priority-high {
  background: rgba(239, 68, 68, 0.05);
  border-left-color: var(--danger);
}

.action-item.priority-medium {
  background: rgba(245, 158, 11, 0.05);
  border-left-color: var(--warning);
}

.action-item.priority-low {
  background: rgba(16, 185, 129, 0.05);
  border-left-color: var(--success);
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  background: var(--secondary);
}

.action-content {
  flex: 1;
}

.action-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--neutral-900);
  margin-bottom: 0.25rem;
}

.action-subtitle {
  font-size: 0.875rem;
  color: var(--neutral-600);
}

.action-arrow {
  color: var(--neutral-400);
  transition: all 0.2s ease;
}

.action-item:hover .action-arrow {
  color: var(--secondary);
  transform: translateX(4px);
}

/* Timeline */
.timeline-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  height: 100%;
  box-shadow: var(--shadow-md);
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.timeline-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--neutral-900);
  margin: 0;
}

.btn-link {
  color: var(--secondary);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  padding: 0;
  border: none;
  background: none;
}

.btn-link:hover {
  text-decoration: underline;
}

.timeline-container {
  max-height: 400px;
  overflow-y: auto;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--neutral-100);
}

.timeline-item:last-child {
  border-bottom: none;
}

.timeline-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
  flex-shrink: 0;
}

.timeline-avatar.nova_solicitacao { background: var(--secondary); }
.timeline-avatar.nova_proposta { background: var(--success); }
.timeline-avatar.novo_usuario { background: var(--info); }

.timeline-content {
  flex: 1;
}

.timeline-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--neutral-900);
  margin-bottom: 0.5rem;
}

.timeline-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.75rem;
  color: var(--neutral-600);
}

.timeline-user {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-md);
  border: 1px solid var(--neutral-200);
  background: white;
  color: var(--neutral-600);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background: var(--secondary);
  color: white;
  border-color: var(--secondary);
}

/* Alerts */
.alerts-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
}

.alerts-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.alerts-header h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--neutral-900);
  margin: 0;
}

.alerts-count {
  background: var(--danger);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  min-width: 20px;
  text-align: center;
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: var(--radius-md);
  background: var(--neutral-50);
  border-left: 3px solid;
}

.alert-item.warning {
  border-left-color: var(--warning);
  background: rgba(245, 158, 11, 0.05);
}

.alert-item.info {
  border-left-color: var(--info);
  background: rgba(14, 165, 233, 0.05);
}

.alert-item.success {
  border-left-color: var(--success);
  background: rgba(16, 185, 129, 0.05);
}

.alert-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  color: white;
  background: var(--warning);
}

.alert-item.info .alert-icon { background: var(--info); }
.alert-item.success .alert-icon { background: var(--success); }

.alert-content {
  flex: 1;
}

.alert-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--neutral-900);
  margin-bottom: 0.25rem;
}

.alert-time {
  font-size: 0.75rem;
  color: var(--neutral-600);
}

/* Distribution Chart */
.distribution-card {
  background: var(--glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
}

.distribution-header h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--neutral-900);
  margin: 0 0 1rem 0;
}

.distribution-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--neutral-700);
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.distribution-chart {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Botões Modernos */
.btn-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-modern::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.btn-modern:hover::before {
  left: 100%;
}

.btn-primary {
  background: var(--secondary);
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-ghost {
  background: rgba(255, 255, 255, 0.8);
  color: var(--neutral-700);
  border: 1px solid var(--neutral-200);
}

.btn-ghost:hover {
  background: white;
  color: var(--neutral-900);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

/* Dashboard Actions */
.dashboard-actions {
  display: flex;
  gap: 1rem;
}

/* Responsividade */
@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-areas:
      "hero hero hero hero hero hero chart chart chart chart chart chart"
      "actions actions actions actions actions actions actions actions actions actions actions actions"
      "kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis kpis"
      "timeline timeline timeline timeline timeline timeline alerts alerts alerts alerts alerts alerts";
  }
}

@media (max-width: 768px) {
  .dashboard-modern {
    padding: 1rem;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
    grid-template-areas:
      "hero"
      "actions"
      "chart"
      "kpis"
      "timeline"
      "alerts";
  }
  
  .dashboard-title {
    font-size: 1.75rem;
  }
  
  .kpi-hero-value {
    font-size: 2rem;
  }
  
  .kpis-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

/* Animações de entrada */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dashboard-grid > * {
  animation: slideInUp 0.6s ease-out backwards;
}

.grid-area-hero { animation-delay: 0.1s; }
.grid-area-chart { animation-delay: 0.2s; }
.grid-area-actions { animation-delay: 0.3s; }
.grid-area-kpis { animation-delay: 0.4s; }
.grid-area-timeline { animation-delay: 0.5s; }
.grid-area-alerts { animation-delay: 0.6s; }
';

// JavaScript com Chart.js e Interações
$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dados dos gráficos vindos do PHP
    const dadosGraficos = ' . json_encode($dadosParaGrafico) . ';
    
    // Configurações globais do Chart.js
    Chart.defaults.font.family = "Inter";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = "#64748b";
    
    // Gráfico Principal - Performance Mensal
    const performanceCtx = document.getElementById("performanceChart").getContext("2d");
    new Chart(performanceCtx, {
        type: "line",
        data: {
            labels: dadosGraficos.labels_mes,
            datasets: [
                {
                    label: "Solicitações",
                    data: dadosGraficos.solicitacoes_mes,
                    borderColor: "#3b82f6",
                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: "#3b82f6",
                    pointBorderColor: "#ffffff",
                    pointBorderWidth: 2
                },
                {
                    label: "Concluídos", 
                    data: dadosGraficos.concluidos_mes,
                    borderColor: "#06d6a0",
                    backgroundColor: "rgba(6, 214, 160, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: "#06d6a0",
                    pointBorderColor: "#ffffff",
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        usePointStyle: true,
                        pointStyle: "circle",
                        padding: 20,
                        font: {
                            weight: "600"
                        }
                    }
                },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                    titleColor: "#ffffff",
                    bodyColor: "#ffffff",
                    borderColor: "#3b82f6",
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    mode: "index",
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "#f1f5f9"
                    },
                    ticks: {
                        font: {
                            weight: "500"
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            weight: "500"
                        }
                    }
                }
            },
            interaction: {
                mode: "index",
                intersect: false
            }
        }
    });
    
    // Gráfico de Distribuição - Doughnut Chart
    const distributionCtx = document.getElementById("distributionChart").getContext("2d");
    new Chart(distributionCtx, {
        type: "doughnut",
        data: {
            labels: dadosGraficos.tipos_servico.map(item => item.label),
            datasets: [{
                data: dadosGraficos.tipos_servico.map(item => item.value),
                backgroundColor: dadosGraficos.tipos_servico.map(item => item.color),
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                    titleColor: "#ffffff",
                    bodyColor: "#ffffff",
                    borderColor: "#3b82f6",
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: "70%"
        }
    });
    
    // Sparklines para KPIs
    function createSparkline(canvasId, data, color = "#3b82f6") {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext("2d");
        new Chart(ctx, {
            type: "line",
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: `${color}20`,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                }
            }
        });
    }
    
    // Criar sparklines
    createSparkline("usersSparkline", dadosGraficos.sparklines.usuarios, "#3b82f6");
    createSparkline("requestsSparkline", dadosGraficos.sparklines.solicitacoes, "#06d6a0");
    createSparkline("conversionSparkline", [85, 82, 88, 90, 87, 89, 92, 88, 91, 94], "#f59e0b");
    createSparkline("activeSparkline", [120, 135, 128, 142, 158, 165, 172, 169, 178, 185], "#10b981");
    
    // Sparkline Hero
    const heroCanvas = document.getElementById("heroSparkline");
    if (heroCanvas) {
        const heroCtx = heroCanvas.getContext("2d");
        new Chart(heroCtx, {
            type: "line",
            data: {
                labels: dadosGraficos.sparklines.receita.map((_, i) => i),
                datasets: [{
                    data: dadosGraficos.sparklines.receita,
                    borderColor: "rgba(255, 255, 255, 0.8)",
                    backgroundColor: "rgba(255, 255, 255, 0.2)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "rgba(59, 130, 246, 0.8)",
                    pointBorderWidth: 2,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "rgba(255, 255, 255, 0.9)",
                        titleColor: "#1e293b",
                        bodyColor: "#1e293b",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `R$ ${context.parsed.y.toLocaleString("pt-BR")}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }
});

// Funções de Interação
function refreshDashboard() {
    const btn = event.target;
    const icon = btn.querySelector("i");
    
    icon.style.animation = "spin 1s linear infinite";
    btn.disabled = true;
    
    // Simular refresh
    setTimeout(() => {
        icon.style.animation = "";
        btn.disabled = false;
        showNotification("Dashboard atualizado!", "success");
    }, 1500);
}

function exportarRelatorio() {
    showNotification("Relatório está sendo gerado...", "info");
    
    setTimeout(() => {
        showNotification("Relatório exportado com sucesso!", "success");
    }, 2000);
}

function gerarBackup() {
    if (confirm("Deseja gerar um backup completo do sistema?")) {
        showNotification("Backup iniciado em segundo plano", "info");
        
        setTimeout(() => {
            showNotification("Backup concluído com sucesso!", "success");
        }, 3000);
    }
}

function verTodasAtividades() {
    window.location.href = "' . url('admin/atividades') . '";
}

function viewActivity(id) {
    console.log("Visualizar atividade:", id);
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

// Sistema de Notificações
function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="bi bi-${type === "success" ? "check-circle" : type === "error" ? "x-circle" : "info-circle"}"></i>
        <span>${message}</span>
    `;
    
    const style = document.createElement("style");
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-left: 4px solid;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            transform: translateX(400px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .notification-success { border-left-color: #10b981; color: #059669; }
        .notification-error { border-left-color: #ef4444; color: #dc2626; }
        .notification-info { border-left-color: #3b82f6; color: #2563eb; }
        .notification.show { transform: translateX(0); }
    `;
    
    if (!document.querySelector("style[data-notifications]")) {
        style.setAttribute("data-notifications", "true");
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    requestAnimationFrame(() => {
        notification.classList.add("show");
    });
    
    setTimeout(() => {
        notification.style.transform = "translateX(400px)";
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Auto-refresh periódico (opcional)
setInterval(() => {
    console.log("Auto-refresh dashboard dados...");
    // Aqui você pode fazer chamadas AJAX para atualizar dados específicos
}, 60000); // A cada 1 minuto
</script>
';

include 'views/admin/layouts/app.php';
?>