<?php
$title = 'Nova Senha - ChamaServiço';
ob_start();
?>

<style>
    body {
        background: #283579;
        min-height: 100vh;
    }

    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-login {
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(40, 53, 121, 0.12);
        border: none;
        padding: 0;
        max-width: 380px;
        width: 100%;
    }

    .card-login .card-body {
        padding: 2.5rem 2rem;
    }

    .btn-success {
        background: #f5a522;
        color: #fff;
        font-weight: 600;
        border-radius: 24px;
        border: none;
        box-shadow: 0 2px 8px rgba(245, 165, 34, 0.08);
        transition: background 0.2s;
    }

    .btn-success:hover {
        background: #d48c00;
        color: #fff;
    }

    .input-group-text {
        background: #f8f9fa;
        border: none;
    }

    .form-control:focus {
        border-color: #f5a522;
        box-shadow: 0 0 0 0.2rem rgba(245, 165, 34, 0.15);
    }

    .link-login {
        color: #283579;
        font-weight: 500;
        text-decoration: none;
    }

    .link-login:hover {
        text-decoration: underline;
        color: #f5a522;
    }
</style>

<div class="login-container">
    <div class="card card-login mx-auto">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock" style="font-size: 3rem; color: #f5a522;"></i>
                <h3 class="mb-1 mt-3" style="color: #f5a522;">Definir Nova Senha</h3>
                <p class="text-muted mb-3">Digite sua nova senha</p>
            </div>
            <form method="POST" id="formNovaSenha">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <div class="mb-3">
                    <label for="nova_senha" class="form-label fw-semibold">Nova Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required minlength="6" placeholder="Digite a nova senha">
                        <span class="input-group-text" style="cursor:pointer;" onclick="toggleSenha('nova_senha', 'iconEye1')">
                            <i class="bi bi-eye" id="iconEye1"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label fw-semibold">Confirmar Nova Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="6" placeholder="Confirme a nova senha">
                        <span class="input-group-text" style="cursor:pointer;" onclick="toggleSenha('confirmar_senha', 'iconEye2')">
                            <i class="bi bi-eye" id="iconEye2"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        A senha deve ter pelo menos 6 caracteres
                    </small>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg" id="btnRedefinir">
                        <i class="bi bi-check-lg me-1"></i>Redefinir Senha
                    </button>
                </div>
            </form>
            <div class="text-center">
                <a href="/chamaservico/login" class="link-login small">
                    <i class="bi bi-arrow-left me-1"></i>Voltar ao Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSenha(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

document.getElementById('formNovaSenha').addEventListener('submit', function(e) {
    e.preventDefault();

    const senha = document.getElementById('nova_senha').value;
    const confirmar = document.getElementById('confirmar_senha').value;
    const btn = document.getElementById('btnRedefinir');

    // Validação no frontend
    if (senha.length < 6) {
        alert('A senha deve ter pelo menos 6 caracteres.');
        return;
    }
    if (senha !== confirmar) {
        alert('As senhas não coincidem.');
        return;
    }

    // Desabilitar botão
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Redefinindo...';

    // Enviar via AJAX
    fetch(window.location.pathname + window.location.search, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(new FormData(this))
    })
    .then(response => response.text())
    .then(html => {
        // Se a resposta não contém erro, redirecionar
        if (html.includes('Senha redefinida com sucesso')) {
            alert('Senha redefinida com sucesso!');
            window.location.href = '/chamaservico/login';
        } else {
            // Atualizar a página com a resposta
            document.body.innerHTML = html;
        }
    })
    .catch(() => {
        alert('Erro ao redefinir senha. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Redefinir Senha';
    });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>