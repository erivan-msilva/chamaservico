<?php
$title = 'Criar Conta - ChamaServiço';
ob_start();
?>

<style>
    body {
        background: #283579;
        min-height: 100vh;
    }

    .registro-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-registro {
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(40, 53, 121, 0.12);
        border: none;
        padding: 0;
        max-width: 420px;
        width: 100%;
    }

    .card-registro .card-body {
        padding: 2.5rem 2rem;
    }

    .logo-chama {
        width: 70px;
        height: 70px;
        object-fit: contain;
        margin-bottom: 10px;
    }

    .btn-registro {
        background: #f5a522;
        color: #fff;
        font-weight: 600;
        border-radius: 24px;
        border: none;
        box-shadow: 0 2px 8px rgba(245, 165, 34, 0.08);
        transition: background 0.2s;
    }

    .btn-registro:hover {
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

    .link-registro {
        color: #283579;
        font-weight: 500;
        text-decoration: none;
    }

    .link-registro:hover {
        text-decoration: underline;
        color: #f5a522;
    }

    .divider {
        border-top: 1px solid #e9ecef;
        margin: 1.5rem 0;
    }
</style>

<div class="registro-container">
    <div class="card card-registro mx-auto">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="/chamaservico/assets/img/logo_branca_sem-fundo.png" alt="Logo ChamaServiço" class="logo-chama">
                <h4 class="fw-bold mb-1">Criar Conta</h4>
                <p class="text-muted mb-3">Preencha os dados para se cadastrar</p>
            </div>
            <form method="POST" action="/chamaservico/registro">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <div class="mb-3">
                    <label for="nome" class="form-label fw-semibold">Nome Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="nome" name="nome" required maxlength="100" placeholder="Seu nome completo">
                    </div>
                    <div class="form-text text-muted">Informe seu nome como aparece em documentos.</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="100" placeholder="Seu e-mail">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label fw-semibold">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="6" placeholder="Crie uma senha">
                        <span class="input-group-text" style="cursor:pointer;" onclick="toggleSenha()">
                            <i class="bi bi-eye" id="iconEye"></i>
                        </span>
                    </div>
                    <div class="form-text text-muted">Mínimo 6 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label for="senha_confirmar" class="form-label fw-semibold">Confirmar Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="senha_confirmar" name="senha_confirmar" required minlength="6" placeholder="Repita a senha">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label fw-semibold">Tipo de Conta</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="cliente" selected>Cliente</option>
                        <option value="prestador">Prestador</option>
                        <option value="ambos">Cliente & Prestador</option>
                    </select>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-registro btn-lg">Cadastrar</button>
                </div>
            </form>
            <div class="divider"></div>
            <div class="text-center mb-2">
                <span class="text-muted">Já tem conta?</span>
            </div>
            <div class="d-grid mb-2">
                <a href="/chamaservico/login" class="btn btn-outline-warning btn-lg fw-semibold" style="border-radius:24px;">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                </a>
            </div>
            <div class="text-center mt-2">
                <a href="/chamaservico/" class="link-registro small"><i class="bi bi-arrow-left me-1"></i>Voltar para a Home</a>
            </div>
        </div>
    </div>
</div>



<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>