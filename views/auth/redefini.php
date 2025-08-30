<?php
$title = 'Redefinir Senha - ChamaServiço';
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
        max-width: 380px;
        width: 100%;
    }

    .card-login .card-body {
        padding: 2.5rem 2rem;
    }

    .btn-primary {
        background: #f5a522;
        color: #fff;
        font-weight: 600;
        border-radius: 24px;
        border: none;
        box-shadow: 0 2px 8px rgba(245, 165, 34, 0.08);
        transition: background 0.2s;
    }

    .btn-primary:hover {
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
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-key" style="font-size: 3rem; color: #f5a522;"></i>
                <h3 class="mt-3" style="color: #f5a522;">Redefinir Senha</h3>
                <p class="text-muted">Informe seu e-mail para receber instruções</p>
            </div>
            <form method="POST" action="/chamaservico/redefinir-senha">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail cadastrado</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Digite seu e-mail">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-1"></i>Enviar instruções
                    </button>
                </div>
            </form>
            <div class="text-center">
                <a href="/chamaservico/login" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Voltar ao Login
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
<script>
document.getElementById('formRedefinir').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';
    
    // Simulação (já que não há servidor de e-mail configurado)
    setTimeout(() => {
        alert('Em um sistema real, um e-mail seria enviado com o link para redefinir a senha. Por enquanto, esta funcionalidade está simulada.');
        window.location.href = '/chamaservico/login';
    }, 2000);
});
</script>

