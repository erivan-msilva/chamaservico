<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Variáveis dinâmicas para notificações
$novasSolicitacoes = $novasSolicitacoes ?? 3; // Esta variável viria do controller/model
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin - ChamaServiço' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
        }

        /* MELHORIAS DO MENU - Agrupamento Lógico */
        .nav-section-title {
            color: rgba(255, 255, 255, 0.6) !important;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem 0.25rem 1rem;
            margin-top: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: default;
        }
        
        .nav-section-title:first-child {
            margin-top: 0;
        }
        
        /* Melhorias no Link Ativo */
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15) !important;
            border-left: 3px solid #fff !important;
            margin-left: 0 !important;
            padding-left: calc(1rem - 3px) !important;
            position: relative;
        }
        
        /* Badge de Notificação */
        .notification-badge {
            background: #dc3545 !important;
            color: white !important;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Estilos específicos da página */
        <?= $styles ?? '' ?>
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
                    
                    <!-- MENU MELHORADO COM AGRUPAMENTO LÓGICO -->
                    <ul class="nav flex-column">
                        <!-- SEÇÃO: PAINEL -->
                        <li class="nav-section-title">
                            <i class="bi bi-speedometer2 me-1"></i>Painel
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/chamaservico/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: GESTÃO -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear me-1"></i>Gestão
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= ($currentPage ?? '') === 'solicitacoes' ? 'active' : '' ?>" href="/chamaservico/admin/solicitacoes">
                                <span>
                                    <i class="bi bi-list-task me-2"></i>
                                    Solicitações
                                </span>
                                <?php if ($novasSolicitacoes > 0): ?>
                                    <span class="notification-badge"><?= $novasSolicitacoes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'usuarios' ? 'active' : '' ?>" href="/chamaservico/admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'tipos-servico' ? 'active' : '' ?>" href="/chamaservico/admin/tipos-servico">
                                <i class="bi bi-tools me-2"></i>
                                Tipos de Serviços
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: ANÁLISE -->
                        <li class="nav-section-title">
                            <i class="bi bi-graph-up me-1"></i>Análise
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'relatorios' ? 'active' : '' ?>" href="/chamaservico/admin/relatorios">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: SISTEMA -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear-fill me-1"></i>Sistema
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'configuracoes' ? 'active' : '' ?>" href="/chamaservico/admin/configuracoes">
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
                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>