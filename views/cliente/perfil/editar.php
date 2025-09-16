<?php
$title = 'Editar Perfil Cliente - ChamaServiço';
ob_start();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">
                    <i class="bi bi-person-gear me-2"></i>Editar Perfil Cliente
                </h2>
                <div class="d-flex gap-2">
                    <a href="<?= url('cliente/perfil') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao Perfil
                    </a>
                    <a href="<?= url('cliente/perfil/enderecos') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-geo-alt me-1"></i>Gerenciar Endereços
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar com foto e navegação -->
                <div class="col-lg-3 mb-4">
                    <!-- Card da foto do perfil -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <?php
                                $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                if ($fotoPerfil) {
                                    $fotoPerfil = basename($fotoPerfil);
                                    $arquivoExiste = file_exists("uploads/perfil/" . $fotoPerfil);
                                }
                                ?>
                                <div class="position-relative d-inline-block">
                                    <?php if ($fotoPerfil && $arquivoExiste): ?>
                                        <img src="<?= url('uploads/perfil/' . htmlspecialchars($fotoPerfil)) ?>"
                                            class="rounded-circle border border-3 border-primary"
                                            style="width: 120px; height: 120px; object-fit: cover;"
                                            alt="Foto do perfil">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-3 border-primary"
                                            style="width: 120px; height: 120px;">
                                            <i class="bi bi-person text-secondary" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="position-absolute bottom-0 end-0">
                                        <button type="button" class="btn btn-primary btn-sm rounded-circle"
                                            data-bs-toggle="modal" data-bs-target="#modalFoto">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($usuario['email']) ?></p>
                        </div>
                    </div>

                    <!-- Navegação das seções -->
                    <div class="list-group shadow-sm">
                        <a href="#dadosPessoais" class="list-group-item list-group-item-action active"
                            data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i>Dados Pessoais
                        </a>
                        <a href="#seguranca" class="list-group-item list-group-item-action"
                            data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i>Segurança
                        </a>
                        <a href="<?= url('cliente/perfil/enderecos') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-geo-alt me-2"></i>Meus Endereços
                        </a>
                    </div>
                </div>

                <!-- Conteúdo principal -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Dados Pessoais -->
                                <div class="tab-pane fade show active" id="dadosPessoais">
                                    <h4 class="mb-4 text-primary">
                                        <i class="bi bi-person me-2"></i>Dados Pessoais do Cliente
                                    </h4>

                                    <form method="POST" action="<?= url('cliente/perfil/editar') ?>" id="formDadosPessoais">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="acao" value="dados_pessoais">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nome" class="form-label fw-semibold">Nome Completo *</label>
                                                <input type="text" class="form-control" id="nome" name="nome"
                                                    required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="email" class="form-label fw-semibold">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="cpf" class="form-label fw-semibold">
                                                    CPF
                                                    <?php if (!empty($usuario['cpf'])): ?>
                                                        <small class="text-muted">(não pode ser alterado)</small>
                                                    <?php endif; ?>
                                                </label>
                                                <input type="text" class="form-control" id="cpf" name="cpf"
                                                    value="<?= $usuario['cpf'] ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario['cpf']) : '' ?>"
                                                    placeholder="000.000.000-00" maxlength="14"
                                                    <?= !empty($usuario['cpf']) ? 'readonly style="background-color: #f8f9fa;"' : '' ?> disabled>
                                                <?php if (!empty($usuario['cpf'])): ?>
                                                    <div class="form-text text-warning">
                                                        <i class="bi bi-lock me-1"></i>CPF não pode ser alterado por questões de segurança.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="telefone" class="form-label fw-semibold">Telefone</label>
                                                <input type="text" class="form-control" id="telefone" name="telefone"
                                                    value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>"
                                                    placeholder="(00) 00000-0000" maxlength="15">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="dt_nascimento" class="form-label fw-semibold">
                                                    Data de Nascimento
                                                    <?php if (!empty($usuario['dt_nascimento'])): ?>
                                                        <small class="text-muted">(não pode ser alterada)</small>
                                                    <?php endif; ?>
                                                </label>
                                                <input type="date" class="form-control" id="dt_nascimento" name="dt_nascimento"
                                                    value="<?= $usuario['dt_nascimento'] ?? '' ?>"
                                                    <?= !empty($usuario['dt_nascimento']) ? 'readonly style="background-color: #f8f9fa;"' : '' ?> disabled>
                                                <?php if (!empty($usuario['dt_nascimento'])): ?>
                                                    <div class="form-text text-warning">
                                                        <i class="bi bi-lock me-1"></i>Data de nascimento não pode ser alterada por questões de segurança.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Tipo de Conta</label>
                                                <input type="text" class="form-control bg-light"
                                                    value="<?= ucfirst($usuario['tipo'] ?? 'cliente') ?>" readonly>
                                                <div class="form-text text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>Você é um cliente do sistema.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary" id="btnSalvarDados">
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                                            </button>
                                            <a href="<?= url('cliente/perfil') ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-lg me-1"></i>Cancelar
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <!-- Alterar Senha -->
                                <div class="tab-pane fade" id="seguranca">
                                    <h4 class="mb-4 text-primary">
                                        <i class="bi bi-shield-lock me-2"></i>Alterar Senha
                                    </h4>

                                    <form method="POST" action="<?= url('cliente/perfil/editar') ?>" id="formSenha">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="acao" value="alterar_senha">

                                        <div class="mb-3">
                                            <label for="senha_atual" class="form-label fw-semibold">Senha Atual *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="senha_atual" name="senha_atual"
                                                    required placeholder="Digite sua senha atual">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senha_atual')">
                                                    <i class="bi bi-eye" id="toggleIcon_senha_atual"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nova_senha" class="form-label fw-semibold">Nova Senha *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="nova_senha" name="nova_senha"
                                                    required placeholder="Mínimo 6 caracteres" minlength="6">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nova_senha')">
                                                    <i class="bi bi-eye" id="toggleIcon_nova_senha"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="confirmar_senha" class="form-label fw-semibold">Confirmar Nova Senha *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                                    required placeholder="Digite novamente a nova senha">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmar_senha')">
                                                    <i class="bi bi-eye" id="toggleIcon_confirmar_senha"></i>
                                                </button>
                                            </div>
                                            <div id="senhaMatchStatus" class="form-text"></div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-warning" id="btnAlterarSenha">
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                <i class="bi bi-shield-check me-1"></i>Alterar Senha
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Limpar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>
                    <?= ($usuario['foto_perfil']) ? 'Alterar' : 'Adicionar' ?> Foto de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= url('cliente/perfil/editar') ?>" enctype="multipart/form-data" id="formFoto">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="upload_foto">

                    <div class="mb-3">
                        <label for="foto_perfil" class="form-label">Selecione uma imagem</label>
                        <input type="file" class="form-control" id="foto_perfil" name="foto_perfil"
                            accept="image/jpeg,image/jpg,image/png" required>
                        <div class="form-text">
                            Formatos aceitos: JPG, PNG. Tamanho máximo: 2MB
                        </div>
                    </div>

                    <div class="mb-3">
                        <div id="imagemPreview" class="text-center d-none">
                            <p class="mb-2">Preview da imagem:</p>
                            <img id="previewImg" src="" class="img-fluid rounded-circle border border-3 border-primary"
                                style="max-height: 200px; max-width: 200px;">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="btnUploadFoto">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <i class="bi bi-upload me-1"></i>
                            <?= ($usuario['foto_perfil']) ? 'Alterar' : 'Adicionar' ?> Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
// Toggle visibilidade da senha
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById("toggleIcon_" + inputId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}

// Validação de confirmação de senha
function validarSenhas() {
    const novaSenha = document.getElementById("nova_senha").value;
    const confirmarSenha = document.getElementById("confirmar_senha").value;
    const statusDiv = document.getElementById("senhaMatchStatus");
    
    if (confirmarSenha === "") {
        statusDiv.innerHTML = "";
        return;
    }
    
    if (novaSenha === confirmarSenha) {
        statusDiv.innerHTML = "<span class=\\"text-success\\"><i class=\\"bi bi-check-circle me-1\\"></i>Senhas coincidem</span>";
    } else {
        statusDiv.innerHTML = "<span class=\\"text-danger\\"><i class=\\"bi bi-x-circle me-1\\"></i>Senhas não coincidem</span>";
    }
}

// Máscara para CPF
function mascaraCPF(input) {
    let value = input.value.replace(/\\D/g, "");
    value = value.replace(/(\\d{3})(\\d)/, "$1.$2");
    value = value.replace(/(\\d{3})(\\d)/, "$1.$2");
    value = value.replace(/(\\d{3})(\\d{1,2})/, "$1-$2");
    input.value = value;
}

// Máscara para telefone
function mascaraTelefone(input) {
    let value = input.value.replace(/\\D/g, "");
    value = value.replace(/(\\d{2})(\\d)/, "($1) $2");
    value = value.replace(/(\\d{4,5})(\\d{4})/, "$1-$2");
    input.value = value;
}

// Preview da foto - CORRIGIDO
function previewFoto(input) {
    const file = input.files[0];
    const previewDiv = document.getElementById("imagemPreview");
    const previewImg = document.getElementById("previewImg");
    
    if (file) {
        // Validar tamanho do arquivo
        if (file.size > 2 * 1024 * 1024) { // 2MB
            alert("Arquivo muito grande! Máximo 2MB.");
            input.value = "";
            previewDiv.classList.add("d-none");
            return;
        }
        
        // Validar tipo do arquivo
        const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
        if (!allowedTypes.includes(file.type)) {
            alert("Formato não permitido! Use JPG ou PNG.");
            input.value = "";
            previewDiv.classList.add("d-none");
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.classList.remove("d-none");
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.classList.add("d-none");
    }
}

// Spinner nos formulários - MELHORADO
function addSpinnerToForm(formId, buttonId) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener("submit", function(e) {
            const btn = document.getElementById(buttonId);
            if (btn) {
                const spinner = btn.querySelector(".spinner-border");
                const icon = btn.querySelector("i:not(.spinner-border)");
                
                if (spinner) spinner.classList.remove("d-none");
                if (icon) icon.classList.add("d-none");
                btn.disabled = true;
            }
        });
    }
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
    // Aplicar máscaras
    document.getElementById("cpf").addEventListener("input", function() {
        if (!this.readOnly) {
            mascaraCPF(this);
        }
    });
    
    document.getElementById("telefone").addEventListener("input", function() {
        mascaraTelefone(this);
    });
    
    // Validação de senhas
    document.getElementById("nova_senha").addEventListener("input", validarSenhas);
    document.getElementById("confirmar_senha").addEventListener("input", validarSenhas);
    
    // Validação de idade
    document.getElementById("dt_nascimento").addEventListener("change", validarIdade);
    
    // Preview da foto - CORRIGIDO
    const fotoInput = document.getElementById("foto_perfil");
    if (fotoInput) {
        fotoInput.addEventListener("change", function() {
            previewFoto(this);
        });
    }
    
    // Spinners nos formulários
    addSpinnerToForm("formDadosPessoais", "btnSalvarDados");
    addSpinnerToForm("formSenha", "btnAlterarSenha");
    addSpinnerToForm("formFoto", "btnUploadFoto");
    
    // Validação adicional do formulário de foto
    const formFoto = document.getElementById("formFoto");
    if (formFoto) {
        formFoto.addEventListener("submit", function(e) {
            const fotoInput = document.getElementById("foto_perfil");
            if (!fotoInput.files || !fotoInput.files[0]) {
                e.preventDefault();
                alert("Por favor, selecione uma imagem.");
                return false;
            }
        });
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>