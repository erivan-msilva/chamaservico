<?php
$title = 'Nova Senha - ChamaServiço';
$token = $_GET['token'] ?? '';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <!-- Logo e Título -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center mb-3">
                    <span style="width: 20px; height: 20px; background-color: #f5a522; border-radius: 50%; margin-right: 10px;"></span>
                    <span class="h3 fw-bold" style="color: #283579;">CHAMA</span>
                    <span class="h3" style="color: #f5a522; font-weight: 300;">SERVIÇO</span>
                </div>
                <h1 class="h4 text-dark mb-2">Definir Nova Senha</h1>
                <p class="text-muted">Crie uma senha segura para sua conta</p>
            </div>

            <!-- Card de Nova Senha -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" id="formNovaSenha">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="mb-3">
                            <label for="nova_senha" class="form-label fw-bold">
                                <i class="bi bi-lock me-2 text-primary"></i>
                                Nova Senha *
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="nova_senha" 
                                       name="nova_senha" 
                                       required 
                                       minlength="6"
                                       placeholder="Digite sua nova senha">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nova_senha')">
                                    <i class="bi bi-eye" id="nova_senha-icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <small>Mínimo de 6 caracteres</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label fw-bold">
                                <i class="bi bi-lock-fill me-2 text-primary"></i>
                                Confirmar Nova Senha *
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="confirmar_senha" 
                                       name="confirmar_senha" 
                                       required 
                                       minlength="6"
                                       placeholder="Confirme sua nova senha">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmar_senha')">
                                    <i class="bi bi-eye" id="confirmar_senha-icon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Indicador de Força da Senha -->
                        <div class="mb-3">
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <small class="strength-text" id="strengthText">Digite uma senha</small>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                Redefinir Senha
                            </button>
                        </div>
                    </form>

                    <!-- Links de Navegação -->
                    <div class="text-center">
                        <hr class="my-3">
                        <p class="mb-0">
                            <a href="/chamaservico/login" class="text-decoration-none fw-bold">
                                <i class="bi bi-arrow-left me-1"></i>
                                Voltar ao Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --cor-primaria: #283579;
    --cor-secundaria: #f5a522;
}

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

.card {
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.form-control:focus {
    border-color: var(--cor-primaria);
    box-shadow: 0 0 0 0.2rem rgba(40, 53, 121, 0.25);
}

.btn-primary {
    background: var(--cor-primaria);
    border-color: var(--cor-primaria);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 53, 121, 0.3);
}

.password-strength {
    margin-top: 8px;
}

.strength-bar {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.strength-weak { background-color: #dc3545; }
.strength-fair { background-color: #fd7e14; }
.strength-good { background-color: #ffc107; }
.strength-strong { background-color: #28a745; }

.strength-text {
    font-size: 0.8rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formNovaSenha');
    const senhaInput = document.getElementById('nova_senha');
    const confirmarSenhaInput = document.getElementById('confirmar_senha');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    // Verificar força da senha
    senhaInput.addEventListener('input', function() {
        const senha = this.value;
        const strength = checkPasswordStrength(senha);
        
        // Atualizar barra de força
        strengthFill.style.width = strength.percentage + '%';
        strengthFill.className = 'strength-fill ' + strength.class;
        strengthText.textContent = strength.text;
        strengthText.className = 'strength-text ' + strength.class;
    });
    
    // Verificar se as senhas coincidem
    function validatePasswordMatch() {
        const senha = senhaInput.value;
        const confirmarSenha = confirmarSenhaInput.value;
        
        if (confirmarSenha && senha !== confirmarSenha) {
            confirmarSenhaInput.classList.add('is-invalid');
            showFieldError(confirmarSenhaInput, 'As senhas não coincidem');
        } else {
            confirmarSenhaInput.classList.remove('is-invalid');
            hideFieldError(confirmarSenhaInput);
        }
    }
    
    confirmarSenhaInput.addEventListener('blur', validatePasswordMatch);
    confirmarSenhaInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validatePasswordMatch();
        }
    });
    
    // Validação no submit
    form.addEventListener('submit', function(e) {
        const senha = senhaInput.value;
        const confirmarSenha = confirmarSenhaInput.value;
        
        if (senha.length < 6) {
            e.preventDefault();
            senhaInput.classList.add('is-invalid');
            showFieldError(senhaInput, 'A senha deve ter pelo menos 6 caracteres');
            senhaInput.focus();
            return;
        }
        
        if (senha !== confirmarSenha) {
            e.preventDefault();
            confirmarSenhaInput.classList.add('is-invalid');
            showFieldError(confirmarSenhaInput, 'As senhas não coincidem');
            confirmarSenhaInput.focus();
            return;
        }
        
        // Mostrar loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Redefinindo...';
        submitBtn.disabled = true;
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 6) score += 1;
        if (password.length >= 8) score += 1;
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        
        if (score < 3) {
            return { percentage: 25, class: 'strength-weak', text: 'Senha fraca' };
        } else if (score < 4) {
            return { percentage: 50, class: 'strength-fair', text: 'Senha razoável' };
        } else if (score < 5) {
            return { percentage: 75, class: 'strength-good', text: 'Senha boa' };
        } else {
            return { percentage: 100, class: 'strength-strong', text: 'Senha forte' };
        }
    }
    
    function showFieldError(input, message) {
        hideFieldError(input);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.parentNode.appendChild(errorDiv);
    }
    
    function hideFieldError(input) {
        const errorDiv = input.parentNode.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app_public.php';
?>