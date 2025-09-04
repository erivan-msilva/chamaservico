<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// Simular notificações dinâmicas para demonstração
$novasSolicitacoes = 3; // Esta variável viria do controller/model

// Configuração do layout
$title = 'Admin Dashboard - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="bg-dark text-white min-vh-100">
                <div class="p-3">
                    <h5><i class="bi bi-shield-check me-2"></i>Admin</h5>
                    <small class="text-muted">Painel Administrativo</small>
                </div>
                <hr class="text-muted">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="<?= url('admin/dashboard') ?>">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= url('admin/usuarios') ?>">
                            <i class="bi bi-people me-2"></i>Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= url('admin/solicitacoes') ?>">
                            <i class="bi bi-list-task me-2"></i>Solicitações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= url('admin/propostas') ?>">
                            <i class="bi bi-file-earmark-text me-2"></i>Propostas
                        </a>
                    </li>
                </ul>
                <hr class="text-muted">
                <div class="p-3">
                    <a href="<?= url('logout') ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Sair
                    </a>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-md-9 col-lg-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Administrativo</h2>
                    <span class="badge bg-success">
                        Logado como: <?= htmlspecialchars(Session::getAdminName()) ?>
                    </span>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                                <h3 class="mt-2"><?= number_format($stats['total_usuarios']) ?></h3>
                                <p class="text-muted mb-0">Total de Usuários</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-list-task text-info" style="font-size: 2rem;"></i>
                                <h3 class="mt-2"><?= number_format($stats['total_solicitacoes']) ?></h3>
                                <p class="text-muted mb-0">Solicitações</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-text text-warning" style="font-size: 2rem;"></i>
                                <h3 class="mt-2"><?= number_format($stats['total_propostas']) ?></h3>
                                <p class="text-muted mb-0">Propostas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                <h3 class="mt-2"><?= number_format($stats['servicos_concluidos']) ?></h3>
                                <p class="text-muted mb-0">Concluídos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="<?= url('admin/usuarios') ?>" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-people me-2"></i>Gerenciar Usuários
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= url('admin/solicitacoes') ?>" class="btn btn-outline-info w-100">
                                    <i class="bi bi-list-task me-2"></i>Ver Solicitações
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= url('admin/relatorios') ?>" class="btn btn-outline-success w-100">
                                    <i class="bi bi-graph-up me-2"></i>Relatórios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/admin.php';
?>