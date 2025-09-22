<?php
$title = 'Detalhes da Solicitação - ChamaServiço';
ob_start();

// Definir $jaEnviouProposta ANTES do HTML
require_once 'models/Proposta.php';
$propostaModel = new Proposta();
$jaEnviouProposta = false;
if (isset($solicitacao['id']) && isset($_SESSION['user_id'])) {
    $prestadorId = $_SESSION['user_id'];
    $jaEnviouProposta = $propostaModel->verificarPropostaExistente($solicitacao['id'], $prestadorId);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Card Principal da Solicitação -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text me-2"></i>
                            <?= htmlspecialchars($solicitacao['titulo']) ?>
                        </h5>
                        <div class="d-flex gap-2">
                            <?php
                            $urgenciaColors = ['baixa' => 'success', 'media' => 'warning', 'alta' => 'danger'];
                            $urgenciaIcons = ['baixa' => '🟢', 'media' => '🟡', 'alta' => '🔴'];
                            ?>
                            <span class="badge bg-<?= $urgenciaColors[$solicitacao['urgencia']] ?>">
                                <?= $urgenciaIcons[$solicitacao['urgencia']] ?>
                                <?= ucfirst($solicitacao['urgencia']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Descrição -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-chat-text me-1"></i>Descrição do Serviço
                        </h6>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($solicitacao['descricao'])) ?></p>
                    </div>

                    <!-- Informações do Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary">
                                <i class="bi bi-person me-1"></i>Cliente
                            </h6>
                            <p class="mb-1"><?= htmlspecialchars($solicitacao['cliente_nome']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary">
                                <i class="bi bi-calendar3 me-1"></i>Solicitado em
                            </h6>
                            <p class="mb-1"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></p>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-geo-alt me-1"></i>Local do Serviço
                        </h6>
                        <p class="mb-0">
                            <?= htmlspecialchars($solicitacao['logradouro']) ?>,
                            <?= htmlspecialchars($solicitacao['numero']) ?>
                            <?php if ($solicitacao['complemento']): ?>
                                , <?= htmlspecialchars($solicitacao['complemento']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="mb-0">
                            <?= htmlspecialchars($solicitacao['bairro']) ?> -
                            <?= htmlspecialchars($solicitacao['cidade']) ?>,
                            <?= htmlspecialchars($solicitacao['estado']) ?> -
                            <small class="text-muted">CEP: <?= htmlspecialchars($solicitacao['cep']) ?></small>
                        </p>
                    </div>

                    <!-- Imagens da Solicitação -->
                    <?php if (!empty($solicitacao['imagens'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary">
                                <i class="bi bi-images me-1"></i>Imagens da Solicitação
                            </h6>
                            <div class="row g-2">
                                <?php foreach ($solicitacao['imagens'] as $imagem): ?>
                                    <div class="col-md-3">
                                        <div class="position-relative">
                                         
                                            <img src="/uploads/solicitacoes/<?= htmlspecialchars($imagem['caminho_imagem']) ?>"
                                                class="img-fluid rounded" alt="Imagem da solicitacao"
                                                style="height: 120px; object-fit: cover; width: 100%;">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Informações Adicionais -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-info-circle me-1"></i>Informações Adicionais
                        </h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> <?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></p>
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($solicitacao['cliente_nome'] ?? 'Nome não disponível') ?></p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($solicitacao['orcamento_estimado']): ?>
                                    <p><strong>Orçamento Estimado:</strong> R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?></p>
                                <?php endif; ?>
                                <?php if ($solicitacao['data_atendimento']): ?>
                                    <p><strong>Data Preferencial:</strong> <?= date('d/m/Y H:i', strtotime($solicitacao['data_atendimento'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        // Adicionar esta lógica no início do arquivo após receber $outrasPropostas
                        $outrasPropostasMsg = '';
                        if ($outrasPropostas == 0) {
                            $outrasPropostasMsg = 'Nenhuma proposta enviada ainda para esta solicitação. Seja o primeiro!';
                        } elseif ($outrasPropostas == 1) {
                            $outrasPropostasMsg = '1 prestador já enviou uma proposta para esta solicitação.';
                        } else {
                            $outrasPropostasMsg = "$outrasPropostas prestadores já enviaram propostas para esta solicitação.";
                        }
                        ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Outras propostas:</strong> <?= $outrasPropostasMsg ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Formulário de Proposta -->
                        <?php if (!$jaEnviouProposta): ?>
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-send me-2"></i>Enviar Proposta</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="<?= url('prestador/solicitacoes/proposta') ?>">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">

                                        <div class="mb-3">
                                            <label for="valor" class="form-label">Valor da Proposta *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" class="form-control" id="valor" name="valor"
                                                    step="0.01" min="1" required placeholder="0,00">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="prazo_execucao" class="form-label">Prazo de Execução *</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="prazo_execucao"
                                                    name="prazo_execucao" min="1" required placeholder="5">
                                                <span class="input-group-text">dia(s)</span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descricao" class="form-label">Descrição da Proposta *</label>
                                            <textarea class="form-control" id="descricao" name="descricao"
                                                rows="4" required placeholder="Descreva como você fará o serviço, materiais inclusos, garantia, etc."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-send me-1"></i>Enviar Proposta
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Proposta já enviada!</strong><br>
                                Você já enviou uma proposta para esta solicitação.
                                <div class="mt-2">
                                    <a href="<?= url('prestador/propostas') ?>" class="btn btn-sm btn-outline-primary">
                                        Ver Minhas Propostas
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar imagens -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>Visualizar Imagem <span id="imageNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-1">
                <img id="modalImage" src="" class="img-fluid" alt="Imagem ampliada" style="max-height: 80vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function openImageModal(imageSrc, imageNumber) {
    document.getElementById("modalImage").src = imageSrc;
    document.getElementById("imageNumber").textContent = "- Foto " + imageNumber;
    new bootstrap.Modal(document.getElementById("imageModal")).show();
}

// Fechar modal ao clicar na imagem
document.getElementById("modalImage").addEventListener("click", function() {
    bootstrap.Modal.getInstance(document.getElementById("imageModal")).hide();
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>