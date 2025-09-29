<?php
$title = 'Dashboard Cliente - ChamaServiço';
ob_start();
?>

<!-- Header Principal -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2 text-dark">
                                <i class="bi bi-speedometer2 me-3"></i>Dashboard Cliente
                            </h1>
                            <p class="text-dark opacity-75 mb-0">Bem-vindo, <?= htmlspecialchars(Session::getUserName()) ?>! Gerencie suas solicitações e acompanhe o progresso dos seus serviços.</p>
                        </div>
                        <div class="col-md-4 text-dark">
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <select class="form-select form-select-sm text-dark" style="max-width: 150px;">
                                    <option>Últimos 30 dias</option>
                                    <option>Últimos 7 dias</option>
                                    <option>Este mês</option>
                                    <option>Último mês</option>
                                </select>
                                <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-light btn-sm px-3">
                                    <i class="bi bi-plus-circle me-2"></i>Nova Solicitação
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Total de Solicitações</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?= $estatisticas['total_solicitacoes'] ?></h2>

                        </div>
                        <div class="col-auto">
                            <div class="bg-body bg-opacity-10 p-3 rounded-3">
                                <i class="bi bi-list-ul text-info" style="font-size: 1.75rem; background:  #3c8fe9ff;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #007bff, #0056b3);"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Aguardando Propostas</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?= $estatisticas['aguardando_propostas'] ?></h2>

                        </div>
                        <div class="col-auto">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="bi bi-hourglass-split text-warning" style="font-size: 1.75rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #ffc107, #e0a800);"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Em Andamento</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?= $estatisticas['em_andamento'] ?></h2>

                        </div>
                        <div class="col-auto">
                            <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                <i class="bi bi-gear-fill text-info" style="font-size: 1.75rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #17a2b8, #138496);"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Concluídos</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?= $estatisticas['concluidas'] ?></h2>

                        </div>
                        <div class="col-auto">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 1.75rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #28a745, #1e7e34);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4 mb-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>Status dos Serviços
                    </h5>
                    <p class="text-muted small mb-0">Distribuição por situação</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                    <canvas id="statusChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>


    <!-- Seção de Dados Recentes -->
    <div class="row g-4 mb-5">
        <!-- Últimas Solicitações -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-list-ul me-2 text-primary"></i>Últimas Solicitações
                        </h5>
                        <p class="text-muted small mb-0">Acompanhe suas solicitações mais recentes</p>
                    </div>
                    <a href="<?= url('cliente/solicitacoes') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Ver Todas
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($solicitacoesRecentes)): ?>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h6 class="text-muted mb-2">Nenhuma solicitação encontrada</h6>
                            <p class="text-muted small mb-3">Você ainda não criou nenhuma solicitação de serviço</p>
                            <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-2"></i>Criar Primeira Solicitação
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-bold text-uppercase small">Título</th>
                                        <th class="border-0 fw-bold text-uppercase small">Categoria</th>
                                        <th class="border-0 fw-bold text-uppercase small">Data</th>
                                        <th class="border-0 fw-bold text-uppercase small">Status</th>
                                        <th class="border-0 fw-bold text-uppercase small">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitacoesRecentes as $solicitacao): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <div class="fw-bold"><?= htmlspecialchars($solicitacao['titulo']) ?></div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-tools me-1"></i><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-muted small">
                                                <?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="<?= url('cliente/solicitacoes/visualizar?id=' . $solicitacao['id']) ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Propostas Recentes -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-envelope me-2 text-success"></i>Propostas
                        </h5>
                        <p class="text-muted small mb-0">Recebidas recentemente</p>
                    </div>
                    <a href="<?= url('cliente/propostas/recebidas') ?>" class="btn btn-outline-success btn-sm">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($propostasRecentes)): ?>
                        <div class="text-center py-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-envelope text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-2">Nenhuma proposta</h6>
                            <p class="text-muted small mb-3">Aguardando propostas dos prestadores</p>
                            <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-success btn-sm">Criar Solicitação</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($propostasRecentes as $proposta): ?>
                                <a href="<?= url('cliente/propostas/detalhes?id=' . $proposta['id']) ?>" class="list-group-item list-group-item-action border-0 py-3">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($proposta['solicitacao_titulo']) ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($proposta['prestador_nome']) ?>
                                            </p>
                                            <p class="mb-0 small fw-bold text-success">
                                                <i class="bi bi-cash-coin me-1"></i>R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-<?= $proposta['status'] === 'pendente' ? 'warning' : ($proposta['status'] === 'aceita' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($proposta['status']) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-lightning-charge me-2 text-warning"></i>Ações Rápidas
                    </h5>
                    <p class="text-muted small mb-0">Acesse rapidamente as principais funcionalidades</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-outline-primary w-100 py-3 text-decoration-none">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-plus-circle-fill mb-2" style="font-size: 2rem;"></i>
                                    <span class="fw-bold">Nova Solicitação</span>
                                    <small class="text-muted">Criar nova solicitação</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= url('cliente/propostas/recebidas') ?>" class="btn btn-outline-success w-100 py-3 text-decoration-none">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-envelope-check-fill mb-2" style="font-size: 2rem;"></i>
                                    <span class="fw-bold">Ver Propostas</span>
                                    <small class="text-muted">Gerenciar propostas</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= url('cliente/solicitacoes') ?>" class="btn btn-outline-info w-100 py-3 text-decoration-none">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-list-check mb-2" style="font-size: 2rem;"></i>
                                    <span class="fw-bold">Minhas Solicitações</span>
                                    <small class="text-muted">Ver todas solicitações</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= url('cliente/perfil/editar') ?>" class="btn btn-outline-secondary w-100 py-3 text-decoration-none">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-person-gear mb-2" style="font-size: 2rem;"></i>
                                    <span class="fw-bold">Editar Perfil</span>
                                    <small class="text-muted">Atualizar informações</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Gráfico de Status (barras)
    const ctxStatus = document.getElementById("statusChart").getContext("2d");
    const statusChart = new Chart(ctxStatus, {
        type: "bar",
        data: {
            labels: ["Aguardando Propostas", "Em Andamento", "Concluídos"],
            datasets: [{
                data: [
                    ' . $estatisticas['aguardando_propostas'] . ',
                    ' . $estatisticas['em_andamento'] . ',
                    ' . $estatisticas['concluidas'] . '
                ],
                backgroundColor: [
                    "#FFC107",
                    "#17A2B8", 
                    "#28A745"
                ],
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    display: false,
                    beginAtZero: true,
                    grid: {
                        color: "#f8f9fa"
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de Categorias (Barras)
    const ctxCategory = document.getElementById("categoryChart").getContext("2d");
    const categoryChart = new Chart(ctxCategory, {
        type: "bar",
        data: {
            labels: ["Limpeza", "Manutenção", "Jardinagem", "Técnicos", "Consultoria", "Outros"],
            datasets: [{
                label: "Solicitações",
                data: [12, 8, 15, 6, 4, 3],
                backgroundColor: [
                    "#007bff",
                    "#28a745", 
                    "#ffc107",
                    "#17a2b8",
                    "#6f42c1",
                    "#fd7e14"
                ],
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "#f8f9fa"
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>';
$content = ob_get_clean();
include 'views/layouts/app.php';
?>