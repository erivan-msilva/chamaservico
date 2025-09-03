<?php
$title = 'Minhas Propostas - Prestador';
ob_start();
?>

<div class="container-fluid">
    <!-- Header da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                Minhas Propostas
            </h2>
            <p class="text-muted mb-0">Gerencie suas propostas enviadas para clientes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>
                Buscar Serviços
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-0">Total de Propostas</h6>
                            <h3 class="mb-0 text-primary"><?= $estatisticas['total'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-0">Aceitas</h6>
                            <h3 class="mb-0 text-success"><?= $estatisticas['aceitas'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-0">Pendentes</h6>
                            <h3 class="mb-0 text-warning"><?= $estatisticas['pendentes'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-x-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-0">Recusadas</h6>
                            <h3 class="mb-0 text-danger"><?= $estatisticas['recusadas'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos os status</option>
                        <option value="pendente" <?= ($_GET['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="aceita" <?= ($_GET['status'] ?? '') === 'aceita' ? 'selected' : '' ?>>Aceita</option>
                        <option value="recusada" <?= ($_GET['status'] ?? '') === 'recusada' ? 'selected' : '' ?>>Recusada</option>
                        <option value="cancelada" <?= ($_GET['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="ordenacao" class="form-label">Ordenação</label>
                    <select class="form-select" id="ordenacao" name="ordenacao">
                        <option value="data_desc" <?= ($_GET['ordenacao'] ?? 'data_desc') === 'data_desc' ? 'selected' : '' ?>>Mais recentes</option>
                        <option value="data_asc" <?= ($_GET['ordenacao'] ?? '') === 'data_asc' ? 'selected' : '' ?>>Mais antigas</option>
                        <option value="valor_desc" <?= ($_GET['ordenacao'] ?? '') === 'valor_desc' ? 'selected' : '' ?>>Maior valor</option>
                        <option value="valor_asc" <?= ($_GET['ordenacao'] ?? '') === 'valor_asc' ? 'selected' : '' ?>>Menor valor</option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-1"></i>
                        Filtrar
                    </button>
                    <a href="/chamaservico/prestador/propostas" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Propostas -->
    <div class="row">
        <?php if (empty($propostas)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">Nenhuma proposta encontrada</h5>
                        <p class="text-muted mb-4">
                            <?php if (!empty($_GET['status']) || !empty($_GET['busca'])): ?>
                                Tente ajustar os filtros para encontrar suas propostas.
                            <?php else: ?>
                                Você ainda não enviou nenhuma proposta. Que tal buscar alguns serviços?
                            <?php endif; ?>
                        </p>
                        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>
                            Buscar Serviços Disponíveis
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($propostas as $proposta): ?>
                <div class="col-xl-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1">
                                        <i class="bi bi-<?= $proposta['tipo_servico_nome'] === 'Limpeza Residencial' ? 'house' : 'tools' ?> text-primary me-2"></i>
                                        <?= htmlspecialchars($proposta['solicitacao_titulo']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($proposta['cidade']) ?>, <?= htmlspecialchars($proposta['estado']) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php
                                    $statusConfig = [
                                        'pendente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pendente'],
                                        'aceita' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Aceita'],
                                        'recusada' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Recusada'],
                                        'cancelada' => ['class' => 'secondary', 'icon' => 'x-octagon', 'text' => 'Cancelada']
                                    ];
                                    $status = $statusConfig[$proposta['status']] ?? $statusConfig['pendente'];
                                    ?>
                                    <span class="badge bg-<?= $status['class'] ?>">
                                        <i class="bi bi-<?= $status['icon'] ?> me-1"></i>
                                        <?= $status['text'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Valor Proposto</small>
                                    <strong class="text-success fs-5">
                                        R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                    </strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Prazo</small>
                                    <strong>
                                        <?= $proposta['prazo_execucao'] ? $proposta['prazo_execucao'] . ' dia(s)' : 'A combinar' ?>
                                    </strong>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Cliente</small>
                                <strong><?= htmlspecialchars($proposta['cliente_nome']) ?></strong>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Sua Proposta</small>
                                <p class="mb-0"><?= htmlspecialchars(substr($proposta['descricao'], 0, 120)) ?><?= strlen($proposta['descricao']) > 120 ? '...' : '' ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    Enviada em <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'])) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group" role="group">
                                    <a href="/chamaservico/prestador/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>
                                        Detalhes
                                    </a>
                                    
                                    <?php if ($proposta['status'] === 'aceita'): ?>
                                        <a href="/chamaservico/prestador/servicos/detalhes?id=<?= $proposta['id'] ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="bi bi-tools me-1"></i>
                                            Gerenciar Serviço
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($proposta['status'] === 'pendente'): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelarProposta(<?= $proposta['id'] ?>)">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Confirmação para Cancelar Proposta -->
<div class="modal fade" id="modalCancelarProposta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Confirmar Cancelamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja cancelar esta proposta?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Atenção:</strong> Esta ação não pode ser desfeita. Você poderá enviar uma nova proposta posteriormente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>
                    Não, manter proposta
                </button>
                <form method="POST" action="/chamaservico/prestador/propostas/cancelar" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="proposta_id_cancelar" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>
                        Sim, cancelar proposta
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cancelarProposta(propostaId) {
    document.getElementById('proposta_id_cancelar').value = propostaId;
    const modal = new bootstrap.Modal(document.getElementById('modalCancelarProposta'));
    modal.show();
}

// Auto-refresh das estatísticas (opcional)
document.addEventListener('DOMContentLoaded', function() {
    // Destacar propostas recém aceitas ou recusadas
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const badge = card.querySelector('.badge');
        if (badge) {
            if (badge.textContent.includes('Aceita')) {
                card.style.borderLeft = '4px solid #198754';
            } else if (badge.textContent.includes('Recusada')) {
                card.style.borderLeft = '4px solid #dc3545';
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
