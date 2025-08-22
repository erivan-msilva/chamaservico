<?php
$title = 'Buscar Serviços - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-search me-2"></i>Buscar Serviços</h2>
        <p class="text-muted mb-0">Encontre oportunidades de trabalho na sua área</p>
    </div>
    <div>
        <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas
        </a>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Busca</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                <select class="form-select" name="tipo_servico" id="tipo_servico">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($tiposServico as $tipo): ?>
                        <option value="<?= $tipo['id'] ?>" <?= ($_GET['tipo_servico'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="urgencia" class="form-label">Urgência</label>
                <select class="form-select" name="urgencia" id="urgencia">
                    <option value="">Todas</option>
                    <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="cidade" class="form-label">Cidade</label>
                <select class="form-select" name="cidade" id="cidade">
                    <option value="">Todas as cidades</option>
                    <?php foreach ($cidades as $cidade): ?>
                        <option value="<?= htmlspecialchars($cidade) ?>" <?= ($_GET['cidade'] ?? '') == $cidade ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cidade) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="orcamento_min" class="form-label">Orçamento Mín.</label>
                <input type="number" class="form-control" name="orcamento_min" id="orcamento_min" 
                       placeholder="R$ 0" value="<?= htmlspecialchars($_GET['orcamento_min'] ?? '') ?>">
            </div>
            
            <div class="col-md-2">
                <label for="orcamento_max" class="form-label">Orçamento Máx.</label>
                <input type="number" class="form-control" name="orcamento_max" id="orcamento_max" 
                       placeholder="R$ 999999" value="<?= htmlspecialchars($_GET['orcamento_max'] ?? '') ?>">
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Solicitações -->
<?php if (empty($solicitacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhum serviço encontrado</h4>
        <p class="text-muted">Tente ajustar os filtros para encontrar mais oportunidades.</p>
        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Limpar Filtros
        </a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($solicitacoes as $solicitacao): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?>
                        </small>
                        <span class="badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                            <?= ucfirst($solicitacao['urgencia']) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                        <p class="card-text">
                            <small class="text-primary">
                                <i class="bi bi-tools me-1"></i>
                                <?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?>
                            </small>
                        </p>
                        <p class="card-text"><?= htmlspecialchars(substr($solicitacao['descricao'], 0, 100)) ?>...</p>
                        
                        <!-- Indicadores -->
                        <div class="mb-3">
                            <?php if ($solicitacao['total_imagens'] > 0): ?>
                                <span class="badge bg-info me-1">
                                    <i class="bi bi-camera me-1"></i>
                                    <?= $solicitacao['total_imagens'] ?> foto<?= $solicitacao['total_imagens'] > 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($solicitacao['total_propostas'] > 0): ?>
                                <span class="badge bg-secondary">
                                    <?= $solicitacao['total_propostas'] ?> proposta<?= $solicitacao['total_propostas'] > 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>
                            </small>
                        </div>
                        
                        <?php if (!empty($solicitacao['orcamento_estimado'])): ?>
                            <div class="mb-2">
                                <small class="text-success fw-bold">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    Orçamento: R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-grid">
                            <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $solicitacao['id'] ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-eye me-1"></i>Ver Detalhes e Enviar Proposta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$scripts = '
<script>
// Auto-submit dos filtros principais
document.querySelectorAll("#tipo_servico, #urgencia, #cidade").forEach(select => {
    select.addEventListener("change", function() {
        this.form.submit();
    });
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
