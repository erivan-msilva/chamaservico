<?php
$title = 'Notificações - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-bell text-primary me-2"></i>
                Notificações
            </h2>
            <p class="text-muted mb-0">Acompanhe todas as atualizações importantes</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($estatisticas['nao_lidas'] > 0): ?>
                <form method="POST" action="notificacoes/marcar-todas-lidas" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-check-all me-1"></i>
                        Marcar Todas como Lidas
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <h3 class="mb-0"><?= $estatisticas['total'] ?></h3>
                    <small class="text-muted">Total de Notificações</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-bell-fill fs-1"></i>
                    </div>
                    <h3 class="mb-0"><?= $estatisticas['nao_lidas'] ?></h3>
                    <small class="text-muted">Não Lidas</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                    <h3 class="mb-0"><?= $estatisticas['lidas'] ?></h3>
                    <small class="text-muted">Lidas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos os tipos</option>
                        <option value="proposta_aceita" <?= ($_GET['tipo'] ?? '') === 'proposta_aceita' ? 'selected' : '' ?>>Proposta Aceita</option>
                        <option value="proposta_recusada" <?= ($_GET['tipo'] ?? '') === 'proposta_recusada' ? 'selected' : '' ?>>Proposta Recusada</option>
                        <option value="nova_proposta" <?= ($_GET['tipo'] ?? '') === 'nova_proposta' ? 'selected' : '' ?>>Nova Proposta</option>
                        <option value="servico_concluido" <?= ($_GET['tipo'] ?? '') === 'servico_concluido' ? 'selected' : '' ?>>Serviço Concluído</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todas</option>
                        <option value="nao_lidas" <?= ($_GET['status'] ?? '') === 'nao_lidas' ? 'selected' : '' ?>>Não Lidas</option>
                        <option value="lidas" <?= ($_GET['status'] ?? '') === 'lidas' ? 'selected' : '' ?>>Lidas</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-1"></i>
                        Filtrar
                    </button>
                    <a href="notificacoes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if (empty($notificacoes)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">Nenhuma notificação encontrada</h5>
                        <p class="text-muted">
                            <?php if (!empty($_GET['tipo']) || !empty($_GET['status'])): ?>
                                Tente ajustar os filtros para encontrar suas notificações.
                            <?php else: ?>
                                Você receberá notificações aqui quando houver atualizações importantes.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notificacoes as $notificacao): ?>
                <div class="col-12 mb-3" id="notificacao-<?= $notificacao['id'] ?>">
                    <div class="card border-0 shadow-sm <?= $notificacao['lida'] ? '' : 'border-start border-primary border-3' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <?php
                                        $iconMap = [
                                            'proposta_aceita' => 'bi-check-circle text-success',
                                            'proposta_recusada' => 'bi-x-circle text-danger',
                                            'nova_proposta' => 'bi-file-earmark-text text-primary',
                                            'servico_concluido' => 'bi-clipboard-check text-info'
                                        ];
                                        $icon = $iconMap[$notificacao['tipo']] ?? 'bi-bell text-secondary';
                                        ?>
                                        <i class="bi <?= $icon ?> me-2 fs-5"></i>
                                        <h6 class="mb-0 <?= $notificacao['lida'] ? 'text-muted' : 'fw-bold' ?>">
                                            <?= htmlspecialchars($notificacao['titulo']) ?>
                                        </h6>
                                        <?php if (!$notificacao['lida']): ?>
                                            <span class="badge bg-warning ms-2">Nova</span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="mb-2 <?= $notificacao['lida'] ? 'text-muted' : '' ?>">
                                        <?= nl2br(htmlspecialchars($notificacao['mensagem'])) ?>
                                    </p>

                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($notificacao['data_notificacao'])) ?>
                                    </small>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if (!$notificacao['lida']): ?>
                                            <li>
                                                <button class="dropdown-item" onclick="marcarComoLida(<?= $notificacao['id'] ?>)">
                                                    <i class="bi bi-check me-2"></i>Marcar como lida
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="deletarNotificacao(<?= $notificacao['id'] ?>)">
                                                <i class="bi bi-trash me-2"></i>Excluir
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<form id="formMarcarLida" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
</form>

<form id="formDeletarNotificacao" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
</form>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmarExclusaoLabel"><i class="bi bi-trash me-2"></i>Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja excluir esta notificação? Esta ação não poderá ser desfeita.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de sucesso ao marcar como lida -->
<div class="modal fade" id="modalMensagemLida" tabindex="-1" aria-labelledby="modalMensagemLidaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalMensagemLidaLabel"><i class="bi bi-check-circle me-2"></i>Notificação</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body text-center">
        Notificação marcada como lida com sucesso!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Toasts Bootstrap centralizado -->
<div aria-live="polite" aria-atomic="true"
     class="position-fixed top-50 start-50 translate-middle p-3"
     style="z-index: 1080; min-width: 300px;">
    <div id="toastContainer" class="toast-container justify-content-center align-items-center"></div>
</div>

<script>
    async function marcarComoLida(notificacaoId) {
        try {
            const formData = new FormData();
            const csrfToken = document.querySelector('#formMarcarLida input[name="csrf_token"]').value;

            formData.append('notificacao_id', notificacaoId);
            formData.append('csrf_token', csrfToken);

            const response = await fetch('notificacoes/marcar-lida', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.sucesso) {
                const cardContainer = document.getElementById('notificacao-' + notificacaoId);
                if (cardContainer) {
                    const card = cardContainer.querySelector('.card');
                    const titulo = cardContainer.querySelector('h6');
                    const mensagem = cardContainer.querySelector('p');
                    const novaBadge = cardContainer.querySelector('.badge.bg-warning');
                    const menuButton = cardContainer.querySelector('.dropdown-item[onclick^="marcarComoLida"]');

                    card.classList.remove('border-start', 'border-primary', 'border-3');
                    if (novaBadge) novaBadge.remove();
                    titulo.classList.remove('fw-bold');
                    titulo.classList.add('text-muted');
                    mensagem.classList.add('text-muted');
                    if (menuButton) menuButton.closest('li').remove();
                }
                // Exibe modal de sucesso
                const modal = new bootstrap.Modal(document.getElementById('modalMensagemLida'));
                modal.show();
            } else {
                alert('Erro ao marcar como lida: ' + (data.erro || 'Erro desconhecido.'));
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Ocorreu um erro de comunicação. Tente novamente.');
        }
    }

    // Toast Bootstrap 5
    function mostrarToast(mensagem, tipo = 'sucesso') {
        const toastId = 'toast_' + Date.now();
        const cor = tipo === 'sucesso' ? 'bg-success text-white' : 'bg-danger text-white';
        const icone = tipo === 'sucesso'
            ? '<i class="bi bi-check-circle-fill me-2"></i>'
            : '<i class="bi bi-x-circle-fill me-2"></i>';
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center ${cor}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
                <div class="d-flex">
                    <div class="toast-body">
                        ${icone}${mensagem}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
            </div>
        `;
        const container = document.getElementById('toastContainer');
        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // Variável global para guardar o ID da notificação a excluir
    let notificacaoParaExcluir = null;

    // Função chamada ao clicar em "Excluir"
    function deletarNotificacao(notificacaoId) {
        notificacaoParaExcluir = notificacaoId;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
        modal.show();
    }

    // Evento do botão de confirmação do modal
    document.addEventListener('DOMContentLoaded', function() {
        const btnConfirmar = document.getElementById('btnConfirmarExclusao');
        btnConfirmar.addEventListener('click', async function() {
            if (!notificacaoParaExcluir) return;
            try {
                const formData = new FormData();
                const csrfToken = document.querySelector('#formDeletarNotificacao input[name="csrf_token"]').value;
                formData.append('notificacao_id', notificacaoParaExcluir);
                formData.append('csrf_token', csrfToken);

                const response = await fetch('notificacoes/deletar', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.sucesso) {
                    const notificacaoRemover = document.getElementById('notificacao-' + notificacaoParaExcluir);
                    if (notificacaoRemover) {
                        notificacaoRemover.style.transition = 'opacity 0.5s ease';
                        notificacaoRemover.style.opacity = '0';
                        setTimeout(() => notificacaoRemover.remove(), 500);
                    }
                    mostrarToast('Notificação excluída com sucesso!', 'sucesso');
                } else {
                    mostrarToast('Erro ao excluir notificação: ' + (data.erro || 'Erro desconhecido.'), 'erro');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                mostrarToast('Ocorreu um erro de comunicação. Tente novamente.', 'erro');
            }
            notificacaoParaExcluir = null;
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao')).hide();
        });
    });
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>