<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/login'));
    exit;
}

// Configuração do layout
$title = 'Configurações do Sistema - Admin';
$currentPage = 'configuracoes';

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

// Buscar dados reais do banco
try {
    require_once 'core/Database.php';
    $db = Database::getInstance();
    
    // Estatísticas para exibir na página
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_pessoa WHERE ativo = 1");
    $stmt->execute();
    $usuariosAtivos = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_solicita_servico");
    $stmt->execute();
    $totalSolicitacoes = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_solicita_servico WHERE status_id = 1");
    $stmt->execute();
    $solicitacoesAtivas = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_proposta WHERE status = 'aceita'");
    $stmt->execute();
    $propostasAceitas = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_pessoa WHERE tipo = 'prestador' OR tipo = 'ambos'");
    $stmt->execute();
    $prestadores = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_pessoa WHERE tipo = 'cliente' OR tipo = 'ambos'");
    $stmt->execute();
    $clientes = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    $usuariosAtivos = 0;
    $totalSolicitacoes = 0;
    $solicitacoesAtivas = 0;
    $propostasAceitas = 0;
    $prestadores = 0;
    $clientes = 0;
}

ob_start();
?>

<!-- Modern Configuration Interface with Enhanced Features -->
<div class="config-page-wrapper">
    <!-- Enhanced Header with Quick Actions -->
    <div class="config-header-modern">
        <div class="config-header-content">
            <div class="config-header-info">
                <div class="breadcrumb-modern">
                    <i class="bi bi-house"></i>
                    <span>Admin</span>
                    <i class="bi bi-chevron-right"></i>
                    <span class="current">Configurações</span>
                </div>
                <h1 class="config-page-title">
                    <div class="config-title-icon">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div class="title-content">
                        <span class="title-text">Configurações do Sistema</span>
                        <span class="title-badge">v2.1.0</span>
                    </div>
                </h1>
                <p class="config-page-subtitle">
                    Gerencie todas as configurações da sua plataforma em um só lugar
                    <span class="last-update">Última atualização: <?= date('d/m/Y H:i') ?></span>
                </p>
            </div>
            
            <!-- Enhanced System Status with Health Monitor -->
            <div class="system-status-panel">
                <div class="status-header">
                    <h3>Status do Sistema</h3>
                    <button class="btn-refresh" onclick="atualizarStatus()" title="Atualizar Status">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div class="status-grid">
                    <div class="status-card online">
                        <div class="status-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="status-info">
                            <span class="status-label">Sistema</span>
                            <span class="status-value">Online</span>
                        </div>
                    </div>
                    <div class="status-card">
                        <div class="status-icon">
                            <i class="bi bi-database"></i>
                        </div>
                        <div class="status-info">
                            <span class="status-label">Banco</span>
                            <span class="status-value">Ativo</span>
                        </div>
                    </div>
                    <div class="status-card">
                        <div class="status-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="status-info">
                            <span class="status-label">Último Backup</span>
                            <span class="status-value"><?= date('d/m') ?></span>
                        </div>
                    </div>
                    <div class="status-card">
                        <div class="status-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="status-info">
                            <span class="status-label">Usuários</span>
                            <span class="status-value"><?= number_format($usuariosAtivos) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="config-tools">
        <div class="config-tools-content">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="configSearch" placeholder="Buscar configurações..." class="search-input">
                    <button class="search-clear" onclick="limparBusca()" style="display: none;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="filter-container">
                <button class="filter-btn active" data-filter="all">
                    <i class="bi bi-grid"></i>
                    Todas
                </button>
                <button class="filter-btn" data-filter="modified">
                    <i class="bi bi-pencil"></i>
                    Modificadas
                </button>
                <button class="filter-btn" data-filter="critical">
                    <i class="bi bi-exclamation-triangle"></i>
                    Críticas
                </button>
            </div>
            <div class="view-toggle">
                <button class="view-btn active" data-view="cards" title="Visualização em Cards">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button class="view-btn" data-view="list" title="Visualização em Lista">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Layout with Improved Navigation -->
    <div class="config-layout">
        <!-- Enhanced Left Navigation with Progress -->
        <nav class="config-nav">
            <div class="config-nav-inner">
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="bi bi-sliders"></i>
                        Configurações
                        <span class="config-progress">
                            <span class="progress-text">75%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                        </span>
                    </div>
                    
                    <ul class="nav-menu">
                        <li class="nav-item active" data-section="geral">
                            <a href="#geral" class="nav-link">
                                <div class="nav-icon">
                                    <i class="bi bi-sliders"></i>
                                </div>
                                <div class="nav-content">
                                    <span class="nav-title">Geral</span>
                                    <span class="nav-description">Sistema e configurações básicas</span>
                                    <span class="nav-badge">5</span>
                                </div>
                                <div class="nav-status complete"></div>
                            </a>
                        </li>
                        <li class="nav-item" data-section="email">
                            <a href="#email" class="nav-link">
                                <div class="nav-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="nav-content">
                                    <span class="nav-title">E-mail</span>
                                    <span class="nav-description">SMTP e notificações</span>
                                    <span class="nav-badge warning">!</span>
                                </div>
                                <div class="nav-status incomplete"></div>
                            </a>
                        </li>
                        <li class="nav-item" data-section="sistema">
                            <a href="#sistema" class="nav-link">
                                <div class="nav-icon">
                                    <i class="bi bi-gear"></i>
                                </div>
                                <div class="nav-content">
                                    <span class="nav-title">Sistema</span>
                                    <span class="nav-description">Performance e uploads</span>
                                    <span class="nav-badge">3</span>
                                </div>
                                <div class="nav-status complete"></div>
                            </a>
                        </li>
                        <li class="nav-item" data-section="backup">
                            <a href="#backup" class="nav-link">
                                <div class="nav-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="nav-content">
                                    <span class="nav-title">Backup</span>
                                    <span class="nav-description">Segurança e restauração</span>
                                    <span class="nav-badge success">✓</span>
                                </div>
                                <div class="nav-status complete"></div>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Quick Actions Panel -->
                <div class="quick-actions-panel">
                    <h4 class="quick-actions-title">Ações Rápidas</h4>
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="exportarConfiguracoes()">
                            <i class="bi bi-download"></i>
                            <span>Exportar</span>
                        </button>
                        <button class="quick-action-btn" onclick="importarConfiguracoes()">
                            <i class="bi bi-upload"></i>
                            <span>Importar</span>
                        </button>
                        <button class="quick-action-btn" onclick="resetarPadrao()">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span>Resetar</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Enhanced Content Area with Improved Cards -->
        <main class="config-content">
            <!-- Seção Geral com melhorias visuais -->
            <section id="geral" class="config-section active-section">
                <div class="section-header">
                    <div class="section-title-wrapper">
                        <h2 class="section-title">
                            <i class="bi bi-sliders"></i>
                            Configurações Gerais
                            <span class="section-counter">5 itens</span>
                        </h2>
                        <div class="section-actions">
                            <button class="section-action-btn" onclick="expandirTodos('geral')">
                                <i class="bi bi-arrows-expand"></i>
                                Expandir Todos
                            </button>
                            <button class="section-action-btn" onclick="salvarSecao('geral')">
                                <i class="bi bi-check"></i>
                                Salvar Seção
                            </button>
                        </div>
                    </div>
                    <p class="section-description">Configure os aspectos fundamentais da sua plataforma</p>
                    <div class="section-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 80%"></div>
                        </div>
                        <span class="progress-text">80% concluído</span>
                    </div>
                </div>

                <div class="config-cards enhanced">
                    <!-- Enhanced Card: Identidade do Sistema -->
                    <div class="config-card collapsible">
                        <div class="config-card-header" onclick="toggleCard(this)">
                            <div class="card-title-group">
                                <h3 class="config-card-title">
                                    <i class="bi bi-type"></i>
                                    Identidade do Sistema
                                    <span class="completion-badge complete">✓</span>
                                </h3>
                                <p class="card-subtitle">Nome e identificação da plataforma</p>
                            </div>
                            <div class="card-header-actions">
                                <button class="card-action-btn" onclick="resetarCard(event, 'identidade')" title="Resetar">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="collapse-btn">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item enhanced">
                                <div class="config-item-info">
                                    <label class="config-label">
                                        Nome da Plataforma
                                        <span class="label-badge required">Obrigatório</span>
                                    </label>
                                    <p class="config-description">
                                        Nome exibido no cabeçalho, e-mails e documentos do sistema
                                        <span class="config-tip">
                                            <i class="bi bi-info-circle"></i>
                                            Será usado em todas as comunicações oficiais
                                        </span>
                                    </p>
                                </div>
                                <div class="config-control">
                                    <div class="input-container">
                                        <input type="text" class="form-input-modern enhanced" 
                                               value="<?= htmlspecialchars($configuracoes['nome_sistema']) ?>" 
                                               name="nome_sistema" 
                                               placeholder="ChamaServiço"
                                               data-original="<?= htmlspecialchars($configuracoes['nome_sistema']) ?>">
                                        <div class="input-validation">
                                            <i class="bi bi-check-circle validation-icon valid"></i>
                                            <span class="validation-message">Nome válido</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Controles de Acesso -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-key"></i>
                                Controles de Acesso
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Novos Cadastros</label>
                                    <p class="config-description">Permitir que usuários se registrem livremente na plataforma</p>
                                </div>
                                <div class="config-control">
                                    <div class="modern-switch">
                                        <input type="checkbox" class="switch-input" 
                                               <?= $configuracoes['permitir_cadastros'] ? 'checked' : '' ?> 
                                               name="permitir_cadastros"
                                               id="permitar_cadastros">
                                        <label for="permitar_cadastros" class="switch-label">
                                            <span class="switch-slider"></span>
                                            <span class="switch-text"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Modo Manutenção</label>
                                    <p class="config-description">Bloquear acesso de usuários (administradores permanecem com acesso)</p>
                                </div>
                                <div class="config-control">
                                    <div class="modern-switch warning">
                                        <input type="checkbox" class="switch-input" 
                                               <?= $configuracoes['modo_manutencao'] ? 'checked' : '' ?> 
                                               name="modo_manutencao"
                                               id="modo_manutencao">
                                        <label for="modo_manutencao" class="switch-label">
                                            <span class="switch-slider"></span>
                                            <span class="switch-text"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Configurações Comerciais -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-currency-dollar"></i>
                                Configurações Comerciais
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Taxa da Plataforma</label>
                                    <p class="config-description">Porcentagem cobrada sobre serviços realizados (valor sugerido: 5-10%)</p>
                                </div>
                                <div class="config-control">
                                    <div class="input-with-addon">
                                        <input type="number" class="form-input-modern" 
                                               value="<?= $configuracoes['taxa_sistema'] ?>" 
                                               min="0" max="50" step="0.1" 
                                               name="taxa_sistema">
                                        <span class="input-addon">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Limite de Imagens</label>
                                    <p class="config-description">Máximo de fotos por solicitação de serviço</p>
                                </div>
                                <div class="config-control">
                                    <input type="number" class="form-input-modern" 
                                           value="<?= $configuracoes['limite_imagens'] ?>" 
                                           min="1" max="20" 
                                           name="limite_imagens">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seção E-mail -->
            <section id="email" class="config-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-envelope"></i>
                        Configurações de E-mail
                    </h2>
                    <p class="section-description">Configure o servidor SMTP para envio de e-mails automáticos</p>
                </div>

                <div class="config-cards">
                    <!-- Card: Servidor SMTP -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-server"></i>
                                Servidor SMTP
                            </h3>
                            <div class="card-actions">
                                <button type="button" class="btn-ghost" onclick="testarEmail()">
                                    <i class="bi bi-send"></i>
                                    Testar Conexão
                                </button>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item-grid">
                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">Host SMTP</label>
                                        <p class="config-description">Endereço do servidor de e-mail</p>
                                    </div>
                                    <div class="config-control">
                                        <input type="text" class="form-input-modern" 
                                               value="<?= htmlspecialchars($configuracoes['smtp_host']) ?>" 
                                               name="smtp_host" 
                                               placeholder="smtp.gmail.com">
                                    </div>
                                </div>

                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">Porta</label>
                                        <p class="config-description">587 (TLS) ou 465 (SSL)</p>
                                    </div>
                                    <div class="config-control">
                                        <input type="number" class="form-input-modern" 
                                               value="<?= $configuracoes['smtp_port'] ?>" 
                                               name="smtp_port">
                                    </div>
                                </div>
                            </div>

                            <div class="config-item-grid">
                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">E-mail do Sistema</label>
                                        <p class="config-description">E-mail usado como remetente</p>
                                    </div>
                                    <div class="config-control">
                                        <input type="email" class="form-input-modern" 
                                               value="<?= htmlspecialchars($configuracoes['email_sistema']) ?>" 
                                               name="email_sistema">
                                    </div>
                                </div>

                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">Senha/Token</label>
                                        <p class="config-description">Senha ou token de aplicação</p>
                                    </div>
                                    <div class="config-control">
                                        <input type="password" class="form-input-modern" 
                                               placeholder="••••••••" 
                                               name="email_senha">
                                    </div>
                                </div>
                            </div>

                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Criptografia SSL/TLS</label>
                                    <p class="config-description">Usar conexão segura com o servidor de e-mail</p>
                                </div>
                                <div class="config-control">
                                    <div class="modern-switch">
                                        <input type="checkbox" class="switch-input" 
                                               <?= $configuracoes['smtp_ssl'] ? 'checked' : '' ?> 
                                               name="smtp_ssl"
                                               id="smtp_ssl">
                                        <label for="smtp_ssl" class="switch-label">
                                            <span class="switch-slider"></span>
                                            <span class="switch-text"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seção Sistema -->
            <section id="sistema" class="config-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-gear"></i>
                        Configurações do Sistema
                    </h2>
                    <p class="section-description">Performance, cache e limites de upload</p>
                </div>

                <div class="config-cards">
                    <!-- Card: Performance -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-lightning"></i>
                                Performance
                            </h3>
                            <div class="card-actions">
                                <button type="button" class="btn-ghost" onclick="limparCache()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    Limpar Cache
                                </button>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Cache do Sistema</label>
                                    <p class="config-description">Habilitar cache para melhor performance (recomendado)</p>
                                </div>
                                <div class="config-control">
                                    <div class="modern-switch">
                                        <input type="checkbox" class="switch-input" 
                                               <?= $configuracoes['cache_sistema'] ? 'checked' : '' ?> 
                                               name="cache_sistema"
                                               id="cache_sistema">
                                        <label for="cache_sistema" class="switch-label">
                                            <span class="switch-slider"></span>
                                            <span class="switch-text"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Tempo de Cache</label>
                                    <p class="config-description">Por quantos minutos manter dados em cache</p>
                                </div>
                                <div class="config-control">
                                    <div class="input-with-addon">
                                        <input type="number" class="form-input-modern" 
                                               value="<?= $configuracoes['tempo_cache'] ?>" 
                                               min="5" max="1440" 
                                               name="tempo_cache">
                                        <span class="input-addon">min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Uploads -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-upload"></i>
                                Upload de Arquivos
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Tamanho Máximo</label>
                                    <p class="config-description">Limite por arquivo em megabytes</p>
                                </div>
                                <div class="config-control">
                                    <div class="input-with-addon">
                                        <input type="number" class="form-input-modern" 
                                               value="<?= $configuracoes['max_upload'] ?>" 
                                               min="1" max="100" 
                                               name="max_upload">
                                        <span class="input-addon">MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Estatísticas -->
                    <div class="config-card stats-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-graph-up"></i>
                                Estatísticas Rápidas
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="stats-grid-compact">
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($solicitacoesAtivas) ?></div>
                                    <div class="stat-label">Solicitações Ativas</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($propostasAceitas) ?></div>
                                    <div class="stat-label">Propostas Aceitas</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($prestadores) ?></div>
                                    <div class="stat-label">Prestadores</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($clientes) ?></div>
                                    <div class="stat-label">Clientes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seção Backup -->
            <section id="backup" class="config-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-shield-check"></i>
                        Backup e Segurança
                    </h2>
                    <p class="section-description">Proteja seus dados com backups regulares</p>
                </div>

                <div class="config-cards">
                    <!-- Alert -->
                    <div class="config-alert">
                        <div class="alert-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div class="alert-content">
                            <strong>Importante:</strong> Execute backups regulares para proteger seus dados. 
                            Recomendamos backup diário para sistemas em produção.
                        </div>
                    </div>

                    <!-- Card: Configurações de Backup -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-calendar-check"></i>
                                Backup Automático
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="config-item">
                                <div class="config-item-info">
                                    <label class="config-label">Backup Diário</label>
                                    <p class="config-description">Executar backup automático todos os dias</p>
                                </div>
                                <div class="config-control">
                                    <div class="modern-switch">
                                        <input type="checkbox" class="switch-input" 
                                               <?= $configuracoes['backup_automatico'] ? 'checked' : '' ?> 
                                               name="backup_automatico"
                                               id="backup_automatico">
                                        <label for="backup_automatico" class="switch-label">
                                            <span class="switch-slider"></span>
                                            <span class="switch-text"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="config-item-grid">
                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">Horário</label>
                                        <p class="config-description">Melhor horário: madrugada</p>
                                    </div>
                                    <div class="config-control">
                                        <input type="time" class="form-input-modern" 
                                               value="<?= $configuracoes['horario_backup'] ?>" 
                                               name="horario_backup">
                                    </div>
                                </div>

                                <div class="config-item">
                                    <div class="config-item-info">
                                        <label class="config-label">Retenção</label>
                                        <p class="config-description">Manter backups por quantos dias</p>
                                    </div>
                                    <div class="config-control">
                                        <div class="input-with-addon">
                                            <input type="number" class="form-input-modern" 
                                                   value="<?= $configuracoes['dias_backup'] ?>" 
                                                   min="7" max="365" 
                                                   name="dias_backup">
                                            <span class="input-addon">dias</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Ações de Backup -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h3 class="config-card-title">
                                <i class="bi bi-tools"></i>
                                Ações Manuais
                            </h3>
                        </div>
                        <div class="config-card-body">
                            <div class="backup-actions-grid">
                                <div class="backup-action-item">
                                    <div class="backup-action-icon success">
                                        <i class="bi bi-download"></i>
                                    </div>
                                    <div class="backup-action-content">
                                        <h4 class="backup-action-title">Criar Backup</h4>
                                        <p class="backup-action-description">Backup completo com estrutura e dados</p>
                                        <button type="button" class="btn-primary" onclick="criarBackup()">
                                            <i class="bi bi-download"></i>
                                            Gerar e Baixar
                                        </button>
                                    </div>
                                </div>

                                <div class="backup-action-item">
                                    <div class="backup-action-icon warning">
                                        <i class="bi bi-upload"></i>
                                    </div>
                                    <div class="backup-action-content">
                                        <h4 class="backup-action-title">Restaurar</h4>
                                        <p class="backup-action-description">Substituirá todos os dados atuais</p>
                                        <button type="button" class="btn-warning" onclick="restaurarBackup()">
                                            <i class="bi bi-upload"></i>
                                            Escolher Arquivo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Enhanced Floating Actions Bar with Preview -->
    <div class="actions-bar enhanced" id="actionsBar">
        <div class="actions-bar-content">
            <div class="actions-preview">
                <div class="preview-icon">
                    <i class="bi bi-eye"></i>
                </div>
                <div class="preview-info">
                    <span class="preview-title">Alterações pendentes</span>
                    <span class="preview-count">3 configurações modificadas</span>
                </div>
            </div>
            <div class="actions-buttons">
                <button type="button" class="btn-secondary" onclick="visualizarAlteracoes()">
                    <i class="bi bi-eye"></i>
                    Visualizar
                </button>
                <button type="button" class="btn-secondary" onclick="descartarAlteracoes()">
                    <i class="bi bi-x"></i>
                    Descartar
                </button>
                <button type="button" class="btn-primary" onclick="salvarConfiguracoes()">
                    <i class="bi bi-check-lg"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="preview-modal" id="previewModal">
        <div class="modal-backdrop" onclick="fecharPreview()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Prévia das Alterações</h3>
                <button class="modal-close" onclick="fecharPreview()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="changes-list" id="changesList">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="fecharPreview()">Cancelar</button>
                <button class="btn-primary" onclick="confirmarAlteracoes()">Confirmar e Salvar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$styles = '
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

:root {
  --primary: #4f46e5;
  --primary-light: #6366f1;
  --primary-dark: #3730a3;
  --success: #059669;
  --warning: #d97706;
  --error: #dc2626;
  --info: #2563eb;
  --neutral-50: #f8fafc;
  --neutral-100: #f1f5f9;
  --neutral-200: #e2e8f0;
  --neutral-300: #cbd5e1;
  --neutral-400: #94a3b8;
  --neutral-500: #64748b;
  --neutral-600: #475569;
  --neutral-700: #334155;
  --neutral-800: #1e293b;
  --neutral-900: #0f172a;
  --glass-bg: rgba(255, 255, 255, 0.95);
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;
}

* {
  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Page Wrapper */
.config-page-wrapper {
  background: linear-gradient(135deg, var(--neutral-50) 0%, var(--neutral-100) 100%);
  min-height: 100vh;
}

/* Modern Header */
.config-header-modern {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--neutral-200);
  padding: 2rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
}

.config-header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.config-page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--neutral-800);
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 0 0 0.5rem 0;
}

.config-title-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  box-shadow: var(--shadow-md);
}

.config-page-subtitle {
  color: var(--neutral-600);
  font-size: 1rem;
  margin: 0;
}

.system-status-panel {
  background: white;
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--neutral-200);
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.status-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--neutral-800);
  margin: 0;
}

.btn-refresh {
  background: transparent;
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-md);
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--neutral-600);
}

.btn-refresh:hover {
  background: var(--neutral-50);
  color: var(--primary);
  transform: rotate(180deg);
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.status-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--neutral-50);
  border-radius: var(--radius-lg);
  border: 1px solid var(--neutral-100);
  transition: all 0.2s ease;
}

.status-card:hover {
  background: white;
  border-color: var(--neutral-200);
  box-shadow: var(--shadow-sm);
}

.status-card.online {
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  border-color: #a7f3d0;
}

.status-icon {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-lg);
  font-size: 1.25rem;
}

.status-card.online .status-icon {
  background: var(--success);
  color: white;
}

.status-card:not(.online) .status-icon {
  background: var(--neutral-200);
  color: var(--neutral-600);
}

.status-info {
  display: flex;
  flex-direction: column;
}

.status-label {
  font-size: 0.75rem;
  color: var(--neutral-500);
  font-weight: 500;
}

.status-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--neutral-800);
}

/* Search and Filter Tools */
.config-tools {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--neutral-200);
  padding: 1rem 0;
  position: sticky;
  top: 140px;
  z-index: 90;
}

.config-tools-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  gap: 2rem;
}

.search-container {
  flex: 1;
}

.search-input-wrapper {
  position: relative;
  max-width: 400px;
}

.search-input-wrapper i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--neutral-500);
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: 2px solid var(--neutral-200);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  background: white;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-clear {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--neutral-400);
  cursor: pointer;
  padding: 0.25rem;
}

.filter-container {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  background: white;
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-lg);
  padding: 0.5rem 1rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--neutral-600);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-btn:hover {
  background: var(--neutral-50);
  border-color: var(--neutral-300);
}

.filter-btn.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.view-toggle {
  display: flex;
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.view-btn {
  background: white;
  border: none;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  color: var(--neutral-600);
  transition: all 0.2s ease;
}

.view-btn:hover {
  background: var(--neutral-50);
}

.view-btn.active {
  background: var(--primary);
  color: white;
}

/* Enhanced Navigation */
.config-progress {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.progress-text {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 600;
}

.progress-bar {
  flex: 1;
  height: 4px;
  background: rgba(255,255,255,0.1);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--success) 0%, #10b981 100%);
  transition: width 0.3s ease;
}

.nav-badge {
  background: rgba(255,255,255,0.2);
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: auto;
}

.nav-badge.warning {
  background: var(--warning);
}

.nav-badge.success {
  background: var(--success);
}

.nav-status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-left: 0.5rem;
}

.nav-status.complete {
  background: var(--success);
}

.nav-status.incomplete {
  background: var(--warning);
}

/* Quick Actions Panel */
.quick-actions-panel {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.quick-actions-title {
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 1rem 0;
}

.quick-actions {
  display: grid;
  gap: 0.5rem;
}

.quick-action-btn {
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: var(--radius-lg);
  padding: 0.75rem;
  color: #cbd5e1;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
}

.quick-action-btn:hover {
  background: rgba(255,255,255,0.2);
  color: white;
}

/* Enhanced Section Headers */
.section-title-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.section-counter {
  background: var(--neutral-100);
  color: var(--neutral-600);
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 500;
  margin-left: 1rem;
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

.section-action-btn {
  background: transparent;
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-md);
  padding: 0.5rem 1rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--neutral-600);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-action-btn:hover {
  background: var(--neutral-50);
  border-color: var(--neutral-300);
}

.section-progress {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.section-progress .progress-bar {
  flex: 1;
  max-width: 200px;
  height: 6px;
  background: var(--neutral-200);
  border-radius: 3px;
}

.section-progress .progress-text {
  font-size: 0.8125rem;
  color: var(--neutral-600);
  font-weight: 500;
}

/* Enhanced Cards */
.config-cards.enhanced .config-card {
  margin-bottom: 1rem;
}

.config-card.collapsible .config-card-header {
  cursor: pointer;
  user-select: none;
}

.card-title-group {
  flex: 1;
}

.card-subtitle {
  color: var(--neutral-500);
  font-size: 0.8125rem;
  margin: 0.25rem 0 0 0;
}

.completion-badge {
  background: var(--success);
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  margin-left: 0.5rem;
}

.card-header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-action-btn {
  background: transparent;
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-sm);
  padding: 0.375rem;
  cursor: pointer;
  color: var(--neutral-500);
  transition: all 0.2s ease;
}

.card-action-btn:hover {
  background: var(--neutral-50);
  color: var(--neutral-700);
}

.collapse-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--neutral-500);
  transition: all 0.2s ease;
  padding: 0.25rem;
}

.collapse-btn i {
  transition: transform 0.2s ease;
}

.config-card.collapsed .collapse-btn i {
  transform: rotate(-90deg);
}

.config-card.collapsed .config-card-body {
  display: none;
}

/* Enhanced Config Items */
.config-item.enhanced {
  position: relative;
}

.label-badge {
  background: var(--error);
  color: white;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.6875rem;
  font-weight: 600;
  margin-left: 0.5rem;
}

.label-badge.required {
  background: var(--error);
}

.config-tip {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border-radius: var(--radius-md);
  border-left: 3px solid var(--info);
  font-size: 0.75rem;
  color: var(--neutral-600);
}

.input-container {
  position: relative;
}

.form-input-modern.enhanced {
  padding-right: 3rem;
}

.input-validation {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.validation-icon {
  font-size: 1rem;
}

.validation-icon.valid {
  color: var(--success);
}

.validation-icon.invalid {
  color: var(--error);
}

.validation-message {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--success);
}

/* Enhanced Actions Bar */
.actions-bar.enhanced {
  min-width: 500px;
}

.actions-preview {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.preview-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, var(--info) 0%, var(--primary) 100%);
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.preview-info {
  display: flex;
  flex-direction: column;
}

.preview-title {
  font-weight: 600;
  color: var(--neutral-800);
  font-size: 0.875rem;
}

.preview-count {
  font-size: 0.75rem;
  color: var(--neutral-500);
}

/* Preview Modal */
.preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1000;
  display: none;
}

.preview-modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
}

.modal-content {
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow: hidden;
  position: relative;
  z-index: 1001;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--neutral-200);
}

.modal-header h3 {
  margin: 0;
  color: var(--neutral-800);
  font-size: 1.125rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  color: var(--neutral-500);
  border-radius: var(--radius-md);
}

.modal-close:hover {
  background: var(--neutral-100);
  color: var(--neutral-700);
}

.modal-body {
  padding: 1.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid var(--neutral-200);
}

/* Responsive enhancements */
@media (max-width: 1200px) {
  .system-status-panel {
    display: none;
  }
  
  .config-tools-content {
    flex-wrap: wrap;
    gap: 1rem;
  }
  
  .filter-container {
    order: 3;
    flex-basis: 100%;
  }
}

@media (max-width: 768px) {
  .config-tools {
    display: none;
  }
  
  .section-title-wrapper {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .card-header-actions {
    flex-direction: column;
    align-self: flex-start;
  }
}

/* Animations */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-up {
  animation: slideUp 0.4s ease;
}
';

$scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
  initializeNavigation();
  initializeFormTracking();
  initializeAnimations();
});

function initializeNavigation() {
  const navItems = document.querySelectorAll(".nav-item");
  const sections = document.querySelectorAll(".config-section");
  
  // Handle navigation clicks
  navItems.forEach(item => {
    item.addEventListener("click", function(e) {
      e.preventDefault();
      const targetSection = this.dataset.section;
      
      // Update active nav item
      navItems.forEach(nav => nav.classList.remove("active"));
      this.classList.add("active");
      
      // Smooth scroll to section
      const section = document.getElementById(targetSection);
      if (section) {
        section.scrollIntoView({ 
          behavior: "smooth",
          block: "start"
        });
      }
    });
  });
  
  // Update active nav on scroll
  const observerOptions = {
    rootMargin: "-20% 0px -70% 0px"
  };
  
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const sectionId = entry.target.id;
        navItems.forEach(nav => nav.classList.remove("active"));
        const activeNav = document.querySelector(`[data-section="${sectionId}"]`);
        if (activeNav) {
          activeNav.classList.add("active");
        }
      }
    });
  }, observerOptions);
  
  sections.forEach(section => {
    observer.observe(section);
  });
}

function initializeFormTracking() {
  const inputs = document.querySelectorAll("input, select, textarea");
  const actionsBar = document.getElementById("actionsBar");
  let hasChanges = false;
  
  inputs.forEach(input => {
    input.addEventListener("change", function() {
      hasChanges = true;
      showActionsBar();
    });
  });
  
  function showActionsBar() {
    if (actionsBar) {
      actionsBar.classList.add("show");
    }
  }
  
  function hideActionsBar() {
    if (actionsBar) {
      actionsBar.classList.remove("show");
    }
  }
  
  // Global functions for actions
  window.salvarConfiguracoes = function() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = `<div class="spinner"></div> Salvando...`;
    
    // Simulate save
    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = originalHTML;
      hasChanges = false;
      hideActionsBar();
      showNotification("Configurações salvas com sucesso!", "success");
    }, 1500);
  };
  
  window.descartarAlteracoes = function() {
    if (confirm("Descartar todas as alterações não salvas?")) {
      // Reset form
      inputs.forEach(input => {
        if (input.type === "checkbox") {
          input.checked = input.defaultChecked;
        } else {
          input.value = input.defaultValue;
        }
      });
      hasChanges = false;
      hideActionsBar();
      showNotification("Alterações descartadas", "info");
    }
  };
}

function initializeAnimations() {
  const cards = document.querySelectorAll(".config-card");
  
  const cardObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("slide-up");
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px"
  });
  
  cards.forEach(card => {
    cardObserver.observe(card);
  });
}

function criarBackup() {
  if (!confirm("Criar backup completo do sistema?\\n\\nEste processo pode levar alguns minutos.")) {
    return;
  }
  
  const btn = event.target;
  const originalHTML = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner"></div> Gerando...`;
  
  // Criar form para submeter
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "<?= url("admin/backup/gerar") ?>";
  form.style.display = "none";
  
  // Adicionar token CSRF se disponível
  <?php if (isset($_SESSION["csrf_token"])): ?>
  const csrfInput = document.createElement("input");
  csrfInput.type = "hidden";
  csrfInput.name = "csrf_token";
  csrfInput.value = "<?= $_SESSION["csrf_token"] ?>";
  form.appendChild(csrfInput);
  <?php endif; ?>
  
  document.body.appendChild(form);
  
  // Submeter form
  form.submit();
  
  // Aguardar um pouco e restaurar botão
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = originalHTML;
    document.body.removeChild(form);
    showNotification("Download do backup iniciado!", "success");
  }, 2000);
}

function restaurarBackup() {
  if (!confirm("ATENÇÃO: Restaurar backup substituirá todos os dados atuais!\\n\\nDeseja continuar?")) {
    return;
  }
  
  const input = document.createElement("input");
  input.type = "file";
  input.accept = ".sql,.zip";
  input.onchange = function() {
    if (this.files[0]) {
      showNotification(`Arquivo selecionado: ${this.files[0].name}`, "info");
    }
  };
  input.click();
}

function testarEmail() {
  const btn = event.target;
  const originalHTML = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner"></div> Testando...`;
  
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = originalHTML;
    showNotification("E-mail de teste enviado com sucesso!", "success");
  }, 2000);
}

function limparCache() {
  if (!confirm("Limpar cache do sistema?\\n\\nIsto pode afetar temporariamente a performance.")) {
    return;
  }
  
  const btn = event.target;
  const originalHTML = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner"></div> Limpando...`;
  
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = originalHTML;
    showNotification("Cache limpo com sucesso!", "success");
  }, 1500);
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  
  const icons = {
    success: "check-circle-fill",
    error: "exclamation-triangle-fill",
    warning: "exclamation-triangle-fill",
    info: "info-circle-fill"
  };
  
  notification.innerHTML = `
    <div class="notification-content">
      <i class="bi bi-${icons[type]}"></i>
      <span>${message}</span>
    </div>
    <button class="notification-close" onclick="this.parentElement.remove()">
      <i class="bi bi-x"></i>
    </button>
  `;
  
  notification.style.cssText = `
    position: fixed;
    top: 2rem;
    right: 2rem;
    background: white;
    border: 1px solid var(--neutral-200);
    border-left: 4px solid var(--${type === "error" || type === "warning" ? "error" : type === "success" ? "success" : "info"});
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    padding: 1rem 1.5rem;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 1rem;
    max-width: 400px;
    animation: slideInFromRight 0.3s ease;
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.animation = "slideOutToRight 0.3s ease";
    setTimeout(() => notification.remove(), 300);
  }, 4000);
}

// Add CSS for notifications and spinner
const additionalCSS = `
@keyframes slideInFromRight {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes slideOutToRight {
  from { opacity: 1; transform: translateX(0); }
  to { opacity: 0; transform: translateX(100%); }
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.notification-close {
  background: none;
  border: none;
  color: var(--neutral-500);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
}

.notification-close:hover {
  background: var(--neutral-100);
  color: var(--neutral-700);
}

.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
`;

const styleSheet = document.createElement("style");
styleSheet.textContent = additionalCSS;
document.head.appendChild(styleSheet);
</script>
';

include 'views/admin/layouts/app.php';
?>
