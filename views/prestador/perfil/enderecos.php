<?php
$title = 'Meus Endereços - Prestador - ChamaServiço';
ob_start();

// Incluir o model e buscar endereços
require_once 'models/Endereco.php';
$enderecoModel = new Endereco();
$prestadorId = Session::getUserId();
$enderecos = $enderecoModel->buscarPorPessoa($prestadorId);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Container de alertas -->
            <div id="alertContainer" class="mb-3"></div>
            
            <!-- Header da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        Meus Endereços de Atendimento
                    </h2>
                    <p class="text-muted">Gerencie os locais onde você oferece seus serviços</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoEndereco">
                        <i class="bi bi-plus-circle me-1"></i>
                        Novo Endereço
                    </button>
                </div>
            </div>

            <!-- DEBUG: Mostrar dados carregados -->
            <?php if (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento'): ?>
                <div class="alert alert-info">
                    <strong>DEBUG:</strong>
                    Prestador ID: <?= Session::getUserId() ?> |
                    Endereços encontrados: <?= count($enderecos ?? []) ?> |
                    BASE_URL: <?= BASE_URL ?>
                </div>
            <?php endif; ?>

            <!-- Lista de endereços -->
            <div class="row" id="enderecosContainer">
                <?php if (empty($enderecos)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 text-muted">Nenhum endereço de atendimento cadastrado</h4>
                                <p class="text-muted">Adicione os locais onde você oferece seus serviços.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoEndereco">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Adicionar Primeiro Endereço
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($enderecos as $endereco): ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <?php if ($endereco['principal']): ?>
                                    <div class="card-header bg-primary text-white border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-star-fill me-2"></i>
                                            <strong>Endereço Principal</strong>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-house me-2 text-primary"></i>
                                        <?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?>
                                    </h6>

                                    <?php if ($endereco['complemento']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <?= htmlspecialchars($endereco['complemento']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-building me-1"></i>
                                        <?= htmlspecialchars($endereco['bairro']) ?>
                                    </p>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-geo me-1"></i>
                                        <?= htmlspecialchars($endereco['cidade']) ?>/<?= htmlspecialchars($endereco['estado']) ?>
                                    </p>

                                    <p class="text-muted small">
                                        <i class="bi bi-mailbox me-1"></i>
                                        CEP: <?= htmlspecialchars($endereco['cep']) ?>
                                    </p>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="d-grid gap-2">
                                        <?php if (!$endereco['principal']): ?>
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                onclick="definirPrincipal(<?= $endereco['id'] ?>)">
                                                <i class="bi bi-star me-1"></i>
                                                Definir como Principal
                                            </button>
                                        <?php endif; ?>

                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="excluirEndereco(<?= $endereco['id'] ?>)">
                                            <i class="bi bi-trash me-1"></i>
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Endereço (mesmo código do cliente) -->
<div class="modal fade" id="modalNovoEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formNovoEndereco">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    Novo Endereço de Atendimento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="acao" value="adicionar">

                <!-- Alerta de status -->
                <div id="alertModal" class="alert" style="display: none;"></div>

                <!-- CEP -->
                <div class="mb-3">
                    <label for="cep" class="form-label fw-bold">
                        <i class="bi bi-mailbox me-1"></i>
                        CEP *
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cep" name="cep" required
                               maxlength="9" pattern="\d{5}-?\d{3}" placeholder="00000-000">
                        <button type="button" class="btn btn-outline-info" id="btnBuscarCep">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div id="cepStatus" class="form-text"></div>
                </div>

                <!-- Logradouro e Número -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="logradouro" class="form-label fw-bold">Logradouro *</label>
                        <input type="text" class="form-control" id="logradouro" name="logradouro" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="numero" class="form-label fw-bold">Número *</label>
                        <input type="text" class="form-control" id="numero" name="numero" required>
                    </div>
                </div>

                <!-- Complemento e Bairro -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="complemento" class="form-label">Complemento</label>
                        <input type="text" class="form-control" id="complemento" name="complemento"
                               placeholder="Apto, casa, etc.">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bairro" class="form-label fw-bold">Bairro *</label>
                        <input type="text" class="form-control" id="bairro" name="bairro" required>
                    </div>
                </div>

                <!-- Cidade e Estado -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="cidade" class="form-label fw-bold">Cidade *</label>
                        <input type="text" class="form-control" id="cidade" name="cidade" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label fw-bold">Estado *</label>
                        <input type="text" class="form-control" id="estado" name="estado"
                               required maxlength="2" placeholder="SP" style="text-transform: uppercase;">
                    </div>
                </div>

                <!-- Principal -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="principal" id="principal" value="1">
                    <label class="form-check-label fw-bold" for="principal">
                        <i class="bi bi-star text-warning me-1"></i>
                        Definir como endereço principal
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSalvar">
                    <i class="bi bi-save me-2"></i>
                    Salvar Endereço
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmação de exclusão (Bootstrap 5) -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmarExclusaoLabel">
            <i class="bi bi-trash me-2"></i>Confirmar Exclusão
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja excluir este endereço? Esta ação não poderá ser desfeita.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoEndereco">Excluir</button>
      </div>
    </div>
  </div>
</div>

<script>
// CORREÇÃO: Mover funções para escopo global antes do DOMContentLoaded
let alertContainer;

// Funções globais (acessíveis pelos onclick do HTML)
async function definirPrincipal(enderecoId) {
    if (!confirm('Definir este endereço como principal?')) return;

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
        formData.append('acao', 'definir_principal');
        formData.append('endereco_id', enderecoId);

        const response = await fetch('<?= url("prestador/perfil/enderecos") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
        
        const data = await response.json();
        if (data.sucesso) {
            showGlobalAlert('success', data.mensagem || 'Endereço principal definido!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showGlobalAlert('danger', data.mensagem || 'Erro ao definir endereço principal');
        }
    } catch (error) {
        console.error('Erro:', error);
        showGlobalAlert('danger', 'Erro de conexão. Tente novamente.');
    }
}

async function excluirEndereco(enderecoId) {
    // Exibe modal Bootstrap antes de excluir
    window.enderecoParaExcluir = enderecoId;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    modal.show();
}

// Função chamada pelo botão de confirmação do modal
async function confirmarExclusaoEndereco() {
    if (!window.enderecoParaExcluir) return;
    await excluirEnderecoAjax(window.enderecoParaExcluir);
    window.enderecoParaExcluir = null;
    bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao')).hide();
}

// Função AJAX para excluir
async function excluirEnderecoAjax(enderecoId) {
    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
        formData.append('acao', 'excluir');
        formData.append('endereco_id', enderecoId);

        const response = await fetch('<?= url("prestador/perfil/enderecos") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);

        // Tenta garantir que é JSON
        let data;
        try {
            data = await response.json();
        } catch (e) {
            showGlobalAlert('danger', 'Erro de resposta do servidor. Tente novamente.');
            return;
        }

        // Fallback para diferentes chaves
        const sucesso = data.sucesso ?? data.success;
        const mensagem = data.mensagem ?? data.message ?? 'Erro ao excluir endereço';

        if (sucesso) {
            showGlobalAlert('success', mensagem || 'Endereço excluído com sucesso!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showGlobalAlert('danger', mensagem);
        }
    } catch (error) {
        console.error('Erro:', error);
        showGlobalAlert('danger', 'Erro de conexão. Tente novamente.');
    }
}

function showGlobalAlert(type, message) {
    if (!alertContainer) {
        alertContainer = document.getElementById('alertContainer');
    }
    
    if (alertContainer) {
        alertContainer.innerHTML = '';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Event listeners após DOM estar pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUG: Página de endereços prestador carregada ===');
    
    // Inicializar referência global
    alertContainer = document.getElementById('alertContainer');
    
    const formNovoEndereco = document.getElementById('formNovoEndereco');
    const btnSalvar = document.getElementById('btnSalvar');

    if (!formNovoEndereco || !btnSalvar) {
        console.error('Elementos do formulário não encontrados');
        return;
    }

    formNovoEndereco.addEventListener('submit', function(e) {
        e.preventDefault();
        salvarEndereco();
    });

    async function salvarEndereco() {
        // Validar campos obrigatórios
        const campos = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'];
        const camposInvalidos = campos.filter(campo => {
            const input = document.getElementById(campo);
            if (!input || !input.value.trim()) {
                input?.classList.add('is-invalid');
                return true;
            }
            input.classList.remove('is-invalid');
            return false;
        });

        if (camposInvalidos.length > 0) {
            showGlobalAlert('danger', 'Preencha todos os campos obrigatórios!');
            return;
        }

        // UI Loading
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Salvando...';

        try {
            const formData = new FormData(formNovoEndereco);
            const response = await fetch('<?= url("prestador/perfil/enderecos") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);

            const contentType = response.headers.get("content-type");
            if (!contentType?.includes("application/json")) {
                throw new Error("Resposta inválida do servidor");
            }

            const data = await response.json();

            if (data.sucesso) {
                showGlobalAlert('success', data.mensagem || 'Endereço salvo com sucesso!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showGlobalAlert('danger', data.mensagem || 'Erro ao salvar endereço');
            }
        } catch (error) {
            console.error('Erro:', error);
            showGlobalAlert('danger', 'Erro de conexão: ' + error.message);
        } finally {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-save me-2"></i>Salvar Endereço';
        }
    }

    // Máscara e validação de CEP
    const cepInput = document.getElementById("cep");
    if (cepInput) {
        cepInput.addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 5) {
                value = value.substring(0, 5) + "-" + value.substring(5, 8);
            }
            e.target.value = value;
        });

        cepInput.addEventListener("blur", function() {
            const cep = this.value.replace(/\D/g, "");
            if (cep.length === 8) {
                buscarCEP(cep);
            }
        });
    }

    function buscarCEP(cep) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById("logradouro").value = data.logradouro || "";
                    document.getElementById("bairro").value = data.bairro || "";
                    document.getElementById("cidade").value = data.localidade || "";
                    document.getElementById("estado").value = data.uf || "";
                    showGlobalAlert("success", "Endereço preenchido automaticamente!");
                } else {
                    showGlobalAlert("warning", "CEP não encontrado");
                }
            })
            .catch(error => {
                console.error("Erro ao buscar CEP:", error);
                showGlobalAlert("danger", "Erro ao consultar CEP");
            });
    }

    // Adiciona evento ao botão de confirmação do modal
    const btnConfirmarExclusao = document.getElementById('btnConfirmarExclusaoEndereco');
    if (btnConfirmarExclusao) {
        btnConfirmarExclusao.addEventListener('click', confirmarExclusaoEndereco);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>