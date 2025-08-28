<?php
$isEdit = isset($solicitacao);
$title = ($isEdit ? 'Editar' : 'Nova') . ' Solicitação - ChamaServiço';
ob_start();
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2"></i>
        <?= $isEdit ? 'Editar Solicitação' : 'Criar Nova Solicitação' ?>
    </h2>
    <form method="POST" action="/chamaservico/cliente/solicitacoes/criar" enctype="multipart/form-data" id="formSolicitacao">
        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

        <!-- Título -->
        <div class="mb-3">
            <label for="titulo" class="form-label">Título da Solicitação *</label>
            <input type="text" class="form-control" id="titulo" name="titulo" 
                   value="<?= $isEdit ? htmlspecialchars($solicitacao['titulo'] ?? '') : '' ?>"
                   required maxlength="100" placeholder="Ex: Instalar chuveiro, consertar torneira...">
            <div class="form-text">Escolha um título claro e objetivo.</div>
        </div>

        <!-- Descrição -->
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição *</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                      required placeholder="Descreva detalhadamente o que precisa ser feito"><?= $isEdit ? htmlspecialchars($solicitacao['descricao'] ?? '') : '' ?></textarea>
            <div class="form-text">Inclua informações como materiais necessários, prazos e outros detalhes importantes.</div>
        </div>

        <!-- Tipo de Serviço -->
        <div class="mb-3">
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

        <!-- Endereço -->
        <div class="mb-3">
            <label for="endereco_id" class="form-label">
                <i class="bi bi-geo-alt me-1"></i>Endereço <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="endereco_id" name="endereco_id" required>
                <option value="">Selecione um endereço</option>
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
            <div class="form-text">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalEndereco">Cadastrar novo endereço</a>
            </div>
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
                <div class="form-text">Informe um valor aproximado, se desejar.</div>
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

        <!-- Upload de Imagens -->
        <div class="mb-3">
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

        <!-- Botões -->
        <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-outline-secondary me-2">Limpar</button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $isEdit ? 'Atualizar' : 'Criar' ?> Solicitação
            </button>
        </div>
    </form>
</div>

<!-- Modal de Cadastro de Endereço -->
<div class="modal fade" id="modalEndereco" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formEnderecoModal" method="POST" action="/chamaservico/cliente/perfil/enderecos">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>Cadastrar Endereço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="acao" value="criar">
                <!-- Campos do endereço -->
                <div class="mb-3">
                    <label for="cepModal" class="form-label">CEP *</label>
                    <input type="text" class="form-control" id="cepModal" name="cep" required>
                </div>
                <div class="mb-3">
                    <label for="logradouroModal" class="form-label">Logradouro *</label>
                    <input type="text" class="form-control" id="logradouroModal" name="logradouro" required>
                </div>
                <div class="mb-3">
                    <label for="numeroModal" class="form-label">Número *</label>
                    <input type="text" class="form-control" id="numeroModal" name="numero" required>
                </div>
                <div class="mb-3">
                    <label for="bairroModal" class="form-label">Bairro *</label>
                    <input type="text" class="form-control" id="bairroModal" name="bairro" required>
                </div>
                <div class="mb-3">
                    <label for="cidadeModal" class="form-label">Cidade *</label>
                    <input type="text" class="form-control" id="cidadeModal" name="cidade" required>
                </div>
                <div class="mb-3">
                    <label for="estadoModal" class="form-label">Estado *</label>
                    <input type="text" class="form-control" id="estadoModal" name="estado" required maxlength="2">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Endereço</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('formEnderecoModal').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('/chamaservico/cliente/perfil/enderecos', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso || data.success) {
            // Atualizar o select de endereços
            fetch('/chamaservico/cliente/perfil/enderecos?action=api_list')
                .then(resp => resp.json())
                .then(endData => {
                    if (endData.sucesso && Array.isArray(endData.enderecos)) {
                        const select = document.getElementById('endereco_id');
                        select.innerHTML = '<option value="">Selecione o endereço</option>';
                        endData.enderecos.forEach(end => {
                            const opt = document.createElement('option');
                            opt.value = end.id;
                            opt.textContent = `${end.logradouro}, ${end.numero} - ${end.bairro}`;
                            select.appendChild(opt);
                        });
                        // Seleciona o último cadastrado
                        if (endData.enderecos.length > 0) {
                            select.value = endData.enderecos[endData.enderecos.length - 1].id;
                        }
                    }
                });
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEndereco'));
            modal.hide();

            // ATUALIZAR TOKEN CSRF DO FORMULÁRIO PRINCIPAL
            fetch('/chamaservico/cliente/solicitacoes/criar?action=csrf')
                .then(resp => resp.json())
                .then(obj => {
                    if (obj.csrf_token) {
                        document.getElementById('csrfTokenSolicitacao').value = obj.csrf_token;
                    }
                });
        } else {
            alert(data.mensagem || 'Erro ao salvar endereço');
        }
    })
    .catch(() => {
        alert('Erro ao salvar endereço');
    });
});

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

document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cepModal');
    const status = document.getElementById('cepStatusModal');
    const logradouro = document.getElementById('logradouroModal');
    const bairro = document.getElementById('bairroModal');
    const cidade = document.getElementById('cidadeModal');
    const estado = document.getElementById('estadoModal');

    // Buscar endereço pelo CEP
    document.getElementById('btnBuscarCepModal').addEventListener('click', function() {
        const cep = cepInput.value.replace(/\D/g, '');
        if (cep.length !== 8) {
            status.textContent = 'CEP inválido';
            status.className = 'text-danger small';
            return;
        }

        status.textContent = 'Buscando endereço...';
        status.className = 'text-muted small';

        fetch('/chamaservico/cliente/perfil/api/buscar-cep?cep=' + cep)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.endereco) {
                    logradouro.value = data.endereco.logradouro || '';
                    bairro.value = data.endereco.bairro || '';
                    cidade.value = data.endereco.cidade || '';
                    estado.value = data.endereco.estado || '';
                    status.textContent = 'Endereço preenchido automaticamente!';
                    status.className = 'text-success small';
                } else {
                    status.textContent = data.message || 'Endereço não encontrado';
                    status.className = 'text-warning small';
                }
            })
            .catch(() => {
                status.textContent = 'Erro ao buscar endereço';
                status.className = 'text-danger small';
            });
    });

    // Validação em tempo real
    cepInput.addEventListener('input', function() {
        const cep = cepInput.value.replace(/\D/g, '');
        if (cep.length === 8) {
            status.textContent = '';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>