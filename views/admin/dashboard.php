<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover,
        .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .stats-widget {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid;
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        .stats-widget:hover {
            transform: translateY(-2px);
        }
        .activity-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-shield-check me-2"></i>
                            Admin Panel
                        </h4>
                        <p class="text-white-50 small">ChamaServiço</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/chamaservico/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/solicitacoes">
                                <i class="bi bi-list-task me-2"></i>
                                Solicitações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/tipos-servico">
                                <i class="bi bi-tools me-2"></i>
                                Tipos de Serviços
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/relatorios">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/configuracoes">
                                <i class="bi bi-gear me-2"></i>
                                Configurações
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-auto pt-4">
                        <div class="text-center">
                            <div class="text-white-50 small">
                                Logado como:
                            </div>
                            <div class="text-white fw-bold small">
                                <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin Sistema') ?>
                            </div>
                            <a href="/chamaservico/admin/logout" class="btn btn-outline-light btn-sm mt-2">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <h1 class="h2 text-dark">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard Administrativo
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>
                                Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['admin_flash'])): ?>
                    <?php $flash = $_SESSION['admin_flash']; unset($_SESSION['admin_flash']); ?>
                    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                        <?= htmlspecialchars($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estatísticas Principais -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #007bff;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total de Usuários
                                    </div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['total_usuarios'] ?? 0 ?>
                                    </div>
                                    <small class="text-success">
                                        <i class="bi bi-person-plus me-1"></i>
                                        <?= $stats['novos_usuarios_hoje'] ?? 0 ?> novos hoje
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #28a745;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Solicitações de Serviço
                                    </div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['total_solicitacoes'] ?? 0 ?>
                                    </div>
                                    <small class="text-warning">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $stats['solicitacoes_pendentes'] ?? 0 ?> pendentes
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-list-task fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #ffc107;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Propostas Enviadas
                                    </div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['total_propostas'] ?? 0 ?>
                                    </div>
                                    <small class="text-info">
                                        <i class="bi bi-envelope me-1"></i>
                                        Prestadores ativos
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-envelope fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #dc3545;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Valor Transacionado
                                    </div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        R$ <?= number_format($stats['valor_transacionado'] ?? 0, 2, ',', '.') ?>
                                    </div>
                                    <small class="text-success">
                                        <i class="bi bi-arrow-up me-1"></i>
                                        <?= $stats['servicos_concluidos'] ?? 0 ?> serviços
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Atividades Recentes e Alertas -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card activity-card mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 text-primary">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Atividades Recentes
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($atividadesRecentes)): ?>
                                    <div class="timeline">
                                        <?php foreach ($atividadesRecentes as $atividade): ?>
                                            <div class="timeline-item d-flex mb-3">
                                                <div class="timeline-marker me-3">
                                                    <?php if ($atividade['tipo'] === 'nova_solicitacao'): ?>
                                                        <i class="bi bi-plus-circle text-success fs-5"></i>
                                                    <?php elseif ($atividade['tipo'] === 'nova_proposta'): ?>
                                                        <i class="bi bi-envelope text-warning fs-5"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-person-plus text-info fs-5"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="fw-bold"><?= htmlspecialchars($atividade['descricao']) ?></div>
                                                    <small class="text-muted">
                                                        por <?= htmlspecialchars($atividade['usuario']) ?> • 
                                                        <?= date('d/m/Y H:i', strtotime($atividade['data_atividade'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-clock text-muted" style="font-size: 3rem;"></i>
                                        <h6 class="text-muted mt-2">Nenhuma atividade recente</h6>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Alertas -->
                        <div class="card activity-card mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 text-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Alertas
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($alertas)): ?>
                                    <?php foreach ($alertas as $alerta): ?>
                                        <div class="alert alert-<?= $alerta['tipo'] ?> py-2 px-3 mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="<?= $alerta['icone'] ?? 'bi bi-info-circle' ?> me-2"></i>
                                                <div>
                                                    <div class="fw-bold small"><?= htmlspecialchars($alerta['titulo']) ?></div>
                                                    <div class="small"><?= htmlspecialchars($alerta['mensagem']) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                        <div class="text-muted small mt-1">Nenhum alerta</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="card activity-card">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 text-primary">
                                    <i class="bi bi-lightning me-2"></i>
                                    Ações Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="/chamaservico/admin/usuarios" class="btn btn-outline-primary">
                                        <i class="bi bi-people me-2"></i>
                                        Gerenciar Usuários
                                    </a>
                                    <a href="/chamaservico/admin/solicitacoes" class="btn btn-outline-success">
                                        <i class="bi bi-list-task me-2"></i>
                                        Ver Solicitações
                                    </a>
                                    <a href="/chamaservico/admin/tipos-servico" class="btn btn-outline-warning">
                                        <i class="bi bi-tools me-2"></i>
                                        Tipos de Serviços
                                    </a>
                                    <a href="/chamaservico/admin/relatorios" class="btn btn-outline-info">
                                        <i class="bi bi-graph-up me-2"></i>
                                        Relatórios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>