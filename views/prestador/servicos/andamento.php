<?php
$title = 'Serviços em Andamento - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools me-2"></i>Serviços em Andamento</h2>
    <div>
        <a href="/chamaservico/prestador/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas
        </a>
    </div>
</div>

<?php if (empty($servicosAndamento)): ?>
    <div class="text-center py-5">
        <i class="bi bi-clipboard-check" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhum serviço em andamento</h4>
        <p class="text-muted">Você não possui serviços em andamento no momento.</p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Buscar Novos Serviços
            </a>
            <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text me-1"></i>Ver Minhas Propostas
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($servicosAndamento as $servico): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-header d-flex justify-content-between align-items-center py-2 bg-success text-white">
                        <small>
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($servico['cliente_nome'] ?? 'Não informado') ?>
                        </small>
                        <span class="badge" style="background-color: <?= htmlspecialchars($servico['status_cor']) ?>;">
                            <?= htmlspecialchars($servico['status_nome'] ?? 'Não informado') ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h6>
                        <p class="card-text">
                            <small class="text-primary">
                                <i class="bi bi-tools me-1"></i>
                                <?= htmlspecialchars($servico['tipo_servico_nome'] ?? 'Não informado') ?>
                            </small>
                        </p>
                        <p class="card-text"><?= htmlspecialchars(substr($servico['descricao'], 0, 100)) ?>...</p>
                        
                        <!-- Informações do serviço -->
                        <div class="mb-3">
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($servico['cidade'] . ', ' . $servico['estado']) ?>
                                </small>
                            </div>
                            
                            <?php if ($servico['data_atendimento']): ?>
                                <div class="mb-2">
                                    <small class="text-info">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    R$ <?= number_format($servico['valor'], 2, ',', '.') ?>
                                </small>
                            </div>
                            
                            <span class="badge bg-<?= ($servico['urgencia'] ?? 'baixa') === 'alta' ? 'danger' : (($servico['urgencia'] ?? 'baixa') === 'media' ? 'warning' : 'info') ?>">
                                <?= ucfirst($servico['urgencia'] ?? 'baixa') ?>
                            </span>
                        </div>
                        
                        <!-- Informações de contato -->
                        <?php if ($servico['cliente_telefone']): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-telephone me-1"></i>
                                    <?= htmlspecialchars($servico['cliente_telefone']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-1 mb-2">
                            <a href="/chamaservico/prestador/servicos/detalhes?id=<?= $servico['id'] ?>" 
                               class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>Ver Detalhes
                            </a>
                            
                            <button type="button" 
                                    class="btn btn-success btn-sm flex-fill"
                                    onclick="atualizarStatus(<?= $servico['id'] ?>, '<?= htmlspecialchars($servico['titulo']) ?>')">
                                <i class="bi bi-gear me-1"></i>Atualizar
                            </button>
                        </div>
                    
                        
                        <!-- Botão para visualizar Ordem de Serviço se concluído -->
                        <?php if ($servico['status_nome'] === 'Concluído'): ?>
                            <?php
                            // Buscar Ordem de Serviço gerada para esta proposta
                            require_once 'models/OrdemServico.php';
                            $osModel = new OrdemServico();
                            $os = $osModel->buscarPorProposta($servico['id']);
                            ?>
                            <?php if ($os): ?>
                                <a href="/chamaservico/ordem-servico/visualizar?id=<?= $os['id'] ?>" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-file-earmark-text me-1"></i>Ordem de Serviço
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Atualizar Status -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/prestador/servicos/atualizar-status">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaId">
                    
                    <div class="mb-3">
                        <label class="form-label">Serviço: <span id="tituloServico" class="fw-bold"></span></label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Novo Status *</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="">Selecione o status</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                            <option value="aguardando_materiais">Aguardando Materiais</option>
                            <option value="suspenso">Suspenso Temporariamente</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="3"
                                  placeholder="Informe detalhes sobre a atualização (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function atualizarStatus(propostaId, titulo) {
    document.getElementById("propostaId").value = propostaId;
    document.getElementById("tituloServico").textContent = titulo;
    document.getElementById("status").value = "";
    document.getElementById("observacoes").value = "";
    
    new bootstrap.Modal(document.getElementById("modalAtualizarStatus")).show();
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
