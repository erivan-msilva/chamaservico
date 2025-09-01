<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado como admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tipo de Serviço - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        
        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border: none;
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <h1 class="h2 text-dark">
                        <i class="bi bi-pencil me-2"></i>
                        Editar Tipo de Serviço
                    </h1>
                    <div class="btn-toolbar">
                        <a href="/chamaservico/admin/tipos-servico" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Voltar à Lista
                        </a>
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

                <!-- Formulário de Edição -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="form-card">
                            <div class="card-body p-4">
                                <form method="POST" action="/chamaservico/admin/tipos-servico/editar?id=<?= $tipoServico['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="nome" class="form-label fw-bold">Nome do Serviço *</label>
                                            <input type="text" class="form-control form-control-lg" id="nome" name="nome" 
                                                   value="<?= htmlspecialchars($tipoServico['nome']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="categoria" class="form-label fw-bold">Categoria</label>
                                            <input type="text" class="form-control form-control-lg" id="categoria" name="categoria" 
                                                   value="<?= htmlspecialchars($tipoServico['categoria']) ?>" list="categorias">
                                            <datalist id="categorias">
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?= htmlspecialchars($categoria) ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label fw-bold">Descrição</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= htmlspecialchars($tipoServico['descricao']) ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="preco_medio" class="form-label fw-bold">Preço Médio (R$)</label>
                                            <input type="number" class="form-control form-control-lg" id="preco_medio" name="preco_medio" 
                                                   step="0.01" min="0" value="<?= $tipoServico['preco_medio'] ?>">
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex align-items-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                                       value="1" <?= $tipoServico['ativo'] ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-bold" for="ativo">
                                                    Ativo (disponível para solicitações)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-check me-1"></i>Salvar Alterações
                                        </button>
                                        <a href="/chamaservico/admin/tipos-servico" class="btn btn-outline-secondary btn-lg">
                                            Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
