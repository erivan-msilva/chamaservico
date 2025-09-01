<?php
$title = 'Dashboard Cliente - ChamaServiço';
ob_start();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-3 text-primary">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard Cliente
            </h2>
            <p class="text-muted">Bem-vindo ao seu painel de controle, <?= htmlspecialchars(Session::getUserName()) ?>! Acompanhe suas solicitações, propostas e serviços em andamento.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nova Solicitação
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Total de Solicitações</h6>
                            <h2 class="mt-2 mb-0 text-primary"><?= $estatisticas['total_solicitacoes'] ?></h2>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="bi bi-list-ul text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Aguardando Propostas</h6>
                            <h2 class="mt-2 mb-0 text-warning"><?= $estatisticas['aguardando_propostas'] ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-clock-history text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Em Andamento</h6>
                            <h2 class="mt-2 mb-0 text-info"><?= $estatisticas['em_andamento'] ?></h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-gear-fill text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Concluídos</h6>
                            <h2 class="mt-2 mb-0 text-success"><?= $estatisticas['concluidas'] ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimas Solicitações -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Últimas Solicitações</h5>
                    <a href="/chamaservico/cliente/solicitacoes" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($solicitacoesRecentes)): ?>
                        <div class="text-center p-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0 text-muted">Você ainda não tem solicitações</p>
                            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-sm btn-primary mt-2">
                                Criar Primeira Solicitação
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($solicitacoesRecentes as $solicitacao): ?>
                                <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
                                    class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                                        <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                            <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <i class="bi bi-tools me-1"></i><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?>
                                        <span class="ms-2">
                                            <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?>
                                        </span>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Propostas Recentes -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Propostas Recentes</h5>
                    <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($propostasRecentes)): ?>
                        <div class="text-center p-4">
                            <i class="bi bi-envelope text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0 text-muted">Você ainda não recebeu propostas</p>
                            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-sm btn-primary mt-2">
                                Criar Solicitação
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($propostasRecentes as $proposta): ?>
                                <a href="/chamaservico/cliente/propostas/detalhes?id=<?= $proposta['id'] ?>"
                                    class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($proposta['solicitacao_titulo']) ?></h6>
                                        <span class="badge bg-<?= $proposta['status'] === 'pendente' ? 'warning' : ($proposta['status'] === 'aceita' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($proposta['status']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($proposta['prestador_nome']) ?>
                                        <span class="ms-2">
                                            <i class="bi bi-cash-coin me-1"></i>R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                        </span>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráficos -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Estatísticas de Serviços</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Notificações Recentes -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-bell me-2"></i>Notificações Recentes</h5>
                    <a href="/chamaservico/notificacoes" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($notificacoesRecentes)): ?>
                        <div class="text-center p-4">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0 text-muted">Você não tem notificações</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notificacoesRecentes as $notificacao): ?>
                                <a href="/chamaservico/notificacoes"
                                    class="list-group-item list-group-item-action py-3 <?= $notificacao['lida'] ? '' : 'bg-light' ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($notificacao['titulo']) ?></h6>
                                        <small><?= date('d/m/Y H:i', strtotime($notificacao['data_notificacao'])) ?></small>
                                    </div>
                                    <p class="mb-1 small text-truncate">
                                        <?= htmlspecialchars(mb_substr($notificacao['mensagem'], 0, 100)) ?>
                                        <?= (mb_strlen($notificacao['mensagem']) > 100) ? '...' : '' ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Links Rápidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-outline-primary w-100 p-3">
                                <i class="bi bi-plus-circle mb-2" style="font-size: 1.5rem;"></i>
                                <div>Nova Solicitação</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-success w-100 p-3">
                                <i class="bi bi-envelope mb-2" style="font-size: 1.5rem;"></i>
                                <div>Ver Propostas</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-info w-100 p-3">
                                <i class="bi bi-list-check mb-2" style="font-size: 1.5rem;"></i>
                                <div>Minhas Solicitações</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/chamaservico/cliente/perfil/editar" class="btn btn-outline-secondary w-100 p-3">
                                <i class="bi bi-person-gear mb-2" style="font-size: 1.5rem;"></i>
                                <div>Editar Perfil</div>
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
    // Dados para o gráfico
    const ctxStatus = document.getElementById("statusChart").getContext("2d");
    const statusChart = new Chart(ctxStatus, {
        type: "doughnut",
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
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
});
</script>';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>