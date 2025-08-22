<div class="admin-topbar">
    <div class="topbar-left">
        <button class="mobile-toggle" onclick="adminPanel.toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="breadcrumb-nav">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" id="adminBreadcrumb">
                    <li class="breadcrumb-item"><a href="#" data-page="dashboard">Admin</a></li>
                    <li class="breadcrumb-item active" id="currentPageBreadcrumb">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="topbar-center">
        <div class="search-box">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Buscar usuários, solicitações..." id="globalSearch">
                <button class="btn btn-outline-secondary" type="button" onclick="adminPanel.performGlobalSearch()">
                    Buscar
                </button>
            </div>
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- Notifications -->
        <div class="notification-dropdown">
            <div class="dropdown">
                <button class="notification-btn" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationCount">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu">
                    <div class="notification-header">
                        <h6>Notificações</h6>
                        <button class="btn btn-link btn-sm" onclick="adminPanel.markAllNotificationsRead()">
                            Marcar todas como lidas
                        </button>
                    </div>
                    <div class="notification-body" id="notificationList">
                        <!-- Notifications will be loaded here -->
                    </div>
                    <div class="notification-footer">
                        <a href="#" class="btn btn-link btn-sm" onclick="adminPanel.showAllNotifications()">
                            Ver todas as notificações
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="adminPanel.toggleTheme()" title="Alternar tema">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
        </button>
        
        <!-- Admin Menu -->
        <div class="admin-menu">
            <div class="dropdown">
                <button class="admin-menu-btn" type="button" data-bs-toggle="dropdown">
                    <div class="admin-avatar-sm">
                        <?= strtoupper(substr($_SESSION['admin_nome'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span class="admin-name"><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?></span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <div class="admin-info-dropdown">
                            <strong><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Administrator') ?></strong>
                            <small class="text-muted"><?= ucfirst($_SESSION['admin_nivel'] ?? 'admin') ?></small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showProfile()">
                        <i class="bi bi-person me-2"></i>Meu Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showSettings()">
                        <i class="bi bi-gear me-2"></i>Configurações
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showHelp()">
                        <i class="bi bi-question-circle me-2"></i>Ajuda
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="adminPanel.logout()">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showProfile()">
                        <i class="bi bi-person me-2"></i>Meu Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showSettings()">
                        <i class="bi bi-gear me-2"></i>Configurações
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="adminPanel.showHelp()">
                        <i class="bi bi-question-circle me-2"></i>Ajuda
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="adminPanel.logout()">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
