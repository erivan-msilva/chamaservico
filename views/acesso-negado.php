<?php
$title = 'Acesso Negado - ChamaServiço';
ob_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Acesso Negado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h1 class="text-danger"><i class="bi bi-x-octagon me-2"></i>Acesso Negado</h1>
                <p class="lead">Você não tem permissão para acessar esta página.</p>
                <a href="<?= url('') ?>" class="btn btn-primary">Ir para Home</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
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
