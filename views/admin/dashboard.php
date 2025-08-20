<?php
//session_start();

 // Verificar se o usuário está logado como admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Chama  Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            width: 250px;
            transition: all 0.3s;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s;
            border-radius: 8px;
            margin: 0.25rem 0.5rem;
        }
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .content-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Modal para criar usuário admin -->
    <div class="modal fade" id="modalCriarAdmin" tabindex="-1" aria-labelledby="modalCriarAdminLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCriarAdminLabel">
                        <i class="bi bi-shield-plus me-2"></i>Criar Usuário Administrador
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCriarAdmin">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Atenção:</strong> Usuários administradores têm acesso total ao sistema.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminNome" class="form-label">
                                        <i class="bi bi-person me-1"></i>Nome Completo
                                    </label>
                                    <input type="text" class="form-control" id="adminNome" name="nome" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminEmail" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>E-mail
                                    </label>
                                    <input type="email" class="form-control" id="adminEmail" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminSenha" class="form-label">
                                        <i class="bi bi-lock me-1"></i>Senha
                                    </label>
                                    <input type="password" class="form-control" id="adminSenha" name="senha" required minlength="6">
                                    <div class="form-text">Mínimo de 6 caracteres</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminConfirmarSenha" class="form-label">
                                        <i class="bi bi-lock-fill me-1"></i>Confirmar Senha
                                    </label>
                                    <input type="password" class="form-control" id="adminConfirmarSenha" name="confirmar_senha" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminTelefone" class="form-label">
                                        <i class="bi bi-telephone me-1"></i>Telefone
                                    </label>
                                    <input type="tel" class="form-control" id="adminTelefone" name="telefone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adminNivel" class="form-label">
                                        <i class="bi bi-shield me-1"></i>Nível de Acesso
                                    </label>
                                    <select class="form-select" id="adminNivel" name="nivel_admin">
                                        <option value="admin">Administrador</option>
                                        <option value="super_admin">Super Administrador</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="adminAtivo" name="ativo" checked>
                                <label class="form-check-label" for="adminAtivo">
                                    Usuário ativo
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adminObservacoes" class="form-label">
                                <i class="bi bi-chat-left-text me-1"></i>Observações
                            </label>
                            <textarea class="form-control" id="adminObservacoes" name="observacoes" rows="3" placeholder="Informações adicionais sobre o administrador..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Criar Administrador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="logoutModalLabel"><i class="bi bi-box-arrow-right"></i> Sair do Sistema</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body text-center">
            <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
            <p class="mb-0 fs-5">Tem certeza que deseja <strong>sair do sistema</strong>?</p>
            <small class="text-muted">Sua sessão será encerrada imediatamente.</small>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmLogout">
              <i class="bi bi-box-arrow-right"></i> Sair Agora
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4 p-3">
                    <h4 class="text-white">Admin Panel</h4>
                    <small class="text-white-50">ChamaServiço</small>
                    <hr class="text-white-50">
                    <div class="d-flex align-items-center text-white-50">
                        <i class="bi bi-person-circle me-2"></i>
                        <small>Olá, <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?></small>
                    </div>
                    
                    <!-- Botão para criar admin -->
                    <button class="btn btn-outline-light btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCriarAdmin">
                        <i class="bi bi-shield-plus me-1"></i>Novo Admin
                    </button>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="carregarConteudo('dashboard')">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="carregarConteudo('tipos-servico')">
                            <i class="bi bi-gear me-2"></i> Tipos de Serviço
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="carregarConteudo('status-solicitacao')">
                            <i class="bi bi-flag me-2"></i> Status Solicitação
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="carregarConteudo('usuarios')">
                            <i class="bi bi-people me-2"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="carregarConteudo('relatorios')">
                            <i class="bi bi-graph-up me-2"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="carregarConteudo('monitor')">
                            <i class="bi bi-activity me-2"></i> Monitor
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="/chamaservico/admin/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2" id="page-title">Dashboard Administrativo</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/chamaservico/admin/dashboard">Admin</a></li>
                                <li class="breadcrumb-item active" id="breadcrumb-current">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" onclick="atualizarDados()">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="logout()">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row mb-4" id="stats-cards">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase mb-0 text-white-50">Total Usuários</h6>
                                        <span class="h2 mb-0" id="total-usuarios">0</span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase mb-0 text-white-50">Clientes</h6>
                                        <span class="h2 mb-0" id="total-clientes">0</span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase mb-0 text-white-50">Prestadores</h6>
                                        <span class="h2 mb-0" id="total-prestadores">0</span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-tools fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase mb-0 text-white-50">Solicitações Hoje</h6>
                                        <span class="h2 mb-0" id="solicitacoes-hoje">0</span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-file-text fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo dinâmico -->
                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card content-card">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">Atividade Recente</h5>
                                </div>
                                <div class="card-body">
                                    <div id="atividades-recentes">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="bi bi-person-plus text-white"></i>
                                            </div>
                                            <div>
                                                <strong>Novo usuário cadastrado</strong>
                                                <br><small class="text-muted">2 minutos atrás</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="bi bi-file-plus text-white"></i>
                                            </div>
                                            <div>
                                                <strong>Nova solicitação criada</strong>
                                                <br><small class="text-muted">15 minutos atrás</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="bi bi-star text-white"></i>
                                            </div>
                                            <div>
                                                <strong>Nova avaliação recebida</strong>
                                                <br><small class="text-muted">1 hora atrás</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card content-card">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">Status do Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Sistema</span>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Banco de Dados</span>
                                        <span class="badge bg-success">Conectado</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Usuários Ativos</span>
                                        <span class="badge bg-info" id="usuarios-ativos">0</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Última Atualização</span>
                                        <small class="text-muted" id="ultima-atualizacao"><?= date('H:i:s') ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script>
        let currentPage = 'dashboard';

        // Carregar dados do dashboard
        function carregarDashboard() {
            // Mostrar dashboard inicial
            document.getElementById('main-content').innerHTML = `
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card content-card">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">Atividade Recente</h5>
                            </div>
                            <div class="card-body">
                                <div id="atividades-recentes">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary rounded-circle p-2 me-3">
                                            <i class="bi bi-person-plus text-white"></i>
                                        </div>
                                        <div>
                                            <strong>Novo usuário cadastrado</strong>
                                            <br><small class="text-muted">2 minutos atrás</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success rounded-circle p-2 me-3">
                                            <i class="bi bi-file-plus text-white"></i>
                                        </div>
                                        <div>
                                            <strong>Nova solicitação criada</strong>
                                            <br><small class="text-muted">15 minutos atrás</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning rounded-circle p-2 me-3">
                                            <i class="bi bi-star text-white"></i>
                                        </div>
                                        <div>
                                            <strong>Nova avaliação recebida</strong>
                                            <br><small class="text-muted">1 hora atrás</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card content-card">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">Status do Sistema</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Sistema</span>
                                    <span class="badge bg-success">Online</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Banco de Dados</span>
                                    <span class="badge bg-success">Conectado</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Usuários Ativos</span>
                                    <span class="badge bg-info" id="usuarios-ativos">0</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Última Atualização</span>
                                    <small class="text-muted" id="ultima-atualizacao"><?= date('H:i:s') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            fetch('/chamaservico/admin/api/dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        atualizarEstatisticas(data.dados);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar dashboard:', error);
                    // Usar dados mockados para demonstração
                    atualizarEstatisticas({
                        total_usuarios: 150,
                        total_clientes: 95,
                        total_prestadores: 55,
                        solicitacoes_hoje: 12,
                        usuarios_ativos: 23
                    });
                });
        }

        function atualizarEstatisticas(dados) {
            document.getElementById('total-usuarios').textContent = dados.total_usuarios || 0;
            document.getElementById('total-clientes').textContent = dados.total_clientes || 0;
            document.getElementById('total-prestadores').textContent = dados.total_prestadores || 0;
            document.getElementById('solicitacoes-hoje').textContent = dados.solicitacoes_hoje || 0;
            document.getElementById('usuarios-ativos').textContent = dados.usuarios_ativos || 0;
            document.getElementById('ultima-atualizacao').textContent = new Date().toLocaleTimeString('pt-BR');
        }

        function carregarConteudo(pagina) {
            // Atualizar navegação ativa
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');

            currentPage = pagina;
            
            // Atualizar breadcrumb
            const titles = {
                'dashboard': 'Dashboard',
                'tipos-servico': 'Tipos de Serviço',
                'status-solicitacao': 'Status de Solicitação',
                'usuarios': 'Usuários',
                'relatorios': 'Relatórios',
                'monitor': 'Monitor do Sistema'
            };
            
            document.getElementById('page-title').textContent = titles[pagina] || 'Dashboard';
            document.getElementById('breadcrumb-current').textContent = titles[pagina] || 'Dashboard';
            
            switch(pagina) {
                case 'dashboard':
                    carregarDashboard();
                    break;
                case 'tipos-servico':
                    carregarTiposServico();
                    break;
                case 'status-solicitacao':
                    carregarStatusSolicitacao();
                    break;
                case 'usuarios':
                    carregarUsuarios();
                    break;
                case 'relatorios':
                    carregarRelatorios();
                    break;
                case 'monitor':
                    carregarMonitor();
                    break;
            }
        }

        function carregarTiposServico() {
            document.getElementById('main-content').innerHTML = `
                <div class="card content-card">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gerenciar Tipos de Serviço</h5>
                        <button class="btn btn-primary" onclick="novoTipoServico()">
                            <i class="bi bi-plus"></i> Novo Tipo
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Funcionalidade em desenvolvimento...</p>
                    </div>
                </div>
            `;
        }

        function carregarStatusSolicitacao() {
            document.getElementById('page-title').textContent = 'Status de Solicitação';
            document.getElementById('stats-cards').innerHTML = '';
            
            // Carregar estatísticas primeiro
            fetch('/chamaservico/admin/api/dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarEstatisticasStatus(data.dados);
                    }
                })
                .catch(error => console.error('Erro:', error));
            
            document.getElementById('main-content').innerHTML = `
                <div class="card content-card">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gerenciar Status de Solicitação</h5>
                        <button class="btn btn-primary" onclick="novoStatus()">
                            <i class="bi bi-plus"></i> Novo Status
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Funcionalidade em desenvolvimento...</p>
                    </div>
                </div>
            `;
        }

        function carregarUsuarios() {
            document.getElementById('page-title').textContent = 'Gerenciar Usuários';
            document.getElementById('stats-cards').innerHTML = '';
            
            // Carregar estatísticas primeiro
            fetch('/chamaservico/admin/api/dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarEstatisticasUsuarios(data.dados);
                    }
                })
                .catch(error => console.error('Erro:', error));
            
            document.getElementById('main-content').innerHTML = `
                <div class="card content-card">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Gerenciar Usuários</h5>
                        <div>
                            <button class="btn btn-outline-success btn-sm me-2" onclick="exportarUsuarios()">
                                <i class="bi bi-file-earmark-excel me-1"></i>Exportar
                            </button>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCriarAdmin">
                                <i class="bi bi-shield-plus me-1"></i>Novo Admin
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="filtroNome" placeholder="Filtrar por nome...">
                            </div>
                            <div class="col-md-3">
                                <input type="email" class="form-control" id="filtroEmail" placeholder="Filtrar por e-mail...">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos os tipos</option>
                                    <option value="admin">Admin</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="prestador">Prestador</option>
                                    <option value="ambos">Cliente/Prestador</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="filtroStatus">
                                    <option value="">Todos os status</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" onclick="filtrarUsuarios()">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                        
                        <div id="usuarios-lista">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando usuários...</span>
                                </div>
                                <p class="mt-2 text-muted">Carregando usuários...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Carregar lista de usuários
            filtrarUsuarios();
        }

        function filtrarUsuarios() {
            const filtros = {
                nome: document.getElementById('filtroNome')?.value || '',
                email: document.getElementById('filtroEmail')?.value || '',
                tipo: document.getElementById('filtroTipo')?.value || '',
                status: document.getElementById('filtroStatus')?.value || ''
            };
            
            const params = new URLSearchParams(filtros);
            
            fetch(`/chamaservico/admin/api/usuarios?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarListaUsuarios(data.dados.usuarios);
                    } else {
                        document.getElementById('usuarios-lista').innerHTML = 
                            '<div class="alert alert-danger">Erro ao carregar usuários: ' + (data.erro || 'Erro desconhecido') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('usuarios-lista').innerHTML = 
                        '<div class="alert alert-danger">Erro ao carregar usuários.</div>';
                });
        }

        function renderizarListaUsuarios(usuarios) {
            const container = document.getElementById('usuarios-lista');
            
            if (usuarios.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted">Nenhum usuário encontrado.</p></div>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Último Acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            usuarios.forEach(usuario => {
                const statusBadge = usuario.ativo == 1 
                    ? '<span class="badge bg-success">Ativo</span>' 
                    : '<span class="badge bg-danger">Inativo</span>';
                
                const tipoBadge = {
                    'admin': '<span class="badge bg-dark"><i class="bi bi-shield"></i> Admin</span>',
                    'super_admin': '<span class="badge bg-danger"><i class="bi bi-shield-fill"></i> Super Admin</span>',
                    'cliente': '<span class="badge bg-primary">Cliente</span>',
                    'prestador': '<span class="badge bg-warning">Prestador</span>',
                    'ambos': '<span class="badge bg-info">Ambos</span>'
                }[usuario.tipo] || '<span class="badge bg-secondary">Indefinido</span>';

                const dataFormatada = usuario.data_cadastro 
                    ? new Date(usuario.data_cadastro).toLocaleDateString('pt-BR')
                    : '-';
                
                const ultimoAcesso = usuario.ultimo_acesso 
                    ? new Date(usuario.ultimo_acesso).toLocaleDateString('pt-BR')
                    : 'Nunca';

                html += `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                        ${usuario.nome.charAt(0).toUpperCase()}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${usuario.nome}</div>
                                    ${usuario.telefone ? '<small class="text-muted">' + usuario.telefone + '</small>' : ''}
                                </div>
                            </div>
                        </td>
                        <td>${usuario.email}</td>
                        <td>${tipoBadge}</td>
                        <td>${statusBadge}</td>
                        <td>${dataFormatada}</td>
                        <td>${ultimoAcesso}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editarUsuario(${usuario.id})" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-${usuario.ativo == 1 ? 'warning' : 'success'}" 
                                        onclick="toggleStatusUsuario(${usuario.id})" 
                                        title="${usuario.ativo == 1 ? 'Desativar' : 'Ativar'}">
                                    <i class="bi bi-${usuario.ativo == 1 ? 'pause' : 'play'}"></i>
                                </button>
                                ${usuario.tipo !== 'super_admin' ? `
                                <button class="btn btn-outline-danger" onclick="deletarUsuario(${usuario.id})" title="Deletar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        function renderizarEstatisticasUsuarios(dados) {
            const statsCards = document.getElementById('stats-cards');
            
            statsCards.innerHTML = `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0 text-white-50">Total Usuários</h6>
                                    <span class="h2 mb-0">${dados.total_usuarios || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fs-1 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0 text-white-50">Usuários Ativos</h6>
                                    <span class="h2 mb-0">${dados.usuarios_ativos || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-check fs-1 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0 text-white-50">Administradores</h6>
                                    <span class="h2 mb-0">${dados.total_admins || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-shield fs-1 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0 text-white-50">Cadastros Hoje</h6>
                                    <span class="h2 mb-0">${dados.cadastros_hoje || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-plus fs-1 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function toggleStatusUsuario(id) {
            Swal.fire({
                title: 'Alterar Status',
                text: "Tem certeza que deseja alterar o status deste usuário?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, alterar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/chamaservico/admin/toggle-status-usuario', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire('Sucesso!', data.mensagem, 'success');
                            filtrarUsuarios();
                            carregarDashboard(); // Atualizar estatísticas
                        } else {
                            Swal.fire('Erro!', data.mensagem, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        Swal.fire('Erro!', 'Erro ao alterar status do usuário', 'error');
                    });
                }
            });
        }

        function editarUsuario(id) {
            // Implementar edição de usuário
            Swal.fire('Info', 'Funcionalidade de edição em desenvolvimento', 'info');
        }

        function deletarUsuario(id) {
            Swal.fire({
                title: 'Deletar Usuário',
                text: "Esta ação não pode ser desfeita! Tem certeza?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Info', 'Funcionalidade de deleção em desenvolvimento', 'info');
                }
            });
        }

        function exportarUsuarios() {
            Swal.fire('Info', 'Funcionalidade de exportação em desenvolvimento', 'info');
        }

        // Função para criar usuário admin
        document.getElementById('formCriarAdmin').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const senha = document.getElementById('adminSenha').value;
            const confirmarSenha = document.getElementById('adminConfirmarSenha').value;
            
            // Validar senhas
            if (senha !== confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'As senhas não coincidem.',
                    confirmButtonColor: '#764ba2'
                });
                return;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Criando...';
            submitBtn.disabled = true;
            
            fetch('/chamaservico/admin/api/criar-admin', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Usuário administrador criado com sucesso!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Fechar modal e limpar formulário
                    bootstrap.Modal.getInstance(document.getElementById('modalCriarAdmin')).hide();
                    this.reset();
                    
                    // Atualizar estatísticas se estivermos na página de usuários
                    if (currentPage === 'usuarios') {
                        carregarUsuarios();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.mensagem || 'Erro ao criar usuário administrador.',
                        confirmButtonColor: '#764ba2'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão. Tente novamente.',
                    confirmButtonColor: '#764ba2'
                });
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Função para logout com confirmação
        function logout() {
            Swal.fire({
                title: 'Sair do Sistema?',
                text: "Tem certeza que deseja encerrar sua sessão?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, sair!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/chamaservico/admin/logout';
                }
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            carregarDashboard();
            
            // Atualizar dados a cada 30 segundos
            setInterval(carregarDashboard, 30000);
        });
    </script>
</body>
</html>
        }

        function renderizarGraficoDistribuicao(dados, tipo) {
            const ctx = document.getElementById('chartDistribuicao');
            if (!ctx) return;
            
            if (chartDistribuicao) {
                chartDistribuicao.destroy();
            }
            
            if (!dados || dados.length === 0) {
                ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
                return;
            }
            
            chartDistribuicao = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dados.map(d => d.categoria || d.nome || 'Sem categoria'),
                    datasets: [{
                        data: dados.map(d => d.valor || d.quantidade || 0),
                        backgroundColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#f5576c',
                            '#4facfe',
                            '#00f2fe'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function renderizarTabelaDetalhada(dados, tipo) {
            if (!dados || dados.length === 0) {
                document.getElementById('relatorio-tabela').innerHTML = '<p class="text-center text-muted">Nenhum dado detalhado disponível.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-striped table-hover"><thead class="table-dark"><tr>';
            
            // Cabeçalhos da tabela baseados no tipo
            const headers = {
                usuarios: ['ID', 'Nome', 'Email', 'Tipo', 'Data Cadastro', 'Último Acesso', 'Status'],
                solicitacoes: ['ID', 'Título', 'Cliente', 'Tipo Serviço', 'Valor', 'Status', 'Data'],
                propostas: ['ID', 'Solicitação', 'Prestador', 'Valor', 'Status', 'Data'],
                avaliacoes: ['ID', 'Solicitação', 'Avaliador', 'Nota', 'Data'],
                geral: ['Item', 'Valor', 'Descrição']
            };

            (headers[tipo] || headers.geral).forEach(header => {
                html += `<th>${header}</th>`;
            });
            html += '</tr></thead><tbody>';

            dados.slice(0, 100).forEach(item => { // Limitar a 100 itens para performance
                html += '<tr>';
                Object.values(item).forEach(value => {
                    html += `<td>${value || '-'}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            
            if (dados.length > 100) {
                html += `<p class="text-muted text-center">Mostrando primeiros 100 de ${dados.length} registros. Use a exportação para ver todos.</p>`;
            }

            document.getElementById('relatorio-tabela').innerHTML = html;
        }

        function gerarInsights(dados, tipo) {
            let insights = [];
            
            switch(tipo) {
                case 'usuarios':
                    if (dados.crescimento_percentual && dados.crescimento_percentual > 10) {
                        insights.push('📈 <strong>Crescimento Acelerado:</strong> O crescimento de usuários está  10% acima do normal. Continue investindo em marketing!');
                    }
                    if (dados.total_usuarios && dados.usuarios_ativos && (dados.usuarios_ativos / dados.total_usuarios) < 0.7) {
                        insights.push('⚠️ <strong>Baixo Engajamento:</strong> Menos de 70% dos usuários estão ativos. Considere campanhas de reativação.');
                    }
                    if (dados.total_usuarios && dados.total_prestadores && (dados.total_prestadores / dados.total_usuarios) < 0.3) {
                        insights.push('👷 <strong>Necessário mais prestadores:</strong> A proporção de prestadores está baixa. Foque no recrutamento.');
                    }
                    break;
                    
                case 'solicitacoes':
                    if (dados.tempo_medio_resposta && dados.tempo_medio_resposta > 24) {
                        insights.push('⏰ <strong>Tempo de resposta alto:</strong> Solicitações demoram mais de 24h para receber propostas. Melhore a notificação para prestadores.');
                    }
                    if (dados.total_solicitacoes && dados.solicitacoes_concluidas && (dados.solicitacoes_concluidas / dados.total_solicitacoes) > 0.8) {
                        insights.push('✅ <strong>Excelente taxa de conclusão:</strong> Mais de 80% das solicitações são concluídas com sucesso!');
                    }
                    break;
            }
            
            if (insights.length === 0) {
                insights.push('📊 <strong>Desempenho estável:</strong> Os indicadores estão dentro do esperado. Continue monitorando as métricas.');
            }
            
            document.getElementById('relatorio-insights').innerHTML = insights.map(insight => 
                `<div class="alert alert-info border-0 mb-2">${insight}</div>`
            ).join('');
        }

        function alterarTipoGrafico(tipo) {
            if (chartTemporal) {
                chartTemporal.config.type = tipo;
                chartTemporal.update();
                
                // Atualizar botões ativos
                document.querySelectorAll('.btn-group button').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');
            }
        }

        function exportarRelatorio(formato) {
            const tipo = document.getElementById('relatorioTipo').value;
            const periodo = document.getElementById('relatorioPeriodo').value;
            
            const params = new URLSearchParams({
                acao: 'exportar_relatorio',
                formato,
                tipo,
                periodo,
                data_inicio: document.getElementById('relatorioDataInicio').value,
                data_fim: document.getElementById('relatorioDataFim').value
            });

            // Criar link de download
            const link = document.createElement('a');
            link.href = `../controllers/AdminController.class.php?${params}`;
            link.download = `relatorio_${tipo}_${new Date().toISOString().split('T')[0]}.${formato}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function imprimirRelatorio() {
            const conteudo = document.getElementById('content-body').innerHTML;
            const janela = window.open('', '_blank');
            janela.document.write(`
                <html>
                    <head>
                        <title>Relatório - ${document.getElementById('relatorioTipo').value}</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @media print {
                                .btn, .card-header .btn-group { display: none !important; }
                                canvas { max-height: 400px !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container-fluid">
                            <h1>Relatório - ${document.getElementById('relatorioTipo').value}</h1>
                            <hr>
                            ${conteudo}
                        </div>
                    </body>
                </html>
            `);
            janela.document.close();
            janela.print();
        }

        function limparFiltroRelatorio() {
            document.getElementById('formFiltroRelatorio').reset();
            document.getElementById('dataInicioContainer').style.display = 'none';
            document.getElementById('dataFimContainer').style.display = 'none';
            gerarRelatorio();
        }



        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            carregarDashboard();
            
            // Atualizar dados a cada 30 segundos
            setInterval(carregarDashboard, 30000);
        });
    </script>
</body>
</html>
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeServico').value);
                    formData.append('categoria', document.getElementById('categoriaServico').value);
                    formData.append('preco_medio', document.getElementById('precoMedioServico').value);
                    formData.append('ativo', document.getElementById('ativoServico').value);
                    formData.append('descricao', document.getElementById('descricaoServico').value);

                    // Remover: formData.append('icone', ...);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_tipo_servico&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_tipo_servico';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: data.mensagem,
                                showConfirmButton: false,
                                timer: 1800
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalTipoServico')).hide();
                            listarTiposServico();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao processar solicitação',
                            confirmButtonColor: '#764ba2'
                        });
                    });
                }

                // Form submit para status
                if (e.target.id === 'formStatus') {
                    e.preventDefault();
                    
                    const id = document.getElementById('statusId').value;
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeStatus').value);
                    formData.append('descricao', document.getElementById('descricaoStatus').value);
                    formData.append('cor', document.getElementById('corStatus').value);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_status_solicitacao&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_status_solicitacao';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            alert(data.mensagem);
                            bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide();
                            listarStatus();
                            carregarStatusSolicitacao(); // Recarregar estatísticas
                        } else {
                            alert('Erro: ' + data.mensagem);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar solicitação');
                    });
                }
            });
        });
    </script>
</body>
</html>
                acao: 'exportar_relatorio',
                formato,
                tipo,
                periodo,
                data_inicio: document.getElementById('relatorioDataInicio').value,
                data_fim: document.getElementById('relatorioDataFim').value
            });

            // Criar link de download
            const link = document.createElement('a');
            link.href = `../controllers/AdminController.class.php?${params}`;
            link.download = `relatorio_${tipo}_${new Date().toISOString().split('T')[0]}.${formato}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function imprimirRelatorio() {
            const conteudo = document.getElementById('content-body').innerHTML;
            const janela = window.open('', '_blank');
            janela.document.write(`
                <html>
                    <head>
                        <title>Relatório - ${document.getElementById('relatorioTipo').value}</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @media print {
                                .btn, .card-header .btn-group { display: none !important; }
                                canvas { max-height: 400px !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container-fluid">
                            <h1>Relatório - ${document.getElementById('relatorioTipo').value}</h1>
                            <hr>
                            ${conteudo}
                        </div>
                    </body>
                </html>
            `);
            janela.document.close();
            janela.print();
        }

        function limparFiltroRelatorio() {
            document.getElementById('formFiltroRelatorio').reset();
            document.getElementById('dataInicioContainer').style.display = 'none';
            document.getElementById('dataFimContainer').style.display = 'none';
            gerarRelatorio();
        }



        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            carregarDashboard();

            // Form submit para tipo de serviço (remover qualquer referência a 'icone')
            document.addEventListener('submit', function(e) {
                if (e.target.id === 'formTipoServico') {
                    e.preventDefault();
                    const id = document.getElementById('tipoServicoId').value;
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeServico').value);
                    formData.append('categoria', document.getElementById('categoriaServico').value);
                    formData.append('preco_medio', document.getElementById('precoMedioServico').value);
                    formData.append('ativo', document.getElementById('ativoServico').value);
                    formData.append('descricao', document.getElementById('descricaoServico').value);

                    // Remover: formData.append('icone', ...);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_tipo_servico&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_tipo_servico';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: data.mensagem,
                                showConfirmButton: false,
                                timer: 1800
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalTipoServico')).hide();
                            listarTiposServico();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao processar solicitação',
                            confirmButtonColor: '#764ba2'
                        });
                    });
                }

                // Form submit para status
                if (e.target.id === 'formStatus') {
                    e.preventDefault();
                    
                    const id = document.getElementById('statusId').value;
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeStatus').value);
                    formData.append('descricao', document.getElementById('descricaoStatus').value);
                    formData.append('cor', document.getElementById('corStatus').value);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_status_solicitacao&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_status_solicitacao';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            alert(data.mensagem);
                            bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide();
                            listarStatus();
                            carregarStatusSolicitacao(); // Recarregar estatísticas
                        } else {
                            alert('Erro: ' + data.mensagem);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar solicitação');
                    });
                }
            });
        });
    </script>
</body>
</html>
                <!-- Gráficos e Análises -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h5><i class="bi bi-graph-up"></i> Análise Temporal</h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="alterarTipoGrafico('line')">Linha</button>
                                    <button class="btn btn-outline-primary" onclick="alterarTipoGrafico('bar')">Barra</button>
                                    <button class="btn btn-outline-primary" onclick="alterarTipoGrafico('area')">Área</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTemporal"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-pie-chart"></i> Distribuição</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDistribuicao"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabelas Detalhadas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-table"></i> Dados Detalhados</h5>
                            </div>
                            <div class="card-body">
                                <div id="relatorio-tabela">
                                    <!-- Tabela será carregada aqui -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights e Recomendações -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5><i class="bi bi-lightbulb"></i> Insights e Recomendações</h5>
                            </div>
                            <div class="card-body" id="relatorio-insights">
                                <!-- Insights serão carregados aqui -->
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Event listeners
            document.getElementById('formFiltroRelatorio').onsubmit = function(e) {
                e.preventDefault();
                gerarRelatorio();
            };

            // Carregar relatório inicial
            gerarRelatorio();
        }

        let chartTemporal = null;
        let chartDistribuicao = null;

        function setPeriodoPersonalizado() {
            const periodo = document.getElementById('relatorioPeriodo').value;
            const dataInicioContainer = document.getElementById('dataInicioContainer');
            const dataFimContainer = document.getElementById('dataFimContainer');
            
            if (periodo === 'personalizado') {
                dataInicioContainer.style.display = 'block';
                dataFimContainer.style.display = 'block';
            } else {
                dataInicioContainer.style.display = 'none';
                dataFimContainer.style.display = 'none';
                
                // Definir datas automaticamente baseado no período
                const hoje = new Date();
                let dataInicio = new Date();
                
                switch(periodo) {
                    case 'hoje':
                        dataInicio = hoje;
                        break;
                    case 'ontem':
                        dataInicio.setDate(hoje.getDate() - 1);
                        break;
                    case 'ultima_semana':
                        dataInicio.setDate(hoje.getDate() - 7);
                        break;
                    case 'ultimo_mes':
                        dataInicio.setMonth(hoje.getMonth() - 1);
                        break;
                    case 'ultimos_3_meses':
                        dataInicio.setMonth(hoje.getMonth() - 3);
                        break;
                    case 'ultimo_ano':
                        dataInicio.setFullYear(hoje.getFullYear() - 1);
                        break;
                }
                
                document.getElementById('relatorioDataInicio').value = dataInicio.toISOString().split('T')[0];
                document.getElementById('relatorioDataFim').value = hoje.toISOString().split('T')[0];
            }
        }

        function gerarRelatorio() {
            const tipo = document.getElementById('relatorioTipo').value;
            const periodo = document.getElementById('relatorioPeriodo').value;
            const data_inicio = document.getElementById('relatorioDataInicio').value;
            const data_fim = document.getElementById('relatorioDataFim').value;
            const status = document.getElementById('relatorioStatus').value;

            const params = new URLSearchParams({
                acao: 'relatorio_avancado',
                tipo,
                periodo,
                data_inicio,
                data_fim,
                status
            });

            // Mostrar loading
            document.getElementById('relatorio-cards').innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';

            fetch(`../controllers/AdminController.class.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarRelatorioAvancado(data.dados, tipo);
                        gerarInsights(data.dados, tipo);
                    } else {
                        document.getElementById('relatorio-cards').innerHTML = `<div class="col-12"><div class="alert alert-danger">${data.mensagem || 'Erro ao gerar relatório.'}</div></div>`;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('relatorio-cards').innerHTML = '<div class="col-12"><div class="alert alert-danger">Erro ao gerar relatório.</div></div>';
                });
        }

        function renderizarRelatorioAvancado(dados, tipo) {
            // Renderizar cards
            renderizarCardsRelatorio(dados, tipo);
            
            // Renderizar gráficos
            renderizarGraficoTemporal(dados.temporal || [], tipo);
            renderizarGraficoDistribuicao(dados.distribuicao || [], tipo);
            
            // Renderizar tabela
            renderizarTabelaDetalhada(dados.detalhado || [], tipo);
        }

        function renderizarCardsRelatorio(dados, tipo) {
            let html = '';
            
            // Cards baseados no tipo de relatório
            switch(tipo) {
                case 'usuarios':
                    html = `
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <h3>${dados.total_usuarios || 0}</h3>
                                    <p class="text-muted">Total de Usuários</p>
                                    <small class="text-success">+${dados.novos_usuarios || 0} este período</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                                    <h3>${dados.usuarios_ativos || 0}</h3>
                                    <p class="text-muted">Usuários Ativos</p>
                                    <small class="text-info">${dados.total_usuarios > 0 ? ((dados.usuarios_ativos / dados.total_usuarios) * 100).toFixed(1) : 0}% do total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-hard-hat fa-2x text-warning mb-2"></i>
                                    <h3>${dados.total_prestadores || 0}</h3>
                                    <p class="text-muted">Prestadores</p>
                                    <small class="text-primary">Taxa: ${dados.total_usuarios > 0 ? ((dados.total_prestadores / dados.total_usuarios) * 100).toFixed(1) : 0}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                    <h3>${dados.crescimento_percentual || 0}%</h3>
                                    <p class="text-muted">Crescimento</p>
                                    <small class="text-${dados.crescimento_percentual >= 0 ? 'success' : 'danger'}">
                                        ${dados.crescimento_percentual >= 0 ? '↑' : '↓'} vs período anterior
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'solicitacoes':
                    html = `
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                    <h3>${dados.total_solicitacoes || 0}</h3>
                                    <p class="text-muted">Total de Solicitações</p>
                                    <small class="text-success">+${dados.novas_solicitacoes || 0} este período</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <h3>${dados.solicitacoes_concluidas || 0}</h3>
                                    <p class="text-muted">Concluídas</p>
                                    <small class="text-info">Taxa: ${dados.total_solicitacoes > 0 ? ((dados.solicitacoes_concluidas / dados.total_solicitacoes) * 100).toFixed(1) : 0}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                    <h3>${dados.tempo_medio_resposta || 0}h</h3>
                                    <p class="text-muted">Tempo Médio</p>
                                    <small class="text-primary">Para primeira proposta</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-dollar-sign fa-2x text-info mb-2"></i>
                                    <h3>R$ ${(dados.valor_medio || 0).toLocaleString('pt-BR')}</h3>
                                    <p class="text-muted">Valor Médio</p>
                                    <small class="text-success">Por solicitação</small>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                    
                default: // geral
                    html = `
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-tachometer-alt fa-2x text-primary mb-2"></i>
                                    <h3>${dados.total_usuarios || 0}</h3>
                                    <p class="text-muted">Total Usuários</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                                    <h3>${dados.total_solicitacoes || 0}</h3>
                                    <p class="text-muted">Solicitações</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-handshake fa-2x text-warning mb-2"></i>
                                    <h3>${dados.total_propostas || 0}</h3>
                                    <p class="text-muted">Propostas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-star fa-2x text-info mb-2"></i>
                                    <h3>${(dados.media_avaliacoes || 0).toFixed(1)} ⭐</h3>
                                    <p class="text-muted">Avaliação Média</p>
                                </div>
                            </div>
                        </div>
                    `;
            }
            
            document.getElementById('relatorio-cards').innerHTML = html;
        }

        function renderizarGraficoTemporal(dados, tipo) {
            const ctx = document.getElementById('chartTemporal');
            if (!ctx) return;
            
            if (chartTemporal) {
                chartTemporal.destroy();
            }
            
            chartTemporal = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dados.map(d => d.periodo || d.data || 'Sem data'),
                    datasets: [{
                        label: 'Quantidade',
                        data: dados.map(d => d.valor || d.quantidade || 0),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }

        function renderizarGraficoDistribuicao(dados, tipo) {
            const ctx = document.getElementById('chartDistribuicao');
            if (!ctx) return;
            
            if (chartDistribuicao) {
                chartDistribuicao.destroy();
            }
            
            if (!dados || dados.length === 0) {
                ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
                return;
            }
            
            chartDistribuicao = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dados.map(d => d.categoria || d.nome || 'Sem categoria'),
                    datasets: [{
                        data: dados.map(d => d.valor || d.quantidade || 0),
                        backgroundColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#f5576c',
                            '#4facfe',
                            '#00f2fe'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function renderizarTabelaDetalhada(dados, tipo) {
            if (!dados || dados.length === 0) {
                document.getElementById('relatorio-tabela').innerHTML = '<p class="text-center text-muted">Nenhum dado detalhado disponível.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-striped table-hover"><thead class="table-dark"><tr>';
            
            // Cabeçalhos da tabela baseados no tipo
            const headers = {
                usuarios: ['ID', 'Nome', 'Email', 'Tipo', 'Data Cadastro', 'Último Acesso', 'Status'],
                solicitacoes: ['ID', 'Título', 'Cliente', 'Tipo Serviço', 'Valor', 'Status', 'Data'],
                propostas: ['ID', 'Solicitação', 'Prestador', 'Valor', 'Status', 'Data'],
                avaliacoes: ['ID', 'Solicitação', 'Avaliador', 'Nota', 'Data'],
                geral: ['Item', 'Valor', 'Descrição']
            };

            (headers[tipo] || headers.geral).forEach(header => {
                html += `<th>${header}</th>`;
            });
            html += '</tr></thead><tbody>';

            dados.slice(0, 100).forEach(item => { // Limitar a 100 itens para performance
                html += '<tr>';
                Object.values(item).forEach(value => {
                    html += `<td>${value || '-'}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            
            if (dados.length > 100) {
                html += `<p class="text-muted text-center">Mostrando primeiros 100 de ${dados.length} registros. Use a exportação para ver todos.</p>`;
            }

            document.getElementById('relatorio-tabela').innerHTML = html;
        }

        function gerarInsights(dados, tipo) {
            let insights = [];
            
            switch(tipo) {
                case 'usuarios':
                    if (dados.crescimento_percentual && dados.crescimento_percentual > 10) {
                        insights.push('📈 <strong>Crescimento Acelerado:</strong> O crescimento de usuários está  10% acima do normal. Continue investindo em marketing!');
                    }
                    if (dados.total_usuarios && dados.usuarios_ativos && (dados.usuarios_ativos / dados.total_usuarios) < 0.7) {
                        insights.push('⚠️ <strong>Baixo Engajamento:</strong> Menos de 70% dos usuários estão ativos. Considere campanhas de reativação.');
                    }
                    if (dados.total_usuarios && dados.total_prestadores && (dados.total_prestadores / dados.total_usuarios) < 0.3) {
                        insights.push('👷 <strong>Necessário mais prestadores:</strong> A proporção de prestadores está baixa. Foque no recrutamento.');
                    }
                    break;
                    
                case 'solicitacoes':
                    if (dados.tempo_medio_resposta && dados.tempo_medio_resposta > 24) {
                        insights.push('⏰ <strong>Tempo de resposta alto:</strong> Solicitações demoram mais de 24h para receber propostas. Melhore a notificação para prestadores.');
                    }
                    if (dados.total_solicitacoes && dados.solicitacoes_concluidas && (dados.solicitacoes_concluidas / dados.total_solicitacoes) > 0.8) {
                        insights.push('✅ <strong>Excelente taxa de conclusão:</strong> Mais de 80% das solicitações são concluídas com sucesso!');
                    }
                    break;
            }
            
            if (insights.length === 0) {
                insights.push('📊 <strong>Desempenho estável:</strong> Os indicadores estão dentro do esperado. Continue monitorando as métricas.');
            }
            
            document.getElementById('relatorio-insights').innerHTML = insights.map(insight => 
                `<div class="alert alert-info border-0 mb-2">${insight}</div>`
            ).join('');
        }

        function alterarTipoGrafico(tipo) {
            if (chartTemporal) {
                chartTemporal.config.type = tipo;
                chartTemporal.update();
                
                // Atualizar botões ativos
                document.querySelectorAll('.btn-group button').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');
            }
        }

        function exportarRelatorio(formato) {
            const tipo = document.getElementById('relatorioTipo').value;
            const periodo = document.getElementById('relatorioPeriodo').value;
            
            const params = new URLSearchParams({
                acao: 'exportar_relatorio',
                formato,
                tipo,
                periodo,
                data_inicio: document.getElementById('relatorioDataInicio').value,
                data_fim: document.getElementById('relatorioDataFim').value
            });

            // Criar link de download
            const link = document.createElement('a');
            link.href = `../controllers/AdminController.class.php?${params}`;
            link.download = `relatorio_${tipo}_${new Date().toISOString().split('T')[0]}.${formato}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function imprimirRelatorio() {
            const conteudo = document.getElementById('content-body').innerHTML;
            const janela = window.open('', '_blank');
            janela.document.write(`
                <html>
                    <head>
                        <title>Relatório - ${document.getElementById('relatorioTipo').value}</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @media print {
                                .btn, .card-header .btn-group { display: none !important; }
                                canvas { max-height: 400px !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container-fluid">
                            <h1>Relatório - ${document.getElementById('relatorioTipo').value}</h1>
                            <hr>
                            ${conteudo}
                        </div>
                    </body>
                </html>
            `);
            janela.document.close();
            janela.print();
        }

        function limparFiltroRelatorio() {
            document.getElementById('formFiltroRelatorio').reset();
            document.getElementById('dataInicioContainer').style.display = 'none';
            document.getElementById('dataFimContainer').style.display = 'none';
            gerarRelatorio();
        }



        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            carregarDashboard();

            // Form submit para tipo de serviço (remover qualquer referência a 'icone')
            document.addEventListener('submit', function(e) {
                if (e.target.id === 'formTipoServico') {
                    e.preventDefault();
                    const id = document.getElementById('tipoServicoId').value;
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeServico').value);
                    formData.append('categoria', document.getElementById('categoriaServico').value);
                    formData.append('preco_medio', document.getElementById('precoMedioServico').value);
                    formData.append('ativo', document.getElementById('ativoServico').value);
                    formData.append('descricao', document.getElementById('descricaoServico').value);

                    // Remover: formData.append('icone', ...);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_tipo_servico&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_tipo_servico';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: data.mensagem,
                                showConfirmButton: false,
                                timer: 1800
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalTipoServico')).hide();
                            listarTiposServico();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao processar solicitação',
                            confirmButtonColor: '#764ba2'
                        });
                    });
                }

                // Form submit para status
                if (e.target.id === 'formStatus') {
                    e.preventDefault();
                    
                    const id = document.getElementById('statusId').value;
                    const formData = new FormData();
                    formData.append('nome', document.getElementById('nomeStatus').value);
                    formData.append('descricao', document.getElementById('descricaoStatus').value);
                    formData.append('cor', document.getElementById('corStatus').value);

                    const url = id ? 
                        `../controllers/AdminController.class.php?acao=atualizar_status_solicitacao&id=${id}` :
                        '../controllers/AdminController.class.php?acao=criar_status_solicitacao';

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            alert(data.mensagem);
                            bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide();
                            listarStatus();
                            carregarStatusSolicitacao(); // Recarregar estatísticas
                        } else {
                            alert('Erro: ' + data.mensagem);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar solicitação');
                    });
                }
            });
        });
    </script>
</body>
</html>
                <div class="modal fade" id="modalStatus" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalStatusTitle">Novo Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="formStatus">
                                <div class="modal-body">
                                    <input type="hidden" id="statusId">
                                    <div class="mb-3">
                                        <label for="nomeStatus" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nomeStatus" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descricaoStatus" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricaoStatus" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="corStatus" class="form-label">Cor</label>
                                        <input type="color" class="form-control" id="corStatus" value="#007bff">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;

            listarStatus();
        }

        // Renderizar estatísticas de status
        function renderizarEstatisticasStatus(stats) {
            const statsCards = document.getElementById('stats-cards');
            
            // Calcular totais
            const totalGeral = stats.reduce((acc, curr) => acc + parseInt(curr.total_solicitacoes || 0), 0);
            const totalHoje = stats.reduce((acc, curr) => acc + parseInt(curr.hoje || 0), 0);
            const totalSemana = stats.reduce((acc, curr) => acc + parseInt(curr.esta_semana || 0), 0);
            const totalMes = stats.reduce((acc, curr) => acc + parseInt(curr.este_mes || 0), 0);

            statsCards.innerHTML = `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Total Solicitações</h6>
                                    <span class="h2 mb-0">${totalGeral}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Hoje</h6>
                                    <span class="h2 mb-0">${totalHoje}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Esta Semana</h6>
                                    <span class="h2 mb-0">${totalSemana}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Este Mês</h6>
                                    <span class="h2 mb-0">${totalMes}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Listar status
        function listarStatus(page = 1) {
            const nome = document.getElementById('filtroNomeStatus')?.value || '';
            const params = new URLSearchParams({ page, per_page: 20, nome });

            fetch(`../controllers/AdminController.class.php?acao=listar_status_solicitacao&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarStatus(data.dados.status);
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Renderizar lista de status
        function renderizarStatus(statusList) {
            const lista = document.getElementById('status-lista');
            
            if (statusList.length === 0) {
                lista.innerHTML = '<p class="text-center">Nenhum status encontrado.</p>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Cor</th>
                                <th>Total Solicitações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            statusList.forEach(status => {
                const descricao = status.descricao || '-';
                
                html += `
                    <tr>
                        <td>${status.id}</td>
                        <td>
                            <span class="badge" style="background-color: ${status.cor}; color: white;">
                                ${status.nome}
                            </span>
                        </td>
                        <td>${descricao}</td>
                        <td>
                            <div style="width: 30px; height: 20px; background-color: ${status.cor}; border-radius: 3px; display: inline-block;"></div>
                            ${status.cor}
                        </td>
                        <td>
                            <span class="badge bg-info">${status.total_solicitacoes || 0}</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarStatus(${status.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletarStatus(${status.id})" ${(status.total_solicitacoes || 0) > 0 ? 'disabled title="Não é possível deletar status com solicitações"' : ''}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            lista.innerHTML = html;
        }

        // Abrir modal para novo status
        function abrirModalStatus() {
            document.getElementById('modalStatusTitle').textContent = 'Novo Status';
            document.getElementById('formStatus').reset();
            document.getElementById('statusId').value = '';
            document.getElementById('corStatus').value = '#007bff';
            new bootstrap.Modal(document.getElementById('modalStatus')).show();
        }

        // Editar status
        function editarStatus(id) {
            fetch(`../controllers/AdminController.class.php?acao=buscar_status_solicitacao&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const status = data.dados;
                        document.getElementById('modalStatusTitle').textContent = 'Editar Status';
                        document.getElementById('statusId').value = status.id;
                        document.getElementById('nomeStatus').value = status.nome;
                        document.getElementById('descricaoStatus').value = status.descricao || '';
                        document.getElementById('corStatus').value = status.cor;
                        new bootstrap.Modal(document.getElementById('modalStatus')).show();
                    } else {
                        alert('Erro ao carregar status: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar status');
                });
        }

        // Deletar status
        function deletarStatus(id) {
            if (confirm('Tem certeza que deseja deletar este status?')) {
                fetch(`../controllers/AdminController.class.php?acao=deletar_status_solicitacao&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert(data.mensagem);
                        listarStatus();
                        carregarStatusSolicitacao(); // Recarregar estatísticas
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                });
            }
        }

    </script>
</body>
</html>
