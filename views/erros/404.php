<?php
$title = 'Página não encontrada - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Ícone 404 -->
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded-circle mx-auto"
                        style="width: 120px; height: 120px;">
                        <span class="display-6 fw-bold">404</span>
                    </div>
                </div>

                <!-- Título -->
                <h1 class="display-4 fw-bold text-dark mb-3">Página não encontrada</h1>
                <h2 class="h4 text-muted mb-4">A página que você está procurando não existe</h2>

                <!-- Mensagem explicativa -->
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-info me-3 fs-4"></i>
                        <div class="text-start">
                            <strong>O que pode ter acontecido:</strong>
                            <ul class="mb-0 mt-2">
                                <li>A URL foi digitada incorretamente</li>
                                <li>O link que você clicou está quebrado</li>
                                <li>A página foi movida ou removida</li>
                                <li>Você não tem permissão para acessar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Ações recomendadas -->
                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-house text-primary fs-1 mb-3"></i>
                                <h5 class="card-title">Página Inicial</h5>
                                <p class="card-text text-muted">Volte para a página principal</p>
                                <a href="<?= BASE_URL ?>" class="btn btn-primary">
                                    <i class="bi bi-house me-2"></i>
                                    Ir para Início
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-box-arrow-in-right text-success fs-1 mb-3"></i>
                                <h5 class="card-title">Fazer Login</h5>
                                <p class="card-text text-muted">Acesse sua conta para continuar</p>
                                <a href="<?= BASE_URL ?>/login" class="btn btn-success">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Link para voltar -->
                <div class="mt-4">
                    <button onclick="history.back()" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar
                    </button>

                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::isPrestador() && !Session::isCliente()): ?>
                            <a href="<?= BASE_URL ?>/prestador/dashboard" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        <?php elseif (Session::isCliente()): ?>
                            <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .alert {
        border-left: 4px solid #0dcaf0;
    }

    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }
    }
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>