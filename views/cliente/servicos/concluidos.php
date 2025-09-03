<?php
$title = 'Serviços Concluídos - Cliente';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-check-circle text-success me-2"></i>
                Serviços Concluídos
            </h2>
            <p class="text-muted">Serviços finalizados e pendentes de avaliação</p>
        </div>
        <a href="/chamaservico/cliente/dashboard" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>

    <?php if (empty($servicos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-emoji-smile text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Nenhum serviço concluído</h4>
            <p class="text-muted">Quando você tiver serviços finalizados, eles aparecerão aqui para avaliação.</p>
            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Criar Nova Solicitação
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($servicos as $servico): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-tools me-2"></i>
                                <?= htmlspecialchars($servico['titulo']) ?>
                            </h6>
                            <span class="badge" style="background-color: <?= $servico['status_cor'] ?>;">
                                <?= htmlspecialchars($servico['status_nome']) ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <!-- Informações do Prestador -->
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-circle text-primary me-2"></i>
                                <div>
                                    <strong><?= htmlspecialchars($servico['prestador_nome']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Endereço -->
                            <div class="mb-3">
                                <i class="bi bi-geo-alt text-success me-2"></i>
                                <small>
                                    <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?> - 
                                    <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                </small>
                            </div>

                            <!-- Valor -->
                            <div class="mb-3">
                                <i class="bi bi-currency-dollar text-warning me-2"></i>
                                <strong>R$ <?= number_format($servico['valor_aceito'], 2, ',', '.') ?></strong>
                            </div>

                            <!-- Ações -->
                            <div class="d-grid gap-2">
                                <?php if ($servico['status_id'] == 5): // Concluído ?>
                                    <form method="POST" action="/chamaservico/cliente/servicos/confirmar-conclusao" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="solicitacao_id" value="<?= $servico['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Confirmar Conclusão
                                        </button>
                                    </form>
                                <?php elseif ($servico['status_id'] == 11 && $servico['ja_avaliado'] == 0): // Aguardando Avaliação ?>
                                    <a href="/chamaservico/cliente/servicos/avaliar?id=<?= $servico['id'] ?>" 
                                       class="btn btn-warning w-100">
                                        <i class="bi bi-star me-2"></i>
                                        Avaliar Serviço
                                    </a>
                                <?php elseif ($servico['ja_avaliado'] > 0): // Já avaliado ?>
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-check-circle me-2"></i>
                                        Já Avaliado
                                    </button>
                                <?php endif; ?>

                                <!-- Solicitar Revisão -->
                                <?php if ($servico['status_id'] != 13): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                            data-bs-toggle="modal" data-bs-target="#modalRevisao<?= $servico['id'] ?>">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        Solicitar Revisão
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Solicitar Revisão -->
                    <div class="modal fade" id="modalRevisao<?= $servico['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Solicitar Revisão</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="/chamaservico/cliente/servicos/solicitar-revisao">
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="solicitacao_id" value="<?= $servico['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label for="motivo_revisao<?= $servico['id'] ?>" class="form-label">
                                                Motivo da Revisão *
                                            </label>
                                            <textarea class="form-control" id="motivo_revisao<?= $servico['id'] ?>" 
                                                      name="motivo_revisao" rows="3" required
                                                      placeholder="Descreva o que precisa ser revisado..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-send me-2"></i>
                                            Solicitar Revisão
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>