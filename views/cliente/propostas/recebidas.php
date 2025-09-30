<?php
$title = 'Propostas Recebidas - ChamaServiço';
ob_start();
?>

<!-- CSS customizado para melhorar o design -->
<style>
.card-proposta {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.card-proposta:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.card-proposta.selecionado {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.prestador-destaque {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.prestador-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.prestador-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.valor-prazo-destaque {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1rem;
}

.valor-principal {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.prazo-principal {
    font-size: 1.1rem;
    opacity: 0.9;
}

.avaliacao-estrelas {
    color: #ffc107;
    margin-right: 0.5rem;
}

.badge-status {
    font-size: 0.8rem;
    padding: 0.5rem 0.8rem;
}

.checkbox-comparacao {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    background: white;
    border-radius: 6px;
    padding: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.modo-comparacao .card-proposta {
    cursor: pointer;
}

.acoes-lote {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    display: none;
}

.timeline-view .card {
    border-left: 4px solid #007bff;
    margin-bottom: 1rem;
}

.timeline-view .card.status-aceita {
    border-left-color: #28a745;
}

.timeline-view .card.status-recusada {
    border-left-color: #dc3545;
}

.timeline-view .card.status-pendente {
    border-left-color: #ffc107;
}

.btn-group-toggle .btn {
    border-radius: 8px !important;
    margin-right: 5px;
}

.dropdown-toggle::after {
    display: none;
}

.info-badge {
    background: rgba(0,123,255,0.1);
    color: #007bff;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.3rem;
    display: inline-block;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="bi bi-inbox me-2 text-primary"></i>Propostas Recebidas
        </h2>
        <p class="text-muted mb-0">Gerencie as propostas que você recebeu para suas solicitações</p>
    </div>
    
    <!-- Botões de visualização -->
    <div class="d-flex gap-2">
        <div class="btn-group btn-group-toggle" role="group">
            <button type="button" class="btn btn-outline-secondary active" id="btn-cards" onclick="alterarVisualizacao('cards')">
                <i class="bi bi-grid me-1"></i>Cards
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-lista" onclick="alterarVisualizacao('lista')">
                <i class="bi bi-list me-1"></i>Lista
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-timeline" onclick="alterarVisualizacao('timeline')">
                <i class="bi bi-clock-history me-1"></i>Timeline
            </button>
        </div>
        

        
        <!-- Nova Solicitação -->
        <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nova Solicitação
        </a>
    </div>
</div>

<!-- Ações em Lote (quando modo comparação ativo) -->
<div class="acoes-lote" id="acoes-lote">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong><span id="contador-selecionadas">0</span> propostas selecionadas</strong>
        </div>
        <div class="btn-group">
            <button class="btn btn-light btn-sm" onclick="compararSelecionadas()">
                <i class="bi bi-bar-chart me-1"></i>Comparar
            </button>
            <button class="btn btn-success btn-sm" onclick="aceitarSelecionadas()">
                <i class="bi bi-check me-1"></i>Aceitar
            </button>
            <button class="btn btn-danger btn-sm" onclick="recusarSelecionadas()">
                <i class="bi bi-x me-1"></i>Recusar
            </button>
            <button class="btn btn-secondary btn-sm" onclick="limparSelecao()">
                <i class="bi bi-arrow-clockwise me-1"></i>Limpar
            </button>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="form-filtros">
            <div class="col-md-4">
                <label for="solicitacao_id" class="form-label">Solicitação</label>
                <select class="form-select" id="solicitacao_id" name="solicitacao_id">
                    <option value="">Todas as solicitações</option>
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <option value="<?= $solicitacao['id'] ?>" 
                                <?= ($_GET['solicitacao_id'] ?? '') == $solicitacao['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($solicitacao['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= ($_GET['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="aceita" <?= ($_GET['status'] ?? '') == 'aceita' ? 'selected' : '' ?>>Aceita</option>
                    <option value="recusada" <?= ($_GET['status'] ?? '') == 'recusada' ? 'selected' : '' ?>>Recusada</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="ordenacao" class="form-label">Ordenar por</label>
                <select class="form-select" id="ordenacao" name="ordenacao">
                    <option value="data_desc" <?= ($_GET['ordenacao'] ?? '') == 'data_desc' ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="data_asc" <?= ($_GET['ordenacao'] ?? '') == 'data_asc' ? 'selected' : '' ?>>Mais antigas</option>
                    <option value="valor_asc" <?= ($_GET['ordenacao'] ?? '') == 'valor_asc' ? 'selected' : '' ?>>Menor valor</option>
                    <option value="valor_desc" <?= ($_GET['ordenacao'] ?? '') == 'valor_desc' ? 'selected' : '' ?>>Maior valor</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="<?= url('cliente/propostas/recebidas') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Container das Propostas -->
<div id="container-propostas">
    <?php if (empty($propostas)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Nenhuma proposta encontrada</h4>
                <p class="text-muted">
                    <?php if (!empty($_GET['status']) || !empty($_GET['solicitacao_id'])): ?>
                        Tente ajustar os filtros para ver mais resultados.
                    <?php else: ?>
                        Você ainda não recebeu propostas para suas solicitações.
                    <?php endif; ?>
                </p>
                <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Criar Nova Solicitação
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Visualização em Cards (Redesenhada) -->
        <div id="view-cards" class="row">
            <?php foreach ($propostas as $proposta): ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card card-proposta shadow-sm h-100 position-relative" data-proposta-id="<?= $proposta['id'] ?>">
                        <!-- Checkbox para Comparação -->
                        <div class="checkbox-comparacao d-none">
                            <input type="checkbox" class="form-check-input" value="<?= $proposta['id'] ?>" onchange="toggleSelecao(this)">
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge badge-status bg-<?= $proposta['status'] == 'pendente' ? 'warning' : 
                                                               ($proposta['status'] == 'aceita' ? 'success' : 'secondary') ?>">
                                <?= $proposta['status'] == 'pendente' ? 'Aguardando' : ucfirst($proposta['status']) ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <!-- Prestador em Destaque -->
                            <div class="prestador-destaque">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($proposta['prestador_foto'])): ?>
                                        <img src="<?= url('uploads/perfil/' . htmlspecialchars($proposta['prestador_foto'])) ?>" 
                                             class="prestador-avatar me-3" alt="Foto do prestador">
                                    <?php else: ?>
                                        <div class="prestador-avatar-placeholder bg-primary text-white me-3">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold"><?= htmlspecialchars($proposta['prestador_nome']) ?></h5>
                                        
                                        <!-- Avaliação -->
                                        <?php if (!empty($proposta['prestador_avaliacao'])): ?>
                                            <div class="avaliacao-estrelas">
                                                <?php 
                                                $rating = floatval($proposta['prestador_avaliacao']);
                                                for($i = 1; $i <= 5; $i++): 
                                                ?>
                                                    <i class="bi bi-star<?= $i <= $rating ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-1">
                                                    (<?= number_format($rating, 1) ?> • <?= $proposta['prestador_total_avaliacoes'] ?? 0 ?> avaliações)
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">Prestador ainda sem avaliações</small>
                                        <?php endif; ?>
                                        
                                        <!-- Info badges -->
                                        <div class="mt-2">
                                            <?php if (!empty($proposta['prestador_servicos_concluidos'])): ?>
                                                <span class="info-badge">
                                                    <i class="bi bi-check-circle me-1"></i><?= $proposta['prestador_servicos_concluidos'] ?> serviços
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($proposta['prestador_anos_experiencia'])): ?>
                                                <span class="info-badge">
                                                    <i class="bi bi-calendar me-1"></i><?= $proposta['prestador_anos_experiencia'] ?>+ anos
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Valor e Prazo em Destaque -->
                            <div class="valor-prazo-destaque">
                                <div class="valor-principal">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></div>
                                <div class="prazo-principal">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= $proposta['prazo_execucao'] ?? 'A combinar' ?> dia<?= ($proposta['prazo_execucao'] ?? 0) != 1 ? 's' : '' ?>
                                </div>
                            </div>
                            
                            <!-- Título da Solicitação -->
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-tools me-2 text-primary"></i>
                                <?= htmlspecialchars($proposta['solicitacao_titulo']) ?>
                            </h6>
                            
                            <!-- Tipo de Serviço -->
                            <p class="text-muted mb-2">
                                <i class="bi bi-tag me-1"></i>
                                <?= htmlspecialchars($proposta['tipo_servico_nome']) ?>
                            </p>
                            
                            <!-- Descrição da Proposta -->
                            <?php if (!empty($proposta['descricao'])): ?>
                                <div class="bg-light p-2 rounded mb-3">
                                    <small class="text-dark">
                                        <?= htmlspecialchars(substr($proposta['descricao'], 0, 100)) ?>
                                        <?= strlen($proposta['descricao']) > 100 ? '...' : '' ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Data da Proposta -->
                            <small class="text-muted d-block mb-3">
                                <i class="bi bi-calendar3 me-1"></i>
                                Recebida em <?= date('d/m/Y \à\s H:i', strtotime($proposta['data_proposta'])) ?>
                            </small>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <!-- Botão Principal: Ver Detalhes -->
                            <div class="d-grid gap-2 mb-2">
                                <a href="<?= url('cliente/propostas/detalhes?id=' . $proposta['id']) ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>
                                    Ver Detalhes
                                </a>
                                
                                <?php if ($proposta['status'] === 'pendente'): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalAceitar"
                                            onclick="setPropostaId(<?= $proposta['id'] ?>)">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Aceitar
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalRecusar"
                                            onclick="setPropostaId(<?= $proposta['id'] ?>)">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Recusar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Visualização em Lista -->
        <div id="view-lista" class="d-none">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="checkbox-comparacao d-none">
                                    <input type="checkbox" class="form-check-input" id="select-all" onchange="toggleSelectAll(this)">
                                </th>
                                <th>Solicitação</th>
                                <th>Prestador</th>
                                <th>Valor</th>
                                <th>Prazo</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($propostas as $proposta): ?>
                            <tr data-proposta-id="<?= $proposta['id'] ?>">
                                <td class="checkbox-comparacao d-none">
                                    <input type="checkbox" class="form-check-input proposta-checkbox" value="<?= $proposta['id'] ?>" onchange="toggleSelecao(this)">
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($proposta['solicitacao_titulo']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($proposta['tipo_servico_nome']) ?></small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($proposta['prestador_nome']) ?></td>
                                <td><strong class="text-success">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></strong></td>
                                <td><?= $proposta['prazo_execucao'] ?> dia(s)</td>
                                <td>
                                    <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'recusada' ? 'danger' : 'warning') ?>">
                                        <?= $proposta['status'] === 'pendente' ? 'Aguardando' : ucfirst($proposta['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($proposta['data_proposta'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('cliente/propostas/detalhes?id=' . $proposta['id']) ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($proposta['status'] === 'pendente'): ?>
                                            <button class="btn btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalAceitar"
                                                    onclick="setPropostaModalData(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>', '<?= htmlspecialchars($proposta['solicitacao_titulo']) ?>', '<?= number_format($proposta['valor'], 2, ',', '.') ?>', '<?= $proposta['prazo_execucao'] ?>')">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalRecusar"
                                                    onclick="setPropostaModalData(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>', '<?= htmlspecialchars($proposta['solicitacao_titulo']) ?>')">
                                                <i class="bi bi-x"></i>
                                            </button>
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

        <!-- Visualização em Timeline -->
        <div id="view-timeline" class="d-none timeline-view">
            <?php foreach ($propostas as $proposta): ?>
            <div class="card status-<?= $proposta['status'] ?> position-relative" data-proposta-id="<?= $proposta['id'] ?>">
                <!-- Checkbox para Timeline -->
                <div class="checkbox-comparacao d-none position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                    <input type="checkbox" class="form-check-input proposta-checkbox" value="<?= $proposta['id'] ?>" onchange="toggleSelecao(this)">
                </div>
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0"><?= htmlspecialchars($proposta['solicitacao_titulo']) ?></h6>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($proposta['data_proposta'])) ?></small>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-1"><strong>Prestador:</strong> <?= htmlspecialchars($proposta['prestador_nome']) ?></p>
                            <p class="mb-1"><strong>Valor:</strong> R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></p>
                            <p class="mb-0"><strong>Prazo:</strong> <?= $proposta['prazo_execucao'] ?> dia(s)</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'recusada' ? 'danger' : 'warning') ?> mb-2">
                                <?= $proposta['status'] === 'pendente' ? 'Aguardando' : ucfirst($proposta['status']) ?>
                            </span>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('cliente/propostas/detalhes?id=' . $proposta['id']) ?>" 
                                   class="btn btn-outline-primary btn-sm">Ver</a>
                                <?php if ($proposta['status'] === 'pendente'): ?>
                                    <button class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalAceitar"
                                            onclick="setPropostaModalData(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>', '<?= htmlspecialchars($proposta['solicitacao_titulo']) ?>', '<?= number_format($proposta['valor'], 2, ',', '.') ?>', '<?= $proposta['prazo_execucao'] ?>')">
                                        Aceitar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Aceitar Proposta -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Aceitar Proposta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('cliente/propostas/aceitar') ?>">
                
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdAceitar">
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Confirmar Aceitação</h6>
                        <p class="mb-2">Você está prestes a aceitar a proposta de:</p>
                        <ul class="mb-0">
                            <li><strong>Prestador:</strong> <span id="prestadorNomeAceitar"></span></li>
                            <li><strong>Serviço:</strong> <span id="solicitacaoTituloAceitar"></span></li>
                            <li><strong>Valor:</strong> R$ <span id="valorPropostaAceitar"></span></li>
                            <li><strong>Prazo:</strong> <span id="prazoPropostaAceitar"></span> dia(s)</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                  placeholder="Deixe observações específicas sobre o serviço..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Aceitar Proposta
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
            <form method="POST" action="<?= url('cliente/propostas/recusar') ?>">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdRecusar">
                    
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Confirmar Recusa</h6>
                        <p class="mb-2">Você está prestes a recusar a proposta de:</p>
                        <ul class="mb-0">
                            <li><strong>Prestador:</strong> <span id="prestadorNomeRecusar"></span></li>
                            <li><strong>Serviço:</strong> <span id="solicitacaoTituloRecusar"></span></li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label">Motivo da recusa (opcional)</label>
                        <textarea class="form-control" id="motivo_recusa" name="motivo_recusa" rows="3" 
                                  placeholder="Explique o motivo da recusa para ajudar o prestador..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Recusar Proposta
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
                    <i class="bi bi-bar-chart me-2"></i>Comparar Propostas
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
                <button type="button" class="btn btn-outline-primary" onclick="exportarComparacao()">
                    <i class="bi bi-download me-1"></i>Exportar PDF
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>

<?php
$scripts = '
<script>
// Estado da aplicação
let modoComparacao = false;
let propostasSelecionadas = [];
let visualizacaoAtual = "cards";

// CORREÇÃO: Função para definir dados no modal
function setPropostaModalData(propostaId, prestadorNome, solicitacaoTitulo, valor = "", prazo = "") {
    // Modal Aceitar
    if (document.getElementById("propostaIdAceitar")) {
        document.getElementById("propostaIdAceitar").value = propostaId;
        document.getElementById("prestadorNomeAceitar").textContent = prestadorNome;
        document.getElementById("solicitacaoTituloAceitar").textContent = solicitacaoTitulo;
        if (valor) document.getElementById("valorPropostaAceitar").textContent = valor;
        if (prazo) document.getElementById("prazoPropostaAceitar").textContent = prazo;
    }
    
    // Modal Recusar
    if (document.getElementById("propostaIdRecusar")) {
        document.getElementById("propostaIdRecusar").value = propostaId;
        document.getElementById("prestadorNomeRecusar").textContent = prestadorNome;
        document.getElementById("solicitacaoTituloRecusar").textContent = solicitacaoTitulo;
    }
}

// CORREÇÃO: Função para alternar visualizações
function alterarVisualizacao(tipo) {
    console.log("Alterando visualização para:", tipo);
    
    // Ocultar todas as visualizações
    document.querySelectorAll("#view-cards, #view-lista, #view-timeline").forEach(el => {
        el.classList.add("d-none");
    });
    
    // Remover classe active dos botões
    document.querySelectorAll("#btn-cards, #btn-lista, #btn-timeline").forEach(btn => {
        btn.classList.remove("active");
    });
    
    // Mostrar visualização selecionada
    const viewElement = document.getElementById("view-" + tipo);
    const btnElement = document.getElementById("btn-" + tipo);
    
    if (viewElement && btnElement) {
        viewElement.classList.remove("d-none");
        btnElement.classList.add("active");
        
        // Atualizar checkboxes se modo comparação estiver ativo
        if (modoComparacao) {
            const checkboxes = viewElement.querySelectorAll(".checkbox-comparacao");
            checkboxes.forEach(cb => cb.classList.remove("d-none"));
        }
        
        visualizacaoAtual = tipo;
        localStorage.setItem("visualizacao-propostas", tipo);
        console.log("Visualização alterada com sucesso para:", tipo);
    } else {
        console.error("Elementos não encontrados para visualização:", tipo);
    }
}


function toggleModoComparacao() {
    modoComparacao = !modoComparacao;
    const btnComparacao = document.getElementById("btn-comparacao");
    const containerPropostas = document.getElementById("container-propostas");
    
    // Buscar checkboxes em todas as visualizações
    const checkboxesCards = document.querySelectorAll("#view-cards .checkbox-comparacao");
    const checkboxesLista = document.querySelectorAll("#view-lista .checkbox-comparacao");
    const checkboxesTimeline = document.querySelectorAll("#view-timeline .checkbox-comparacao");
    
    const todosCheckboxes = [...checkboxesCards, ...checkboxesLista, ...checkboxesTimeline];
    
    console.log("Modo comparação:", modoComparacao, "Checkboxes encontrados:", todosCheckboxes.length);
    
    todosCheckboxes.forEach(cb => {
        cb.classList.toggle("d-none", !modoComparacao);
    });
    
    if (modoComparacao) {
        btnComparacao.innerHTML = `<i class="bi bi-x-square me-1"></i>Sair da Comparação`;
        btnComparacao.classList.remove("btn-outline-info");
        btnComparacao.classList.add("btn-warning");
        containerPropostas.classList.add("modo-comparacao");
    } else {
        btnComparacao.innerHTML = `<i class="bi bi-check2-square me-1"></i>Comparar`;
        btnComparacao.classList.remove("btn-warning");
        btnComparacao.classList.add("btn-outline-info");
        containerPropostas.classList.remove("modo-comparacao");
        limparSelecao();
    }
    
    atualizarAcoesLote();
}

// CORREÇÃO: Função para selecionar/deselecionar proposta
function toggleSelecao(checkbox) {
    const propostaId = parseInt(checkbox.value);
    
    if (checkbox.checked) {
        if (!propostasSelecionadas.includes(propostaId)) {
            propostasSelecionadas.push(propostaId);
        }
    } else {
        propostasSelecionadas = propostasSelecionadas.filter(id => id !== propostaId);
    }
    
    // Marcar/desmarcar elementos relacionados em todas as visualizações
    const elementos = document.querySelectorAll(`[data-proposta-id="${propostaId}"]`);
    elementos.forEach(el => {
        el.classList.toggle("selecionado", checkbox.checked);
    });
    
    // Sincronizar checkboxes em outras visualizações
    const outrosCheckboxes = document.querySelectorAll(`input[value="${propostaId}"]`);
    outrosCheckboxes.forEach(cb => {
        if (cb !== checkbox) {
            cb.checked = checkbox.checked;
        }
    });
    
    console.log("Propostas selecionadas:", propostasSelecionadas);
    atualizarAcoesLote();
}

// Função para selecionar/deselecionar todas
function toggleSelectAll(checkbox) {
    const visualizacaoAtiva = document.getElementById("view-" + visualizacaoAtual);
    const checkboxesAtivos = visualizacaoAtiva.querySelectorAll(".proposta-checkbox");
    
    checkboxesAtivos.forEach(cb => {
        cb.checked = checkbox.checked;
        toggleSelecao(cb);
    });
}

// Atualizar interface de ações em lote
function atualizarAcoesLote() {
    const acoesLote = document.getElementById("acoes-lote");
    const contador = document.getElementById("contador-selecionadas");
    
    if (propostasSelecionadas.length > 0) {
        acoesLote.style.display = "block";
        contador.textContent = propostasSelecionadas.length;
    } else {
        acoesLote.style.display = "none";
    }
}

// Limpar seleção
function limparSelecao() {
    propostasSelecionadas = [];
    
    // Limpar todos os checkboxes
    document.querySelectorAll(".proposta-checkbox, #select-all").forEach(cb => {
        cb.checked = false;
    });
    
    // Remover classe selecionado
    document.querySelectorAll("[data-proposta-id]").forEach(el => {
        el.classList.remove("selecionado");
    });
    
    atualizarAcoesLote();
}

// CORREÇÃO: Comparar propostas selecionadas
function compararSelecionadas() {
    if (propostasSelecionadas.length < 2) {
        alert("Selecione pelo menos 2 propostas para comparar");
        return;
    }
    
    console.log("Comparando propostas:", propostasSelecionadas);
    
    // Criar tabela de comparação simples
    const modal = new bootstrap.Modal(document.getElementById("modalComparar"));
    const tabelaDiv = document.getElementById("tabelaComparacao");
    
    // Buscar dados das propostas selecionadas
    const dadosPropostas = [];
    propostasSelecionadas.forEach(id => {
        const card = document.querySelector(`[data-proposta-id="${id}"]`);
        if (card) {
            // Extrair dados do card/linha
            const prestadorNome = card.querySelector(".prestador-nome")?.textContent || 
                                 card.textContent.match(/Prestador:\s*([^\\n]+)/)?.[1] || "N/A";
            const valor = card.textContent.match(/R\$\s*([\d.,]+)/)?.[1] || "N/A";
            const prazo = card.textContent.match(/(\d+)\s*dia/)?.[1] || "N/A";
            const solicitacao = card.textContent.match(/Solicitação:\s*([^\\n]+)/)?.[1] || 
                              card.querySelector("h6, strong")?.textContent || "N/A";
            
            dadosPropostas.push({
                id: id,
                prestador: prestadorNome.trim(),
                valor: valor,
                prazo: prazo,
                solicitacao: solicitacao.trim()
            });
        }
    });
    
    // Gerar HTML da tabela
    let html = `
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Critério</th>
                        ${dadosPropostas.map((p, i) => `<th>Proposta ${i + 1}</th>`).join("")}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Prestador</strong></td>
                        ${dadosPropostas.map(p => `<td>${p.prestador}</td>`).join("")}
                    </tr>
                    <tr>
                        <td><strong>Valor</strong></td>
                        ${dadosPropostas.map(p => `<td class="text-success fw-bold">R$ ${p.valor}</td>`).join("")}
                    </tr>
                    <tr>
                        <td><strong>Prazo</strong></td>
                        ${dadosPropostas.map(p => `<td>${p.prazo} dia(s)</td>`).join("")}
                    </tr>
                    <tr>
                        <td><strong>Ações</strong></td>
                        ${dadosPropostas.map(p => `
                            <td>
                                <a href="<?= url("cliente/propostas/detalhes?id=") ?>${p.id}" class="btn btn-sm btn-primary">
                                    Ver Detalhes
                                </a>
                            </td>
                        `).join("")}
                    </tr>
                </tbody>
            </table>
        </div>
    `;
    
    tabelaDiv.innerHTML = html;
    modal.show();
}

// Exportar comparação para PDF
function exportarComparacao() {
    if (propostasSelecionadas.length < 2) {
        alert("Selecione pelo menos 2 propostas para exportar");
        return;
    }
    
    const params = new URLSearchParams();
    params.append("propostas", propostasSelecionadas.join(","));
    params.append("formato", "pdf");

    window.open(`<?= url("api/comparar-propostas/export") ?>?${params.toString()}`, "_blank");
}

// Inicialização quando a página carrega
document.addEventListener("DOMContentLoaded", function() {
    // Restaurar visualização salva
    const visualizacaoSalva = localStorage.getItem("visualizacao-propostas");
    if (visualizacaoSalva && ["cards", "lista", "timeline"].includes(visualizacaoSalva)) {
        alterarVisualizacao(visualizacaoSalva);
    } else {
        alterarVisualizacao("cards");
    }
    
    // Auto-submit dos filtros quando mudarem
    document.querySelectorAll("#status, #solicitacao_id, #ordenacao").forEach(select => {
        select.addEventListener("change", function() {
            this.form.submit();
        });
    });
    
    console.log("Sistema de propostas inicializado com sucesso!");
    console.log("Propostas encontradas:", document.querySelectorAll("[data-proposta-id]").length);
});
</script>
';
