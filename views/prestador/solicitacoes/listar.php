<?php
$title = 'Buscar Serviços - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-search me-2"></i>Buscar Serviços Disponíveis</h2>
    <a href="/chamaservico/prestador/dashboard" class="btn btn-outline-success">
        <i class="bi bi-speedometer2 me-1"></i>Dashboard
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                <select class="form-select" id="tipo_servico" name="tipo_servico">
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
                <select class="form-select" id="urgencia" name="urgencia">
                    <option value="">Todas</option>
                    <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="cidade" class="form-label">Cidade</label>
                <select class="form-select" id="cidade" name="cidade">
                    <option value="">Todas</option>
                    <?php foreach ($cidades as $cidade): ?>
                        <option value="<?= htmlspecialchars($cidade) ?>" <?= ($_GET['cidade'] ?? '') == $cidade ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cidade) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="orcamento_min" class="form-label">Orçamento Mín.</label>
                <input type="number" class="form-control" id="orcamento_min" name="orcamento_min" 
                       value="<?= $_GET['orcamento_min'] ?? '' ?>" placeholder="R$ 0,00">
            </div>
            <div class="col-md-2">
                <label for="orcamento_max" class="form-label">Orçamento Máx.</label>
                <input type="number" class="form-control" id="orcamento_max" name="orcamento_max" 
                       value="<?= $_GET['orcamento_max'] ?? '' ?>" placeholder="R$ 1000,00">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Solicitações -->
<?php if (empty($solicitacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhum serviço encontrado</h4>
        <p class="text-muted">Ajuste os filtros ou tente novamente mais tarde.</p>
        
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($solicitacoes as $solicitacao): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
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
                        
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($solicitacao['cidade']) ?>, <?= htmlspecialchars($solicitacao['estado']) ?>
                            </small>
                        </div>
                        
                        <?php if ($solicitacao['orcamento_estimado']): ?>
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    Orçamento: R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($solicitacao['total_imagens'] > 0): ?>
                            <div class="mb-2">
                                <span class="badge bg-info">
                                    <i class="bi bi-camera me-1"></i>
                                    <?= $solicitacao['total_imagens'] ?> foto<?= $solicitacao['total_imagens'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-info">
                                <i class="bi bi-people me-1"></i>
                                <?= $solicitacao['total_propostas'] ?> proposta<?= $solicitacao['total_propostas'] != 1 ? 's' : '' ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-grid">
                            <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $solicitacao['id'] ?>" 
                               class="btn btn-success">
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
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
