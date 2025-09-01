<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
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
    <title>Visualizar Solicitação - Admin</title>
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
        
        .info-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            border: none !important;
            padding: 1.5rem 2rem !important;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .card-body {
            padding: 2rem !important;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .urgencia-badge {
            font-size: 0.8rem;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .timeline-item {
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            position: relative;
            transition: all 0.3s ease;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 1.5rem;
            width: 16px;
            height: 16px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }
        
        .timeline-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .proposta-card {
            border: none !important;
            border-radius: 15px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem !important;
            overflow: hidden;
            position: relative;
        }
        
        .proposta-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
        }
        
        .proposta-pendente::before { background: linear-gradient(to bottom, #ffc107, #ff8c00); }
        .proposta-aceita::before { background: linear-gradient(to bottom, #28a745, #20c997); }
        .proposta-recusada::before { background: linear-gradient(to bottom, #dc3545, #e91e63); }
        .proposta-cancelada::before { background: linear-gradient(to bottom, #6c757d, #495057); }
        
        .proposta-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }
        
        .image-gallery {
            margin: -0.5rem;
        }
        
        .image-gallery .col-md-4 {
            padding: 0.5rem;
        }
        
        .image-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .image-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .image-item img {
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.7));
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: 15px;
        }
        
        .image-item:hover .image-overlay {
            opacity: 1;
        }
        
        .zoom-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .image-item:hover .zoom-icon {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }
        
        .info-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 5px solid #667eea;
        }
        
        .info-section h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .address-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        }
        
        .address-card address {
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
        }
        
        .actions-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 2rem;
        }
        
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }
            
            .timeline-item {
                padding: 1rem;
            }
            
            .address-card {
                padding: 1.5rem;
            }
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
                            <a class="nav-link" href="/chamaservico/admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/chamaservico/admin/solicitacoes">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <h1 class="h2 text-dark">
                        <i class="bi bi-eye me-2"></i>
                        Solicitação #<?= $solicitacao['id'] ?>
                    </h1>
                    <div class="btn-toolbar">
                        <div class="btn-group me-2">
                            <a href="/chamaservico/admin/solicitacoes" class="btn btn-modern btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Voltar
                            </a>
                            <button type="button" class="btn btn-modern btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>
                                Imprimir
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-modern btn-warning" onclick="alterarStatus()">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Alterar Status
                            </button>
                            <button type="button" class="btn btn-modern btn-info" onclick="enviarEmail()">
                                <i class="bi bi-envelope me-1"></i>
                                Contatar Cliente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informações da Solicitação -->
                <div class="row">
                    <!-- Coluna Principal -->
                    <div class="col-lg-8">
                        <!-- Card Principal -->
                        <div class="info-card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">
                                        <i class="bi bi-clipboard-check me-2"></i>
                                        <?= htmlspecialchars($solicitacao['titulo']) ?>
                                    </h4>
                                    <div class="d-flex gap-2">
                                        <span class="status-badge badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                            <i class="bi bi-circle-fill me-1"></i>
                                            <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                        </span>
                                        <span class="urgencia-badge badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                            <i class="bi bi-<?= $solicitacao['urgencia'] === 'alta' ? 'exclamation-triangle' : ($solicitacao['urgencia'] === 'media' ? 'clock' : 'info-circle') ?> me-1"></i>
                                            <?= ucfirst($solicitacao['urgencia']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-icon bg-primary text-white">
                                            <i class="bi bi-tools"></i>
                                        </div>
                                        <h6 class="mb-1">Tipo</h6>
                                        <small class="text-muted"><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></small>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon bg-success text-white">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                        <h6 class="mb-1">Solicitado em</h6>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></small>
                                    </div>
                                    <?php if ($solicitacao['data_atendimento']): ?>
                                        <div class="stat-item">
                                            <div class="stat-icon bg-info text-white">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <h6 class="mb-1">Data Preferencial</h6>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($solicitacao['data_atendimento'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($solicitacao['orcamento_estimado']): ?>
                                        <div class="stat-item">
                                            <div class="stat-icon bg-warning text-white">
                                                <i class="bi bi-currency-dollar"></i>
                                            </div>
                                            <h6 class="mb-1">Orçamento</h6>
                                            <small class="text-muted">R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="info-section">
                                    <h6><i class="bi bi-person-circle me-2"></i>Informações do Cliente</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Nome:</strong> <?= htmlspecialchars($solicitacao['cliente_nome']) ?></p>
                                            <p class="mb-2"><strong>Email:</strong> 
                                                <a href="mailto:<?= $solicitacao['cliente_email'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($solicitacao['cliente_email']) ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if ($solicitacao['cliente_telefone']): ?>
                                                <p class="mb-2"><strong>Telefone:</strong> 
                                                    <a href="tel:<?= $solicitacao['cliente_telefone'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($solicitacao['cliente_telefone']) ?>
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-section">
                                    <h6><i class="bi bi-chat-text me-2"></i>Descrição do Serviço</h6>
                                    <div class="p-3 bg-white rounded-3 border border-light">
                                        <?= nl2br(htmlspecialchars($solicitacao['descricao'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagens -->
                        <?php if (!empty($imagens)): ?>
                            <div class="info-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-camera me-2"></i>
                                        Galeria de Imagens
                                        <span class="badge bg-light text-dark ms-2"><?= count($imagens) ?></span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row image-gallery">
                                        <?php foreach ($imagens as $index => $imagem): ?>
                                            <div class="col-md-4 col-sm-6 mb-3">
                                                <div class="image-item">
                                                    <?php
                                                    $imagemPath = "/chamaservico/uploads/solicitacoes/" . basename($imagem['caminho_imagem']);
                                                    ?>
                                                    <img src="<?= $imagemPath ?>" 
                                                         class="img-fluid w-100"
                                                         style="height: 200px; object-fit: cover;"
                                                         onclick="openImageModal('<?= $imagemPath ?>', <?= $index + 1 ?>)"
                                                         alt="Imagem <?= $index + 1 ?>"
                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZW0gTsOjbyBFbmNvbnRyYWRhPC90ZXh0Pjwvc3ZnPg=='">
                                                    
                                                    <div class="image-overlay"></div>
                                                    
                                                    <div class="zoom-icon">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </div>
                                                    
                                                    <div class="position-absolute bottom-0 start-0 p-2">
                                                        <small class="text-white bg-dark bg-opacity-75 px-2 py-1 rounded">
                                                            <?= date('d/m/Y H:i', strtotime($imagem['data_upload'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Propostas Recebidas -->
                        <?php if (!empty($propostas)): ?>
                            <div class="info-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-envelope-heart me-2"></i>
                                        Propostas Recebidas
                                        <span class="badge bg-light text-dark ms-2"><?= count($propostas) ?></span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($propostas as $proposta): ?>
                                        <div class="proposta-card proposta-<?= $proposta['status'] ?> p-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="bi bi-person-badge text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($proposta['prestador_nome']) ?></h6>
                                                            <small class="text-muted">Prestador de Serviços</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="mb-3"><?= nl2br(htmlspecialchars($proposta['descricao'])) ?></p>
                                                    
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="border-end">
                                                                <div class="h5 text-success mb-1">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></div>
                                                                <small class="text-muted">Valor</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="border-end">
                                                                <div class="h5 text-info mb-1"><?= $proposta['prazo_execucao'] ?></div>
                                                                <small class="text-muted">Dias</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="h5 text-primary mb-1"><?= date('d/m', strtotime($proposta['data_proposta'])) ?></div>
                                                            <small class="text-muted">Enviada</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <div class="mb-3">
                                                        <span class="badge fs-6 px-3 py-2 bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'pendente' ? 'warning' : ($proposta['status'] === 'recusada' ? 'danger' : 'secondary')) ?>">
                                                            <i class="bi bi-<?= $proposta['status'] === 'aceita' ? 'check-circle' : ($proposta['status'] === 'pendente' ? 'clock' : ($proposta['status'] === 'recusada' ? 'x-circle' : 'dash-circle')) ?> me-1"></i>
                                                            <?= ucfirst($proposta['status']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <a href="mailto:<?= $proposta['prestador_email'] ?>" class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-envelope"></i>
                                                        </a>
                                                        <?php if ($proposta['prestador_telefone']): ?>
                                                            <a href="tel:<?= $proposta['prestador_telefone'] ?>" class="btn btn-outline-success btn-sm">
                                                                <i class="bi bi-telephone"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="info-card mb-4">
                                <div class="card-body text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-inbox" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="text-muted">Nenhuma proposta recebida</h5>
                                    <p class="text-muted">Esta solicitação ainda não recebeu propostas de prestadores.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Endereço -->
                        <div class="address-card">
                            <h6 class="mb-3">
                                <i class="bi bi-geo-alt me-2"></i>
                                Local do Serviço
                            </h6>
                            <address class="mb-0">
                                <strong><?= htmlspecialchars($solicitacao['logradouro']) ?>, <?= htmlspecialchars($solicitacao['numero']) ?></strong><br>
                                <?php if ($solicitacao['complemento']): ?>
                                    <?= htmlspecialchars($solicitacao['complemento']) ?><br>
                                <?php endif; ?>
                                <?= htmlspecialchars($solicitacao['bairro']) ?><br>
                                <?= htmlspecialchars($solicitacao['cidade']) ?> - <?= htmlspecialchars($solicitacao['estado']) ?><br>
                                <small class="opacity-75">CEP: <?= htmlspecialchars($solicitacao['cep']) ?></small>
                            </address>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($solicitacao['logradouro'] . ', ' . $solicitacao['numero'] . ', ' . $solicitacao['bairro'] . ', ' . $solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>" 
                               target="_blank" class="btn btn-light btn-modern w-100">
                                <i class="bi bi-map me-1"></i>Ver no Google Maps
                            </a>
                        </div>

                        <!-- Histórico -->
                        <?php if (!empty($historico)): ?>
                            <div class="info-card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-clock-history me-2"></i>
                                        Histórico de Ações
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($historico as $item): ?>
                                        <div class="timeline-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="text-primary"><?= htmlspecialchars($item['acao']) ?></strong>
                                                    <p class="mb-2 small"><?= htmlspecialchars($item['detalhes']) ?></p>
                                                    <small class="text-muted">
                                                        <i class="bi bi-person me-1"></i>
                                                        <?= htmlspecialchars($item['responsavel']) ?>
                                                    </small>
                                                </div>
                                                <small class="text-muted fw-bold">
                                                    <?= date('d/m H:i', strtotime($item['data_acao'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Ações Administrativas -->
                        <div class="actions-card">
                            <h6 class="mb-3 text-dark">
                                <i class="bi bi-tools me-2"></i>
                                Ações Administrativas
                            </h6>
                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-modern btn-warning" onclick="alterarStatus()">
                                    <i class="bi bi-arrow-repeat me-2"></i>
                                    Alterar Status
                                </button>
                                <button type="button" class="btn btn-modern btn-info" onclick="enviarEmail()">
                                    <i class="bi bi-envelope me-2"></i>
                                    Contatar Cliente
                                </button>
                                <button type="button" class="btn btn-modern btn-secondary" onclick="gerarRelatorio()">
                                    <i class="bi bi-file-text me-2"></i>
                                    Gerar Relatório
                                </button>
                                <?php if ($solicitacao['status_id'] == 1): ?>
                                    <button type="button" class="btn btn-modern btn-danger" onclick="cancelarSolicitacao()">
                                        <i class="bi bi-x-circle me-2"></i>
                                        Cancelar Solicitação
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para visualizar imagens -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-camera me-2"></i>Visualizar Imagem <span id="imageNumber"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-1">
                    <img id="modalImage" src="" class="img-fluid" alt="Imagem ampliada" style="max-height: 80vh; border-radius: 10px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para alterar status -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Alterar Status da Solicitação
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/chamaservico/admin/solicitacoes/alterar-status">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $solicitacao['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="novoStatus" class="form-label fw-bold">Novo Status</label>
                            <select class="form-select" name="status" id="novoStatus" required>
                                <option value="">Selecione um status</option>
                                <option value="1">Aguardando Propostas</option>
                                <option value="2">Em Análise</option>
                                <option value="3">Proposta Aceita</option>
                                <option value="4">Em Andamento</option>
                                <option value="5">Concluído</option>
                                <option value="6">Cancelado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label fw-bold">Motivo/Observação</label>
                            <textarea class="form-control" name="motivo" id="motivo" rows="3" placeholder="Descreva o motivo da alteração..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modern btn-primary">Alterar Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openImageModal(imageSrc, imageNumber) {
            document.getElementById("modalImage").src = imageSrc;
            document.getElementById("imageNumber").textContent = "- Foto " + imageNumber;
            new bootstrap.Modal(document.getElementById("imageModal")).show();
        }
        
        function alterarStatus() {
            new bootstrap.Modal(document.getElementById("statusModal")).show();
        }
        
        function enviarEmail() {
            window.location.href = 'mailto:<?= $solicitacao['cliente_email'] ?>';
        }
        
        function gerarRelatorio() {
            window.print();
        }
        
        function cancelarSolicitacao() {
            if (confirm('Tem certeza que deseja cancelar esta solicitação?')) {
                document.getElementById('novoStatus').value = '6';
                document.getElementById('motivo').value = 'Cancelado pela administração';
                alterarStatus();
            }
        }
        
        // Fechar modal ao clicar na imagem
        document.getElementById("modalImage").addEventListener("click", function() {
            bootstrap.Modal.getInstance(document.getElementById("imageModal")).hide();
        });
        
        // Animação de entrada dos cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
