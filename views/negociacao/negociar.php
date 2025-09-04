<?php
// Define o título da página.
$title = 'Negociar Proposta - ChamaServiço';

// Verifica o tipo de usuário (cliente ou prestador) com base na sessão.
$isCliente = ($proposta['cliente_id'] ?? null) == Session::getUserId();
$isPrestador = ($proposta['prestador_id'] ?? null) == Session::getUserId();
$propostaNegociacoes = $proposta['negociacoes'] ?? [];
$ultimaNegociacao = !empty($propostaNegociacoes) ? end($propostaNegociacoes) : null;
$temNegociacaoAtiva = ($ultimaNegociacao && $ultimaNegociacao['tipo'] === 'contra_proposta');

// Inicia o buffer de saída para capturar o conteúdo HTML.
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-chat-square-dots me-2"></i>Negociar Proposta</h2>
            <a href="<?= $isCliente ? 'cliente/propostas/recebidas' : 'prestador/propostas' ?>"
                class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <!-- Informações da Proposta -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Título não disponível') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Prestador:</strong> <?= htmlspecialchars($proposta['prestador_nome'] ?? 'Nome do Prestador') ?></p>
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($proposta['cliente_nome'] ?? 'Nome do Cliente') ?></p>
                        <p><strong>Tipo de Serviço:</strong> <?= htmlspecialchars($proposta['tipo_servico_nome'] ?? 'Tipo de Serviço') ?></p>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-primary mb-1">Proposta Original</h4>
                                <h3 class="text-success">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></h3>
                                <small class="text-muted">
                                    Prazo: <?= $proposta['prazo_execucao'] ?? 0 ?> dia<?= ($proposta['prazo_execucao'] ?? 0) > 1 ? 's' : '' ?>
                                </small>
                                <p class="mt-2 small"><?= htmlspecialchars($proposta['descricao'] ?? 'Descrição não disponível') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Negociações -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Histórico de Negociação (<?= count($propostaNegociacoes) ?>)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($propostaNegociacoes)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-chat-square" style="font-size: 2rem;"></i>
                        <p class="mt-2">Nenhuma negociação iniciada ainda</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($propostaNegociacoes as $negociacao): ?>
                            <?php
                            // Mapeia o tipo de negociação para classes e ícones do Bootstrap.
                            $badgeClass = 'warning';
                            $iconClass = 'arrow-up-right';
                            $titleText = 'Contra-proposta do Cliente';
                            if ($negociacao['tipo'] === 'resposta_prestador') {
                                $badgeClass = 'success';
                                $iconClass = 'check-lg';
                                $titleText = 'Prestador Aceitou';
                            } elseif ($negociacao['tipo'] === 'recusa') {
                                $badgeClass = 'danger';
                                $iconClass = 'x-lg';
                                $titleText = 'Prestador Recusou';
                            }
                            ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker me-3">
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <i class="bi bi-<?= $iconClass ?>"></i>
                                        </span>
                                    </div>
                                    <div class="timeline-content flex-grow-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <i class="bi bi-<?= $iconClass ?> me-1"></i><?= $titleText ?>
                                                        </h6>
                                                        <?php if (!empty($negociacao['valor']) && !empty($negociacao['prazo'])): ?>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <strong class="text-success">
                                                                        R$ <?= number_format($negociacao['valor'], 2, ',', '.') ?>
                                                                    </strong>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <small class="text-muted">
                                                                        <?= $negociacao['prazo'] ?> dia<?= $negociacao['prazo'] > 1 ? 's' : '' ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($negociacao['observacoes'])): ?>
                                                            <p class="mt-2 mb-0 small"><?= htmlspecialchars($negociacao['observacoes']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($negociacao['data_negociacao'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulários de Negociação -->
        <?php
        // Verifica se a proposta está pendente.
        if (($proposta['status'] ?? null) === 'pendente'):
        ?>
            <div class="row">
                <!-- Cliente: Fazer Contra-proposta -->
                <?php if ($isCliente && !$temNegociacaoAtiva): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-warning shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-arrow-up-right me-2"></i>Fazer Contra-proposta
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="contra_proposta">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="valor" class="form-label">Valor Desejado *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" class="form-control" id="valor" name="valor"
                                                    step="0.01" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="prazo" class="form-label">Prazo Desejado *</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="prazo" name="prazo"
                                                    min="1" required>
                                                <span class="input-group-text">dia(s)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="observacoes" class="form-label">Observações</label>
                                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                                            placeholder="Explique o motivo da contra-proposta..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-send me-1"></i>Enviar Contra-proposta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Prestador: Responder Contra-proposta -->
                <?php if ($isPrestador && $temNegociacaoAtiva): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-success shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-check-lg me-2"></i>Aceitar Contra-proposta
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>Contra-proposta do cliente:</strong><br>
                                    R$ <?= number_format($ultimaNegociacao['valor'], 2, ',', '.') ?>
                                    em <?= $ultimaNegociacao['prazo'] ?> dia(s)
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="aceitar_contra_proposta">
                                    <input type="hidden" name="valor" value="<?= htmlspecialchars($ultimaNegociacao['valor']) ?>">
                                    <input type="hidden" name="prazo" value="<?= htmlspecialchars($ultimaNegociacao['prazo']) ?>">

                                    <div class="mb-3">
                                        <label for="observacoes_aceite" class="form-label">Comentário (opcional)</label>
                                        <textarea class="form-control" id="observacoes_aceite" name="observacoes" rows="2"
                                            placeholder="Comentário sobre a aceitação..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check-lg me-1"></i>Aceitar Contra-proposta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card border-danger shadow-sm">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-x-lg me-2"></i>Recusar Contra-proposta
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="recusar_contra_proposta">

                                    <div class="mb-3">
                                        <label for="observacoes_recusa" class="form-label">Motivo da Recusa *</label>
                                        <textarea class="form-control" id="observacoes_recusa" name="observacoes" rows="3"
                                            placeholder="Explique o motivo da recusa..." required></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-x-lg me-1"></i>Recusar Contra-proposta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-2"></i>
                Esta proposta não está mais disponível para negociação (Status: <?= ucfirst($proposta['status'] ?? 'Não Definido') ?>).
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$scripts = '
<style>
.timeline-item {
    position: relative;
}
.timeline-marker {
    min-width: 40px;
}
.timeline-content {
    position: relative;
}
</style>
';

// Captura o conteúdo do buffer e o armazena na variável $content.
$content = ob_get_clean();

// Inclui o layout principal da aplicação para renderizar a página.
include 'views/layouts/app.php';
?>