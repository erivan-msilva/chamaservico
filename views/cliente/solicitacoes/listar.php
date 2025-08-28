<?php
$title = 'Minhas Solicitações - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-task me-2"></i>Minhas Solicitações</h2>
    <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nova Solicitação
    </a>
</div>

<!-- Filtros e Controles de Visualização -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <form method="GET" class="row g-3" id="filterForm">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">Todos os status</option>
                            <option value="1" <?= ($_GET['status'] ?? '') == '1' ? 'selected' : '' ?>>Aguardando Propostas</option>
                            <option value="3" <?= ($_GET['status'] ?? '') == '3' ? 'selected' : '' ?>>Proposta Aceita</option>
                            <option value="5" <?= ($_GET['status'] ?? '') == '5' ? 'selected' : '' ?>>Concluído</option>
                            <option value="6" <?= ($_GET['status'] ?? '') == '6' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="urgencia" class="form-label">Urgência</label>
                        <select class="form-select" name="urgencia" id="urgencia">
                            <option value="">Todas as urgências</option>
                            <option value="baixa" <?= ($_GET['urgencia'] ?? '') == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                            <option value="media" <?= ($_GET['urgencia'] ?? '') == 'media' ? 'selected' : '' ?>>Média</option>
                            <option value="alta" <?= ($_GET['urgencia'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="busca" class="form-label">Buscar</label>
                        <input type="text" class="form-control" name="busca" id="busca"
                            placeholder="Título ou descrição..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="filtrar()">
                            <i class="bi bi-funnel me-1"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-end align-items-end h-100">
                    <!-- Busca -->
                    <div class="input-group me-3" style="max-width: 300px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput"
                            placeholder="Buscar..."
                            value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    </div>

                    <!-- Botões de Visualização -->
                    <div class="btn-group" role="group" aria-label="Visualização">
                        <button type="button" class="btn btn-outline-secondary active" id="btnCards" onclick="alterarVisualizacao('cards')">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnLista" onclick="alterarVisualizacao('lista')">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnTimeline" onclick="alterarVisualizacao('timeline')">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Exportar e Limpar -->
        <div class="row mt-3">
            <div class="col-md-6">
                <?php if (!empty($_GET['status']) || !empty($_GET['urgencia']) || !empty($_GET['busca'])): ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="limparFiltros()">
                        <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                    </button>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down me-1"></i>Ordenar
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="ordenarPor('data_desc')">Data (Mais recente)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="ordenarPor('data_asc')">Data (Mais antigo)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="ordenarPor('titulo')">Título (A-Z)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="ordenarPor('status')">Status</a></li>
                        <li><a class="dropdown-item" href="#" onclick="ordenarPor('urgencia')">Urgência</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($solicitacoes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma solicitação encontrada</h4>
        <p class="text-muted">
            <?= !empty($_GET['status']) || !empty($_GET['urgencia']) || !empty($_GET['busca'])
                ? 'Tente ajustar os filtros ou ' : '' ?>
            Crie sua primeira solicitação de serviço!
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Criar Solicitação
            </a>
            <?php if (!empty($_GET['status']) || !empty($_GET['urgencia']) || !empty($_GET['busca'])): ?>
                <button type="button" class="btn btn-outline-secondary" onclick="limparFiltros()">
                    <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Visualização em Cards (padrão) -->
    <div id="viewCards" class="view-container">
        <div class="row" id="cardsContainer">
            <?php foreach ($solicitacoes as $solicitacao): ?>
                <div class="col-md-6 col-lg-4 mb-4 solicitacao-item"
                    data-status="<?= $solicitacao['status_id'] ?>"
                    data-titulo="<?= strtolower($solicitacao['titulo']) ?>"
                    data-descricao="<?= strtolower($solicitacao['descricao']) ?>"
                    data-data="<?= $solicitacao['data_solicitacao'] ?>">
                    <div class="card h-100 shadow-sm border-start border-4"
                        style="border-left-color: <?= htmlspecialchars($solicitacao['status_cor'] ?? '#283579') ?> !important;">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                            </small>
                            <span class="badge" style="background-color: <?= htmlspecialchars($solicitacao['status_cor'] ?? '#283579') ?>;">
                                <?= htmlspecialchars($solicitacao['status_nome'] ?? 'Sem status') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($solicitacao['titulo'] ?? 'Sem título') ?></h6>
                            <p class="card-text">
                                <small class="text-primary">
                                    <i class="bi bi-tools me-1"></i>
                                    <?= htmlspecialchars($solicitacao['tipo_servico_nome'] ?? 'Tipo não informado') ?>
                                </small>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($solicitacao['descricao'] ?? 'Sem descrição', 0, 100)) ?>...</p>

                            <!-- Indicadores -->
                            <div class="mb-3">
                                <?php if (($solicitacao['total_imagens'] ?? 0) > 0): ?>
                                    <span class="badge bg-info me-1">
                                        <i class="bi bi-camera me-1"></i>
                                        <?= $solicitacao['total_imagens'] ?> foto<?= $solicitacao['total_imagens'] > 1 ? 's' : '' ?>
                                    </span>
                                <?php endif; ?>

                                <span class="badge bg-<?= ($solicitacao['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($solicitacao['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($solicitacao['urgencia'] ?? 'media') ?>
                                </span>

                                <?php if (!empty($solicitacao['orcamento_estimado'])): ?>
                                    <span class="badge bg-success">
                                        R$ <?= number_format($solicitacao['orcamento_estimado'], 0, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars(($solicitacao['cidade'] ?? 'Cidade') . ', ' . ($solicitacao['estado'] ?? 'UF')) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-1">
                                <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
                                    class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-eye me-1"></i>Ver
                                </a>

                                <?php if (in_array($solicitacao['status_id'] ?? 0, [1, 2])): ?>
                                    <a href="/chamaservico/cliente/solicitacoes/editar?id=<?= $solicitacao['id'] ?>"
                                        class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                <?php endif; ?>

                                <button type="button"
                                    class="btn btn-outline-danger btn-sm flex-fill"
                                    onclick="confirmarExclusao(<?= $solicitacao['id'] ?>)">
                                    <i class="bi bi-trash me-1"></i>Excluir
                                </button>
                            </div>

                            <?php if ($solicitacao['status_id'] == 1): ?>
                                <div class="mt-2">
                                    <a href="/chamaservico/cliente/propostas/recebidas?solicitacao_id=<?= $solicitacao['id'] ?>"
                                        class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-inbox me-1"></i>Ver Propostas Recebidas
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Visualização em Lista -->
    <div id="viewLista" class="view-container" style="display: none;">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Urgência</th>
                        <th>Orçamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <tr class="solicitacao-item"
                            data-status="<?= $solicitacao['status_id'] ?>"
                            data-titulo="<?= strtolower($solicitacao['titulo']) ?>"
                            data-descricao="<?= strtolower($solicitacao['descricao']) ?>"
                            data-data="<?= $solicitacao['data_solicitacao'] ?>">
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($solicitacao['titulo']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(substr($solicitacao['descricao'], 0, 60)) ?>...
                                    </small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></td>
                            <td>
                                <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($solicitacao['urgencia']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $solicitacao['orcamento_estimado'] ? 'R$ ' . number_format($solicitacao['orcamento_estimado'], 2, ',', '.') : '-' ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
                                        class="btn btn-outline-primary" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (in_array($solicitacao['status_id'], [1, 2])): ?>
                                        <a href="/chamaservico/cliente/solicitacoes/editar?id=<?= $solicitacao['id'] ?>"
                                            class="btn btn-outline-secondary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button"
                                        class="btn btn-outline-danger"
                                        onclick="confirmarExclusao(<?= $solicitacao['id'] ?>)" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Visualização Timeline -->
    <div id="viewTimeline" class="view-container" style="display: none;">
        <div class="timeline">
            <?php foreach ($solicitacoes as $index => $solicitacao): ?>
                <div class="timeline-item solicitacao-item"
                    data-status="<?= $solicitacao['status_id'] ?>"
                    data-titulo="<?= strtolower($solicitacao['titulo']) ?>"
                    data-descricao="<?= strtolower($solicitacao['descricao']) ?>"
                    data-data="<?= $solicitacao['data_solicitacao'] ?>">
                    <div class="timeline-marker" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                        <i class="bi bi-tools text-white"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                                    </small>
                                </div>
                                <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="card-text">
                                            <small class="text-primary">
                                                <i class="bi bi-tools me-1"></i>
                                                <?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?>
                                            </small>
                                        </p>
                                        <p><?= htmlspecialchars(substr($solicitacao['descricao'], 0, 150)) ?>...</p>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-<?= $solicitacao['urgencia'] === 'alta' ? 'danger' : ($solicitacao['urgencia'] === 'media' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($solicitacao['urgencia']) ?>
                                            </span>
                                            <?php if ($solicitacao['total_imagens'] > 0): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-camera me-1"></i><?= $solicitacao['total_imagens'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php if ($solicitacao['orcamento_estimado']): ?>
                                            <div class="mb-2">
                                                <small class="text-success">
                                                    <i class="bi bi-currency-dollar me-1"></i>
                                                    R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $solicitacao['id'] ?>"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Ver
                                            </a>
                                            <?php if (in_array($solicitacao['status_id'], [1, 2])): ?>
                                                <a href="/chamaservico/cliente/solicitacoes/editar?id=<?= $solicitacao['id'] ?>"
                                                    class="btn btn-outline-secondary">
                                                    <i class="bi bi-pencil me-1"></i>Editar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-primary"><?= count($solicitacoes) ?></h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-warning">
                            <?= count(array_filter($solicitacoes, fn($s) => $s['status_id'] == 1)) ?>
                        </h4>
                        <small class="text-muted">Aguardando</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success">
                            <?= count(array_filter($solicitacoes, fn($s) => $s['status_id'] == 5)) ?>
                        </h4>
                        <small class="text-muted">Concluídos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-info">
                            <?= array_sum(array_column($solicitacoes, 'total_imagens')) ?>
                        </h4>
                        <small class="text-muted">Fotos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta solicitação?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as propostas relacionadas serão perdidas.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="/chamaservico/cliente/solicitacoes/deletar" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="id" id="idExcluir">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding: 0;
        margin: 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 30px;
        height: 100%;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 80px;
    }

    .timeline-marker {
        position: absolute;
        left: 20px;
        top: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .timeline-content {
        position: relative;
    }

    .timeline-content::before {
        content: '';
        position: absolute;
        top: 15px;
        left: -10px;
        border: 5px solid transparent;
        border-right-color: #dee2e6;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .timeline-item {
            padding-left: 60px;
        }

        .timeline::before {
            left: 25px;
        }

        .timeline-marker {
            left: 15px;
        }
    }


    .view-container {
        transition: opacity 0.3s ease-in-out;
    }
</style>

<?php
$scripts = '
<script>
let currentView = "cards";

function alterarVisualizacao(view) {
    // Atualizar botões
    document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.remove("active"));
    document.getElementById("btn" + view.charAt(0).toUpperCase() + view.slice(1)).classList.add("active");
    
    // Esconder todas as views
    document.querySelectorAll(".view-container").forEach(container => {
        container.style.display = "none";
    });
    
    // Mostrar view selecionada
    document.getElementById("view" + view.charAt(0).toUpperCase() + view.slice(1)).style.display = "block";
    
    currentView = view;
    localStorage.setItem("solicitacoes_view", view);
}

function confirmarExclusao(id) {
    document.getElementById("idExcluir").value = id;
    new bootstrap.Modal(document.getElementById("modalExcluir")).show();
}

function filtrar() {
    const formData = new FormData(document.getElementById("filterForm"));
    const busca = document.getElementById("searchInput").value;
    if (busca) formData.append("busca", busca);
    
    const params = new URLSearchParams(formData);
    window.location.href = window.location.pathname + "?" + params.toString();
}

function limparFiltros() {
    window.location.href = window.location.pathname;
}

function ordenarPor(criterio) {
    const url = new URL(window.location);
    url.searchParams.set("ordem", criterio);
    window.location.href = url.toString();
}

// Busca em tempo real
document.getElementById("searchInput").addEventListener("input", function() {
    const termo = this.value.toLowerCase();
    const itens = document.querySelectorAll(".solicitacao-item");
    
    itens.forEach(item => {
        const titulo = item.getAttribute("data-titulo") || "";
        const descricao = item.getAttribute("data-descricao") || "";
        
        if (titulo.includes(termo) || descricao.includes(termo)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
});

// Filtros automáticos
document.querySelectorAll("#status, #urgencia").forEach(select => {
    select.addEventListener("change", function() {
        filtrar();
    });
});

// Enter para buscar
document.getElementById("searchInput").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        filtrar();
    }
});

// Restaurar visualização salva
document.addEventListener("DOMContentLoaded", function() {
    const savedView = localStorage.getItem("solicitacoes_view");
    if (savedView && ["cards", "lista", "timeline"].includes(savedView)) {
        alterarVisualizacao(savedView);
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>