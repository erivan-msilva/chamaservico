<?php
//session_start();

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}

$title = 'Dashboard Administrativo - ChamaServiço';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .navbar-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/chamaservico/admin/dashboard">
                <i class="bi bi-shield-check me-2"></i>Admin ChamaServiço
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['admin_nome']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/chamaservico/admin/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Administrativo
                </h2>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?= $stats['total_usuarios'] ?? 0 ?></h4>
                                        <p class="mb-0">Total de Usuários</p>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?= $stats['total_clientes'] ?? 0 ?></h4>
                                        <p class="mb-0">Clientes</p>
                                    </div>
                                    <i class="bi bi-person" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?= $stats['total_prestadores'] ?? 0 ?></h4>
                                        <p class="mb-0">Prestadores</p>
                                    </div>
                                    <i class="bi bi-tools" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card dashboard-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?= $stats['solicitacoes_hoje'] ?? 0 ?></h4>
                                        <p class="mb-0">Solicitações Hoje</p>
                                    </div>
                                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ações Rápidas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-lightning me-2"></i>Ações Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <a href="/chamaservico/admin/usuarios" class="btn btn-outline-primary w-100 p-3">
                                            <i class="bi bi-people d-block mb-2" style="font-size: 2rem;"></i>
                                            <strong>Gerenciar Usuários</strong>
                                            <small class="d-block text-muted">Visualizar e editar usuários</small>
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <a href="/chamaservico/admin/servicos" class="btn btn-outline-success w-100 p-3">
                                            <i class="bi bi-list-task d-block mb-2" style="font-size: 2rem;"></i>
                                            <strong>Serviços</strong>
                                            <small class="d-block text-muted">Gerenciar solicitações</small>
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <a href="/chamaservico/admin/relatorios" class="btn btn-outline-info w-100 p-3">
                                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 2rem;"></i>
                                            <strong>Relatórios</strong>
                                            <small class="d-block text-muted">Estatísticas e relatórios</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Sistema -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Informações do Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Versão do Sistema:</strong> 1.0.0</p>
                                        <p><strong>Administrador:</strong> <?= htmlspecialchars($_SESSION['admin_nome']) ?></p>
                                        <p><strong>Nível de Acesso:</strong> <?= ucfirst($_SESSION['admin_nivel']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total de Admins:</strong> <?= $stats['total_admins'] ?? 0 ?></p>
                                        <p><strong>Usuários Ativos:</strong> <?= $stats['usuarios_ativos'] ?? 0 ?></p>
                                        <p><strong>Cadastros Hoje:</strong> <?= $stats['cadastros_hoje'] ?? 0 ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>