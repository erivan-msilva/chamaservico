<?php
//session_start();

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ChamaServiço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #2c3e50;
            --admin-secondary: #34495e;
            --admin-accent: #3498db;
            --admin-success: #27ae60;
            --admin-warning: #f39c12;
            --admin-danger: #e74c3c;
            --sidebar-width: 280px;
            --topbar-height: 70px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #2d3748;
            overflow-x: hidden;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0,0,0,0.1);
        }

        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .sidebar-brand:hover {
            color: var(--admin-accent);
            transform: translateX(5px);
        }

        .admin-info {
            padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.15);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            background: var(--admin-accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            margin-right: 0.75rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .sidebar-nav {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }

        .nav-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.6);
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .nav-section:first-child {
            margin-top: 0;
        }

        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 2px 12px;
            border-radius: 8px;
            font-weight: 500;
            position: relative;
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar-nav .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .sidebar-nav .nav-link.active {
            color: white;
            background: var(--admin-accent);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: white;
            border-radius: 2px;
        }

        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-topbar {
            background: white;
            height: var(--topbar-height);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--admin-primary);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .content-area {
            padding: 2rem;
        }

        /* Cards e Componentes */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--admin-primary);
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
            /* Adicionado para controlar o canvas */
            position: relative;
            min-height: 350px;
            max-height: 450px;
        }

        .chart-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--admin-primary);
            margin-bottom: 1rem;
        }

        /* Correção específica para o canvas do Chart.js */
        .chart-container canvas {
            max-height: 300px !important;
            width: 100% !important;
            height: auto !important;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--admin-primary);
        }

        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: block;
            }
        }

        /* Botões personalizados */
        .btn-admin {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="/chamaservico/admin/dashboard" class="sidebar-brand">
                <i class="bi bi-shield-shaded me-3"></i>
                ChamaServiço Admin
            </a>
        </div>

        <div class="admin-info">
            <div class="d-flex align-items-center">
                <div class="admin-avatar">
                    <?= strtoupper(substr($_SESSION['admin_nome'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                    <div class="text-white fw-semibold"><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?></div>
                    <small class="text-white-50"><?= ucfirst($_SESSION['admin_nivel'] ?? 'admin') ?></small>
                </div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-section">Principal</li>
            <li>
                <a class="nav-link active" href="#" onclick="loadPage('dashboard')">
                    <i class="bi bi-grid-1x2"></i>Dashboard
                </a>
            </li>
            
            <li class="nav-section">Gerenciamento</li>
            <li>
                <a class="nav-link" href="#" onclick="loadPage('usuarios')">
                    <i class="bi bi-people"></i>Usuários
                </a>
            </li>
            <li>
                <a class="nav-link" href="#" onclick="loadPage('tipos-servico')">
                    <i class="bi bi-tools"></i>Tipos de Serviço
                </a>
            </li>
            <li>
                <a class="nav-link" href="#" onclick="loadPage('status-solicitacao')">
                    <i class="bi bi-flag"></i>Status Solicitação
                </a>
            </li>
            
            <li class="nav-section">Análise</li>
            <li>
                <a class="nav-link" href="#" onclick="loadPage('relatorios')">
                    <i class="bi bi-graph-up-arrow"></i>Relatórios
                </a>
            </li>
            <li>
                <a class="nav-link" href="#" onclick="loadPage('monitor')">
                    <i class="bi bi-activity"></i>Monitor
                </a>
            </li>
            
            <li class="nav-section">Sistema</li>
            <li>
                <a class="nav-link text-danger" href="#" onclick="logout()">
                    <i class="bi bi-box-arrow-right"></i>Sair
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="main-topbar">
            <div class="d-flex align-items-center">
                <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="page-title" id="pageTitle">Dashboard Administrativo</h1>
                    <p class="page-subtitle">Visão geral do sistema ChamaServiço</p>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn btn-outline-primary btn-admin btn-sm" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
                </button>
                <button class="btn btn-outline-danger btn-admin btn-sm" onclick="logout()">
                    <i class="bi bi-box-arrow-right me-1"></i>Sair
                </button>
            </div>
        </header>

        <div class="content-area" id="mainContent">
            <!-- Dashboard Content -->
            <div id="dashboardContent">
                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-value" id="totalUsuarios">0</div>
                            <div class="stat-label">Total de Usuários</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon bg-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-value" id="usuariosAtivos">0</div>
                            <div class="stat-label">Usuários Ativos</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="stat-value" id="solicitacoesHoje">0</div>
                            <div class="stat-label">Solicitações Hoje</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--admin-accent);">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="stat-value" id="valorTotalMes">R$ 0</div>
                            <div class="stat-label">Faturamento Mensal</div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <h5 class="chart-title">Crescimento de Usuários</h5>
                            <div style="position: relative; height: 300px;">
                                <canvas id="chartUsuarios"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <h5 class="chart-title">Distribuição de Usuários</h5>
                            <div style="position: relative; height: 300px;">
                                <canvas id="chartTiposUsuarios"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="chart-container">
                    <h5 class="chart-title">Atividade Recente</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivity">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'layouts/modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 'dashboard';

        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
        }

        function loadPage(page) {
            currentPage = page;

            // Update active nav
            document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');

            // Update page title
            const titles = {
                'dashboard': 'Dashboard Administrativo',
                'usuarios': 'Gerenciar Usuários',
                'tipos-servico': 'Tipos de Serviço',
                'status-solicitacao': 'Status de Solicitação',
                'relatorios': 'Relatórios e Análises',
                'monitor': 'Monitor do Sistema'
            };

            document.getElementById('pageTitle').textContent = titles[page] || page;

            if (page === 'dashboard') {
                showDashboard();
            } else {
                loadPageContent(page);
            }
        }

        function showDashboard() {
            document.getElementById('mainContent').innerHTML = document.getElementById('dashboardContent').innerHTML;
            loadStats();
            initCharts();
        }

        function loadPageContent(page) {
            const pages = {
                'usuarios': '/chamaservico/views/admin/usuarios.php',
                'monitor': '/chamaservico/views/admin/monitor.php'
            };

            if (pages[page]) {
                fetch(pages[page])
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('mainContent').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('mainContent').innerHTML = `
                            <div class="chart-container text-center">
                                <h5>Página em Desenvolvimento</h5>
                                <p class="text-muted">Esta funcionalidade está sendo implementada.</p>
                            </div>
                        `;
                    });
            } else {
                document.getElementById('mainContent').innerHTML = `
                    <div class="chart-container text-center">
                        <h5>Página em Desenvolvimento</h5>
                        <p class="text-muted">Esta funcionalidade está sendo implementada.</p>
                    </div>
                `;
            }
        }

        async function loadStats() {
            try {
                const response = await fetch('/chamaservico/admin/api/dashboard');
                const data = await response.json();

                if (data.sucesso) {
                    const stats = data.dados;
                    document.getElementById('totalUsuarios').textContent = stats.total_usuarios || 0;
                    document.getElementById('usuariosAtivos').textContent = stats.usuarios_ativos || 0;
                    document.getElementById('solicitacoesHoje').textContent = stats.solicitacoes_hoje || 0;
                    document.getElementById('valorTotalMes').textContent =
                        'R$ ' + new Intl.NumberFormat('pt-BR').format(stats.valor_total_mes || 0);
                }
            } catch (error) {
                console.error('Erro ao carregar estatísticas:', error);
            }
        }

        function initCharts() {
            // Chart de crescimento de usuários
            const ctxUsuarios = document.getElementById('chartUsuarios');
            if (ctxUsuarios) {
                new Chart(ctxUsuarios, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                        datasets: [{
                            label: 'Novos Usuários',
                            data: [12, 19, 15, 25, 22, 30],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                max: 35 // Definir um máximo para controlar a altura
                            }
                        }
                    }
                });
            }

            // Chart de tipos de usuários
            const ctxTipos = document.getElementById('chartTiposUsuarios');
            if (ctxTipos) {
                new Chart(ctxTipos, {
                    type: 'doughnut',
                    data: {
                        labels: ['Clientes', 'Prestadores', 'Ambos'],
                        datasets: [{
                            data: [45, 35, 20],
                            backgroundColor: ['#3498db', '#27ae60', '#f39c12']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }

        function refreshData() {
            loadStats();
            showToast('Dados atualizados com sucesso!');
        }

        function logout() {
            Swal.fire({
                title: 'Sair do sistema?',
                text: 'Tem certeza que deseja encerrar sua sessão?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, sair!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/chamaservico/admin/logout';
                }
            });
        }

        function showToast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            initCharts();
        });
    </script>
</body>

</html>