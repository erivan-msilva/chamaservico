<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Chama Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .content-area {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Animação de loading para botão de sair */
        @keyframes spin {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
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

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Admin Panel</h4>
                        <small class="text-white-50">Chama Serviço</small>
                        <hr class="text-white-50">
                        <div class="d-flex align-items-center text-white-50">
                            <i class="bi bi-person-circle me-2"></i>
                            <small>Olá, <?php echo htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin'); ?></small>
                        </div>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" onclick="carregarConteudo('dashboard')">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="carregarConteudo('tipos-servico')">
                                <i class="bi bi-gear"></i> Tipos de Serviço
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="carregarConteudo('status-solicitacao')">
                                <i class="bi bi-flag"></i> Status Solicitação
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="carregarConteudo('usuarios')">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                     
                    
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="carregarConteudo('relatorios')">
                                <i class="bi bi-graph-up"></i> Relatórios
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="#" onclick="logout()">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2" id="page-title">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="atualizarDados()">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="logout()">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo dinâmico -->
                <div id="main-content">
                    <!-- Dashboard cards -->
                    <div class="row mb-4" id="stats-cards">
                        <!-- Cards serão carregados via JavaScript -->
                    </div>
                    
                    <!-- Gráficos e tabelas -->
                    <div id="content-body">
                        <!-- Conteúdo carregado dinamicamente -->
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script>
        let currentPage = 'dashboard';

        // Carregar conteúdo dinâmico
        function carregarConteudo(pagina) {
            // Atualizar navegação ativa
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');

            currentPage = pagina;
            
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
                case 'solicitacoes':
                    carregarSolicitacoes();
                    break;
                case 'avaliacoes':
                    carregarAvaliacoes();
                    break;
                case 'relatorios':
                    carregarRelatorios();
                    break;
            }
        }

        // Carregar dashboard
        function carregarDashboard() {
            document.getElementById('page-title').textContent = 'Dashboard';
            
            fetch('../controllers/AdminController.class.php?acao=dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarDashboard(data.dados);
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Renderizar dashboard
        function renderizarDashboard(stats) {
            const statsCards = document.getElementById('stats-cards');
            statsCards.innerHTML = `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Total Usuários</h6>
                                    <span class="h2 mb-0">${stats.total_usuarios || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Clientes</h6>
                                    <span class="h2 mb-0">${stats.total_clientes || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Prestadores</h6>
                                    <span class="h2 mb-0">${stats.total_prestadores || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hard-hat fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Solicitações Hoje</h6>
                                    <span class="h2 mb-0">${stats.solicitacoes_hoje || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Carregar gráficos e tabelas do dashboard
            document.getElementById('content-body').innerHTML = `
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Solicitações por Mês</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartSolicitacoes"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Últimas Atividades</h5>
                            </div>
                            <div class="card-body">
                                <div class="activity-feed">
                                    <div class="activity-item">
                                        <i class="bi bi-person-plus text-success"></i>
                                        <span>Novo usuário cadastrado</span>
                                        <small class="text-muted">2 min atrás</small>
                                    </div>
                                    <div class="activity-item">
                                        <i class="bi bi-file-text text-primary"></i>
                                        <span>Nova solicitação criada</span>
                                        <small class="text-muted">15 min atrás</small>
                                    </div>
                                    <div class="activity-item">
                                        <i class="bi bi-star text-warning"></i>
                                        <span>Nova avaliação recebida</span>
                                        <small class="text-muted">1 hora atrás</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Carregar tipos de serviço
        function carregarTiposServico() {
            document.getElementById('page-title').textContent = 'Tipos de Serviço';
            document.getElementById('stats-cards').innerHTML = '';
            document.getElementById('content-body').innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Gerenciar Tipos de Serviço</h5>
                        <button type="button" class="btn btn-primary" onclick="abrirModalTipoServico()">
                            <i class="bi bi-plus"></i> Novo Tipo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="filtroNomeTipo" placeholder="Buscar por nome...">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary" onclick="filtrarTiposServico()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div id="tipos-servico-lista">
                            <!-- Lista carregada via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Modal para adicionar/editar tipo de serviço -->
                <div class="modal fade" id="modalTipoServico" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTipoServicoTitle">Novo Tipo de Serviço</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="formTipoServico">
                                <div class="modal-body">
                                    <input type="hidden" id="tipoServicoId">
                                    <div class="mb-3">
                                        <label for="nomeServico" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nomeServico" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="categoriaServico" class="form-label">Categoria</label>
                                        <input type="text" class="form-control" id="categoriaServico">
                                    </div>
                                    <div class="mb-3">
                                        <label for="precoMedioServico" class="form-label">Preço Médio</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="precoMedioServico">
                                    </div>
                                    <div class="mb-3">
                                        <label for="ativoServico" class="form-label">Ativo</label>
                                        <select class="form-select" id="ativoServico">
                                            <option value="1">Sim</option>
                                            <option value="0">Não</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descricaoServico" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricaoServico" rows="3"></textarea>
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

            listarTiposServico();
        }

        // Listar tipos de serviço
        function listarTiposServico(page = 1) {
            const nome = document.getElementById('filtroNomeTipo')?.value || '';
            const params = new URLSearchParams({ page, per_page: 10, nome });

            fetch(`../controllers/AdminController.class.php?acao=listar_tipos_servico&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarTiposServico(data.dados.tipos_servico);
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Carregar gráfico de solicitações
        function carregarGraficoSolicitacoes() {
            fetch('../controllers/AdminController.class.php?acao=dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const ctx = document.getElementById('chartSolicitacoes').getContext('2d');
                        const chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.dados.meses,
                                datasets: [{
                                    label: 'Solicitações',
                                    data: data.dados.solicitacoes_por_mes,
                                    borderColor: '#667eea',
                                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                                    borderWidth: 2,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                    }
                                },
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar gráfico:', error));
        }

        // Renderizar lista de tipos de serviço
        function renderizarTiposServico(tipos) {
            const lista = document.getElementById('tipos-servico-lista');
            if (!tipos || tipos.length === 0) {
                lista.innerHTML = '<p class="text-center">Nenhum tipo de serviço encontrado.</p>';
                return;
            }
            let html = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Preço Médio</th>
                                <th>Ativo</th>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            tipos.forEach(tipo => {
                html += `
                    <tr>
                        <td>${tipo.id}</td>
                        <td>${tipo.nome}</td>
                        <td>${tipo.categoria || '-'}</td>
                        <td>${tipo.preco_medio !== undefined && tipo.preco_medio !== null ? 'R$ ' + Number(tipo.preco_medio).toLocaleString('pt-BR', {minimumFractionDigits: 2}) : '-'}</td>
                        <td>
                            ${tipo.ativo == 1 ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>'}
                        </td>
                        <td>${tipo.descricao || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarTipoServico(${tipo.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletarTipoServico(${tipo.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table></div>';
            lista.innerHTML = html;
        }

        // Abrir modal para novo tipo de serviço
        function abrirModalTipoServico() {
            document.getElementById('modalTipoServicoTitle').textContent = 'Novo Tipo de Serviço';
            document.getElementById('formTipoServico').reset();
            document.getElementById('tipoServicoId').value = '';
            new bootstrap.Modal(document.getElementById('modalTipoServico')).show();
        }

        // Editar tipo de serviço
        function editarTipoServico(id) {
            fetch(`../controllers/AdminController.class.php?acao=buscar_tipo_servico&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const tipo = data.dados;
                        document.getElementById('modalTipoServicoTitle').textContent = 'Editar Tipo de Serviço';
                        document.getElementById('tipoServicoId').value = tipo.id;
                        document.getElementById('nomeServico').value = tipo.nome;
                        document.getElementById('categoriaServico').value = tipo.categoria || '';
                        document.getElementById('precoMedioServico').value = tipo.preco_medio !== undefined && tipo.preco_medio !== null ? tipo.preco_medio : '';
                        document.getElementById('ativoServico').value = tipo.ativo;
                        document.getElementById('descricaoServico').value = tipo.descricao || '';
                        new bootstrap.Modal(document.getElementById('modalTipoServico')).show();
                    } else {
                        alert('Erro ao carregar tipo de serviço: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar tipo de serviço');
                });
        }

        // Deletar tipo de serviço
        function deletarTipoServico(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Deseja deletar este tipo de serviço?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../controllers/AdminController.class.php?acao=deletar_tipo_servico&id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deletado!',
                                text: data.mensagem,
                                showConfirmButton: false,
                                timer: 1800
                            });
                            listarTiposServico();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    });
                }
            });
        }

        // Filtrar tipos de serviço
        function filtrarTiposServico() {
            listarTiposServico(1);
        }

        // Atualizar dados
        function atualizarDados() {
            carregarConteudo(currentPage);
        }

        // Logout com modal estilizado
        function logout() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();

            // Evitar múltiplos binds
            const btn = document.getElementById('btnConfirmLogout');
            btn.onclick = function() {
                btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Saindo...';
                btn.disabled = true;
                window.location.href = 'logout.php';
            };
        }

        // Verificar sessão periodicamente (a cada 5 minutos)
        setInterval(function() {
            fetch('../controllers/AdminController.class.php?acao=verificar_sessao')
                .then(response => response.json())
                .then(data => {
                    if (!data.sucesso || !data.logado) {
                        alert('Sua sessão expirou. Você será redirecionado para o login.');
                        window.location.href = 'login.php?sessao_expirada=1';
                    }
                })
                .catch(error => {
                    console.log('Erro ao verificar sessão:', error);
                });
        }, 300000); // 5 minutos

        // Detectar inatividade do usuário
        let timeoutInatividade;
        let tempoInatividade = 30 * 60 * 1000; // 30 minutos

        function resetarTimeoutInatividade() {
            clearTimeout(timeoutInatividade);
            timeoutInatividade = setTimeout(function() {
                if (confirm('Você ficou inativo por muito tempo. Deseja continuar?')) {
                    resetarTimeoutInatividade();
                } else {
                    logout();
                }
            }, tempoInatividade);
        }

        // Eventos para detectar atividade
        document.addEventListener('mousedown', resetarTimeoutInatividade);
        document.addEventListener('mousemove', resetarTimeoutInatividade);
        document.addEventListener('keypress', resetarTimeoutInatividade);
        document.addEventListener('scroll', resetarTimeoutInatividade);
        document.addEventListener('touchstart', resetarTimeoutInatividade);

        // Iniciar timeout de inatividade
        resetarTimeoutInatividade();

        // Carregar usuários
        function carregarUsuarios() {
            document.getElementById('page-title').textContent = 'Gerenciar Usuários';
            document.getElementById('stats-cards').innerHTML = '';
            
            // Carregar estatísticas primeiro
            fetch('../controllers/AdminController.class.php?acao=estatisticas_usuarios')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarEstatisticasUsuarios(data.dados);
                    }
                })
                .catch(error => console.error('Erro:', error));
            
            document.getElementById('content-body').innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Gerenciar Usuários do Sistema</h5>
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportarUsuarios()">
                                <i class="bi bi-download"></i> Exportar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" id="filtroNomeUsuario" placeholder="Buscar por nome...">
                            </div>
                            <div class="col-md-3">
                                <input type="email" class="form-control form-control-sm" id="filtroEmailUsuario" placeholder="Buscar por email...">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" id="filtroTipoUsuario">
                                    <option value="">Todos os tipos</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="prestador">Prestador</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" id="filtroStatusUsuario">
                                    <option value="">Todos status</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary btn-sm me-1" onclick="filtrarUsuarios()">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="limparFiltrosUsuarios()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="usuarios-lista">
                            <!-- Lista carregada via JavaScript -->
                        </div>
                        
                        <!-- Paginação -->
                        <nav id="paginacao-usuarios" class="mt-3">
                            <!-- Paginação carregada via JavaScript -->
                        </nav>
                    </div>
                </div>

                <!-- Modal para visualizar/editar usuário -->
                <div class="modal fade" id="modalUsuario" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalUsuarioTitle">Detalhes do Usuário</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Abas -->
                                <ul class="nav nav-tabs" id="usuarioTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                                            <i class="bi bi-person"></i> Informações
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button">
                                            <i class="bi bi-graph-up"></i> Estatísticas
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content mt-3" id="usuarioTabContent">
                                    <!-- Aba Informações -->
                                    <div class="tab-pane fade show active" id="info">
                                        <div id="usuario-info">
                                            <!-- Informações carregadas via JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Aba Estatísticas -->
                                    <div class="tab-pane fade" id="stats">
                                        <div id="usuario-stats">
                                            <!-- Estatísticas carregadas via JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Aba Editar -->
                                    <div class="tab-pane fade" id="edit">
                                        <form id="formEditarUsuario">
                                            <input type="hidden" id="editarUsuarioId">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editarNomeUsuario" class="form-label">Nome</label>
                                                        <input type="text" class="form-control" id="editarNomeUsuario" name="nome" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editarEmailUsuario" class="form-label">E-mail</label>
                                                        <input type="email" class="form-control" id="editarEmailUsuario" name="email" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editarTelefoneUsuario" class="form-label">Telefone</label>
                                                        <input type="text" class="form-control" id="editarTelefoneUsuario" name="telefone">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editarTipoUsuario" class="form-label">Tipo</label>
                                                        <select class="form-select" id="editarTipoUsuario" name="tipo" required>
                                                            <option value="cliente">Cliente</option>
                                                            <option value="prestador">Prestador</option>
                                                            <option value="ambos">Ambos</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editarSenhaUsuario" class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                                                <input type="password" class="form-control" id="editarSenhaUsuario" name="senha">
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-check"></i> Salvar Alterações
                                                </button>
                                                <button type="button" class="btn btn-warning" onclick="toggleStatusUsuarioModal()">
                                                    <i class="bi bi-toggle-on"></i> Alterar Status
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="deletarUsuarioModal()">
                                                    <i class="bi bi-trash"></i> Deletar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            listarUsuarios();
        }

        // Renderizar estatísticas de usuários
        function renderizarEstatisticasUsuarios(stats) {
            const statsCards = document.getElementById('stats-cards');
            
            statsCards.innerHTML = `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase mb-0">Total Usuários</h6>
                                    <span class="h2 mb-0">${stats.total_usuarios || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Clientes</h6>
                                    <span class="h2 mb-0">${stats.total_clientes || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Prestadores</h6>
                                    <span class="h2 mb-0">${stats.total_prestadores || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hard-hat fa-2x opacity-75"></i>
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
                                    <h6 class="text-uppercase mb-0">Cadastros Hoje</h6>
                                    <span class="h2 mb-0">${stats.cadastros_hoje || 0}</span>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Listar usuários
        let paginaAtualUsuarios = 1;
        function listarUsuarios(page = 1) {
            paginaAtualUsuarios = page;
            const filtros = obterFiltrosUsuarios();
            const params = new URLSearchParams({ page, per_page: 10, ...filtros });

            fetch(`../controllers/AdminController.class.php?acao=listar_usuarios&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarUsuarios(data.dados.usuarios);
                        renderizarPaginacaoUsuarios(data.dados.paginacao);
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Obter filtros
        function obterFiltrosUsuarios() {
            return {
                nome: document.getElementById('filtroNomeUsuario')?.value || '',
                email: document.getElementById('filtroEmailUsuario')?.value || '',
                tipo: document.getElementById('filtroTipoUsuario')?.value || '',
                status: document.getElementById('filtroStatusUsuario')?.value || ''
            };
        }

        // Renderizar lista de usuários
        function renderizarUsuarios(usuarios) {
            const lista = document.getElementById('usuarios-lista');
            
            if (usuarios.length === 0) {
                lista.innerHTML = '<p class="text-center">Nenhum usuário encontrado.</p>';
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
                                <th>Solicitações</th>
                                <th>Propostas</th>
                                <th>Avaliação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            usuarios.forEach(usuario => {
                const statusBadge = usuario.ativo == 1 ? 
                    '<span class="badge bg-success">Ativo</span>' : 
                    '<span class="badge bg-danger">Inativo</span>';

                const tipoBadge = {
                    'cliente': '<span class="badge bg-primary">Cliente</span>',
                    'prestador': '<span class="badge bg-warning text-dark">Prestador</span>',
                    'ambos': '<span class="badge bg-info">Ambos</span>'
                }[usuario.tipo] || '<span class="badge bg-secondary">-</span>';

                const mediaAvaliacao = usuario.media_avaliacao ? 
                    `<span class="badge bg-success">${parseFloat(usuario.media_avaliacao).toFixed(1)} ⭐</span>` : 
                    '<span class="text-muted">-</span>';

                html += `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div>
                                    <strong>${usuario.nome}</strong>
                                    ${usuario.telefone ? `<br><small class="text-muted">${usuario.telefone}</small>` : ''}
                                </div>
                            </div>
                        </td>
                        <td>${usuario.email}</td>
                        <td>${tipoBadge}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <small>${new Date(usuario.data_cadastro).toLocaleDateString('pt-BR')}</small>
                            ${usuario.ultimo_acesso ? `<br><small class="text-muted">Último: ${new Date(usuario.ultimo_acesso).toLocaleDateString('pt-BR')}</small>` : ''}
                        </td>
                        <td><span class="badge bg-info">${usuario.total_solicitacoes || 0}</span></td>
                        <td><span class="badge bg-warning text-dark">${usuario.total_propostas || 0}</span></td>
                        <td>${mediaAvaliacao}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="visualizarUsuario(${usuario.id})" title="Visualizar">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="toggleStatusUsuario(${usuario.id})" title="Alterar Status">
                                    <i class="bi bi-toggle-${usuario.ativo == 1 ? 'on' : 'off'}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            lista.innerHTML = html;
        }

        // Renderizar paginação
        function renderizarPaginacaoUsuarios(paginacao) {
            const nav = document.getElementById('paginacao-usuarios');
            
            if (paginacao.total_pages <= 1) {
                nav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination justify-content-center">';

            // Anterior
            if (paginacao.page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="listarUsuarios(${paginacao.page - 1})">Anterior</a></li>`;
            }

            // Páginas
            for (let i = 1; i <= paginacao.total_pages; i++) {
                const active = i == paginacao.page ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="listarUsuarios(${i})">${i}</a></li>`;
            }

            // Próximo
            if (paginacao.page < paginacao.total_pages) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="listarUsuarios(${paginacao.page + 1})">Próximo</a></li>`;
            }

            html += '</ul>';
            nav.innerHTML = html;
        }

        // Visualizar usuário
        function visualizarUsuario(id) {
            fetch(`../controllers/AdminController.class.php?acao=buscar_usuario&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const usuario = data.dados;
                        preencherModalUsuario(usuario);
                        new bootstrap.Modal(document.getElementById('modalUsuario')).show();
                    } else {
                        alert('Erro ao carregar usuário: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar usuário');
                });
        }

        // Preencher modal do usuário
        function preencherModalUsuario(usuario) {
            // Aba Informações
            document.getElementById('usuario-info').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Dados Pessoais</h6>
                        <p><strong>Nome:</strong> ${usuario.nome}</p>
                        <p><strong>E-mail:</strong> ${usuario.email}</p>
                        <p><strong>CPF:</strong> ${usuario.cpf || 'Não informado'}</p>
                        <p><strong>Telefone:</strong> ${usuario.telefone || 'Não informado'}</p>
                        <p><strong>Tipo:</strong> <span class="badge bg-primary">${usuario.tipo}</span></p>
                        <p><strong>Status:</strong> <span class="badge bg-${usuario.ativo == 1 ? 'success' : 'danger'}">${usuario.ativo == 1 ? 'Ativo' : 'Inativo'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Datas</h6>
                        <p><strong>Cadastro:</strong> ${new Date(usuario.data_cadastro).toLocaleString('pt-BR')}</p>
                        <p><strong>Último Acesso:</strong> ${usuario.ultimo_acesso ? new Date(usuario.ultimo_acesso).toLocaleString('pt-BR') : 'Nunca acessou'}</p>
                    </div>
                </div>
            `;

            // Aba Estatísticas
            document.getElementById('usuario-stats').innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">${usuario.total_solicitacoes || 0}</h5>
                                <p class="card-text">Solicitações</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">${usuario.total_propostas || 0}</h5>
                                <p class="card-text">Propostas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">${usuario.total_avaliacoes || 0}</h5>
                                <p class="card-text">Avaliações</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">${usuario.media_avaliacao ? parseFloat(usuario.media_avaliacao).toFixed(1) : 'N/A'}</h5>
                                <p class="card-text">Média ⭐</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Aba Editar
            document.getElementById('editarUsuarioId').value = usuario.id;
            document.getElementById('editarNomeUsuario').value = usuario.nome;
            document.getElementById('editarEmailUsuario').value = usuario.email;
            document.getElementById('editarTelefoneUsuario').value = usuario.telefone || '';
            document.getElementById('editarTipoUsuario').value = usuario.tipo;
            document.getElementById('editarSenhaUsuario').value = '';
        }

        // Filtrar usuários
        function filtrarUsuarios() {
            listarUsuarios(1);
        }

        // Limpar filtros
        function limparFiltrosUsuarios() {
            document.getElementById('filtroNomeUsuario').value = '';
            document.getElementById('filtroEmailUsuario').value = '';
            document.getElementById('filtroTipoUsuario').value = '';
            document.getElementById('filtroStatusUsuario').value = '';
            listarUsuarios(1);
        }

        // Toggle status do usuário
        function toggleStatusUsuario(id) {
            Swal.fire({
                title: 'Alterar status?',
                text: "Deseja alterar o status deste usuário?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#764ba2',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, alterar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../controllers/AdminController.class.php?acao=toggle_status_usuario&id=${id}`, {
                        method: 'POST'
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
                            listarUsuarios(paginaAtualUsuarios);
                            carregarUsuarios(); // Recarregar estatísticas
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    });
                }
            });
        }

        // Funções do modal
        let usuarioAtualId = null;

        function toggleStatusUsuarioModal() {
            const id = document.getElementById('editarUsuarioId').value;
            toggleStatusUsuario(id);
        }

        function deletarUsuarioModal() {
            const id = document.getElementById('editarUsuarioId').value;
            Swal.fire({
                title: 'Tem certeza?',
                text: "Deseja realmente deletar este usuário? Esta ação não pode ser desfeita.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../controllers/AdminController.class.php?acao=deletar_usuario&id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deletado!',
                                text: data.mensagem,
                                showConfirmButton: false,
                                timer: 1800
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
                            listarUsuarios(paginaAtualUsuarios);
                            carregarUsuarios(); // Recarregar estatísticas
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.mensagem,
                                confirmButtonColor: '#764ba2'
                            });
                        }
                    });
                }
            });
        }

        // Substitua alert() por Swal.fire() no submit do formEditarUsuario
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'formEditarUsuario') {
                e.preventDefault();
                const id = document.getElementById('editarUsuarioId').value;
                const formData = new FormData(e.target);

                fetch(`../controllers/AdminController.class.php?acao=atualizar_usuario&id=${id}`, {
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
                        listarUsuarios(paginaAtualUsuarios);
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

            // Form submit para tipo de serviço (remover qualquer referência a 'icone')
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

        // Novas funções para Relatórios
        function carregarRelatorios() {
            document.getElementById('page-title').textContent = 'Relatórios ';
            document.getElementById('stats-cards').innerHTML = '';

            document.getElementById('content-body').innerHTML = `
                <div class="row mb-4">
                    <!-- Filtros Avançados -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="bi bi-funnel"></i> Filtros Avançados</h5>
                                <div class="btn-group">
                                    <button class="btn btn-outline-success btn-sm" onclick="exportarRelatorio('excel')">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="exportarRelatorio('pdf')">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="imprimirRelatorio()">
                                        <i class="bi bi-printer"></i> Imprimir
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="formFiltroRelatorio" class="row g-3">
                                    <div class="col-md-2">
                                        <label for="relatorioTipo" class="form-label">Tipo de Relatório</label>
                                        <select class="form-select" id="relatorioTipo" name="tipo">
                                            
                                            <option value="usuarios">👥 Usuários</option>
                                            <option value="solicitacoes">📋 Solicitações</option>
                                            <option value="propostas">💼 Propostas</option>
                                            <option value="avaliacoes">⭐ Avaliações</option>
                                            <option value="financeiro">💰 Financeiro</option>
                                           
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="relatorioPeriodo" class="form-label">Período</label>
                                        <select class="form-select" id="relatorioPeriodo" name="periodo" onchange="setPeriodoPersonalizado()">
                                            <option value="hoje">Hoje</option>
                                            <option value="ontem">Ontem</option>
                                            <option value="ultima_semana">Última Semana</option>
                                            <option value="ultimo_mes">Último Mês</option>
                                            <option value="ultimos_3_meses">Últimos 3 Meses</option>
                                            <option value="ultimo_ano">Último Ano</option>
                                            <option value="personalizado">📅 Personalizado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2" id="dataInicioContainer" style="display: none;">
                                        <label for="relatorioDataInicio" class="form-label">Data Início</label>
                                        <input type="date" class="form-control" id="relatorioDataInicio" name="data_inicio">
                                    </div>
                                    <div class="col-md-2" id="dataFimContainer" style="display: none;">
                                        <label for="relatorioDataFim" class="form-label">Data Fim</label>
                                        <input type="date" class="form-control" id="relatorioDataFim" name="data_fim">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="relatorioStatus" class="form-label">Status</label>
                                        <select class="form-select" id="relatorioStatus" name="status">
                                            <option value="">Todos</option>
                                            <option value="ativo">Ativos</option>
                                            <option value="inativo">Inativos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-search"></i> Gerar
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limparFiltroRelatorio()">
                                            <i class="bi bi-x"></i> Limpar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards de Resumo -->
                <div class="row mb-4" id="relatorio-cards">
                    <!-- Cards serão carregados aqui -->
                </div>

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

        // Implementar funções que faltavam
        function carregarStatusSolicitacao() {
            document.getElementById('page-title').textContent = 'Status de Solicitação';
            document.getElementById('stats-cards').innerHTML = '';
            
            // Carregar estatísticas primeiro
            fetch('../controllers/AdminController.class.php?acao=estatisticas_status_solicitacao')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarEstatisticasStatus(data.dados);
                    }
                })
                .catch(error => console.error('Erro:', error));
            
            document.getElementById('content-body').innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Gerenciar Status de Solicitação</h5>
                        <button type="button" class="btn btn-primary" onclick="abrirModalStatus()">
                            <i class="bi bi-plus"></i> Novo Status
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="filtroNomeStatus" placeholder="Buscar por nome...">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary" onclick="filtrarStatus()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div id="status-lista">
                            <!-- Lista carregada via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Modal para adicionar/editar status -->
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
