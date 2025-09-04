<?php
$title = 'Serviços em Andamento - Prestador';
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-tools text-primary me-2"></i>
                Serviços em Andamento
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="prestador/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Serviços em Andamento</li>
                </ol>
            </nav>
        </div>
        <a href="prestador/dashboard" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos os Status</option>
                        <option value="3" <?= ($_GET['status'] ?? '') == '3' ? 'selected' : '' ?>>Proposta Aceita</option>
                        <option value="4" <?= ($_GET['status'] ?? '') == '4' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="5" <?= ($_GET['status'] ?? '') == '5' ? 'selected' : '' ?>>Concluído</option>
                        <option value="16" <?= ($_GET['status'] ?? '') == '16' ? 'selected' : '' ?>>Aguardando Materiais</option>
                        <option value="15" <?= ($_GET['status'] ?? '') == '15' ? 'selected' : '' ?>>Suspenso</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="urgencia" class="form-label">Urgência</label>
                    <select class="form-select" id="urgencia" name="urgencia">
                        <option value="">Todas as Urgências</option>
                        <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                        <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="busca" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busca" name="busca" 
                           value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" 
                           placeholder="Título, cliente, cidade...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($servicosAndamento)): ?>
        <!-- Estado Vazio -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-tools" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted">Nenhum serviço em andamento</h4>
            <p class="text-muted mb-4">
                Quando você tiver propostas aceitas pelos clientes, elas aparecerão aqui.
            </p>
            <a href="prestador/solicitacoes" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>
                Buscar Novas Solicitações
            </a>
        </div>
    <?php else: ?>
        <!-- Lista de Serviços -->
        <div class="row">
            <?php foreach ($servicosAndamento as $servico): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <!-- Header do Card -->
                        <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-tools me-2"></i>
                                    <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                </h6>
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars($servico['status_nome']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Corpo do Card -->
                        <div class="card-body d-flex flex-column">
                            <!-- Título -->
                            <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                            
                            <!-- Descrição -->
                            <p class="card-text text-muted flex-grow-1">
                                <?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>
                                <?= strlen($servico['descricao']) > 100 ? '...' : '' ?>
                            </p>

                            <!-- Informações Principais -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Valor:</small>
                                    <div class="fw-bold text-success">
                                        R$ <?= number_format($servico['valor'], 2, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Urgência:</small>
                                    <div>
                                        <?php
                                        $urgenciaBadge = [
                                            'baixa' => 'success',
                                            'media' => 'warning',
                                            'alta' => 'danger'
                                        ];
                                        $urgenciaTexto = [
                                            'baixa' => 'Baixa',
                                            'media' => 'Média',
                                            'alta' => 'Alta'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $urgenciaBadge[$servico['urgencia']] ?? 'secondary' ?>">
                                            <?= $urgenciaTexto[$servico['urgencia']] ?? 'N/A' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="mb-3">
                                <small class="text-muted">Cliente:</small>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person text-primary me-2"></i>
                                    <strong><?= htmlspecialchars($servico['cliente_nome']) ?></strong>
                                </div>
                            </div>

                            <!-- Localização -->
                            <div class="mb-3">
                                <small class="text-muted">Local:</small>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt text-warning me-2"></i>
                                    <span><?= htmlspecialchars($servico['bairro']) ?> - <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?></span>
                                </div>
                            </div>

                            <!-- Data Preferencial -->
                            <?php if ($servico['data_atendimento']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Data Preferencial:</small>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-event text-info me-2"></i>
                                        <span><?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Prazo -->
                            <?php if ($servico['prazo_execucao']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Prazo Acordado:</small>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock text-secondary me-2"></i>
                                        <span><?= $servico['prazo_execucao'] ?> dia(s)</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Status Visual -->
                            <div class="mb-3">
                                <div class="progress" style="height: 8px;">
                                    <?php
                                    $progressValue = [
                                        3 => 25,  // Proposta Aceita
                                        4 => 50,  // Em Andamento
                                        5 => 100, // Concluído
                                        16 => 40, // Aguardando Materiais
                                        15 => 30  // Suspenso
                                    ];
                                    $statusId = $servico['status_id'] ?? 0;
                                    $progress = $progressValue[$statusId] ?? 25;
                                    $progressColor = $statusId == 5 ? 'success' : ($statusId == 15 ? 'warning' : 'primary');
                                    ?>
                                    <div class="progress-bar bg-<?= $progressColor ?>" 
                                         style="width: <?= $progress ?>%"></div>
                                </div>
                                <small class="text-muted">Progresso do serviço</small>
                            </div>
                        </div>

                        <!-- Footer do Card -->
                        <div class="card-footer bg-transparent border-0">
                            <div class="d-grid gap-2">
                                <a href="prestador/servicos/detalhes?id=<?= $servico['id'] ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-eye me-2"></i>
                                    Ver Detalhes
                                </a>
                                
                                <!-- Ações Rápidas -->
                                <div class="btn-group" role="group">
                                    <?php if (!empty($servico['cliente_telefone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="https://www.google.com/maps/search/<?= urlencode($servico['logradouro'] . ', ' . $servico['numero'] . ', ' . $servico['bairro'] . ', ' . $servico['cidade'] . ', ' . $servico['estado']) ?>" 
                                       target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-map"></i>
                                    </a>
                                    
                                    <?php 
                                    $statusId = $servico['status_id'] ?? 0;
                                    if ($statusId != 5): // Não concluído ?>
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                onclick="atualizarStatusRapido(<?= $servico['id'] ?>, 'concluido')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Estatísticas -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white border-0">
                    <div class="card-body text-center">
                        <h3><?= count($servicosAndamento) ?></h3>
                        <small>Total de Serviços</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white border-0">
                    <div class="card-body text-center">
                        <h3><?= count(array_filter($servicosAndamento, fn($s) => ($s['status_id'] ?? 0) == 4)) ?></h3>
                        <small>Em Andamento</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white border-0">
                    <div class="card-body text-center">
                        <h3><?= count(array_filter($servicosAndamento, fn($s) => ($s['status_id'] ?? 0) == 5)) ?></h3>
                        <small>Concluídos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white border-0">
                    <div class="card-body text-center">
                        <h3>R$ <?= number_format(array_sum(array_column($servicosAndamento, 'valor')), 2, ',', '.') ?></h3>
                        <small>Valor Total</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Atualização Rápida -->
<div class="modal fade" id="modalStatusRapido" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="prestador/servicos/atualizar-status">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="rapidoPropostaId">
                    <input type="hidden" name="novo_status" id="rapidoNovoStatus">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Tem certeza que deseja marcar este serviço como <strong id="rapidoStatusTexto"></strong>?
                    </div>
                    
                    <div class="mb-3">
                        <label for="rapidoObservacoes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="rapidoObservacoes" name="observacoes" rows="3"
                                  placeholder="Adicione observações sobre o serviço..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function atualizarStatusRapido(propostaId, novoStatus) {
    document.getElementById('rapidoPropostaId').value = propostaId;
    document.getElementById('rapidoNovoStatus').value = novoStatus;
    
    const statusTextos = {
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'aguardando_materiais': 'Aguardando Materiais',
        'suspenso': 'Suspenso'
    };
    
    document.getElementById('rapidoStatusTexto').textContent = statusTextos[novoStatus] || novoStatus;
    
    new bootstrap.Modal(document.getElementById('modalStatusRapido')).show();
}

// Auto-refresh a cada 5 minutos
setTimeout(() => {
    location.reload();
}, 300000);
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>