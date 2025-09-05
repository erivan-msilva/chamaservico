<?php
$title = 'Detalhes da Proposta - Prestador';
ob_start();
?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= url('prestador/dashboard') ?>" class="text-decoration-none">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= url('prestador/propostas') ?>" class="text-decoration-none">Propostas</a>
            </li>
            <li class="breadcrumb-item active">Detalhes</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 mb-1">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                Detalhes da Proposta
            </h2>
            <p class="text-muted mb-0">Acompanhe o status e gerencie sua proposta</p>
        </div>
        <div class="col-md-4 text-md-end">
            <?php
            $statusConfig = [
                'pendente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Aguardando resposta'],
                'aceita' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Proposta aceita'],
                'recusada' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Proposta recusada'],
                'cancelada' => ['class' => 'secondary', 'icon' => 'x-octagon', 'text' => 'Cancelada por você']
            ];
            $status = $statusConfig[$proposta['status']] ?? $statusConfig['pendente'];
            ?>
            <span class="badge bg-<?= $status['class'] ?> fs-6 px-3 py-2">
                <i class="bi bi-<?= $status['icon'] ?> me-1"></i>
                <?= $status['text'] ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Informações do Serviço -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informações do Serviço
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3"><?= htmlspecialchars($proposta['solicitacao_titulo']) ?></h6>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tipo de Serviço</small>
                            <strong><?= htmlspecialchars($proposta['tipo_servico_nome']) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Cliente</small>
                            <strong><?= htmlspecialchars($proposta['cliente_nome']) ?></strong>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <small class="text-muted d-block mb-2">Descrição do Serviço</small>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($proposta['solicitacao_descricao'])) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Endereço</small>
                        <address class="mb-0">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            <?= htmlspecialchars($proposta['logradouro']) ?>, <?= htmlspecialchars($proposta['numero']) ?>
                            <?= $proposta['complemento'] ? ' - ' . htmlspecialchars($proposta['complemento']) : '' ?><br>
                            <?= htmlspecialchars($proposta['bairro']) ?> - <?= htmlspecialchars($proposta['cidade']) ?>/<?= htmlspecialchars($proposta['estado']) ?>
                            <br>CEP: <?= htmlspecialchars($proposta['cep']) ?>
                        </address>
                    </div>
                </div>
            </div>

            <!-- Sua Proposta -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        Sua Proposta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Valor Proposto</small>
                            <strong class="text-success fs-4">
                                R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                            </strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Prazo de Execução</small>
                            <strong class="fs-5">
                                <?= $proposta['prazo_execucao'] ? $proposta['prazo_execucao'] . ' dia(s)' : 'A combinar' ?>
                            </strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Data da Proposta</small>
                            <strong><?= date('d/m/Y H:i', strtotime($proposta['data_proposta'])) ?></strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Descrição da Proposta</small>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($proposta['descricao'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Negociações (se existirem) -->
            <?php if (!empty($proposta['negociacoes'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-dots me-2"></i>
                            Histórico de Negociação
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($proposta['negociacoes'] as $negociacao): ?>
                            <div class="border-start border-3 border-info ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong class="text-capitalize"><?= str_replace('_', ' ', $negociacao['tipo']) ?></strong>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($negociacao['data_negociacao'])) ?></small>
                                </div>
                                
                                <?php if ($negociacao['valor']): ?>
                                    <p class="mb-1">
                                        <strong>Valor:</strong> R$ <?= number_format($negociacao['valor'], 2, ',', '.') ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($negociacao['prazo']): ?>
                                    <p class="mb-1">
                                        <strong>Prazo:</strong> <?= $negociacao['prazo'] ?> dia(s)
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($negociacao['observacoes']): ?>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($negociacao['observacoes']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ações -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Ações
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($proposta['status'] === 'aceita'): ?>
                            <a href="<?= url('prestador/servicos/detalhes?id=' . $proposta['id']) ?>" 
                               class="btn btn-success">
                                <i class="bi bi-tools me-2"></i>
                                Gerenciar Serviço
                            </a>
                            
                            <div class="alert alert-success small mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>Parabéns!</strong> Sua proposta foi aceita. Você pode agora gerenciar este serviço.
                            </div>
                        <?php elseif ($proposta['status'] === 'pendente'): ?>
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="cancelarProposta(<?= $proposta['id'] ?>)">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar Proposta
                            </button>
                            
                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-clock me-1"></i>
                                Aguardando resposta do cliente. Você será notificado quando houver uma atualização.
                            </div>
                        <?php elseif ($proposta['status'] === 'recusada'): ?>
                            <a href="prestador/solicitacoes" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>
                                Buscar Novos Serviços
                            </a>
                            
                            <div class="alert alert-danger small mb-0">
                                <i class="bi bi-x-circle me-1"></i>
                                Esta proposta foi recusada pelo cliente. Você pode buscar outros serviços disponíveis.
                            </div>
                        <?php else: ?>
                            <a href="prestador/propostas" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar às Propostas
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informações do Cliente -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        Contato do Cliente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="bi bi-person text-primary fs-3"></i>
                        </div>
                    </div>
                    
                    <h6 class="text-center mb-3"><?= htmlspecialchars($proposta['cliente_nome']) ?></h6>
                    
                    <?php if ($proposta['status'] === 'aceita'): ?>
                        <div class="d-grid gap-2">
                            <?php if (!empty($proposta['cliente_telefone'])): ?>
                                <a href="tel:<?= htmlspecialchars($proposta['cliente_telefone']) ?>" 
                                   class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-telephone me-1"></i>
                                    <?= htmlspecialchars($proposta['cliente_telefone']) ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($proposta['cliente_email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($proposta['cliente_email']) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-envelope me-1"></i>
                                    E-mail
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            As informações de contato ficarão disponíveis após a proposta ser aceita.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dicas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Dicas
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-1"></i>
                            Mantenha comunicação clara com o cliente
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-1"></i>
                            Cumpra prazos acordados
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-1"></i>
                            Documente o progresso do trabalho
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check text-success me-1"></i>
                            Solicite avaliação após conclusão
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Cancelar -->
<div class="modal fade" id="modalCancelarProposta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Cancelar Proposta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja cancelar esta proposta?</p>
                <div class="alert alert-warning">
                    <strong>Atenção:</strong> Esta ação não pode ser desfeita. Você poderá enviar uma nova proposta posteriormente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não, manter</button>
                <form method="POST" action="<?= url('prestador/propostas/cancelar') ?>" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="proposta_id_cancelar" value="">
                    <button type="submit" class="btn btn-danger">Sim, cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cancelarProposta(propostaId) {
    document.getElementById('proposta_id_cancelar').value = propostaId;
    const modal = new bootstrap.Modal(document.getElementById('modalCancelarProposta'));
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
