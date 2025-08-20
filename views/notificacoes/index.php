<?php
$title = 'Notificações - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bell me-2"></i>Notificações</h2>
    <div>
        <button type="button" class="btn btn-outline-secondary" onclick="marcarTodasComoLidas()">
            <i class="bi bi-check-all me-1"></i>Marcar Todas como Lidas
        </button>
    </div>
</div>

<?php if (empty($notificacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-bell-slash" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma notificação</h4>
        <p class="text-muted">Você não tem notificações no momento.</p>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <?php foreach ($notificacoes as $notificacao): ?>
                <div class="card mb-3 <?= $notificacao['lida'] ? '' : 'border-primary' ?>" 
                     id="notificacao-<?= $notificacao['id'] ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="card-title <?= $notificacao['lida'] ? 'text-muted' : '' ?>">
                                    <?php if (!$notificacao['lida']): ?>
                                        <span class="badge bg-primary me-2">Nova</span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($notificacao['titulo']) ?>
                                </h6>
                                <p class="card-text <?= $notificacao['lida'] ? 'text-muted' : '' ?>">
                                    <?= htmlspecialchars($notificacao['mensagem']) ?>
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($notificacao['data_notificacao'])) ?>
                                </small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if (!$notificacao['lida']): ?>
                                        <li>
                                            <button class="dropdown-item" onclick="marcarComoLida(<?= $notificacao['id'] ?>)">
                                                <i class="bi bi-check me-1"></i>Marcar como Lida
                                            </button>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deletarNotificacao(<?= $notificacao['id'] ?>)">
                                            <i class="bi bi-trash me-1"></i>Excluir
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php
$scripts = '
<script>
function marcarComoLida(id) {
    fetch("/chamaservico/notificacoes/marcar-lida", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            notificacao_id: id,
            csrf_token: "' . Session::generateCSRFToken() . '"
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function marcarTodasComoLidas() {
    fetch("/chamaservico/notificacoes/marcar-todas-lidas", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            csrf_token: "' . Session::generateCSRFToken() . '"
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deletarNotificacao(id) {
    if (confirm("Tem certeza que deseja excluir esta notificação?")) {
        fetch("/chamaservico/notificacoes/deletar", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                notificacao_id: id,
                csrf_token: "' . Session::generateCSRFToken() . '"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>