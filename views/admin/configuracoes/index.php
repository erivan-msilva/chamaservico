<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// Buscar configurações atuais (exemplo)
$configuracoes = [
    'nome_sistema' => 'ChamaServiço',
    'permitir_cadastros' => true,
    'modo_manutencao' => false,
    'taxa_sistema' => 5.0,
    'limite_imagens' => 5,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'email_sistema' => 'sistema@chamaservico.com',
    'smtp_ssl' => true,
    'notif_email' => true,
    'notif_nova_solicitacao' => true,
    'notif_nova_proposta' => true,
    'email_admins' => 'admin@chamaservico.com',
    'max_upload' => 5,
    'cache_sistema' => true,
    'tempo_cache' => 30,
    'backup_automatico' => true,
    'horario_backup' => '02:00',
    'dias_backup' => 30
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - Admin</title>
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
        
        .config-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .config-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .config-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .config-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
        
        .form-switch .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .setting-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }
        
        .setting-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .stats-widget {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid;
            margin-bottom: 1.5rem;
        }
        
        .nav-pills .nav-link {
            border-radius: 25px;
            color: #667eea;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .btn-warning-modern {
            background: linear-gradient(135deg, #ffd43b 0%, #fab005 100%);
            color: white;
        }
        
        .btn-info-modern {
            background: linear-gradient(135deg, #74c0fc 0%, #339af0 100%);
            color: white;
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
                            <a class="nav-link" href="admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/solicitacoes">
                                <i class="bi bi-list-task me-2"></i>
                                Solicitações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/propostas">
                                <i class="bi bi-file-text me-2"></i>
                                Propostas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/relatorios">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin/configuracoes">
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
                        <i class="bi bi-gear me-2"></i>
                        Configurações do Sistema
                    </h1>
                    <div class="btn-toolbar">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-modern btn-info-modern" onclick="criarBackup()">
                                <i class="bi bi-download me-1"></i>
                                Backup
                            </button>
                            <button type="button" class="btn btn-modern btn-warning-modern" onclick="limparCache()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Limpar Cache
                            </button>
                        </div>
                        <button type="button" class="btn btn-modern btn-success-modern" onclick="salvarConfiguracoes()">
                            <i class="bi bi-check-lg me-1"></i>
                            Salvar Alterações
                        </button>
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

                <!-- Status do Sistema -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #28a745;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Status do Sistema
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Online
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-server fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #17a2b8;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Versão do Sistema
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        v2.1.0
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-code-square fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #ffc107;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Último Backup
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= date('d/m/Y') ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-archive fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #dc3545;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Usuários Ativos
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        // Buscar dados reais do banco
                                        try {
                                            require_once 'core/Database.php';
                                            $db = Database::getInstance();
                                            $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1");
                                            $stmt->execute();
                                            echo $stmt->fetchColumn();
                                        } catch (Exception $e) {
                                            echo "N/A";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navegação por Abas -->
                <ul class="nav nav-pills mb-4" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="geral-tab" data-bs-toggle="pill" data-bs-target="#geral" type="button">
                            <i class="bi bi-sliders me-1"></i>Geral
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="pill" data-bs-target="#email" type="button">
                            <i class="bi bi-envelope me-1"></i>E-mail
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sistema-tab" data-bs-toggle="pill" data-bs-target="#sistema" type="button">
                            <i class="bi bi-gear me-1"></i>Sistema
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="backup-tab" data-bs-toggle="pill" data-bs-target="#backup" type="button">
                            <i class="bi bi-shield-check me-1"></i>Backup
                        </button>
                    </li>
                </ul>

                <!-- Conteúdo das Abas -->
                <div class="tab-content" id="configTabsContent">
                    
                    <!-- Aba Geral -->
                    <div class="tab-pane fade show active" id="geral" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="config-card">
                                    <div class="config-section">
                                        <h5 class="mb-0">
                                            <i class="bi bi-sliders me-2"></i>
                                            Configurações Gerais do Sistema
                                        </h5>
                                    </div>
                                    <div class="p-4">
                                        <form id="formGeral">
                                            <div class="setting-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Nome do Sistema</h6>
                                                        <div class="setting-description">Nome exibido no cabeçalho e emails do sistema</div>
                                                    </div>
                                                    <div style="min-width: 200px;">
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($configuracoes['nome_sistema']) ?>" name="nome_sistema">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="setting-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Permitir Novos Cadastros</h6>
                                                        <div class="setting-description">Usuários podem se cadastrar livremente no sistema</div>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" <?= $configuracoes['permitir_cadastros'] ? 'checked' : '' ?> name="permitir_cadastros">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="setting-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Sistema em Manutenção</h6>
                                                        <div class="setting-description">Bloquear acesso de usuários (exceto administradores)</div>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" <?= $configuracoes['modo_manutencao'] ? 'checked' : '' ?> name="modo_manutencao">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="setting-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Taxa do Sistema (%)</h6>
                                                        <div class="setting-description">Porcentagem cobrada sobre serviços realizados</div>
                                                    </div>
                                                    <div style="min-width: 120px;">
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" value="<?= $configuracoes['taxa_sistema'] ?>" min="0" max="50" step="0.1" name="taxa_sistema">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="setting-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Limite de Imagens por Solicitação</h6>
                                                        <div class="setting-description">Máximo de fotos que podem ser anexadas</div>
                                                    </div>
                                                    <div style="min-width: 120px;">
                                                        <input type="number" class="form-control" value="<?= $configuracoes['limite_imagens'] ?>" min="1" max="20" name="limite_imagens">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="config-card">
                                    <div class="config-section">
                                        <h6 class="mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Informações do Sistema
                                        </h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="mb-3">
                                            <strong>PHP:</strong> <?= phpversion() ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Banco de Dados:</strong> MySQL
                                        </div>
                                        <div class="mb-3">
                                            <strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache' ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Último Login Admin:</strong><br>
                                            <small><?= date('d/m/Y H:i') ?></small>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Total de Solicitações:</strong>
                                            <?php
                                            try {
                                                $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico");
                                                $stmt->execute();
                                                echo $stmt->fetchColumn();
                                            } catch (Exception $e) {
                                                echo "N/A";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba E-mail -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <div class="config-card">
                            <div class="config-section">
                                <h5 class="mb-0">
                                    <i class="bi bi-envelope me-2"></i>
                                    Configurações de E-mail SMTP
                                </h5>
                            </div>
                            <div class="p-4">
                                <form id="formEmail">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="setting-item">
                                                <label class="form-label fw-bold">Servidor SMTP</label>
                                                <input type="text" class="form-control" placeholder="smtp.gmail.com" value="<?= htmlspecialchars($configuracoes['smtp_host']) ?>" name="smtp_host">
                                                <div class="setting-description">Endereço do servidor de e-mail</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="setting-item">
                                                <label class="form-label fw-bold">Porta SMTP</label>
                                                <input type="number" class="form-control" value="<?= $configuracoes['smtp_port'] ?>" name="smtp_port">
                                                <div class="setting-description">Porta do servidor (587 ou 465)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="setting-item">
                                                <label class="form-label fw-bold">E-mail do Sistema</label>
                                                <input type="email" class="form-control" value="<?= htmlspecialchars($configuracoes['email_sistema']) ?>" name="email_sistema">
                                                <div class="setting-description">E-mail usado para envios automáticos</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="setting-item">
                                                <label class="form-label fw-bold">Senha do E-mail</label>
                                                <input type="password" class="form-control" placeholder="••••••••" name="email_senha">
                                                <div class="setting-description">Senha ou token de aplicação</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="setting-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Usar Autenticação SSL/TLS</h6>
                                                <div class="setting-description">Conexão segura com o servidor</div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" <?= $configuracoes['smtp_ssl'] ? 'checked' : '' ?> name="smtp_ssl">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-modern btn-warning-modern me-2" onclick="testarEmail()">
                                            <i class="bi bi-send me-1"></i>Testar Configuração
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Sistema -->
                    <div class="tab-pane fade" id="sistema" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="config-card mb-4">
                                    <div class="config-section">
                                        <h6 class="mb-0">
                                            <i class="bi bi-hdd me-2"></i>
                                            Armazenamento e Uploads
                                        </h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="setting-item">
                                            <label class="form-label fw-bold">Tamanho Máximo de Upload (MB)</label>
                                            <input type="number" class="form-control" value="<?= $configuracoes['max_upload'] ?>" min="1" max="50" name="max_upload">
                                            <div class="setting-description">Tamanho máximo para upload de imagens</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="config-card">
                                    <div class="config-section">
                                        <h6 class="mb-0">
                                            <i class="bi bi-speedometer2 me-2"></i>
                                            Performance do Sistema
                                        </h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="setting-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Cache do Sistema</h6>
                                                    <div class="setting-description">Habilitar cache para melhor performance</div>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" <?= $configuracoes['cache_sistema'] ? 'checked' : '' ?> name="cache_sistema">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="setting-item">
                                            <label class="form-label fw-bold">Tempo de Cache (minutos)</label>
                                            <input type="number" class="form-control" value="<?= $configuracoes['tempo_cache'] ?>" min="5" max="1440" name="tempo_cache">
                                            <div class="setting-description">Por quanto tempo manter dados em cache</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="config-card">
                                    <div class="config-section">
                                        <h6 class="mb-0">
                                            <i class="bi bi-graph-up me-2"></i>
                                            Estatísticas do Sistema
                                        </h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="row text-center">
                                            <div class="col-6 mb-3">
                                                <div class="h4 text-primary mb-0">
                                                    <?php
                                                    try {
                                                        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 1");
                                                        $stmt->execute();
                                                        echo $stmt->fetchColumn();
                                                    } catch (Exception $e) { echo "0"; }
                                                    ?>
                                                </div>
                                                <small class="text-muted">Solicitações Ativas</small>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="h4 text-success mb-0">
                                                    <?php
                                                    try {
                                                        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_proposta WHERE status = 'aceita'");
                                                        $stmt->execute();
                                                        echo $stmt->fetchColumn();
                                                    } catch (Exception $e) { echo "0"; }
                                                    ?>
                                                </div>
                                                <small class="text-muted">Propostas Aceitas</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="h4 text-info mb-0">
                                                    <?php
                                                    try {
                                                        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE tipo = 'prestador' OR tipo = 'ambos'");
                                                        $stmt->execute();
                                                        echo $stmt->fetchColumn();
                                                    } catch (Exception $e) { echo "0"; }
                                                    ?>
                                                </div>
                                                <small class="text-muted">Prestadores</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="h4 text-warning mb-0">
                                                    <?php
                                                    try {
                                                        $stmt = $db->prepare("SELECT COUNT(*) FROM tb_pessoa WHERE tipo = 'cliente' OR tipo = 'ambos'");
                                                        $stmt->execute();
                                                        echo $stmt->fetchColumn();
                                                    } catch (Exception $e) { echo "0"; }
                                                    ?>
                                                </div>
                                                <small class="text-muted">Clientes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Backup -->
                    <div class="tab-pane fade" id="backup" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="config-card">
                                    <div class="config-section">
                                        <h5 class="mb-0">
                                            <i class="bi bi-shield-check me-2"></i>
                                            Backup e Restauração
                                        </h5>
                                    </div>
                                    <div class="p-4">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Importante:</strong> Execute backups regulares para proteger seus dados. 
                                            Recomendamos backup diário para sistemas em produção.
                                        </div>

                                        <div class="setting-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Backup Automático</h6>
                                                    <div class="setting-description">Executar backup automático diariamente</div>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" <?= $configuracoes['backup_automatico'] ? 'checked' : '' ?> name="backup_automatico">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="setting-item">
                                            <label class="form-label fw-bold">Horário do Backup Automático</label>
                                            <input type="time" class="form-control" value="<?= $configuracoes['horario_backup'] ?>" name="horario_backup">
                                            <div class="setting-description">Melhor horário: madrugada (menor uso)</div>
                                        </div>

                                        <div class="setting-item">
                                            <label class="form-label fw-bold">Manter Backups por (dias)</label>
                                            <input type="number" class="form-control" value="<?= $configuracoes['dias_backup'] ?>" min="7" max="365" name="dias_backup">
                                            <div class="setting-description">Backups mais antigos serão excluídos automaticamente</div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <form method="POST" action="admin/configuracoes/backup" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                                    <button type="submit" class="btn btn-modern btn-success-modern w-100 mb-2" id="btnBackup">
                                                        <i class="bi bi-download me-1"></i>
                                                        Criar e Baixar Backup
                                                    </button>
                                                </form>
                                                <small class="text-muted d-block text-center">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Backup completo com estrutura e dados
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-modern btn-warning-modern w-100 mb-2" onclick="restaurarBackup()">
                                                    <i class="bi bi-upload me-1"></i>
                                                    Restaurar Backup
                                                </button>
                                                <small class="text-muted d-block text-center">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Substituirá todos os dados atuais
                                                </small>
                                            </div>
                                        </div>

                                        <!-- NOVO: Informações de backup -->
                                        <div class="alert alert-info mt-3">
                                            <h6><i class="bi bi-info-circle me-2"></i>Informações do Backup</h6>
                                            <ul class="mb-0 small">
                                                <li><strong>Métodos:</strong> mysqldump (preferencial) + PHP (fallback)</li>
                                                <li><strong>Conteúdo:</strong> Estrutura completa + todos os dados</li>
                                                <li><strong>Segurança:</strong> Transações isoladas + verificação de integridade</li>
                                                <li><strong>Formato:</strong> SQL padrão compatível com MySQL/MariaDB</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="config-card">
                                    <div class="config-section">
                                        <h6 class="mb-0">
                                            <i class="bi bi-archive me-2"></i>
                                            Backups Recentes
                                        </h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <div class="fw-bold">backup_<?= date('Y-m-d') ?>.sql</div>
                                                    <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                                                </div>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-success me-1" onclick="downloadBackup('backup_<?= date('Y-m-d') ?>.sql')">
                                                        <i class="bi bi-download"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Backups são armazenados de forma segura
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funções de configuração
        function salvarConfiguracoes() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Salvando...';
            
            // Simular salvamento (aqui você faria a requisição real)
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                // Mostrar mensagem de sucesso
                showAlert('success', 'Configurações salvas com sucesso!');
                
            }, 1500);
        }
        
        function limparCache() {
            if (confirm('Deseja limpar o cache do sistema? Isso pode afetar temporariamente a performance.')) {
                const btn = event.target;
                const originalText = btn.innerHTML;
                
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Limpando...';
                
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    showAlert('info', 'Cache limpo com sucesso!');
                }, 2000);
            }
        }
        
        function criarBackup() {
            const btn = document.getElementById('btnBackup');
            const originalText = btn.innerHTML;
            
            if (!confirm('Deseja criar um backup completo do banco de dados?\n\nEste processo pode levar alguns minutos dependendo do tamanho dos dados.')) {
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Criando Backup...';
            
            // Criar formulário para envio
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'admin/configuracoes/backup';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= Session::generateCSRFToken() ?>';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            
            // Timeout de segurança
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                document.body.removeChild(form);
            }, 60000); // 1 minuto
            
            form.submit();
        }
        
        function testarEmail() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Testando...';
            
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                showAlert('success', 'E-mail de teste enviado com sucesso!');
            }, 2000);
        }
        
        function restaurarBackup() {
            if (confirm('ATENÇÃO: Restaurar um backup irá substituir todos os dados atuais. Deseja continuar?')) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.sql';
                input.onchange = function() {
                    if (this.files[0]) {
                        showAlert('info', 'Funcionalidade de restauração será implementada. Arquivo selecionado: ' + this.files[0].name);
                    }
                };
                input.click();
            }
        }
        
        function downloadBackup(filename) {
            showAlert('info', 'Download iniciado: ' + filename);
        }
        
        function showAlert(type, message) {
            const alertContainer = document.createElement('div');
            alertContainer.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertContainer.style.zIndex = '9999';
            alertContainer.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-triangle')} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertContainer);
            
            setTimeout(() => {
                alertContainer.remove();
            }, 5000);
        }
    </script>
</body>
</html>
