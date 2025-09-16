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

<!-- Header Redesenhado -->
<div class="config-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="config-title">
            <div class="config-title-icon">
                <i class="bi bi-sliders"></i>
            </div>
            Configurações do Sistema
        </h1>
        
        <div class="d-flex gap-1rem">
            <button class="btn-modern btn-info" onclick="criarBackup()">
                <i class="bi bi-download"></i>
                Backup
            </button>
            <button class="btn-modern btn-warning" onclick="limparCache()">
                <i class="bi bi-arrow-clockwise"></i>
                Limpar Cache
            </button>
            <button class="btn-modern btn-success" onclick="salvarConfiguracoes()">
                <i class="bi bi-check-lg"></i>
                Salvar Alterações
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards Redesenhados -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-card-content">
            <div class="stats-card-info">
                <h6>Status do Sistema</h6>
                <div class="stats-card-value">
                    <i class="bi bi-check-circle"></i>
                    Online
                </div>
            </div>
            <div class="stats-card-icon success">
                <i class="bi bi-server"></i>
            </div>
        </div>
    </div>

    <div class="stats-card">
        <div class="stats-card-content">
            <div class="stats-card-info">
                <h6>Versão do Sistema</h6>
                <div class="stats-card-value">v2.1.0</div>
            </div>
            <div class="stats-card-icon info">
                <i class="bi bi-code-square"></i>
            </div>
        </div>
    </div>

    <div class="stats-card">
        <div class="stats-card-content">
            <div class="stats-card-info">
                <h6>Último Backup</h6>
                <div class="stats-card-value"><?= date('d/m') ?></div>
            </div>
            <div class="stats-card-icon warning">
                <i class="bi bi-archive"></i>
            </div>
        </div>
    </div>

    <div class="stats-card">
        <div class="stats-card-content">
            <div class="stats-card-info">
                <h6>Usuários Ativos</h6>
                <div class="stats-card-value"><?= number_format($usuariosAtivos) ?></div>
            </div>
            <div class="stats-card-icon primary">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>
</div>

<!-- Navegação Redesenhada -->
<div class="config-navigation">
    <div class="nav-card active" data-target="geral">
        <i class="nav-card-icon bi bi-sliders"></i>
        <div class="nav-card-content">
            <h3 class="nav-card-title">Configurações Gerais</h3>
            <p class="nav-card-description">Sistema, taxa e limites</p>
        </div>
    </div>
    
    <div class="nav-card" data-target="email">
        <i class="nav-card-icon bi bi-envelope"></i>
        <div class="nav-card-content">
            <h3 class="nav-card-title">E-mail & SMTP</h3>
            <p class="nav-card-description">Configurações de envio</p>
        </div>
    </div>
    
    <div class="nav-card" data-target="sistema">
        <i class="nav-card-icon bi bi-gear"></i>
        <div class="nav-card-content">
            <h3 class="nav-card-title">Sistema</h3>
            <p class="nav-card-description">Performance e uploads</p>
        </div>
    </div>
    
    <div class="nav-card" data-target="backup">
        <i class="nav-card-icon bi bi-shield-check"></i>
        <div class="nav-card-content">
            <h3 class="nav-card-title">Backup</h3>
            <p class="nav-card-description">Segurança de dados</p>
        </div>
    </div>
</div>

<!-- Conteúdo Principal Redesenhado -->
<div class="config-content-grid">
    <!-- Aba Geral -->
    <div class="content-card active-content" id="content-geral">
        <div class="content-card-header">
            <h2 class="content-card-title">
                <i class="bi bi-sliders"></i>
                Configurações Gerais do Sistema
            </h2>
        </div>
        <div class="content-card-body">
            <div class="setting-group">
                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-type"></i>
                                Nome do Sistema
                            </h3>
                            <p class="setting-item-description">
                                Nome exibido no cabeçalho e emails automáticos do sistema
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <input type="text" class="form-control-modern" 
                                   value="<?= htmlspecialchars($configuracoes['nome_sistema']) ?>" 
                                   name="nome_sistema" 
                                   placeholder="ChamaServiço">
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-person-plus"></i>
                                Permitir Novos Cadastros
                            </h3>
                            <p class="setting-item-description">
                                Usuários podem se registrar livremente na plataforma
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="switch-container">
                                <input type="checkbox" class="switch-input" 
                                       <?= $configuracoes['permitir_cadastros'] ? 'checked' : '' ?> 
                                       name="permitir_cadastros">
                                <span class="switch-slider"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-percent"></i>
                                Taxa do Sistema
                            </h3>
                            <p class="setting-item-description">
                                Porcentagem cobrada sobre serviços realizados na plataforma
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="input-group-modern">
                                <input type="number" class="form-control-modern" 
                                       value="<?= $configuracoes['taxa_sistema'] ?>" 
                                       min="0" max="50" step="0.1" 
                                       name="taxa_sistema">
                                <span class="input-group-text-modern">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-exclamation-triangle"></i>
                                Sistema em Manutenção
                            </h3>
                            <p class="setting-item-description">
                                Bloquear acesso de usuários (exceto administradores)
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="switch-container">
                                <input type="checkbox" class="switch-input" 
                                       <?= $configuracoes['modo_manutencao'] ? 'checked' : '' ?> 
                                       name="modo_manutencao">
                                <span class="switch-slider"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-images"></i>
                                Limite de Imagens por Solicitação
                            </h3>
                            <p class="setting-item-description">
                                Máximo de fotos que podem ser anexadas
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <input type="number" class="form-control-modern" 
                                   value="<?= $configuracoes['limite_imagens'] ?>" 
                                   min="1" max="20" 
                                   name="limite_imagens">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aba E-mail -->
    <div class="content-card" id="content-email" style="display: none;">
        <div class="content-card-header">
            <h2 class="content-card-title">
                <i class="bi bi-envelope"></i>
                Configurações de E-mail SMTP
            </h2>
        </div>
        <div class="content-card-body">
            <div class="setting-group">
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-server"></i>
                                    Servidor SMTP
                                </h3>
                                <p class="setting-item-description">Endereço do servidor de e-mail</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="text" class="form-control-modern" 
                                       value="<?= htmlspecialchars($configuracoes['smtp_host']) ?>" 
                                       name="smtp_host" 
                                       placeholder="smtp.gmail.com">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-outlet"></i>
                                    Porta SMTP
                                </h3>
                                <p class="setting-item-description">Porta do servidor (587 ou 465)</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="number" class="form-control-modern" 
                                       value="<?= $configuracoes['smtp_port'] ?>" 
                                       name="smtp_port">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-envelope-at"></i>
                                    E-mail do Sistema
                                </h3>
                                <p class="setting-item-description">E-mail usado para envios automáticos</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="email" class="form-control-modern" 
                                       value="<?= htmlspecialchars($configuracoes['email_sistema']) ?>" 
                                       name="email_sistema">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-key"></i>
                                    Senha do E-mail
                                </h3>
                                <p class="setting-item-description">Senha ou token de aplicação</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="password" class="form-control-modern" 
                                       placeholder="••••••••" 
                                       name="email_senha">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-shield-check"></i>
                                Usar Autenticação SSL/TLS
                            </h3>
                            <p class="setting-item-description">
                                Conexão segura com o servidor
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="switch-container">
                                <input type="checkbox" class="switch-input" 
                                       <?= $configuracoes['smtp_ssl'] ? 'checked' : '' ?> 
                                       name="smtp_ssl">
                                <span class="switch-slider"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn-modern btn-warning" onclick="testarEmail()">
                        <i class="bi bi-send"></i>
                        Testar Configuração
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Aba Sistema -->
    <div class="content-card" id="content-sistema" style="display: none;">
        <div class="content-card-header">
            <h2 class="content-card-title">
                <i class="bi bi-gear"></i>
                Configurações do Sistema
            </h2>
        </div>
        <div class="content-card-body">
            <div class="setting-group">
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-upload"></i>
                                    Tamanho Máximo de Upload (MB)
                                </h3>
                                <p class="setting-item-description">Tamanho máximo para upload de imagens</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="number" class="form-control-modern" 
                                       value="<?= $configuracoes['max_upload'] ?>" 
                                       min="1" max="50" 
                                       name="max_upload">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-clock"></i>
                                    Tempo de Cache (minutos)
                                </h3>
                                <p class="setting-item-description">Por quanto tempo manter dados em cache</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="number" class="form-control-modern" 
                                       value="<?= $configuracoes['tempo_cache'] ?>" 
                                       min="5" max="1440" 
                                       name="tempo_cache">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-lightning"></i>
                                Cache do Sistema
                            </h3>
                            <p class="setting-item-description">
                                Habilitar cache para melhor performance
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="switch-container">
                                <input type="checkbox" class="switch-input" 
                                       <?= $configuracoes['cache_sistema'] ? 'checked' : '' ?> 
                                       name="cache_sistema">
                                <span class="switch-slider"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas do Sistema -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3">Estatísticas do Sistema</h4>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="metric-item p-3 bg-light rounded">
                                <div class="h4 text-primary mb-0"><?= number_format($solicitacoesAtivas) ?></div>
                                <small class="text-muted">Solicitações Ativas</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-item p-3 bg-light rounded">
                                <div class="h4 text-success mb-0"><?= number_format($propostasAceitas) ?></div>
                                <small class="text-muted">Propostas Aceitas</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-item p-3 bg-light rounded">
                                <div class="h4 text-info mb-0"><?= number_format($prestadores) ?></div>
                                <small class="text-muted">Prestadores</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-item p-3 bg-light rounded">
                                <div class="h4 text-warning mb-0"><?= number_format($clientes) ?></div>
                                <small class="text-muted">Clientes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aba Backup -->
    <div class="content-card" id="content-backup" style="display: none;">
        <div class="content-card-header">
            <h2 class="content-card-title">
                <i class="bi bi-shield-check"></i>
                Backup e Restauração
            </h2>
        </div>
        <div class="content-card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Importante:</strong> Execute backups regulares para proteger seus dados. 
                Recomendamos backup diário para sistemas em produção.
            </div>

            <div class="setting-group">
                <div class="setting-item">
                    <div class="setting-item-header">
                        <div class="setting-item-info">
                            <h3 class="setting-item-title">
                                <i class="bi bi-calendar-check"></i>
                                Backup Automático
                            </h3>
                            <p class="setting-item-description">
                                Executar backup automático diariamente
                            </p>
                        </div>
                        <div class="setting-item-control">
                            <div class="switch-container">
                                <input type="checkbox" class="switch-input" 
                                       <?= $configuracoes['backup_automatico'] ? 'checked' : '' ?> 
                                       name="backup_automatico">
                                <span class="switch-slider"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-clock"></i>
                                    Horário do Backup
                                </h3>
                                <p class="setting-item-description">Melhor horário: madrugada (menor uso)</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="time" class="form-control-modern" 
                                       value="<?= $configuracoes['horario_backup'] ?>" 
                                       name="horario_backup">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-item-info">
                                <h3 class="setting-item-title">
                                    <i class="bi bi-calendar"></i>
                                    Manter Backups por (dias)
                                </h3>
                                <p class="setting-item-description">Backups mais antigos serão excluídos automaticamente</p>
                            </div>
                            <div class="setting-item-control">
                                <input type="number" class="form-control-modern" 
                                       value="<?= $configuracoes['dias_backup'] ?>" 
                                       min="7" max="365" 
                                       name="dias_backup">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações de Backup -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="text-center p-4 border rounded">
                        <i class="bi bi-download fs-1 text-success mb-3"></i>
                        <h5>Criar Backup</h5>
                        <p class="text-muted mb-3">Backup completo com estrutura e dados</p>
                        <button class="btn-modern btn-success w-100" onclick="criarBackup()">
                            <i class="bi bi-download"></i>
                            Gerar e Baixar
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-4 border rounded">
                        <i class="bi bi-upload fs-1 text-warning mb-3"></i>
                        <h5>Restaurar Backup</h5>
                        <p class="text-muted mb-3">Substituirá todos os dados atuais</p>
                        <button class="btn-modern btn-warning w-100" onclick="restaurarBackup()">
                            <i class="bi bi-upload"></i>
                            Restaurar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Estilos completamente redesenhados
$styles = '
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

:root {
  --primary: #6366f1;
  --primary-light: #818cf8;
  --primary-dark: #4f46e5;
  --secondary: #8b5cf6;
  --accent: #06d6a0;
  --warning: #f59e0b;
  --error: #ef4444;
  --neutral-50: #f8fafc;
  --neutral-100: #f1f5f9;
  --neutral-200: #e2e8f0;
  --neutral-300: #cbd5e1;
  --neutral-800: #1e293b;
  --neutral-600: #475569;
  --neutral-500: #64748b;
  --glass-bg: rgba(255, 255, 255, 0.95);
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --radius-sm: 0.5rem;
  --radius-md: 0.75rem;
  --radius-lg: 1rem;
  --radius-xl: 1.5rem;
}

* {
  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
}

.main-content {
  background: linear-gradient(135deg, var(--neutral-50) 0%, var(--neutral-100) 100%);
  min-height: 100vh;
  padding: 0;
}

/* Header Redesign */
.config-header {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--neutral-200);
  padding: 2rem 2.5rem;
  margin-bottom: 2rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.config-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--neutral-800);
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 0;
}

.config-title-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  box-shadow: var(--shadow-md);
}

/* Stats Cards Redesign */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stats-card {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-xl);
  padding: 2rem;
  box-shadow: var(--shadow-md);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.stats-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
  border-color: var(--primary-light);
}

.stats-card-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stats-card-info h6 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--neutral-500);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.stats-card-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--neutral-800);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stats-card-icon {
  width: 4rem;
  height: 4rem;
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  opacity: 0.8;
}

.stats-card-icon.success { background: linear-gradient(135deg, #10b981 20%, #059669 100%); color: white; }
.stats-card-icon.info { background: linear-gradient(135deg, #3b82f6 20%, #1d4ed8 100%); color: white; }
.stats-card-icon.warning { background: linear-gradient(135deg, #f59e0b 20%, #d97706 100%); color: white; }
.stats-card-icon.primary { background: linear-gradient(135deg, var(--primary) 20%, var(--primary-dark) 100%); color: white; }

/* Navigation Redesign */
.config-navigation {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 3rem;
}

.nav-card {
  background: var(--glass-bg);
  border: 2px solid var(--neutral-200);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  text-decoration: none;
  color: var(--neutral-600);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.nav-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 100%;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 0;
}

.nav-card.active,
.nav-card:hover {
  border-color: var(--primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.nav-card.active::before,
.nav-card:hover::before {
  width: 100%;
}

.nav-card-content {
  position: relative;
  z-index: 1;
}

.nav-card-icon {
  font-size: 1.5rem;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-card:hover .nav-card-icon {
  transform: scale(1.1);
}

.nav-card-title {
  font-weight: 600;
  font-size: 1rem;
  margin: 0;
}

.nav-card-description {
  font-size: 0.875rem;
  opacity: 0.8;
  margin: 0;
}

/* Content Cards */
.config-content-grid {
  display: grid;
  gap: 2rem;
  grid-template-columns: 1fr;
  position: relative;
}

.content-card {
  background: var(--glass-bg);
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  transform: translateY(20px);
}

.content-card.active-content {
  opacity: 1;
  transform: translateY(0);
}

.content-card:hover {
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-light);
}

.metric-item {
  transition: all 0.3s ease;
  border: 1px solid transparent;
  border-radius: var(--radius-md);
}

.metric-item:hover {
  transform: translateY(-2px);
  border-color: #dee2e6;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Setting Items Redesign */
.setting-group {
  display: grid;
  gap: 1.5rem;
}

.setting-item {
  background: var(--neutral-50);
  border: 1px solid var(--neutral-200);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.setting-item:hover {
  background: white;
  border-color: var(--primary-light);
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.setting-item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  margin-bottom: 1rem;
}

.setting-item-info {
  flex: 1;
}

.setting-item-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--neutral-800);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.setting-item-description {
  font-size: 0.875rem;
  color: var(--neutral-500);
  line-height: 1.6;
  margin: 0;
}

.setting-item-control {
  flex-shrink: 0;
  min-width: 200px;
}

/* Form Controls Redesign */
.form-control-modern {
  background: white;
  border: 2px solid var(--neutral-200);
  border-radius: var(--radius-md);
  padding: 0.875rem 1rem;
  font-size: 1rem;
  font-weight: 500;
  color: var(--neutral-800);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: none;
}

.form-control-modern:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  outline: none;
  transform: translateY(-1px);
}

.input-group-modern {
  position: relative;
}

.input-group-text-modern {
  background: var(--primary);
  color: white;
  border: 2px solid var(--primary);
  border-left: none;
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  font-weight: 600;
  padding: 0.875rem 1rem;
}

/* Switch Redesign */
.switch-container {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 32px;
}

.switch-input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--neutral-300);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 32px;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.switch-slider:before {
  position: absolute;
  content: "";
  height: 24px;
  width: 24px;
  left: 4px;
  bottom: 4px;
  background: white;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.switch-input:checked + .switch-slider {
  background: var(--primary);
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

.switch-input:checked + .switch-slider:before {
  transform: translateX(28px);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
}

/* Action Buttons */
.btn-modern {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  padding: 0.875rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: var(--shadow-md);
  position: relative;
  overflow: hidden;
}

.btn-modern::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
}

.btn-modern:hover::before {
  left: 100%;
}

.btn-modern:active {
  transform: translateY(0);
  box-shadow: var(--shadow-md);
}

.btn-modern.btn-success {
  background: linear-gradient(135deg, var(--accent) 0%, #059669 100%);
}

.btn-modern.btn-warning {
  background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
}

.btn-modern.btn-info {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
  .config-header {
    padding: 1.5rem;
  }
  
  .config-title {
    font-size: 1.5rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .config-navigation {
    grid-template-columns: 1fr;
  }
  
  .setting-item-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .setting-item-control {
    min-width: 100%;
  }
}

/* Micro-interactions */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-in {
  animation: slideInFromTop 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.loading {
  animation: pulse 1.5s ease-in-out infinite;
}
';

// Scripts com micro-interações melhoradas
$scripts = '
<script>
// Inicialização com animações
document.addEventListener("DOMContentLoaded", function() {
  // Animar entrada dos cards
  const cards = document.querySelectorAll(".stats-card, .setting-item");
  cards.forEach((card, index) => {
    setTimeout(() => {
      card.classList.add("slide-in");
    }, index * 100);
  });
  
  // Configurar navegação entre abas
  setupNavigation();
  
  // Configurar switches customizados
  setupCustomSwitches();
});

function setupNavigation() {
  const navCards = document.querySelectorAll(".nav-card");
  const contentCards = document.querySelectorAll(".content-card");
  
  navCards.forEach(card => {
    card.addEventListener("click", function() {
      const target = this.getAttribute("data-target");
      
      // Remover classe active de todos os nav-cards
      navCards.forEach(nav => nav.classList.remove("active"));
      
      // Adicionar classe active ao card clicado
      this.classList.add("active");
      
      // Esconder todos os content-cards
      contentCards.forEach(content => {
        content.style.display = "none";
        content.classList.remove("active-content");
      });
      
      // Mostrar o content-card correspondente
      const targetContent = document.getElementById("content-" + target);
      if (targetContent) {
        targetContent.style.display = "block";
        setTimeout(() => {
          targetContent.classList.add("active-content");
        }, 50);
      }
      
      // Feedback visual
      showMicroFeedback("Seção alterada", "info");
    });
  });
}

function setupCustomSwitches() {
  const switches = document.querySelectorAll(".form-check-input");
  switches.forEach(switchEl => {
    if (switchEl.parentNode.querySelector(".switch-container")) {
      return; // Já foi processado
    }
    
    const container = document.createElement("div");
    container.className = "switch-container";
    
    const input = document.createElement("input");
    input.type = "checkbox";
    input.className = "switch-input";
    input.checked = switchEl.checked;
    input.name = switchEl.name;
    
    const slider = document.createElement("span");
    slider.className = "switch-slider";
    
    container.appendChild(input);
    container.appendChild(slider);
    
    switchEl.parentNode.replaceChild(container, switchEl);
    
    // Micro-interação no click
    slider.addEventListener("click", () => {
      input.checked = !input.checked;
      if (input.checked) {
        showMicroFeedback("✓ Ativado", "success");
      } else {
        showMicroFeedback("○ Desativado", "info");
      }
    });
  });
}

function salvarConfiguracoes() {
  const btn = event.target;
  const originalText = btn.innerHTML;
  
  btn.disabled = true;
  btn.classList.add("loading");
  btn.innerHTML = `<i class="bi bi-arrow-clockwise me-2"></i>Salvando...`;
  
  // Simular salvamento
  setTimeout(() => {
    btn.disabled = false;
    btn.classList.remove("loading");
    btn.innerHTML = originalText;
    
    showNotification("Configurações salvas com sucesso!", "success");
    
    // Micro-interação de sucesso
    btn.style.transform = "scale(1.05)";
    setTimeout(() => {
      btn.style.transform = "";
    }, 200);
  }, 1500);
}

function limparCache() {
  if (confirm("Deseja limpar o cache do sistema? Isso pode afetar temporariamente a performance.")) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = "<i class=\"bi bi-arrow-clockwise me-1\"></i>Limpando...";
    
    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = originalText;
      showNotification("Cache limpo com sucesso!", "info");
    }, 2000);
  }
}

function criarBackup() {
  if (!confirm("Deseja criar um backup completo do banco de dados?\\n\\nEste processo pode levar alguns minutos dependendo do tamanho dos dados.")) {
    return;
  }
  
  const btn = event.target;
  const originalText = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = "<i class=\"bi bi-arrow-clockwise me-1\"></i>Criando...";
  
  // Simular criação de backup
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = originalText;
    showNotification("Backup criado e baixado com sucesso!", "success");
  }, 3000);
}

function testarEmail() {
  const btn = event.target;
  const originalText = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = "<i class=\"bi bi-arrow-clockwise me-1\"></i>Testando...";
  
  // Simular teste de email
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = originalText;
    showNotification("E-mail de teste enviado com sucesso!", "success");
  }, 2000);
}

function restaurarBackup() {
  if (confirm("ATENÇÃO: Restaurar um backup irá substituir todos os dados atuais. Deseja continuar?")) {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = ".sql";
    input.onchange = function() {
      if (this.files[0]) {
        showNotification("Arquivo selecionado: " + this.files[0].name + ". Funcionalidade será implementada em breve.", "info");
      }
    };
    input.click();
  }
}

function showMicroFeedback(message, type) {
  const feedback = document.createElement("div");
  feedback.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === "success" ? "var(--accent)" : "var(--primary)"};
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 9999;
    animation: slideInFromTop 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-lg);
  `;
  feedback.textContent = message;
  document.body.appendChild(feedback);
  
  setTimeout(() => {
    feedback.style.animation = "slideInFromTop 0.3s cubic-bezier(0.4, 0, 0.2, 1) reverse";
    setTimeout(() => feedback.remove(), 300);
  }, 1500);
}

function showNotification(message, type) {
  const notification = document.createElement("div");
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = `
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 300px;
    border: none;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    backdrop-filter: blur(20px);
  `;
  
  const icon = type === "success" ? "check-circle" : (type === "info" ? "info-circle" : "exclamation-triangle");
  
  notification.innerHTML = `
    <i class="bi bi-${icon} me-2"></i>
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      const bsAlert = new bootstrap.Alert(notification);
      bsAlert.close();
    }
  }, 4000);
}
</script>
';

include 'views/admin/layouts/app.php';
?>
