<?php
$title = 'Minhas Propostas - ChamaServiço';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text me-2"></i>Minhas Propostas</h2>
    <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-success">
        <i class="bi bi-search me-1"></i>Buscar Mais Serviços
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= ($_GET['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="aceita" <?= ($_GET['status'] ?? '') == 'aceita' ? 'selected' : '' ?>>Aceita</option>
                    <option value="recusada" <?= ($_GET['status'] ?? '') == 'recusada' ? 'selected' : '' ?>>Recusada</option>
                    <option value="cancelada" <?= ($_GET['status'] ?? '') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                </div>
                
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="<?= url('prestador/propostas') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpar
                    </a>
                </div>
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
            <button type="button" class="btn btn-outline-primary" id="btnTimeline" onclick="alternarVisualizacao('timeline')">
                <i class="bi bi-clock-history me-1"></i>Timeline
            </button>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">
            <i class="bi bi-list-ul me-1"></i>
            <?= count($propostas ?? []) ?> proposta(s) encontrada(s)
        </span>
    </div>
</div>

<?php if (empty($propostas)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="text-muted mt-3">Nenhuma proposta encontrada</h4>
        <p class="text-muted">Você ainda não enviou propostas para solicitações.</p>
        <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-success">
            <i class="bi bi-search me-1"></i>Buscar Serviços Disponíveis
        </a>
    </div>
<?php else: ?>

    <!-- Visualização em Cards -->
    <div id="visualizacaoCards" class="view-mode">
        <div class="row">
            <?php foreach ($propostas as $proposta): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <small class="text-white">
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?>
                            </small>
                            <span class="badge bg-<?= ($proposta['status'] ?? 'pendente') == 'pendente' ? 'warning' : (($proposta['status'] ?? 'pendente') == 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'danger' : 'secondary')) ?>">
                                <?= ucfirst($proposta['status'] ?? 'pendente') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Título não disponível') ?></h6>
                            
                            <div class="mb-3">
                                <strong class="text-success">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></strong>
                                <span class="text-muted">em <?= $proposta['prazo_execucao'] ?? 'N/A' ?> dia(s)</span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-primary">
                                    <i class="bi bi-person me-1"></i>
                                    Cliente: <?= htmlspecialchars($proposta['cliente_nome'] ?? 'Nome não disponível') ?>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars(($proposta['cidade'] ?? 'Cidade') . ', ' . ($proposta['estado'] ?? 'UF')) ?>
                                </small>
                            </div>
                            
                            <p class="card-text"><?= htmlspecialchars(substr($proposta['descricao'] ?? 'Sem descrição', 0, 100)) ?>...</p>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex gap-2">
                                <a href="<?= url('prestador/solicitacoes/detalhes?id=' . $proposta['solicitacao_id']) ?>" 
                                   class="btn btn-outline-success btn-sm flex-fill">
                                    <i class="bi bi-eye me-1"></i>Ver Solicitação
                                </a>
                                <?php if ($proposta['status'] === 'pendente'): ?>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmarCancelamento(<?= $proposta['id'] ?>)">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
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
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Prazo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propostas as $proposta): ?>
                            <tr>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?></small>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Título não disponível') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($proposta['descricao'] ?? 'Sem descrição', 0, 50)) ?>...</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?= htmlspecialchars($proposta['cliente_nome'] ?? 'Nome não disponível') ?>
                                        <br><small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars(($proposta['cidade'] ?? 'Cidade') . ', ' . ($proposta['estado'] ?? 'UF')) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?= $proposta['prazo_execucao'] ?? 'N/A' ?> dia(s)
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($proposta['status'] ?? 'pendente') == 'pendente' ? 'warning' : (($proposta['status'] ?? 'pendente') == 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'danger' : 'secondary')) ?>">
                                        <?= ucfirst($proposta['status'] ?? 'pendente') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('prestador/solicitacoes/detalhes?id=' . $proposta['solicitacao_id']) ?>" 
                                           class="btn btn-outline-success" title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($proposta['status'] === 'pendente'): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmarCancelamento(<?= $proposta['id'] ?>)"
                                                    title="Cancelar proposta">
                                                <i class="bi bi-x-circle"></i>
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
    <div id="visualizacaoTimeline" class="view-mode" style="display: none;">
        <div class="timeline">
            <?php foreach ($propostas as $index => $proposta): ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-<?= ($proposta['status'] ?? 'pendente') == 'pendente' ? 'warning' : (($proposta['status'] ?? 'pendente') == 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'danger' : 'secondary')) ?>">
                        <i class="bi bi-<?= ($proposta['status'] ?? 'pendente') == 'aceita' ? 'check-circle' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'x-circle' : 'clock') ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Título não disponível') ?></h6>
                                    <span class="badge bg-<?= ($proposta['status'] ?? 'pendente') == 'pendente' ? 'warning' : (($proposta['status'] ?? 'pendente') == 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') == 'recusada' ? 'danger' : 'secondary')) ?>">
                                        <?= ucfirst($proposta['status'] ?? 'pendente') ?>
                                    </span>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="text-muted mb-2"><?= htmlspecialchars(substr($proposta['descricao'] ?? 'Sem descrição', 0, 120)) ?>...</p>
                                        
                                        <div class="mb-2">
                                            <small class="text-primary">
                                                <i class="bi bi-person me-1"></i>
                                                Cliente: <?= htmlspecialchars($proposta['cliente_nome'] ?? 'Nome não disponível') ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?= htmlspecialchars(($proposta['cidade'] ?? 'Cidade') . ', ' . ($proposta['estado'] ?? 'UF')) ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            <strong class="text-success fs-5">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></strong>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Prazo: <?= $proposta['prazo_execucao'] ?? 'N/A' ?> dia(s)</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="<?= url('prestador/solicitacoes/detalhes?id=' . $proposta['solicitacao_id']) ?>" 
                                       class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-eye me-1"></i>Ver Solicitação
                                    </a>
                                    <?php if ($proposta['status'] === 'pendente'): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="confirmarCancelamento(<?= $proposta['id'] ?>)">
                                            <i class="bi bi-x-circle me-1"></i>Cancelar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php endif; ?>

<!-- Modal de Confirmação para Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja cancelar esta proposta?</p>
                <p class="text-warning"><small>Esta ação não pode ser desfeita e você não poderá enviar uma nova proposta para esta solicitação.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <form method="POST" action="<?= url('prestador/propostas/cancelar') ?>" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" id="propostaIdCancelar">
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.view-mode {
    transition: opacity 0.3s ease;
}

/* Adicione para garantir fundo escuro nos cards e texto branco */
.card-header {
    background-color: #283579 !important;
    color: white !important;
}

.card-header .text-white {
    color: #fff !important;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: white;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
</style>

<?php
$scripts = '
<script>
// CORREÇÃO: Funções globais acessíveis pelos onclick do HTML
function alternarVisualizacao(tipo) {
    try {
        // Esconder todas as visualizações
        document.querySelectorAll(".view-mode").forEach(el => {
            if (el) el.style.display = "none";
        });
        
        // Remover classe active de todos os botões
        document.querySelectorAll(".btn-group .btn").forEach(btn => {
            if (btn) btn.classList.remove("active");
        });
        
        // Mostrar visualização selecionada
        const elementosVis = {
            "cards": "visualizacaoCards",
            "lista": "visualizacaoLista", 
            "timeline": "visualizacaoTimeline"
        };
        
        const botoes = {
            "cards": "btnCards",
            "lista": "btnLista",
            "timeline": "btnTimeline"
        };
        
        const elemento = document.getElementById(elementosVis[tipo]);
        const botao = document.getElementById(botoes[tipo]);
        
        if (elemento && botao) {
            elemento.style.display = "block";
            botao.classList.add("active");
        }
        
        // Salvar preferência
        localStorage.setItem("visualizacaoPropostas", tipo);
    } catch (error) {
        console.error("Erro ao alternar visualização:", error);
    }
}

function confirmarCancelamento(id) {
    if (!id || isNaN(id)) {
        console.error("ID inválido para cancelamento:", id);
        return;
    }
    
    const inputId = document.getElementById("propostaIdCancelar");
    if (inputId) {
        inputId.value = id;
        const modal = new bootstrap.Modal(document.getElementById("modalCancelar"));
        modal.show();
    } else {
        console.error("Elemento propostaIdCancelar não encontrado");
    }
}

// Event listeners após DOM pronto
document.addEventListener("DOMContentLoaded", function() {
    try {
        // Carregar preferência de visualização
        const visualizacaoSalva = localStorage.getItem("visualizacaoPropostas") || "cards";
        alternarVisualizacao(visualizacaoSalva);
        
        console.log("Página de propostas carregada com sucesso");
    } catch (error) {
        console.error("Erro na inicialização:", error);
        // Fallback para visualização padrão
        alternarVisualizacao("cards");
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
