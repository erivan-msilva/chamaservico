<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin/login');
    exit;
}

// Configuração do layout
$title = 'Tipos de Serviços - Admin';
$currentPage = 'tipos-servico';

// Buscar dados reais do banco de dados
try {
    require_once 'core/Database.php';
    $db = Database::getInstance();

    // Buscar estatísticas reais
    $sqlStats = "
        SELECT 
            COUNT(*) as total_tipos,
            SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as tipos_ativos,
            SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as tipos_inativos,
            (SELECT COUNT(*) FROM tb_solicita_servico s 
             JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id 
             WHERE DATE(s.data_solicitacao) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as solicitacoes_mes
        FROM tb_tipo_servico
    ";

    $stmt = $db->prepare($sqlStats);
    $stmt->execute();
    $estatisticas = $stmt->fetch() ?: [
        'total_tipos' => 0,
        'tipos_ativos' => 0,
        'tipos_inativos' => 0,
        'solicitacoes_mes' => 0
    ];

    // Buscar tipos de serviços com filtros
    $filtros = [
        'categoria' => $_GET['categoria'] ?? '',
        'status' => $_GET['status'] ?? '',
        'busca' => $_GET['busca'] ?? ''
    ];

    $sqlTipos = "
        SELECT 
            ts.*,
            (SELECT COUNT(*) FROM tb_solicita_servico WHERE tipo_servico_id = ts.id) as total_solicitacoes,
            (SELECT COUNT(*) FROM tb_solicita_servico s 
             WHERE s.tipo_servico_id = ts.id 
             AND DATE(s.data_solicitacao) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as solicitacoes_recentes
        FROM tb_tipo_servico ts 
        WHERE 1=1
    ";

    $params = [];

    if (!empty($filtros['categoria'])) {
        $sqlTipos .= " AND ts.categoria = ?";
        $params[] = $filtros['categoria'];
    }

    if ($filtros['status'] !== '') {
        $ativo = $filtros['status'] === 'ativo' ? 1 : 0;
        $sqlTipos .= " AND ts.ativo = ?";
        $params[] = $ativo;
    }

    if (!empty($filtros['busca'])) {
        $sqlTipos .= " AND (ts.nome LIKE ? OR ts.descricao LIKE ?)";
        $termoBusca = '%' . $filtros['busca'] . '%';
        $params[] = $termoBusca;
        $params[] = $termoBusca;
    }

    $sqlTipos .= " ORDER BY ts.nome ASC";

    $stmt = $db->prepare($sqlTipos);
    $stmt->execute($params);
    $tiposServico = $stmt->fetchAll();

    // Buscar categorias disponíveis
    $sqlCategorias = "SELECT DISTINCT categoria FROM tb_tipo_servico WHERE categoria IS NOT NULL ORDER BY categoria";
    $stmt = $db->prepare($sqlCategorias);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Erro ao buscar dados de tipos de serviços: " . $e->getMessage());
    $estatisticas = [
        'total_tipos' => 0,
        'tipos_ativos' => 0,
        'tipos_inativos' => 0,
        'solicitacoes_mes' => 0
    ];
    $tiposServico = [];
    $categorias = [];
}

ob_start();
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <h1 class="h2 text-dark">
        <i class="bi bi-tools me-2"></i>
        Tipos de Serviços -Admin
    </h1>

    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoTipo">
                <i class="bi bi-plus-circle me-1"></i>
                Novo Tipo
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="exportarTipos()">
                <i class="bi bi-download me-1"></i>
                Exportar
            </button>
        </div>

        <button type="button" class="btn btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Atualizar
        </button>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['admin_flash'])): ?>
    <?php $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']); ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #007bff;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                        Total de Tipos
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($estatisticas['total_tipos']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-tools fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                        Tipos Ativos
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($estatisticas['tipos_ativos']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #17a2b8;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                        Solicitações (30d)
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($estatisticas['solicitacoes_mes']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-graph-up fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label class="form-label fw-bold">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control"
                        placeholder="Nome ou descrição..."
                        value="<?= htmlspecialchars($filtros['busca']) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Categoria</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= htmlspecialchars($categoria) ?>"
                            <?= $filtros['categoria'] === $categoria ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo" <?= $filtros['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $filtros['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="admin/tipos-servico" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle me-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Tipos de Serviços -->
<div class="content-area">
    <?php if (empty($tiposServico)): ?>
        <div class="text-center py-5">
            <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">Nenhum tipo de serviço encontrado</h4>
            <p class="text-muted">
                <?php if (!empty(array_filter($filtros))): ?>
                    Tente ajustar os filtros de busca.
                <?php else: ?>
                    Não há tipos de serviços cadastrados no sistema ainda.
                <?php endif; ?>
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoTipo">
                <i class="bi bi-plus-circle me-1"></i>Criar Primeiro Tipo
            </button>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Atividade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiposServico as $tipo): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($tipo['nome']) ?></div>
                                            <?php if ($tipo['descricao']): ?>
                                                <small class="text-muted"><?= htmlspecialchars(substr($tipo['descricao'], 0, 60)) ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tipo['categoria']): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($tipo['categoria']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $tipo['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $tipo['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-primary" title="Total de solicitações">
                                                <?= $tipo['total_solicitacoes'] ?>
                                            </span>
                                            <?php if ($tipo['solicitacoes_recentes'] > 0): ?>
                                                <span class="badge bg-warning" title="Solicitações nos últimos 30 dias">
                                                    +<?= $tipo['solicitacoes_recentes'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary"
                                                onclick="editarTipo(<?= $tipo['id'] ?>)"
                                                title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-<?= $tipo['ativo'] ? 'warning' : 'success' ?>"
                                                onclick="alterarStatus(<?= $tipo['id'] ?>, <?= $tipo['ativo'] ? 0 : 1 ?>)"
                                                title="<?= $tipo['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="bi bi-<?= $tipo['ativo'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                onclick="confirmarExclusao(<?= $tipo['id'] ?>)"
                                                title="Excluir">
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
        </div>

        <!-- Informações da listagem -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                Mostrando <?= count($tiposServico) ?> tipo<?= count($tiposServico) != 1 ? 's' : '' ?> de serviço<?= count($tiposServico) != 1 ? 's' : '' ?>
            </small>

            <div class="d-flex gap-2">
                <small class="text-muted align-self-center">
                    Última atualização: <?= date('d/m/Y H:i') ?>
                </small>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Novo Tipo -->
<div class="modal fade" id="modalNovoTipo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Novo Tipo de Serviço
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/tipos-servico/criar') ?>">

                <div class=" modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome" class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required
                                placeholder="Ex: Serviços Elétricos">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="categoria" class="form-label fw-bold">Categoria</label>
                            <input type="text" class="form-control" id="categoria" name="categoria"
                                placeholder="Ex: Elétrica" list="categoriasList">
                            <datalist id="categoriasList">
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>">
                                    <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label fw-bold">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"
                            placeholder="Descrição detalhada do tipo de serviço..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ativo" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="ativo" name="ativo">
                                <option value="1" selected>Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Salvar Tipo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Tipo -->
<div class="modal fade" id="modalEditarTipo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Editar Tipo de Serviço
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin/tipos-servico/editar" id="formEditarTipo">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="id" id="editId">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="editNome" class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" id="editNome" name="nome" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editCategoria" class="form-label fw-bold">Categoria</label>
                            <input type="text" class="form-control" id="editCategoria" name="categoria"
                                list="categoriasList">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editDescricao" class="form-label fw-bold">Descrição</label>
                        <textarea class="form-control" id="editDescricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAtivo" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="editAtivo" name="ativo">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Confirmar Ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalMensagem">
                Tem certeza que deseja realizar esta ação?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Estilos específicos da página
$styles = '
.stats-widget {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border-left: 4px solid;
    margin-bottom: 1.5rem;
    transition: transform 0.2s;
}
.stats-widget:hover {
    transform: translateY(-2px);
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
.badge {
    font-size: 0.75rem;
}
';

// Scripts específicos da página
$scripts = '
<script>
let tiposData = ' . json_encode($tiposServico) . ';

function editarTipo(id) {
    const tipo = tiposData.find(t => t.id == id);
    if (!tipo) {
        alert("Tipo de serviço não encontrado!");
        return;
    }
    
    document.getElementById("editId").value = tipo.id;
    document.getElementById("editNome").value = tipo.nome;
    document.getElementById("editCategoria").value = tipo.categoria || "";
    document.getElementById("editDescricao").value = tipo.descricao || "";
    document.getElementById("editAtivo").value = tipo.ativo;
    
    new bootstrap.Modal(document.getElementById("modalEditarTipo")).show();
}

function alterarStatus(id, novoStatus) {
    const acao = novoStatus ? "ativar" : "desativar";
    const modal = new bootstrap.Modal(document.getElementById("modalConfirmacao"));
    
    document.getElementById("modalTitulo").textContent = novoStatus ? "Ativar Tipo" : "Desativar Tipo";
    document.getElementById("modalMensagem").textContent = `Tem certeza que deseja ${acao} este tipo de serviço?`;
    
    const btnConfirmar = document.getElementById("btnConfirmar");
    btnConfirmar.className = `btn btn-${novoStatus ? "success" : "warning"}`;
    btnConfirmar.textContent = novoStatus ? "Ativar" : "Desativar";
    
    btnConfirmar.onclick = function() {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "admin/tipos-servico/alterar-status";
        
        const inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "id";
        inputId.value = id;
        
        const inputStatus = document.createElement("input");
        inputStatus.type = "hidden";
        inputStatus.name = "ativo";
        inputStatus.value = novoStatus;
        
        const inputToken = document.createElement("input");
        inputToken.type = "hidden";
        inputToken.name = "csrf_token";
        inputToken.value = "<?= Session::generateCSRFToken() ?>";
        
        form.appendChild(inputId);
        form.appendChild(inputStatus);
        form.appendChild(inputToken);
        document.body.appendChild(form);
        form.submit();
        
        modal.hide();
    };
    
    modal.show();
}

function confirmarExclusao(id) {
    const tipo = tiposData.find(t => t.id == id);
    const modal = new bootstrap.Modal(document.getElementById("modalConfirmacao"));
    
    document.getElementById("modalTitulo").textContent = "Excluir Tipo de Serviço";
    
    let mensagem = `Tem certeza que deseja excluir o tipo "${tipo.nome}"?`;
    if (tipo.total_solicitacoes > 0) {
        mensagem += `\\n\\nAtenção: Este tipo possui ${tipo.total_solicitacoes} solicitação${tipo.total_solicitacoes > 1 ? "ões" : ""} associada${tipo.total_solicitacoes > 1 ? "s" : ""}.`;
    }
    
    document.getElementById("modalMensagem").textContent = mensagem;
    
    const btnConfirmar = document.getElementById("btnConfirmar");
    btnConfirmar.className = "btn btn-danger";
    btnConfirmar.textContent = "Excluir";
    
    btnConfirmar.onclick = function() {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "admin/tipos-servico/excluir";
        
        const inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "id";
        inputId.value = id;
        
        const inputToken = document.createElement("input");
        inputToken.type = "hidden";
        inputToken.name = "csrf_token";
        inputToken.value = "<?= Session::generateCSRFToken() ?>";
        
        form.appendChild(inputId);
        form.appendChild(inputToken);
        document.body.appendChild(form);
        form.submit();
        
        modal.hide();
    };
    
    modal.show();
}

function exportarTipos() {
    alert("Funcionalidade de exportação será implementada em breve.");
}

// Auto-submit dos filtros
document.querySelectorAll("select[name=\"categoria\"], select[name=\"status\"]").forEach(select => {
    select.addEventListener("change", function() {
        this.form.submit();
    });
});

// Busca com Enter
document.querySelector("input[name=\"busca\"]").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        this.form.submit();
    }
});

// Auto-remover alertas após 5 segundos
setTimeout(function() {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(alert => {
        if (alert.classList.contains("show")) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);
</script>
';

include 'views/admin/layouts/app.php';
?>