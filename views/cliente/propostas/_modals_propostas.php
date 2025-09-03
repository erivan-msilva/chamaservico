<!-- Modal Aceitar Proposta -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Aceitar Proposta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/aceitar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdAceitar">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Confirmar aceitação da proposta?</strong>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <p><strong>Prestador:</strong> <span id="prestadorNomeAceitar"></span></p>
                            <p><strong>Valor:</strong> R$ <span id="valorPropostaAceitar"></span></p>
                            <p><strong>Prazo:</strong> <span id="prazoPropostaAceitar"></span> dia(s)</p>
                        </div>
                    </div>
                    
                    <p>Ao aceitar esta proposta:</p>
                    <ul>
                        <li>O prestador será notificado imediatamente</li>
                        <li>Outras propostas serão automaticamente recusadas</li>
                        <li>O status da solicitação mudará para "Em Andamento"</li>
                        <li>Você poderá acompanhar o progresso do serviço</li>
                    </ul>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações para o prestador (opcional)</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="3" 
                                  placeholder="Deixe uma mensagem para o prestador sobre detalhes específicos..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check me-1"></i>Confirmar Aceite
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recusar Proposta -->
<div class="modal fade" id="modalRecusar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i>Recusar Proposta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/recusar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdRecusar">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Confirmar recusa da proposta?</strong>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <p><strong>Prestador:</strong> <span id="prestadorNomeRecusar"></span></p>
                            <p><strong>Solicitação:</strong> <span id="solicitacaoTituloRecusar"></span></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label">Motivo da recusa (opcional)</label>
                        <textarea class="form-control" name="motivo_recusa" id="motivo_recusa" rows="3" 
                                  placeholder="Explique o motivo da recusa (ajuda o prestador a melhorar)..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Dica:</strong> Dar feedback construtivo ajuda os prestadores a melhorarem suas propostas futuras.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x me-1"></i>Confirmar Recusa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Comparar Propostas -->
<div class="modal fade" id="modalComparar" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-bar-chart me-2"></i>Comparar Propostas Selecionadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="tabelaComparacao">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando comparação...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="exportarComparacao()">
                    <i class="bi bi-download me-1"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>
