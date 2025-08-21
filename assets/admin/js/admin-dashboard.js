/**
 * Admin Dashboard Controller
 * Gerencia toda a funcionalidade do painel administrativo
 */
class AdminDashboard {
    constructor(initialPage = 'dashboard') {
        this.currentPage = initialPage;
        this.isMobile = window.innerWidth < 992;
        this.sidebar = document.getElementById('adminSidebar');
        this.main = document.getElementById('adminMain');
        this.overlay = document.getElementById('sidebarOverlay');
        this.refreshInterval = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupResponsive();
        this.loadPage(this.currentPage);
        this.startAutoRefresh();
    }

    setupEventListeners() {
        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileMenuToggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.closeSidebar());
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeSidebar());
        }

        // Navigation links
        document.querySelectorAll('.nav-link[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.page;
                this.loadPage(page);
                
                if (this.isMobile) {
                    this.closeSidebar();
                }
            });
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboard(e);
        });
    }

    setupResponsive() {
        window.addEventListener('resize', () => {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth < 992;
            
            if (wasMobile !== this.isMobile) {
                if (!this.isMobile) {
                    this.closeSidebar();
                }
            }
        });
    }

    startAutoRefresh() {
        // Auto-refresh apenas no dashboard
        this.refreshInterval = setInterval(() => {
            if (this.currentPage === 'dashboard') {
                this.refreshData();
            }
        }, window.adminConfig?.refreshInterval || 30000);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    toggleSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.toggle('show');
            this.overlay?.classList.toggle('show');
            document.body.style.overflow = this.sidebar.classList.contains('show') ? 'hidden' : '';
        }
    }

    closeSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.remove('show');
            this.overlay?.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    updateNavigation(page) {
        // Update active nav item
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-page="${page}"]`)?.classList.add('active');

        // Update page title and breadcrumb
        const titles = {
            'dashboard': 'Dashboard',
            'tipos-servico': 'Tipos de Serviço',
            'status-solicitacao': 'Status de Solicitação',
            'usuarios': 'Usuários',
            'relatorios': 'Relatórios',
            'monitor': 'Monitor do Sistema',
            'configuracoes': 'Configurações'
        };

        const pageTitle = document.getElementById('pageTitle');
        const breadcrumb = document.getElementById('breadcrumbCurrent');
        
        if (pageTitle) pageTitle.textContent = titles[page] || 'Dashboard';
        if (breadcrumb) breadcrumb.textContent = titles[page] || 'Dashboard';
    }

    showLoading() {
        return `
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-3 text-muted">Carregando conteúdo...</p>
            </div>
        `;
    }

    loadPage(page) {
        this.currentPage = page;
        this.updateNavigation(page);
        
        const mainContent = document.getElementById('mainContent');
        if (mainContent) {
            mainContent.innerHTML = this.showLoading();
        }
        
        // Stop auto-refresh quando não está no dashboard
        if (page !== 'dashboard') {
            this.stopAutoRefresh();
        } else {
            this.startAutoRefresh();
        }
        
        switch(page) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'usuarios':
                this.loadUsuarios();
                break;
            case 'tipos-servico':
                this.loadTiposServico();
                break;
            case 'status-solicitacao':
                this.loadStatusSolicitacao();
                break;
            case 'relatorios':
                this.loadRelatorios();
                break;
            case 'monitor':
                this.loadMonitor();
                break;
            case 'configuracoes':
                this.loadConfiguracoes();
                break;
            default:
                this.loadDashboard();
        }
    }

    loadDashboard() {
        this.renderStatsCards({
            total_usuarios: 150,
            total_clientes: 95,
            total_prestadores: 55,
            solicitacoes_hoje: 12,
            usuarios_ativos: 23
        });

        document.getElementById('mainContent').innerHTML = `
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-activity me-2 text-primary"></i>Atividade Recente</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <div class="activity-item">
                                    <div class="activity-icon bg-primary">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Novo usuário cadastrado</div>
                                        <div class="activity-time">2 minutos atrás</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon bg-success">
                                        <i class="bi bi-file-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Nova solicitação criada</div>
                                        <div class="activity-time">15 minutos atrás</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon bg-warning">
                                        <i class="bi bi-star"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Nova avaliação recebida</div>
                                        <div class="activity-time">1 hora atrás</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="content-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-gear me-2 text-info"></i>Status do Sistema</h5>
                        </div>
                        <div class="card-body">
                            <div class="system-status">
                                <div class="status-item">
                                    <span><i class="bi bi-server me-2"></i>Sistema</span>
                                    <span class="badge bg-success">Online</span>
                                </div>
                                <div class="status-item">
                                    <span><i class="bi bi-database me-2"></i>Banco de Dados</span>
                                    <span class="badge bg-success">Conectado</span>
                                </div>
                                <div class="status-item">
                                    <span><i class="bi bi-people me-2"></i>Usuários Ativos</span>
                                    <span class="badge bg-info">23</span>
                                </div>
                                <div class="status-item">
                                    <span><i class="bi bi-clock me-2"></i>Última Atualização</span>
                                    <small class="text-muted current-time">${new Date().toLocaleTimeString('pt-BR')}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Load actual data
        this.fetchDashboardData();
    }

    renderStatsCards(data) {
        const statsData = [
            {
                title: 'Total de Usuários',
                value: data.total_usuarios || 0,
                icon: 'bi-people',
                color: 'primary',
                gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                change: '+12%',
                changeType: 'positive'
            },
            {
                title: 'Clientes Ativos',
                value: data.total_clientes || 0,
                icon: 'bi-person-check',
                color: 'success',
                gradient: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                change: '+8%',
                changeType: 'positive'
            },
            {
                title: 'Prestadores',
                value: data.total_prestadores || 0,
                icon: 'bi-tools',
                color: 'warning',
                gradient: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                change: '+15%',
                changeType: 'positive'
            },
            {
                title: 'Solicitações Hoje',
                value: data.solicitacoes_hoje || 0,
                icon: 'bi-file-text',
                color: 'info',
                gradient: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                change: '+5%',
                changeType: 'positive'
            }
        ];

        const cardsHtml = statsData.map(stat => `
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card modern-card" style="--card-gradient: ${stat.gradient}">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon text-white">
                                    <i class="bi ${stat.icon}"></i>
                                </div>
                            </div>
                            <div class="stat-change ${stat.changeType}">
                                <i class="bi bi-${stat.changeType === 'positive' ? 'arrow-up' : 'arrow-down'}"></i>
                                ${stat.change}
                            </div>
                        </div>
                        <div>
                            <div class="stat-label">${stat.title}</div>
                            <div class="stat-number">${stat.value.toLocaleString('pt-BR')}</div>
                        </div>
                    </div>
                    <div class="card-overlay"></div>
                </div>
            </div>
        `).join('');

        document.getElementById('statsCards').innerHTML = cardsHtml;
    }

    loadUsuarios() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-people me-2 text-primary"></i>Gerenciar Usuários</h5>
                        <p class="mb-0 text-muted small">Administre todos os usuários do sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="exportarUsuarios()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Exportar
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCriarAdmin">
                            <i class="bi bi-shield-plus me-1"></i>Novo Admin
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros Modernos -->
                    <div class="filter-section mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="filtroNome" placeholder="Nome do usuário">
                                    <label for="filtroNome"><i class="bi bi-search me-1"></i>Filtrar por nome</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="filtroEmail" placeholder="E-mail do usuário">
                                    <label for="filtroEmail"><i class="bi bi-envelope me-1"></i>Filtrar por e-mail</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating">
                                    <select class="form-select" id="filtroTipo">
                                        <option value="">Todos</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                        <option value="cliente">Cliente</option>
                                        <option value="prestador">Prestador</option>
                                        <option value="ambos">Cliente/Prestador</option>
                                    </select>
                                    <label for="filtroTipo"><i class="bi bi-filter me-1"></i>Tipo</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating">
                                    <select class="form-select" id="filtroStatus">
                                        <option value="">Todos</option>
                                        <option value="ativo">Ativo</option>
                                        <option value="inativo">Inativo</option>
                                    </select>
                                    <label for="filtroStatus"><i class="bi bi-toggle-on me-1"></i>Status</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary h-100 w-100" onclick="filtrarUsuarios()">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="usuarios-lista">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Carregando usuários...</span>
                            </div>
                            <p class="text-muted">Carregando usuários...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Carregar lista de usuários
        this.filtrarUsuarios();
    }

    loadTiposServico() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-gear me-2 text-info"></i>Tipos de Serviço</h5>
                        <p class="mb-0 text-muted small">Configure os tipos de serviços disponíveis</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="novoTipoServico()">
                        <i class="bi bi-plus-circle me-1"></i>Novo Tipo
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Em desenvolvimento:</strong> Esta funcionalidade estará disponível em breve.
                    </div>
                </div>
            </div>
        `;
    }

    loadStatusSolicitacao() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-flag me-2 text-warning"></i>Status de Solicitação</h5>
                        <p class="mb-0 text-muted small">Gerencie os status das solicitações</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="novoStatus()">
                        <i class="bi bi-plus-circle me-1"></i>Novo Status
                    </button>
                </div>
                <div class="card-body">
                    <div class="filter-section mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="filtroNomeStatus" placeholder="Nome do status">
                                    <label for="filtroNomeStatus"><i class="bi bi-search me-1"></i>Filtrar por nome</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary h-100 w-100" onclick="filtrarStatus()">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="status-lista">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Carregando status...</span>
                            </div>
                            <p class="text-muted">Carregando status...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Carregar lista de status
        this.listarStatus();
    }

    loadRelatorios() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-graph-up me-2 text-success"></i>Relatórios Avançados</h5>
                        <p class="mb-0 text-muted small">Análises detalhadas e insights do sistema</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Em desenvolvimento:</strong> Módulo de relatórios em construção.
                    </div>
                </div>
            </div>
        `;
    }

    loadMonitor() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-activity me-2 text-danger"></i>Monitor do Sistema</h5>
                        <p class="mb-0 text-muted small">Monitoramento em tempo real</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Em desenvolvimento:</strong> Sistema de monitoramento em construção.
                    </div>
                </div>
            </div>
        `;
    }

    loadConfiguracoes() {
        document.getElementById('mainContent').innerHTML = `
            <div class="content-card modern-card">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-gear-fill me-2 text-secondary"></i>Configurações do Sistema</h5>
                        <p class="mb-0 text-muted small">Configurações gerais e preferências</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Em desenvolvimento:</strong> Painel de configurações em construção.
                    </div>
                </div>
            </div>
        `;
    }

    refreshData() {
        if (this.currentPage === 'dashboard') {
            this.fetchDashboardData();
        }
        
        // Update time display
        const timeElements = document.querySelectorAll('.current-time');
        timeElements.forEach(el => {
            el.textContent = new Date().toLocaleTimeString('pt-BR');
        });
    }

    handleKeyboard(e) {
        // ESC para fechar sidebar no mobile
        if (e.key === 'Escape' && this.isMobile) {
            this.closeSidebar();
        }
        
        // Atalhos do teclado (Ctrl + número)
        if (e.ctrlKey && !isNaN(e.key)) {
            const pageMap = {
                '1': 'dashboard',
                '2': 'usuarios',
                '3': 'tipos-servico',
                '4': 'status-solicitacao',
                '5': 'relatorios',
                '6': 'monitor'
            };
            
            if (pageMap[e.key]) {
                e.preventDefault();
                this.loadPage(pageMap[e.key]);
            }
        }
    }

    // Método para cleanup quando necessário
    destroy() {
        this.stopAutoRefresh();
        
        // Remove event listeners
        window.removeEventListener('resize', this.setupResponsive);
        document.removeEventListener('keydown', this.handleKeyboard);
    }
}

// Export para uso global
window.AdminDashboard = AdminDashboard;
