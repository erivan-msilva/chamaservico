<?php
$isEdit = isset($solicitacao);
$title = ($isEdit ? 'Editar' : 'Nova') . ' Solicitação - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">
                    <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2"></i>
                    <?= $isEdit ? 'Editar Solicitação' : 'Nova Solicitação' ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="formSolicitacao">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_servico_id" class="form-label">
                                <i class="bi bi-tools me-1"></i>Tipo de Serviço <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_servico_id" name="tipo_servico_id" required>
                                <option value="">Selecione o tipo de serviço</option>
                                <?php if (!empty($tiposServico)): ?>
                                    <?php foreach ($tiposServico as $tipo): ?>
                                        <option value="<?= $tipo['id'] ?>" 
                                                <?= ($isEdit && $solicitacao['tipo_servico_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Nenhum tipo de serviço disponível</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text text-muted">
                                Escolha o serviço que melhor representa sua necessidade.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="endereco_id" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i>Endereço <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="endereco_id" name="endereco_id" required <?= $isEdit ? 'disabled' : '' ?>>
                                <option value="">Selecione o endereço</option>
                                <?php if (!empty($enderecos)): ?>
                                    <?php foreach ($enderecos as $endereco): ?>
                                        <option value="<?= $endereco['id'] ?>"
                                                <?= ($isEdit && $solicitacao['endereco_id'] == $endereco['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(($endereco['logradouro'] ?? '') . ', ' . ($endereco['numero'] ?? '') . ' - ' . ($endereco['bairro'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Nenhum endereço cadastrado</option>
                                <?php endif; ?>
                            </select>
                            <?php if ($isEdit): ?>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Endereço não pode ser alterado durante a edição.
                                </div>
                            <?php elseif (empty($enderecos)): ?>
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Cadastre um endereço antes de criar uma solicitação.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            <i class="bi bi-card-heading me-1"></i>Título <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?= $isEdit ? htmlspecialchars($solicitacao['titulo'] ?? '') : '' ?>"
                               required maxlength="100" placeholder="Ex: Instalar chuveiro, consertar torneira...">
                        <div class="form-text text-muted">
                            Seja breve e objetivo. Exemplo: "Instalar chuveiro elétrico".
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">
                            <i class="bi bi-chat-left-text me-1"></i>Descrição <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                  required placeholder="Descreva detalhadamente o que precisa ser feito"><?= $isEdit ? htmlspecialchars($solicitacao['descricao'] ?? '') : '' ?></textarea>
                        <div class="form-text text-muted">
                            Informe detalhes importantes: local, problema, expectativas, horários, etc.
                        </div>
                    </div>

                    <!-- Fotos Existentes (apenas na edição) -->
                    <?php if ($isEdit && !empty($solicitacao['imagens'])): ?>
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-camera me-1"></i>Fotos Atuais 
                                <span class="badge bg-primary"><?= count($solicitacao['imagens']) ?></span>
                            </label>
                            <div class="row g-3">
                                <?php foreach ($solicitacao['imagens'] as $index => $imagem): ?>
                                    <div class="col-md-3 col-sm-6" id="imagem-<?= $imagem['id'] ?>">
                                        <div class="card h-100">
                                            <div class="position-relative">
                                                <?php 
                                                $imagemPath = "/chamaservico/uploads/solicitacoes/" . htmlspecialchars($imagem['caminho_imagem']);
                                                ?>
                                                <img src="<?= $imagemPath ?>" 
                                                     class="card-img-top" 
                                                     style="height: 180px; object-fit: cover; cursor: pointer;"
                                                     onclick="openImageModal('<?= $imagemPath ?>', '<?= $index + 1 ?>')"
                                                     alt="Foto <?= $index + 1 ?>"
                                                     onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-center p-3\' style=\'height: 180px;\'><div><i class=\'bi bi-image-alt text-muted\' style=\'font-size: 2rem;\'></i><br><small class=\'text-muted\'>Erro ao carregar</small></div></div>'">
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
                                                        <?= date('d/m/Y', strtotime($imagem['data_upload'])) ?>
                                                    </small>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="confirmarRemocaoImagem(<?= $imagem['id'] ?>)"
                                                            title="Remover imagem">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Upload de Novas Imagens -->
                    <div class="mb-4">
                        <label for="imagens" class="form-label">
                            <i class="bi bi-camera me-1"></i>
                            <?= $isEdit ? 'Adicionar Mais Fotos' : 'Fotos do Local/Problema' ?>
                        </label>
                        <input type="file" class="form-control" id="imagens" name="imagens[]" 
                               multiple accept="image/*" onchange="previewImages(this)">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            <?= $isEdit ? 'Adicione mais fotos para complementar a solicitação.' : 'Adicione fotos para ajudar os prestadores a entenderem melhor o serviço.' ?>
                            Máximo 5MB por imagem. Formatos: JPG, PNG, GIF.
                        </div>
                        <div id="imagePreview" class="mt-3 row g-2" style="display: none;"></div>
                        <?php if ($isEdit): ?>
                            <div class="mt-3" id="addImagesSection" style="display: none;">
                                <button type="button" class="btn btn-success" onclick="adicionarImagens()">
                                    <i class="bi bi-plus-circle me-1"></i>Adicionar Apenas Estas Fotos
                                </button>
                                <small class="text-muted ms-2">
                                    As fotos serão adicionadas sem alterar outros dados da solicitação
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="orcamento_estimado" class="form-label">
                                <i class="bi bi-currency-dollar me-1"></i>Orçamento Estimado
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="orcamento_estimado" 
                                       name="orcamento_estimado" step="0.01" min="0"
                                       value="<?= $isEdit ? ($solicitacao['orcamento_estimado'] ?? '') : '' ?>"
                                       placeholder="0,00">
                            </div>
                            <div class="form-text text-muted">
                                Informe um valor se desejar receber propostas dentro de um orçamento.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="data_atendimento" class="form-label">
                                <i class="bi bi-calendar-event me-1"></i>Data Preferencial
                            </label>
                            <input type="datetime-local" class="form-control" id="data_atendimento" 
                                   name="data_atendimento"
                                   value="<?= $isEdit && !empty($solicitacao['data_atendimento']) ? date('Y-m-d\TH:i', strtotime($solicitacao['data_atendimento'])) : '' ?>">
                            <div class="form-text text-muted">
                                Selecione a data e horário desejados para o serviço.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="urgencia" class="form-label">
                                <i class="bi bi-lightning me-1"></i>Urgência <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="urgencia" name="urgencia" required>
                                <option value="baixa" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'baixa') ? 'selected' : '' ?>>
                                    Baixa
                                </option>
                                <option value="media" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'media') ? 'selected' : 'selected' ?>>
                                    Média
                                </option>
                                <option value="alta" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'alta') ? 'selected' : '' ?>>
                                    Alta
                                </option>
                            </select>
                            <div class="form-text text-muted">
                                Serviços urgentes são priorizados pelos prestadores.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $isEdit ? 'Atualizar' : 'Criar' ?> Solicitação
                        </button>
                        <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Voltar
                        </a>
                        <?php if ($isEdit): ?>
                            <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-eye me-1"></i>Visualizar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
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

<!-- Modal de confirmação para remover imagem -->
<div class="modal fade" id="modalRemoverImagem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Remoção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja remover esta imagem?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="deletar_imagem">
                    <input type="hidden" name="imagem_id" id="imagemIdRemover">
                    <button type="submit" class="btn btn-danger">Confirmar Remoção</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function previewImages(input) {
    const previewContainer = document.getElementById("imagePreview");
    const addImagesSection = document.getElementById("addImagesSection");
    previewContainer.innerHTML = "";

    if (input.files && input.files.length > 0) {
        previewContainer.style.display = "block";
        if (addImagesSection) addImagesSection.style.display = "block";

        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];

            if (file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const col = document.createElement("div");
                    col.className = "col-md-3 col-sm-6";

                    col.innerHTML = `
                        <div class="card border-success">
                            <div class="card-header bg-success text-white p-1 text-center">
                                <small><i class="bi bi-plus-circle me-1"></i>Nova Foto</small>
                            </div>
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;

                    previewContainer.appendChild(col);
                };

                reader.readAsDataURL(file);
            }
        }
    } else {
        previewContainer.style.display = "none";
        if (addImagesSection) addImagesSection.style.display = "none";
    }
}

function openImageModal(imageSrc, imageNumber) {
    document.getElementById("modalImage").src = imageSrc;
    document.getElementById("imageNumber").textContent = "- Foto " + imageNumber;
    new bootstrap.Modal(document.getElementById("imageModal")).show();
}

function confirmarRemocaoImagem(imagemId) {
    document.getElementById("imagemIdRemover").value = imagemId;
    new bootstrap.Modal(document.getElementById("modalRemoverImagem")).show();
}

function adicionarImagens() {
    // Criar um formulário temporário para enviar apenas as imagens
    const form = document.createElement("form");
    form.method = "POST";
    form.enctype = "multipart/form-data";

    // Adicionar campos necessários
    const csrfToken = document.createElement("input");
    csrfToken.type = "hidden";
    csrfToken.name = "csrf_token";
    csrfToken.value = "' . Session::generateCSRFToken() . '";
    form.appendChild(csrfToken);

    const acao = document.createElement("input");
    acao.type = "hidden";
    acao.name = "acao";
    acao.value = "adicionar_imagens";
    form.appendChild(acao);

    // Clonar o input de imagens
    const imagensInput = document.getElementById("imagens");
    const imagensClone = imagensInput.cloneNode(true);
    form.appendChild(imagensClone);

    // Adicionar ao DOM e submeter
    document.body.appendChild(form);
    form.submit();
}

// Fechar modal ao clicar na imagem
document.addEventListener("DOMContentLoaded", function() {
    const modalImage = document.getElementById("modalImage");
    if (modalImage) {
        modalImage.addEventListener("click", function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById("imageModal"));
            if (modal) modal.hide();
        });
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
