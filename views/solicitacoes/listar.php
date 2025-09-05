<?php
$title = 'Minhas Solicitações - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-task me-2"></i>Minhas Solicitações</h2>
    <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nova Solicitação
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">Todos os status</option>
                    <option value="1" <?= ($_GET['status'] ?? '') == '1' ? 'selected' : '' ?>>Aguardando Propostas</option>
                    <option value="3" <?= ($_GET['status'] ?? '') == '3' ? 'selected' : '' ?>>Proposta Aceita</option>
                    <option value="5" <?= ($_GET['status'] ?? '') == '5' ? 'selected' : '' ?>>Concluído</option>
                    <option value="6" <?= ($_GET['status'] ?? '') == '6' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="urgencia" class="form-label">Urgência</label>
                <select class="form-select" name="urgencia" id="urgencia">
                    <option value="">Todas as urgências</option>
                    <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="busca" class="form-label">Buscar</label>
                <input type="text" class="form-control" name="busca" id="busca"
                    placeholder="Título ou descrição..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($solicitacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma solicitação encontrada</h4>
        <p class="text-muted">
            <?= !empty($_GET['status']) || !empty($_GET['urgencia']) || !empty($_GET['busca'])
                ? 'Tente ajustar os filtros ou ' : '' ?>
            Crie sua primeira solicitação de serviço!
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Criar Solicitação
            </a>
            <a href="<?= url('cliente/solicitacoes') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar Filtros
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($solicitacoes as $solicitacao): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <small class="text-with">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                        </small>
                        <span class="badge" style="background-color: <?= htmlspecialchars($solicitacao['status_cor'] ?? '#283579') ?>;">
                            <?= htmlspecialchars($solicitacao['status_nome'] ?? 'Sem status') ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($solicitacao['titulo'] ?? 'Sem título') ?></h6>
                        <p class="card-text">
                            <small class="text-primary">
                                <i class="bi bi-tools me-1"></i>
                                <?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'Tipo não informado') ?>
                            </small>
                        </p>
                        <p class="card-text"><?= htmlspecialchars(substr($solicitacao['descricao'] ?? 'Sem descrição', 0, 100)) ?>...</p>

                        <!-- Indicadores -->
                        <div class="mb-3">
                            <?php if (($solicitacao['total_imagens'] ?? 0) > 0): ?>
                                <span class="badge bg-info me-1">
                                    <i class="bi bi-camera me-1"></i>
                                    <?= $solicitacao['total_imagens'] ?> foto<?= $solicitacao['total_imagens'] > 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>

                            <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                <?= ucfirst($solicitacao['urgencia'] ?? 'media') ?>
                            </span>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars(($solicitacao['cidade'] ?? 'Cidade') . ', ' . ($solicitacao['estado'] ?? 'UF')) ?>
                            </small>
                        </div>

                        <?php if (!empty($solicitacao['orcamento_estimado'])): ?>
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    Orçamento: R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-1">
                            <a href="<?= url('cliente/solicitacoes/visualizar?id=' . $solicitacao['id']) ?>"
                                class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>Ver
                            </a>

                            <?php if (in_array($solicitacao['status_id'] ?? 0, [1, 2])): ?>
                                <a href="<?= url('cliente/solicitacoes/editar?id=' . $solicitacao['id']) ?>"
                                    class="btn btn-outline-secondary btn-sm flex-fill">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </a>
                            <?php endif; ?>

                            <button type="button"
                                class="btn btn-outline-danger btn-sm flex-fill"
                                onclick="confirmarExclusao(<?= $solicitacao['id'] ?>)">
                                <i class="bi bi-trash me-1"></i>Excluir
                            </button>
                        </div>

                        <?php if ($solicitacao['status_id'] == 1): ?>
                            <div class="mt-2">
                                <a href="<?= url('cliente/propostas/recebidas?solicitacao_id=' . $solicitacao['id']) ?>"
                                    class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-inbox me-1"></i>Ver Propostas Recebidas
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginação simples -->
    <?php if (count($solicitacoes) >= 12): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta solicitação?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as propostas relacionadas serão perdidas.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= url('cliente/solicitacoes/deletar') ?>" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="id" id="idExcluir">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function confirmarExclusao(id) {
    document.getElementById("idExcluir").value = id;
    new bootstrap.Modal(document.getElementById("modalExcluir")).show();
}

// Auto-submit dos filtros
document.querySelectorAll("#status, #urgencia").forEach(select => {
    select.addEventListener("change", function() {
        this.form.submit();
    });
});

// Busca com Enter
document.getElementById("busca").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        this.form.submit();
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>