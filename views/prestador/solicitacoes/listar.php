<?php
$title = 'Buscar Serviços - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-search me-2"></i>Buscar Serviços</h2>
        <p class="text-muted mb-0">Encontre oportunidades de trabalho na sua área</p>
    </div>
    <div>
        <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas
        </a>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Busca</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                <select class="form-select" name="tipo_servico" id="tipo_servico">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($tiposServico as $tipo): ?>
                        <option value="<?= $tipo['id'] ?>" <?= ($_GET['tipo_servico'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="urgencia" class="form-label">Urgência</label>
                <select class="form-select" name="urgencia" id="urgencia">
                    <option value="">Todas</option>
                    <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="cidade" class="form-label">Cidade</label>
                <select class="form-select" name="cidade" id="cidade">
                    <option value="">Todas as cidades</option>
                    <?php foreach ($cidades as $cidade): ?>
                        <option value="<?= htmlspecialchars($cidade) ?>" <?= ($_GET['cidade'] ?? '') == $cidade ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cidade) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="orcamento_min" class="form-label">Orçamento Mín.</label>
                <input type="number" class="form-control" name="orcamento_min" id="orcamento_min" 
                       placeholder="R$ 0" value="<?= htmlspecialchars($_GET['orcamento_min'] ?? '') ?>">
            </div>
            
            <div class="col-md-2">
                <label for="orcamento_max" class="form-label">Orçamento Máx.</label>
                <input type="number" class="form-control" name="orcamento_max" id="orcamento_max" 
                       placeholder="R$ 999999" value="<?= htmlspecialchars($_GET['orcamento_max'] ?? '') ?>">
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Controles de Visualização -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted fw-semibold">Visualização:</span>
        <div class="btn-group" role="group" aria-label="Tipo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="btnCards" onclick="alternarVisualizacao('cards')">
                <i class="bi bi-grid-3x3 me-1"></i>Cards
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnLista" onclick="alternarVisualizacao('lista')">
                <i class="bi bi-list me-1"></i>Lista
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnMapa" onclick="alternarVisualizacao('mapa')">
                <i class="bi bi-geo-alt me-1"></i>Mapa
            </button>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">
            <i class="bi bi-list-ul me-1"></i>
            <?= count($solicitacoes ?? []) ?> solicitação(ões) encontrada(s)
        </span>
        <div class="vr"></div>
        <button class="btn btn-outline-success btn-sm" onclick="atualizarSolicitacoes()">
            <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
        </button>
    </div>
</div>

<!-- Lista de Solicitações -->
<?php if (empty($solicitacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma solicitação encontrada</h4>
        <p class="text-muted">Ajuste os filtros ou aguarde novas solicitações.</p>
        <a href="/chamaservico/prestador/solicitacoes" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Limpar Filtros
        </a>
    </div>
<?php else: ?>

    <!-- Visualização em Cards -->
    <div id="visualizacaoCards" class="view-mode">
        <div class="row g-4">
            <?php foreach ($solicitacoes as $solicitacao): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                            </small>
                            <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                <?= ucfirst($solicitacao['urgencia'] ?? 'media') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                            <p class="card-text text-muted">
                                <?= htmlspecialchars(substr($solicitacao['descricao'], 0, 100)) ?>...
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Tipo:</small><br>
                                    <span class="badge bg-primary"><?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Orçamento:</small><br>
                                    <strong class="text-success">
                                        <?= $solicitacao['orcamento_estimado'] ? 'R$ ' . number_format($solicitacao['orcamento_estimado'], 2, ',', '.') : 'A combinar' ?>
                                    </strong>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>
                                </small>
                            </div>
                            
                            <?php if ($solicitacao['data_atendimento']): ?>
                                <div class="mb-3">
                                    <small class="text-info">
                                        <i class="bi bi-clock me-1"></i>
                                        Preferência: <?= date('d/m/Y H:i', strtotime($solicitacao['data_atendimento'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $solicitacao['id'] ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-eye me-1"></i>Ver Detalhes e Enviar Proposta
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Visualização em Lista -->
    <div id="visualizacaoLista" class="view-mode" style="display: none;">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Solicitação</th>
                            <th>Tipo</th>
                            <th>Localização</th>
                            <th>Orçamento</th>
                            <th>Urgência</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></small>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($solicitacao['titulo']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($solicitacao['descricao'], 0, 60)) ?>...</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        <?= $solicitacao['orcamento_estimado'] ? 'R$ ' . number_format($solicitacao['orcamento_estimado'], 2, ',', '.') : 'A combinar' ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($solicitacao['urgencia'] ?? 'media') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $solicitacao['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm" title="Ver detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Visualização em Mapa -->
    <div id="visualizacaoMapa" class="view-mode" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Mapa de Solicitações</h6>
            </div>
            <div class="card-body">
                <div id="mapaContainer" style="height: 500px; background: #f8f9fa; border-radius: 8px;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="bi bi-map text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">Mapa em Desenvolvimento</h5>
                            <p class="text-muted">Funcionalidade de mapa será implementada em breve.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de solicitações no mapa -->
                <div class="row mt-4 g-3">
                    <?php foreach ($solicitacoes as $index => $solicitacao): ?>
                        <div class="col-md-6">
                            <div class="card card-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?= htmlspecialchars($solicitacao['cidade'] . ', ' . $solicitacao['estado']) ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary"><?= $index + 1 ?></span>
                                    </div>
                                    <div class="mt-2">
                                        <a href="/chamaservico/prestador/solicitacoes/detalhes?id=<?= $solicitacao['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<style>
.view-mode {
    transition: opacity 0.3s ease;
}

.hover-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    border-color: var(--bs-primary);
}

.btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.card-sm {
    transition: all 0.2s ease;
}

.card-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Responsive table improvements */
@media (max-width: 768px) {
    .table-responsive table {
        font-size: 0.875rem;
    }
    
    .hover-card:hover {
        transform: none;
    }
}
</style>

<?php
$scripts = '
<script>
function alternarVisualizacao(tipo) {
    // Esconder todas as visualizações
    document.querySelectorAll(".view-mode").forEach(el => el.style.display = "none");
    
    // Remover classe active de todos os botões
    document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.remove("active"));
    
    // Mostrar visualização selecionada
    if (tipo === "cards") {
        document.getElementById("visualizacaoCards").style.display = "block";
        document.getElementById("btnCards").classList.add("active");
    } else if (tipo === "lista") {
        document.getElementById("visualizacaoLista").style.display = "block";
        document.getElementById("btnLista").classList.add("active");
    } else if (tipo === "mapa") {
        document.getElementById("visualizacaoMapa").style.display = "block";
        document.getElementById("btnMapa").classList.add("active");
        
        // Inicializar mapa se necessário
        setTimeout(() => {
            inicializarMapa();
        }, 300);
    }
    
    // Salvar preferência no localStorage
    localStorage.setItem("visualizacaoSolicitacoes", tipo);
}

function inicializarMapa() {
    // Placeholder para inicialização do mapa
    console.log("Mapa seria inicializado aqui");
    
    // Simular carregamento do mapa
    const mapaContainer = document.getElementById("mapaContainer");
    if (mapaContainer) {
        mapaContainer.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando mapa...</span>
                    </div>
                    <p class="text-muted">Carregando mapa...</p>
                </div>
            </div>
        `;
        
        // Simular carregamento
        setTimeout(() => {
            mapaContainer.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center">
                        <i class="bi bi-map text-success" style="font-size: 3rem;"></i>
                        <h5 class="text-success mt-3">Mapa Carregado!</h5>
                        <p class="text-muted">Implementação Futura: Aqui seria exibido o mapa com as solicitações.</p>
                    </div>
                </div>
            `;
        }, 1500);
    }
}

function atualizarSolicitacoes() {
    // Recarregar a página mantendo os filtros
    window.location.reload();
}

// Carregar preferência de visualização
document.addEventListener("DOMContentLoaded", function() {
    const visualizacaoSalva = localStorage.getItem("visualizacaoSolicitacoes") || "cards";
    alternarVisualizacao(visualizacaoSalva);
});

// Auto-refresh a cada 2 minutos
setInterval(() => {
    if (document.visibilityState === "visible") {
        const now = new Date();
        const lastRefresh = localStorage.getItem("lastRefreshSolicitacoes");
        
        if (!lastRefresh || (now - new Date(lastRefresh)) > 120000) { // 2 minutos
            const btn = document.querySelector(".btn-outline-success");
            if (btn) {
                btn.innerHTML = "<i class=\"bi bi-arrow-clockwise me-1 spinning\"></i>Atualizando...";
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }
    }
}, 120000);

// Salvar timestamp do último refresh
localStorage.setItem("lastRefreshSolicitacoes", new Date().toISOString());
</script>

<style>
.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
