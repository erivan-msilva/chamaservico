<?php
$title = 'Meus Endereços - Prestador - ChamaServiço';
ob_start();

// Buscar endereços do prestador
require_once 'models/Endereco.php';
$enderecoModel = new Endereco();
$prestadorId = Session::getUserId();
$enderecos = $enderecoModel->buscarPorPessoa($prestadorId);
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
                    <a href="/chamaservico/prestador/perfil" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao Perfil
                    </a>
                </div>
            </div>

            <!-- Dica informativa -->
            <div class="alert alert-info mb-4">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Dica:</strong> Mantenha seus endereços de atendimento atualizados para receber mais oportunidades de trabalho na sua região.
            </div>

            <?php if (empty($enderecos)): ?>
                <!-- Estado vazio -->
                <div class="text-center py-5">
                    <i class="bi bi-geo-alt text-muted" style="font-size: 5rem;"></i>
                    <h4 class="text-muted mt-3">Nenhum endereço cadastrado</h4>
                    <p class="text-muted">Adicione endereços onde você pode atender clientes para aparecer nas buscas por região.</p>
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
                                                <i class="bi bi-geo me-1"></i>Área de Atendimento
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

<!-- Modal Novo Endereço -->
<div class="modal fade" id="modalNovoEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Adicionar Novo Endereço
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoEndereco">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="adicionar">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="cep" class="form-label fw-semibold">CEP *</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   placeholder="00000-000" maxlength="9" required>
                            <div class="form-text">Digite o CEP para preenchimento automático</div>
                        </div>
                        <div class="col-md-8">
                            <label for="logradouro" class="form-label fw-semibold">Logradouro *</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="numero" class="form-label fw-semibold">Número *</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                        </div>
                        <div class="col-md-9">
                            <label for="complemento" class="form-label fw-semibold">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento" 
                                   placeholder="Apartamento, sala, loja, etc.">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="bairro" class="form-label fw-semibold">Bairro *</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" required>
                        </div>
                        <div class="col-md-5">
                            <label for="cidade" class="form-label fw-semibold">Cidade *</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label fw-semibold">UF *</label>
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
                        <label class="form-check-label fw-semibold" for="principal">
                            <i class="bi bi-star me-1"></i>Definir como endereço principal
                        </label>
                        <div class="form-text">O endereço principal é usado como referência para cálculos de distância.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="salvarEndereco()" id="btnSalvarEndereco">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <i class="bi bi-check-lg me-1"></i>Salvar Endereço
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Endereço -->
<div class="modal fade" id="modalEditarEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Editar Endereço
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarEndereco">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="endereco_id" id="editEnderecoId">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="editCep" class="form-label fw-semibold">CEP *</label>
                            <input type="text" class="form-control" id="editCep" name="cep" 
                                   placeholder="00000-000" maxlength="9" required>
                        </div>
                        <div class="col-md-8">
                            <label for="editLogradouro" class="form-label fw-semibold">Logradouro *</label>
                            <input type="text" class="form-control" id="editLogradouro" name="logradouro" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="editNumero" class="form-label fw-semibold">Número *</label>
                            <input type="text" class="form-control" id="editNumero" name="numero" required>
                        </div>
                        <div class="col-md-9">
                            <label for="editComplemento" class="form-label fw-semibold">Complemento</label>
                            <input type="text" class="form-control" id="editComplemento" name="complemento" 
                                   placeholder="Apartamento, sala, loja, etc.">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="editBairro" class="form-label fw-semibold">Bairro *</label>
                            <input type="text" class="form-control" id="editBairro" name="bairro" required>
                        </div>
                        <div class="col-md-5">
                            <label for="editCidade" class="form-label fw-semibold">Cidade *</label>
                            <input type="text" class="form-control" id="editCidade" name="cidade" required>
                        </div>
                        <div class="col-md-2">
                            <label for="editEstado" class="form-label fw-semibold">UF *</label>
                            <select class="form-select" id="editEstado" name="estado" required>
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
                        <input class="form-check-input" type="checkbox" id="editPrincipal" name="principal" value="1">
                        <label class="form-check-label fw-semibold" for="editPrincipal">
                            <i class="bi bi-star me-1"></i>Definir como endereço principal
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicaoEndereco()" id="btnEditarEndereco">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="modalExcluirEndereco" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este endereço?</p>
                <p class="text-danger">
                    <small><i class="bi bi-info-circle me-1"></i>Esta ação não pode ser desfeita.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoEndereco()" id="btnConfirmarExclusao">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <i class="bi bi-trash me-1"></i>Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>

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
$scripts = '
<script>
let enderecoIdParaExcluir = null;

// Buscar CEP
async function buscarCEP(cep, prefixo = "") {
    const cepLimpo = cep.replace(/\D/g, "");
    if (cepLimpo.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                document.getElementById(prefixo + "logradouro").value = data.logradouro || "";
                document.getElementById(prefixo + "bairro").value = data.bairro || "";
                document.getElementById(prefixo + "cidade").value = data.localidade || "";
                document.getElementById(prefixo + "estado").value = data.uf || "";
            } else {
                alert("CEP não encontrado!");
            }
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
            alert("Erro ao buscar CEP. Tente novamente.");
        }
    }
}

// Máscara de CEP
function mascaraCEP(input) {
    let value = input.value.replace(/\D/g, "");
    value = value.replace(/^(\d{5})(\d)/, "$1-$2");
    input.value = value;
}

// Salvar novo endereço
async function salvarEndereco() {
    const form = document.getElementById("formNovoEndereco");
    const formData = new FormData(form);
    const btn = document.getElementById("btnSalvarEndereco");
    const spinner = btn.querySelector(".spinner-border");
    
    // Mostrar loading
    spinner.classList.remove("d-none");
    btn.disabled = true;
    
    try {
        const response = await fetch("/chamaservico/prestador/perfil/enderecos", {
            method: "POST",
            body: formData
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("modalNovoEndereco")).hide();
            location.reload();
        } else {
            alert("Erro ao salvar endereço. Tente novamente.");
        }
    } catch (error) {
        console.error("Erro:", error);
        alert("Erro ao salvar endereço. Tente novamente.");
    } finally {
        spinner.classList.add("d-none");
        btn.disabled = false;
    }
}

// Editar endereço
async function editarEndereco(id) {
    try {
        const response = await fetch(`/chamaservico/prestador/perfil/enderecos?acao=obter&id=${id}`);
        const endereco = await response.json();
        
        if (endereco) {
            document.getElementById("editEnderecoId").value = endereco.id;
            document.getElementById("editCep").value = endereco.cep;
            document.getElementById("editLogradouro").value = endereco.logradouro;
            document.getElementById("editNumero").value = endereco.numero;
            document.getElementById("editComplemento").value = endereco.complemento || "";
            document.getElementById("editBairro").value = endereco.bairro;
            document.getElementById("editCidade").value = endereco.cidade;
            document.getElementById("editEstado").value = endereco.estado;
            document.getElementById("editPrincipal").checked = endereco.principal == 1;
            
            new bootstrap.Modal(document.getElementById("modalEditarEndereco")).show();
        }
    } catch (error) {
        console.error("Erro:", error);
        alert("Erro ao carregar dados do endereço.");
    }
}

// Salvar edição do endereço
async function salvarEdicaoEndereco() {
    const form = document.getElementById("formEditarEndereco");
    const formData = new FormData(form);
    const btn = document.getElementById("btnEditarEndereco");
    const spinner = btn.querySelector(".spinner-border");
    
    spinner.classList.remove("d-none");
    btn.disabled = true;
    
    try {
        const response = await fetch("/chamaservico/prestador/perfil/enderecos", {
            method: "POST",
            body: formData
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("modalEditarEndereco")).hide();
            location.reload();
        } else {
            alert("Erro ao salvar alterações. Tente novamente.");
        }
    } catch (error) {
        console.error("Erro:", error);
        alert("Erro ao salvar alterações. Tente novamente.");
    } finally {
        spinner.classList.add("d-none");
        btn.disabled = false;
    }
}

// Definir como principal
async function definirPrincipal(id) {
    if (confirm("Definir este endereço como principal?")) {
        try {
            const formData = new FormData();
            formData.append("csrf_token", "<?= Session::generateCSRFToken() ?>");
            formData.append("acao", "definir_principal");
            formData.append("endereco_id", id);
            
            const response = await fetch("/chamaservico/prestador/perfil/enderecos", {
                method: "POST",
                body: formData
            });
            
            if (response.ok) {
                location.reload();
            } else {
                alert("Erro ao definir endereço principal.");
            }
        } catch (error) {
            console.error("Erro:", error);
            alert("Erro ao definir endereço principal.");
        }
    }
}

// Excluir endereço
function excluirEndereco(id) {
    enderecoIdParaExcluir = id;
    new bootstrap.Modal(document.getElementById("modalExcluirEndereco")).show();
}

// Confirmar exclusão
async function confirmarExclusaoEndereco() {
    if (!enderecoIdParaExcluir) return;
    
    const btn = document.getElementById("btnConfirmarExclusao");
    const spinner = btn.querySelector(".spinner-border");
    
    spinner.classList.remove("d-none");
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append("csrf_token", "<?= Session::generateCSRFToken() ?>");
        formData.append("acao", "excluir");
        formData.append("endereco_id", enderecoIdParaExcluir);
        
        const response = await fetch("/chamaservico/prestador/perfil/enderecos", {
            method: "POST",
            body: formData
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("modalExcluirEndereco")).hide();
            location.reload();
        } else {
            alert("Erro ao excluir endereço.");
        }
    } catch (error) {
        console.error("Erro:", error);
        alert("Erro ao excluir endereço.");
    } finally {
        spinner.classList.add("d-none");
        btn.disabled = false;
        enderecoIdParaExcluir = null;
    }
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
    // Máscaras de CEP
    document.getElementById("cep").addEventListener("input", function() {
        mascaraCEP(this);
        if (this.value.length === 9) {
            buscarCEP(this.value);
        }
    });
    
    document.getElementById("editCep").addEventListener("input", function() {
        mascaraCEP(this);
        if (this.value.length === 9) {
            buscarCEP(this.value, "edit");
        }
    });
    
    // Limpar formulários ao fechar modais
    document.getElementById("modalNovoEndereco").addEventListener("hidden.bs.modal", function() {
        document.getElementById("formNovoEndereco").reset();
    });
    
    document.getElementById("modalEditarEndereco").addEventListener("hidden.bs.modal", function() {
        document.getElementById("formEditarEndereco").reset();
    });
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
