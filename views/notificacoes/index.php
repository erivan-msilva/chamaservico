<?php
$title = 'Notificações - ChamaServiço';
ob_start();

// Verificar se é prestador ou cliente para personalizar
$isPrestador = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'prestador';
$userType = $isPrestador ? 'prestador' : 'cliente';
$themeColor = $isPrestador ? '#f5a522' : '#007bff';
?>

<style>
.notificacao-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.notificacao-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.notificacao-card[data-lida="0"] {
    background: linear-gradient(90deg, rgba(<?= $isPrestador ? '245,165,34' : '0,123,255' ?>,0.05) 0%, rgba(255,255,255,1) 10%);
    border-left-color: <?= $themeColor ?>;
}
.pulse {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-bell me-2" style="color: <?= $themeColor ?>;"></i>
            Minhas Notificações
        </h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="marcarTodasComoLidas()">
                <i class="bi bi-check-all me-1"></i>Marcar Todas como Lidas
            </button>
            <a href="/chamaservico/<?= $userType ?>/dashboard" class="btn btn-outline-primary">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center" style="border-left: 4px solid <?= $themeColor ?>;">
                <div class="card-body">
                    <h3 style="color: <?= $themeColor ?>;">
                        <?= isset($notificacoes) ? count($notificacoes) : 0 ?>
                    </h3>
                    <p class="text-muted mb-0">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <h3 class="text-danger">
                        <?= isset($notificacoes) ? count(array_filter($notificacoes, function($n) { return !$n['lida']; })) : 0 ?>
                    </h3>
                    <p class="text-muted mb-0">Não Lidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <h3 class="text-success">
                        <?= isset($notificacoes) ? count(array_filter($notificacoes, function($n) { return $n['lida']; })) : 0 ?>
                    </h3>
                    <p class="text-muted mb-0">Lidas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Notificações -->
    <?php if (empty($notificacoes)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bell-slash" style="font-size: 5rem; color: #6c757d; opacity: 0.5;"></i>
            <h4 class="text-muted mt-3">Nenhuma notificação</h4>
            <p class="text-muted">Você não tem notificações no momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notificacoes as $notificacao): ?>
            <div class="card mb-3 notificacao-card" 
                 id="notificacao-<?= $notificacao['id'] ?>"
                 data-lida="<?= $notificacao['lida'] ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <!-- Ícone baseado no tipo -->
                                <div class="me-3">
                                    <?php
                                    $icones = [
                                        'nova_proposta' => '<i class="bi bi-file-earmark-plus text-success" style="font-size: 1.5rem;"></i>',
                                        'proposta_aceita' => '<i class="bi bi-check-circle text-info" style="font-size: 1.5rem;"></i>',
                                        'status_servico' => '<i class="bi bi-tools text-warning" style="font-size: 1.5rem;"></i>',
                                        'nova_solicitacao' => '<i class="bi bi-clipboard-plus text-info" style="font-size: 1.5rem;"></i>',
                                        'default' => '<i class="bi bi-bell text-secondary" style="font-size: 1.5rem;"></i>'
                                    ];
                                    echo $icones[$notificacao['tipo']] ?? $icones['default'];
                                    ?>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 <?= $notificacao['lida'] ? 'text-muted' : 'fw-bold' ?>">
                                        <?php if (!$notificacao['lida']): ?>
                                            <span class="badge pulse me-2" style="background-color: <?= $themeColor ?>;">Nova</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($notificacao['titulo']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($notificacao['data_notificacao'])) ?>
                                    </small>
                                </div>
                            </div>
                            
                            <p class="<?= $notificacao['lida'] ? 'text-muted' : '' ?> mb-3">
                                <?= nl2br(htmlspecialchars($notificacao['mensagem'])) ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group" role="group">
                                    <?php if ($notificacao['referencia_id']): ?>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Ver Detalhes
                                        </a>
                                    <?php endif; ?>
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
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Toast para feedback -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert">
        <div class="toast-header">
            <strong class="me-auto">Notificação</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<?php
$scripts = '
<script>
function mostrarToast(mensagem, tipo = "info") {
    const toast = document.getElementById("toast");
    const toastMessage = document.getElementById("toastMessage");
    
    toastMessage.textContent = mensagem;
    
    const toastBootstrap = new bootstrap.Toast(toast);
    toastBootstrap.show();
}

function marcarComoLida(id) {
    fetch("/chamaservico/notificacoes/marcar-lida", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById("notificacao-" + id);
            if (card) {
                card.setAttribute("data-lida", "1");
                card.querySelector(".badge")?.remove();
                card.querySelector(".fw-bold")?.classList.remove("fw-bold");
                card.querySelector(".fw-bold")?.classList.add("text-muted");
            }
            mostrarToast("Notificação marcada como lida!");
        } else {
            mostrarToast("Erro ao marcar notificação", "erro");
        }
    });
}

function deletarNotificacao(id) {
    if (confirm("Tem certeza que deseja excluir esta notificação?")) {
        fetch("/chamaservico/notificacoes/deletar", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `notificacao_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById("notificacao-" + id);
                if (card) {
                    card.style.opacity = "0";
                    setTimeout(() => card.remove(), 300);
                }
                mostrarToast("Notificação excluída!");
            } else {
                mostrarToast("Erro ao excluir notificação", "erro");
            }
        });
    }
}

function marcarTodasComoLidas() {
    if (confirm("Marcar todas as notificações como lidas?")) {
        fetch("/chamaservico/notificacoes/marcar-todas-lidas", {
            method: "POST"
        })
        .then(() => {
            location.reload();
        });
    }
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>