<?php
$title = isset($solicitacao) ? 'Editar Solicitação - ChamaServiço' : 'Nova Solicitação - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Header da página -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center bg-primary text-white px-4 py-2 rounded-pill mb-3">
                    <i class="bi bi-plus-circle me-2 fs-5"></i>
                    <span class="fw-bold"><?= isset($solicitacao) ? 'Editar Solicitação' : 'Nova Solicitação' ?></span>
                </div>
                <h2 class="display-6 fw-bold text-dark mb-2">
                    <?= isset($solicitacao) ? 'Edite sua Solicitação' : 'Conte-nos o que você precisa' ?>
                </h2>
                <p class="text-muted fs-5">
                    <?= isset($solicitacao) ? 'Faça as alterações necessárias em sua solicitação' : 'Preencha os detalhes e receba propostas de prestadores qualificados' ?>
                </p>
            </div>

            <!-- Formulário Principal -->
            <form method="POST" enctype="multipart/form-data" id="formSolicitacao" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <?php if (isset($solicitacao)): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($solicitacao['id']) ?>">
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Coluna Principal -->
                    <div class="col-lg-8">
                        <!-- Card 1: Informações Básicas -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Informações Básicas
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <!-- Tipo de Serviço -->
                                <div class="mb-4">
                                    <label for="tipo_servico_id" class="form-label fw-bold">
                                        <i class="bi bi-tools text-primary me-2"></i>
                                        Tipo de Serviço *
                                    </label>
                                    <select class="form-select form-select-lg" id="tipo_servico_id" name="tipo_servico_id" required>
                                        <option value="" disabled <?= !isset($solicitacao) ? 'selected' : '' ?>>
                                            🔍 Selecione o tipo de serviço que você precisa
                                        </option>
                                        <?php foreach ($tiposServico as $tipo): ?>
                                            <option value="<?= $tipo['id'] ?>"
                                                <?= isset($solicitacao) && $solicitacao['tipo_servico_id'] == $tipo['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-lightbulb text-warning"></i>
                                        Escolha a categoria que melhor descreve seu serviço
                                    </div>
                                </div>

                                <!-- Título -->
                                <div class="mb-4">
                                    <label for="titulo" class="form-label fw-bold">
                                        <i class="bi bi-card-text text-primary me-2"></i>
                                        Título da Solicitação *
                                    </label>
                                    <input type="text"
                                        class="form-control form-control-lg"
                                        id="titulo"
                                        name="titulo"
                                        placeholder="Ex.: Instalação de chuveiro elétrico na suíte"
                                        value="<?= htmlspecialchars($solicitacao['titulo'] ?? '') ?>"
                                        maxlength="100"
                                        required>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle text-info"></i>
                                        Seja específico e claro. Um bom título atrai mais prestadores!
                                    </div>
                                    <div class="char-counter text-end">
                                        <small class="text-muted">
                                            <span id="titulo-count">0</span>/100 caracteres
                                        </small>
                                    </div>
                                </div>

                                <!-- Descrição -->
                                <div class="mb-3">
                                    <label for="descricao" class="form-label fw-bold">
                                        <i class="bi bi-chat-text text-primary me-2"></i>
                                        Descrição Detalhada *
                                    </label>
                                    <textarea class="form-control"
                                        id="descricao"
                                        name="descricao"
                                        rows="5"
                                        maxlength="1000"
                                        required><?= htmlspecialchars($solicitacao['descricao'] ?? '') ?></textarea>
                                    <div class="form-text">
                                        <i class="bi bi-check-circle text-success"></i>
                                        Quanto mais detalhes, melhores serão as propostas recebidas
                                    </div>
                                    <div class="char-counter text-end">
                                        <small class="text-muted">
                                            <span id="descricao-count">0</span>/1000 caracteres
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Localização e Urgência -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="mb-0">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    Localização e Urgência
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <!-- Endereço -->
                                    <div class="col-md-8 mb-3">
                                        <label for="endereco_id" class="form-label fw-bold">
                                            <i class="bi bi-house-door text-primary me-2"></i>
                                            Endereço do Serviço *
                                        </label>
                                        <select class="form-select form-select-lg" id="endereco_id" name="endereco_id" required>
                                            <option value="" disabled <?= !isset($solicitacao) ? 'selected' : '' ?>>
                                                📍 Escolha onde o serviço será realizado
                                            </option>
                                            <?php foreach ($enderecos as $endereco): ?>
                                                <option value="<?= $endereco['id'] ?>"
                                                    <?= isset($solicitacao) && $solicitacao['endereco_id'] == $endereco['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($endereco['logradouro'] . ', ' . $endereco['numero'] . ' - ' . $endereco['cidade'] . '/' . $endereco['estado']) ?>
                                                    <?= $endereco['principal'] ? ' (Principal)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                Cadastrar novo endereço
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Urgência -->
                                    <div class="mb-3">
                                        <label for="urgencia" class="form-label fw-bold">
                                            <i class="bi bi-clock text-primary me-2"></i>
                                            Urgência *
                                        </label>
                                        <select class="form-select form-select-lg" id="urgencia" name="urgencia" required>
                                            <option value="baixa" <?= isset($solicitacao) && $solicitacao['urgencia'] == 'baixa' ? 'selected' : '' ?>>
                                                🟢 Baixa - Tenho tempo
                                            </option>
                                            <option value="media" <?= isset($solicitacao) && $solicitacao['urgencia'] == 'media' ? 'selected' : (!isset($solicitacao) ? 'selected' : '') ?>>
                                                🟡 Média - Em alguns dias
                                            </option>
                                            <option value="alta" <?= isset($solicitacao) && $solicitacao['urgencia'] == 'alta' ? 'selected' : '' ?>>
                                                🔴 Alta - É urgente!
                                            </option>
                                        </select>
                                    </div>

                                    <!-- NOVO: Data e Horário da Visita -->
                                    <div class="mb-3">
                                        <label for="data_atendimento" class="form-label fw-bold">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            Data e Horário Preferencial
                                        </label>
                                        <input type="datetime-local"
                                            class="form-control form-control-lg"
                                            id="data_atendimento"
                                            name="data_atendimento"
                                            value="<?= isset($solicitacao['data_atendimento']) ? date('Y-m-d\TH:i', strtotime($solicitacao['data_atendimento'])) : '' ?>"
                                            min="<?= date('Y-m-d\TH:i', strtotime('+1 hour')) ?>">
                                        <div class="form-text">
                                            <i class="bi bi-info-circle text-info"></i>
                                            Informe quando seria ideal realizar o serviço (opcional)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Fotos e Anexos -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <h5 class="mb-0">
                                    <i class="bi bi-camera me-2"></i>
                                    Fotos e Anexos
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="upload-area" id="uploadArea">
                                    <div class="text-center py-5">
                                        <i class="bi bi-cloud-upload text-primary mb-3" style="font-size: 3rem;"></i>
                                        <h6 class="fw-bold">Adicione fotos do local ou problema</h6>
                                        <p class="text-muted mb-3">
                                            Arrastar e soltar ou clique para selecionar
                                        </p>
                                        <input type="file"
                                            class="d-none"
                                            id="imagens"
                                            name="imagens[]"
                                            multiple
                                            accept="image/*">
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imagens').click()">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Selecionar Fotos
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text mt-2">
                                    <i class="bi bi-info-circle text-info"></i>
                                    Máximo 5 fotos • Formatos: JPG, PNG, GIF • Até 5MB cada
                                </div>
                                <div id="preview-container" class="row mt-3 g-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Card: Orçamento -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                                <h5 class="mb-0">
                                    <i class="bi bi-currency-dollar me-2"></i>
                                    Orçamento
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label for="orcamento_estimado" class="form-label fw-bold">
                                        Valor Estimado (R$)
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">R$</span>
                                        <input type="number"
                                            class="form-control"
                                            id="orcamento_estimado"
                                            name="orcamento_estimado"
                                            placeholder="0,00"
                                            step="0.01"
                                            min="0"
                                            value="<?= htmlspecialchars($solicitacao['orcamento_estimado'] ?? '') ?>">
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-lightbulb text-warning"></i>
                                        Opcional. Ajuda os prestadores a entenderem suas expectativas
                                    </div>
                                </div>

                                <!-- Dicas de Orçamento -->
                                <div class="alert alert-info border-0 bg-light">
                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-question-circle me-2"></i>
                                        Dicas de Orçamento
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Pesquise preços na internet</li>
                                        <li>Considere materiais e mão de obra</li>
                                        <li>Deixe margem para negociação</li>
                                        <li>Valores justos atraem melhores profissionais</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Resumo -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-check2-square me-2"></i>
                                    Resumo da Solicitação
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="summary-item mb-3">
                                    <strong>Tipo:</strong>
                                    <span id="summary-tipo" class="text-muted">Não selecionado</span>
                                </div>
                                <div class="summary-item mb-3">
                                    <strong>Urgência:</strong>
                                    <span id="summary-urgencia" class="badge bg-secondary">Média</span>
                                </div>
                                <div class="summary-item mb-3">
                                    <strong>Endereço:</strong>
                                    <span id="summary-endereco" class="text-muted">Não selecionado</span>
                                </div>
                                <div class="summary-item mb-3">
                                    <strong>Orçamento:</strong>
                                    <span id="summary-orcamento" class="text-success">A combinar</span>
                                </div>

                                <!-- NOVO: Resumo da Data -->
                                <div class="summary-item mb-3">
                                    <strong>Data Preferencial:</strong>
                                    <span id="summary-data" class="text-info">Não informada</span>
                                </div>

                                <div class="summary-item">
                                    <strong>Fotos:</strong>
                                    <span id="summary-fotos" class="text-muted">0 anexadas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>
                                <?= isset($solicitacao) ? 'Salvar Alterações' : 'Publicar Solicitação' ?>
                            </button>
                            <a href="<?= url('solicitacoes') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar à Lista
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Cadastro de Endereço -->
<div class="modal fade" id="modalEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formEnderecoModal">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    Cadastrar Novo Endereço
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="acao" value="adicionar">
                <input type="hidden" name="from_modal" value="true">

                <!-- Alerta de sucesso/erro -->
                <div id="alertModal" style="display: none;"></div>

                <!-- CEP -->
                <div class="mb-3">
                    <label for="cepModal" class="form-label fw-bold">CEP *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cepModal" name="cep" required maxlength="9" pattern="\d{5}-?\d{3}" placeholder="00000-000">
                        <button type="button" class="btn btn-outline-info" id="btnBuscarCepModal">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div id="cepStatusModal" class="small mt-1"></div>
                </div>

                <!-- Logradouro -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="logradouroModal" class="form-label fw-bold">Logradouro *</label>
                        <input type="text" class="form-control" id="logradouroModal" name="logradouro" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="numeroModal" class="form-label fw-bold">Número *</label>
                        <input type="text" class="form-control" id="numeroModal" name="numero" required>
                    </div>
                </div>

                <!-- Complemento e Bairro -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="complementoModal" class="form-label">Complemento</label>
                        <input type="text" class="form-control" id="complementoModal" name="complemento" placeholder="Apto, casa, etc.">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bairroModal" class="form-label fw-bold">Bairro *</label>
                        <input type="text" class="form-control" id="bairroModal" name="bairro" required>
                    </div>
                </div>

                <!-- Cidade e Estado -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="cidadeModal" class="form-label fw-bold">Cidade *</label>
                        <input type="text" class="form-control" id="cidadeModal" name="cidade" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estadoModal" class="form-label fw-bold">Estado *</label>
                        <input type="text" class="form-control" id="estadoModal" name="estado" required maxlength="2" placeholder="SP">
                    </div>
                </div>

                <!-- Principal -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="principal" id="principalModal" value="1">
                    <label class="form-check-label" for="principalModal">
                        Definir como endereço principal
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSalvarEndereco">
                    <i class="bi bi-save me-2"></i>
                    Salvar Endereço
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .upload-area {
        border: 2px dashed #007bff;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #0056b3;
        background-color: #f8f9fa;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background-color: #d4edda;
    }

    .char-counter {
        font-size: 0.875rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .preview-image {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }

    .preview-image img {
        width: 100%;
        height: 80px;
        object-fit: cover;
    }

    .preview-image .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        font-size: 12px;
        cursor: pointer;
    }

    .card-header.bg-gradient {
        border: none;
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding: 15px;
        }

        .card-body {
            padding: 20px !important;
        }
    }
</style>

<?php
$scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Contadores de caracteres
    const tituloInput = document.getElementById("titulo");
    const descricaoInput = document.getElementById("descricao");
    const tituloCount = document.getElementById("titulo-count");
    const descricaoCount = document.getElementById("descricao-count");
    
    function updateCharCount(input, counter) {
        counter.textContent = input.value.length;
        const maxLength = input.getAttribute("maxlength");
        if (input.value.length > maxLength * 0.9) {
            counter.parentElement.classList.add("text-warning");
        } else {
            counter.parentElement.classList.remove("text-warning");
        }
    }
    
    tituloInput.addEventListener("input", () => updateCharCount(tituloInput, tituloCount));
    descricaoInput.addEventListener("input", () => updateCharCount(descricaoInput, descricaoCount));
    
    // Inicializar contadores
    updateCharCount(tituloInput, tituloCount);
    updateCharCount(descricaoInput, descricaoCount);
    
    // Resumo dinâmico
    function updateSummary() {
        const tipoSelect = document.getElementById("tipo_servico");
        const urgenciaSelect = document.getElementById("urgencia");
        const enderecoSelect = document.getElementById("endereco");
        const orcamentoInput = document.getElementById("orcamento");
        const dataInput = document.getElementById("data_atendimento");
        
        // Tipo
        const summaryTipo = document.getElementById("summary-tipo");
        summaryTipo.textContent = tipoSelect.value ? tipoSelect.options[tipoSelect.selectedIndex].text : "Não selecionado";
        
        // Urgência
        const summaryUrgencia = document.getElementById("summary-urgencia");
        const urgenciaText = urgenciaSelect.options[urgenciaSelect.selectedIndex].text;
        summaryUrgencia.textContent = urgenciaText.split(" - ")[0];
        summaryUrgencia.className = "badge " + (urgenciaSelect.value === "alta" ? "bg-danger" : urgenciaSelect.value === "media" ? "bg-warning" : "bg-success");
        
        // Endereço
        const summaryEndereco = document.getElementById("summary-endereco");
        summaryEndereco.textContent = enderecoSelect.value ? enderecoSelect.options[enderecoSelect.selectedIndex].text.substring(0, 30) + "..." : "Não selecionado";
        
        // Orçamento
        const summaryOrcamento = document.getElementById("summary-orcamento");
        summaryOrcamento.textContent = orcamentoInput.value ? "R$ " + parseFloat(orcamentoInput.value).toLocaleString("pt-BR", {minimumFractionDigits: 2}) : "A combinar";
        
        // NOVO: Data Preferencial
        const summaryData = document.getElementById("summary-data");
        if (dataInput.value) {
            const dataFormatada = new Date(dataInput.value).toLocaleString("pt-BR", {
                day: "2-digit",
                month: "2-digit", 
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            });
            summaryData.textContent = dataFormatada;
            summaryData.className = "text-success";
        } else {
            summaryData.textContent = "Não informada";
            summaryData.className = "text-muted";
        }
    }
    
    document.getElementById("tipo_servico").addEventListener("change", updateSummary);
    document.getElementById("urgencia").addEventListener("change", updateSummary);
    document.getElementById("endereco").addEventListener("change", updateSummary);
    document.getElementById("orcamento").addEventListener("input", updateSummary);
    document.getElementById("data_atendimento").addEventListener("change", updateSummary);
    
    // Upload de imagens
    const uploadArea = document.getElementById("uploadArea");
    const imageInput = document.getElementById("imagens");
    const previewContainer = document.getElementById("preview-container");
    let selectedFiles = [];
    
    uploadArea.addEventListener("click", () => imageInput.click());
    
    uploadArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        uploadArea.classList.add("dragover");
    });
    
    uploadArea.addEventListener("dragleave", () => {
        uploadArea.classList.remove("dragover");
    });
    
    uploadArea.addEventListener("drop", (e) => {
        e.preventDefault();
        uploadArea.classList.remove("dragover");
        handleFiles(e.dataTransfer.files);
    });
    
    imageInput.addEventListener("change", (e) => {
        handleFiles(e.target.files);
    });
    
    function handleFiles(files) {
        if (files.length + selectedFiles.length > 5) {
            alert("Máximo 5 imagens permitidas!");
            return;
        }
        
        Array.from(files).forEach(file => {
            if (file.type.startsWith("image/") && file.size <= 5 * 1024 * 1024) {
                selectedFiles.push(file);
                createPreview(file);
            } else {
                alert("Arquivo inválido: " + file.name);
            }
        });
        
        updateFileInput();
        updatePhotosSummary();
    }
    
    function createPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const col = document.createElement("div");
            col.className = "col-3";
            col.innerHTML = `
                <div class="preview-image">
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" onclick="removePreview(this, \'${file.name}\')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            previewContainer.appendChild(col);
            previewContainer.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
    
    window.removePreview = function(btn, fileName) {
        selectedFiles = selectedFiles.filter(f => f.name !== fileName);
        btn.closest(".col-3").remove();
        if (selectedFiles.length === 0) {
            previewContainer.style.display = "none";
        }
        updateFileInput();
        updatePhotosSummary();
    };
    
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
    }
    
    function updatePhotosSummary() {
        document.getElementById("summary-fotos").textContent = selectedFiles.length + " anexada" + (selectedFiles.length !== 1 ? "s" : "");
    }
    
    // Buscar CEP no modal
    const btnBuscarCepModal = document.getElementById("btnBuscarCepModal");
    if (btnBuscarCepModal) {
        btnBuscarCepModal.addEventListener("click", function() {
            const cepInput = document.getElementById("cepModal");
            const status = document.getElementById("cepStatusModal");
            const cep = cepInput.value.replace(/\D/g, "");
            
            if (cep.length !== 8) {
                status.textContent = "CEP deve ter 8 dígitos";
                status.className = "text-danger small mt-1";
                return;
            }
            
            // Mostrar loading
            status.textContent = "Buscando endereço...";
            status.className = "text-primary small mt-1";
            btnBuscarCepModal.disabled = true;
            btnBuscarCepModal.innerHTML = \'<i class="spinner-border spinner-border-sm"></i>\';
            
            // Fazer requisição para API
            fetch(`perfil/api/buscar-cep?cep=${cep}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.endereco) {
                        // Preencher campos automaticamente
                        document.getElementById("logradouroModal").value = data.endereco.logradouro || "";
                        document.getElementById("bairroModal").value = data.endereco.bairro || "";
                        document.getElementById("cidadeModal").value = data.endereco.cidade || "";
                        document.getElementById("estadoModal").value = data.endereco.estado || "";
                        
                        status.textContent = "Endereço preenchido automaticamente!";
                        status.className = "text-success small mt-1";
                        
                        // Focar no campo número
                        document.getElementById("numeroModal").focus();
                    } else {
                        status.textContent = data.message || "CEP não encontrado";
                        status.className = "text-warning small mt-1";
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    status.textContent = "Erro ao buscar CEP. Verifique sua conexão.";
                    status.className = "text-danger small mt-1";
                })
                .finally(() => {
                    // Restaurar botão
                    btnBuscarCepModal.disabled = false;
                    btnBuscarCepModal.innerHTML = \'<i class="bi bi-search"></i>\';
                });
        });
        
        // Buscar CEP ao pressionar Enter
        document.getElementById("cepModal").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                btnBuscarCepModal.click();
            }
        });
        
        // Formatar CEP enquanto digita
        document.getElementById("cepModal").addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 5) {
                value = value.substring(0, 5) + "-" + value.substring(5, 8);
            }
            e.target.value = value;
            
            // Limpar status se CEP for alterado
            const status = document.getElementById("cepStatusModal");
            status.textContent = "";
        });
    }
    
    // Processar formulário da modal via AJAX
    const formEnderecoModal = document.getElementById("formEnderecoModal");
    const btnSalvarEndereco = document.getElementById("btnSalvarEndereco");
    const alertModal = document.getElementById("alertModal");
    
    if (formEnderecoModal) {
        formEnderecoModal.addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Validar campos obrigatórios no frontend
            const camposObrigatorios = [
                {name: "cep", label: "CEP"},
                {name: "logradouro", label: "Logradouro"},
                {name: "numero", label: "Número"},
                {name: "bairro", label: "Bairro"},
                {name: "cidade", label: "Cidade"},
                {name: "estado", label: "Estado"}
            ];
            
            let camposVazios = [];
            
            camposObrigatorios.forEach(campo => {
                const input = document.querySelector(\`#formEnderecoModal input[name="\${campo.name}"]\`);
                if (!input || !input.value.trim()) {
                    camposVazios.push(campo.label);
                    if (input) input.classList.add("is-invalid");
                } else {
                    if (input) input.classList.remove("is-invalid");
                }
            });
            
            if (camposVazios.length > 0) {
                showModalAlert("danger", "Preencha os campos obrigatórios: " + camposVazios.join(", "));
                return;
            }
            
            // Validar CEP
            const cep = document.getElementById("cepModal").value.replace(/\D/g, "");
            if (cep.length !== 8) {
                showModalAlert("danger", "CEP deve ter 8 dígitos");
                document.getElementById("cepModal").classList.add("is-invalid");
                return;
            }
            
            // Validar estado
            const estado = document.getElementById("estadoModal").value.trim();
            if (estado.length !== 2) {
                showModalAlert("danger", "Estado deve ter 2 caracteres");
                document.getElementById("estadoModal").classList.add("is-invalid");
                return;
            }
            
            // Mostrar loading
            btnSalvarEndereco.disabled = true;
            btnSalvarEndereco.innerHTML = \'<i class="spinner-border spinner-border-sm me-2"></i>Salvando...\';
            
            // Esconder alertas anteriores
            alertModal.style.display = "none";
            
            const formData = new FormData(formEnderecoModal);
            
            // Log para debug
            console.log("Enviando dados do endereço:", Object.fromEntries(formData));
            
            fetch("perfil/enderecos", {
                method: "POST",
                body: formData
            })
            .then(response => {
                console.log("Status da resposta:", response.status);
                
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Resposta não é JSON válido");
                }
                
                return response.json();
            })
            .then(data => {
                console.log("Resposta do servidor:", data);
                
                if (data.sucesso) {
                    // Mostrar sucesso
                    showModalAlert("success", data.mensagem);
                    
                    // Atualizar o select de endereços
                    if (data.endereco && data.reload_select) {
                        addEnderecoToSelect(data.endereco);
                    }
                    
                    // Fechar modal após 2 segundos
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById("modalEndereco"));
                        if (modal) {
                            modal.hide();
                        }
                        formEnderecoModal.reset();
                        alertModal.style.display = "none";
                        
                        // Remover classes de erro
                        document.querySelectorAll("#formEnderecoModal .is-invalid").forEach(el => {
                            el.classList.remove("is-invalid");
                        });
                    }, 2000);
                    
                } else {
                    showModalAlert("danger", data.mensagem || "Erro desconhecido ao salvar endereço");
                }
            })
            .catch(error => {
                console.error("Erro na requisição:", error);
                showModalAlert("danger", "Erro ao salvar endereço: " + error.message);
            })
            .finally(() => {
                // Restaurar botão
                btnSalvarEndereco.disabled = false;
                btnSalvarEndereco.innerHTML = \'<i class="bi bi-save me-2"></i>Salvar Endereço\';
            });
        });
    }
    
    // Função para mostrar alertas na modal
    function showModalAlert(type, message) {
        alertModal.className = `alert alert-${type} alert-dismissible fade show`;
        alertModal.innerHTML = `
            <i class="bi bi-${type === "success" ? "check-circle" : "exclamation-triangle"} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertModal.style.display = "block";
    }
    
    // Função para adicionar endereço ao select
    function addEnderecoToSelect(endereco) {
        const enderecoSelect = document.getElementById("endereco");
        if (enderecoSelect && endereco) {
            const option = document.createElement("option");
            option.value = endereco.id;
            option.selected = true;
            
            const enderecoCompleto = `${endereco.logradouro}, ${endereco.numero} - ${endereco.cidade}/${endereco.estado}`;
            if (endereco.principal) {
                option.textContent = enderecoCompleto + " (Principal)";
            } else {
                option.textContent = enderecoCompleto;
            }
            
            enderecoSelect.appendChild(option);
            
            // Atualizar resumo se existir
            if (typeof updateSummary === "function") {
                updateSummary();
            }
            
            // Mostrar feedback visual
            enderecoSelect.classList.add("border-success");
            setTimeout(() => {
                enderecoSelect.classList.remove("border-success");
            }, 3000);
        }
    }
    
    // Limpar modal ao fechar
    const modalEndereco = document.getElementById("modalEndereco");
    if (modalEndereco) {
        modalEndereco.addEventListener("hidden.bs.modal", function() {
            formEnderecoModal.reset();
            alertModal.style.display = "none";
            document.getElementById("cepStatusModal").textContent = "";
            btnSalvarEndereco.disabled = false;
            btnSalvarEndereco.innerHTML = \'<i class="bi bi-save me-2"></i>Salvar Endereço\';
        });
    }
    
    // Inicializar resumo
    updateSummary();
    updatePhotosSummary();
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>