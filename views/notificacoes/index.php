<?php
$title = 'Notificações - ChamaServiço';
ob_start();
?>

<div class="container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-bell me-2"></i>Notificações</h2>
            <p class="text-muted">Central de notificações e atualizações</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="marcarTodasComoLidas()">
                <i class="bi bi-check-all me-1"></i>Marcar Todas como Lidas
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </button>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?= $estatisticas['total'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total de Notificações</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= $estatisticas['nao_lidas'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Não Lidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= $estatisticas['lidas'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Lidas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" id="tipo">
                        <option value="">Todos os tipos</option>
                        <option value="proposta_aceita" <?= ($_GET['tipo'] ?? '') == 'proposta_aceita' ? 'selected' : '' ?>>Proposta Aceita</option>
                        <option value="proposta_recusada" <?= ($_GET['tipo'] ?? '') == 'proposta_recusada' ? 'selected' : '' ?>>Proposta Recusada</option>
                        <option value="status_servico" <?= ($_GET['tipo'] ?? '') == 'status_servico' ? 'selected' : '' ?>>Status do Serviço</option>
                        <option value="nova_proposta" <?= ($_GET['tipo'] ?? '') == 'nova_proposta' ? 'selected' : '' ?>>Nova Proposta</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lida" class="form-label">Status</label>
                    <select class="form-select" name="lida" id="lida">
                        <option value="">Todas</option>
                        <option value="0" <?= ($_GET['lida'] ?? '') === '0' ? 'selected' : '' ?>>Não Lidas</option>
                        <option value="1" <?= ($_GET['lida'] ?? '') === '1' ? 'selected' : '' ?>>Lidas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="/chamaservico/notificacoes" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Notificações -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($notificacoes)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="text-muted mt-3">Nenhuma notificação encontrada</h4>
                    <p class="text-muted">Você não possui notificações no momento.</p>
                </div>
            <?php else: ?>
                <div id="notificacoesList">
                    <?php foreach ($notificacoes as $notificacao): ?>
                        <div class="notification-item <?= !$notificacao['lida'] ? 'bg-light' : '' ?>"
                            data-id="<?= $notificacao['id'] ?>">
                            <div class="d-flex p-3 border-bottom">
                                <div class="flex-shrink-0 me-3">
                                    <div class="notification-icon <?= getNotificationIconClass($notificacao['tipo'] ?? '') ?>">
                                        <i class="bi bi-<?= getNotificationIcon($notificacao['tipo'] ?? '') ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1 <?= !$notificacao['lida'] ? 'fw-bold' : '' ?>">
                                            <?= htmlspecialchars($notificacao['titulo']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= timeAgo($notificacao['data_notificacao']) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 text-muted">
                                        <?= nl2br(htmlspecialchars($notificacao['mensagem'])) ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <span class="badge bg-<?= getNotificationBadgeColor($notificacao['tipo'] ?? '') ?>">
                                                <?= getNotificationTypeLabel($notificacao['tipo'] ?? '') ?>
                                            </span>
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (!$notificacao['lida']): ?>
                                                <button type="button" class="btn btn-outline-success btn-sm"
                                                    onclick="marcarComoLida(<?= $notificacao['id'] ?>)"
                                                    title="Marcar como lida">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="deletarNotificacao(<?= $notificacao['id'] ?>)"
                                                title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .notification-icon.bg-success {
        background-color: #28a745;
    }

    .notification-icon.bg-danger {
        background-color: #dc3545;
    }

    .notification-icon.bg-info {
        background-color: #17a2b8;
    }

    .notification-icon.bg-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .notification-icon.bg-primary {
        background-color: #007bff;
    }

    .notification-item {
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fa !important;
    }
</style>

<?php
// Funções auxiliares para as notificações
function getNotificationIcon($tipo)
{
    switch ($tipo) {
        case 'proposta_aceita':
            return 'check-circle';
        case 'proposta_recusada':
            return 'x-circle';
        case 'status_servico':
            return 'gear';
        case 'nova_proposta':
            return 'envelope';
        default:
            return 'bell';
    }
}

function getNotificationIconClass($tipo)
{
    switch ($tipo) {
        case 'proposta_aceita':
            return 'bg-success';
        case 'proposta_recusada':
            return 'bg-danger';
        case 'status_servico':
            return 'bg-info';
        case 'nova_proposta':
            return 'bg-warning';
        default:
            return 'bg-primary';
    }
}

function getNotificationBadgeColor($tipo)
{
    switch ($tipo) {
        case 'proposta_aceita':
            return 'success';
        case 'proposta_recusada':
            return 'danger';
        case 'status_servico':
            return 'info';
        case 'nova_proposta':
            return 'warning';
        default:
            return 'primary';
    }
}

function getNotificationTypeLabel($tipo)
{
    switch ($tipo) {
        case 'proposta_aceita':
            return 'Proposta Aceita';
        case 'proposta_recusada':
            return 'Proposta Recusada';
        case 'status_servico':
            return 'Status do Serviço';
        case 'nova_proposta':
            return 'Nova Proposta';
        default:
            return 'Sistema';
    }
}

function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'agora';
    if ($time < 3600) return floor($time / 60) . 'm';
    if ($time < 86400) return floor($time / 3600) . 'h';
    if ($time < 2592000) return floor($time / 86400) . 'd';

    return date('d/m/Y', strtotime($datetime));
}

$scripts = '
<script>
async function marcarComoLida(id) {
    try {
        const formData = new FormData();
        formData.append("notificacao_id", id);
        
        const response = await fetch("/chamaservico/notificacoes/marcar-lida", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.classList.remove("bg-light");
                const title = item.querySelector("h6");
                if (title) title.classList.remove("fw-bold");
                
                const btn = item.querySelector(".btn-outline-success");
                if (btn) btn.remove();
            }
            
            // Atualizar contador global
            atualizarContadorGlobal();
        }
    } catch (error) {
        console.error("Erro ao marcar como lida:", error);
    }
}

async function marcarTodasComoLidas() {
    if (confirm("Marcar todas as notificações como lidas?")) {
        try {
            const response = await fetch("/chamaservico/notificacoes/marcar-todas-lidas", {
                method: "POST"
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                location.reload();
            } else {
                alert("Erro: " + data.mensagem);
            }
        } catch (error) {
            console.error("Erro:", error);
            alert("Erro interno");
        }
    }
}

async function deletarNotificacao(id) {
    if (confirm("Excluir esta notificação?")) {
        try {
            const formData = new FormData();
            formData.append("notificacao_id", id);
            
            const response = await fetch("/chamaservico/notificacoes/deletar", {
                method: "POST",
                body: formData
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.style.transition = "all 0.3s ease";
                    item.style.opacity = "0";
                    item.style.transform = "translateX(-100%)";
                    
                    setTimeout(() => {
                        item.remove();
                    }, 300);
                }
                
                atualizarContadorGlobal();
            } else {
                alert("Erro: " + data.mensagem);
            }
        } catch (error) {
            console.error("Erro:", error);
            alert("Erro interno");
        }
    }
}

async function atualizarContadorGlobal() {
    try {
        const response = await fetch("/chamaservico/notificacoes/contador");
        const data = await response.json();
        
        if (data.sucesso) {
            const badges = document.querySelectorAll(".notification-badge, .notification-badge-left, .notification-badge-menu");
            badges.forEach(badge => {
                if (data.contador > 0) {
                    badge.textContent = data.contador;
                    badge.style.display = badge.classList.contains("notification-badge-menu") ? "inline-block" : "flex";
                } else {
                    badge.style.display = "none";
                }
            });
            
            // Atualizar sino também
            const bell = document.getElementById("notificationBell");
            if (bell) {
                bell.style.display = data.contador > 0 ? "flex" : "none";
            }
        }
    } catch (error) {
        console.error("Erro ao atualizar contador:", error);
    }
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';