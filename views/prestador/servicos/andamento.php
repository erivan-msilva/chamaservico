<?php
$title = 'Serviços em Andamento - Prestador - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-tools text-primary me-2"></i>
                        Serviços em Andamento
                    </h2>
                    <p class="text-muted">Gerencie os serviços que você está executando</p>
                </div>
                <div>
                    <a href="<?= url('prestador/dashboard') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar ao Dashboard
                    </a>
                </div>

                <?php if (empty($servicosAndamento)): ?>
                    <div class="text-center py-5">
                        <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>
                            Buscar Novas Solicitações
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($servicosAndamento as $servico): ?>
                            <a href="<?= url('prestador/servicos/detalhes?id=' . $servico['id']) ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>
                                Ver Detalhes
                            </a>
                            <a href="https://www.google.com/maps/search/<?= urlencode($servico['logradouro'] . ', ' . $servico['numero'] . ', ' . $servico['bairro'] . ', ' . $servico['cidade'] . ', ' . $servico['estado']) ?>" 
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-map"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- DEBUG: Mostrar dados carregados -->
            <?php if (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento'): ?>
                <div class="alert alert-info">
                    <strong>DEBUG:</strong>
                    Prestador ID: <?= Session::getUserId() ?> |
                    Serviços encontrados: <?= count($servicos ?? []) ?> |
                    BASE_URL: <?= BASE_URL ?>
                </div>
            <?php endif; ?>

            <!-- Lista de serviços -->
            <?php if (empty($servicos)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Nenhum serviço em andamento</h4>
                        <p class="text-muted">Você não possui serviços em execução no momento.</p>
                        <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Buscar Novas Oportunidades
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($servicos as $servico): ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <!-- Header do Card -->
                                <div class="card-header bg-primary text-white border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-<?= $servico['urgencia'] === 'alta' ? 'exclamation-triangle' : ($servico['urgencia'] === 'media' ? 'clock' : 'calendar') ?> me-1"></i>
                                            <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                        </h6>
                                        <span class="badge" style="background-color: <?= htmlspecialchars($servico['status_cor']) ?>">
                                            <?= htmlspecialchars($servico['status_nome']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Corpo do Card -->
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <?= htmlspecialchars($servico['titulo']) ?>
                                    </h5>
                                    
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>
                                        <?php if (strlen($servico['descricao']) > 100): ?>...<?php endif; ?>
                                    </p>

                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <strong class="text-success">R$ <?= number_format($servico['valor'], 2, ',', '.') ?></strong>
                                            <br><small class="text-muted">Valor acordado</small>
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-info"><?= $servico['prazo_execucao'] ?? 'A definir' ?> dias</strong>
                                            <br><small class="text-muted">Prazo</small>
                                        </div>
                                    </div>

                                    <!-- Informações do Cliente -->
                                    <div class="border-top pt-3">
                                        <h6 class="text-dark">
                                            <i class="bi bi-person me-1"></i>
                                            <?= htmlspecialchars($servico['cliente_nome']) ?>
                                        </h6>
                                        
                                        <p class="small text-muted mb-2">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?>
                                            <br><?= htmlspecialchars($servico['bairro']) ?> - <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                        </p>

                                        <?php if ($servico['data_atendimento']): ?>
                                            <p class="small text-info mb-2">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <strong>Data preferencial:</strong> 
                                                <?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($servico['cliente_telefone']): ?>
                                            <p class="small text-success mb-2">
                                                <i class="bi bi-telephone me-1"></i>
                                                <?= htmlspecialchars($servico['cliente_telefone']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Footer do Card -->
                                <div class="card-footer bg-transparent border-0">
                                    <div class="d-grid gap-2">
                                        <a href="<?= url('prestador/servicos/detalhes?id=' . $servico['id']) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>
                                            Ver Detalhes
                                        </a>
                                        
                                        <?php if ($servico['status_id'] != 5): // Se não estiver concluído ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-success btn-sm" 
                                                        onclick="atualizarStatus(<?= $servico['id'] ?>, 'concluido')">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Concluir
                                                </button>
                                                <button type="button" class="btn btn-outline-warning btn-sm" 
                                                        onclick="atualizarStatus(<?= $servico['id'] ?>, 'aguardando_materiais')">
                                                    <i class="bi bi-pause-circle me-1"></i>
                                                    Pausar
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para Atualizar Status -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?= url('prestador/servicos/atualizar-status') ?>">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="proposta_id" id="modalPropostaId">
                <input type="hidden" name="novo_status" id="modalNovoStatus">
                <input type="hidden" name="redirect" value="prestador/servicos/andamento">

                <div class="mb-3">
                    <label for="modalObservacoes" class="form-label">Observações (opcional)</label>
                    <textarea class="form-control" id="modalObservacoes" name="observacoes" rows="3" 
                              placeholder="Adicione observações sobre a atualização do status..."></textarea>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="modalStatusMensagem"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="modalBtnConfirmar">
                    Confirmar Atualização
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function atualizarStatus(propostaId, novoStatus) {
    const statusMessages = {
        'concluido': 'Marcar este serviço como concluído? O cliente será notificado.',
        'aguardando_materiais': 'Marcar como "Aguardando Materiais"? O cliente será informado.',
        'suspenso': 'Suspender temporariamente este serviço?'
    };
    
    const statusTitles = {
        'concluido': 'Concluir Serviço',
        'aguardando_materiais': 'Aguardando Materiais',
        'suspenso': 'Suspender Serviço'
    };
    
    document.getElementById('modalPropostaId').value = propostaId;
    document.getElementById('modalNovoStatus').value = novoStatus;
    document.getElementById('modalStatusMensagem').textContent = statusMessages[novoStatus];
    document.querySelector('#modalAtualizarStatus .modal-title').textContent = statusTitles[novoStatus];
    
    const btnConfirmar = document.getElementById('modalBtnConfirmar');
    btnConfirmar.className = `btn ${novoStatus === 'concluido' ? 'btn-success' : (novoStatus === 'suspenso' ? 'btn-warning' : 'btn-info')}`;
    btnConfirmar.innerHTML = `<i class="bi bi-check me-1"></i>Confirmar`;
    
    const modal = new bootstrap.Modal(document.getElementById('modalAtualizarStatus'));
    modal.show();
}

// Auto-focus no campo de observações quando modal abrir
document.getElementById('modalAtualizarStatus').addEventListener('shown.bs.modal', function () {
    document.getElementById('modalObservacoes').focus();
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>