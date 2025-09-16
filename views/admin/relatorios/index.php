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
$title = 'Relatórios e Análises - Admin';
$currentPage = 'relatorios';

// Se as variáveis não estiverem definidas, usar valores padrão
if (!isset($estatisticas)) {
    $estatisticas = [
        'total_solicitacoes' => 0,
        'total_clientes' => 0,
        'total_prestadores' => 0,
        'total_propostas' => 0,
        'propostas_aceitas' => 0,
        'valor_total_aceito' => 0,
        'servicos_concluidos' => 0,
        'total_avaliacoes' => 0,
        'nota_media_geral' => 0
    ];
}

if (!isset($evolucaoMensal)) $evolucaoMensal = [];
if (!isset($tiposPopulares)) $tiposPopulares = [];
if (!isset($statusDistribuicao)) $statusDistribuicao = [];
if (!isset($cidadesAtivas)) $cidadesAtivas = [];

ob_start();
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4 border-bottom">
    <h1 class="h2 text-dark">
        <i class="bi bi-graph-up me-2 text-primary"></i>
        Relatórios Gerenciais
    </h1>
    
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" onclick="exportarPDF()">
                <i class="bi bi-file-pdf me-1"></i>
                Exportar PDF
            </button>
            <button type="button" class="btn btn-outline-success" onclick="exportarExcel()">
                <i class="bi bi-file-excel me-1"></i>
                Excel
            </button>
        </div>
        
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-calendar-range me-1"></i>
                Período
            </button>
            <ul class="dropdown-menu">
                <li><h6 class="dropdown-header">Períodos Rápidos</h6></li>
                <li><a class="dropdown-item" href="?periodo=hoje">Hoje</a></li>
                <li><a class="dropdown-item" href="?periodo=semana">Esta Semana</a></li>
                <li><a class="dropdown-item" href="?periodo=mes">Este Mês</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?periodo=trimestre">Último Trimestre</a></li>
                <li><a class="dropdown-item" href="?periodo=ano">Este Ano</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['admin_flash'])): ?>
    <?php $flash = $_SESSION['admin_flash']; unset($_SESSION['admin_flash']); ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- KPIs Principais -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-widget" style="border-left-color: #007bff;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-2">Solicitações Total</h6>
                    <h2 class="text-primary mb-0"><?= number_format($estatisticas['total_solicitacoes']) ?></h2>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> Sistema ativo
                    </small>
                </div>
                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                    <i class="bi bi-clipboard-check fs-4 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-widget" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-2">Receita Total</h6>
                    <h2 class="text-success mb-0">R$ <?= number_format($estatisticas['valor_total_aceito'] ?? 0, 0, ',', '.') ?></h2>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> Movimentação
                    </small>
                </div>
                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                    <i class="bi bi-currency-dollar fs-4 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-widget" style="border-left-color: #ffc107;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-2">Total de Usuários</h6>
                    <h2 class="text-warning mb-0"><?= number_format(($estatisticas['total_clientes'] ?? 0) + ($estatisticas['total_prestadores'] ?? 0)) ?></h2>
                    <small class="text-info">
                        <i class="bi bi-people"></i> Cadastrados
                    </small>
                </div>
                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                    <i class="bi bi-people fs-4 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-widget" style="border-left-color: #17a2b8;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-2">Serviços Concluídos</h6>
                    <h2 class="text-info mb-0"><?= number_format($estatisticas['servicos_concluidos'] ?? 0) ?></h2>
                    <small class="text-success">
                        <i class="bi bi-check-circle"></i> Finalizados
                    </small>
                </div>
                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                    <i class="bi bi-check-circle fs-4 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Evolução Mensal de Atividades
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartEvolucao" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Status das Solicitações
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartStatus" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabelas de Análise -->
<div class="row">
    <!-- Tipos de Serviços Mais Solicitados -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-tools me-2"></i>
                    Tipos de Serviços Mais Solicitados
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($tiposPopulares)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo de Serviço</th>
                                    <th>Total</th>
                                    <th>Orçamento Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tiposPopulares as $tipo): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tipo['nome']) ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?= $tipo['total'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($tipo['orcamento_medio']): ?>
                                                <span class="text-success fw-bold">R$ <?= number_format($tipo['orcamento_medio'], 2, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle text-muted fs-1"></i>
                        <p class="text-muted mt-2">Nenhum dado disponível</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cidades Mais Ativas -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    Cidades Mais Ativas
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($cidadesAtivas)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cidade</th>
                                    <th>UF</th>
                                    <th>Solicitações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cidadesAtivas as $cidade): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cidade['cidade']) ?></td>
                                        <td><?= htmlspecialchars($cidade['estado']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $cidade['total_solicitacoes'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle text-muted fs-1"></i>
                        <p class="text-muted mt-2">Nenhum dado disponível</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Métricas Detalhadas -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Métricas Detalhadas do Sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="metric-item text-center p-3 bg-light rounded">
                            <div class="h4 text-primary mb-1"><?= number_format($estatisticas['total_clientes']) ?></div>
                            <small class="text-muted">Total de Clientes</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-item text-center p-3 bg-light rounded">
                            <div class="h4 text-success mb-1"><?= number_format($estatisticas['total_prestadores']) ?></div>
                            <small class="text-muted">Total de Prestadores</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-item text-center p-3 bg-light rounded">
                            <div class="h4 text-warning mb-1"><?= number_format($estatisticas['total_propostas']) ?></div>
                            <small class="text-muted">Total de Propostas</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-item text-center p-3 bg-light rounded">
                            <div class="h4 text-info mb-1"><?= number_format($estatisticas['total_avaliacoes']) ?></div>
                            <small class="text-muted">Total de Avaliações</small>
                        </div>
                    </div>
                </div>
                
                <!-- Taxa de Conversão -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Taxa de Conversão de Propostas</h6>
                        <?php 
                        $taxaConversao = $estatisticas['total_propostas'] > 0 
                            ? ($estatisticas['propostas_aceitas'] / $estatisticas['total_propostas']) * 100 
                            : 0;
                        ?>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: <?= $taxaConversao ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?= number_format($taxaConversao, 1) ?>% 
                            (<?= $estatisticas['propostas_aceitas'] ?> de <?= $estatisticas['total_propostas'] ?> propostas)
                        </small>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold">Taxa de Conclusão de Serviços</h6>
                        <?php 
                        $taxaConclusao = $estatisticas['total_solicitacoes'] > 0 
                            ? ($estatisticas['servicos_concluidos'] / $estatisticas['total_solicitacoes']) * 100 
                            : 0;
                        ?>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" style="width: <?= $taxaConclusao ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?= number_format($taxaConclusao, 1) ?>% 
                            (<?= $estatisticas['servicos_concluidos'] ?> de <?= $estatisticas['total_solicitacoes'] ?> solicitações)
                        </small>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold">Resumo Financeiro</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="text-success mb-1">R$ <?= number_format($estatisticas['valor_total_aceito'] ?? 0, 2, ',', '.') ?></h5>
                                    <small class="text-muted">Valor Total Movimentado</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="text-primary mb-1">
                                        <?php 
                                        $ticketMedio = $estatisticas['propostas_aceitas'] > 0 
                                            ? $estatisticas['valor_total_aceito'] / $estatisticas['propostas_aceitas'] 
                                            : 0;
                                        ?>
                                        R$ <?= number_format($ticketMedio, 2, ',', '.') ?>
                                    </h5>
                                    <small class="text-muted">Ticket Médio</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="text-warning mb-1"><?= number_format($estatisticas['nota_media_geral'] ?? 0, 1) ?>/5.0</h5>
                                    <small class="text-muted">Nota Média Geral</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Estilos específicos da página
$styles = '
.stats-widget {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border-left: 4px solid;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}
.stats-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.metric-item {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}
.metric-item:hover {
    transform: translateY(-2px);
    border-color: #dee2e6;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.progress {
    height: 8px;
}
.card {
    border-radius: 12px;
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
';

// Scripts específicos da página - CORRIGIDO para evitar erro do Canvas
$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dados para os gráficos
    const evolucaoData = ' . json_encode($evolucaoMensal) . ';
    const statusDistribuicao = ' . json_encode($statusDistribuicao) . ';

    // Verificar se os elementos canvas existem antes de criar os gráficos
    const canvasEvolucao = document.getElementById("chartEvolucao");
    const canvasStatus = document.getElementById("chartStatus");

    // Gráfico de Evolução
    if (canvasEvolucao && evolucaoData && evolucaoData.length > 0) {
        try {
            new Chart(canvasEvolucao.getContext("2d"), {
                type: "line",
                data: {
                    labels: evolucaoData.map(item => {
                        const [ano, mes] = item.mes.split("-");
                        return new Date(ano, mes - 1).toLocaleDateString("pt-BR", { month: "short", year: "numeric" });
                    }),
                    datasets: [{
                        label: "Solicitações",
                        data: evolucaoData.map(item => item.total_solicitacoes),
                        borderColor: "#0d6efd",
                        backgroundColor: "rgba(13, 110, 253, 0.1)",
                        tension: 0.4,
                        fill: true
                    }, {
                        label: "Propostas",
                        data: evolucaoData.map(item => item.total_propostas),
                        borderColor: "#198754",
                        backgroundColor: "rgba(25, 135, 84, 0.1)",
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: "index"
                    },
                    plugins: {
                        legend: {
                            position: "top"
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error("Erro ao criar gráfico de evolução:", e);
        }
    } else {
        // Mostrar mensagem de dados vazios no gráfico
        if (canvasEvolucao) {
            const ctx = canvasEvolucao.getContext("2d");
            ctx.font = "16px Arial";
            ctx.fillStyle = "#666";
            ctx.textAlign = "center";
            ctx.fillText("Nenhum dado disponível", canvasEvolucao.width/2, canvasEvolucao.height/2);
        }
    }

    // Gráfico de Status
    if (canvasStatus && statusDistribuicao && statusDistribuicao.length > 0) {
        try {
            new Chart(canvasStatus.getContext("2d"), {
                type: "doughnut",
                data: {
                    labels: statusDistribuicao.map(item => item.nome),
                    datasets: [{
                        data: statusDistribuicao.map(item => item.total),
                        backgroundColor: statusDistribuicao.map(item => item.cor),
                        borderWidth: 2,
                        borderColor: "#fff"
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error("Erro ao criar gráfico de status:", e);
        }
    } else {
        // Mostrar mensagem de dados vazios
        if (canvasStatus) {
            const ctx = canvasStatus.getContext("2d");
            ctx.font = "14px Arial";
            ctx.fillStyle = "#666";
            ctx.textAlign = "center";
            ctx.fillText("Nenhum dado disponível", canvasStatus.width/2, canvasStatus.height/2);
        }
    }
});

function exportarPDF() {
    alert("Funcionalidade de exportação PDF será implementada em breve!");
}

function exportarExcel() {
    alert("Funcionalidade de exportação Excel será implementada em breve!");
}

// Auto-remover alertas após 5 segundos
setTimeout(function() {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(alert => {
        if (alert.classList.contains("show")) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);
</script>
';

include 'views/admin/layouts/app.php';
?>