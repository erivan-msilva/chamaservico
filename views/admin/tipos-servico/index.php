<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Definir variáveis padrão se não existirem
$tiposServico = $tiposServico ?? [];
$categorias = $categorias ?? [];
$stats = $stats ?? [
    'total' => 0,
    'ativos' => 0,
    'inativos' => 0,
    'total_categorias' => 0
];
$filtros = $filtros ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Serviços - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .stats-widget {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
            margin-bottom: 1.5rem;
        }

        .tipo-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: all 0.3s ease;
        }

        .tipo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-shield-check me-2"></i>
                            Admin Panel
                        </h4>
                        <p class="text-white-50 small">ChamaServiço</p>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/solicitacoes">
                                <i class="bi bi-list-task me-2"></i>
                                Solicitações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/chamaservico/admin/tipos-servico">
                                <i class="bi bi-tools me-2"></i>
                                Tipos de Serviços
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/relatorios">
                                <i class="bi bi-graph-up me-2"></i>
                                Relatórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/chamaservico/admin/configuracoes">
                                <i class="bi bi-gear me-2"></i>
                                Configurações
                            </a>
                        </li>
                    </ul>

                    <div class="mt-auto pt-4">
                        <div class="text-center">
                            <div class="text-white-50 small">
                                Logado como:
                            </div>
                            <div class="text-white fw-bold small">
                                <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin Sistema') ?>
                            </div>
                            <a href="/chamaservico/admin/logout" class="btn btn-outline-light btn-sm mt-2">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <h1 class="h2 text-dark">
                        <i class="bi bi-tools me-2"></i>
                        Gestão de Tipos de Serviços
                    </h1>

                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarTipo">
                                <i class="bi bi-plus-circle me-1"></i>
                                Novo Tipo
                            </button>
                        </div>
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
                                        <?= number_format($stats['total']) ?>
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
                                        Ativos
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($stats['ativos']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #dc3545;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                        Inativos
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($stats['inativos']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-x-circle fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-widget" style="border-left-color: #ffc107;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                        Categorias
                                    </div>
                                    <div class="h3 mb-0 fw-bold text-gray-800">
                                        <?= number_format($stats['total_categorias']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-tag fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="busca" id="busca"
                                    placeholder="Nome ou descrição..." value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
                            </div>

                            <div class="col-md-3">
                                <label for="categoria" class="form-label">Categoria</label>
                                <select class="form-select" name="categoria" id="categoria">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= htmlspecialchars($categoria) ?>"
                                            <?= ($filtros['categoria'] ?? '') == $categoria ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoria) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="ativo" class="form-label">Status</label>
                                <select class="form-select" name="ativo" id="ativo">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                                    <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                                <a href="/chamaservico/admin/tipos-servico" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Limpar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Tipos de Serviços -->
                <div class="content-area">
                    <?php if (empty($tiposServico)): ?>
                        <!-- Estado Vazio -->
                        <div class="text-center py-5">
                            <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Nenhum tipo de serviço encontrado</h4>
                            <p class="text-muted">
                                <?php if (!empty(array_filter($filtros))): ?>
                                    Tente ajustar os filtros de busca.
                                <?php else: ?>
                                    Comece criando o primeiro tipo de serviço.
                                <?php endif; ?>
                            </p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarTipo">
                                <i class="bi bi-plus-circle me-1"></i>
                                Criar Primeiro Tipo
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($tiposServico as $tipo): ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card tipo-card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="badge bg-<?= $tipo['ativo'] ? 'success' : 'danger' ?>">
                                                <?= $tipo['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item" onclick="editarTipo(<?= $tipo['id'] ?>)">
                                                            <i class="bi bi-pencil me-2"></i>Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" onclick="alterarStatus(<?= $tipo['id'] ?>, <?= $tipo['ativo'] ? 0 : 1 ?>)">
                                                            <i class="bi bi-<?= $tipo['ativo'] ? 'eye-slash' : 'eye' ?> me-2"></i>
                                                            <?= $tipo['ativo'] ? 'Desativar' : 'Ativar' ?>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" onclick="excluirTipo(<?= $tipo['id'] ?>)">
                                                            <i class="bi bi-trash me-2"></i>Excluir
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($tipo['nome']) ?></h5>

                                            <?php if (!empty($tipo['categoria'])): ?>
                                                <span class="badge bg-info mb-2"><?= htmlspecialchars($tipo['categoria']) ?></span>
                                            <?php endif; ?>

                                            <p class="card-text text-muted">
                                                <?= htmlspecialchars($tipo['descricao'] ?? 'Sem descrição') ?>
                                            </p>

                                            <?php if (!empty($tipo['preco_medio'])): ?>
                                                <div class="text-success small mb-2">
                                                    <i class="bi bi-cash-coin me-1"></i>
                                                    Preço médio: R$ <?= number_format($tipo['preco_medio'], 2, ',', '.') ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-list-check me-1"></i>
                                                    <?= $tipo['total_solicitacoes'] ?? 0 ?> solicitação<?= ($tipo['total_solicitacoes'] ?? 0) != 1 ? 'ões' : '' ?>
                                                </small>

                                                <?php if (($tipo['servicos_concluidos'] ?? 0) > 0): ?>
                                                    <br>
                                                    <small class="text-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        <?= $tipo['servicos_concluidos'] ?> concluído<?= $tipo['servicos_concluidos'] != 1 ? 's' : '' ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Informações de Resultado -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Mostrando <?= count($tiposServico) ?> tipo<?= count($tiposServico) != 1 ? 's' : '' ?> de serviço
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Criar/Editar Tipo -->
    <div class="modal fade" id="modalCriarTipo" tabindex="-1" aria-labelledby="modalCriarTipoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCriarTipoLabel">Novo Tipo de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="/chamaservico/admin/tipos-servico/criar" id="formTipo">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                        <input type="hidden" name="id" id="tipoId">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" name="nome" id="nome" required
                                placeholder="Ex: Limpeza Residencial">
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" id="descricao" rows="3"
                                placeholder="Descreva o tipo de serviço..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoria</label>
                                <input type="text" class="form-control" name="categoria" id="categoriaInput"
                                    placeholder="Ex: Limpeza, Elétrica">
                            </div>
                            <div class="col-md-6">
                                <label for="preco_medio" class="form-label">Preço Médio (R$)</label>
                                <input type="number" class="form-control" name="preco_medio" id="preco_medio"
                                    step="0.01" min="0" placeholder="0,00">
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Tipo de serviço ativo
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Tipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para editar tipo
        function editarTipo(id) {
            // Implementar busca e preenchimento do modal
            alert('Funcionalidade de edição será implementada em breve.');
        }

        // Função para alterar status
        function alterarStatus(id, novoStatus) {
            if (confirm('Confirma a alteração do status deste tipo de serviço?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/chamaservico/admin/tipos-servico/alterar-status';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= Session::generateCSRFToken() ?>';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'ativo';
                statusInput.value = novoStatus;

                form.appendChild(csrfInput);
                form.appendChild(idInput);
                form.appendChild(statusInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Função para excluir tipo
        function excluirTipo(id) {
            if (confirm('Tem certeza que deseja excluir este tipo de serviço?\n\nATENÇÃO: Esta ação não pode ser desfeita!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/chamaservico/admin/tipos-servico/excluir';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= Session::generateCSRFToken() ?>';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                form.appendChild(csrfInput);
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-submit dos filtros
        document.querySelectorAll('#categoria, #ativo').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Busca com Enter
        document.getElementById('busca').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>

</html>