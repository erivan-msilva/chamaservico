<?php
$title = 'Dashboard Prestador - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard do Prestador</h2>
        <p class="text-muted mb-0">Bem-vindo, <?= htmlspecialchars(Session::getUserName()) ?>!</p>
    </div>
    <div>
        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary">
            <i class="bi bi-search me-1"></i>Buscar Serviços
        </a>
    </div>
</div>

<!-- Alertas -->
<?php if (!empty($alertas)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <?php foreach ($alertas as $alerta): ?>
                <div class="alert alert-<?= htmlspecialchars($alerta['tipo'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= htmlspecialchars($alerta['icone'] ?? 'info-circle') ?> me-2"></i>
                    <strong><?= htmlspecialchars($alerta['titulo'] ?? 'Aviso') ?>:</strong> <?= htmlspecialchars($alerta['mensagem'] ?? '') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-primary h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <i class="bi bi-file-earmark-text text-primary" style="font-size: 2.5rem;"></i>
                    <span class="badge bg-primary"><?= $stats['propostas_enviadas'] ?? 0 ?></span>
                </div>
                <h4 class="mb-0"><?= $stats['propostas_enviadas'] ?? 0 ?></h4>
                <p class="text-muted mb-2">Propostas Enviadas</p>
                <small class="text-primary">
                    <i class="bi bi-arrow-up"></i> Total desde o início
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-success h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <span class="badge bg-success"><?= number_format($stats['taxa_conversao'] ?? 0, 1) ?>%</span>
                </div>
                <h4 class="mb-0"><?= $stats['propostas_aceitas'] ?? 0 ?></h4>
                <p class="text-muted mb-2">Propostas Aceitas</p>
                <small class="text-success">
                    <i class="bi bi-graph-up"></i> Taxa: <?= number_format($stats['taxa_conversao'] ?? 0, 1) ?>%
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-info h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <i class="bi bi-currency-dollar text-info" style="font-size: 2.5rem;"></i>
                    <span class="badge bg-info">R$</span>
                </div>
                <h4 class="mb-0">R$ <?= number_format($stats['valor_total_aceitas'] ?? 0, 0, ',', '.') ?></h4>
                <p class="text-muted mb-2">Valor Total</p>
                <small class="text-info">
                    <i class="bi bi-calculator"></i> Média: R$ <?= number_format($stats['valor_medio_aceitas'] ?? 0, 0, ',', '.') ?>
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-warning h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <i class="bi bi-star text-warning" style="font-size: 2.5rem;"></i>
                    <span class="badge bg-warning text-dark"><?= number_format($stats['avaliacao_media'] ?? 0, 1) ?></span>
                </div>
                <h4 class="mb-0"><?= number_format($stats['avaliacao_media'] ?? 0, 1) ?></h4>
                <p class="text-muted mb-2">Avaliação Média</p>
                <small class="text-warning">
                    <i class="bi bi-star-fill"></i> De 5.0 estrelas
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <!-- Gráfico de Propostas por Mês -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Propostas por Mês
                </h6>
                <span class="badge bg-primary">Últimos 12 meses</span>
            </div>
            <div class="card-body">
                <canvas id="chartPropostasMes" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Status das Propostas -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-pie-chart me-2"></i>Status das Propostas
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartStatusPropostas" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Gráfico de Valores por Mês -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-currency-dollar me-2"></i>Valores por Mês
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartValoresMes" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Taxa de Conversão -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-percent me-2"></i>Taxa de Conversão
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartConversao" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Comparação com Mercado e Tipos de Serviço -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Comparação com Mercado
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($graficos['comparacao_mercado']['meus_dados']) && !empty($graficos['comparacao_mercado']['media_mercado'])): ?>
                    <p class="text-center text-muted">Dados de comparação em desenvolvimento</p>
                <?php else: ?>
                    <p class="text-center text-muted">Dados insuficientes para comparação</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-tools me-2"></i>Tipos de Serviço
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($graficos['tipos_servico'])): ?>
                    <?php foreach ($graficos['tipos_servico'] as $tipo): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($tipo['nome'] ?? 'Tipo não informado') ?></span>
                            <span class="badge bg-primary"><?= $tipo['total'] ?? 0 ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Nenhum serviço cadastrado ainda</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Atividades -->
<div class="row">
    <!-- Serviços em Andamento -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Serviços em Andamento</h6>
                <a href="/chamaservico/prestador/servicos/andamento" class="btn btn-sm btn-outline-success">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($servicosAndamento)): ?>
                    <p class="text-center text-muted py-3">
                        <i class="bi bi-clipboard-check" style="font-size: 2.5rem; color: #ccc;"></i><br>
                        Nenhum serviço em andamento
                    </p>
                    <div class="text-center">
                        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Buscar Serviços
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($servicosAndamento as $servico): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($servico['titulo']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($servico['cliente_nome']) ?>
                                    <span class="ms-2">
                                        <i class="bi bi-currency-dollar me-1"></i>R$ <?= number_format($servico['valor'], 2, ',', '.') ?>
                                    </span>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge" style="background-color: <?= htmlspecialchars($servico['status_cor']) ?>;">
                                    <?= htmlspecialchars($servico['status_nome']) ?>
                                </span>
                                <div class="mt-1">
                                    <a href="/chamaservico/prestador/servicos/detalhes?id=<?= $servico['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Propostas -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Últimas Propostas</h6>
                <a href="/chamaservico/prestador/propostas" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                <?php if (empty($ultimasPropostas)): ?>
                    <p class="text-center text-muted py-3">
                        <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; color: #ccc;"></i><br>
                        Nenhuma proposta enviada ainda
                    </p>
                    <div class="text-center">
                        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Buscar Oportunidades
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($ultimasPropostas as $proposta): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($proposta['titulo']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-tools me-1"></i><?= htmlspecialchars($proposta['tipo_servico_nome']) ?>
                                    <span class="ms-2">
                                        <i class="bi bi-currency-dollar me-1"></i>R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                    </span>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($proposta['status']) ?>
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <?= date('d/m', strtotime($proposta['data_proposta'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados dos gráficos com verificação de existência
const dadosPropostasMes = ' . json_encode($graficos['propostas_mes'] ?? []) . ';
const dadosStatusPropostas = ' . json_encode($graficos['status_propostas'] ?? []) . ';
const dadosValoresMes = ' . json_encode($graficos['valores_mes'] ?? []) . ';
const dadosConversao = ' . json_encode($graficos['conversao_mes'] ?? []) . ';

// Configuração global dos gráficos
Chart.defaults.font.family = "Inter, -apple-system, BlinkMacSystemFont, sans-serif";
Chart.defaults.color = "#6c757d";

// Só renderizar gráficos se houver dados
if (dadosPropostasMes.length > 0) {
    // Gráfico de Propostas por Mês
    const ctxPropostasMes = document.getElementById("chartPropostasMes");
    if (ctxPropostasMes) {
        new Chart(ctxPropostasMes, {
            type: "line",
            data: {
                labels: dadosPropostasMes.map(item => {
                    const [ano, mes] = item.mes.split("-");
                    return new Date(ano, mes - 1).toLocaleDateString("pt-BR", { month: "short", year: "2-digit" });
                }),
                datasets: [
                    {
                        label: "Total",
                        data: dadosPropostasMes.map(item => item.total || 0),
                        borderColor: "#0d6efd",
                        backgroundColor: "rgba(13, 110, 253, 0.1)",
                        tension: 0.4
                    },
                    {
                        label: "Aceitas",
                        data: dadosPropostasMes.map(item => item.aceitas || 0),
                        borderColor: "#198754",
                        backgroundColor: "rgba(25, 135, 84, 0.1)",
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    }
}

if (dadosStatusPropostas.length > 0) {
    // Gráfico de Status das Propostas
    const ctxStatusPropostas = document.getElementById("chartStatusPropostas");
    if (ctxStatusPropostas) {
        new Chart(ctxStatusPropostas, {
            type: "doughnut",
            data: {
                labels: dadosStatusPropostas.map(item => {
                    const labels = {
                        "pendente": "Pendentes",
                        "aceita": "Aceitas", 
                        "recusada": "Recusadas",
                        "cancelada": "Canceladas"
                    };
                    return labels[item.status] || item.status;
                }),
                datasets: [{
                    data: dadosStatusPropostas.map(item => item.quantidade || 0),
                    backgroundColor: [
                        "#ffc107", // Pendente
                        "#198754", // Aceita
                        "#dc3545", // Recusada
                        "#6c757d"  // Cancelada
                    ],
                    borderWidth: 2,
                    borderColor: "#fff"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ": " + context.raw + " (" + percentage + "%)";
                            }
                        }
                    }
                }
            }
        });
    }
}

if (dadosValoresMes.length > 0) {
    // Gráfico de Valores por Mês
    const ctxValoresMes = document.getElementById("chartValoresMes");
    if (ctxValoresMes) {
        new Chart(ctxValoresMes, {
            type: "bar",
            data: {
                labels: dadosValoresMes.map(item => {
                    const [ano, mes] = item.mes.split("-");
                    return new Date(ano, mes - 1).toLocaleDateString("pt-BR", { month: "short", year: "2-digit" });
                }),
                datasets: [
                    {
                        label: "Valor Total Aceitas (R$)",
                        data: dadosValoresMes.map(item => parseFloat(item.valor_aceitas || 0)),
                        backgroundColor: "rgba(25, 135, 84, 0.8)",
                        borderColor: "#198754",
                        borderWidth: 1
                    },
                    {
                        label: "Valor Médio (R$)",
                        data: dadosValoresMes.map(item => parseFloat(item.valor_medio || 0)),
                        type: "line",
                        borderColor: "#fd7e14",
                        backgroundColor: "rgba(253, 126, 20, 0.1)",
                        tension: 0.4,
                        yAxisID: "y1"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top"
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ": R$ " + context.raw.toLocaleString("pt-BR", {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: "left",
                        ticks: {
                            callback: function(value) {
                                return "R$ " + value.toLocaleString("pt-BR");
                            }
                        }
                    },
                    y1: {
                        type: "linear",
                        position: "right",
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function(value) {
                                return "R$ " + value.toLocaleString("pt-BR");
                            }
                        }
                    }
                }
            }
        });
    }
}

if (dadosConversao.length > 0) {
    // Gráfico de Taxa de Conversão
    const ctxConversao = document.getElementById("chartConversao");
    if (ctxConversao) {
        new Chart(ctxConversao, {
            type: "line",
            data: {
                labels: dadosConversao.map(item => {
                    const [ano, mes] = item.mes.split("-");
                    return new Date(ano, mes - 1).toLocaleDateString("pt-BR", { month: "short", year: "2-digit" });
                }),
                datasets: [{
                    label: "Taxa de Conversão (%)",
                    data: dadosConversao.map(item => parseFloat(item.taxa_conversao || 0)),
                    borderColor: "#20c997",
                    backgroundColor: "rgba(32, 201, 151, 0.1)",
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + "%";
                            }
                        }
                    }
                }
            }
        });
    }
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>