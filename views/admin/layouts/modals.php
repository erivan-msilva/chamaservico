<!-- Modal Criar Novo Admin -->
<div class="modal fade" id="modalCriarAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-plus me-2"></i>Novo Administrador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCriarAdmin" onsubmit="criarAdmin(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha *</label>
                        <input type="password" class="form-control" name="senha" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nível de Acesso *</label>
                        <select class="form-select" name="nivel_admin" required>
                            <option value="">Selecione...</option>
                            <option value="admin">Administrador</option>
                            <option value="moderador">Moderador</option>
                            <option value="suporte">Suporte</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i>Criar Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este item?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                <div id="itemExclusaoDetalhes"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                    <i class="bi bi-trash me-1"></i>Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function criarAdmin(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/chamaservico/admin/api/criar-admin', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            showToast(data.mensagem, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalCriarAdmin')).hide();
            form.reset();
        } else {
            showToast(data.mensagem, 'error');
        }
    } catch (error) {
        showToast('Erro ao criar administrador', 'error');
    }
}

let itemParaExcluir = null;

function mostrarConfirmacaoExclusao(item, detalhes = '') {
    itemParaExcluir = item;
    document.getElementById('itemExclusaoDetalhes').innerHTML = detalhes;
    new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
}

function confirmarExclusao() {
    if (itemParaExcluir && typeof itemParaExcluir.callback === 'function') {
        itemParaExcluir.callback();
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao')).hide();
        itemParaExcluir = null;
    }
}
</script>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
                <p class="mb-0 fs-5">Tem certeza que deseja <strong>sair do sistema</strong>?</p>
                <small class="text-muted">Sua sessão será encerrada imediatamente.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmLogout">
                    <i class="bi bi-box-arrow-right"></i> Sair Agora
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalStatusTitle">Novo Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formStatus">
                <div class="modal-body">
                    <input type="hidden" id="statusId">
                    <div class="mb-3">
                        <label for="nomeStatus" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nomeStatus" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricaoStatus" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricaoStatus" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="corStatus" class="form-label">Cor</label>
                        <input type="color" class="form-control" id="corStatus" value="#007bff">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
