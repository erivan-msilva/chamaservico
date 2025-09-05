<?php
$title = 'Meus Endereços - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        Meus Endereços
                    </h2>
                    <p class="text-muted">Gerencie seus endereços de atendimento</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoEndereco">
                        <i class="bi bi-plus-circle me-1"></i>
                        Novo Endereço
                    </button>
                </div>
            </div>

            <!-- Lista de endereços -->
            <div class="row" id="enderecosContainer">
                <?php if (empty($enderecos)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 text-muted">Nenhum endereço cadastrado</h4>
                                <p class="text-muted">Adicione um endereço para começar a usar o sistema.</p>
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

<!-- Modal Novo Endereço -->
<div class="modal fade" id="modalNovoEndereco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formNovoEndereco">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    Novo Endereço
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUG: Página de endereços carregada ===');
    
    const formNovoEndereco = document.getElementById('formNovoEndereco');
    const btnBuscarCep = document.getElementById('btnBuscarCep');
    const btnSalvar = document.getElementById('btnSalvar');
    const alertModal = document.getElementById('alertModal');

    // Buscar CEP
    btnBuscarCep.addEventListener('click', buscarCep);
    document.getElementById('cep').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarCep();
        }
    });

    // Formatar CEP enquanto digita
    document.getElementById('cep').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
        
        // Limpar status
        document.getElementById('cepStatus').textContent = '';
    });

    // Converter estado para maiúsculo
    document.getElementById('estado').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Submeter formulário
    formNovoEndereco.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('=== DEBUG: Formulário submetido ===');
        salvarEndereco();
    });

    async function buscarCep() {
        console.log('=== DEBUG: Buscando CEP ===');
        const cepInput = document.getElementById('cep');
        const status = document.getElementById('cepStatus');
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            status.textContent = 'CEP deve ter 8 dígitos';
            status.className = 'form-text text-danger';
            return;
        }

        // Mostrar loading
        status.textContent = 'Buscando endereço...';
        status.className = 'form-text text-primary';
        btnBuscarCep.disabled = true;
        btnBuscarCep.innerHTML = '<div class="spinner-border spinner-border-sm"></div>';

        try {
            const response = await fetch(`<?= url('perfil/api/buscar-cep') ?>?cep=${cep}`);
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('CEP data:', data);

            if (data.success && data.endereco) {
                // Preencher campos
                document.getElementById('logradouro').value = data.endereco.logradouro || '';
                document.getElementById('bairro').value = data.endereco.bairro || '';
                document.getElementById('cidade').value = data.endereco.cidade || '';
                document.getElementById('estado').value = data.endereco.estado || '';

                status.textContent = 'Endereço preenchido automaticamente!';
                status.className = 'form-text text-success';

                // Focar no campo número
                document.getElementById('numero').focus();
            } else {
                status.textContent = data.message || 'CEP não encontrado';
                status.className = 'form-text text-warning';
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            status.textContent = 'Erro ao buscar CEP. Verifique sua conexão.';
            status.className = 'form-text text-danger';
        } finally {
            // Restaurar botão
            btnBuscarCep.disabled = false;
            btnBuscarCep.innerHTML = '<i class="bi bi-search"></i>';
        }
    }

    async function salvarEndereco() {
        console.log('=== DEBUG: Iniciando salvamento ===');
        
        // Validar campos obrigatórios
        const campos = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'];
        let valid = true;
        const dadosFormulario = {};

        campos.forEach(campo => {
            const input = document.getElementById(campo);
            const valor = input.value.trim();
            dadosFormulario[campo] = valor;
            
            if (!valor) {
                input.classList.add('is-invalid');
                valid = false;
                console.log(`Campo obrigatório vazio: ${campo}`);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        console.log('Dados do formulário:', dadosFormulario);

        if (!valid) {
            showAlert('danger', 'Preencha todos os campos obrigatórios!');
            return;
        }

        // Validações específicas
        const cep = dadosFormulario.cep.replace(/\D/g, '');
        if (cep.length !== 8) {
            showAlert('danger', 'CEP deve ter 8 dígitos');
            document.getElementById('cep').classList.add('is-invalid');
            return;
        }

        if (dadosFormulario.estado.length !== 2) {
            showAlert('danger', 'Estado deve ter 2 caracteres');
            document.getElementById('estado').classList.add('is-invalid');
            return;
        }

        // Mostrar loading
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Salvando...';

        try {
            const formData = new FormData(formNovoEndereco);
            
            // Debug: Mostrar dados sendo enviados
            console.log('=== DEBUG: Dados sendo enviados ===');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            const response = await fetch('<?= url("cliente/perfil/enderecos") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.log('Resposta não-JSON:', textResponse);
                throw new Error('Resposta não é JSON válido. Recebido: ' + textResponse.substring(0, 200));
            }

            const data = await response.json();
            console.log('Resposta JSON:', data);

            if (data.sucesso) {
                showAlert('success', data.mensagem);
                
                // Recarregar página após 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert('danger', data.mensagem || 'Erro ao salvar endereço');
                if (data.debug) {
                    console.error('Debug do servidor:', data.debug);
                }
            }
        } catch (error) {
            console.error('Erro completo:', error);
            showAlert('danger', 'Erro de conexão: ' + error.message);
        } finally {
            // Restaurar botão
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-save me-2"></i>Salvar Endereço';
        }
    }

    function showAlert(type, message) {
        alertModal.className = `alert alert-${type}`;
        alertModal.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
        `;
        alertModal.style.display = 'block';
    }

    // Limpar modal ao fechar
    const modal = document.getElementById('modalNovoEndereco');
    modal.addEventListener('hidden.bs.modal', function() {
        formNovoEndereco.reset();
        alertModal.style.display = 'none';
        document.getElementById('cepStatus').textContent = '';
        
        // Remover classes de erro
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    });
});

// Funções globais para ações dos endereços
async function definirPrincipal(enderecoId) {
    if (!confirm('Definir este endereço como principal?')) return;

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
        formData.append('acao', 'definir_principal');
        formData.append('endereco_id', enderecoId);

        const response = await fetch('<?= url("cliente/perfil/enderecos") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.sucesso) {
            location.reload();
        } else {
            alert('Erro: ' + data.mensagem);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro de conexão. Tente novamente.');
    }
}

async function excluirEndereco(enderecoId) {
    if (!confirm('Tem certeza que deseja excluir este endereço?')) return;

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
        formData.append('acao', 'excluir');
        formData.append('endereco_id', enderecoId);

        const response = await fetch('<?= url("cliente/perfil/enderecos") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.sucesso) {
            location.reload();
        } else {
            alert('Erro: ' + data.mensagem);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro de conexão. Tente novamente.');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
