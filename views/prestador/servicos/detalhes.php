<?php
$title = 'Detalhes do Serviço - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-check me-2"></i>Detalhes do Serviço</h2>
            <a href="/chamaservico/prestador/servicos/andamento" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar aos Serviços
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($servico['titulo']) ?></h4>
                    <small class="text-muted">
                        Proposta aceita em <?= date('d/m/Y H:i', strtotime($servico['data_aceite'])) ?>
                    </small>
                </div>
                <span class="badge fs-6" style="background-color: <?= htmlspecialchars($servico['status_cor']) ?>;">
                    <?= htmlspecialchars($servico['status_nome']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Descrição do Serviço</h6>
                        <p class="mb-4"><?= nl2br(htmlspecialchars($servico['descricao'])) ?></p>

                        <!-- Galeria de Imagens -->
                        <?php if (!empty($servico['imagens'])): ?>
                            <h6>
                                <i class="bi bi-camera me-2"></i>Fotos do Serviço
                                <span class="badge bg-primary"><?= count($servico['imagens']) ?></span>
                            </h6>
                            <div class="row g-3 mb-4">
                                <?php foreach ($servico['imagens'] as $index => $imagem): ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card h-100 shadow-sm">
                                            <div class="position-relative">
                                                <?php 
                                                $imagemPath = "/chamaservico/uploads/solicitacoes/" . htmlspecialchars($imagem['caminho_imagem']);
                                                ?>
                                                <img src="<?= $imagemPath ?>" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                                     onclick="openImageModal('<?= $imagemPath ?>', '<?= $index + 1 ?>')"
                                                     alt="Foto <?= $index + 1 ?> do serviço"
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
                                <p><strong>Tipo:</strong> <?= htmlspecialchars($servico['tipo_servico_nome']) ?></p>
                                <p><strong>Valor Acordado:</strong> R$ <?= number_format($servico['valor'], 2, ',', '.') ?></p>
                                <p><strong>Prazo:</strong> <?= $servico['prazo_execucao'] ?> dia(s)</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Urgência:</strong>
                                    <span class="badge bg-<?= $servico['urgencia'] === 'alta' ? 'danger' : ($servico['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($servico['urgencia']) ?>
                                    </span>
                                </p>
                                <?php if ($servico['orcamento_estimado']): ?>
                                    <p><strong>Orçamento Original:</strong> R$ <?= number_format($servico['orcamento_estimado'], 2, ',', '.') ?></p>
                                <?php endif; ?>
                                <?php if ($servico['data_atendimento']): ?>
                                    <p><strong>Data Preferencial:</strong> <?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Informações do Cliente -->
                        <h6>Cliente</h6>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($servico['cliente_nome']) ?></h6>
                                <?php if ($servico['cliente_email']): ?>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i>
                                            <?= htmlspecialchars($servico['cliente_email']) ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                                <?php if ($servico['cliente_telefone']): ?>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="bi bi-telephone me-1"></i>
                                            <?= htmlspecialchars($servico['cliente_telefone']) ?>
                                        </small>
                                    </p>
                                    <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>" 
                                       class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-telephone me-1"></i>Ligar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Endereço do Serviço -->
                        <h6>Local do Serviço</h6>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <address class="mb-0">
                                    <strong><?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?></strong><br>
                                    <?php if ($servico['complemento']): ?>
                                        <?= htmlspecialchars($servico['complemento']) ?><br>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($servico['bairro']) ?><br>
                                    <?= htmlspecialchars($servico['cidade']) ?> - <?= htmlspecialchars($servico['estado']) ?><br>
                                    <small class="text-muted">CEP: <?= htmlspecialchars($servico['cep']) ?></small>
                                </address>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="d-grid gap-2">
                            <button type="button" 
                                    class="btn btn-primary"
                                    onclick="atualizarStatus(<?= $servico['id'] ?>, '<?= htmlspecialchars($servico['titulo']) ?>')">
                                <i class="bi bi-gear me-1"></i>Atualizar Status
                            </button>
                            
                            <?php if ($servico['cliente_telefone']): ?>
                                <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>" 
                                   class="btn btn-success">
                                    <i class="bi bi-telephone me-1"></i>Ligar para Cliente
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($servico['cliente_email']): ?>
                                <a href="mailto:<?= htmlspecialchars($servico['cliente_email']) ?>" 
                                   class="btn btn-info">
                                    <i class="bi bi-envelope me-1"></i>Enviar Email
                                </a>
                            <?php endif; ?>
                        </div>
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

<!-- Modal Atualizar Status -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/prestador/servicos/atualizar-status">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaId">
                    
                    <div class="mb-3">
                        <label class="form-label">Serviço: <span id="tituloServico" class="fw-bold"></span></label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Novo Status *</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="">Selecione o status</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                            <option value="aguardando_materiais">Aguardando Materiais</option>
                            <option value="suspenso">Suspenso Temporariamente</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="3"
                                  placeholder="Informe detalhes sobre a atualização (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar Status</button>
                </div>
            </form>
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

function atualizarStatus(propostaId, titulo) {
    document.getElementById("propostaId").value = propostaId;
    document.getElementById("tituloServico").textContent = titulo;
    document.getElementById("status").value = "";
    document.getElementById("observacoes").value = "";
    
    new bootstrap.Modal(document.getElementById("modalAtualizarStatus")).show();
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
