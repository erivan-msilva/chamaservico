<?php
$title = 'Meus Endereços - Cliente - ChamaServiço';
ob_start();

// Buscar endereços do cliente
require_once 'models/Endereco.php';
$enderecoModel = new Endereco();
$clienteId = Session::getUserId();
$enderecos = $enderecoModel->buscarPorPessoa($clienteId);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">
                    <i class="bi bi-geo-alt me-2"></i>Meus Endereços de Atendimento
                </h2>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoEndereco">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Endereço
                    </button>
                    <a href="/chamaservico/cliente/perfil" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao Perfil
                    </a>
                </div>
            </div>

            <!-- Dica informativa -->
            <div class="alert alert-info mb-4">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Dica:</strong> Cadastre os endereços onde você precisa de serviços para facilitar suas solicitações.
            </div>

            <?php if (empty($enderecos)): ?>
                <!-- Estado vazio -->
                <div class="text-center py-5">
                    <i class="bi bi-geo-alt text-muted" style="font-size: 5rem;"></i>
                    <h4 class="text-muted mt-3">Nenhum endereço cadastrado</h4>
                    <p class="text-muted">Adicione endereços onde você precisa de serviços para agilizar suas solicitações.</p>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNovoEndereco">
                        <i class="bi bi-plus-circle me-2"></i>Adicionar Meu Primeiro Endereço
                    </button>
                </div>
            <?php else: ?>
                <!-- Lista de endereços -->
                <div class="row">
                    <?php foreach ($enderecos as $endereco): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 <?= $endereco['principal'] ? 'border-primary' : '' ?> hover-card">
                                <?php if ($endereco['principal']): ?>
                                    <div class="card-header bg-primary text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">
                                                <i class="bi bi-star-fill me-1"></i>Endereço Principal
                                            </span>
                                            <i class="bi bi-house-heart"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <address class="mb-3">
                                        <strong><?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?></strong><br>
                                        <?php if ($endereco['complemento']): ?>
                                            <?= htmlspecialchars($endereco['complemento']) ?><br>
                                        <?php endif; ?>
                                        <span class="text-primary"><?= htmlspecialchars($endereco['bairro']) ?></span><br>
                                        <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?><br>
                                        <small class="text-muted">CEP: <?= htmlspecialchars($endereco['cep']) ?></small>
                                    </address>

                                    <?php if (!$endereco['principal']): ?>
                                        <div class="mb-3">
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-geo me-1"></i>Local de Atendimento
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="editarEndereco(<?= $endereco['id'] ?>)">
                                            <i class="bi bi-pencil me-1"></i>Editar
                                        </button>
                                        <?php if (!$endereco['principal']): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="definirPrincipal(<?= $endereco['id'] ?>)">
                                                <i class="bi bi-star me-1"></i>Tornar Principal
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="excluirEndereco(<?= $endereco['id'] ?>)">
                                                <i class="bi bi-trash me-1"></i>Excluir
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumo dos endereços -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="h3 text-primary mb-0"><?= count($enderecos) ?></div>
                                <small class="text-muted">Endereço(s) Cadastrado(s)</small>
                            </div>
                            <div class="col-md-4">
                                <div class="h3 text-success mb-0">
                                    <?= count(array_filter($enderecos, function($e) { return $e['principal']; })) ?>
                                </div>
                                <small class="text-muted">Endereço Principal</small>
                            </div>
                            <div class="col-md-4">
                                <div class="h3 text-info mb-0">
                                    <?= count(array_unique(array_column($enderecos, 'cidade'))) ?>
                                </div>
                                <small class="text-muted">Cidade(s) de Atendimento</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Usar os mesmos modais da página do prestador -->
<!-- Modal Novo Endereço -->
<!-- Modal Editar Endereço -->  
<!-- Modal Confirmar Exclusão -->

<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-footer .btn {
    transition: all 0.2s;
}

.card-footer .btn:hover {
    transform: translateY(-1px);
}
</style>

<?php
// Mesmo JavaScript da página do prestador, mas com endpoint do cliente
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