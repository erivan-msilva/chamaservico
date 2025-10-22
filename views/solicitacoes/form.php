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

            <!-- Barra de Progresso do Wizard -->
            <div class="wizard-progress mb-5">
                <div class="progress-container">
                    <div class="progress-line" id="progressLine"></div>
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div class="step-label">Detalhes</div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="step-label">Localização</div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">
                            <i class="bi bi-camera"></i>
                        </div>
                        <div class="step-label">Fotos</div>
                    </div>
                    <div class="step-item" data-step="4">
                        <div class="step-circle">
                            <i class="bi bi-check2-square"></i>
                        </div>
                        <div class="step-label">Finalizar</div>
                    </div>
                </div>
            </div>

            <!-- Formulário Principal -->
            <form method="POST" enctype="multipart/form-data" id="formSolicitacao" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <?php if (isset($solicitacao)): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($solicitacao['id']) ?>">
                <?php endif; ?>
                <input type="hidden" name="urgencia" id="urgenciaHidden" value="<?= htmlspecialchars($solicitacao['urgencia'] ?? 'media') ?>">

                <div class="row g-4">
                    <!-- Área Principal do Wizard -->
                    <div class="col-lg-8">
                        <!-- Etapa 1: Informações Básicas -->
                        <div class="wizard-step active" id="step1">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h5 class="mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Passo 1: Informações Básicas
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
                                            placeholder="Descreva detalhadamente o que você precisa. Quanto mais informações, melhores propostas você receberá..."
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

                                    <!-- Orçamento Estimado -->
                                    <div class="mb-3">
                                        <label for="orcamento_estimado" class="form-label fw-bold">
                                            <i class="bi bi-currency-dollar text-primary me-2"></i>
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
                                </div>
                            </div>
                        </div>

                        <!-- Etapa 2: Localização e Urgência -->
                        <div class="wizard-step" id="step2">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <h5 class="mb-0">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        Passo 2: Localização e Urgência
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Endereço -->
                                    <div class="mb-4">
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

                                    <!-- Urgência - Botões Interativos -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-clock text-primary me-2"></i>
                                            Nível de Urgência *
                                        </label>
                                        <div class="urgency-selector">
                                            <div class="urgency-option" data-value="baixa">
                                                <div class="urgency-icon">🟢</div>
                                                <div class="urgency-title">Baixa</div>
                                                <div class="urgency-desc">Tenho tempo</div>
                                            </div>
                                            <div class="urgency-option active" data-value="media">
                                                <div class="urgency-icon">🟡</div>
                                                <div class="urgency-title">Média</div>
                                                <div class="urgency-desc">Em alguns dias</div>
                                            </div>
                                            <div class="urgency-option" data-value="alta">
                                                <div class="urgency-icon">🔴</div>
                                                <div class="urgency-title">Alta</div>
                                                <div class="urgency-desc">É urgente!</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data e Horário da Visita -->
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

                        <!-- Etapa 3: Fotos e Anexos -->
                        <div class="wizard-step" id="step3">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <h5 class="mb-0">
                                        <i class="bi bi-camera me-2"></i>
                                        Passo 3: Fotos e Anexos
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

                                    <!-- Alertas de Upload -->
                                    <div id="uploadAlerts" class="mt-3"></div>

                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle text-info"></i>
                                        Máximo 5 fotos • Formatos: JPG, PNG, GIF • Até 5MB cada
                                    </div>
                                    <div id="preview-container" class="row mt-3 g-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Etapa 4: Finalizar -->
                        <div class="wizard-step" id="step4">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h5 class="mb-0">
                                        <i class="bi bi-check2-square me-2"></i>
                                        Passo 4: Revisão e Finalização
                                    </h5>
                                </div>
                                <div class="card-body p-4 text-center">
                                    <div class="mb-4">
                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                        <h4 class="mt-3 mb-2">Quase pronto!</h4>
                                        <p class="text-muted">Revise suas informações no resumo ao lado e publique sua solicitação.</p>
                                    </div>

                                    <div class="alert alert-info border-0">
                                        <h6 class="fw-bold mb-2">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            O que acontece agora?
                                        </h6>
                                        <ul class="mb-0 text-start small">
                                            <li>Sua solicitação será publicada na plataforma</li>
                                            <li>Prestadores qualificados verão seu pedido</li>
                                            <li>Você receberá propostas e poderá escolher a melhor</li>
                                            <li>Mantenha-se disponível para contato</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navegação do Wizard -->
                        <div class="wizard-navigation d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg" id="btnVoltar" style="display: none;">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar
                            </button>
                            <div></div>
                            <button type="button" class="btn btn-primary btn-lg" id="btnAvancar">
                                Avançar
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success btn-lg" id="btnFinalizar" style="display: none;">
                                <i class="bi bi-send me-2"></i>
                                <?= isset($solicitacao) ? 'Salvar Alterações' : 'Publicar Solicitação' ?>
                            </button>
                        </div>
                    </div>

                    <!-- Sidebar - Resumo Dinâmico -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-check2-square me-2"></i>
                                    Resumo da Solicitação
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="summary-item mb-3">
                                    <div class="summary-label">Tipo de Serviço:</div>
                                    <div class="summary-value" id="summary-tipo">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>Não selecionado</span>
                                    </div>
                                </div>

                                <div class="summary-item mb-3">
                                    <div class="summary-label">Título:</div>
                                    <div class="summary-value" id="summary-titulo">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>Não informado</span>
                                    </div>
                                </div>

                                <div class="summary-item mb-3">
                                    <div class="summary-label">Urgência:</div>
                                    <div class="summary-value" id="summary-urgencia">
                                        <span class="badge bg-warning">🟡 Média</span>
                                    </div>
                                </div>

                                <div class="summary-item mb-3">
                                    <div class="summary-label">Endereço:</div>
                                    <div class="summary-value" id="summary-endereco">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>Não selecionado</span>
                                    </div>
                                </div>

                                <div class="summary-item mb-3">
                                    <div class="summary-label">Orçamento:</div>
                                    <div class="summary-value" id="summary-orcamento">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>A combinar</span>
                                    </div>
                                </div>

                                <div class="summary-item mb-3">
                                    <div class="summary-label">Data Preferencial:</div>
                                    <div class="summary-value" id="summary-data">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>Não informada</span>
                                    </div>
                                </div>

                                <div class="summary-item">
                                    <div class="summary-label">Fotos:</div>
                                    <div class="summary-value" id="summary-fotos">
                                        <i class="bi bi-circle text-muted me-2"></i>
                                        <span>0 anexadas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Link para voltar à lista -->
                        <div class="text-center">
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
    /* Estilos do Wizard */
    .wizard-progress {
        position: relative;
        margin-bottom: 3rem;
    }

    .progress-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .progress-line {
        position: absolute;
        top: 25px;
        left: 50px;
        right: 50px;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        z-index: 1;
    }

    .progress-line::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: linear-gradient(90deg, #007bff, #0056b3);
        border-radius: 2px;
        transition: width 0.3s ease;
        width: 0%;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 2;
        position: relative;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        border: 3px solid #e9ecef;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    .step-item.active .step-circle,
    .step-item.completed .step-circle {
        background: #007bff;
        border-color: #007bff;
        color: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25);
    }

    .step-item.completed .step-circle {
        background: #28a745;
        border-color: #28a745;
        box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.25);
    }

    .step-label {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #6c757d;
        transition: color 0.3s ease;
    }

    .step-item.active .step-label,
    .step-item.completed .step-label {
        color: #007bff;
    }

    .step-item.completed .step-label {
        color: #28a745;
    }

    /* Etapas do Wizard */
    .wizard-step {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }

    .wizard-step.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Seletor de Urgência */
    .urgency-selector {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 1rem;
    }

    .urgency-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .urgency-option:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }

    .urgency-option.active {
        border-color: #007bff;
        background: #f8f9ff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .urgency-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .urgency-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
    }

    .urgency-desc {
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Resumo Lateral */
    .summary-item {
        margin-bottom: 1rem;
    }

    .summary-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .summary-value {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
    }

    .summary-value i.bi-check-circle-fill {
        color: #28a745;
    }

    .summary-value i.bi-circle {
        color: #dee2e6;
    }

    /* Upload melhorado */
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

    .preview-image {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .preview-image img {
        width: 100%;
        height: 100px;
        object-fit: cover;
    }

    .preview-info {
        padding: 0.5rem;
        font-size: 0.75rem;
        color: #6c757d;
        border-top: 1px solid #dee2e6;
    }

    .preview-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
        word-break: break-all;
    }

    .preview-size {
        color: #868e96;
    }

    .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .remove-btn:hover {
        background: #dc3545;
    }

    /* Navegação do Wizard */
    .wizard-navigation {
        border-top: 1px solid #dee2e6;
        padding-top: 1.5rem;
        margin-top: 2rem;
    }

    /* Upload Alerts */
    .upload-alert {
        padding: 0.75rem 1rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        font-size: 0.875rem;
    }

    .upload-alert.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .upload-alert.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .progress-container {
            max-width: 100%;
            padding: 0 1rem;
        }

        .step-label {
            font-size: 0.75rem;
        }

        .urgency-selector {
            grid-template-columns: 1fr;
        }

        .wizard-navigation {
            flex-direction: column;
            gap: 1rem;
        }

        .wizard-navigation button {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .step-circle {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .progress-line {
            left: 40px;
            right: 40px;
            top: 20px;
        }
    }
</style>

<?php
$scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Elementos do wizard
    const progressLine = document.getElementById("progressLine");
    const btnVoltar = document.getElementById("btnVoltar");
    const btnAvancar = document.getElementById("btnAvancar");
    const btnFinalizar = document.getElementById("btnFinalizar");
    
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
        updateSummary();
    }
    
    tituloInput.addEventListener("input", () => updateCharCount(tituloInput, tituloCount));
    descricaoInput.addEventListener("input", () => updateCharCount(descricaoInput, descricaoCount));
    
    // Inicializar contadores
    updateCharCount(tituloInput, tituloCount);
    updateCharCount(descricaoInput, descricaoCount);
    
    // Funcionalidade do wizard
    function updateWizardProgress() {
        // Atualizar barra de progresso
        const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressLine.style.setProperty("--progress", progressPercent + "%");
        progressLine.querySelector("::before") && (progressLine.style.background = `linear-gradient(90deg, #007bff ${progressPercent}%, #e9ecef ${progressPercent}%)`);
        
        // Linha de progresso com pseudo-elemento
        progressLine.style.background = `linear-gradient(90deg, #007bff ${progressPercent}%, #e9ecef ${progressPercent}%)`;
        
        // Atualizar indicadores das etapas
        document.querySelectorAll(".step-item").forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove("active", "completed");
            
            if (stepNum < currentStep) {
                step.classList.add("completed");
                step.querySelector(".step-circle i").className = "bi bi-check";
            } else if (stepNum === currentStep) {
                step.classList.add("active");
            }
        });
        
        // Mostrar/esconder etapas
        document.querySelectorAll(".wizard-step").forEach((step, index) => {
            step.classList.toggle("active", index + 1 === currentStep);
        });
        
        // Atualizar botões de navegação
        btnVoltar.style.display = currentStep > 1 ? "block" : "none";
        btnAvancar.style.display = currentStep < totalSteps ? "block" : "none";
        btnFinalizar.style.display = currentStep === totalSteps ? "block" : "none";
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step${currentStep}`);
        const requiredFields = currentStepElement.querySelectorAll("[required]");
        let isValid = true;
        
        requiredFields.forEach(field => {
            field.classList.remove("is-invalid");
            if (!field.value.trim()) {
                field.classList.add("is-invalid");
                isValid = false;
            }
        });
        
        // Validação específica para urgência (se estivermos na etapa 2)
        if (currentStep === 2) {
            const urgenciaSelected = document.querySelector(".urgency-option.active");
            if (!urgenciaSelected) {
                showAlert("error", "Por favor, selecione o nível de urgência.");
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Navegação do wizard
    btnAvancar.addEventListener("click", function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardProgress();
                updateSummary();
                // Scroll para o topo
                window.scrollTo({top: 0, behavior: "smooth"});
            }
        }
    });
    
    btnVoltar.addEventListener("click", function() {
        if (currentStep > 1) {
            currentStep--;
            updateWizardProgress();
            window.scrollTo({top: 0, behavior: "smooth"});
        }
    });
    
    // Seletor de urgência
    document.querySelectorAll(".urgency-option").forEach(option => {
        option.addEventListener("click", function() {
            // Remover seleção anterior
            document.querySelectorAll(".urgency-option").forEach(opt => opt.classList.remove("active"));
            
            // Adicionar seleção atual
            this.classList.add("active");
            
            // Atualizar campo hidden
            document.getElementById("urgenciaHidden").value = this.dataset.value;
            
            updateSummary();
        });
    });
    
    // Resumo dinâmico
    function updateSummary() {
        const tipoSelect = document.getElementById("tipo_servico_id");
        const tituloInput = document.getElementById("titulo");
        const urgenciaSelected = document.querySelector(".urgency-option.active");
        const enderecoSelect = document.getElementById("endereco_id");
        const orcamentoInput = document.getElementById("orcamento_estimado");
        const dataInput = document.getElementById("data_atendimento");
        
        // Tipo
        updateSummaryItem("summary-tipo", tipoSelect.value, 
            tipoSelect.options[tipoSelect.selectedIndex]?.text || "Não selecionado");
        
        // Título
        updateSummaryItem("summary-titulo", tituloInput.value, 
            tituloInput.value || "Não informado");
        
        // Urgência
        if (urgenciaSelected) {
            const urgenciaTexts = {
                baixa: "🟢 Baixa",
                media: "🟡 Média", 
                alta: "🔴 Alta"
            };
            const urgenciaColors = {
                baixa: "bg-success",
                media: "bg-warning",
                alta: "bg-danger"
            };
            const urgenciaValue = urgenciaSelected.dataset.value;
            document.getElementById("summary-urgencia").innerHTML = 
                `<span class="badge ${urgenciaColors[urgenciaValue]}">${urgenciaTexts[urgenciaValue]}</span>`;
        }
        
        // Endereço
        updateSummaryItem("summary-endereco", enderecoSelect.value,
            enderecoSelect.value ? enderecoSelect.options[enderecoSelect.selectedIndex].text.substring(0, 30) + "..." : "Não selecionado");
        
        // Orçamento
        updateSummaryItem("summary-orcamento", orcamentoInput.value,
            orcamentoInput.value ? "R$ " + parseFloat(orcamentoInput.value).toLocaleString("pt-BR", {minimumFractionDigits: 2}) : "A combinar");
        
        // Data
        if (dataInput.value) {
            const dataFormatada = new Date(dataInput.value).toLocaleString("pt-BR", {
                day: "2-digit",
                month: "2-digit", 
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            });
            updateSummaryItem("summary-data", dataInput.value, dataFormatada, true);
        } else {
            updateSummaryItem("summary-data", "", "Não informada");
        }
    }
    
    function updateSummaryItem(elementId, value, text, isCompleted = null) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector("i");
        const span = element.querySelector("span");
        
        const hasValue = isCompleted !== null ? isCompleted : (value && value.trim());
        
        if (hasValue) {
            icon.className = "bi bi-check-circle-fill text-success me-2";
            span.textContent = text;
            span.style.color = "#212529";
        } else {
            icon.className = "bi bi-circle text-muted me-2";
            span.textContent = text;
            span.style.color = "#6c757d";
        }
    }
    
    // Eventos para atualizar resumo
    document.getElementById("tipo_servico_id").addEventListener("change", updateSummary);
    document.getElementById("endereco_id").addEventListener("change", updateSummary);
    document.getElementById("orcamento_estimado").addEventListener("input", updateSummary);
    document.getElementById("data_atendimento").addEventListener("change", updateSummary);
    
    // Upload de imagens melhorado
    const uploadArea = document.getElementById("uploadArea");
    const imageInput = document.getElementById("imagens");
    const previewContainer = document.getElementById("preview-container");
    const uploadAlerts = document.getElementById("uploadAlerts");
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
        if (e.target.files && e.target.files.length > 0) {
            handleFiles(e.target.files);
            // Limpar o input para permitir selecionar os mesmos arquivos novamente
            e.target.value = "";
        }
    });
    
    function showAlert(type, message) {
        const alertDiv = document.createElement("div");
        alertDiv.className = `upload-alert ${type}`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === "error" ? "exclamation-triangle" : "check-circle"} me-2"></i>
            ${message}
        `;
        
        uploadAlerts.appendChild(alertDiv);
        
        // Remover alerta após 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
    }
    
    function handleFiles(files) {
        // Validar se files existe
        if (!files || files.length === 0) {
            console.log("DEBUG: Nenhum arquivo selecionado");
            return;
        }
        
        console.log(`DEBUG: Processando ${files.length} arquivo(s)`);
        
        // Limpar alertas anteriores
        uploadAlerts.innerHTML = "";
        
        // Verificar limite total
        if (files.length + selectedFiles.length > 5) {
            showAlert("error", "Máximo 5 imagens permitidas!");
            return;
        }
        
        let validFiles = 0;
        let errorCount = 0;
        
        Array.from(files).forEach(file => {
            console.log(`DEBUG: Validando arquivo: ${file.name} (${file.type}, ${formatFileSize(file.size)})`);
            
            // Verificar se já existee
            const jaExiste = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (jaExiste) {
                console.log(`DEBUG: Arquivo ${file.name} já está na lista`);
                showAlert("error", `Arquivo "${file.name}" já foi adicionado.`);
                errorCount++;
                return;
            }
            
            // Validar tipo
            if (!file.type.startsWith("image/")) {
                console.log(`DEBUG: Tipo inválido: ${file.type}`);
                showAlert("error", `Arquivo "${file.name}" não é uma imagem válida.`);
                errorCount++;
                return;
            }
            
            // Validar tamanho
            if (file.size > 5 * 1024 * 1024) {
                console.log(`DEBUG: Arquivo muito grande: ${file.size} bytes`);
                showAlert("error", `Arquivo "${file.name}" é muito grande (máximo 5MB).`);
                errorCount++;
                return;
            }
            
            // Adicionar arquivo válido
            selectedFiles.push(file);
            createPreview(file);
            validFiles++;
            console.log(`DEBUG: Arquivo ${file.name} adicionado com sucesso`);
        });
        
        if (validFiles > 0) {
            showAlert("success", `${validFiles} imagem(ns) adicionada(s) com sucesso!`);
        }
        
        console.log(`DEBUG: Total de arquivos selecionados: ${selectedFiles.length}`);
        
        updateFileInput();
        updatePhotosSummary();
    }
    
    function createPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const col = document.createElement("div");
            col.className = "col-md-6 col-lg-4";
            col.setAttribute("data-filename", file.name);
            
            // Criar elementos programaticamente para evitar problemas com aspas
            const previewDiv = document.createElement("div");
            previewDiv.className = "preview-image";
            
            const img = document.createElement("img");
            img.src = e.target.result;
            img.alt = "Preview";
            
            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.className = "remove-btn";
            removeBtn.innerHTML = \'<i class="bi bi-x"></i>\';
            removeBtn.onclick = function() {
                removePreview(this, file.name);
            };
            
            const infoDiv = document.createElement("div");
            infoDiv.className = "preview-info";
            
            const nameDiv = document.createElement("div");
            nameDiv.className = "preview-name";
            nameDiv.textContent = file.name;
            
            const sizeDiv = document.createElement("div");
            sizeDiv.className = "preview-size";
            sizeDiv.textContent = formatFileSize(file.size);
            
            infoDiv.appendChild(nameDiv);
            infoDiv.appendChild(sizeDiv);
            
            previewDiv.appendChild(img);
            previewDiv.appendChild(removeBtn);
            previewDiv.appendChild(infoDiv);
            
            col.appendChild(previewDiv);
            previewContainer.appendChild(col);
            previewContainer.style.display = "block";
            
            console.log(`DEBUG: Preview criado para ${file.name}`);
        };
        reader.onerror = (error) => {
            console.error(`DEBUG: Erro ao ler arquivo ${file.name}:`, error);
            showAlert("error", `Erro ao processar "${file.name}"`);
        };
        reader.readAsDataURL(file);
    }
    
    window.removePreview = function(btn, fileName) {
        selectedFiles = selectedFiles.filter(f => f.name !== fileName);
        btn.closest(".col-md-6").remove();
        if (selectedFiles.length === 0) {
            previewContainer.style.display = "none";
        }
        updateFileInput();
        updatePhotosSummary();
        showAlert("success", "Imagem removida com sucesso!");
    };
    
    function updateFileInput() {
        try {
            // Usar DataTransfer para atualizar o input com os arquivos selecionados
            const dt = new DataTransfer();
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            imageInput.files = dt.files;
            console.log(`DEBUG: Input atualizado com ${imageInput.files.length} arquivo(s)`);
        } catch (error) {
            console.error("DEBUG: Erro ao atualizar input de arquivos:", error);
            // Fallback: alguns navegadores não suportam DataTransfer
            // Neste caso, os arquivos ainda estão em selectedFiles e serão enviados via FormData
        }
    }
    
    function updatePhotosSummary() {
        updateSummaryItem("summary-fotos", selectedFiles.length > 0, 
            selectedFiles.length + " anexada" + (selectedFiles.length !== 1 ? "s" : ""), 
            selectedFiles.length > 0);
    }
    
    // Inicializar
    updateWizardProgress();
    updateSummary();
    updatePhotosSummary();
    
    // Definir urgência inicial baseada no valor do campo hidden
    const urgenciaInicial = document.getElementById("urgenciaHidden").value;
    document.querySelector(`[data-value="${urgenciaInicial}"]`).classList.add("active");
    
    // ===== CONFIGURAÇÃO E INICIALIZAÇÃO DO MODAL DE ENDEREÇO =====
    
    // Buscar CEP no modal (usando ViaCEP diretamente - igual a /cliente/perfil/enderecos)
    const btnBuscarCepModal = document.getElementById("btnBuscarCepModal");
    const cepModalInput = document.getElementById("cepModal");
    
    if (btnBuscarCepModal && cepModalInput) {
        btnBuscarCepModal.addEventListener("click", buscarCepModal);
        cepModalInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                buscarCepModal();
            }
        });
        
        // Formatação automática do CEP
        cepModalInput.addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 5) {
                value = value.substring(0, 5) + "-" + value.substring(5, 8);
            }
            e.target.value = value;
            
            // Limpar status ao digitar
            const status = document.getElementById("cepStatusModal");
            if (status) status.textContent = "";
        });
    }
    
    async function buscarCepModal() {
        const cepInput = document.getElementById("cepModal");
        const btnBuscar = document.getElementById("btnBuscarCepModal");
        const status = document.getElementById("cepStatusModal");
        
        const cep = cepInput.value.replace(/\D/g, "");
        
        if (cep.length !== 8) {
            showCepStatus("CEP deve ter 8 dígitos", "danger");
            return;
        }
        
        // Loading state
        showCepStatus("Buscando endereço...", "primary");
        btnBuscar.disabled = true;
        btnBuscar.innerHTML = \'<div class="spinner-border spinner-border-sm"></div>\';
        
        try {
            // Buscar direto na API ViaCEP (mesma forma que funciona em /cliente/perfil/enderecos)
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                showCepStatus("CEP não encontrado", "warning");
            } else {
                // Preencher campos
                document.getElementById("logradouroModal").value = data.logradouro || "";
                document.getElementById("bairroModal").value = data.bairro || "";
                document.getElementById("cidadeModal").value = data.localidade || "";
                document.getElementById("estadoModal").value = data.uf || "";
                
                showCepStatus("Endereço preenchido automaticamente!", "success");
                document.getElementById("numeroModal").focus();
            }
        } catch (error) {
            console.error("Erro:", error);
            showCepStatus("Erro ao buscar CEP. Verifique sua conexão.", "danger");
        } finally {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = \'<i class="bi bi-search"></i>\';
        }
    }
    
    function showCepStatus(message, type) {
        const status = document.getElementById("cepStatusModal");
        if (status) {
            status.textContent = message;
            status.className = `text-${type} small mt-1`;
        }
    }
    
    // Submissão do formulário de endereço (usando o mesmo endpoint que /cliente/perfil/enderecos)
    const formEnderecoModal = document.getElementById("formEnderecoModal");
    if (formEnderecoModal) {
        formEnderecoModal.addEventListener("submit", async function(e) {
            e.preventDefault();
            
            const btnSalvar = document.getElementById("btnSalvarEndereco");
            const alertModal = document.getElementById("alertModal");
            
            // Validar formulário
            const requiredFields = [
                { name: "cep", label: "CEP" },
                { name: "logradouro", label: "Logradouro" },
                { name: "numero", label: "Número" },
                { name: "bairro", label: "Bairro" },
                { name: "cidade", label: "Cidade" },
                { name: "estado", label: "Estado" }
            ];
            
            let isValid = true;
            const errors = [];
            
            requiredFields.forEach(field => {
                const input = formEnderecoModal.querySelector(`input[name="${field.name}"]`);
                if (!input || !input.value.trim()) {
                    errors.push(field.label);
                    if (input) input.classList.add("is-invalid");
                    isValid = false;
                } else {
                    if (input) input.classList.remove("is-invalid");
                }
            });
            
            // Validações específicas
            const cep = formEnderecoModal.querySelector("input[name=cep]").value.replace(/\D/g, "");
            if (cep.length !== 8) {
                errors.push("CEP deve ter 8 dígitos");
                isValid = false;
            }
            
            const estado = formEnderecoModal.querySelector("input[name=estado]").value.trim();
            if (estado.length !== 2) {
                errors.push("Estado deve ter 2 caracteres");
                isValid = false;
            }
            
            if (!isValid) {
                showModalAlert("danger", `Preencha os campos obrigatórios: ${errors.join(", ")}`);
                return;
            }
            
            // Loading state
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = \'<i class="spinner-border spinner-border-sm me-2"></i>Salvando...\';
            alertModal.style.display = "none";
            
            try {
                const formData = new FormData(formEnderecoModal);
                
                // DEBUG: Mostrar dados sendo enviados
                console.log("=== DEBUG: Dados do Formulário ===");
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
// Submeter para a mesma rota que funciona na página de endereços
' . "\n" . '             const urlDestino = ' . json_encode(url("cliente/perfil/enderecos")) . ';' . "\n" . '

const response = await fetch(urlDestino, {
    method: "POST",
    body: formData,
    headers: {
        "X-Requested-With": "XMLHttpRequest"
    }
});
                
                console.log("DEBUG: Response status:", response.status);
                console.log("DEBUG: Response headers:", response.headers.get("content-type"));
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("DEBUG: Erro HTTP", response.status, errorText.substring(0, 500));
                    throw new Error(`Erro ${response.status}: Não foi possível cadastrar o endereço`);
                }
                
                const responseText = await response.text();
                console.log("DEBUG: Response (primeiros 200 chars):", responseText.substring(0, 200));
                
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log("DEBUG: JSON parsed:", data);
                } catch (parseError) {
                    console.error("DEBUG: Erro ao parsear JSON:", parseError);
                    console.error("DEBUG: Response completo:", responseText);
                    throw new Error("Resposta inválida do servidor");
                }
                
                if (data.sucesso) {
                    showModalAlert("success", data.mensagem);
                    
                    if (data.endereco) {
                        const select = document.getElementById("endereco_id");
                        const option = document.createElement("option");
                        option.value = data.endereco.id;
                        option.selected = true;
                        
                        const texto = `${data.endereco.logradouro}, ${data.endereco.numero} - ${data.endereco.cidade}/${data.endereco.estado}`;
                        option.textContent = data.endereco.principal ? `${texto} (Principal)` : texto;
                        
                        select.appendChild(option);
                        select.classList.add("border-success");
                        setTimeout(() => select.classList.remove("border-success"), 3000);
                        
                        updateSummary();
                    }
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById("modalEndereco"));
                        if (modal) modal.hide();
                        formEnderecoModal.reset();
                    }, 2000);
                } else {
                    showModalAlert("danger", data.mensagem || "Erro ao salvar endereço");
                }
            } catch (error) {
                console.error("Erro:", error);
                showModalAlert("danger", `Erro ao salvar endereço: ${error.message}`);
            } finally {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = \'<i class="bi bi-save me-2"></i>Salvar Endereço\';
            }
        });
    }
    
    function showModalAlert(type, message) {
        const alertModal = document.getElementById("alertModal");
        if (alertModal) {
            alertModal.className = `alert alert-${type} alert-dismissible fade show`;
            alertModal.innerHTML = `
                <i class="bi bi-${type === "success" ? "check-circle" : "exclamation-triangle"} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertModal.style.display = "block";
        }
    }
    
    // Limpeza ao fechar modal
    const modalEndereco = document.getElementById("modalEndereco");
    if (modalEndereco) {
        modalEndereco.addEventListener("hidden.bs.modal", function() {
            formEnderecoModal.reset();
            document.getElementById("alertModal").style.display = "none";
            document.getElementById("cepStatusModal").textContent = "";
            formEnderecoModal.querySelectorAll(".is-invalid").forEach(el => el.classList.remove("is-invalid"));
        });
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>