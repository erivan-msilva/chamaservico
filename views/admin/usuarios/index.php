<?php
// Configuração do layout
$title = 'Gestão de Usuários - Admin';
$currentPage = 'usuarios';

// Buscar dados reais do banco de dados
try {
    require_once 'core/Database.php';
    $db = Database::getInstance();
    
    // Buscar estatísticas reais
    $sqlStats = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
            SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos,
            SUM(CASE WHEN DATE(data_cadastro) = CURDATE() THEN 1 ELSE 0 END) as novos_hoje,
            SUM(CASE WHEN tipo = 'prestador' THEN 1 ELSE 0 END) as prestadores,
            SUM(CASE WHEN tipo = 'ambos' THEN 1 ELSE 0 END) as ambos
        FROM tb_pessoa
    ";
    
    $stmt = $db->prepare($sqlStats);
    $stmt->execute();
    $stats = $stmt->fetch() ?: [
        'total' => 0,
        'ativos' => 0,
        'inativos' => 0,
        'novos_hoje' => 0,
        'prestadores' => 0,
        'ambos' => 0
    ];
    
    // Buscar usuários com filtros
    $filtros = [
        'busca' => $_GET['busca'] ?? '',
        'tipo' => $_GET['tipo'] ?? '',
        'ativo' => $_GET['ativo'] ?? '',
        'ordem' => $_GET['ordem'] ?? 'data_cadastro'
    ];
    
    $sqlUsuarios = "
        SELECT 
            p.*,
            (SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = p.id) as total_solicitacoes,
            (SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = p.id) as total_propostas
        FROM tb_pessoa p 
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($filtros['busca'])) {
        $sqlUsuarios .= " AND (p.nome LIKE ? OR p.email LIKE ?)";
        $termoBusca = '%' . $filtros['busca'] . '%';
        $params[] = $termoBusca;
        $params[] = $termoBusca;
    }
    
    if (!empty($filtros['tipo'])) {
        $sqlUsuarios .= " AND p.tipo = ?";
        $params[] = $filtros['tipo'];
    }
    
    if ($filtros['ativo'] !== '') {
        $sqlUsuarios .= " AND p.ativo = ?";
        $params[] = $filtros['ativo'];
    }
    
    // Ordenação
    $ordemValida = ['nome', 'data_cadastro', 'ultimo_acesso'];
    $ordem = in_array($filtros['ordem'], $ordemValida) ? $filtros['ordem'] : 'data_cadastro';
    $sqlUsuarios .= " ORDER BY p.{$ordem} DESC LIMIT 50";
    
    $stmt = $db->prepare($sqlUsuarios);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro ao buscar dados de usuários: " . $e->getMessage());
    $stats = [
        'total' => 0,
        'ativos' => 0,
        'inativos' => 0,
        'novos_hoje' => 0,
        'prestadores' => 0,
        'ambos' => 0
    ];
    $usuarios = [];
}

$tipoVisualizacao = $_GET['view'] ?? 'cards';

ob_start();
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <h1 class="h2 text-dark">
        <i class="bi bi-people me-2"></i>
        Gestão de Usuários
    </h1>
    
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportarUsuarios()">
                <i class="bi bi-download me-1"></i>
                Exportar
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="enviarEmailMassa()">
                <i class="bi bi-envelope me-1"></i>
                Email em Massa
            </button>
        </div>
        
        <!-- Controles de visualização -->
        <div class="view-toggle">
            <div class="btn-group" role="group">
                <a href="?view=cards&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                   class="btn btn-<?= $tipoVisualizacao === 'cards' ? 'primary' : 'outline-primary' ?> btn-sm">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Cards
                </a>
                <a href="?view=lista&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                   class="btn btn-<?= $tipoVisualizacao === 'lista' ? 'primary' : 'outline-primary' ?> btn-sm">
                    <i class="bi bi-list-ul me-1"></i>Lista
                </a>
                <a href="?view=timeline&<?= http_build_query(array_diff_key($_GET, ['view' => ''])) ?>" 
                   class="btn btn-<?= $tipoVisualizacao === 'timeline' ? 'primary' : 'outline-primary' ?> btn-sm">
                    <i class="bi bi-clock-history me-1"></i>Timeline
                </a>
            </div>
        </div>
        
        <button type="button" class="btn btn-primary ms-2" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Atualizar
        </button>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['admin_flash'])): ?>
    <?php $flash = $_SESSION['admin_flash']; unset($_SESSION['admin_flash']); ?>
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
                        Total de Usuários
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($stats['total']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-people fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                        Usuários Ativos
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($stats['ativos']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-person-check fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #17a2b8;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                        Prestadores
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($stats['prestadores'] + $stats['ambos']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-person-plus fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-widget" style="border-left-color: #ffc107;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                        Novos Hoje
                    </div>
                    <div class="h3 mb-0 fw-bold text-gray-800">
                        <?= number_format($stats['novos_hoje']) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-tools fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <input type="hidden" name="view" value="<?= htmlspecialchars($tipoVisualizacao) ?>">
            <div class="col-md-3">
                <label class="form-label fw-bold">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" 
                           placeholder="Nome ou email..." 
                           value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="cliente" <?= ($filtros['tipo'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="prestador" <?= ($filtros['tipo'] ?? '') === 'prestador' ? 'selected' : '' ?>>Prestador</option>
                    <option value="ambos" <?= ($filtros['tipo'] ?? '') === 'ambos' ? 'selected' : '' ?>>Ambos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Status</label>
                <select name="ativo" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Conteúdo dos Usuários -->
<div class="content-area">
    <?php if (empty($usuarios)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">Nenhum usuário encontrado</h4>
            <p class="text-muted">
                <?php if (!empty(array_filter($filtros))): ?>
                    Tente ajustar os filtros para ver mais usuários.
                <?php else: ?>
                    Não há usuários cadastrados no sistema ainda.
                <?php endif; ?>
            </p>
            <?php if (!empty(array_filter($filtros))): ?>
                <a href="<?= url('admin/usuarios') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <!-- Visualização CARDS -->
        <?php if ($tipoVisualizacao === 'cards'): ?>
            <div class="row">
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="user-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <?php
                                    $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                    $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                    ?>
                                    <?php if ($fotoExiste): ?>
                                        <img src="<?= url('uploads/perfil/' . htmlspecialchars(basename($fotoPerfil))) ?>"
                                             class="user-avatar me-3" alt="Avatar">
                                    <?php else: ?>
                                        <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-person text-white fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1"><?= htmlspecialchars($usuario['nome']) ?></h6>
                                        <p class="card-text text-muted small mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                                    </div>
                                
                                    <span class="status-badge badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </div>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary"><?= $usuario['total_solicitacoes'] ?></div>
                                        <small class="text-muted">Solicitações</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-success"><?= $usuario['total_propostas'] ?></div>
                                        <small class="text-muted">Propostas</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-info">
                                            <?= $usuario['ultimo_acesso'] ? date('d/m', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                                        </div>
                                        <small class="text-muted">Último Acesso</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="user-tipo tipo-<?= $usuario['tipo'] ?>">
                                        <?= ucfirst($usuario['tipo']) ?>
                                    </span>
                                    <small class="text-muted">
                                        Desde <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-1">
                                    <a href="<?= url('admin/usuarios/visualizar?id=' . $usuario['id']) ?>" 
                                       class="btn btn-outline-info btn-action flex-fill">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    
                                    <?php if ($usuario['ativo']): ?>
                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 0)" 
                                                class="btn btn-outline-warning btn-action flex-fill">
                                            <i class="bi bi-pause"></i> Inativar
                                        </button>
                                    <?php else: ?>
                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 1)" 
                                                class="btn btn-outline-success btn-action flex-fill">
                                            <i class="bi bi-play"></i> Ativar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-action dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="mailto:<?= $usuario['email'] ?>">
                                                <i class="bi bi-envelope me-2"></i>Enviar Email
                                            </a></li>
                                            <li><a class="dropdown-item" href="<?= url('admin/usuarios/visualizar?id=' . $usuario['id']) ?>">
                                                <i class="bi bi-info-circle me-2"></i>Detalhes
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        
        <!-- Visualização LISTA -->
        <?php elseif ($tipoVisualizacao === 'lista'): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Atividade</th>
                                    <th>Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                                $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                                ?>
                                                <?php if ($fotoExiste): ?>
                                                    <img src="<?= url('uploads/perfil/' . htmlspecialchars(basename($fotoPerfil))) ?>"
                                                         class="user-avatar-sm me-3" alt="Avatar">
                                                <?php else: ?>
                                                    <div class="user-avatar-sm bg-secondary d-flex align-items-center justify-content-center me-3">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($usuario['nome']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($usuario['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $usuario['tipo'] === 'cliente' ? 'primary' : ($usuario['tipo'] === 'prestador' ? 'success' : 'info') ?>">
                                                <?= ucfirst($usuario['tipo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <span class="badge bg-primary"><?= $usuario['total_solicitacoes'] ?> sol.</span>
                                                <span class="badge bg-success"><?= $usuario['total_propostas'] ?> prop.</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= url('admin/usuarios/visualizar?id=' . $usuario['id']) ?>" 
                                                   class="btn btn-outline-primary" title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-<?= $usuario['ativo'] ? 'warning' : 'success' ?>"
                                                        onclick="alterarStatus(<?= $usuario['id'] ?>, <?= $usuario['ativo'] ? 0 : 1 ?>)"
                                                        title="<?= $usuario['ativo'] ? 'Inativar' : 'Ativar' ?>">
                                                    <i class="bi bi-<?= $usuario['ativo'] ? 'pause' : 'play' ?>"></i>
                                                </button>
                                                <a href="mailto:<?= $usuario['email'] ?>" class="btn btn-outline-secondary" title="Enviar Email">
                                                    <i class="bi bi-envelope"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
        <!-- Visualização TIMELINE -->
        <?php elseif ($tipoVisualizacao === 'timeline'): ?>
            <div class="timeline-view">
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
                        </div>
                        
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php
                                $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                                ?>
                                <?php if ($fotoExiste): ?>
                                    <img src="<?= url('uploads/perfil/' . htmlspecialchars(basename($fotoPerfil))) ?>"
                                         class="user-avatar mx-auto d-block" alt="Avatar">
                                <?php else: ?>
                                    <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center mx-auto">
                                        <i class="bi bi-person text-white fs-4"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h5>
                                <p class="text-muted mb-2"><?= htmlspecialchars($usuario['email']) ?></p>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="user-tipo tipo-<?= $usuario['tipo'] ?>">
                                        <?= ucfirst($usuario['tipo']) ?>
                                    </span>
                                    <span class="status-badge badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </div>
                                <?php if ($usuario['ultimo_acesso']): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        Último acesso: <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="fw-bold text-primary"><?= $usuario['total_solicitacoes'] ?></div>
                                            <small class="text-muted">Solicitações</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold text-success"><?= $usuario['total_propostas'] ?></div>
                                            <small class="text-muted">Propostas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex flex-column gap-1">
                                    <a href="<?= url('admin/usuarios/visualizar?id=' . $usuario['id']) ?>" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-eye me-1"></i>Visualizar
                                    </a>
                                    
                                    <?php if ($usuario['ativo']): ?>
                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 0)" 
                                                class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pause me-1"></i>Inativar
                                        </button>
                                    <?php else: ?>
                                        <button onclick="alterarStatus(<?= $usuario['id'] ?>, 1)" 
                                                class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-play me-1"></i>Ativar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Informações da listagem -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                Mostrando <?= count($usuarios) ?> usuário<?= count($usuarios) != 1 ? 's' : '' ?> de <?= $stats['total'] ?>
            </small>
            
            <div class="d-flex gap-2">
                <small class="text-muted align-self-center">
                    Última atualização: <?= date('d/m/Y H:i') ?>
                </small>
            </div>
        </div>
    <?php endif; ?>
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
.user-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: none;
    transition: all 0.3s ease;
}
.user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}
.user-avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
.status-badge {
    font-size: 0.75rem;
    padding: 4px 8px;
}
.btn-action {
    padding: 4px 8px;
    font-size: 0.75rem;
    border-radius: 6px;
}
.user-tipo {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
}
.tipo-cliente { background: #e3f2fd; color: #1976d2; }
.tipo-prestador { background: #e8f5e8; color: #388e3c; }
.tipo-ambos { background: #fff3e0; color: #f57c00; }
.view-toggle {
    background: white;
    border-radius: 8px;
    padding: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.view-toggle .btn {
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 0.875rem;
}
.timeline-view {
    position: relative;
    padding-left: 2rem;
}
.timeline-view::before {
    content: "";
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}
.timeline-item {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-left: 1rem;
}
.timeline-item::before {
    content: "";
    position: absolute;
    left: -1.75rem;
    top: 1.5rem;
    width: 12px;
    height: 12px;
    background: #667eea;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #667eea;
}
.timeline-date {
    position: absolute;
    left: -8rem;
    top: 1rem;
    background: #667eea;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}
';

// Scripts específicos da página
$scripts = '
<script>
function alterarStatus(userId, novoStatus) {
    const acao = novoStatus ? "ativar" : "desativar";
    const modal = new bootstrap.Modal(document.getElementById("modalConfirmacao"));
    
    document.getElementById("modalTitulo").textContent = novoStatus ? "Ativar Usuário" : "Desativar Usuário";
    document.getElementById("modalMensagem").textContent = `Tem certeza que deseja ${acao} este usuário?`;
    
    const btnConfirmar = document.getElementById("btnConfirmar");
    btnConfirmar.className = `btn btn-${novoStatus ? "success" : "warning"}`;
    btnConfirmar.textContent = novoStatus ? "Ativar" : "Desativar";
    
    btnConfirmar.onclick = function() {
        fetch("' . url('admin/usuarios/alterar-status') . '", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                id: userId,
                status: novoStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Erro ao alterar status do usuário");
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            alert("Erro ao alterar status do usuário");
        });
        
        modal.hide();
    };
    
    modal.show();
}

function exportarUsuarios() {
    window.open("' . url('admin/api/exportar-usuarios') . '", "_blank");
}

function enviarEmailMassa() {
    alert("Funcionalidade de email em massa será implementada em breve.");
}

// Auto-submit dos filtros
document.querySelectorAll("select[name=\"tipo\"], select[name=\"ativo\"], select[name=\"ordem\"]").forEach(select => {
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
