<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
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
    <title>Visualizar Usuário - Admin</title>
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
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.2);
            object-fit: cover;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .activity-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid #007bff;
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
                            <a class="nav-link active" href="/chamaservico/admin/usuarios">
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
                            <a class="nav-link" href="/chamaservico/admin/propostas">
                                <i class="bi bi-file-text me-2"></i>
                                Propostas
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
                                <?= htmlspecialchars($_SESSION['admin_nome']) ?>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">
                        <i class="bi bi-person me-2"></i>
                        Perfil do Usuário
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/chamaservico/admin/usuarios" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>
                            Voltar
                        </a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary" onclick="enviarEmail()">
                                <i class="bi bi-envelope me-1"></i>
                                Enviar Email
                            </button>
                            <?php if ($usuario['ativo']): ?>
                                <button type="button" class="btn btn-outline-warning" onclick="alterarStatus(0)">
                                    <i class="bi bi-pause me-1"></i>
                                    Desativar
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-success" onclick="alterarStatus(1)">
                                    <i class="bi bi-play me-1"></i>
                                    Ativar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <?php
                            $fotoPerfil = $usuario['foto_perfil'] ?? '';
                            $fotoExiste = $fotoPerfil && file_exists("uploads/perfil/" . basename($fotoPerfil));
                            ?>
                            <?php if ($fotoExiste): ?>
                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($fotoPerfil)) ?>"
                                     class="profile-avatar" alt="Foto do usuário">
                            <?php else: ?>
                                <div class="profile-avatar bg-secondary d-flex align-items-center justify-content-center mx-auto">
                                    <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h2 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h2>
                            <p class="mb-2 opacity-75">
                                <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($usuario['email']) ?>
                            </p>
                            <?php if ($usuario['telefone']): ?>
                                <p class="mb-2 opacity-75">
                                    <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($usuario['telefone']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-light text-dark px-3 py-2">
                                    <i class="bi bi-person-badge me-1"></i><?= ucfirst($usuario['tipo']) ?>
                                </span>
                                <span class="badge <?= $usuario['ativo'] ? 'bg-success' : 'bg-danger' ?> px-3 py-2">
                                    <i class="bi bi-<?= $usuario['ativo'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                    <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                                <span class="badge bg-info px-3 py-2">
                                    <i class="bi bi-calendar me-1"></i>
                                    Desde <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card" style="border-left-color: #4e73df;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Solicitações
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= $estatisticas['solicitacoes'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-list-task fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card" style="border-left-color: #1cc88a;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Propostas
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= $estatisticas['propostas'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-text fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card" style="border-left-color: #36b9cc;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Endereços
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= $estatisticas['enderecos'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-geo-alt fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card" style="border-left-color: #f6c23e;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Avaliações
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?= $estatisticas['avaliacoes'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-star fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalhes e Atividades -->
                <div class="row">
                    <!-- Informações Detalhadas -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Informações Detalhadas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <strong>Nome Completo:</strong><br>
                                        <?= htmlspecialchars($usuario['nome']) ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>Email:</strong><br>
                                        <a href="mailto:<?= $usuario['email'] ?>"><?= htmlspecialchars($usuario['email']) ?></a>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>CPF:</strong><br>
                                        <?= $usuario['cpf'] ? htmlspecialchars($usuario['cpf']) : 'Não informado' ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>Telefone:</strong><br>
                                        <?= $usuario['telefone'] ? htmlspecialchars($usuario['telefone']) : 'Não informado' ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>Data de Nascimento:</strong><br>
                                        <?= $usuario['dt_nascimento'] ? date('d/m/Y', strtotime($usuario['dt_nascimento'])) : 'Não informado' ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>Último Acesso:</strong><br>
                                        <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca acessou' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Endereços -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    Endereços Cadastrados
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($enderecos)): ?>
                                    <?php foreach ($enderecos as $endereco): ?>
                                        <div class="mb-3 p-2 <?= $endereco['principal'] ? 'bg-light' : '' ?> rounded">
                                            <?php if ($endereco['principal']): ?>
                                                <span class="badge bg-primary mb-1">Principal</span>
                                            <?php endif; ?>
                                            <address class="mb-0 small">
                                                <?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?><br>
                                                <?= $endereco['complemento'] ? htmlspecialchars($endereco['complemento']) . '<br>' : '' ?>
                                                <?= htmlspecialchars($endereco['bairro']) ?> - <?= htmlspecialchars($endereco['cidade']) ?>/<?= htmlspecialchars($endereco['estado']) ?><br>
                                                CEP: <?= htmlspecialchars($endereco['cep']) ?>
                                            </address>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Nenhum endereço cadastrado.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas Solicitações -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Últimas Solicitações
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($solicitacoes)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Tipo</th>
                                                    <th>Status</th>
                                                    <th>Data</th>
                                                    <th>Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($solicitacao['titulo']) ?></td>
                                                        <td><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></td>
                                                        <td>
                                                            <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                                                <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                                        <td>
                                                            <?= $solicitacao['orcamento_estimado'] ? 'R$ ' . number_format($solicitacao['orcamento_estimado'], 2, ',', '.') : '-' ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Nenhuma solicitação encontrada.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function alterarStatus(novoStatus) {
            const acao = novoStatus ? 'ativar' : 'desativar';
            if (confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = novoStatus ? '/chamaservico/admin/usuarios/ativar' : '/chamaservico/admin/usuarios/desativar';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = <?= $usuario['id'] ?>;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function enviarEmail() {
            window.location.href = 'mailto:<?= $usuario['email'] ?>';
        }
    </script>
</body>
</html>
