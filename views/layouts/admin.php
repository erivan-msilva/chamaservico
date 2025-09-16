<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard - ChamaServiço' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
            padding: 12px 16px;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.25) !important;
            border-left: 4px solid #fff;
            font-weight: 600;
        }
        
        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 0;
        }

        .admin-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .admin-brand h4 {
            color: white;
            margin: 0;
            font-weight: 700;
        }

        .admin-brand small {
            color: rgba(255,255,255,0.7);
        }

        .nav-section-title {
            color: rgba(255,255,255,0.6) !important;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem 0.25rem 1rem;
            margin-top: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-section-title:first-child {
            margin-top: 0;
        }

        .admin-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        .admin-info {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .admin-info strong {
            color: white;
        }

        .btn-logout {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <nav class="sidebar d-flex flex-column">
                    <div class="admin-brand">
                        <h4>
                            <i class="bi bi-shield-check me-2"></i>
                            Admin Panel
                        </h4>
                        <small>ChamaServiço</small>
                    </div>
                    
                    <div class="flex-grow-1">
                        <ul class="nav flex-column">
                            <!-- SEÇÃO: PAINEL -->
                            <li class="nav-section-title">
                                <i class="bi bi-speedometer2 me-1"></i>Painel
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" 
                                   href="<?= url('admin/dashboard') ?>">
                                    <i class="bi bi-speedometer2 me-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            
                            <!-- SEÇÃO: GESTÃO -->
                            <li class="nav-section-title">
                                <i class="bi bi-gear me-1"></i>Gestão
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'solicitacoes' ? 'active' : '' ?>" 
                                   href="<?= url('admin/solicitacoes') ?>">
                                    <i class="bi bi-list-task me-2"></i>
                                    Solicitações
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'usuarios' ? 'active' : '' ?>" 
                                   href="<?= url('admin/usuarios') ?>">
                                    <i class="bi bi-people me-2"></i>
                                    Usuários
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'tipos-servico' ? 'active' : '' ?>" 
                                   href="<?= url('admin/tipos-servico') ?>">
                                    <i class="bi bi-tools me-2"></i>
                                    Tipos de Serviços
                                </a>
                            </li>
                            
                            <!-- SEÇÃO: ANÁLISE -->
                            <li class="nav-section-title">
                                <i class="bi bi-graph-up me-1"></i>Análise
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'relatorios' ? 'active' : '' ?>" 
                                   href="<?= url('admin/relatorios') ?>">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Relatórios
                                </a>
                            </li>
                            
                            <!-- SEÇÃO: SISTEMA -->
                            <li class="nav-section-title">
                                <i class="bi bi-gear-fill me-1"></i>Sistema
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage ?? '') === 'configuracoes' ? 'active' : '' ?>" 
                                   href="<?= url('admin/configuracoes') ?>">
                                    <i class="bi bi-gear me-2"></i>
                                    Configurações
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="admin-footer">
                        <div class="admin-info">
                            Logado como:<br>
                            <strong><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin Sistema') ?></strong>
                        </div>
                        <div class="text-center">
                            <a href="<?= url('admin/logout') ?>" class="btn-logout">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10">
                <main class="main-content">
                    <div class="p-4">
                        <?= $content ?? '' ?>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts específicos das páginas -->
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>

    <!-- Scripts globais do admin -->
    <script>
        // Auto-remover alertas após 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Confirmação para logout
        document.querySelector('.btn-logout')?.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja sair do painel administrativo?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
