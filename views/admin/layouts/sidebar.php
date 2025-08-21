<?php
// Verificar se existe sessão de admin
if (!isset($_SESSION['admin_id'])) {
    return;
}
?>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="/chamaservico/admin/dashboard" class="sidebar-brand">
            <i class="bi bi-shield-check me-2"></i>Admin Panel
        </a>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div class="sidebar-content">
        <div class="px-3 mb-3">
            <div class="d-flex align-items-center text-white-50 mb-3">
                <div class="avatar-sm bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2">
                    <i class="bi bi-person"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="small">Olá,</div>
                    <div class="fw-semibold text-white"><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?></div>
                </div>
            </div>
            
            <button class="btn btn-outline-light btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalCriarAdmin">
                <i class="bi bi-shield-plus me-2"></i>Novo Admin
            </button>
        </div>
        
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>" href="#" data-page="dashboard">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'tipos-servico' ? 'active' : '' ?>" href="#" data-page="tipos-servico">
                    <i class="bi bi-gear"></i>Tipos de Serviço
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'status-solicitacao' ? 'active' : '' ?>" href="#" data-page="status-solicitacao">
                    <i class="bi bi-flag"></i>Status Solicitação
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'usuarios' ? 'active' : '' ?>" href="#" data-page="usuarios">
                    <i class="bi bi-people"></i>Usuários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'relatorios' ? 'active' : '' ?>" href="#" data-page="relatorios">
                    <i class="bi bi-graph-up"></i>Relatórios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'monitor' ? 'active' : '' ?>" href="#" data-page="monitor">
                    <i class="bi bi-activity"></i>Monitor
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page ?? '') === 'configuracoes' ? 'active' : '' ?>" href="#" data-page="configuracoes">
                    <i class="bi bi-gear-fill"></i>Configurações
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="#" onclick="logout()">
                    <i class="bi bi-box-arrow-right"></i>Sair
                </a>
            </li>
        </ul>
        
        <!-- Info do Sistema -->
        <div class="sidebar-footer mt-auto p-3">
            <div class="text-white-50 small text-center">
                <div class="mb-1">ChamaServiço v1.0</div>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-success bg-opacity-25 text-success">Online</span>
                    <span class="badge bg-info bg-opacity-25 text-info"><?= date('H:i') ?></span>
                </div>
            </div>
        </div>
    </div>
</nav>
