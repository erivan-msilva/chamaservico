<?php
$title = 'Serviços Concluídos - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-check-circle text-success me-2"></i>
                Serviços Concluídos
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/chamaservico/cliente/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Serviços Concluídos</li>
                </ol>
            </nav>
        </div>
        <a href="/chamaservico/cliente/dashboard" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>

    <?php if (empty($servicosConcluidos)): ?>
        <!-- Estado Vazio -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            </div>
            <h4 class="text-muted">Nenhum serviço concluído ainda</h4>
            <p class="text-muted mb-4">
                Quando você tiver serviços finalizados, eles aparecerão aqui para avaliação.
            </p>
            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Criar Nova Solicitação
            </a>
        </div>
    <?php else: ?>
        <!-- Lista de Serviços -->
        <div class="row">
            <?php foreach ($servicosConcluidos as $servico): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-tools me-2"></i>
                                    <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                </h6>
                                <span class="badge bg-light text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Concluído
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                            <p class="card-text text-muted">
                                <?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>
                                <?= strlen($servico['descricao']) > 100 ? '...' : '' ?>
                            </p>

                            <!-- Informações do Prestador -->
                            <?php if (isset($servico['proposta_aceita'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Prestador:</small>
                                    <div class="fw-bold"><?= htmlspecialchars($servico['proposta_aceita']['prestador_nome']) ?></div>
                                    <div class="text-success fw-bold">
                                        R$ <?= number_format($servico['proposta_aceita']['valor'], 2, ',', '.') ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Data de Conclusão -->
                            <div class="mb-3">
                                <small class="text-muted">Concluído em:</small>
                                <div><?= date('d/m/Y H:i', strtotime($servico['data_solicitacao'])) ?></div>
                            </div>

                            <!-- Endereço -->
                            <div class="mb-3">
                                <small class="text-muted">Local:</small>
                                <div><?= htmlspecialchars($servico['bairro']) ?> - <?= htmlspecialchars($servico['cidade']) ?></div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <!-- Botão Avaliar -->
                                <a href="/chamaservico/cliente/servicos/avaliar?id=<?= $servico['id'] ?>" 
                                   class="btn btn-warning btn-sm">
                                    <i class="bi bi-star me-2"></i>
                                    Avaliar Serviço
                                </a>

                                <!-- Botões de Ação -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm" 
                                            onclick="confirmarConclusao(<?= $servico['id'] ?>)">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Confirmar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="solicitarRevisao(<?= $servico['id'] ?>)">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Revisão
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Confirmar Conclusão -->
<div class="modal fade" id="modalConfirmar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Conclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja confirmar a conclusão deste serviço?</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="/chamaservico/cliente/servicos/confirmar-conclusao">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="solicitacao_id" id="confirmarSolicitacaoId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Conclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Solicitar Revisão -->
<div class="modal fade" id="modalRevisao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Revisão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/servicos/solicitar-revisao">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="solicitacao_id" id="revisaoSolicitacaoId">
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo da Revisão *</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                  placeholder="Descreva o motivo da revisão..." required></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        O prestador será notificado sobre sua solicitação de revisão.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Solicitar Revisão</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarConclusao(solicitacaoId) {
    document.getElementById('confirmarSolicitacaoId').value = solicitacaoId;
    new bootstrap.Modal(document.getElementById('modalConfirmar')).show();
}

function solicitarRevisao(solicitacaoId) {
    document.getElementById('revisaoSolicitacaoId').value = solicitacaoId;
    new bootstrap.Modal(document.getElementById('modalRevisao')).show();
}
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>