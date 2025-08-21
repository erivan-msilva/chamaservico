<!-- Modal para criar usuário admin -->
<div class="modal fade" id="modalCriarAdmin" tabindex="-1" aria-labelledby="modalCriarAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCriarAdminLabel">
                    <i class="bi bi-shield-plus me-2"></i>Criar Usuário Administrador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCriarAdmin">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Atenção:</strong> Usuários administradores têm acesso total ao sistema.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminNome" class="form-label">
                                    <i class="bi bi-person me-1"></i>Nome Completo
                                </label>
                                <input type="text" class="form-control" id="adminNome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminEmail" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>E-mail
                                </label>
                                <input type="email" class="form-control" id="adminEmail" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminSenha" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Senha
                                </label>
                                <input type="password" class="form-control" id="adminSenha" name="senha" required minlength="6">
                                <div class="form-text">Mínimo de 6 caracteres</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminConfirmarSenha" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Confirmar Senha
                                </label>
                                <input type="password" class="form-control" id="adminConfirmarSenha" name="confirmar_senha" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminTelefone" class="form-label">
                                    <i class="bi bi-telephone me-1"></i>Telefone
                                </label>
                                <input type="tel" class="form-control" id="adminTelefone" name="telefone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminNivel" class="form-label">
                                    <i class="bi bi-shield me-1"></i>Nível de Acesso
                                </label>
                                <select class="form-select" id="adminNivel" name="nivel_admin">
                                    <option value="admin">Administrador</option>
                                    <option value="super_admin">Super Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="adminAtivo" name="ativo" checked>
                            <label class="form-check-label" for="adminAtivo">
                                Usuário ativo
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminObservacoes" class="form-label">
                            <i class="bi bi-chat-left-text me-1"></i>Observações
                        </label>
                        <textarea class="form-control" id="adminObservacoes" name="observacoes" rows="3" placeholder="Informações adicionais sobre o administrador..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Criar Administrador
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmação de logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="bi bi-box-arrow-right"></i> Sair do Sistema
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
