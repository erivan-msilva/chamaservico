<?php
$title = 'Minhas Propostas - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text me-2"></i>Minhas Propostas</h2>
    <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success">
        <i class="bi bi-search me-1"></i>Buscar Mais Serviços
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= ($_GET['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="aceita" <?= ($_GET['status'] ?? '') == 'aceita' ? 'selected' : '' ?>>Aceita</option>
                    <option value="recusada" <?= ($_GET['status'] ?? '') == 'recusada' ? 'selected' : '' ?>>Recusada</option>
                    <option value="cancelada" <?= ($_GET['status'] ?? '') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="/chamaservico/prestador/propostas" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($propostas)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma proposta encontrada</h4>
        <p class="text-muted">Você ainda não enviou propostas para solicitações.</p>
        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success">
            <i class="bi bi-search me-1"></i>Buscar Serviços Disponíveis
        </a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($propostas as $proposta): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?>
                        </small>
                        <span class="badge bg-<?= ($proposta['status'] ?? 'pendente') == 'pendente' ? 'warning' : (($proposta['status'] ?? 'pendente') == 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'danger' : 'secondary')) ?>">
                            <?= ucfirst($proposta['status'] ?? 'pendente') ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Título não disponível') ?></h6>
                        
                        <div class="mb-3">
                            <strong class="text-success">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></strong>
                            <span class="text-muted">em <?= $proposta['prazo_execucao'] ?? 'N/A' ?> dia(s)</span>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-primary">
                                <i class="bi bi-person me-1"></i>
                                Cliente: <?= htmlspecialchars($proposta['cliente_nome'] ?? 'Nome não disponível') ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars(($proposta['cidade'] ?? 'Cidade') . ', ' . ($proposta['estado'] ?? 'UF')) ?>
                            </small>
                        </div>
                        
                        <p class="card-text"><?= htmlspecialchars(substr($proposta['descricao'] ?? 'Sem descrição', 0, 100)) ?>...</p>
                        
                        <!-- Informações do status da solicitação -->
                        <div class="mb-3">
                            <span class="badge" style="background-color: <?= htmlspecialchars($proposta['status_cor'] ?? '#ccc') ?>;">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= htmlspecialchars($proposta['status_nome'] ?? 'Status não disponível') ?>
                            </span>
                        </div>
                        
                        <!-- Urgência da solicitação -->
                        <div class="mb-3">
                            <span class="badge bg-<?= ($proposta['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($proposta['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                Urgência: <?= ucfirst($proposta['urgencia'] ?? 'media') ?>
                            </span>
                        </div>
                        
                        <!-- Orçamento estimado -->
                        <?php if (!empty($proposta['orcamento_estimado'])): ?>
                            <div class="mb-3">
                                <small class="text-info">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    Orçamento estimado: R$ <?= number_format($proposta['orcamento_estimado'], 2, ',', '.') ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Card da proposta - adicionar indicador de negociação -->
                    <div class="card-footer" style="background: transparent; border-top: 1px solid #4e5264;">
                        <div class="d-flex gap-2">
                            <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $proposta['solicitacao_id'] ?>" 
                               class="btn btn-outline-success btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>Ver Solicitação
                            </a>
                            
                            <?php if ($proposta['status'] === 'pendente'): ?>
                                <!-- Verificar se tem negociação ativa -->
                                <?php 
                                $temNegociacao = $this->model->temNegociacaoAtiva($proposta['id']);
                                if ($temNegociacao): 
                                ?>
                                    <a href="/chamaservico/negociacao/negociar?proposta_id=<?= $proposta['id'] ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-chat-square-dots me-1"></i>Negociar
                                    </a>
                                <?php endif; ?>
                                
                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="confirmarCancelamento(<?= $proposta['id'] ?>)">
                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal de Confirmação para Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja cancelar esta proposta?</p>
                <p class="text-warning"><small>Esta ação não pode ser desfeita e você não poderá enviar uma nova proposta para esta solicitação.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <form method="POST" action="/chamaservico/prestador/propostas/cancelar" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdCancelar">
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function cancelarProposta(id) {
    document.getElementById("propostaIdCancelar").value = id;
    new bootstrap.Modal(document.getElementById("modalCancelar")).show();
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
