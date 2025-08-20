<?php
$title = 'Detalhes da Proposta - ChamaServiço';
ob_start();
?>

<!-- ...existing code da proposta... -->

<?php if (!empty($proposta['negociacoes'])): ?>
    <?php
    // Encontrar a última negociação do tipo 'contra_proposta'
    $ultimaContraProposta = null;
    foreach (array_reverse($proposta['negociacoes']) as $negociacao) {
        if ($negociacao['tipo'] === 'contra_proposta') {
            $ultimaContraProposta = $negociacao;
            break;
        }
    }
    ?>
    <?php if ($ultimaContraProposta): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-arrow-repeat me-2 fs-4"></i>
            <div>
                <strong>Nova contra proposta do cliente!</strong><br>
                Valor sugerido: <span class="text-success fw-bold">R$ <?= number_format($ultimaContraProposta['valor'], 2, ',', '.') ?></span><br>
                Prazo sugerido: <span class="fw-bold"><?= $ultimaContraProposta['prazo'] ?> dia(s)</span><br>
                <?php if (!empty($ultimaContraProposta['observacoes'])): ?>
                    <span class="text-muted">Obs: <?= htmlspecialchars($ultimaContraProposta['observacoes']) ?></span>
                <?php endif; ?>
                <small class="d-block text-muted mt-1">
                    Recebida em <?= date('d/m/Y H:i', strtotime($ultimaContraProposta['data_negociacao'])) ?>
                </small>
            </div>
        </div>
        <!-- Ações do prestador -->
        <div class="mb-4">
            <form method="POST" action="/chamaservico/negociacao/negociar">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">
                <input type="hidden" name="tipo" value="resposta_prestador">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Valor Aceito</label>
                        <input type="number" class="form-control" name="valor" step="0.01" min="1"
                               value="<?= htmlspecialchars($ultimaContraProposta['valor']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prazo Aceito</label>
                        <input type="number" class="form-control" name="prazo" min="1"
                               value="<?= htmlspecialchars($ultimaContraProposta['prazo']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Observações</label>
                        <input type="text" class="form-control" name="observacoes"
                               placeholder="Mensagem para o cliente">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg me-1"></i>Aceitar
                        </button>
                        <button type="submit" name="tipo" value="recusa" class="btn btn-danger w-100">
                            <i class="bi bi-x-lg me-1"></i>Recusar
                        </button>
                    </div>
                </div>
            </form>
            <div class="mt-2">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#novaNegociacaoCollapse">
                    <i class="bi bi-arrow-repeat me-1"></i>Fazer nova negociação
                </button>
                <div class="collapse mt-2" id="novaNegociacaoCollapse">
                    <form method="POST" action="/chamaservico/negociacao/negociar">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                        <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">
                        <input type="hidden" name="tipo" value="resposta_prestador">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Novo Valor</label>
                                <input type="number" class="form-control" name="valor" step="0.01" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Novo Prazo</label>
                                <input type="number" class="form-control" name="prazo" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Observações</label>
                                <input type="text" class="form-control" name="observacoes"
                                       placeholder="Mensagem para o cliente">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-arrow-repeat me-1"></i>Negociar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Histórico de negociações -->
<?php if (!empty($proposta['negociacoes'])): ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Histórico de Negociações
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <?php foreach ($proposta['negociacoes'] as $negociacao): ?>
                    <li class="list-group-item">
                        <strong>
                            <?php
                            if ($negociacao['tipo'] === 'contra_proposta') echo 'Contra Proposta do Cliente';
                            elseif ($negociacao['tipo'] === 'resposta_prestador') echo 'Resposta do Prestador';
                            elseif ($negociacao['tipo'] === 'recusa') echo 'Recusa';
                            else echo ucfirst($negociacao['tipo']);
                            ?>
                        </strong>
                        <span class="ms-2 text-success">R$ <?= number_format($negociacao['valor'], 2, ',', '.') ?></span>
                        <span class="ms-2">Prazo: <?= $negociacao['prazo'] ?> dia(s)</span>
                        <?php if (!empty($negociacao['observacoes'])): ?>
                            <span class="ms-2 text-muted">Obs: <?= htmlspecialchars($negociacao['observacoes']) ?></span>
                        <?php endif; ?>
                        <small class="d-block text-muted mt-1">
                            <?= date('d/m/Y H:i', strtotime($negociacao['data_negociacao'])) ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<!-- ...existing code da proposta... -->

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
