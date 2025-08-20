<?php
$title = 'Propostas Recebidas - ChamaServiço';
ob_start();
?>

<style>
.stats-card {
    border-left: 4px solid;
    transition: transform 0.2s;
}
.stats-card:hover {
    transform: translateY(-2px);
}
.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.5rem;
    padding: 1.5rem;
}
.timeline-view .card {
    border-left: 4px solid #dee2e6;
    margin-bottom: 1rem;
}
.timeline-view .card.status-pendente {
    border-left-color: #ffc107;
}
.timeline-view .card.status-aceita {
    border-left-color: #198754;
}
.timeline-view .card.status-recusada {
    border-left-color: #dc3545;
}
.comparison-checkbox {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}
.card-proposta {
    transition: all 0.3s ease;
    position: relative;
}
.card-proposta:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
.card-proposta.selected {
    border: 2px solid #007bff;
    background-color: #f8f9ff;
}
.prestador-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.prestador-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}
.prestador-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.card-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    z-index: 10;
}
.urgencia-alta {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
.valor-destaque {
    font-size: 1.25rem;
    font-weight: bold;
}
.info-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background: #f8f9fa;
    border-radius: 1rem;
    font-size: 0.75rem;
    margin: 0.125rem;
}
.progress-bar-custom {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.3s ease;
}
.card-proposta .card-body {
    padding: 1rem;
}
.servico-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
}
.tag {
    background: #e7f3ff;
    color: #0056b3;
    padding: 0.125rem 0.5rem;
    border-radius: 0.75rem;
    font-size: 0.7rem;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-inbox me-2"></i>Propostas Recebidas</h2>
        <small class="text-muted">Gerencie todas as propostas dos seus serviços</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info" onclick="toggleComparacao()">
            <i class="bi bi-bar-chart me-1"></i>Comparar
        </button>
        <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>Minhas Solicitações
        </a>
    </div>
</div>

<!-- Estatísticas Resumidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card text-center" style="border-left-color: #ffc107;">
            <div class="card-body py-3">
                <h3 class="text-warning mb-1"><?= $estatisticas['pendentes'] ?? 0 ?></h3>
                <small class="text-muted">Aguardando Resposta</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-center" style="border-left-color: #198754;">
            <div class="card-body py-3">
                <h3 class="text-success mb-1"><?= $estatisticas['aceitas'] ?? 0 ?></h3>
                <small class="text-muted">Aceitas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-center" style="border-left-color: #dc3545;">
            <div class="card-body py-3">
                <h3 class="text-danger mb-1"><?= $estatisticas['recusadas'] ?? 0 ?></h3>
                <small class="text-muted">Recusadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-center" style="border-left-color: #007bff;">
            <div class="card-body py-3">
                <h3 class="text-primary mb-1">R$ <?= number_format($estatisticas['valor_medio'] ?? 0, 0, ',', '.') ?></h3>
                <small class="text-muted">Valor Médio</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="card mb-4">
    <div class="card-body filter-section">
        <form method="GET" class="row g-3" id="formFiltros">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?= ($_GET['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Aguardando Propostas</option>
                    <option value="aceita" <?= ($_GET['status'] ?? '') == 'aceita' ? 'selected' : '' ?>>Proposta Aceita</option>
                    <option value="recusada" <?= ($_GET['status'] ?? '') == 'recusada' ? 'selected' : '' ?>>Recusada</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                <select class="form-select" name="tipo_servico" id="tipo_servico">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($tipos_servico as $tipo): ?>
                        <option value="<?= $tipo['id'] ?>" 
                                <?= ($_GET['tipo_servico'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" name="data_inicio" id="data_inicio" 
                       value="<?= $_GET['data_inicio'] ?? '' ?>">
            </div>

            <div class="col-md-2">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" name="data_fim" id="data_fim" 
                       value="<?= $_GET['data_fim'] ?? '' ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100" role="group">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                </div>
            </div>

            <div class="col-md-6">
                <label for="busca" class="form-label">Buscar por título, descrição ou endereço</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="busca" id="busca" 
                           placeholder="Buscar por título, descrição ou endereço..." 
                           value="<?= $_GET['busca'] ?? '' ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label for="ordenacao" class="form-label">Ordenar por</label>
                <select class="form-select" name="ordenacao" id="ordenacao">
                    <option value="data_desc" <?= ($_GET['ordenacao'] ?? 'data_desc') == 'data_desc' ? 'selected' : '' ?>>Mais Recentes</option>
                    <option value="data_asc" <?= ($_GET['ordenacao'] ?? '') == 'data_asc' ? 'selected' : '' ?>>Mais Antigas</option>
                    <option value="valor_desc" <?= ($_GET['ordenacao'] ?? '') == 'valor_desc' ? 'selected' : '' ?>>Maior Valor</option>
                    <option value="valor_asc" <?= ($_GET['ordenacao'] ?? '') == 'valor_asc' ? 'selected' : '' ?>>Menor Valor</option>
                    <option value="prazo_asc" <?= ($_GET['ordenacao'] ?? '') == 'prazo_asc' ? 'selected' : '' ?>>Menor Prazo</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100" role="group">
                    <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpar
                    </a>
                   
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Controles de Visualização -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <small class="text-muted">
            <strong><?= count($propostas) ?></strong> proposta(s) encontrada(s)
        </small>
        
        <!-- Ações em lote (visível apenas quando há seleções) -->
        <div id="acoes-lote" class="d-none">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-success" onclick="aceitarSelecionadas()">
                    <i class="bi bi-check-all me-1"></i>Aceitar Selecionadas
                </button>
                <button class="btn btn-outline-danger" onclick="recusarSelecionadas()">
                    <i class="bi bi-x-lg me-1"></i>Recusar Selecionadas
                </button>
            </div>
        </div>
    </div>

    <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-secondary active" id="btn-cards" onclick="alterarVisualizacao('cards')">
            <i class="bi bi-grid-3x3-gap me-1"></i>Cards
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btn-lista" onclick="alterarVisualizacao('lista')">
            <i class="bi bi-list me-1"></i>Lista
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btn-timeline" onclick="alterarVisualizacao('timeline')">
            <i class="bi bi-clock-history me-1"></i>Timeline
        </button>
    </div>
</div>

<?php if (empty($propostas)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma proposta encontrada</h4>
        <p class="text-muted">
            <?= !empty($_GET['solicitacao_id']) || !empty($_GET['status']) 
                ? 'Tente ajustar os filtros ou ' : '' ?>
            Crie solicitações para receber propostas de prestadores!
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nova Solicitação
            </a>
            <?php if (!empty($_GET['solicitacao_id']) || !empty($_GET['status'])): ?>
                <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Visualização em Cards (padrão) -->
    <div id="view-cards" class="row">
        <?php foreach ($propostas as $proposta): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm card-proposta position-relative" data-proposta-id="<?= $proposta['id'] ?>">
                    <!-- Badge de urgência -->
                    <?php if (!empty($proposta['urgencia']) && $proposta['urgencia'] === 'alta'): ?>
                    <div class="card-badge">
                        <span class="badge bg-danger urgencia-alta">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Urgente
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Checkbox para comparação -->
                    <div class="comparison-checkbox d-none">
                        <input type="checkbox" class="form-check-input" value="<?= $proposta['id'] ?>" 
                               onchange="toggleComparacaoItem(this)">
                    </div>
                    
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'])) ?>
                            
                            <!-- Tempo decorrido -->
                            <span class="ms-2 badge bg-light text-dark">
                                <?php
                                $agora = new DateTime();
                                $dataProposta = new DateTime($proposta['data_proposta']);
                                $intervalo = $agora->diff($dataProposta);
                                if ($intervalo->days > 0) {
                                    echo $intervalo->days . ' dia(s) atrás';
                                } elseif ($intervalo->h > 0) {
                                    echo $intervalo->h . 'h atrás';
                                } else {
                                    echo $intervalo->i . 'min atrás';
                                }
                                ?>
                            </span>
                        </small>
                        <span class="badge bg-<?= $proposta['status'] === 'aceita' ? 'success' : ($proposta['status'] === 'recusada' ? 'danger' : 'warning') ?>">
                            <?= $proposta['status'] === 'pendente' ? 'Aguardando' : 
                                ($proposta['status'] === 'aceita' ? 'Aceita' : 'Recusada') ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Título da solicitação -->
                        <h6 class="card-title d-flex align-items-center mb-2">
                            <i class="bi bi-tools me-2 text-primary"></i>
                            <?= htmlspecialchars($proposta['solicitacao_titulo']) ?>
                        </h6>
                        
                        <!-- Tags de serviço e urgência -->
                        <div class="servico-tags">
                            <span class="tag">
                                <i class="bi bi-tag me-1"></i>
                                <?= htmlspecialchars($proposta['tipo_servico_nome']) ?>
                            </span>
                            
                            <?php if (!empty($proposta['urgencia'])): ?>
                                <span class="tag bg-<?= $proposta['urgencia'] === 'alta' ? 'danger' : ($proposta['urgencia'] === 'media' ? 'warning' : 'info') ?> text-white">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= ucfirst($proposta['urgencia']) ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($proposta['categoria_especialidade'])): ?>
                                <span class="tag bg-secondary text-white">
                                    <?= htmlspecialchars($proposta['categoria_especialidade']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Informações do prestador -->
                        <div class="prestador-info mb-3 p-2 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($proposta['prestador_foto'])): ?>
                                    <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($proposta['prestador_foto']) ?>" 
                                         class="prestador-avatar" alt="Foto do prestador">
                                <?php else: ?>
                                    <div class="prestador-avatar bg-primary d-flex align-items-center justify-content-center text-white">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?= htmlspecialchars($proposta['prestador_nome']) ?></div>
                                    
                                    <!-- Avaliação do prestador -->
                                    <?php if (!empty($proposta['prestador_avaliacao'])): ?>
                                    <div class="prestador-rating">
                                        <?php 
                                        $rating = floatval($proposta['prestador_avaliacao']);
                                        for($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="bi bi-star<?= $i <= $rating ? '-fill text-warning' : ' text-muted' ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted">
                                            (<?= number_format($rating, 1) ?> - <?= $proposta['prestador_total_avaliacoes'] ?? 0 ?> avaliações)
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Estatísticas do prestador -->
                                    <div class="d-flex gap-2 mt-1">
                                        <?php if (!empty($proposta['prestador_servicos_concluidos'])): ?>
                                            <span class="info-pill">
                                                <i class="bi bi-check-circle text-success me-1"></i>
                                                <?= $proposta['prestador_servicos_concluidos'] ?> serviços
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($proposta['prestador_anos_experiencia'])): ?>
                                            <span class="info-pill">
                                                <i class="bi bi-clock-history text-primary me-1"></i>
                                                <?= $proposta['prestador_anos_experiencia'] ?>+ anos
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($proposta['prestador_verificado'])): ?>
                                            <span class="info-pill bg-success text-white">
                                                <i class="bi bi-patch-check me-1"></i>
                                                Verificado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Valor e prazo destacados -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <div class="valor-destaque text-success">
                                        R$ <?= number_format($proposta['valor'], 2, ',', '.') ?>
                                    </div>
                                    <small class="text-muted">Valor da proposta</small>
                                    
                                    <!-- Comparação com orçamento estimado -->
                                    <?php if (!empty($proposta['orcamento_estimado'])): ?>
                                        <?php 
                                        $diferenca = (($proposta['valor'] / $proposta['orcamento_estimado']) - 1) * 100;
                                        $corDiferenca = $diferenca <= 0 ? 'success' : ($diferenca <= 20 ? 'warning' : 'danger');
                                        ?>
                                        <div class="mt-1">
                                            <small class="text-<?= $corDiferenca ?>">
                                                <?= $diferenca > 0 ? '+' : '' ?><?= number_format($diferenca, 1) ?>% do estimado
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                    <div class="valor-destaque text-primary">
                                        <?= $proposta['prazo_execucao'] ?> dia<?= $proposta['prazo_execucao'] != 1 ? 's' : '' ?>
                                    </div>
                                    <small class="text-muted">Prazo de execução</small>
                                    
                                    <!-- Indicador de prazo -->
                                    <?php if ($proposta['prazo_execucao'] <= 3): ?>
                                        <div class="mt-1">
                                            <small class="text-success">
                                                <i class="bi bi-lightning-fill me-1"></i>Rápido
                                            </small>
                                        </div>
                                    <?php elseif ($proposta['prazo_execucao'] <= 7): ?>
                                        <div class="mt-1">
                                            <small class="text-warning">
                                                <i class="bi bi-clock me-1"></i>Moderado
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Descrição da proposta -->
                        <?php if (!empty($proposta['descricao'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-chat-left-text me-1"></i>Proposta:
                                </h6>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-dark">
                                        <?= htmlspecialchars(substr($proposta['descricao'], 0, 150)) ?>
                                        <?php if (strlen($proposta['descricao']) > 150): ?>
                                            <span id="desc-<?= $proposta['id'] ?>" class="d-none">
                                                <?= htmlspecialchars(substr($proposta['descricao'], 150)) ?>
                                            </span>
                                            <a href="#" class="text-primary" onclick="toggleDescricao(<?= $proposta['id'] ?>); return false;">
                                                <span id="btn-<?= $proposta['id'] ?>">... ver mais</span>
                                            </a>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Localização e distância -->
                        <?php if (!empty($proposta['endereco'])): ?>
                            <div class="mb-2">
                                <small class="text-muted d-flex align-items-center">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($proposta['endereco']) ?>
                                    
                                    <?php if (!empty($proposta['distancia_km'])): ?>
                                        <span class="ms-2 badge bg-info">
                                            <?= number_format($proposta['distancia_km'], 1) ?>km
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informações adicionais -->
                        <div class="row text-center mt-3">
                            <?php if (!empty($proposta['materiais_inclusos'])): ?>
                            <div class="col-4">
                                <div class="info-pill bg-info text-white w-100">
                                    <i class="bi bi-tools me-1"></i>
                                    Materiais inclusos
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($proposta['garantia_meses'])): ?>
                            <div class="col-4">
                                <div class="info-pill bg-success text-white w-100">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <?= $proposta['garantia_meses'] ?> meses garantia
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($proposta['disponibilidade_imediata'])): ?>
                            <div class="col-4">
                                <div class="info-pill bg-warning text-dark w-100">
                                    <i class="bi bi-clock me-1"></i>
                                    Disponível agora
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Barra de progresso para propostas aceitas -->
                        <?php if ($proposta['status'] === 'aceita'): ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Progresso do serviço</small>
                                <small class="text-muted"><?= $proposta['progresso_servico'] ?? 0 ?>%</small>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: <?= $proposta['progresso_servico'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-1">
                            <a href="/chamaservico/cliente/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>Detalhes
                            </a>
                            
                            <?php if ($proposta['status'] === 'pendente'): ?>
                                <button type="button" 
                                        class="btn btn-success btn-sm flex-fill"
                                        onclick="aceitarProposta(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>')">
                                    <i class="bi bi-check me-1"></i>Aceitar
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="recusarProposta(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>')">
                                    <i class="bi bi-x"></i>
                                </button>
                            <?php elseif ($proposta['status'] === 'aceita'): ?>
                                <div class="btn-group flex-fill">
                                    <a href="tel:<?= htmlspecialchars($proposta['prestador_telefone'] ?? '') ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="bi bi-telephone me-1"></i>Ligar
                                    </a>
                                    
                                    <?php if (!empty($proposta['prestador_whatsapp'])): ?>
                                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $proposta['prestador_whatsapp']) ?>?text=Olá! Sobre o serviço de <?= urlencode($proposta['solicitacao_titulo']) ?>..." 
                                       class="btn btn-success btn-sm" target="_blank">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Ações secundárias -->
                        <div class="d-flex justify-content-center mt-2 gap-2">
                            <?php if ($proposta['status'] === 'pendente'): ?>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="enviarMensagem(<?= $proposta['id'] ?>)">
                                    <i class="bi bi-chat-dots me-1"></i>Conversar
                                </button>
                                
                                <button class="btn btn-outline-info btn-sm"
                                        onclick="verPerfilPrestador(<?= $proposta['prestador_id'] ?>)">
                                    <i class="bi bi-person me-1"></i>Ver Perfil
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
                        <tr>
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
                                    <a href="/chamaservico/cliente/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($proposta['status'] === 'pendente'): ?>
                                        <button class="btn btn-success" 
                                                onclick="aceitarProposta(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" 
                                                onclick="recusarProposta(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>')">
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
        <div class="card status-<?= $proposta['status'] ?>">
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
                            <a href="/chamaservico/cliente/propostas/detalhes?id=<?= $proposta['id'] ?>" 
                               class="btn btn-outline-primary btn-sm">Ver</a>
                            <?php if ($proposta['status'] === 'pendente'): ?>
                                <button class="btn btn-success btn-sm" 
                                        onclick="aceitarProposta(<?= $proposta['id'] ?>, '<?= htmlspecialchars($proposta['prestador_nome']) ?>')">
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

<!-- Modal Comparar Propostas -->
<div class="modal fade" id="modalComparar" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comparar Propostas Selecionadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="tabelaComparacao"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aceitar Proposta -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aceitar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/aceitar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdAceitar">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Confirmar aceitação da proposta?</strong>
                    </div>
                    
                    <p>Ao aceitar esta proposta:</p>
                    <ul>
                        <li>O prestador será notificado</li>
                        <li>Outras propostas serão automaticamente recusadas</li>
                        <li>O status da solicitação mudará para "Proposta Aceita"</li>
                        <li>Combine os detalhes finais diretamente com o prestador</li>
                    </ul>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="3" 
                                  placeholder="Deixe uma mensagem para o prestador..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
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
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i>Recusar Proposta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/recusar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdRecusar">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Confirmar recusa da proposta?</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label">Motivo da recusa (opcional)</label>
                        <textarea class="form-control" name="motivo_recusa" id="motivo_recusa" rows="3" 
                                  placeholder="Explique o motivo da recusa (ajuda o prestador a melhorar)..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Dica:</strong> Dar feedback ajuda os prestadores a melhorarem suas propostas futuras.
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

<?php
$scripts = '
<script>
let modoComparacao = false;
let propostasSelecionadas = [];

function alterarVisualizacao(tipo) {
    // Ocultar todas as visualizações
    document.querySelectorAll("#view-cards, #view-lista, #view-timeline").forEach(el => {
        el.classList.add("d-none");
    });
    
    // Remover classe active dos botões
    document.querySelectorAll("#btn-cards, #btn-lista, #btn-timeline").forEach(btn => {
        btn.classList.remove("active");
    });
    
    // Mostrar visualização selecionada
    document.getElementById("view-" + tipo).classList.remove("d-none");
    document.getElementById("btn-" + tipo).classList.add("active");
    
    // Salvar preferência
    localStorage.setItem("visualizacao-propostas", tipo);
}

function toggleComparacao() {
    modoComparacao = !modoComparacao;
    const checkboxes = document.querySelectorAll(".comparison-checkbox");
    
    checkboxes.forEach(cb => {
        cb.classList.toggle("d-none", !modoComparacao);
    });
    
    if (!modoComparacao) {
        // Limpar seleções
        propostasSelecionadas = [];
        document.querySelectorAll(".comparison-checkbox input").forEach(input => {
            input.checked = false;
        });
        document.querySelectorAll(".card-proposta").forEach(card => {
            card.classList.remove("selected");
        });
        document.getElementById("acoes-lote").classList.add("d-none");
    }
}

function toggleComparacaoItem(checkbox) {
    const propostaId = parseInt(checkbox.value);
    const card = checkbox.closest(".card-proposta");
    
    if (checkbox.checked) {
        propostasSelecionadas.push(propostaId);
        card.classList.add("selected");
    } else {
        propostasSelecionadas = propostasSelecionadas.filter(id => id !== propostaId);
        card.classList.remove("selected");
    }
    
    // Mostrar/ocultar ações em lote
    document.getElementById("acoes-lote").classList.toggle("d-none", propostasSelecionadas.length === 0);
    
    // Se tiver 2 ou mais, habilitar comparação
    if (propostasSelecionadas.length >= 2) {
        // Adicionar botão de comparar se não existir
        if (!document.getElementById("btn-comparar")) {
            const btnComparar = document.createElement("button");
            btnComparar.id = "btn-comparar";
            btnComparar.className = "btn btn-outline-info btn-sm";
            btnComparar.innerHTML = `<i class="bi bi-bar-chart me-1"></i>Comparar (${propostasSelecionadas.length})`;
            btnComparar.onclick = () => compararSelecionadas();
            document.getElementById("acoes-lote").appendChild(btnComparar);
        } else {
            document.getElementById("btn-comparar").innerHTML = `<i class="bi bi-bar-chart me-1"></i>Comparar (${propostasSelecionadas.length})`;
        }
    } else {
        const btnComparar = document.getElementById("btn-comparar");
        if (btnComparar) btnComparar.remove();
    }
}

function compararSelecionadas() {
    if (propostasSelecionadas.length < 2) {
        alert("Selecione pelo menos 2 propostas para comparar");
        return;
    }
    
    fetch("/chamaservico/api/comparar-propostas", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({propostas: propostasSelecionadas})
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("tabelaComparacao").innerHTML = data.html;
        new bootstrap.Modal(document.getElementById("modalComparar")).show();
    })
    .catch(error => {
        console.error("Erro ao comparar propostas:", error);
        alert("Erro ao carregar comparação. Tente novamente.");
    });
}

function exportarDados() {
    const params = new URLSearchParams(window.location.search);
    params.set("export", "excel");
    window.open(`/chamaservico/cliente/propostas/recebidas?${params.toString()}`, "_blank");
}

function aceitarSelecionadas() {
    if (propostasSelecionadas.length === 0) return;
    
    if (confirm(`Aceitar ${propostasSelecionadas.length} proposta(s) selecionada(s)?`)) {
        fetch("/chamaservico/cliente/propostas/aceitar-lote", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({propostas: propostasSelecionadas})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Erro ao aceitar propostas: " + (data.message || "Erro desconhecido"));
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            alert("Erro ao processar solicitação");
        });
    }
}

function recusarSelecionadas() {
    if (propostasSelecionadas.length === 0) return;
    
    const motivo = prompt("Motivo da recusa (opcional):");
    
    fetch("/chamaservico/cliente/propostas/recusar-lote", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            propostas: propostasSelecionadas,
            motivo: motivo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert("Erro ao recusar propostas: " + (data.message || "Erro desconhecido"));
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        alert("Erro ao processar solicitação");
    });
}

// Funções para aceitar e recusar propostas individuais
function aceitarProposta(propostaId, prestadorNome) {
    document.getElementById("propostaIdAceitar").value = propostaId;
    new bootstrap.Modal(document.getElementById("modalAceitar")).show();
}

function recusarProposta(propostaId, prestadorNome) {
    document.getElementById("propostaIdRecusar").value = propostaId;
    new bootstrap.Modal(document.getElementById("modalRecusar")).show();
}

// Restaurar visualização salva
document.addEventListener("DOMContentLoaded", function() {
    const visualizacaoSalva = localStorage.getItem("visualizacao-propostas");
    if (visualizacaoSalva && ["cards", "lista", "timeline"].includes(visualizacaoSalva)) {
        alterarVisualizacao(visualizacaoSalva);
    }
    
    // Verificar se Bootstrap está carregado
    if (typeof bootstrap === "undefined") {
        console.error("Bootstrap não está carregado");
    }
});

// Auto-submit dos filtros
document.querySelectorAll("#status, #tipo_servico, #ordenacao").forEach(select => {
    select.addEventListener("change", function() {
        this.form.submit();
    });
});

// Adicionar busca em tempo real
document.getElementById("busca").addEventListener("input", function() {
    // Debounce para evitar muitas requisições
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 1000);
});

function toggleDescricao(propostaId) {
    const descricao = document.getElementById("desc-" + propostaId);
    const botao = document.getElementById("btn-" + propostaId);
    
    if (descricao.classList.contains("d-none")) {
        descricao.classList.remove("d-none");
        botao.textContent = " ver menos";
    } else {
        descricao.classList.add("d-none");
        botao.textContent = "... ver mais";
    }
}

function enviarMensagem(propostaId) {
    // Implementar sistema de mensagens
    alert("Funcionalidade de mensagens em desenvolvimento");
}

function verPerfilPrestador(prestadorId) {
    window.open("/chamaservico/prestador/perfil?id=" + prestadorId, "_blank");
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
