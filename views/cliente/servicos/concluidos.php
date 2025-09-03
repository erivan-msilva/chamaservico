<?php
$title = 'Serviços Concluídos - Cliente';
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-check-circle text-success me-2"></i>
                Serviços Concluídos
            </h2>
            <p class="text-muted mb-0">Confirme a conclusão e avalie os serviços realizados</p>
        </div>
        <div>
            <a href="/chamaservico/cliente/dashboard" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar ao Dashboard
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>
                        Filtros
                    </h6>
                </div>
                <div class="col-md-6">
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="filtroStatus" id="todos" value="" checked>
                        <label class="btn btn-outline-primary" for="todos">Todos</label>
                        
                        <input type="radio" class="btn-check" name="filtroStatus" id="aguardandoConfirmacao" value="5">
                        <label class="btn btn-outline-warning" for="aguardandoConfirmacao">Aguardando Confirmação</label>
                        
                        <input type="radio" class="btn-check" name="filtroStatus" id="aguardandoAvaliacao" value="11">
                        <label class="btn btn-outline-info" for="aguardandoAvaliacao">Aguardando Avaliação</label>
                        
                        <input type="radio" class="btn-check" name="filtroStatus" id="finalizados" value="13">
                        <label class="btn btn-outline-success" for="finalizados">Finalizados</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Serviços -->
    <div class="row" id="listaServicos">
        <?php if (empty($servicos)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-clipboard-check text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">Nenhum serviço concluído</h5>
                        <p class="text-muted">Quando um prestador concluir um serviço, ele aparecerá aqui para você confirmar e avaliar.</p>
                        <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Solicitar Novo Serviço
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($servicos as $servico): ?>
                <div class="col-12 mb-3 servico-item" data-status="<?= $servico['status_id'] ?>">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <!-- Cabeçalho do Serviço -->
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-<?= $servico['status_id'] == 5 ? 'warning' : ($servico['status_id'] == 11 ? 'info' : 'success') ?> me-2">
                                            <?= htmlspecialchars($servico['status_nome']) ?>
                                        </span>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($servico['titulo']) ?></h6>
                                    </div>
                                    
                                    <!-- Informações do Serviço -->
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-tools me-1"></i>
                                        <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                    </p>
                                    
                                    <div class="row text-sm mb-2">
                                        <div class="col-md-6">
                                            <strong>Prestador:</strong> 
                                            <span class="text-primary"><?= htmlspecialchars($servico['prestador_nome']) ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Valor:</strong> 
                                            <span class="text-success fw-bold">R$ <?= number_format($servico['valor_aceito'], 2, ',', '.') ?></span>
                                        </div>
                                    </div>

                                    <!-- Localização -->
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?> - 
                                        <?= htmlspecialchars($servico['bairro']) ?>, <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                    </p>
                                </div>
                                
                                <!-- Ações -->
                                <div class="col-md-4 text-md-end">
                                    <?php if ($servico['status_id'] == 5): // Concluído - Aguardando Confirmação ?>
                                        <div class="d-grid gap-2">
                                            <form method="POST" action="/chamaservico/cliente/servicos/confirmar-conclusao" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                                <input type="hidden" name="solicitacao_id" value="<?= $servico['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                                        onclick="return confirm('Confirmar que o serviço foi concluído adequadamente?')">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Confirmar Conclusão
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                                    data-bs-toggle="modal" data-bs-target="#modalRevisao<?= $servico['id'] ?>">
                                                <i class="bi bi-arrow-clockwise me-1"></i>
                                                Solicitar Revisão
                                            </button>
                                        </div>
                                        
                                    <?php elseif ($servico['status_id'] == 11 && $servico['ja_avaliado'] == 0): // Aguardando Avaliação ?>
                                        <div class="d-grid">
                                            <a href="/chamaservico/cliente/servicos/avaliar?id=<?= $servico['id'] ?>" 
                                               class="btn btn-warning btn-lg">
                                                <i class="bi bi-star me-1"></i>
                                                Avaliar Serviço
                                            </a>
                                            <small class="text-muted mt-1 text-center">Clique para avaliar o trabalho realizado</small>
                                        </div>
                                        
                                    <?php elseif ($servico['ja_avaliado'] > 0): // Já Avaliado ?>
                                        <div class="text-center">
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <i class="bi bi-star-fill me-1"></i>
                                                Já Avaliado
                                            </span>
                                            <p class="text-muted small mt-2 mb-0">Serviço finalizado com sucesso</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Linha do Tempo (se houver) -->
                            <?php if ($servico['status_id'] != 5): ?>
                                <hr class="my-3">
                                <div class="timeline-sm">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        Concluído em: <?= date('d/m/Y \à\s H:i', strtotime($servico['data_aceite'])) ?>
                                        <?php if ($servico['status_id'] == 11): ?>
                                            | Confirmado pelo cliente
                                        <?php elseif ($servico['ja_avaliado'] > 0): ?>
                                            | Avaliado pelo cliente
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal de Revisão -->
                <?php if ($servico['status_id'] == 5): ?>
                    <div class="modal fade" id="modalRevisao<?= $servico['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="/chamaservico/cliente/servicos/solicitar-revisao">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-arrow-clockwise me-2"></i>
                                            Solicitar Revisão
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                        <input type="hidden" name="solicitacao_id" value="<?= $servico['id'] ?>">
                                        
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Atenção:</strong> Use esta opção apenas se o serviço não foi realizado adequadamente ou precisa de ajustes.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="motivo_revisao<?= $servico['id'] ?>" class="form-label fw-bold">
                                                Motivo da revisão *
                                            </label>
                                            <textarea class="form-control" id="motivo_revisao<?= $servico['id'] ?>" name="motivo_revisao" 
                                                      rows="4" required placeholder="Descreva o que precisa ser revisado ou ajustado..."></textarea>
                                            <div class="form-text">
                                                Seja específico sobre o que precisa ser corrigido para facilitar o trabalho do prestador.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-send me-2"></i>
                                            Solicitar Revisão
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Estatísticas -->
    <?php if (!empty($servicos)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-graph-up me-2"></i>
                            Resumo dos Serviços
                        </h6>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0"><?= count($servicos) ?></h4>
                                    <small class="text-muted">Total de Serviços</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-warning mb-0">
                                        <?= count(array_filter($servicos, fn($s) => $s['status_id'] == 5)) ?>
                                    </h4>
                                    <small class="text-muted">Aguardando Confirmação</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-info mb-0">
                                        <?= count(array_filter($servicos, fn($s) => $s['status_id'] == 11 && $s['ja_avaliado'] == 0)) ?>
                                    </h4>
                                    <small class="text-muted">Aguardando Avaliação</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success mb-0">
                                    <?= count(array_filter($servicos, fn($s) => $s['ja_avaliado'] > 0)) ?>
                                </h4>
                                <small class="text-muted">Finalizados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.servico-item {
    transition: opacity 0.3s ease;
}

.servico-item.hidden {
    display: none !important;
}

.timeline-sm {
    border-left: 2px solid #e9ecef;
    padding-left: 15px;
    margin-left: 10px;
}

.btn-group-sm .btn {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .btn-group-sm {
        width: 100%;
    }
    
    .btn-group-sm .btn {
        flex: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sistema de filtros
    const filtros = document.querySelectorAll('input[name="filtroStatus"]');
    const servicoItems = document.querySelectorAll('.servico-item');
    
    filtros.forEach(filtro => {
        filtro.addEventListener('change', function() {
            const statusSelecionado = this.value;
            
            servicoItems.forEach(item => {
                const statusItem = item.dataset.status;
                
                if (statusSelecionado === '' || statusItem === statusSelecionado) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
            
            // Atualizar contador
            const servicosVisiveis = document.querySelectorAll('.servico-item:not(.hidden)').length;
            console.log(`Mostrando ${servicosVisiveis} serviços`);
        });
    });
    
    // Confirmação para ações importantes
    document.querySelectorAll('form[action*="confirmar-conclusao"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja confirmar a conclusão deste serviço? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });
    
    // Validação dos formulários de revisão
    document.querySelectorAll('form[action*="solicitar-revisao"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const motivo = this.querySelector('textarea[name="motivo_revisao"]').value.trim();
            
            if (motivo.length < 10) {
                e.preventDefault();
                alert('Por favor, descreva o motivo da revisão com pelo menos 10 caracteres.');
                return;
            }
            
            if (!confirm('Tem certeza que deseja solicitar revisão deste serviço? O prestador será notificado.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>