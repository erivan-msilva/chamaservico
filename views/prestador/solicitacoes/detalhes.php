<?php
$title = 'Detalhes da Solicitação - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-eye me-2"></i>Detalhes da Solicitação</h2>
            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar à Busca
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($solicitacao['titulo']) ?></h4>
                    <small class="text-muted">
                        Solicitado em <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                    </small>
                </div>
                <span class="badge fs-6 bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                    <?= ucfirst($solicitacao['urgencia']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Descrição do Serviço</h6>
                        <p class="mb-4"><?= nl2br(htmlspecialchars($solicitacao['descricao'])) ?></p>
                        
                        <!-- Galeria de Imagens -->
                        <?php if (!empty($solicitacao['imagens'])): ?>
                            <h6>
                                <i class="bi bi-camera me-2"></i>Fotos do Local 
                                <span class="badge bg-primary"><?= count($solicitacao['imagens']) ?></span>
                            </h6>
                            <div class="row g-3 mb-4">
                                <?php foreach ($solicitacao['imagens'] as $index => $imagem): ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card h-100 shadow-sm">
                                            <div class="position-relative">
                                                <?php 
                                                // CORREÇÃO: garantir subpasta solicitacoes/
                                                $imgFile = ltrim($imagem['caminho_imagem'], '/');
                                                if (strpos($imgFile, 'solicitacoes/') !== 0) {
                                                    $imgFile = 'solicitacoes/' . $imgFile;
                                                }
                                                $imagemPath = "/chamaservico/uploads/" . $imgFile;
                                                ?>
                                                <img src="<?= $imagemPath ?>" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                                     onclick="openImageModal('<?= $imagemPath ?>', '<?= $index + 1 ?>')"
                                                     alt="Foto <?= $index + 1 ?> da solicitação"
                                                     loading="lazy">
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h6>Informações do Serviço</h6>
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
                        <h6>Endereço do Serviço</h6>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <address class="mb-0">
                                    <strong><?= htmlspecialchars($solicitacao['logradouro']) ?>, <?= htmlspecialchars($solicitacao['numero']) ?></strong><br>
                                    <?php if ($solicitacao['complemento']): ?>
                                        <?= htmlspecialchars($solicitacao['complemento']) ?><br>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($solicitacao['bairro']) ?><br>
                                    <?= htmlspecialchars($solicitacao['cidade']) ?> - <?= htmlspecialchars($solicitacao['estado']) ?><br>
                                    <small class="text-muted">CEP: <?= htmlspecialchars($solicitacao['cep']) ?></small>
                                </address>
                            </div>
                        </div>

                        <!-- Formulário de Proposta -->
                        <?php if (!$jaEnviouProposta): ?>
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-send me-2"></i>Enviar Proposta</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/chamaservico/prestador/propostas/enviar">
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
                                    <a href="/chamaservico/prestador/propostas" class="btn btn-sm btn-outline-primary">
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
