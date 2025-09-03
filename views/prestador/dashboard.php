<?php
$title = 'Dashboard - Prestador';
ob_start();
?>

<div class="container-fluid">
    <!-- Header moderno e limpo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="bi bi-speedometer2 me-2" style="color: #f5a522;"></i>
                Dashboard Prestador
            </h1>
            <p class="text-muted mb-0">Visão geral das suas atividades como prestador de serviços</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Buscar Serviços
            </a>
            <a href="/chamaservico/prestador/propostas" class="btn btn-outline-warning">
                <i class="bi bi-file-earmark-text me-2"></i>Minhas Propostas
            </a>
        </div>
    </div>

    <!-- KPIs - Cards de Estatísticas Modernos -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-send text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1 text-dark"><?= $estatisticas['total_propostas'] ?></h3>
                            <p class="text-muted mb-0 small">Propostas Enviadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1 text-dark"><?= $estatisticas['propostas_aceitas'] ?></h3>
                            <p class="text-muted mb-0 small">Propostas Aceitas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-trophy text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1 text-dark"><?= $estatisticas['servicos_concluidos'] ?></h3>
                            <p class="text-muted mb-0 small">Serviços Concluídos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-star-fill text-info" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1 text-dark"><?= $estatisticas['avaliacao_media'] ?></h3>
                            <p class="text-muted mb-0 small">Avaliação Média</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Propostas por Mês - Gráfico Moderno -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-bar-chart me-2" style="color: #f5a522;"></i>
                        Propostas por Mês
                    </h5>
                    <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="propostas-chart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Status das Propostas - Gráfico de Pizza Moderno -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-pie-chart me-2" style="color: #f5a522;"></i>
                        Status das Propostas
                    </h5>
                    <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="status-chart" width="300" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ações Rápidas - Redesign com Hierarquia -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-lightning me-2" style="color: #f5a522;"></i>
                        Ações Rápidas
                    </h5>
                    <div class="d-grid gap-3">
                        <!-- Ação Primária - Mais Destaque -->
                        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary btn-lg">
                            <i class="bi bi-search me-2"></i>
                            Buscar Novos Serviços
                        </a>
                        
                        <!-- Ações Secundárias -->
                        <a href="/chamaservico/prestador/propostas" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Ver Minhas Propostas
                        </a>
                        
                        <a href="/chamaservico/prestador/servicos/andamento" class="btn btn-outline-secondary">
                            <i class="bi bi-tools me-2"></i>
                            Serviços em Andamento
                        </a>
                        
                        <a href="/chamaservico/prestador/perfil/editar" class="btn btn-outline-secondary">
                            <i class="bi bi-person-gear me-2"></i>
                            Editar Perfil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Atividade Recente - Estado Vazio Moderno -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-clock-history me-2" style="color: #f5a522;"></i>
                        Atividade Recente
                    </h5>
                    
                    <?php if (empty($ultimasPropostas)): ?>
                        <!-- Estado Vazio Aprimorado -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-activity" style="font-size: 4rem; color: #e9ecef;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">Nenhuma atividade ainda</h6>
                            <p class="text-muted mb-4 small">
                                Suas atividades aparecerão aqui<br>
                                Envie propostas e acompanhe seus serviços
                            </p>
                            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>
                                Começar Agora
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Lista de Atividades -->
                        <div class="timeline">
                            <?php foreach ($ultimasPropostas as $proposta): ?>
                                <div class="timeline-item d-flex align-items-start mb-3">
                                    <div class="timeline-marker bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-1 text-dark">
                                            <?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Serviço') ?>
                                        </h6>
                                        <p class="text-muted small mb-1">
                                            Proposta de R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                        </p>
                                        <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'pendente' ? 'warning' : 'secondary') ?> small">
                                            <?= ucfirst($proposta['status']) ?>
                                        </span>
                                        <span class="text-muted small ms-2">
                                            <?= date('d/m/Y', strtotime($proposta['data_proposta'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary btn-sm">
                                Ver Todas as Propostas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS para Timeline e Melhorias Visuais -->
<style>
    /* Timeline personalizada */
    .timeline-item {
        position: relative;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 18px;
        top: 40px;
        bottom: -15px;
        width: 2px;
        background: linear-gradient(to bottom, #e9ecef, transparent);
    }
    
    .timeline-marker {
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }
    
    /* Hover effects para cards */
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    
    /* Botões com transições suaves */
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    /* Melhorias nos badges */
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
    
    /* Canvas responsivo */
    canvas {
        max-width: 100%;
        height: auto !important;
    }
    
    /* Estados vazios */
    .text-center .bi {
        opacity: 0.3;
    }
    
    /* Ícones coloridos */
    .card-body i[style*="color: #f5a522"] {
        filter: drop-shadow(0 2px 4px rgba(245, 165, 34, 0.2));
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração dos gráficos com paleta moderna
    const colors = {
        primary: '#283579',
        accent: '#f5a522',
        success: '#28a745',
        info: '#17a2b8',
        warning: '#ffc107',
        gradient: ['#283579', '#3d4a8a', '#586bb5', '#7589d1']
    };

    // Gráfico de Propostas por Mês
    const proposalCtx = document.getElementById('propostas-chart');
    if (proposalCtx) {
        new Chart(proposalCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Propostas Enviadas',
                    data: [2, 5, 3, 8, 6, 4],
                    borderColor: colors.accent,
                    backgroundColor: colors.accent + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.accent,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
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
                        grid: {
                            color: '#f8f9fa'
                        },
                        ticks: {
                            color: '#6c757d'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6c757d'
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Status das Propostas
    const statusCtx = document.getElementById('status-chart');
    if (statusCtx) {
        const total = <?= $estatisticas['total_propostas'] ?>;
        const aceitas = <?= $estatisticas['propostas_aceitas'] ?>;
        const pendentes = total - aceitas;
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aceitas', 'Pendentes', 'Outras'],
                datasets: [{
                    data: [aceitas, pendentes, Math.max(0, total - aceitas - pendentes)],
                    backgroundColor: [
                        colors.success,
                        colors.accent,
                        '#e9ecef'
                    ],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: '#6c757d'
                        }
                    }
                }
            }
        });
    }

    // Animação de entrada para os KPIs
    const kpiCards = document.querySelectorAll('.row.g-4.mb-5 .card');
    kpiCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>