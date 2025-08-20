<?php
$title = 'Visualizar Solicitação - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($solicitacao['titulo']) ?></h4>
                    <small class="text-muted">
                        Solicitado em <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                    </small>
                </div>
                <span class="badge fs-6" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
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
                                <i class="bi bi-camera me-2"></i>Fotos Anexadas
                                <span class="badge bg-primary"><?= count($solicitacao['imagens']) ?></span>
                            </h6>
                            <div class="row g-3 mb-4">
                                <?php foreach ($solicitacao['imagens'] as $index => $imagem): ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card h-100 shadow-sm">
                                            <div class="position-relative">
                                                <?php
                                                $imagemPath = "../uploads/solicitacoes" . htmlspecialchars($imagem['caminho_imagem']);
                                                ?>
                                                <img src="<?= $imagemPath ?>"
                                                    class="card-img-top"
                                                    style="height: 220px; object-fit: cover; cursor: pointer;"
                                                    onclick="openImageModal('<?= $imagemPath ?>', '<?= $index + 1 ?>')"
                                                    alt="Foto <?= $index + 1 ?> da solicitação"
                                                    loading="lazy"
                                                    onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-center p-3\' style=\'height: 220px;\'><div><i class=\'bi bi-image-alt text-muted\' style=\'font-size: 2rem;\'></i><br><small class=\'text-muted\'>Imagem não encontrada</small></div></div>'">
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($imagem['data_upload'])) ?>
                                                    </small>
                                                    <small class="text-primary">
                                                        Foto <?= $index + 1 ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhuma foto foi anexada a esta solicitação.
                            </div>
                        <?php endif; ?>

                        <h6>Informações do Serviço</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> <?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></p>
                                <p><strong>Urgência:</strong>
                                    <span class="badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($solicitacao['urgencia']) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($solicitacao['orcamento_estimado']): ?>
                                    <p><strong>Orçamento:</strong> R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?></p>
                                <?php endif; ?>
                                <?php if ($solicitacao['data_atendimento']): ?>
                                    <p><strong>Data Preferencial:</strong> <?= date('d/m/Y H:i', strtotime($solicitacao['data_atendimento'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6>Endereço do Serviço</h6>
                        <div class="card bg-light">
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
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="/chamaservico/cliente/solicitacoes/editar?id=<?= $solicitacao['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Voltar
                    </a>
                    <button type="button" class="btn btn-outline-danger ms-auto"
                        onclick="confirmarExclusao(<?= $solicitacao['id'] ?>)">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar imagens em tamanho maior -->
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
                <img id="modalImage" src="" class="img-fluid" alt="Imagem ampliada" style="max-height: 80vh;"
                    onerror="this.alt='Erro ao carregar imagem'; this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display: none;" class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Não foi possível carregar a imagem.
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">Clique na imagem para fechar</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta solicitação?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as fotos anexadas serão removidas.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="/chamaservico/cliente/solicitacoes/deletar" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="id" value="<?= $solicitacao['id'] ?>">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
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

function confirmarExclusao(id) {
    new bootstrap.Modal(document.getElementById("modalExcluir")).show();
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
</div>