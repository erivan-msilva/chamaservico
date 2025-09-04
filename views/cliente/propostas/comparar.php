<?php
$title = 'Comparar Propostas - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-compare me-2"></i>Comparar Propostas</h2>
                <p class="text-muted mb-0">
                    Para: <strong><?= htmlspecialchars($solicitacao['titulo']) ?></strong>
                </p>
            </div>
            <div>
                <a href="cliente/propostas/recebidas" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Voltar às Propostas
                </a>
            </div>
        </div>

        <?php if (empty($propostas)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="text-muted mt-3">Nenhuma proposta encontrada</h4>
                <p class="text-muted">Esta solicitação ainda não recebeu propostas.</p>
            </div>
        <?php else: ?>
            <!-- Resumo da Comparação -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h5 class="text-primary"><?= count($propostas) ?></h5>
                            <p class="card-text">Propostas Recebidas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h5 class="text-success">
                                R$ <?= number_format(min(array_column($propostas, 'valor')), 2, ',', '.') ?>
                            </h5>
                            <p class="card-text">Menor Valor</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h5 class="text-warning">
                                R$ <?= number_format(array_sum(array_column($propostas, 'valor')) / count($propostas), 2, ',', '.') ?>
                            </h5>
                            <p class="card-text">Valor Médio</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h5 class="text-info">
                                <?= min(array_column($propostas, 'prazo_execucao')) ?> dia(s)
                            </h5>
                            <p class="card-text">Menor Prazo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Comparação -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comparação Detalhada</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Prestador</th>
                                    <th>Valor</th>
                                    <th>Prazo</th>
                                    <th>Descrição</th>
                                    <th>Data da Proposta</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Ordenar por valor para destacar a melhor
                                usort($propostas, function($a, $b) {
                                    return $a['valor'] <=> $b['valor'];
                                });
                                
                                foreach ($propostas as $index => $proposta): 
                                ?>
                                    <tr class="<?= $index === 0 ? 'table-success' : '' ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($proposta['prestador_foto']) && file_exists("uploads/perfil/" . $proposta['prestador_foto'])): ?>
                                                    <img src="uploads/perfil/<?= htmlspecialchars($proposta['prestador_foto']) ?>" 
                                                         class="rounded-circle me-2" width="32" height="32" alt="Foto do prestador">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person" style="font-size: 1rem; color: #6c757d;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($proposta['prestador_nome']) ?></strong>
                                                    <?php if ($index === 0): ?>
                                                        <br><small class="badge bg-success">Melhor Preço</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="h6 text-success">
                                                R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td><?= $proposta['prazo_execucao'] ?> dia(s)</td>
                                        <td>
                                            <div style="max-width: 200px;">
                                                <?= htmlspecialchars(substr($proposta['descricao'], 0, 80)) ?>
                                                <?= strlen($proposta['descricao']) > 80 ? '...' : '' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="cliente/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($proposta['status'] === 'pendente'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" 
                                                            onclick="aceitarProposta(<?= $proposta['id'] ?>)">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="recusarProposta(<?= $proposta['id'] ?>)">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : 'danger' ?>">
                                                        <?= ucfirst($proposta['status']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cards para Visualização Mobile -->
            <div class="d-md-none mt-4">
                <?php foreach ($propostas as $index => $proposta): ?>
                    <div class="card mb-3 <?= $index === 0 ? 'border-success' : '' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title"><?= htmlspecialchars($proposta['prestador_nome']) ?></h6>
                                <?php if ($index === 0): ?>
                                    <span class="badge bg-success">Melhor Preço</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Valor:</strong></p>
                                    <p class="h5 text-success">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Prazo:</strong></p>
                                    <p class="h6"><?= $proposta['prazo_execucao'] ?> dia(s)</p>
                                </div>
                            </div>
                            
                            <p class="card-text"><?= htmlspecialchars(substr($proposta['descricao'], 0, 100)) ?>...</p>
                            
                            <div class="d-flex gap-1">
                                <a href="cliente/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm flex-fill">Ver Detalhes</a>
                                <?php if ($proposta['status'] === 'pendente'): ?>
                                    <button type="button" class="btn btn-success btn-sm flex-fill" 
                                            onclick="aceitarProposta(<?= $proposta['id'] ?>)">Aceitar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modais de Ação -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aceitar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="cliente/propostas/aceitar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdAceitar">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Confirmar aceitação da proposta?</strong>
                    </div>
                    
                    <p>Ao aceitar esta proposta, todas as outras serão automaticamente recusadas.</p>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" name="observacoes" rows="3" 
                                  placeholder="Deixe uma mensagem para o prestador..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Aceitação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecusar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recusar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="cliente/propostas/recusar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdRecusar">
                    
                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label">Motivo da recusa (opcional)</label>
                        <textarea class="form-control" name="motivo_recusa" rows="3" 
                                  placeholder="Explique o motivo da recusa..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Recusa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function aceitarProposta(id) {
    document.getElementById("propostaIdAceitar").value = id;
    new bootstrap.Modal(document.getElementById("modalAceitar")).show();
}

function recusarProposta(id) {
    document.getElementById("propostaIdRecusar").value = id;
    new bootstrap.Modal(document.getElementById("modalRecusar")).show();
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
