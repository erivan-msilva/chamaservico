<?php
$title = 'Login - ChamaServiço';
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

    .logo-chama {
        width: 70px;
        height: 70px;
        object-fit: contain;
        margin-bottom: 10px;
    }

    .btn-login {
        background: #f5a522;
        color: #fff;
        font-weight: 600;
        border-radius: 24px;
        border: none;
        box-shadow: 0 2px 8px rgba(245, 165, 34, 0.08);
        transition: background 0.2s;
    }

    .btn-login:hover {
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

    .divider {
        border-top: 1px solid #e9ecef;
        margin: 1.5rem 0;
    }
</style>

<div class="login-container">
    <div class="card card-login mx-auto">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="/assets/img/logochama.png" alt="Logo ChamaServiço" class="logo-chama">
                <h4 class="fw-bold mb-1">Bem-vindo!</h4>
                <p class="text-muted mb-3">Acesse sua conta para usar o sistema</p>
            </div>
            <form method="POST" action="/chamaservico/login">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Digite seu e-mail">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label fw-semibold">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" required placeholder="Digite sua senha">
                        <span class="input-group-text" style="cursor:pointer;" onclick="toggleSenha()">
                            <i class="bi bi-eye" id="iconEye"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-2 text-end">
                    <a href="/chamaservico/redefinir-senha" class="link-login small">Esqueceu a senha?</a>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login btn-lg">Entrar</button>
                </div>
            </form>
            <div class="divider"></div>
            <div class="text-center mb-2">
                <span class="text-muted">ou</span>
            </div>
            <div class="d-grid mb-2">
                <a href="/chamaservico/registro" class="btn btn-outline-warning btn-lg fw-semibold" style="border-radius:24px;">
                    <i class="bi bi-person-plus me-1"></i>Criar uma conta
                </a>
            </div>
            <div class="text-center mt-2">
                <a href="/chamaservico/" class="link-login small"><i class="bi bi-arrow-left me-1"></i>Voltar para a Home</a>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    function toggleSenha() {
        const input = document.getElementById('senha');
        const icon = document.getElementById('iconEye');
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
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>