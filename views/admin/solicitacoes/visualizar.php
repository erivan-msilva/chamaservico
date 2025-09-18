<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// CORREÇÃO: Buscar dados da solicitação
$solicitacaoId = $_GET['id'] ?? 0;
$solicitacao = null;

if ($solicitacaoId) {
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        
        // Buscar dados completos da solicitação
        $sql = "SELECT s.*, 
                       p.nome as cliente_nome, p.email as cliente_email, p.telefone as cliente_telefone,
                       ts.nome as tipo_servico_nome,
                       st.nome as status_nome, st.cor as status_cor,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                FROM tb_solicita_servico s
                LEFT JOIN tb_pessoa p ON s.cliente_id = p.id
                LEFT JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                LEFT JOIN tb_status_solicitacao st ON s.status_id = st.id
                LEFT JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE s.id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$solicitacaoId]);
        $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$solicitacao) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Solicitação não encontrada!'
            ];
            header('Location: admin/solicitacoes');
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Erro ao buscar solicitação: " . $e->getMessage());
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'message' => 'Erro ao carregar dados da solicitação!'
        ];
        header('Location: admin/solicitacoes');
        exit;
    }
} else {
    // Se não tem ID, usar dados simulados para evitar erros
    $solicitacao = [
        'id' => 1,
        'titulo' => 'Solicitação de Exemplo',
        'descricao' => 'Esta é uma solicitação de exemplo para demonstração.',
        'data_solicitacao' => date('Y-m-d H:i:s'),
        'data_atendimento' => null,
        'orcamento_estimado' => 200.00,
        'urgencia' => 'media',
        'status_nome' => 'Aguardando Propostas',
        'status_cor' => '#FFC107',
        'tipo_servico_nome' => 'Serviço Geral',
        'cliente_nome' => 'Cliente Exemplo',
        'cliente_email' => 'cliente@exemplo.com',
        'cliente_telefone' => '(11) 99999-9999',
        'logradouro' => 'Rua Exemplo',
        'numero' => '123',
        'complemento' => 'Apto 1',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'cep' => '01000-000'
    ];
}

// CORREÇÃO: Verificar se função url existe
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = 'https://chamaservico.tds104-senac.online';
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
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
            background: #f8fafc;
            min-height: 100vh;
        }

        /* ===== DYNAMIC STATUS HEADER ===== */
        .status-header {
            background: linear-gradient(135deg, var(--status-color-primary, #667eea) 0%, var(--status-color-secondary, #764ba2) 100%);
            border-radius: 16px;
            padding: 24px 32px;
            margin-bottom: 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            position: relative;
            overflow: hidden;
        }
        
        .status-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            opacity: 0.3;
        }
        
        .status-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            opacity: 0.4;
        }
        
        .status-header-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .status-title {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }
        
        .status-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            backdrop-filter: blur(10px);
        }
        
        .status-text {
            flex: 1;
        }
        
        .status-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            margin: 4px 0 0 0;
        }
        
        .status-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .status-action-btn {
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-action-btn:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.5);
            color: white;
            transform: translateY(-2px);
        }
        
        .status-action-btn.primary {
            background: rgba(255,255,255,0.9);
            color: var(--status-color-primary, #667eea);
            border-color: white;
        }
        
        .status-action-btn.primary:hover {
            background: white;
            color: var(--status-color-primary, #667eea);
        }

        /* ===== STATUS COLOR VARIATIONS ===== */
        .status-aguardando {
            --status-color-primary: #f59e0b;
            --status-color-secondary: #d97706;
        }
        
        .status-analise {
            --status-color-primary: #3b82f6;
            --status-color-secondary: #1d4ed8;
        }
        
        .status-aceita {
            --status-color-primary: #10b981;
            --status-color-secondary: #059669;
        }
        
        .status-andamento {
            --status-color-primary: #8b5cf6;
            --status-color-secondary: #7c3aed;
        }
        
        .status-concluido {
            --status-color-primary: #06b6d4;
            --status-color-secondary: #0891b2;
        }
        
        .status-cancelado {
            --status-color-primary: #ef4444;
            --status-color-secondary: #dc2626;
        }

        /* ===== THREE COLUMN LAYOUT ===== */
        .command-center-layout {
            display: grid;
            grid-template-columns: 280px 1fr 320px;
            gap: 24px;
            align-items: start;
        }
        
        @media (max-width: 1200px) {
            .command-center-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* ===== LEFT COLUMN: CLIENT SUMMARY ===== */
        .client-summary {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            position: sticky;
            top: 20px;
        }
        
        .client-avatar-section {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .client-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: bold;
            margin: 0 auto 12px;
        }
        
        .client-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 4px 0;
        }
        
        .client-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            color: #f59e0b;
            font-size: 0.9rem;
        }
        
        .client-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .client-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f8fafc;
        }
        
        .client-info-item:last-child {
            border-bottom: none;
        }
        
        .client-info-icon {
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .client-info-text {
            flex: 1;
        }
        
        .client-info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }
        
        .client-info-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 500;
            margin: 0;
        }

        /* ===== CENTER COLUMN: INTERACTIVE TIMELINE ===== */
        .timeline-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .timeline-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .timeline-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .timeline-body {
            padding: 0;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .timeline-item {
            position: relative;
            padding: 24px;
            border-bottom: 1px solid #f8fafc;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            background: #fafbfc;
        }
        
        .timeline-item:last-child {
            border-bottom: none;
        }
        
        .timeline-connector {
            position: absolute;
            left: 24px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        
        .timeline-item:last-child .timeline-connector {
            display: none;
        }
        
        .timeline-icon {
            position: absolute;
            left: 16px;
            top: 24px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: white;
            z-index: 2;
        }
        
        .timeline-icon.created { background: #3b82f6; }
        .timeline-icon.viewed { background: #06b6d4; }
        .timeline-icon.proposal { background: #8b5cf6; }
        .timeline-icon.status { background: #f59e0b; }
        .timeline-icon.comment { background: #10b981; }
        .timeline-icon.image { background: #ef4444; }
        
        .timeline-content {
            margin-left: 48px;
        }
        
        .timeline-item-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .timeline-item-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            flex: 1;
        }
        
        .timeline-item-time {
            font-size: 0.8rem;
            color: #64748b;
            white-space: nowrap;
            margin-left: 12px;
        }
        
        .timeline-item-description {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 16px 0;
        }
        
        .timeline-item-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .timeline-action-btn {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .timeline-action-btn:hover {
            background: #e2e8f0;
            color: #334155;
        }
        
        .timeline-action-btn.primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .timeline-action-btn.primary:hover {
            background: #2563eb;
            color: white;
        }
        
        .timeline-action-btn.success {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .timeline-action-btn.success:hover {
            background: #059669;
            color: white;
        }
        
        .timeline-action-btn.danger {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        
        .timeline-action-btn.danger:hover {
            background: #dc2626;
            color: white;
        }
        
        /* Timeline Images */
        .timeline-images {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        
        .timeline-image-thumb {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .timeline-image-thumb:hover {
            transform: scale(1.05);
        }
        
        .timeline-image-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .timeline-image-count {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: rgba(0,0,0,0.8);
            color: white;
            font-size: 0.7rem;
            padding: 2px 4px;
            border-radius: 4px;
        }

        /* ===== RIGHT COLUMN: ACTIONS & METADATA ===== */
        .metadata-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .metadata-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .metadata-card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .metadata-card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .metadata-card-icon {
            color: #64748b;
        }
        
        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .stat-item-compact {
            text-align: center;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        
        .stat-item-compact:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
        }
        
        /* Service Details */
        .service-details-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .service-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
        }
        
        .service-detail-item:last-child {
            border-bottom: none;
        }
        
        .service-detail-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .service-detail-value {
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 600;
        }
        
        .service-detail-value.price {
            color: #10b981;
            font-size: 1rem;
        }
        
        /* Address Card */
        .address-card-compact {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .address-card-compact::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .address-content {
            position: relative;
            z-index: 2;
        }
        
        .address-text {
            font-size: 0.9rem;
            line-height: 1.4;
            margin: 0 0 16px 0;
            opacity: 0.9;
        }
        
        .address-action {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .address-action:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-1px);
        }
        
        /* Action Buttons */
        .action-buttons-grid {
            display: grid;
            gap: 10px;
        }
        
        .action-btn-modern {
            background: white;
            border: 2px solid #e2e8f0;
            color: #475569;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .action-btn-modern:hover {
            border-color: #cbd5e1;
            color: #334155;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .action-btn-modern.primary {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .action-btn-modern.primary:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }
        
        .action-btn-modern.success {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        
        .action-btn-modern.success:hover {
            background: #059669;
            border-color: #059669;
            color: white;
        }
        
        .action-btn-modern.warning {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }
        
        .action-btn-modern.warning:hover {
            background: #d97706;
            border-color: #d97706;
            color: white;
        }

        /* ===== LIGHTBOX MODAL ===== */
        .lightbox-modal .modal-content {
            background: transparent;
            border: none;
            box-shadow: none;
        }
        
        .lightbox-modal .modal-header {
            border: none;
            background: rgba(0,0,0,0.8);
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .lightbox-modal .modal-body {
            padding: 0;
            text-align: center;
        }
        
        .lightbox-modal img {
            max-width: 100%;
            max-height: 85vh;
            border-radius: 0 0 8px 8px;
        }
        
        .lightbox-modal .btn-close {
            filter: invert(1);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1200px) {
            .status-header {
                padding: 20px 24px;
            }
            
            .status-actions {
                margin-top: 16px;
            }
            
            .client-summary {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .status-header {
                padding: 16px 20px;
            }
            
            .status-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .status-icon {
                width: 48px;
                height: 48px;
                font-size: 1.4rem;
            }
            
            .status-name {
                font-size: 1.2rem;
            }
            
            .status-actions {
                width: 100%;
            }
            
            .status-action-btn {
                flex: 1;
                justify-content: center;
            }
            
            .timeline-item {
                padding: 16px;
            }
            
            .timeline-content {
                margin-left: 36px;
            }
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
                            <a class="nav-link active" href="<?= url('admin/solicitacoes') ?>">
                                <i class="bi bi-list-task me-2"></i>
                                Solicitações
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
                <!-- Header Simplificado -->
                <div class="d-flex justify-content-between align-items-center pt-4 pb-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= url('admin/solicitacoes') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Voltar
                        </a>
                        <h1 class="h3 text-dark mb-0">
                            <i class="bi bi-eye me-2"></i>
                            Solicitação #<?= $solicitacao['id'] ?>
                        </h1>
                    </div>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>
                        Imprimir
                    </button>
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

                <!-- Dynamic Status Header -->
                <div class="status-header status-aguardando">
                    <div class="status-header-content">
                        <div class="status-title">
                            <div class="status-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="status-text">
                                <h2 class="status-name"><?= htmlspecialchars($solicitacao['status_nome']) ?></h2>
                                <p class="status-subtitle">Aguardando propostas de prestadores qualificados</p>
                            </div>
                        </div>
                        <div class="status-actions">
                            <button type="button" class="status-action-btn primary" onclick="convidarPrestadores()">
                                <i class="bi bi-person-plus"></i>
                                Convidar Prestadores
                            </button>
                            <button type="button" class="status-action-btn" onclick="alterarStatus()">
                                <i class="bi bi-arrow-repeat"></i>
                                Alterar Status
                            </button>
                            <button type="button" class="status-action-btn" onclick="adicionarComentario()">
                                <i class="bi bi-chat-dots"></i>
                                Adicionar Comentário
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Three Column Layout -->
                <div class="command-center-layout">
                    <!-- LEFT COLUMN: Client Summary -->
                    <div class="client-summary">
                        <div class="client-avatar-section">
                            <div class="client-avatar">
                                <?= strtoupper(substr($solicitacao['cliente_nome'], 0, 2)) ?>
                            </div>
                            <h3 class="client-name"><?= htmlspecialchars($solicitacao['cliente_nome']) ?></h3>
                            <div class="client-rating">
                                <i class="bi bi-star-fill"></i>
                                <span>4.8</span>
                                <small class="text-muted ms-1">(23 avaliações)</small>
                            </div>
                        </div>

                        <ul class="client-info-list">
                            <li class="client-info-item">
                                <div class="client-info-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="client-info-text">
                                    <p class="client-info-label">Email</p>
                                    <p class="client-info-value"><?= htmlspecialchars($solicitacao['cliente_email']) ?></p>
                                </div>
                            </li>
                            <?php if ($solicitacao['cliente_telefone']): ?>
                                <li class="client-info-item">
                                    <div class="client-info-icon">
                                        <i class="bi bi-telephone"></i>
                                    </div>
                                    <div class="client-info-text">
                                        <p class="client-info-label">Telefone</p>
                                        <p class="client-info-value"><?= htmlspecialchars($solicitacao['cliente_telefone']) ?></p>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <li class="client-info-item">
                                <div class="client-info-icon">
                                    <i class="bi bi-calendar"></i>
                                </div>
                                <div class="client-info-text">
                                    <p class="client-info-label">Cliente desde</p>
                                    <p class="client-info-value">Janeiro 2023</p>
                                </div>
                            </li>
                            <li class="client-info-item">
                                <div class="client-info-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="client-info-text">
                                    <p class="client-info-label">Solicitações</p>
                                    <p class="client-info-value">12 anteriores</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- CENTER COLUMN: Interactive Timeline -->
                    <div class="timeline-container">
                        <div class="timeline-header">
                            <h3 class="timeline-title">
                                <i class="bi bi-clock-history"></i>
                                Histórico da Solicitação
                            </h3>
                        </div>
                        <div class="timeline-body">
                            <!-- Timeline Item 1: Criação -->
                            <div class="timeline-item">
                                <div class="timeline-connector"></div>
                                <div class="timeline-icon created">
                                    <i class="bi bi-plus"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-item-header">
                                        <h4 class="timeline-item-title">Solicitação Criada</h4>
                                        <span class="timeline-item-time"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></span>
                                    </div>
                                    <p class="timeline-item-description">
                                        <?= htmlspecialchars($solicitacao['cliente_nome']) ?> criou uma nova solicitação para <?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?>.
                                    </p>
                                    <div class="timeline-item-description">
                                        <strong>Descrição:</strong><br>
                                        "<?= htmlspecialchars(substr($solicitacao['descricao'], 0, 150)) ?>..."
                                    </div>
                                    <div class="timeline-item-actions">
                                        <button class="timeline-action-btn">
                                            <i class="bi bi-eye"></i>
                                            Ver descrição completa
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 2: Imagens -->
                            <div class="timeline-item">
                                <div class="timeline-connector"></div>
                                <div class="timeline-icon image">
                                    <i class="bi bi-image"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-item-header">
                                        <h4 class="timeline-item-title">Imagens Anexadas</h4>
                                        <span class="timeline-item-time"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'] . ' +5 minutes')) ?></span>
                                    </div>
                                    <p class="timeline-item-description">
                                        Cliente anexou 3 imagens para melhor detalhamento do serviço necessário.
                                    </p>
                                    <div class="timeline-images">
                                        <div class="timeline-image-thumb" onclick="openLightbox('https://via.placeholder.com/800x600/667eea/ffffff?text=Foto+1', 1)">
                                            <img src="https://via.placeholder.com/60x60/667eea/ffffff?text=1" alt="Foto 1">
                                        </div>
                                        <div class="timeline-image-thumb" onclick="openLightbox('https://via.placeholder.com/800x600/764ba2/ffffff?text=Foto+2', 2)">
                                            <img src="https://via.placeholder.com/60x60/764ba2/ffffff?text=2" alt="Foto 2">
                                        </div>
                                        <div class="timeline-image-thumb" onclick="openLightbox('https://via.placeholder.com/800x600/28a745/ffffff?text=Foto+3', 3)">
                                            <img src="https://via.placeholder.com/60x60/28a745/ffffff?text=3" alt="Foto 3">
                                            <span class="timeline-image-count">+1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 3: Primeira Visualização -->
                            <div class="timeline-item">
                                <div class="timeline-connector"></div>
                                <div class="timeline-icon viewed">
                                    <i class="bi bi-eye"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-item-header">
                                        <h4 class="timeline-item-title">Primeira Visualização</h4>
                                        <span class="timeline-item-time"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'] . ' +1 hour')) ?></span>
                                    </div>
                                    <p class="timeline-item-description">
                                        A solicitação foi visualizada por 5 prestadores de serviço qualificados na região.
                                    </p>
                                </div>
                            </div>

                            <!-- Timeline Item 4: Proposta Recebida -->
                            <div class="timeline-item">
                                <div class="timeline-connector"></div>
                                <div class="timeline-icon proposal">
                                    <i class="bi bi-file-text"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-item-header">
                                        <h4 class="timeline-item-title">Nova Proposta Recebida</h4>
                                        <span class="timeline-item-time"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'] . ' +2 hours')) ?></span>
                                    </div>
                                    <p class="timeline-item-description">
                                        <strong>João Silva - Eletricista</strong> enviou uma proposta de R$ 150,00 com prazo de 2 dias.
                                    </p>
                                    <div class="timeline-item-actions">
                                        <button class="timeline-action-btn primary">
                                            <i class="bi bi-eye"></i>
                                            Ver Proposta
                                        </button>
                                        <button class="timeline-action-btn success">
                                            <i class="bi bi-check"></i>
                                            Aprovar
                                        </button>
                                        <button class="timeline-action-btn danger">
                                            <i class="bi bi-x"></i>
                                            Recusar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 5: Status Atual -->
                            <div class="timeline-item">
                                <div class="timeline-icon status">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-item-header">
                                        <h4 class="timeline-item-title">Status Atual</h4>
                                        <span class="timeline-item-time">Agora</span>
                                    </div>
                                    <p class="timeline-item-description">
                                        Aguardando mais propostas ou decisão sobre as propostas existentes.
                                    </p>
                                    <div class="timeline-item-actions">
                                        <button class="timeline-action-btn primary" onclick="convidarPrestadores()">
                                            <i class="bi bi-person-plus"></i>
                                            Convidar Mais Prestadores
                                        </button>
                                        <button class="timeline-action-btn" onclick="adicionarComentario()">
                                            <i class="bi bi-chat-dots"></i>
                                            Adicionar Comentário
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Actions & Metadata -->
                    <div class="metadata-panel">
                        <!-- Quick Stats -->
                        <div class="metadata-card">
                            <div class="metadata-card-header">
                                <i class="bi bi-graph-up metadata-card-icon"></i>
                                <h4 class="metadata-card-title">Estatísticas</h4>
                            </div>
                            <div class="quick-stats">
                                <div class="stat-item-compact">
                                    <div class="stat-value">2</div>
                                    <div class="stat-label">Propostas</div>
                                </div>
                                <div class="stat-item-compact">
                                    <div class="stat-value">15</div>
                                    <div class="stat-label">Visualizações</div>
                                </div>
                                <div class="stat-item-compact">
                                    <div class="stat-value">3</div>
                                    <div class="stat-label">Dias Online</div>
                                </div>
                                <div class="stat-item-compact">
                                    <div class="stat-value text-success">4.8</div>
                                    <div class="stat-label">Avaliação</div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="metadata-card">
                            <div class="metadata-card-header">
                                <i class="bi bi-tools metadata-card-icon"></i>
                                <h4 class="metadata-card-title">Detalhes do Serviço</h4>
                            </div>
                            <ul class="service-details-list">
                                <li class="service-detail-item">
                                    <span class="service-detail-label">Tipo de Serviço</span>
                                    <span class="service-detail-value"><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></span>
                                </li>
                                <li class="service-detail-item">
                                    <span class="service-detail-label">Urgência</span>
                                    <span class="service-detail-value">
                                        <span class="badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($solicitacao['urgencia']) ?>
                                        </span>
                                    </span>
                                </li>
                                <?php if ($solicitacao['orcamento_estimado']): ?>
                                    <li class="service-detail-item">
                                        <span class="service-detail-label">Orçamento</span>
                                        <span class="service-detail-value price">R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?></span>
                                    </li>
                                <?php endif; ?>
                                <li class="service-detail-item">
                                    <span class="service-detail-label">Data Criação</span>
                                    <span class="service-detail-value"><?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?></span>
                                </li>
                            </ul>
                        </div>

                        <!-- Address -->
                        <div class="address-card-compact">
                            <div class="address-content">
                                <h4 style="margin: 0 0 12px 0; font-size: 1rem;">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    Localização do Serviço
                                </h4>
                                <div class="address-text">
                                    <?= htmlspecialchars($solicitacao['logradouro']) ?>, <?= htmlspecialchars($solicitacao['numero']) ?><br>
                                    <?= htmlspecialchars($solicitacao['bairro']) ?><br>
                                    <?= htmlspecialchars($solicitacao['cidade']) ?> - <?= htmlspecialchars($solicitacao['estado']) ?><br>
                                    CEP: <?= htmlspecialchars($solicitacao['cep']) ?>
                                </div>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($solicitacao['logradouro'] . ', ' . $solicitacao['numero'] . ', ' . $solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>" 
                                   target="_blank" class="address-action">
                                    <i class="bi bi-map"></i>
                                    Ver no Google Maps
                                </a>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="metadata-card">
                            <div class="metadata-card-header">
                                <i class="bi bi-lightning metadata-card-icon"></i>
                                <h4 class="metadata-card-title">Ações Rápidas</h4>
                            </div>
                            <div class="action-buttons-grid">
                                <button type="button" class="action-btn-modern warning" onclick="alterarStatus()">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Alterar Status
                                </button>
                                <a href="mailto:<?= $solicitacao['cliente_email'] ?>" class="action-btn-modern">
                                    <i class="bi bi-envelope"></i>
                                    Enviar Email
                                </a>
                                <button type="button" class="action-btn-modern primary" onclick="verPropostas()">
                                    <i class="bi bi-file-text"></i>
                                    Ver Propostas (2)
                                </button>
                                <button type="button" class="action-btn-modern success" onclick="convidarPrestadores()">
                                    <i class="bi bi-person-plus"></i>
                                    Convidar Prestadores
                                </button>
                                <button type="button" class="action-btn-modern" onclick="gerarRelatorio()">
                                    <i class="bi bi-download"></i>
                                    Gerar Relatório
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para alterar status - CORRIGIR ACTION -->
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
                            <form method="POST" action="<?= url('admin/solicitacoes/alterar-status') ?>">
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
                                        <textarea class="form-control" name="motivo" id="motivo" rows="3" 
                                                  placeholder="Descreva o motivo da alteração..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary btn-modern">Alterar Status</button>
                                </div>
                            </form>
                        </div>
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
                                <img id="modalImage" src="" class="img-fluid" alt="Imagem ampliada" 
                                     style="max-height: 80vh; border-radius: 10px;">
                            </div>
                            <div class="modal-footer">
                                <small class="text-muted me-auto">Clique na imagem para fechar</small>
                                <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lightbox Modal -->
                <div class="modal fade lightbox-modal" id="lightboxModal" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-white">
                                    <i class="bi bi-camera me-2"></i>Imagem <span id="lightboxImageNumber"></span>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <img id="lightboxImage" src="" class="img-fluid" alt="Imagem ampliada">
                            </div>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                    // ===== LIGHTBOX FUNCTIONALITY =====
                    function openLightbox(imageSrc, imageNumber) {
                        document.getElementById("lightboxImage").src = imageSrc;
                        document.getElementById("lightboxImageNumber").textContent = imageNumber;
                        new bootstrap.Modal(document.getElementById("lightboxModal")).show();
                    }
                    
                    // ===== ACTION FUNCTIONS =====
                    function alterarStatus() {
                        // Implementation for status change
                        alert('Função de alterar status será implementada');
                    }
                    
                    function convidarPrestadores() {
                        alert('Função de convidar prestadores será implementada');
                    }
                    
                    function adicionarComentario() {
                        alert('Função de adicionar comentário será implementada');
                    }
                    
                    function verPropostas() {
                        alert('Função de ver propostas será implementada');
                    }
                    
                    function gerarRelatorio() {
                        window.print();
                    }
                    
                    // ===== DYNAMIC STATUS HEADER =====
                    function updateStatusHeader(statusId, statusName, statusColor) {
                        const header = document.querySelector('.status-header');
                        const statusClasses = ['status-aguardando', 'status-analise', 'status-aceita', 'status-andamento', 'status-concluido', 'status-cancelado'];
                        
                        // Remove existing status classes
                        header.classList.remove(...statusClasses);
                        
                        // Add new status class based on statusId
                        const statusMap = {
                            1: 'status-aguardando',
                            2: 'status-analise',
                            3: 'status-aceita',
                            4: 'status-andamento',
                            5: 'status-concluido',
                            6: 'status-cancelado'
                        };
                        
                        header.classList.add(statusMap[statusId] || 'status-aguardando');
                        
                        // Update status name
                        header.querySelector('.status-name').textContent = statusName;
                        
                        // Update actions based on status
                        updateStatusActions(statusId);
                    }
                    
                    function updateStatusActions(statusId) {
                        const actionsContainer = document.querySelector('.status-actions');
                        let actionsHtml = '';
                        
                        switch(statusId) {
                            case 1: // Aguardando
                                actionsHtml = `
                                    <button type="button" class="status-action-btn primary" onclick="convidarPrestadores()">
                                        <i class="bi bi-person-plus"></i>Convidar Prestadores
                                    </button>
                                    <button type="button" class="status-action-btn" onclick="alterarStatus()">
                                        <i class="bi bi-arrow-repeat"></i>Alterar Status
                                    </button>
                                `;
                                break;
                            case 2: // Em Análise
                                actionsHtml = `
                                    <button type="button" class="status-action-btn primary" onclick="verPropostas()">
                                        <i class="bi bi-file-text"></i>Analisar Propostas
                                    </button>
                                    <button type="button" class="status-action-btn" onclick="alterarStatus()">
                                        <i class="bi bi-arrow-repeat"></i>Alterar Status
                                    </button>
                                `;
                                break;
                            // Add more cases as needed
                            default:
                                actionsHtml = `
                                    <button type="button" class="status-action-btn" onclick="alterarStatus()">
                                        <i class="bi bi-arrow-repeat"></i>Alterar Status
                                    </button>
                                `;
                        }
                        
                        actionsContainer.innerHTML = actionsHtml;
                    }
                    
                    // ===== TIMELINE INTERACTIONS =====
                    document.addEventListener('DOMContentLoaded', function() {
                        // Smooth scroll for timeline
                        const timelineBody = document.querySelector('.timeline-body');
                        if (timelineBody) {
                            timelineBody.style.scrollBehavior = 'smooth';
                        }
                        
                        // Add entrance animations
                        const timelineItems = document.querySelectorAll('.timeline-item');
                        timelineItems.forEach((item, index) => {
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-20px)';
                            
                            setTimeout(() => {
                                item.style.transition = 'all 0.5s ease';
                                item.style.opacity = '1';
                                item.style.transform = 'translateX(0)';
                            }, index * 100);
                        });
                    });
                </script>
            </main>
        </div>
    </div>
</body>
</html>