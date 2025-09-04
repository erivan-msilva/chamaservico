<?php
$title = 'Acesso Negado - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="text-center mb-4">
                <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                    <i class="bi bi-shield-exclamation text-white" style="font-size: 3rem;"></i>
                </div>
                <h1 class="display-4 text-danger fw-bold">Acesso Negado</h1>
                <p class="lead text-muted">Você não tem permissão para acessar esta página</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Possíveis motivos:
                        </h5>
                        <ul class="mb-0">
                            <li>Você não está logado no sistema</li>
                            <li>Sua sessão expirou</li>
                            <li>Você não tem o tipo de conta necessário para esta área</li>
                            <li>A página requer permissões específicas</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-house-door fs-1 mb-2"></i>
                                    <h6>Ir para Dashboard</h6>
                                    <p class="small mb-3">Volte para sua área principal</p>
                                    <?php if (Session::isLoggedIn()): ?>
                                        <?php if (Session::isPrestador() && !Session::isCliente()): ?>
                                            <a href="prestador/dashboard" class="btn btn-light btn-sm">
                                                <i class="bi bi-speedometer2 me-1"></i>Dashboard Prestador
                                            </a>
                                        <?php elseif (Session::isCliente()): ?>
                                            <a href="cliente/dashboard" class="btn btn-light btn-sm">
                                                <i class="bi bi-speedometer2 me-1"></i>Dashboard Cliente
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login" class="btn btn-light btn-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Fazer Login
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-gear fs-1 mb-2"></i>
                                    <h6>Meu Perfil</h6>
                                    <p class="small mb-3">Verifique seu tipo de conta</p>
                                    <?php if (Session::isLoggedIn()): ?>
                                        <a href="perfil" class="btn btn-light btn-sm">
                                            <i class="bi bi-person me-1"></i>Ver Perfil
                                        </a>
                                    <?php else: ?>
                                        <a href="registro" class="btn btn-light btn-sm">
                                            <i class="bi bi-person-plus me-1"></i>Criar Conta
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (Session::isLoggedIn()): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-bold">
                                <i class="bi bi-info-circle me-2"></i>
                                Informações da sua conta
                            </h6>
                            <div class="row">
                                <div class="col-sm-4"><strong>Nome:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars(Session::getUserName() ?? 'Não informado') ?></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4"><strong>Tipo de conta:</strong></div>
                                <div class="col-sm-8">
                                    <?php
                                    $tipo = Session::getUserType();
                                    $tipoLabel = [
                                        'cliente' => 'Cliente',
                                        'prestador' => 'Prestador',
                                        'ambos' => 'Cliente & Prestador'
                                    ];
                                    echo $tipoLabel[$tipo] ?? ucfirst($tipo);
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
