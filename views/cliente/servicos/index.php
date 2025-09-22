<?php
$title = $title ?? 'Serviços - ChamaServiço';
ob_start();

// Função auxiliar para exibir dados com segurança
function safeDisplay($value, $default = '') {
    return htmlspecialchars($value ?? $default);
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">
                    <i class="bi bi-tools me-2"></i>
                    <?php
                    switch($statusFiltro ?? 'todos') {
                        case 'concluidos': echo 'Serviços Concluídos'; break;
                        case 'andamento': echo 'Serviços em Andamento'; break;
                        case 'cancelados': echo 'Serviços Cancelados'; break;
                        default: echo 'Meus Serviços'; break;
                    }
                    ?>
                </h2>
                <div class="d-flex gap-2">
                    <a href="<?= url('cliente/dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Dashboard
                    </a>
                    <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Nova Solicitação
                    </a>
                </div>
            </div>

            <!-- Filtros de Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <a href="<?= url('cliente/servicos') ?>" 
                                   class="btn <?= ($statusFiltro ?? 'todos') === 'todos' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="bi bi-list me-1"></i>Todos
                                </a>
                                <a href="<?= url('cliente/servicos/andamento') ?>" 
                                   class="btn <?= ($statusFiltro ?? '') === 'andamento' ? 'btn-warning' : 'btn-outline-warning' ?>">
                                    <i class="bi bi-clock me-1"></i>Em Andamento
                                </a>
                                <a href="<?= url('cliente/servicos/concluidos') ?>" 
                                   class="btn <?= ($statusFiltro ?? '') === 'concluidos' ? 'btn-success' : 'btn-outline-success' ?>">
                                    <i class="bi bi-check-circle me-1"></i>Concluídos
                                </a>
                                <a href="<?= url('cliente/servicos/cancelados') ?>" 
                                   class="btn <?= ($statusFiltro ?? '') === 'cancelados' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                    <i class="bi bi-x-circle me-1"></i>Cancelados
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Serviços -->
            <?php if (!empty($servicos)): ?>
                <div class="row">
                    <?php foreach ($servicos as $servico): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-truncate"><?= safeDisplay($servico['titulo']) ?></h6>
                                        <span class="badge bg-<?= getStatusColor($servico['status_id'] ?? 1) ?>">
                                            <?= getStatusLabel($servico['status_id'] ?? 1) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($servico['data_solicitacao'])) ?>
                                    </p>
                                    
                                    <?php if (!empty($servico['tipo_servico_nome'])): ?>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-tools me-1"></i>
                                            <?= safeDisplay($servico['tipo_servico_nome']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="card-text">
                                        <?= substr(safeDisplay($servico['descricao']), 0, 100) ?>...
                                    </p>
                                    
                                    <?php if (!empty($servico['orcamento_estimado'])): ?>
                                        <p class="text-success fw-bold mb-0">
                                            R$ <?= number_format($servico['orcamento_estimado'], 2, ',', '.') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <div class="d-flex gap-2">
                                        <a href="<?= url('cliente/servicos/detalhes?id=' . $servico['id']) ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill">
                                            <i class="bi bi-eye me-1"></i>Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Estado vazio -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">
                        <?php
                        switch($statusFiltro ?? 'todos') {
                            case 'concluidos': echo 'Nenhum serviço concluído'; break;
                            case 'andamento': echo 'Nenhum serviço em andamento'; break;
                            case 'cancelados': echo 'Nenhum serviço cancelado'; break;
                            default: echo 'Nenhum serviço encontrado'; break;
                        }
                        ?>
                    </h4>
                    <p class="text-muted mb-4">
                        <?= ($statusFiltro ?? 'todos') === 'todos' ? 'Você ainda não tem serviços cadastrados.' : 'Não há serviços nesta categoria.' ?>
                    </p>
                    <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Criar Primeira Solicitação
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Funções auxiliares para status
function getStatusColor($statusId) {
    switch ($statusId) {
        case 5: return 'success';     // Concluído
        case 3: return 'warning';     // Em andamento
        case 6: return 'danger';      // Cancelado
        case 1: return 'secondary';   // Pendente
        case 2: return 'info';        // Em análise
        case 4: return 'primary';     // Aceito
        default: return 'secondary';
    }
}

function getStatusLabel($statusId) {
    switch ($statusId) {
        case 5: return 'Concluído';
        case 3: return 'Em Andamento';
        case 6: return 'Cancelado';
        case 1: return 'Pendente';
        case 2: return 'Em Análise';
        case 4: return 'Aceito';
        default: return 'Não definido';
    }
}
?>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
