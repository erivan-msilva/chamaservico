<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Capturar tipo de visualização da URL ou usar padrão
$tipoVisualizacao = $_GET['view'] ?? 'cards';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - Admin</title>
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
        
        .user-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        .filter-card {
            background: #f8f9fa;
            border-radius: 12px;
            border: none;
        }
        
        .stats-widget {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .btn-action {
            padding: 4px 8px;
            font-size: 0.75rem;
            border-radius: 6px;
        }
        
        .user-tipo {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .tipo-cliente { background: #e3f2fd; color: #1976d2; }
        .tipo-prestador { background: #e8f5e8; color: #388e3c; }
        .tipo-ambos { background: #fff3e0; color: #f57c00; }
        
        /* Estilos para visualização em Lista */
        .list-view .user-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        
        .list-view .user-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
        
        /* Estilos para visualização Timeline */
        .timeline-view {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline-view::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
        }
        
        .timeline-item {
            position: relative;
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-left: 1rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 1.5rem;
            width: 12px;
            height: 12px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }
        
        .timeline-date {
            position: absolute;
            left: -8rem;
            top: 1rem;
            background: #667eea;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* Botões de visualização */
        .view-toggle {
            background: white;
            border-radius: 8px;
            padding: 0.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .view-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            border-radius: 6px;
            color: #6c757d;
            transition: all 0.2s;
        }
        
        .view-btn.active {
            background: #667eea;
            color: white;
        }
        
        .view-btn:hover:not(.active) {
            background: #f8f9fa;
            color: #667eea;
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
                            <a class="nav-link" href="/chamaservico/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/chamaservico/admin/usuarios">
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
                            <a class="nav-link" href="/chamaservico/admin/propostas">
                                <i class="bi bi-file-text me-2"></i>
                                Propostas
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
                                <?= htmlspecialchars($_SESSION['admin_nome']) ?>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-people me-2"></i>
                        Gestão de Usuários
                    </h1>
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Seletor de Visualização -->
                        <div class="view-toggle d-flex">
                            <button class="view-btn <?= $tipoVisualizacao === 'cards' ? 'active' : '' ?>" 
                                    onclick="changeView('cards')" title="Visualização em Cards">
                                <i class="bi bi-grid-3x3-gap"></i> Cards
                            </button>
                            <button class="view-btn <?= $tipoVisualizacao === 'lista' ? 'active' : '' ?>" 
                                    onclick="changeView('lista')" title="Visualização em Lista">
                                <i class="bi bi-list-ul"></i> Lista
                            </button>
                            <button class="view-btn <?= $tipoVisualizacao === 'timeline' ? 'active' : '' ?>" 
                                    onclick="changeView('timeline')" title="Visualização em Timeline">
                                <i class="bi bi-clock-history"></i> Timeline
                            </button>
                        </div>
                        
                        <div class="btn-toolbar">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarUsuarios()">
                                    <i class="bi bi-download me-1"></i>
                                    Exportar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="enviarEmail()">
                                    <i class="bi bi-envelope me-1"></i>
                                    Email em Massa
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Atualizar
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

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-widget p-3" style="border-left-color: #4e73df;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total de Usuários
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($stats['total']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-widget p-3" style="border-left-color: #1cc88a;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Ativos
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($stats['ativos']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-check fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-widget p-3" style="border-left-color: #36b9cc;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Novos Hoje
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($stats['novos_hoje']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-plus fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stats-widget p-3" style="border-left-color: #f6c23e;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Prestadores
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($stats['prestadores'] + $stats['ambos']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-tools fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end" id="filterForm">
                            <input type="hidden" name="view" value="<?= htmlspecialchars($tipoVisualizacao) ?>">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="busca" class="form-control" 
                                           placeholder="Nome ou email..." 
                                           value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Tipo</label>
                                <select name="tipo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="cliente" <?= ($filtros['tipo'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                    <option value="prestador" <?= ($filtros['tipo'] ?? '') === 'prestador' ? 'selected' : '' ?>>Prestador</option>
                                    <option value="ambos" <?= ($filtros['tipo'] ?? '') === 'ambos' ? 'selected' : '' ?>>Ambos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Status</label>
                                <select name="ativo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ordenar por</label>
                                <select name="ordem" class="form-select">
                                    <option value="nome" <?= ($filtros['ordem'] ?? '') === 'nome' ? 'selected' : '' ?>>Nome</option>
                                    <option value="data_cadastro" <?= ($filtros['ordem'] ?? '') === 'data_cadastro' ? 'selected' : '' ?>>Data de Cadastro</option>
                                    <option value="ultimo_acesso" <?= ($filtros['ordem'] ?? '') === 'ultimo_acesso' ? 'selected' : '' ?>>Último Acesso</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel me-1"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Conteúdo dos Usuários baseado na visualização -->
                <div id="usuariosContent">
                    <?php if (empty($usuarios)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Nenhum usuário encontrado</h4>
                            <p class="text-muted">
                                <?= !empty($filtros['busca']) || !empty($filtros['tipo']) || !empty($filtros['ativo']) 
                                    ? 'Tente ajustar os filtros para ver mais usuários.' 
                                    : 'Não há usuários cadastrados no sistema.' ?>
                            </p>
                            <?php if (!empty($filtros['busca']) || !empty($filtros['tipo']) || !empty($filtros['ativo'])): ?>
                                <a href="/chamaservico/admin/usuarios" class="btn btn-outline-primary">
                                    <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        
                        <!-- Visualização CARDS -->
                        <?php if ($tipoVisualizacao === 'cards'): ?>
                            <div class="row">
                                <?php foreach ($usuarios as $usuario): ?>
                                    <div class="col-md-6 col-xl-4 mb-4">
                                        <div class="card user-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <?php
                                                    $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                                    $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                                    ?>
                                                    <?php if ($fotoExiste): ?>
                                                        <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($fotoPerfil)) ?>"
                                                             class="user-avatar me-3" alt="Avatar">
                                                    <?php else: ?>
                                                        <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-person text-white fs-4"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1"><?= htmlspecialchars($usuario['nome']) ?></h6>
                                                        <p class="card-text text-muted small mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                                                    </div>
                                                
                                                    <span class="status-badge badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="row text-center mb-3">
                                                    <div class="col-4">
                                                        <div class="fw-bold text-primary"><?= $usuario['total_solicitacoes'] ?></div>
                                                        <small class="text-muted">Solicitações</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-success"><?= $usuario['total_propostas'] ?></div>
                                                        <small class="text-muted">Propostas</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-info">
                                                            <?= $usuario['ultimo_acesso'] ? date('d/m', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                                                        </div>
                                                        <small class="text-muted">Último Acesso</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="user-tipo tipo-<?= $usuario['tipo'] ?>">
                                                        <?= ucfirst($usuario['tipo']) ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        Desde <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="d-flex gap-1">
                                                    <a href="/chamaservico/admin/usuarios/visualizar?id=<?= $usuario['id'] ?>" 
                                                       class="btn btn-outline-info btn-action flex-fill">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                    
                                                    <?php if ($usuario['ativo']): ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 0)" 
                                                                class="btn btn-outline-warning btn-action flex-fill">
                                                            <i class="bi bi-pause"></i> Inativar
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 1)" 
                                                                class="btn btn-outline-success btn-action flex-fill">
                                                            <i class="bi bi-play"></i> Ativar
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-action dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="mailto:<?= $usuario['email'] ?>">
                                                                <i class="bi bi-envelope me-2"></i>Enviar Email
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="verDetalhes(<?= $usuario['id'] ?>)">
                                                                <i class="bi bi-info-circle me-2"></i>Detalhes
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmarExclusao(<?= $usuario['id'] ?>)">
                                                                <i class="bi bi-trash me-2"></i>Excluir
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        
                        <!-- Visualização LISTA -->
                        <?php elseif ($tipoVisualizacao === 'lista'): ?>
                            <div class="list-view">
                                <?php foreach ($usuarios as $usuario): ?>
                                    <div class="user-item">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                                    $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                                    ?>
                                                    <?php if ($fotoExiste): ?>
                                                        <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($fotoPerfil)) ?>"
                                                             class="user-avatar-sm me-3" alt="Avatar">
                                                    <?php else: ?>
                                                        <div class="user-avatar-sm bg-secondary d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h6>
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <small class="text-muted"><?= htmlspecialchars($usuario['email']) ?></small>
                                                            <span class="user-tipo tipo-<?= $usuario['tipo'] ?>">
                                                                <?= ucfirst($usuario['tipo']) ?>
                                                            </span>
                                                            <span class="status-badge badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                                                <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <div class="fw-bold text-primary"><?= $usuario['total_solicitacoes'] ?></div>
                                                        <small class="text-muted">Sol.</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-success"><?= $usuario['total_propostas'] ?></div>
                                                        <small class="text-muted">Prop.</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-info">
                                                            <?= $usuario['ultimo_acesso'] ? date('d/m', strtotime($usuario['ultimo_acesso'])) : '--' ?>
                                                        </div>
                                                        <small class="text-muted">Acesso</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="/chamaservico/admin/usuarios/visualizar?id=<?= $usuario['id'] ?>" 
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($usuario['ativo']): ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 0)" 
                                                                class="btn btn-outline-warning btn-sm">
                                                            <i class="bi bi-pause"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 1)" 
                                                                class="btn btn-outline-success btn-sm">
                                                            <i class="bi bi-play"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <a href="mailto:<?= $usuario['email'] ?>" class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-envelope"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        
                        <!-- Visualização TIMELINE -->
                        <?php elseif ($tipoVisualizacao === 'timeline'): ?>
                            <div class="timeline-view">
                                <?php foreach ($usuarios as $usuario): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
                                        </div>
                                        
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <?php
                                                $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                                $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                                ?>
                                                <?php if ($fotoExiste): ?>
                                                    <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($fotoPerfil)) ?>"
                                                         class="user-avatar mx-auto d-block" alt="Avatar">
                                                <?php else: ?>
                                                    <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center mx-auto">
                                                        <i class="bi bi-person text-white fs-4"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h5 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h5>
                                                <p class="text-muted mb-2"><?= htmlspecialchars($usuario['email']) ?></p>
                                                <div class="d-flex gap-2 mb-2">
                                                    <span class="user-tipo tipo-<?= $usuario['tipo'] ?>">
                                                        <?= ucfirst($usuario['tipo']) ?>
                                                    </span>
                                                    <span class="status-badge badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                                    </span>
                                                </div>
                                                <?php if ($usuario['ultimo_acesso']): ?>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Último acesso: <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="fw-bold text-primary"><?= $usuario['total_solicitacoes'] ?></div>
                                                            <small class="text-muted">Solicitações</small>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="fw-bold text-success"><?= $usuario['total_propostas'] ?></div>
                                                            <small class="text-muted">Propostas</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex flex-column gap-1">
                                                    <a href="/chamaservico/admin/usuarios/visualizar?id=<?= $usuario['id'] ?>" 
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-eye me-1"></i>Visualizar
                                                    </a>
                                                    
                                                    <?php if ($usuario['ativo']): ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 0)" 
                                                                class="btn btn-outline-warning btn-sm">
                                                            <i class="bi bi-pause me-1"></i>Inativar
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 1)" 
                                                                class="btn btn-outline-success btn-sm">
                                                            <i class="bi bi-play me-1"></i>Ativar
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Paginação -->
                        <?php if (count($usuarios) >= 12): ?>
                            <nav aria-label="Paginação de usuários">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Próximo">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Confirmar Ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalMensagem">
                    Tem certeza que deseja realizar esta ação?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmar">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para alterar visualização
        function changeView(viewType) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('view', viewType);
            window.location.href = currentUrl.toString();
        }
        
        function alterarStatus(userId, novoStatus) {
            const acao = novoStatus ? 'ativar' : 'desativar';
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            
            document.getElementById('modalTitulo').textContent = novoStatus ? 'Ativar Usuário' : 'Desativar Usuário';
            document.getElementById('modalMensagem').textContent = `Tem certeza que deseja ${acao} este usuário?`;
            
            document.getElementById('btnConfirmar').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = novoStatus ? '/chamaservico/admin/usuarios/ativar' : '/chamaservico/admin/usuarios/desativar';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = userId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };
            
            modal.show();
        }
        
        function confirmarExclusao(userId) {
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            
            document.getElementById('modalTitulo').textContent = 'Excluir Usuário';
            document.getElementById('modalMensagem').innerHTML = 
                '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>' +
                '<strong>Atenção!</strong> Esta ação não pode ser desfeita. Todos os dados do usuário serão removidos permanentemente.</div>';
            
            const btnConfirmar = document.getElementById('btnConfirmar');
            btnConfirmar.className = 'btn btn-danger';
            btnConfirmar.textContent = 'Excluir';
            
            btnConfirmar.onclick = function() {
                alert('Funcionalidade de exclusão será implementada conforme necessidade.');
                modal.hide();
            };
            
            modal.show();
        }
        
        function verDetalhes(userId) {
            window.location.href = `/chamaservico/admin/usuarios/visualizar?id=${userId}`;
        }
        
        function exportarUsuarios() {
            alert('Funcionalidade de exportação será implementada.');
        }
        
        function enviarEmail() {
            alert('Funcionalidade de email em massa será implementada.');
        }
        
        // Auto-submit dos filtros mantendo a visualização
        document.querySelectorAll('select[name="tipo"], select[name="ativo"], select[name="ordem"]').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
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
        
        // Salvar preferência de visualização no localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const viewType = new URLSearchParams(window.location.search).get('view') || 'cards';
            localStorage.setItem('admin_users_view', viewType);
        });
    </script>
</body>
</html>
