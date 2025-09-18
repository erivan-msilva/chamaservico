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
        padding: 0;
        max-width: 420px;
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

    .btn-redefinir {
        background: #f5a522;
        color: #fff;
        font-weight: 600;
        border-radius: 24px;
        border: none;
        box-shadow: 0 2px 8px rgba(245, 165, 34, 0.08);
        transition: background 0.2s;
    }

    .btn-redefinir:hover {
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

    .info-box {
        background: rgba(245, 165, 34, 0.1);
        border: 1px solid rgba(245, 165, 34, 0.3);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>

<div class="login-container">
    <div class="card card-login mx-auto">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?= url('assets/img/logochama.png') ?>" alt="Logo ChamaServiço" class="logo-chama">
                <h4 class="fw-bold mb-1">Redefinir Senha</h4>
                <p class="text-muted mb-3">Digite seu e-mail para receber as instruções</p>
            </div>

            <!-- Informações -->
            <div class="info-box">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle text-warning me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <small class="text-muted">
                            <strong>Como funciona:</strong><br>
                            Digite seu e-mail cadastrado e você receberá um link para criar uma nova senha.
                        </small>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?= url('redefinir-senha') ?>">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">E-mail cadastrado</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                            class="form-control form-control-lg"
                            id="email"
                            name="email"
                            required
                            placeholder="Digite seu e-mail"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-text">
                        <i class="bi bi-shield-check text-success"></i>
                        Seus dados estão seguros e protegidos
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-redefinir btn-lg">
                        <i class="bi bi-send me-2"></i>
                        Enviar Link de Redefinição
                    </button>
                </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <div class="mb-3">
                    <a href="<?= url('login') ?>" class="link-login">
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar para o Login
                    </a>
                </div>

                <div>
                    <span class="text-muted">Não tem uma conta? </span>
                    <a href="<?= url('registro') ?>" class="link-login fw-semibold">
                        Criar conta
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Focar no campo email
        document.getElementById('email').focus();

        // Validação em tempo real
        const emailInput = document.getElementById('email');
        const submitBtn = document.querySelector('button[type="submit"]');

        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            const isValid = email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                submitBtn.disabled = false;
            } else if (email.length > 0) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                submitBtn.disabled = true;
            } else {
                this.classList.remove('is-valid', 'is-invalid');
                submitBtn.disabled = false;
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>