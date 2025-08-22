<?php
session_start();
$title = 'Monitor do Sistema - Admin';

// Verificar se é admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}
$current_page = 'monitor';
?>

<div class="page-content">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title">Monitor do Sistema</h4>
                <p class="text-muted mb-0">Monitoramento em tempo real da aplicação</p>
            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshAll()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Atualizar Tudo
                </button>
            </div>
        </div>
    </div>

    <!-- Status do Sistema -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-0">Sistema Online</h6>
                    <small class="text-muted">Funcionando normalmente</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-database text-info" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-0">Banco Online</h6>
                    <small class="text-muted">Conexão estável</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-cpu text-warning" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-0">CPU: <span id="cpuUsage">45%</span></h6>
                    <small class="text-muted">Uso do processador</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-memory text-primary" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-0">RAM: <span id="memUsage">62%</span></h6>
                    <small class="text-muted">Uso da memória</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Atividade em Tempo Real -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Atividades Recentes</h6>
                </div>
                <div class="card-body p-0">
                    <div class="activity-feed" id="activityFeed">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="activity-content">
                                <strong>Novo usuário cadastrado</strong>
                                <div class="text-muted small">João Silva - há 2 minutos</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="bi bi-file-plus"></i>
                            </div>
                            <div class="activity-content">
                                <strong>Nova solicitação criada</strong>
                                <div class="text-muted small">Reparo elétrico - há 5 minutos</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <strong>Proposta aceita</strong>
                                <div class="text-muted small">Instalação de chuveiro - há 10 minutos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Estatísticas Hoje</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary mb-0">15</h4>
                                <small class="text-muted">Novos Usuários</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success mb-0">28</h4>
                                <small class="text-muted">Solicitações</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info mb-0">42</h4>
                                <small class="text-muted">Propostas</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning mb-0">12</h4>
                                <small class="text-muted">Serviços Concluídos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs do Sistema -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Logs do Sistema</h6>
        </div>
        <div class="card-body">
            <div class="log-viewer">
                <div class="log-entry">
                    <span class="log-time">2025-01-11 14:30:15</span>
                    <span class="badge bg-success">INFO</span>
                    <span class="log-message">Sistema iniciado com sucesso</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2025-01-11 14:25:42</span>
                    <span class="badge bg-primary">LOGIN</span>
                    <span class="log-message">Admin logado: admin@chamaservico.com</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2025-01-11 14:20:18</span>
                    <span class="badge bg-warning">WARNING</span>
                    <span class="log-message">Alto uso de CPU detectado: 85%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
}

.log-viewer {
    max-height: 300px;
    overflow-y: auto;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.log-entry {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}

.log-time {
    color: #666;
    margin-right: 0.5rem;
}

.log-message {
    margin-left: 0.5rem;
}
</style>

<script>
function refreshAll() {
    // Simular atualização dos dados
    document.getElementById('cpuUsage').textContent = Math.floor(Math.random() * 40 + 30) + '%';
    document.getElementById('memUsage').textContent = Math.floor(Math.random() * 30 + 50) + '%';
    showToast('Dados atualizados');
}

// Auto-refresh a cada 30 segundos
setInterval(refreshAll, 30000);
</script>
