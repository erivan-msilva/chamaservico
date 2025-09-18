<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// CORREÇÃO: Definir tipo de visualização baseado no parâmetro GET
$tipoVisualizacao = $_GET['view'] ?? 'kanban'; // Mudando padrão para kanban

// CORREÇÃO: Buscar dados reais se não existirem
if (!isset($solicitacoes)) {
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        
        $sql = "SELECT s.*, p.nome as cliente_nome, p.email as cliente_email, 
                       ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor,
                       (SELECT COUNT(*) FROM tb_proposta pr WHERE pr.solicitacao_id = s.id) as total_propostas
                FROM tb_solicita_servico s
                LEFT JOIN tb_pessoa p ON s.cliente_id = p.id
                LEFT JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                LEFT JOIN tb_status_solicitacao st ON s.status_id = st.id
                ORDER BY s.data_solicitacao DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar solicitações: " . $e->getMessage());
        $solicitacoes = [];
    }
}

if (!isset($estatisticas)) {
    try {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 1");
        $stmt->execute();
        $aguardando = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 5");
        $stmt->execute();
        $concluidas = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 6");
        $stmt->execute();
        $canceladas = $stmt->fetchColumn();
        
        $estatisticas = [
            'total_solicitacoes' => $total,
            'aguardando_propostas' => $aguardando,
            'concluidas' => $concluidas,
            'canceladas' => $canceladas
        ];
        
    } catch (Exception $e) {
        $estatisticas = [
            'total_solicitacoes' => 0,
            'aguardando_propostas' => 0,
            'concluidas' => 0,
            'canceladas' => 0
        ];
    }
}

if (!isset($statusList)) {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, nome, cor FROM tb_status_solicitacao ORDER BY id");
        $stmt->execute();
        $statusList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $statusList = [];
    }
}

if (!isset($tiposServico)) {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, nome FROM tb_tipo_servico WHERE ativo = 1 ORDER BY nome");
        $stmt->execute();
        $tiposServico = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $tiposServico = [];
    }
}

$filtros = $filtros ?? [];

// Simular notificações dinâmicas para demonstração
$novasSolicitacoes = 3; // Esta variável viria do controller/model
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações - Admin</title>
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
            background: #f8fafc;
            min-height: 100vh;
        }

        /* ===== KANBAN BOARD SYSTEM ===== */
        .kanban-container {
            padding: 0 16px;
            overflow-x: auto;
        }
        
        .kanban-board {
            display: flex;
            gap: 20px;
            min-height: 70vh;
            padding-bottom: 20px;
        }
        
        .kanban-column {
            background: #f1f5f9;
            border-radius: 12px;
            min-width: 300px;
            max-width: 350px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .kanban-column.drag-over {
            border-color: #3b82f6;
            background: #eff6ff;
            transform: scale(1.02);
        }
        
        .kanban-header {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .kanban-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .kanban-count {
            background: #e2e8f0;
            color: #64748b;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .kanban-cards {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            max-height: calc(70vh - 80px);
        }

        /* ===== REDESIGNED SOLICITACAO CARDS ===== */
        .solicitacao-card-kanban {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .solicitacao-card-kanban:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #cbd5e1;
        }
        
        .solicitacao-card-kanban.dragging {
            opacity: 0.7;
            transform: rotate(3deg) scale(1.05);
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        /* Priority Stripe */
        .card-priority-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 12px 12px 0 0;
        }
        
        .priority-alta { background: #dc2626; }
        .priority-media { background: #d97706; }
        .priority-baixa { background: #059669; }
        
        /* Card Header */
        .card-header-minimal {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-top: 4px;
        }
        
        .card-id-badge {
            font-size: 0.7rem;
            color: #64748b;
            background: #f8fafc;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .card-kebab-menu {
            position: relative;
        }
        
        .kebab-trigger {
            background: none;
            border: none;
            color: #9ca3af;
            padding: 4px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .kebab-trigger:hover {
            background: #f1f5f9;
            color: #64748b;
        }
        
        /* Card Content */
        .card-title-clean {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-client-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }
        
        .card-client-info i {
            font-size: 12px;
        }
        
        /* Card Metadata Row */
        .card-metadata {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: auto;
        }
        
        .card-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .card-micro-badge {
            font-size: 0.65rem;
            padding: 3px 6px;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .badge-proposals {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-urgency-alta {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .badge-urgency-media {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-urgency-baixa {
            background: #dcfce7;
            color: #059669;
        }
        
        .card-date {
            font-size: 0.7rem;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* ===== SMART FILTER PANEL ===== */
        .filter-toggle-btn {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px 0 0 8px;
            padding: 16px 8px;
            writing-mode: vertical-lr;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            z-index: 1040;
            transition: all 0.3s ease;
            box-shadow: -4px 0 10px rgba(0,0,0,0.1);
        }
        
        .filter-toggle-btn:hover {
            background: #2563eb;
            padding-right: 12px;
        }
        
        .filter-offcanvas {
            width: 380px !important;
        }
        
        .saved-filters-section {
            margin-bottom: 24px;
        }
        
        .saved-filter-chip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .saved-filter-chip:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        
        .saved-filter-chip.active {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        
        .quick-filters-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .quick-filter-btn {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        
        .quick-filter-btn:hover,
        .quick-filter-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* ===== MASTER-DETAIL PANEL ===== */
        .detail-offcanvas {
            width: 50% !important;
            min-width: 600px;
        }
        
        @media (max-width: 1200px) {
            .detail-offcanvas {
                width: 70% !important;
                min-width: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .detail-offcanvas {
                width: 100% !important;
                min-width: 100%;
            }
        }
        
        .detail-header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            margin: -16px -16px 24px -16px;
        }
        
        .detail-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 8px;
        }
        
        .detail-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }
        
        .detail-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== VIEW CONTROLS REDESIGN ===== */
        .view-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .view-toggle-modern {
            display: flex;
            background: white;
            border-radius: 8px;
            padding: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .view-toggle-modern .btn {
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
            color: #64748b;
        }
        
        .view-toggle-modern .btn.active {
            background: #3b82f6;
            color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* ===== ENHANCED STATS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card-compact {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card-compact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--stat-color, #3b82f6);
        }
        
        .stat-card-compact:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .kanban-board {
                flex-direction: column;
                gap: 16px;
            }
            
            .kanban-column {
                min-width: 100%;
                max-width: 100%;
            }
            
            .filter-offcanvas {
                width: 100% !important;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes cardSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-animate-in {
            animation: cardSlideIn 0.3s ease;
        }

        /* MELHORIAS DO MENU - Agrupamento Lógico */
        .nav-section-title {
            color: rgba(255,255,255,0.6) !important;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem 0.25rem 1rem;
            margin-top: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            cursor: default;
        }
        
        .nav-section-title:first-child {
            margin-top: 0;
        }
        
        /* Melhorias no Link Ativo */
        .nav-link.active {
            background: rgba(255,255,255,0.15) !important;
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
                            <a class="nav-link" href="<?= url('admin/dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: GESTÃO -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear me-1"></i>Gestão
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center active" href="<?= url('admin/solicitacoes') ?>">
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
                            <a class="nav-link" href="<?= url('admin/usuarios') ?>">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/tipos-servico') ?>">
                                <i class="bi bi-tools me-2"></i>
                                Tipos de Serviços
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: ANÁLISE -->
                        <li class="nav-section-title">
                            <i class="bi bi-graph-up me-1"></i>Análise
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/relatorios') ?>">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: SISTEMA -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear-fill me-1"></i>Sistema
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/configuracoes') ?>">
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
                            <a href="<?= url('admin/logout') ?>" class="btn btn-outline-light btn-sm mt-2">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Enhanced Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <div>
                        <h1 class="h2 text-dark mb-1">
                            <i class="bi bi-<?= $tipoVisualizacao === 'kanban' ? 'kanban' : ($tipoVisualizacao === 'table' ? 'table' : 'grid-3x3-gap') ?> me-2"></i>
                            Central de Solicitações
                        </h1>
                        <p class="text-muted mb-0">Gerencie o fluxo de trabalho das solicitações de serviço</p>
                    </div>
                    
                    <!-- Enhanced View Controls -->
                    <div class="view-controls">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                            <i class="bi bi-funnel me-1"></i>Filtros
                        </button>
                        
                        <div class="view-toggle-modern">
                            <a href="?view=kanban<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['view' => ''])) : '' ?>" 
                               class="btn <?= $tipoVisualizacao === 'kanban' ? 'active' : '' ?>">
                                <i class="bi bi-kanban me-1"></i>Kanban
                            </a>
                            <a href="?view=cards<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['view' => ''])) : '' ?>" 
                               class="btn <?= $tipoVisualizacao === 'cards' ? 'active' : '' ?>">
                                <i class="bi bi-grid-3x3-gap me-1"></i>Cards
                            </a>
                            <a href="?view=table<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['view' => ''])) : '' ?>" 
                               class="btn <?= $tipoVisualizacao === 'table' ? 'active' : '' ?>">
                                <i class="bi bi-table me-1"></i>Lista
                            </a>
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

                <!-- Compact Stats -->
                <div class="stats-row">
                    <div class="stat-card-compact" style="--stat-color: #3b82f6;">
                        <div class="stat-number"><?= $estatisticas['total_solicitacoes'] ?></div>
                        <div class="stat-label">Total de Solicitações</div>
                    </div>
                    <div class="stat-card-compact" style="--stat-color: #d97706;">
                        <div class="stat-number text-warning"><?= $estatisticas['aguardando_propostas'] ?></div>
                        <div class="stat-label">Aguardando Propostas</div>
                    </div>
                    <div class="stat-card-compact" style="--stat-color: #059669;">
                        <div class="stat-number text-success"><?= $estatisticas['concluidas'] ?></div>
                        <div class="stat-label">Concluídas</div>
                    </div>
                    <div class="stat-card-compact" style="--stat-color: #dc2626;">
                        <div class="stat-number text-danger"><?= $estatisticas['canceladas'] ?></div>
                        <div class="stat-label">Canceladas</div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="content-area">
                    <?php if (empty($solicitacoes)): ?>
                        <div class="text-center py-5">
                            <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h4 style="color: #64748b; font-weight: 600;">Nenhuma solicitação encontrada</h4>
                            <p style="color: #94a3b8;">
                                <?php if (!empty(array_filter($filtros))): ?>
                                    Ajuste os filtros ou aguarde novas solicitações.
                                <?php else: ?>
                                    Ainda não há solicitações no sistema.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        
                        <?php if ($tipoVisualizacao === 'kanban'): ?>
                            <!-- KANBAN BOARD -->
                            <div class="kanban-container">
                                <div class="kanban-board" id="kanbanBoard">
                                    <?php 
                                    // Organizar solicitações por status
                                    $statusColumns = [];
                                    foreach ($statusList as $status) {
                                        $statusColumns[$status['id']] = [
                                            'status' => $status,
                                            'solicitacoes' => []
                                        ];
                                    }
                                    
                                    foreach ($solicitacoes as $solicitacao) {
                                        $statusId = $solicitacao['status_id'] ?? 1;
                                        if (isset($statusColumns[$statusId])) {
                                            $statusColumns[$statusId]['solicitacoes'][] = $solicitacao;
                                        }
                                    }
                                    ?>
                                    
                                    <?php foreach ($statusColumns as $columnData): ?>
                                        <div class="kanban-column" data-status-id="<?= $columnData['status']['id'] ?>">
                                            <div class="kanban-header">
                                                <div class="kanban-title">
                                                    <div class="status-indicator">
                                                        <div class="status-dot" style="background-color: <?= $columnData['status']['cor'] ?>;"></div>
                                                        <?= htmlspecialchars($columnData['status']['nome']) ?>
                                                    </div>
                                                    <div class="kanban-count"><?= count($columnData['solicitacoes']) ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="kanban-cards" data-status-id="<?= $columnData['status']['id'] ?>">
                                                <?php foreach ($columnData['solicitacoes'] as $solicitacao): ?>
                                                    <div class="solicitacao-card-kanban card-animate-in" 
                                                         draggable="true" 
                                                         data-solicitacao-id="<?= $solicitacao['id'] ?>"
                                                         onclick="openDetailPanel(<?= $solicitacao['id'] ?>)">
                                                        
                                                        <!-- Priority Stripe -->
                                                        <div class="card-priority-stripe priority-<?= $solicitacao['urgencia'] ?? 'media' ?>"></div>
                                                        
                                                        <!-- Card Header -->
                                                        <div class="card-header-minimal">
                                                            <span class="card-id-badge">#<?= $solicitacao['id'] ?></span>
                                                            <div class="card-kebab-menu">
                                                                <button class="kebab-trigger" onclick="event.stopPropagation(); toggleKebabMenu(<?= $solicitacao['id'] ?>)">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu" id="kebab-<?= $solicitacao['id'] ?>" style="display: none;">
                                                                    <a class="dropdown-item" href="<?= url('admin/solicitacoes/visualizar?id=' . $solicitacao['id']) ?>">
                                                                        <i class="bi bi-eye me-2"></i>Ver Detalhes Completos
                                                                    </a>
                                                                    <a class="dropdown-item" href="mailto:<?= htmlspecialchars($solicitacao['cliente_email'] ?? '') ?>">
                                                                        <i class="bi bi-envelope me-2"></i>Enviar Email
                                                                    </a>
                                                                    <hr class="dropdown-divider">
                                                                    <h6 class="dropdown-header">Alterar Status</h6>
                                                                    <?php foreach ($statusList as $status): ?>
                                                                        <?php if ($status['id'] != ($solicitacao['status_id'] ?? 0)): ?>
                                                                            <button class="dropdown-item" onclick="changeStatus(<?= $solicitacao['id'] ?>, <?= $status['id'] ?>, '<?= addslashes($status['nome']) ?>')">
                                                                                <div class="status-dot me-2" style="background-color: <?= $status['cor'] ?>; width: 8px; height: 8px;"></div>
                                                                                <?= htmlspecialchars($status['nome']) ?>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Card Title -->
                                                        <h6 class="card-title-clean"><?= htmlspecialchars($solicitacao['titulo'] ?? 'Sem título') ?></h6>
                                                        
                                                        <!-- Client Info -->
                                                        <div class="card-client-info">
                                                            <i class="bi bi-person-circle"></i>
                                                            <span><?= htmlspecialchars(explode(' ', $solicitacao['cliente_nome'] ?? 'Cliente')[0]) ?></span>
                                                        </div>
                                                        
                                                        <!-- Card Metadata -->
                                                        <div class="card-metadata">
                                                            <div class="card-badges">
                                                                <?php if (($solicitacao['total_propostas'] ?? 0) > 0): ?>
                                                                    <span class="card-micro-badge badge-proposals">
                                                                        <i class="bi bi-file-text"></i>
                                                                        <?= $solicitacao['total_propostas'] ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                
                                                                <span class="card-micro-badge badge-urgency-<?= $solicitacao['urgencia'] ?? 'media' ?>">
                                                                    <?= ucfirst($solicitacao['urgencia'] ?? 'Média') ?>
                                                                </span>
                                                            </div>
                                                            
                                                            <div class="card-date">
                                                                <?= date('d/m', strtotime($solicitacao['data_solicitacao'])) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                        <?php elseif ($tipoVisualizacao === 'cards'): ?>
                            <!-- CARDS VIEW -->
                            <div class="row">
                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100" style="background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border: none; transition: all 0.3s ease;">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    #<?= $solicitacao['id'] ?> - <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                                                </small>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($solicitacao['status_cor'] ?? '#6c757d') ?>;">
                                                    <?= htmlspecialchars($solicitacao['status_nome'] ?? 'Sem status') ?>
                                                </span>
                                            </div>

                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($solicitacao['titulo'] ?? 'Sem título') ?></h6>

                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-person me-1"></i>
                                                    <?= htmlspecialchars($solicitacao['cliente_nome'] ?? 'Cliente não identificado') ?>
                                                </p>

                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-tools me-1"></i>
                                                    <?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'Tipo não identificado') ?>
                                                </p>

                                                <p class="card-text small text-muted">
                                                    <?= htmlspecialchars(substr($solicitacao['descricao'] ?? 'Sem descrição', 0, 100)) ?>...
                                                </p>

                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                                        <?= ucfirst($solicitacao['urgencia'] ?? 'Média') ?>
                                                    </span>

                                                    <?php if (($solicitacao['total_propostas'] ?? 0) > 0): ?>
                                                        <span class="badge bg-secondary">
                                                            <?= $solicitacao['total_propostas'] ?> proposta<?= $solicitacao['total_propostas'] > 1 ? 's' : '' ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($solicitacao['orcamento_estimado'])): ?>
                                                    <div class="text-success small mb-2">
                                                        <i class="bi bi-cash-coin me-1"></i>
                                                        R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="card-footer bg-transparent">
                                                <div class="d-flex gap-1">
                                                    <a href="<?= url('admin/solicitacoes/visualizar?id=' . $solicitacao['id']) ?>"
                                                        class="btn btn-outline-primary btn-sm flex-fill">
                                                        <i class="bi bi-eye me-1"></i>Ver
                                                    </a>

                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-gear"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <h6 class="dropdown-header">Alterar Status</h6>
                                                            </li>
                                                            <?php foreach ($statusList as $status): ?>
                                                                <?php if ($status['id'] != ($solicitacao['status_id'] ?? 0)): ?>
                                                                    <li>
                                                                        <button class="dropdown-item"
                                                                            onclick="alterarStatus(<?= $solicitacao['id'] ?>, <?= $status['id'] ?>, '<?= addslashes($status['nome']) ?>')">
                                                                            <span class="badge me-2" style="background-color: <?= $status['cor'] ?>;">&nbsp;</span>
                                                                            <?= htmlspecialchars($status['nome']) ?>
                                                                        </button>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($tipoVisualizacao === 'table'): ?>
                            <!-- TABLE VIEW -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th width="8%">ID</th>
                                                    <th width="25%">Título</th>
                                                    <th width="20%">Cliente</th>
                                                    <th width="15%">Tipo</th>
                                                    <th width="12%">Status</th>
                                                    <th width="10%">Urgência</th>
                                                    <th width="10%">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-secondary">#<?= $solicitacao['id'] ?></span>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong><?= htmlspecialchars($solicitacao['titulo'] ?? 'Sem título') ?></strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                                                                </small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <div class="fw-semibold"><?= htmlspecialchars($solicitacao['cliente_nome'] ?? 'N/A') ?></div>
                                                                <small class="text-muted"><?= htmlspecialchars($solicitacao['cliente_email'] ?? '') ?></small>
                                                            </div>
                                                        </td>
                                                        <td><?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <span class="badge" style="background-color: <?= htmlspecialchars($solicitacao['status_cor'] ?? '#6c757d') ?>;">
                                                                <?= htmlspecialchars($solicitacao['status_nome'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                                                <?= ucfirst($solicitacao['urgencia'] ?? 'Média') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="<?= url('admin/solicitacoes/visualizar?id=' . $solicitacao['id']) ?>"
                                                                    class="btn btn-outline-primary" title="Visualizar">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                <button class="btn btn-outline-secondary dropdown-toggle"
                                                                    data-bs-toggle="dropdown" title="Mais ações">
                                                                    <i class="bi bi-three-dots"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a class="dropdown-item" href="mailto:<?= htmlspecialchars($solicitacao['cliente_email'] ?? '') ?>">
                                                                            <i class="bi bi-envelope me-2"></i>Email Cliente
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <h6 class="dropdown-header">Alterar Status</h6>
                                                                    </li>
                                                                    <?php foreach ($statusList as $status): ?>
                                                                        <?php if ($status['id'] != ($solicitacao['status_id'] ?? 0)): ?>
                                                                            <li>
                                                                                <button class="dropdown-item"
                                                                                    onclick="alterarStatus(<?= $solicitacao['id'] ?>, <?= $status['id'] ?>, '<?= addslashes($status['nome']) ?>')">
                                                                                <span class="badge me-2" style="background-color: <?= $status['cor'] ?>;">&nbsp;</span>
                                                                                <?= htmlspecialchars($status['nome']) ?>
                                                                                </button>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- FALLBACK PARA VIEW DESCONHECIDA -->
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Tipo de visualização "<?= htmlspecialchars($tipoVisualizacao) ?>" não reconhecido. 
                                <a href="?view=kanban" class="alert-link">Clique aqui para voltar ao Kanban</a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mt-4 p-4" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="color: #64748b; font-size: 0.875rem;">
                                <strong><?= count($solicitacoes) ?></strong> solicitação<?= count($solicitacoes) != 1 ? 'ões' : '' ?> encontrada<?= count($solicitacoes) != 1 ? 's' : '' ?>
                                <span class="text-muted">• Visualização: <strong><?= ucfirst($tipoVisualizacao) ?></strong></span>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm" onclick="exportarDados()">
                                    <i class="bi bi-download me-1"></i>Exportar
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="atualizarDados()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Smart Filter Offcanvas -->
    <div class="offcanvas offcanvas-end filter-offcanvas" tabindex="-1" id="filterOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Filtros Avançados</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Saved Filters -->
            <div class="saved-filters-section">
                <h6 class="text-uppercase text-muted small mb-3">Filtros Salvos</h6>
                <div class="saved-filter-chip" onclick="applySavedFilter('urgent-today')">
                    <span>Urgentes Hoje</span>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
                <div class="saved-filter-chip" onclick="applySavedFilter('new-proposals')">
                    <span>Com Propostas Novas</span>
                    <i class="bi bi-bell"></i>
                </div>
                <div class="saved-filter-chip" onclick="applySavedFilter('high-value')">
                    <span>Alto Valor (>R$ 2.000)</span>
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <button class="btn btn-outline-primary btn-sm w-100 mt-2">
                    <i class="bi bi-plus me-1"></i>Salvar Filtro Atual
                </button>
            </div>
            
            <!-- Quick Filters -->
            <div class="mb-4">
                <h6 class="text-uppercase text-muted small mb-3">Filtros Rápidos</h6>
                <div class="quick-filters-grid">
                    <button class="quick-filter-btn" data-filter="urgencia" data-value="alta">Urgente</button>
                    <button class="quick-filter-btn" data-filter="status" data-value="1">Novas</button>
                    <button class="quick-filter-btn" data-filter="propostas" data-value=">0">Com Propostas</button>
                    <button class="quick-filter-btn" data-filter="data" data-value="today">Hoje</button>
                </div>
            </div>
            
            <!-- Detailed Filters Form -->
            <form id="detailedFilters">
                <div class="mb-3">
                    <label class="form-label small text-muted">STATUS</label>
                    <?php foreach ($statusList as $status): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status[]" 
                                   value="<?= $status['id'] ?>" id="status-<?= $status['id'] ?>">
                            <label class="form-check-label small" for="status-<?= $status['id'] ?>">
                                <div class="status-dot me-1" style="background-color: <?= $status['cor'] ?>; width: 8px; height: 8px; display: inline-block;"></div>
                                <?= htmlspecialchars($status['nome']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">PERÍODO</label>
                    <select class="form-select form-select-sm" name="periodo">
                        <option value="">Qualquer período</option>
                        <option value="today">Hoje</option>
                        <option value="week">Esta semana</option>
                        <option value="month">Este mês</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">ORÇAMENTO</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="number" class="form-control form-control-sm" placeholder="Mínimo">
                        </div>
                        <div class="col">
                            <input type="number" class="form-control form-control-sm" placeholder="Máximo">
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">Limpar Tudo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Master-Detail Offcanvas -->
    <div class="offcanvas offcanvas-end detail-offcanvas" tabindex="-1" id="detailOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Detalhes da Solicitação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body" id="detailContent">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toggle Button -->
    <button class="filter-toggle-btn" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
        FILTROS
    </button>

    <div class="modal fade" id="modalAlterarStatus" tabindex="-1" aria-labelledby="modalAlterarStatusLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlterarStatusLabel">Alterar Status da Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?= url('admin/solicitacoes/alterar-status') ?>">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                        <input type="hidden" name="id" id="modalSolicitacaoId">
                        <input type="hidden" name="status" id="modalNovoStatus">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Confirma a alteração do status da solicitação para <strong id="modalStatusNome"></strong>?
                        </div>

                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (opcional)</label>
                            <textarea class="form-control" name="observacoes" id="observacoes" rows="3"
                                placeholder="Motivo da alteração ou observações sobre a mudança de status..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar Alteração</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Debug: Mostrar tipo de visualização atual
        console.log('Tipo de visualização atual:', '<?= $tipoVisualizacao ?>');
        
        // ===== KANBAN FUNCTIONALITY =====
        let draggedElement = null;
        let draggedData = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Só inicializar Kanban se estivermos na view Kanban
            if ('<?= $tipoVisualizacao ?>' === 'kanban') {
                initializeKanban();
            }
            initializeFilters();
        });
        
        function initializeKanban() {
            const cards = document.querySelectorAll('.solicitacao-card-kanban[draggable="true"]');
            const dropZones = document.querySelectorAll('.kanban-cards');
            
            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });
            
            dropZones.forEach(zone => {
                zone.addEventListener('dragover', handleDragOver);
                zone.addEventListener('drop', handleDrop);
                zone.addEventListener('dragenter', handleDragEnter);
                zone.addEventListener('dragleave', handleDragLeave);
            });
        }
        
        function handleDragStart(e) {
            draggedElement = this;
            draggedData = {
                id: this.dataset.solicitacaoId,
                originalStatus: this.closest('.kanban-column').dataset.statusId
            };
            
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }
        
        function handleDragEnd(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.kanban-column').forEach(col => {
                col.classList.remove('drag-over');
            });
            draggedElement = null;
            draggedData = null;
        }
        
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }
        
        function handleDragEnter(e) {
            this.closest('.kanban-column').classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            if (!this.contains(e.relatedTarget)) {
                this.closest('.kanban-column').classList.remove('drag-over');
            }
        }
        
        function handleDrop(e) {
            e.preventDefault();
            const column = this.closest('.kanban-column');
            column.classList.remove('drag-over');
            
            if (draggedElement && draggedData) {
                const newStatusId = column.dataset.statusId;
                
                if (newStatusId !== draggedData.originalStatus) {
                    // Move card visually
                    this.appendChild(draggedElement);
                    draggedElement.classList.add('card-animate-in');
                    
                    // Update status via AJAX
                    updateCardStatus(draggedData.id, newStatusId);
                    
                    // Update column counts
                    updateColumnCounts();
                }
            }
        }
        
        function updateCardStatus(solicitacaoId, newStatusId) {
            fetch('<?= url("admin/solicitacoes/alterar-status-ajax") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: solicitacaoId,
                    status: newStatusId,
                    csrf_token: '<?= Session::generateCSRFToken() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Status atualizado com sucesso!', 'success');
                } else {
                    showToast('Erro: ' + (data.message || 'Falha na atualização'), 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro de conexão', 'error');
            });
        }
        
        function updateColumnCounts() {
            document.querySelectorAll('.kanban-column').forEach(column => {
                const count = column.querySelectorAll('.solicitacao-card-kanban').length;
                column.querySelector('.kanban-count').textContent = count;
            });
        }
        
        // ===== DETAIL PANEL =====
        function openDetailPanel(solicitacaoId) {
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('detailOffcanvas'));
            const content = document.getElementById('detailContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            `;
            
            offcanvas.show();
            
            // Load content via AJAX
            fetch(`<?= url('admin/solicitacoes/detalhes-ajax') ?>?id=${solicitacaoId}`)
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = generateDetailContent(data);
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro ao carregar detalhes da solicitação
                        </div>
                    `;
                });
        }
        
        function generateDetailContent(data) {
            return `
                <div class="detail-header-section">
                    <h4 class="mb-2">${data.titulo || 'Solicitação #' + data.id}</h4>
                    <div class="detail-status-badge">
                        <div class="status-dot" style="background-color: ${data.status_cor}; width: 8px; height: 8px;"></div>
                        ${data.status_nome}
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">
                        <i class="bi bi-person-circle"></i>
                        Informações do Cliente
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nome:</strong><br>
                            ${data.cliente_nome || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            <a href="mailto:${data.cliente_email || ''}">${data.cliente_email || 'N/A'}</a>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">
                        <i class="bi bi-file-text"></i>
                        Detalhes da Solicitação
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo de Serviço:</strong><br>
                            ${data.tipo_servico_nome || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Urgência:</strong><br>
                            <span class="badge badge-urgency-${data.urgencia || 'media'}">${(data.urgencia || 'Média').charAt(0).toUpperCase() + (data.urgencia || 'média').slice(1)}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Descrição:</strong><br>
                        <p class="text-muted mt-2">${data.descricao || 'Sem descrição disponível'}</p>
                    </div>
                    ${data.orcamento_estimado ? `
                        <div>
                            <strong>Orçamento Estimado:</strong><br>
                            <span class="text-success fs-5">R$ ${parseFloat(data.orcamento_estimado).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                        </div>
                    ` : ''}
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">
                        <i class="bi bi-gear"></i>
                        Ações Rápidas
                    </div>
                    <div class="d-grid gap-2">
                        <a href="<?= url('admin/solicitacoes/visualizar') ?>?id=${data.id}" class="btn btn-primary">
                            <i class="bi bi-eye me-2"></i>Ver Página Completa
                        </a>
                        <a href="mailto:${data.cliente_email || ''}" class="btn btn-outline-primary">
                            <i class="bi bi-envelope me-2"></i>Enviar Email para Cliente
                        </a>
                        <button class="btn btn-outline-secondary" onclick="showStatusModal(${data.id})">
                            <i class="bi bi-arrow-repeat me-2"></i>Alterar Status
                        </button>
                    </div>
                </div>
            `;
        }
        
        // ===== KEBAB MENU =====
        function toggleKebabMenu(solicitacaoId) {
            const menu = document.getElementById(`kebab-${solicitacaoId}`);
            const allMenus = document.querySelectorAll('.dropdown-menu[id^="kebab-"]');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m !== menu) m.style.display = 'none';
            });
            
            // Toggle current menu
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
        
        // Close menus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.card-kebab-menu')) {
                document.querySelectorAll('.dropdown-menu[id^="kebab-"]').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
        
        // ===== FILTER FUNCTIONALITY =====
        function initializeFilters() {
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.classList.toggle('active');
                    applyQuickFilters();
                });
            });
        }
        
        function applyQuickFilters() {
            const activeFilters = document.querySelectorAll('.quick-filter-btn.active');
            const params = new URLSearchParams(window.location.search);
            
            // Clear existing quick filter params
            params.delete('urgencia');
            params.delete('status');
            params.delete('propostas');
            params.delete('data');
            
            activeFilters.forEach(filter => {
                const filterType = filter.dataset.filter;
                const filterValue = filter.dataset.value;
                params.set(filterType, filterValue);
            });
            
            window.location.search = params.toString();
        }
        
        function applySavedFilter(filterName) {
            const savedFilters = {
                'urgent-today': '?urgencia=alta&data=today',
                'new-proposals': '?status=1&propostas=>0',
                'high-value': '?orcamento_min=2000'
            };
            
            if (savedFilters[filterName]) {
                window.location.search = savedFilters[filterName];
            }
        }
        
        function clearAllFilters() {
            window.location.href = window.location.pathname + '?view=kanban';
        }
        
        // ===== UTILITIES =====
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed`;
            toast.style.cssText = `
                top: 20px; right: 20px; z-index: 9999; min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
        
        function changeStatus(solicitacaoId, statusId, statusNome) {
            // Close kebab menu first
            document.querySelectorAll('.dropdown-menu[id^="kebab-"]').forEach(menu => {
                menu.style.display = 'none';
            });
            
            // Call existing modal function
            alterarStatus(solicitacaoId, statusId, statusNome);
        }
        
        // Função para alterar status
        function alterarStatus(solicitacaoId, novoStatus, statusNome) {
            document.getElementById('modalSolicitacaoId').value = solicitacaoId;
            document.getElementById('modalNovoStatus').value = novoStatus;
            document.getElementById('modalStatusNome').textContent = statusNome;

            const modal = new bootstrap.Modal(document.getElementById('modalAlterarStatus'));
            modal.show();
        }
        
        // Auto-remover alertas após 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // ===== NOVAS FUNÇÕES =====
        function atualizarDados() {
            // Manter o tipo de visualização atual ao atualizar
            const currentView = '<?= $tipoVisualizacao ?>';
            window.location.href = window.location.pathname + '?view=' + currentView;
        }
        
        function exportarDados() {
            alert('Funcionalidade de exportação será implementada em breve.\nTipo de visualização atual: <?= $tipoVisualizacao ?>');
        }
    </script>
</body>
</html>