<?php
$title = 'Detalhes do Serviço - Prestador - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-eye text-primary me-2"></i>
                        Detalhes do Serviço
                    </h2>
                    <p class="text-muted">Informações completas do serviço em execução</p>
                </div>
                <div>
                    <a href="<?= url('prestador/servicos/andamento') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar à Lista
                    </a>
                </div>
            </div>

            <!-- Informações do Serviço -->
            <div class="row">
                <!-- Coluna Principal -->
                <div class="col-lg-8">
                    <!-- Card: Detalhes do Serviço -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-data me-2"></i>
                                    Informações do Serviço
                                </h5>
                                <span class="badge" style="background-color: <?= htmlspecialchars($servico['status_cor']) ?>">
                                    <?= htmlspecialchars($servico['status_nome']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h4 class="text-primary mb-3"><?= htmlspecialchars($servico['titulo']) ?></h4>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <strong>Tipo de Serviço:</strong>
                                    <p class="text-muted"><?= htmlspecialchars($servico['tipo_servico_nome']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Urgência:</strong>
                                    <span class="badge bg-<?= $servico['urgencia'] === 'alta' ? 'danger' : ($servico['urgencia'] === 'media' ? 'warning' : 'success') ?>">
                                        <?= ucfirst($servico['urgencia']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <strong>Descrição:</strong>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($servico['descricao'])) ?></p>
                            </div>

                            <?php if ($servico['data_atendimento']): ?>
                                <div class="mb-4">
                                    <strong>Data Preferencial:</strong>
                                    <p class="text-info">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Imagens do Serviço -->
                            <?php if (!empty($servico['imagens'])): ?>
                                <div class="mb-4">
                                    <strong>Imagens Anexadas:</strong>
                                    <div class="row mt-2">
                                        <?php foreach ($servico['imagens'] as $imagem): ?>
                                            <div class="col-md-3 mb-3">
                                                <img src="<?= url('uploads/solicitacoes/' . htmlspecialchars($imagem['caminho_imagem'])) ?>"
                                                    class="img-fluid rounded shadow-sm"
                                                    style="cursor: pointer;"
                                                    onclick="ampliarImagem(this.src)">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card: Endereço -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-geo-alt me-2"></i>
                                Local do Serviço
                            </h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                <strong><?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?></strong><br>
                                <?php if ($servico['complemento']): ?>
                                    <?= htmlspecialchars($servico['complemento']) ?><br>
                                <?php endif; ?>
                                <?= htmlspecialchars($servico['bairro']) ?><br>
                                <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?><br>
                                CEP: <?= htmlspecialchars($servico['cep']) ?>
                            </address>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Card: Informações Financeiras -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-cash-coin me-2"></i>
                                Valores e Prazos
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="text-success mb-3">R$ <?= number_format($servico['valor'], 2, ',', '.') ?></h3>

                            <div class="row text-center">
                                <div class="col-6">
                                    <strong class="text-primary"><?= $servico['prazo_execucao'] ?? 'A definir' ?></strong>
                                    <br><small class="text-muted">Dias para execução</small>
                                </div>
                                <div class="col-6">
                                    <strong class="text-warning">R$ <?= number_format($servico['orcamento_estimado'] ?? 0, 2, ',', '.') ?></strong>
                                    <br><small class="text-muted">Orçamento inicial</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Cliente -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                Informações do Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-dark"><?= htmlspecialchars($servico['cliente_nome']) ?></h6>

                            <?php if ($servico['cliente_email']): ?>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-envelope me-1"></i>
                                    <a href="mailto:<?= htmlspecialchars($servico['cliente_email']) ?>">
                                        <?= htmlspecialchars($servico['cliente_email']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if ($servico['cliente_telefone']): ?>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-telephone me-1"></i>
                                    <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>">
                                        <?= htmlspecialchars($servico['cliente_telefone']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card: Ações -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-lightning me-2"></i>
                                Ações Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if ($servico['status_id'] != 5): // Se não estiver concluído 
                                ?>
                                    <button type="button" class="btn btn-success"
                                        onclick="atualizarStatus(<?= $servico['id'] ?>, 'concluido')">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Marcar como Concluído
                                    </button>

                                    <button type="button" class="btn btn-warning"
                                        onclick="atualizarStatus(<?= $servico['id'] ?>, 'aguardando_materiais')">
                                        <i class="bi bi-pause-circle me-1"></i>
                                        Aguardando Materiais
                                    </button>

                                    <button type="button" class="btn btn-outline-danger"
                                        onclick="atualizarStatus(<?= $servico['id'] ?>, 'suspenso')">
                                        <i class="bi bi-stop-circle me-1"></i>
                                        Suspender Temporariamente
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-success text-center">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>Serviço Concluído!</strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($servico['cliente_telefone']): ?>
                                    <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>"
                                        class="btn btn-outline-primary">
                                        <i class="bi bi-telephone me-1"></i>
                                        Ligar para Cliente
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ampliar Imagem -->
<div class="modal fade" id="modalImagem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imagem do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagemAmpliada" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Modal para Atualizar Status (mesmo da página anterior) -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?= url('prestador/servicos/atualizar-status') ?>">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="proposta_id" id="modalPropostaId" value="<?= $servico['id'] ?>">
                <input type="hidden" name="novo_status" id="modalNovoStatus">
                <input type="hidden" name="redirect" value="prestador/servicos/detalhes?id=<?= $servico['id'] ?>">

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
    function ampliarImagem(src) {
        document.getElementById('imagemAmpliada').src = src;
        const modal = new bootstrap.Modal(document.getElementById('modalImagem'));
        modal.show();
    }

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

        document.getElementById('modalNovoStatus').value = novoStatus;
        document.getElementById('modalStatusMensagem').textContent = statusMessages[novoStatus];
        document.querySelector('#modalAtualizarStatus .modal-title').textContent = statusTitles[novoStatus];

        const btnConfirmar = document.getElementById('modalBtnConfirmar');
        btnConfirmar.className = `btn ${novoStatus === 'concluido' ? 'btn-success' : (novoStatus === 'suspenso' ? 'btn-warning' : 'btn-info')}`;
        btnConfirmar.innerHTML = `<i class="bi bi-check me-1"></i>Confirmar`;

        const modal = new bootstrap.Modal(document.getElementById('modalAtualizarStatus'));
        modal.show();
    }
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>