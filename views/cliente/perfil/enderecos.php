<?php
$title = 'Meus Endereços - Cliente - ChamaServiço';
ob_start();

// CORREÇÃO: Incluir o model e buscar endereços
require_once 'models/Endereco.php';
$enderecoModel = new Endereco();
$clienteId = Session::getUserId();
$enderecos = $enderecoModel->buscarPorPessoa($clienteId);
?>

<div class="container">
    <!-- Cabeçalho da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-geo-alt me-2"></i>Meus Endereços</h2>
            <p class="text-muted">Gerencie os endereços onde você pode receber serviços</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/chamaservico/cliente/perfil/editar" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar para Editar Perfil
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                <i class="bi bi-plus-circle me-1"></i>Novo Endereço
            </button>
        </div>
    </div>

    <!-- Container para alertas -->
    <div id="alertContainer"></div>

    <!-- Lista de endereços -->
    <div id="enderecosList">
        <?php if (empty($enderecos)): ?>
            <div class="text-center py-5" id="emptyState">
                <i class="bi bi-house" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="text-muted mt-3">Nenhum endereço cadastrado</h4>
                <p class="text-muted">Adicione um endereço para começar a solicitar serviços</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                    <i class="bi bi-plus-circle me-1"></i>Adicionar Primeiro Endereço
                </button>
            </div>
        <?php else: ?>
            <div class="row" id="enderecosGrid">
                <?php foreach ($enderecos as $endereco): ?>
                    <div class="col-md-6 col-lg-4 mb-4" id="endereco-<?= $endereco['id'] ?>">
                        <div class="card h-100 <?= $endereco['principal'] ? 'border-primary' : '' ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <?php if ($endereco['principal']): ?>
                                    <span class="badge bg-primary"><i class="bi bi-star me-1"></i>Principal</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Secundário</span>
                                <?php endif; ?>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" onclick="editarEndereco(<?= htmlspecialchars(json_encode($endereco)) ?>)">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                        </li>
                                        <?php if (!$endereco['principal']): ?>
                                            <li>
                                                <button class="dropdown-item" onclick="definirPrincipal(<?= $endereco['id'] ?>)">
                                                    <i class="bi bi-star me-1"></i>Definir como Principal
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="excluirEndereco(<?= $endereco['id'] ?>)">
                                                <i class="bi bi-trash me-1"></i>Excluir
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <address class="mb-0">
                                    <strong><?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?></strong><br>
                                    <?php if ($endereco['complemento']): ?>
                                        <?= htmlspecialchars($endereco['complemento']) ?><br>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($endereco['bairro']) ?><br>
                                    <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?><br>
                                    <small class="text-muted">CEP: <?= htmlspecialchars($endereco['cep']) ?></small>
                                </address>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Adicionar/Editar Endereço -->
<div class="modal fade" id="modalEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Novo Endereço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEndereco">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" id="acaoEndereco" value="adicionar">
                    <input type="hidden" name="endereco_id" id="enderecoId" value="">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cep" class="form-label">CEP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cep" name="cep" required maxlength="10" 
                                   placeholder="00000-000">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="logradouro" class="form-label">Logradouro <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" required 
                                   placeholder="Rua, Avenida, etc.">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="numero" class="form-label">Número <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero" name="numero" required 
                                   placeholder="123">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento" 
                                   placeholder="Apto, Casa, Bloco, etc.">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="bairro" class="form-label">Bairro <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bairro" name="bairro" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="estado" class="form-label">UF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="estado" name="estado" required maxlength="2" 
                                   style="text-transform: uppercase;">
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="principal" name="principal">
                        <label class="form-check-label" for="principal">
                            <i class="bi bi-star me-1"></i>Definir como endereço principal
                        </label>
                        <small class="form-text text-muted d-block">
                            O endereço principal será usado como padrão nas suas solicitações
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar">
                        <i class="bi bi-check-lg me-1"></i>Salvar Endereço
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Definir Principal -->
<div class="modal fade" id="modalDefinirPrincipal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Definir Endereço Principal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja definir este endereço como principal?</p>
                <p class="text-muted small">O endereço principal atual será alterado automaticamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDefinirPrincipal" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="definir_principal">
                    <input type="hidden" name="endereco_id" id="enderecoIdPrincipal">
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir Endereço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este endereço?</p>
                <p class="text-danger small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formExcluirEndereco" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="endereco_id" id="enderecoIdExcluir">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
// Máscara para CEP
function mascaraCEP(input) {
    let value = input.value.replace(/\D/g, "");
    value = value.replace(/(\d{5})(\d)/, "$1-$2");
    input.value = value;
}

// Aplicar máscara no CEP
document.getElementById("cep").addEventListener("input", function(e) {
    mascaraCEP(e.target);
});

// Buscar CEP automaticamente
document.getElementById("cep").addEventListener("blur", function() {
    const cep = this.value.replace(/\D/g, "");
    if (cep.length === 8) {
        buscarCEP(cep);
    }
});

function buscarCEP(cep) {
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            if (!data.erro) {
                document.getElementById("logradouro").value = data.logradouro || "";
                document.getElementById("bairro").value = data.bairro || "";
                document.getElementById("cidade").value = data.localidade || "";
                document.getElementById("estado").value = data.uf || "";
            } else {
                mostrarAlerta("CEP não encontrado", "warning");
            }
        })
        .catch(error => {
            console.error("Erro ao buscar CEP:", error);
            mostrarAlerta("Erro ao consultar CEP", "danger");
        });
}

// Submissão do formulário principal via AJAX
document.getElementById("formEndereco").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btnSalvar = document.getElementById("btnSalvar");
    
    // Desabilitar botão
    btnSalvar.disabled = true;
    btnSalvar.innerHTML = "<i class=\\"bi bi-hourglass-split me-1\\"></i>Salvando...";
    
    fetch("/chamaservico/cliente/perfil/enderecos", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarAlerta(data.mensagem, "success");
            
            // Fechar modal e recarregar página após delay
            bootstrap.Modal.getInstance(document.getElementById("modalEndereco")).hide();
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarAlerta(data.mensagem || "Erro ao salvar endereço", "danger");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarAlerta("Erro interno do servidor", "danger");
    })
    .finally(() => {
        // Reabilitar botão
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = "<i class=\\"bi bi-check-lg me-1\\"></i>Salvar Endereço";
    });
});

// Submissão do formulário de definir principal
document.getElementById("formDefinirPrincipal").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("/chamaservico/cliente/perfil/enderecos", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarAlerta(data.mensagem, "success");
            bootstrap.Modal.getInstance(document.getElementById("modalDefinirPrincipal")).hide();
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarAlerta(data.mensagem || "Erro ao definir endereço principal", "danger");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarAlerta("Erro interno do servidor", "danger");
    });
});

// Submissão do formulário de exclusão
document.getElementById("formExcluirEndereco").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("/chamaservico/cliente/perfil/enderecos", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarAlerta(data.mensagem, "success");
            bootstrap.Modal.getInstance(document.getElementById("modalExcluir")).hide();
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarAlerta(data.mensagem || "Erro ao excluir endereço", "danger");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarAlerta("Erro interno do servidor", "danger");
    });
});

function mostrarAlerta(message, type) {
    const alertContainer = document.getElementById("alertContainer");
    const alertId = "alert-" + Date.now();
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="${alertId}">
            <i class="bi bi-${type === "success" ? "check-circle" : (type === "warning" ? "exclamation-triangle" : "x-circle")} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML("beforeend", alertHTML);
    
    // Auto remover após 5 segundos
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function editarEndereco(endereco) {
    document.getElementById("modalTitle").textContent = "Editar Endereço";
    document.getElementById("acaoEndereco").value = "editar";
    document.getElementById("enderecoId").value = endereco.id;
    document.getElementById("cep").value = endereco.cep;
    document.getElementById("logradouro").value = endereco.logradouro;
    document.getElementById("numero").value = endereco.numero;
    document.getElementById("complemento").value = endereco.complemento || "";
    document.getElementById("bairro").value = endereco.bairro;
    document.getElementById("cidade").value = endereco.cidade;
    document.getElementById("estado").value = endereco.estado;
    document.getElementById("principal").checked = endereco.principal == 1;
    
    new bootstrap.Modal(document.getElementById("modalEndereco")).show();
}

function definirPrincipal(id) {
    document.getElementById("enderecoIdPrincipal").value = id;
    new bootstrap.Modal(document.getElementById("modalDefinirPrincipal")).show();
}

function excluirEndereco(id) {
    document.getElementById("enderecoIdExcluir").value = id;
    new bootstrap.Modal(document.getElementById("modalExcluir")).show();
}

// Reset modal ao fechar
document.getElementById("modalEndereco").addEventListener("hidden.bs.modal", function() {
    document.getElementById("formEndereco").reset();
    document.getElementById("modalTitle").textContent = "Novo Endereço";
    document.getElementById("acaoEndereco").value = "adicionar";
    document.getElementById("enderecoId").value = "";
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>