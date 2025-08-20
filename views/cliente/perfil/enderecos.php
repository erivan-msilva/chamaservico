<?php
$title = 'Gerenciar Endereços - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-geo-alt me-2"></i>Gerenciar Endereços</h2>
            <div>
                <a href="/chamaservico/cliente/perfil" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Voltar ao Perfil
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                    <i class="bi bi-plus-circle me-1"></i>Novo Endereço
                </button>
            </div>
        </div>

        <!-- Container para mensagens dinâmicas -->
        <div id="alertContainer"></div>

        <div id="enderecosList">
            <?php if (empty($enderecos)): ?>
                <div class="text-center py-5" id="emptyState">
                    <i class="bi bi-house" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="text-muted mt-3">Nenhum endereço cadastrado</h4>
                    <p class="text-muted">Adicione seu primeiro endereço para começar a solicitar serviços.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Endereço
                    </button>
                </div>
            <?php else: ?>
                <div class="row" id="enderecosGrid">
                    <?php foreach ($enderecos as $endereco): ?>
                        <div class="col-md-6 col-lg-4 mb-4" id="endereco-<?= $endereco['id'] ?>">
                            <!-- Card do Endereço (código consolidado) -->
                            <div class="card h-100 <?= $endereco['principal'] ? 'border-primary' : '' ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <?php if ($endereco['principal']): ?>
                                        <span class="badge bg-primary">
                                            <i class="bi bi-star me-1"></i>Principal
                                        </span>
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
</div>

<!-- Modal Adicionar/Editar Endereço -->
<div class="modal fade" id="modalEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span id="modalTitle">Novo Endereço</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEndereco">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="adicionar" id="acaoEndereco">
                    <input type="hidden" name="endereco_id" id="enderecoId">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cep" class="form-label">CEP *</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   placeholder="00000-000" maxlength="9" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="logradouro" class="form-label">Logradouro *</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" 
                                   placeholder="Rua, Avenida, etc." required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text" class="form-control" id="numero" name="numero" 
                                   placeholder="123" required>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento" 
                                   placeholder="Apartamento, Casa, etc.">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="bairro" class="form-label">Bairro *</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" 
                                   placeholder="Centro" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cidade" class="form-label">Cidade *</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                   placeholder="São Paulo" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">UF</option>
                                <option value="AC">AC</option>
                                <option value="AL">AL</option>
                                <option value="AP">AP</option>
                                <option value="AM">AM</option>
                                <option value="BA">BA</option>
                                <option value="CE">CE</option>
                                <option value="DF">DF</option>
                                <option value="ES">ES</option>
                                <option value="GO">GO</option>
                                <option value="MA">MA</option>
                                <option value="MT">MT</option>
                                <option value="MS">MS</option>
                                <option value="MG">MG</option>
                                <option value="PA">PA</option>
                                <option value="PB">PB</option>
                                <option value="PR">PR</option>
                                <option value="PE">PE</option>
                                <option value="PI">PI</option>
                                <option value="RJ">RJ</option>
                                <option value="RN">RN</option>
                                <option value="RS">RS</option>
                                <option value="RO">RO</option>
                                <option value="RR">RR</option>
                                <option value="SC">SC</option>
                                <option value="SP">SP</option>
                                <option value="SE">SE</option>
                                <option value="TO">TO</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="principal" name="principal" value="1">
                        <label class="form-check-label" for="principal">
                            Definir como endereço principal
                        </label>
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

<!-- Modais de ação -->
<div class="modal fade" id="modalDefinirPrincipal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Definir Endereço Principal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja definir este endereço como principal?</p>
                <p><small class="text-muted">O endereço principal anterior será alterado para secundário.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;" id="formDefinirPrincipal">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="definir_principal">
                    <input type="hidden" name="endereco_id" id="enderecoIdPrincipal">
                    <button type="submit" class="btn btn-primary">Definir como Principal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir Endereço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este endereço?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;" id="formExcluirEndereco">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="endereco_id" id="enderecoIdExcluir">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
let enderecoIdAtual = null;

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

// Buscar CEP
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
            }
        })
        .catch(error => {
            console.error("Erro ao buscar CEP:", error);
        });
}

// Submissão do formulário principal via AJAX (adicionar/editar)
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
        if (data.success) {
            mostrarAlerta(data.message, "success");
            
            // Se é um novo endereço, adicionar à lista
            if (document.getElementById("acaoEndereco").value === "adicionar") {
                adicionarEnderecoNaLista(data.endereco);
            } else {
                // Se é edição, atualizar o card existente
                atualizarEnderecoNaLista(data.endereco);
            }
            
            // Fechar modal e resetar formulário
            bootstrap.Modal.getInstance(document.getElementById("modalEndereco")).hide();
            document.getElementById("formEndereco").reset();
        } else {
            mostrarAlerta(data.message, "danger");
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

// Submissão do formulário de definir principal via AJAX
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
        if (data.success) {
            mostrarAlerta(data.message, "success");
            
            if (data.enderecos) {
                // Atualizar todos os cards com os dados atualizados
                data.enderecos.forEach(endereco => {
                    atualizarEnderecoNaLista(endereco);
                });
            }
            
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById("modalDefinirPrincipal")).hide();
        } else {
            mostrarAlerta(data.message, "danger");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarAlerta("Erro interno do servidor", "danger");
    });
});

// Submissão do formulário de exclusão via AJAX
document.getElementById("formExcluirEndereco").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const enderecoId = document.getElementById("enderecoIdExcluir").value;
    
    fetch("/chamaservico/cliente/perfil/enderecos", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta(data.message, "success");
            
            // Remover o card da lista
            const card = document.getElementById(`endereco-${enderecoId}`);
            if (card) {
                card.style.transition = "all 0.3s ease-out";
                card.style.opacity = "0";
                card.style.transform = "translateX(-100%)";
                
                setTimeout(() => {
                    card.remove();
                    
                    // Verificar se não há mais endereços
                    const enderecosGrid = document.getElementById("enderecosGrid");
                    if (enderecosGrid && enderecosGrid.children.length === 0) {
                        location.reload(); // Recarregar para mostrar estado vazio
                    }
                }, 300);
            }
            
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById("modalExcluir")).hide();
        } else {
            mostrarAlerta(data.message, "danger");
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
            <i class="bi bi-${type === "success" ? "check-circle" : "exclamation-triangle"} me-2"></i>${message}
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

function adicionarEnderecoNaLista(endereco) {
    // Remover estado vazio se existir
    const emptyState = document.getElementById("emptyState");
    if (emptyState) {
        emptyState.remove();
    }
    
    // Criar ou obter o grid de endereços
    let enderecosGrid = document.getElementById("enderecosGrid");
    if (!enderecosGrid) {
        enderecosGrid = document.createElement("div");
        enderecosGrid.className = "row";
        enderecosGrid.id = "enderecosGrid";
        document.getElementById("enderecosList").appendChild(enderecosGrid);
    }
    
    // Criar o card do endereço
    const enderecoCard = criarCardEndereco(endereco);
    enderecosGrid.insertAdjacentHTML("afterbegin", enderecoCard);
    
    // Animar a entrada
    const novoCard = document.getElementById(`endereco-${endereco.id}`);
    novoCard.style.opacity = "0";
    novoCard.style.transform = "translateY(-20px)";
    
    setTimeout(() => {
        novoCard.style.transition = "all 0.3s ease-in";
        novoCard.style.opacity = "1";
        novoCard.style.transform = "translateY(0)";
    }, 100);
}

function atualizarEnderecoNaLista(endereco) {
    const cardExistente = document.getElementById(`endereco-${endereco.id}`);
    if (cardExistente) {
        cardExistente.outerHTML = `<div class="col-md-6 col-lg-4 mb-4" id="endereco-${endereco.id}">${criarCardEndereco(endereco, false)}</div>`;
    }
}

function criarCardEndereco(endereco, incluirDiv = true) {
    const isPrincipal = endereco.principal == 1;
    const cardContent = `
        <div class="card h-100 ${isPrincipal ? "border-primary" : ""}">
            <div class="card-header d-flex justify-content-between align-items-center">
                ${isPrincipal ? 
                    \'<span class="badge bg-primary"><i class="bi bi-star me-1"></i>Principal</span>\' : 
                    \'<span class="badge bg-secondary">Secundário</span>\'
                }
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button class="dropdown-item" onclick="editarEndereco(${JSON.stringify(endereco).replace(/"/g, "&quot;")})">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </button>
                        </li>
                        ${!isPrincipal ? `
                        <li>
                            <button class="dropdown-item" onclick="definirPrincipal(${endereco.id})">
                                <i class="bi bi-star me-1"></i>Definir como Principal
                            </button>
                        </li>` : ""}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-danger" onclick="excluirEndereco(${endereco.id})">
                                <i class="bi bi-trash me-1"></i>Excluir
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <strong>${endereco.logradouro}, ${endereco.numero}</strong><br>
                    ${endereco.complemento ? endereco.complemento + "<br>" : ""}
                    ${endereco.bairro}<br>
                    ${endereco.cidade} - ${endereco.estado}<br>
                    <small class="text-muted">CEP: ${endereco.cep}</small>
                </address>
            </div>
        </div>
    `;
    
    return incluirDiv ? `<div class="col-md-6 col-lg-4 mb-4" id="endereco-${endereco.id}">${cardContent}</div>` : cardContent;
}

function editarEndereco(endereco) {
    document.getElementById("modalTitle").textContent = "Editar Endereço";
    document.getElementById("acaoEndereco").value = "editar";
    document.getElementById("enderecoId").value = endereco.id;
    document.getElementById("cep").value = endereco.cep.replace(/(\d{5})(\d)/, "$1-$2");
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