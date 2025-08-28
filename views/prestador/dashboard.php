<?php
$title = 'Dashboard Prestador - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: #f5a522;"><i class="bi bi-speedometer2 me-2"></i>Dashboard Prestador</h2>
            <p class="text-muted mb-0">Visão geral das suas atividades como prestador de serviços</p>
        </div>
        <div>
            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success me-2">
                <i class="bi bi-search me-1"></i>Buscar Serviços
            </a>
            <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="text-primary mb-0"><?= $stats['propostas_enviadas'] ?? 0 ?></h3>
                            <small class="text-muted">Propostas Enviadas</small>
                        </div>
                        <i class="bi bi-send text-primary" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="text-success mb-0"><?= $stats['propostas_aceitas'] ?? 0 ?></h3>
                            <small class="text-muted">Propostas Aceitas</small>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-info h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="text-info mb-0">R$ <?= number_format($stats['valor_total_aceitas'] ?? 0, 2, ',', '.') ?></h3>
                            <small class="text-muted">Valor Total Aceitas</small>
                        </div>
                        <span class="fs-2 text-info" style="opacity: 0.7;">R$</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="text-warning mb-0"><?= number_format($stats['avaliacao_media'] ?? 0, 1) ?></h3>
                            <small class="text-muted">Avaliação Média</small>
                        </div>
                        <i class="bi bi-star-fill text-warning" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #f5a522;">
                        <i class="bi bi-graph-up me-2"></i>Propostas por Mês
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPropostasMes" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #f5a522;">
                        <i class="bi bi-pie-chart me-2"></i>Status das Propostas
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartStatusPropostas" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #f5a522;">
                        <i class="bi bi-lightning me-2"></i>Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Buscar Novos Serviços
                        </a>
                        <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-2"></i>Ver Minhas Propostas
                        </a>
                        <a href="/chamaservico/prestador/servicos/andamento" class="btn btn-outline-info">
                            <i class="bi bi-tools me-2"></i>Serviços em Andamento
                        </a>
                        <a href="/chamaservico/prestador/perfil" class="btn btn-outline-secondary">
                            <i class="bi bi-person-gear me-2"></i>Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #f5a522;">
                        <i class="bi bi-clock me-2"></i>Atividade Recente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-data" style="font-size: 3rem; color: #6c757d;"></i>
                        <h6 class="mt-3 text-muted">Suas atividades aparecerão aqui</h6>
                        <p class="text-muted mb-4">Envie propostas e acompanhe seus serviços</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados dos gráficos vindos do PHP
    const propostasMes = <?= json_encode($graficos['propostas_mes'] ?? []) ?>;
    const statusPropostas = <?= json_encode($graficos['status_propostas'] ?? []) ?>;
    
    // Gráfico de Propostas por Mês
    const ctxMes = document.getElementById('chartPropostasMes');
    if (ctxMes) {
        const meses = propostasMes.map(item => {
            const [ano, mes] = item.mes.split('-');
            const nomesMeses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return nomesMeses[parseInt(mes) - 1] + '/' + ano.substr(2);
        });
        const valores = propostasMes.map(item => item.total);
        
        new Chart(ctxMes, {
            type: 'line',
            data: {
                labels: meses.length > 0 ? meses : ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Propostas Enviadas',
                    data: valores.length > 0 ? valores : [0, 0, 0, 0, 0, 0],
                    borderColor: '#f5a522',
                    backgroundColor: 'rgba(245, 165, 34, 0.1)',
                    tension: 0.4,
                    fill: true
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
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de Status das Propostas
    const ctxStatus = document.getElementById('chartStatusPropostas');
    if (ctxStatus) {
        const labels = statusPropostas.map(item => {
            const statusLabels = {
                'pendente': 'Pendentes',
                'aceita': 'Aceitas',
                'recusada': 'Recusadas',
                'cancelada': 'Canceladas'
            };
            return statusLabels[item.status] || item.status;
        });
        const dados = statusPropostas.map(item => item.total);
        
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: labels.length > 0 ? labels : ['Sem dados'],
                datasets: [{
                    data: dados.length > 0 ? dados : [1],
                    backgroundColor: dados.length > 0 ? ['#ffc107', '#28a745', '#dc3545', '#6c757d'] : ['#e9ecef']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>