<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ChamaServiço' ?></title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Incluir configurações globais -->
    <?php
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
    }
    ?>
    <style>
        .navbar {
            box-shadow: 0 2px 12px rgba(40, 53, 121, 0.08);
            font-family: 'Inter', Arial, sans-serif;
            font-size: 1.05rem;
        }

        .navbar-brand {
            font-weight: bold;
            color: #f5a522 !important;
            /* amarelo sólido */
            font-size: 1.35rem;
            letter-spacing: 1px;
        }

        .card-login {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #283579 0%, #0a112e 100%);
        }

        .btn-primary {
            background: #f5a522;
            color: #fff !important;
            border-radius: 24px;
            border: none;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #d48c00;
            color: #fff;
        }

        .text-primary-custom {
            color: #b02a37 !important;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #4e5264;
        }

        .profile-img-sm {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border: 2px solid #f5a522;
            margin-right: 8px;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        /* Melhorias no menu */
        .navbar-nav .nav-link {
            transition: all 0.2s;
            border-radius: 10px;
            margin: 0 4px;
            padding: 10px 18px !important;
            font-weight: 500;
            font-size: 1.08rem;
            color: #fff !important;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            background: transparent !important;
            color: #f5a522 !important;
            text-decoration: none;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            content: "";
            display: block;
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 6px;
            height: 3px;
            background: #f5a522;
            border-radius: 2px;
            transition: width 0.2s;
        }

        .navbar-nav .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(40, 53, 121, 0.10);
            border: none;
            min-width: 220px;
            padding: 10px 0;
            font-size: 1rem;
        }

        .dropdown-item {
            padding: 10px 24px;
            transition: background 0.2s;
            border-radius: 8px;
            font-size: 1.01rem;
        }

        .dropdown-item:hover {
            background: transparent !important;
            color: #f5a522 !important;
            text-decoration: underline;
            text-underline-offset: 4px;
            text-decoration-thickness: 3px;
            text-decoration-color: #f5a522;
        }

        .dropdown-header {
            font-size: 1.05rem;
            color: #283579;
            font-weight: 600;
            padding-left: 24px;
        }

        .user-info {
            font-weight: 600;
            color: #fff;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1.05rem;
        }

        .notification-badge {
            background: #f44336cc !important;
            color: #fff !important;
            font-size: 0.95rem;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: 6px;
            animation: pulse 2s infinite;
        }

        /* NOVOS ESTILOS para notificações - LADO ESQUERDO */
        .notification-bell-left {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-badge-left {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545 !important;
            color: white !important;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .notification-badge-menu {
            background: #dc3545 !important;
            color: white !important;
            font-size: 0.75rem;
            padding: 3px 7px;
            border-radius: 12px;
            margin-left: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
            20%, 40%, 60%, 80% { transform: rotate(10deg); }
        }

        .bell-shake {
            animation: shake 0.8s ease-in-out;
        }

        @media (max-width: 991px) {
            .navbar-nav .nav-link {
                padding: 10px 12px !important;
                font-size: 1rem;
            }

            .user-info {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <?php if (Session::isLoggedIn()): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary sticky-top">
            <div class="container">
                <!-- Logo/Brand -->
                <a href="<?= Session::isLoggedIn() ? (Session::isCliente() ? '/chamaservico/cliente/dashboard' : '/chamaservico/prestador/dashboard') : '/chamaservico/' ?>" 
                   class="navbar-brand d-flex align-items-center" style="gap: 8px;">
                    <span style="display:inline-block;width:18px;height:18px;background:#f5a522;border-radius:50%;margin-right:6px;"></span>
                    <span class="fw-bold" style="letter-spacing:0.5px;color:#fff;">CHAMA</span>
                    <span class="fw-light" style="color:#f5a522;">SERVIÇO</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Menu Principal -->
                    <ul class="navbar-nav me-auto">
                        <!-- Dashboard/Início -->
                        <li class="nav-item">
                            <?php if (Session::isPrestador() && !Session::isCliente()): ?>
                                <a class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/prestador/dashboard') !== false ? ' active' : '' ?>" href="/chamaservico/prestador/dashboard">
                                    <i class="bi bi-speedometer2 fs-5"></i>Dashboard Prestador
                                </a>
                            <?php elseif (Session::isCliente()): ?>
                                <a class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/cliente/dashboard') !== false ? ' active' : '' ?>" href="/chamaservico/cliente/dashboard">
                                    <i class="bi bi-speedometer2 fs-5"></i>Dashboard Cliente
                                </a>
                            <?php else: ?>
                                <a class="nav-link<?= $_SERVER['REQUEST_URI'] === '/chamaservico/' ? ' active' : '' ?>" href="/chamaservico/">
                                    <i class="bi bi-house fs-5"></i>Início
                                </a>
                            <?php endif; ?>
                        </li>

                        <!-- Menu do Cliente -->
                        <?php if (Session::isCliente()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle<?= strpos($_SERVER['REQUEST_URI'], '/cliente/') !== false ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person fs-5"></i>Cliente
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-list-task me-1"></i>Solicitações</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/solicitacoes"><i class="bi bi-list me-1"></i>Minhas Solicitações</a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/solicitacoes/criar"><i class="bi bi-plus-circle me-1"></i>Nova Solicitação</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-inbox me-1"></i>Propostas</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/chamaservico/cliente/propostas/recebidas">
                                            <i class="bi bi-envelope me-1"></i>Propostas Recebidas
                                            <?php
                                            try {
                                                require_once 'models/Proposta.php';
                                                $propostaModel = new Proposta();
                                                $propostas = $propostaModel->buscarPropostasRecebidas(Session::getUserId(), ['status' => 'pendente']);
                                                $pendentes = count($propostas);
                                                if ($pendentes > 0): ?>
                                                    <span class="notification-badge"><?= $pendentes ?></span>
                                            <?php endif;
                                            } catch (Exception $e) {
                                            }
                                            ?>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Menu do Prestador -->
                        <?php if (Session::isPrestador()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle<?= strpos($_SERVER['REQUEST_URI'], '/prestador/') !== false ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-tools fs-5"></i>Prestador
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-graph-up me-1"></i>Gestão</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/servicos/andamento"><i class="bi bi-tools me-1"></i>Serviços em Andamento</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-briefcase me-1"></i>Trabalho</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/solicitacoes"><i class="bi bi-search me-1"></i>Buscar Serviços</a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/propostas"><i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Rápido -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-lightning fs-5"></i>Rápido
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (Session::isCliente()): ?>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/solicitacoes/criar"><i class="bi bi-plus-circle me-1 text-primary"></i><strong>Nova Solicitação</strong></a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/propostas/recebidas"><i class="bi bi-inbox me-1 text-warning"></i>Ver Propostas</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <?php if (Session::isPrestador()): ?>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/solicitacoes"><i class="bi bi-search me-1 text-success"></i><strong>Buscar Trabalhos</strong></a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/propostas"><i class="bi bi-file-earmark-text me-1 text-info"></i>Minhas Propostas</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/chamaservico/perfil"><i class="bi bi-person-gear me-1 text-secondary"></i>Editar Perfil</a></li>
                                <li><a class="dropdown-item" href="<?= Session::isPrestador() && !Session::isCliente() ? '/chamaservico/prestador/perfil/enderecos' : '/chamaservico/cliente/perfil/enderecos' ?>"><i class="bi bi-geo-alt me-1 text-secondary"></i>Meus Endereços</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Menu do Usuário -->
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center user-info" href="#" role="button" data-bs-toggle="dropdown">
                                <!-- NOVO: Container para sino e foto -->
                                <div class="d-flex align-items-center position-relative">
                                    <!-- SINO DE NOTIFICAÇÕES - LADO ESQUERDO -->
                                    <?php
                                    $notificacoesNaoLidas = 0;
                                    try {
                                        if (file_exists('models/Notificacao.php')) {
                                            require_once 'models/Notificacao.php';
                                            $notificacaoModel = new Notificacao();
                                            $userId = Session::getUserId();
                                            
                                            if (is_array($userId)) {
                                                $userId = $userId[0] ?? 0;
                                            }
                                            
                                            if ($userId) {
                                                $notificacoesNaoLidas = $notificacaoModel->contarNaoLidas($userId);
                                            }
                                        }
                                    } catch (Exception $e) {
                                        error_log("Erro ao contar notificações: " . $e->getMessage());
                                        $notificacoesNaoLidas = 0;
                                    }
                                    
                                    if ($notificacoesNaoLidas > 0): ?>
                                        <div class="notification-bell-left me-2" id="notificationBell">
                                            <i class="bi bi-bell-fill text-warning fs-5"></i>
                                            <span class="notification-badge-left" id="notificationCount"><?= $notificacoesNaoLidas ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="notification-bell-left me-2" id="notificationBell" style="display: none;">
                                            <i class="bi bi-bell-fill text-warning fs-5"></i>
                                            <span class="notification-badge-left" id="notificationCount">0</span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- FOTO DO USUÁRIO - LADO DIREITO -->
                                    <div class="position-relative">
                                        <?php
                                        $fotoPerfil = Session::get('foto_perfil');
                                        if ($fotoPerfil) {
                                            $fotoPerfil = basename($fotoPerfil);
                                            $caminhoCompleto = "uploads/perfil/" . $fotoPerfil;
                                            $arquivoExiste = file_exists($caminhoCompleto);
                                        }
                                        if ($fotoPerfil && $arquivoExiste):
                                        ?>
                                            <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>"
                                                class="rounded-circle profile-img-sm" alt="Foto do perfil"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                            <i class="bi bi-person-circle" style="font-size: 2rem; display: none;"></i>
                                        <?php else: ?>
                                            <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <span class="d-none d-md-inline ms-2">
                                    <?php
                                    $nomeCompleto = Session::getUserName() ?? '';
                                    $primeiroNome = explode(' ', trim($nomeCompleto))[0];
                                    echo htmlspecialchars($primeiroNome);
                                    ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-item-text">
                                        <div class="d-flex align-items-center">
                                            <?php if ($fotoPerfil && file_exists("uploads/perfil/" . $fotoPerfil)): ?>
                                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>"
                                                    class="rounded-circle me-2" width="40" height="40" alt="Foto do perfil"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                                <i class="bi bi-person-circle me-2" style="font-size: 2.5rem; color: #6c757d; display: none;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-person-circle me-2" style="font-size: 2.5rem; color: #6c757d;"></i>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars(Session::getUserName() ?? '') ?></div>
                                                <small class="text-muted">
                                                    <?php
                                                    $tipo = Session::getUserType();
                                                    $tipoLabel = [
                                                        'cliente' => 'Cliente',
                                                        'prestador' => 'Prestador',
                                                        'ambos' => 'Cliente & Prestador'
                                                    ];
                                                    echo $tipoLabel[$tipo] ?? ucfirst($tipo);
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/chamaservico/notificacoes">
                                        <i class="bi bi-bell me-2"></i>Notificações
                                        <span class="notification-badge-menu" id="notificationBadgeMenu" <?= $notificacoesNaoLidas > 0 ? '' : 'style="display: none;"' ?>>
                                            <?= $notificacoesNaoLidas ?>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header"><i class="bi bi-person-gear me-1"></i>Minha Conta</h6>
                                </li>
                                <li><a class="dropdown-item" href="/chamaservico/perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="<?= Session::isPrestador() && !Session::isCliente() ? '/chamaservico/prestador/perfil/enderecos' : '/chamaservico/cliente/perfil/enderecos' ?>"><i class="bi bi-geo-alt me-2"></i>Meus Endereços</a></li>
                                <?php if (Session::isCliente()): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-person me-1"></i>Cliente</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/propostas/recebidas"><i class="bi bi-inbox me-2"></i>Propostas Recebidas</a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/cliente/solicitacoes"><i class="bi bi-list-task me-2"></i>Minhas Solicitações</a></li>
                                <?php endif; ?>
                                <?php if (Session::isPrestador()): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-tools me-1"></i>Prestador</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/chamaservico/prestador/propostas"><i class="bi bi-file-earmark-text me-2"></i>Minhas Propostas</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="/chamaservico/logout"><i class="bi bi-box-arrow-right me-2"></i>Sair da Conta</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <!-- FIM Navbar -->
    <!-- Flash Messages -->
    <!-- Flash Messages centralizadas e temporárias -->
<?php if (Session::hasFlash('success')): ?>
    <?php $flash = Session::getFlash('success'); ?>
    <div id="alertCenter" class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show position-fixed top-50 start-50 translate-middle shadow-lg text-center" role="alert" style="z-index:1055; min-width:320px; max-width:90vw;">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('error')): ?>
    <?php $flash = Session::getFlash('error'); ?>
    <div id="alertCenter" class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show position-fixed top-50 start-50 translate-middle shadow-lg text-center" role="alert" style="z-index:1055; min-width:320px; max-width:90vw;">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<script>
    // Fechar alertas automaticamente após 5 segundos
    document.addEventListener('DOMContentLoaded', function () {
        const alertCenter = document.getElementById('alertCenter');
        if (alertCenter) {
            setTimeout(() => {
                alertCenter.classList.remove('show');
                setTimeout(() => alertCenter.remove(), 400); // Remover do DOM após animação
            }, 4000);
        }
    });
</script>

    <!-- Conteúdo Principal -->
    <main class="container my-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-auto py-3">
        <div class="container">
            <div class="row">
                
                    <span class="text-center">&copy; 2025 ChamaServiço. Todos os direitos reservados.</span>
                </div>
              
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para marcar item ativo no menu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar item ativo baseado na URL atual
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.includes(href) && href !== '/chamaservico/') {
                    link.classList.add('active');
                }
            });

            // Marcar dropdowns ativos
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && currentPath.includes(href) && href !== '/chamaservico/') {
                    item.closest('.dropdown').querySelector('.nav-link').classList.add('active');
                }
            });
        });
    </script>

    <script>
async function carregarEnderecosCard() {
    const card = document.getElementById('enderecoCard');
    const overlay = document.getElementById('enderecoCardOverlay');
    const content = document.getElementById('enderecoCardContent');
    
    // Mostrar card com carregamento
    card.style.display = 'block';
    overlay.style.display = 'block';
    
    // Resetar conteúdo para carregamento
    content.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2 text-muted">Carregando endereços...</p>
        </div>
    `;
    
    try {
        const response = await fetch('/chamaservico/cliente/perfil/enderecos?action=api_list');
        
        if (!response.ok) {
            throw new Error('Erro ao carregar endereços');
        }
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarEnderecos(data.enderecos);
        } else {
            throw new Error(data.mensagem || 'Erro desconhecido');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <h6 class="text-muted mt-2">Erro ao carregar endereços</h6>
                <p class="text-muted">${error.message}</p>
                <button class="btn btn-primary btn-sm" onclick="carregarEnderecosCard()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Tentar Novamente
                </button>
            </div>
        `;
    }
}

async function definirPrincipalCard(id) {
    if (confirm('Definir este endereço como principal?')) {
        try {
            const formData = new FormData();
            formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
            formData.append('acao', 'definir_principal');
            formData.append('endereco_id', id);
            
            const response = await fetch('/chamaservico/cliente/perfil/enderecos', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                // Recarregar a lista
                carregarEnderecosCard();
            } else {
                alert('Erro ao definir endereço principal: ' + data.mensagem);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao definir endereço principal');
        }
    }
}

async function excluirEnderecoCard(id) {
    if (confirm('Tem certeza que deseja excluir este endereço?')) {
        try {
            const formData = new FormData();
            formData.append('csrf_token', '<?= Session::generateCSRFToken() ?>');
            formData.append('acao', 'excluir');
            formData.append('endereco_id', id);
            
            const response = await fetch('/chamaservico/cliente/perfil/enderecos', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                // Recarregar a lista
                carregarEnderecosCard();
            } else {
                alert('Erro ao excluir endereço: ' + data.mensagem);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir endereço');
        }
    }
}
</script>

    <script>
// Sistema global de notificações em tempo real
let sistemaNotificacoes = {
    ultimaVerificacao: new Date().toISOString(),
    intervalo: null,
    ultimoContador: <?= $notificacoesNaoLidas ?>,
    
    iniciar() {
        // Verificar a cada 10 segundos
        this.intervalo = setInterval(() => {
            this.verificarNovas();
        }, 10000);
        
        // Verificar imediatamente após 5 segundos
        setTimeout(() => {
            this.verificarNovas();
        }, 5000);
    },
    
    parar() {
        if (this.intervalo) {
            clearInterval(this.intervalo);
            this.intervalo = null;
        }
    },
    
    async verificarNovas() {
        try {
            const response = await fetch('/chamaservico/notificacoes/contador');
            const data = await response.json();
            
            if (data.sucesso) {
                const novoContador = data.contador;
                
                // Se aumentou o contador, mostrar animações
                if (novoContador > this.ultimoContador) {
                    this.animarNovaNotificacao();
                    this.mostrarToastRapido();
                }
                
                this.atualizarBadges(novoContador);
                this.ultimoContador = novoContador;
            }
        } catch (error) {
            console.error('Erro ao verificar notificações:', error);
        }
    },
    
    atualizarBadges(contador) {
        const bell = document.getElementById('notificationBell');
        const count = document.getElementById('notificationCount');
        const menuBadge = document.getElementById('notificationBadgeMenu');
        
        if (contador > 0) {
            // Mostrar sino e contador
            if (bell) bell.style.display = 'flex';
            if (count) count.textContent = contador;
            if (menuBadge) {
                menuBadge.textContent = contador;
                menuBadge.style.display = 'inline-block';
            }
        } else {
            // Esconder sino
            if (bell) bell.style.display = 'none';
            if (menuBadge) menuBadge.style.display = 'none';
        }
    },
    
    animarNovaNotificacao() {
        const bell = document.getElementById('notificationBell');
        const icon = bell?.querySelector('.bi-bell-fill');
        
        if (icon) {
            icon.classList.add('bell-shake');
            setTimeout(() => {
                icon.classList.remove('bell-shake');
            }, 800);
        }
    },
    
    mostrarToastRapido() {
        // Toast discreto para nova notificação
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <i class="bi bi-bell-fill text-warning me-2"></i>
                    <strong class="me-auto">Nova Notificação</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Você tem uma nova notificação!
                </div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remover
        setTimeout(() => {
            toast.remove();
        }, 4000);
    }
};

// CSS para animação do sininho
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
        20%, 40%, 60%, 80% { transform: rotate(10deg); }
    }
    
    .notification-badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Só inicializar se o usuário estiver logado
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        sistemaNotificacoes.iniciar();
    }
});

// Parar quando sair da página
window.addEventListener('beforeunload', function() {
    sistemaNotificacoes.parar();
});
</script>

    <?= $scripts ?? '' ?>
</body>

</html>