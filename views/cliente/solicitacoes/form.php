<?php
$isEdit = isset($solicitacao);
$title = ($isEdit ? 'Editar' : 'Nova') . ' Solicitação - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2"></i>
                <?= $isEdit ? 'Editar Solicitação' : 'Nova Solicitação' ?>
            </h2>
            <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <div class="card shadow-sm">
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
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="endereco_id" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i>Endereço <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="endereco_id" name="endereco_id" required>
                                <option value="">Selecione o endereço</option>
                                <?php if (!empty($enderecos)): ?>
                                    <?php foreach ($enderecos as $endereco): ?>
                                        <option value="<?= $endereco['id'] ?>"
                                                <?= ($isEdit && $solicitacao['endereco_id'] == $endereco['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($endereco['logradouro'] . ', ' . $endereco['numero'] . ' - ' . $endereco['bairro']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Nenhum endereço cadastrado</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            <i class="bi bi-card-heading me-1"></i>Título <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?= $isEdit ? htmlspecialchars($solicitacao['titulo'] ?? '') : '' ?>"
                               required maxlength="100" placeholder="Ex: Instalar chuveiro, consertar torneira...">
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">
                            <i class="bi bi-chat-left-text me-1"></i>Descrição <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                  required placeholder="Descreva detalhadamente o que precisa ser feito"><?= $isEdit ? htmlspecialchars($solicitacao['descricao'] ?? '') : '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="imagens" class="form-label">
                            <i class="bi bi-camera me-1"></i>Fotos do Local/Problema
                        </label>
                        <input type="file" class="form-control" id="imagens" name="imagens[]" 
                               multiple accept="image/*" onchange="previewImages(this)">
                        <div class="form-text">
                            Adicione fotos para ajudar os prestadores. Máximo 5MB por imagem.
                        </div>
                        <div id="imagePreview" class="mt-3 row g-2" style="display: none;"></div>
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
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="data_atendimento" class="form-label">
                                <i class="bi bi-calendar-event me-1"></i>Data Preferencial
                            </label>
                            <input type="datetime-local" class="form-control" id="data_atendimento" 
                                   name="data_atendimento"
                                   value="<?= $isEdit && !empty($solicitacao['data_atendimento']) ? date('Y-m-d\TH:i', strtotime($solicitacao['data_atendimento'])) : '' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="urgencia" class="form-label">
                                <i class="bi bi-lightning me-1"></i>Urgência <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="urgencia" name="urgencia" required>
                                <option value="baixa" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'baixa') ? 'selected' : '' ?>>Baixa</option>
                                <option value="media" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'media') ? 'selected' : 'selected' ?>>Média</option>
                                <option value="alta" <?= ($isEdit && ($solicitacao['urgencia'] ?? '') === 'alta') ? 'selected' : '' ?>>Alta</option>
                            </select>
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
                    </div>
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
    previewContainer.innerHTML = "";

    if (input.files && input.files.length > 0) {
        previewContainer.style.display = "block";

        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];

            if (file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const col = document.createElement("div");
                    col.className = "col-md-3 col-sm-6";

                    col.innerHTML = `
                        <div class="card border-success">
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
    }
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>