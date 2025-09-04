<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// Definir variáveis padrão se não existirem
$tipoVisualizacao = $tipoVisualizacao ?? 'cards';
$solicitacoes = $solicitacoes ?? [];
$estatisticas = $estatisticas ?? [
    'total_solicitacoes' => 0,
    'aguardando_propostas' => 0,
    'concluidas' => 0,
    'canceladas' => 0
];
$statusList = $statusList ?? [];
$tiposServico = $tiposServico ?? [];
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
        }
        
        .solicitacao-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
        }
        
        .solicitacao-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .view-toggle {
            background: white;
            border-radius: 8px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .view-toggle .btn {
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.875rem;
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
                            <a class="nav-link" href="admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: GESTÃO -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear me-1"></i>Gestão
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center active" href="admin/solicitacoes">
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
                            <a class="nav-link" href="admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/tipos-servico">
                                <i class="bi bi-tools me-2"></i>
                                Tipos de Serviços
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: ANÁLISE -->
                        <li class="nav-section-title">
                            <i class="bi bi-graph-up me-1"></i>Análise
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/relatorios">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        
                        <!-- SEÇÃO: SISTEMA -->
                        <li class="nav-section-title">
                            <i class="bi bi-gear-fill me-1"></i>Sistema
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href=admin/configuracoes">
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
                            <a href="admin/logout" class="btn btn-outline-light btn-sm mt-2">
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
                        <i class="bi bi-list-task me-2"></i>
                        Gestão de Solicitações
                    </h1
                    
                    <!-- Controles de visualização -->
                    <div class="view-toggle">
                        <div class="btn-group" role="group">
                            <a href="?view=cards&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                               class="btn btn-<?= $tipoVisualizacao === 'cards' ? 'primary' : 'outline-primary' ?> btn-sm">
                                <i class="bi bi-grid-3x3-gap me-1"></i>Cards
                            </a>
                            <a href="?view=table&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                               class="btn btn-<?= $tipoVisualizacao === 'table' ? 'primary' : 'outline-primary' ?> btn-sm">
                                <i class="bi bi-table me-1"></i>Tabela
                            </a>
                            <a href="?view=mapa&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                               class="btn btn-<?= $tipoVisualizacao === 'mapa' ? 'primary' : 'outline-primary' ?> btn-sm">
                                <i class="bi bi-map me-1"></i>Mapa
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

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #007bff;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                        Total de Solicitações
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($estatisticas['total_solicitacoes']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-list fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #ffc107;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                        Aguardando Propostas
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($estatisticas['aguardando_propostas']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #28a745;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                        Concluídas
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($estatisticas['concluidas']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #dc3545;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                        Canceladas
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($estatisticas['canceladas']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-x-circle fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="view" value="<?= htmlspecialchars($tipoVisualizacao) ?>">

                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="">Todos</option>
                                    <?php foreach ($statusList as $status): ?>
                                        <option value="<?= $status['id'] ?>" <?= ($filtros['status'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="urgencia" class="form-label">Urgência</label>
                                <select class="form-select" name="urgencia" id="urgencia">
                                    <option value="">Todas</option>
                                    <option value="baixa" <?= ($filtros['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                                    <option value="media" <?= ($filtros['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                                    <option value="alta" <?= ($filtros['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="tipo_servico" class="form-label">Tipo</label>
                                <select class="form-select" name="tipo_servico" id="tipo_servico">
                                    <option value="">Todos</option>
                                    <?php foreach ($tiposServico as $tipo): ?>
                                        <option value="<?= $tipo['id'] ?>" <?= ($filtros['tipo_servico'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="busca" id="busca"
                                    placeholder="Título, descrição, cliente..." value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                                <a href="admin/solicitacoes" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Limpar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="content-area">
                    <?php if (empty($solicitacoes)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Nenhuma solicitação encontrada</h4>
                            <p class="text-muted">
                                <?php if (!empty(array_filter($filtros))): ?>
                                    Tente ajustar os filtros de busca ou aguarde novas solicitações.
                                <?php else: ?>
                                    Não há solicitações cadastradas no sistema ainda.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php if ($tipoVisualizacao === 'cards'): ?>
                            <div class="row">
                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card solicitacao-card h-100">
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
                                                    <a href="admin/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
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
                                                                <a href="admin/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
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

                        <?php elseif ($tipoVisualizacao === 'mapa'): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-map me-2"></i>
                                        Mapa de Solicitações
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Visualização em Mapa</strong><br>
                                        Esta funcionalidade será implementada em breve para mostrar as solicitações geograficamente.
                                    </div>

                                    <h6>Solicitações por Cidade:</h6>
                                    <?php
                                    $cidades = [];
                                    foreach ($solicitacoes as $s) {
                                        $cidade = ($s['cidade'] ?? 'N/A') . '/' . ($s['estado'] ?? 'N/A');
                                        $cidades[$cidade] = ($cidades[$cidade] ?? 0) + 1;
                                    }
                                    arsort($cidades);
                                    ?>
                                    <div class="row">
                                        <?php foreach (array_slice($cidades, 0, 6) as $cidade => $total): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span><?= htmlspecialchars($cidade) ?></span>
                                                    <span class="badge bg-primary"><?= $total ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if (empty($cidades)): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Nenhuma cidade encontrada nas solicitações.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Mostrando <?= count($solicitacoes) ?> solicitação<?= count($solicitacoes) != 1 ? 'ões' : '' ?>
                            </small>

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

    <div class="modal fade" id="modalAlterarStatus" tabindex="-1" aria-labelledby="modalAlterarStatusLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlterarStatusLabel">Alterar Status da Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="admin/solicitacoes/alterar-status">
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
        // Função para alterar status
        function alterarStatus(solicitacaoId, novoStatus, statusNome) {
            document.getElementById('modalSolicitacaoId').value = solicitacaoId;
            document.getElementById('modalNovoStatus').value = novoStatus;
            document.getElementById('modalStatusNome').textContent = statusNome;

            const modal = new bootstrap.Modal(document.getElementById('modalAlterarStatus'));
            modal.show();
        }

        // Auto-submit dos filtros
        document.querySelectorAll('#status, #urgencia, #tipo_servico').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Função para exportar dados
        function exportarDados() {
            alert('Funcionalidade de exportação será implementada em breve.');
        }

        // Função para atualizar dados
        function atualizarDados() {
            window.location.reload();
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

        // Tooltip para elementos com title
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>