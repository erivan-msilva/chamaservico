<?php
$title = 'Editar Perfil Cliente - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil me-2"></i>Editar Perfil Cliente</h2>
            <div>
                <a href="/chamaservico/cliente/perfil" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Voltar ao Perfil
                </a>
                <a href="/chamaservico/cliente/perfil/enderecos" class="btn btn-outline-primary">
                    <i class="bi bi-geo-alt me-1"></i>Gerenciar Endereços
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Foto do Perfil -->
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-camera me-2"></i>Foto do Perfil</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php 
                            $fotoPerfil = $usuario['foto_perfil'];
                            if ($fotoPerfil) {
                                // Remover qualquer prefixo de pasta e usar apenas o nome do arquivo
                                $fotoPerfil = basename($fotoPerfil);
                            }
                            if ($fotoPerfil && file_exists("uploads/perfil/" . $fotoPerfil)): ?>
                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>" 
                                    class="rounded-circle profile-img" alt="Foto do perfil">
                            <?php else: ?>
                                <div class="rounded-circle profile-img bg-light d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                            <input type="hidden" name="acao" value="upload_foto">
                            
                            <div class="mb-3">
                                <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" 
                                       accept="image/*" onchange="previewFoto(this)">
                                <div class="form-text">
                                    Formatos aceitos: JPG, PNG. Máximo 2MB.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-upload me-1"></i>Atualizar Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Dados Pessoais -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Dados Pessoais do Cliente</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                            <input type="hidden" name="acao" value="dados_pessoais">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cpf" class="form-label">
                                        CPF 
                                        <?php if (!empty($usuario['cpf'])): ?>
                                            <small class="text-muted">(não pode ser alterado)</small>
                                        <?php endif; ?>
                                    </label>
                                    <input type="text" class="form-control" id="cpf" name="cpf" 
                                           value="<?= $usuario['cpf'] ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario['cpf']) : '' ?>"
                                           placeholder="000.000.000-00" maxlength="14"
                                           <?= !empty($usuario['cpf']) ? 'readonly style="background-color: #f8f9fa;"' : '' ?>>
                                    <?php if (!empty($usuario['cpf'])): ?>
                                        <div class="form-text text-warning">
                                            <i class="bi bi-lock me-1"></i>CPF não pode ser alterado por questões de segurança.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="telefone" name="telefone" 
                                           value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" 
                                           placeholder="(00) 00000-0000" maxlength="15">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dt_nascimento" class="form-label">
                                        Data de Nascimento 
                                        <?php if (!empty($usuario['dt_nascimento'])): ?>
                                            <small class="text-muted">(não pode ser alterada)</small>
                                        <?php endif; ?>
                                    </label>
                                    <input type="date" class="form-control" id="dt_nascimento" name="dt_nascimento" 
                                           value="<?= $usuario['dt_nascimento'] ?? '' ?>"
                                           <?= !empty($usuario['dt_nascimento']) ? 'readonly style="background-color: #f8f9fa;"' : '' ?>>
                                    <?php if (!empty($usuario['dt_nascimento'])): ?>
                                        <div class="form-text text-warning">
                                            <i class="bi bi-lock me-1"></i>Data de nascimento não pode ser alterada por questões de segurança.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tipo" class="form-label">Tipo de Conta</label>
                                    <input type="text" class="form-control" value="Cliente" readonly style="background-color: #f8f9fa;">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>Você é um cliente do sistema.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                                </button>
                                <a href="/chamaservico/cliente/perfil" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alterar Senha -->
        <div class="row">
            <div class="col-md-8 offset-md-4">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Alterar Senha</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                            <input type="hidden" name="acao" value="alterar_senha">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual *</label>
                                    <input type="password" class="form-control" id="senha_atual" name="senha_atual" required placeholder="Digite sua senha atual">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nova_senha" class="form-label">Nova Senha *</label>
                                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" required placeholder="Mínimo 6 caracteres" minlength="6">
                                    <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required placeholder="Digite novamente a nova senha">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn" style="background:#f5a522;color:#fff;border-radius:24px;">
                                    <i class="bi bi-shield-check me-1"></i>Alterar Senha
                                </button>
                                <button type="reset" class="btn btn-outline-secondary" style="border-radius:24px;">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
// Máscara para CPF
function mascaraCPF(input) {
    let value = input.value.replace(/\D/g, "");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d{1,2})/, "$1-$2");
    input.value = value;
}

// Máscara para telefone
function mascaraTelefone(input) {
    let value = input.value.replace(/\D/g, "");
    value = value.replace(/(\d{2})(\d)/, "($1) $2");
    value = value.replace(/(\d{4,5})(\d{4})/, "$1-$2");
    input.value = value;
}

// Aplicar máscaras
document.getElementById("cpf").addEventListener("input", function(e) {
    if (!e.target.readOnly) {
        mascaraCPF(e.target);
    }
});

document.getElementById("telefone").addEventListener("input", function(e) {
    mascaraTelefone(e.target);
});

// Validação de confirmação de senha
document.getElementById("confirmar_senha").addEventListener("input", function() {
    const novaSenha = document.getElementById("nova_senha").value;
    const confirmarSenha = this.value;
    
    if (novaSenha !== confirmarSenha) {
        this.setCustomValidity("As senhas não coincidem");
        this.classList.add("is-invalid");
    } else {
        this.setCustomValidity("");
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
    }
});

// Preview da foto
function previewFoto(input) {
    const file = input.files[0];
    const previewImage = document.getElementById("previewImage");
    const placeholderImage = document.getElementById("placeholderImage");
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewImage.classList.remove("d-none");
            if (placeholderImage) {
                placeholderImage.classList.add("d-none");
            }
        };
        
        reader.readAsDataURL(file);
    }
}

// Validação de idade mínima para data de nascimento
document.getElementById("dt_nascimento").addEventListener("change", function() {
    if (!this.readOnly) {
        const hoje = new Date();
        const nascimento = new Date(this.value);
        const idade = hoje.getFullYear() - nascimento.getFullYear();
        const mes = hoje.getMonth() - nascimento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
            idade--;
        }
        
        if (idade < 16) {
            alert("Você deve ter pelo menos 16 anos para se cadastrar.");
            this.value = "";
        } else if (idade > 120) {
            alert("Data de nascimento inválida.");
            this.value = "";
        }
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>