<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ChamaServiço' ?></title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --cor-primaria: #283579;
            /* Azul escuro */
            --cor-secundaria: #f5a522;
            /* Amarelo/dourado */
            --cor-branco: #ffffff;
            /* Branco */
            --cor-cinza-claro: #f8f9fa;
            /* Cinza claro */
            --cor-texto: #212529;
            /* Texto escuro */
            --cor-hover: #1e2a5f;
            /* Azul mais escuro para hover */
            --cor-dourado-hover: #d48c00;
            /* Dourado mais escuro para hover */
            --cor-nav-text: rgba(255, 255, 255, 0.9);
            /* Texto nav normal */
            --cor-nav-active: #ffffff;
            /* Texto nav ativo */
            --cor-nav-hover: rgba(255, 255, 255, 0.75);
            /* Texto nav hover */
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--cor-cinza-claro);
            color: var(--cor-texto);
        }

        /* Reset básico para altura total */
        html {
            height: 100%;
        }

        /* ========================================
           NAVBAR SIMPLIFICADA - SEM GRADIENTES
           ======================================== */
        .navbar {
            background-color: var(--cor-primaria) !important;
            box-shadow: 0 2px 10px rgba(40, 53, 121, 0.15);
            border-bottom: 2px solid var(--cor-secundaria);
            padding: 1rem 0;
            min-height: 70px;
        }

        /* LOGO - Limpo e Simples */
        .navbar-brand {
            font-weight: 700;
            color: var(--cor-branco) !important;
            font-size: 1.4rem;
            letter-spacing: 1px;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
            color: var(--cor-branco) !important;
        }

        .navbar-brand .logo-circle {
            width: 18px;
            height: 18px;
            background-color: var(--cor-secundaria);
            border-radius: 50%;
            margin-right: 10px;
            display: inline-block;
            vertical-align: middle;
        }

        .navbar-brand .brand-text {
            color: var(--cor-branco);
            font-weight: 700;
        }

        .navbar-brand .brand-accent {
            color: var(--cor-secundaria);
            font-weight: 300;
            margin-left: 2px;
        }

        /* NAVEGAÇÃO PRINCIPAL - Links Limpos */
        .navbar-nav {
            gap: 2rem;
        }

        .navbar-nav .nav-link {
            color: var(--cor-nav-text) !important;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 0 !important;
            margin: 0;
            border: none;
            background: none !important;
            border-radius: 0;
            position: relative;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        /* Estado Normal */
        .navbar-nav .nav-link:hover {
            color: var(--cor-nav-hover) !important;
            transform: translateY(-1px);
        }

        /* Estado Ativo - Sublinhado Amarelo */
        .navbar-nav .nav-link.active {
            color: var(--cor-nav-active) !important;
            font-weight: 600;
        }

        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--cor-secundaria);
            border-radius: 2px 2px 0 0;
        }

        /* Dropdown da Navegação */
        .navbar-nav .dropdown-toggle::after {
            margin-left: 0.5rem;
            opacity: 0.7;
        }

        .dropdown-menu {
            background-color: var(--cor-branco);
            border: 1px solid rgba(40, 53, 121, 0.1);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(40, 53, 121, 0.15);
            padding: 0.75rem 0;
            min-width: 220px;
            margin-top: 0.5rem;
        }

        .dropdown-header {
            color: var(--cor-primaria);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.5rem 1.25rem 0.25rem;
            border-bottom: 1px solid rgba(245, 165, 34, 0.2);
            margin-bottom: 0.5rem;
        }

        .dropdown-item {
            color: var(--cor-texto);
            padding: 0.6rem 1.25rem;
            border-radius: 0;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .dropdown-item:hover {
            background-color: rgba(245, 165, 34, 0.1);
            color: var(--cor-primaria);
            transform: translateX(3px);
        }

        .dropdown-item i {
            width: 18px;
            margin-right: 0.5rem;
        }

        /* ÁREA DO USUÁRIO - Status, Não Botão */
        .user-area {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--cor-nav-text);
        }

        /* Sino de Notificações */
        .notification-area {
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-area:hover {
            transform: translateY(-1px);
        }

        .notification-bell {
            font-size: 1.4rem;
            color: var(--cor-nav-text);
            transition: all 0.3s ease;
        }

        .notification-area:hover .notification-bell {
            color: var(--cor-secundaria);
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background-color: #dc3545 !important;
            color: var(--cor-branco) !important;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid var(--cor-primaria);
            animation: pulse 2s infinite;
        }

        /* Dropdown Toggle do Usuário */
        .user-dropdown {
            color: var(--cor-nav-text) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
            padding: 0 !important;
            border: none !important;
            background: none !important;
        }

        .user-dropdown:hover {
            color: var(--cor-nav-active) !important;
            transform: translateY(-1px);
        }

        .user-dropdown::after {
            margin-left: 0.5rem;
            opacity: 0.8;
        }

        /* Avatar do Usuário */
        .user-avatar {
            width: 38px;
            height: 38px;
            border: 2px solid rgba(245, 165, 34, 0.3);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .user-dropdown:hover .user-avatar {
            border-color: var(--cor-secundaria);
        }

        .user-avatar-placeholder {
            width: 38px;
            height: 38px;
            background-color: rgba(245, 165, 34, 0.2);
            color: var(--cor-secundaria);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 2px solid rgba(245, 165, 34, 0.3);
            transition: all 0.3s ease;
        }

        .user-dropdown:hover .user-avatar-placeholder {
            background-color: rgba(245, 165, 34, 0.3);
            border-color: var(--cor-secundaria);
        }

        /* BOTÕES SIMPLIFICADOS */
        .btn-primary {
            background-color: var(--cor-secundaria);
            border-color: var(--cor-secundaria);
            color: var(--cor-primaria);
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--cor-dourado-hover);
            border-color: var(--cor-dourado-hover);
            color: var(--cor-branco);
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid var(--cor-secundaria);
            color: var(--cor-secundaria);
            background-color: transparent;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--cor-secundaria);
            color: var(--cor-primaria);
            border-color: var(--cor-secundaria);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--cor-primaria);
            border-color: var(--cor-primaria);
            color: var(--cor-branco);
            border-radius: 8px;
        }

        .btn-secondary:hover {
            background-color: var(--cor-hover);
            border-color: var(--cor-hover);
            color: var(--cor-branco);
        }

        /* BADGES SIMPLIFICADOS */
        .notification-badge-menu {
            background-color: #dc3545 !important;
            color: var(--cor-branco) !important;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 0.5rem;
        }

        /* CARDS LIMPOS */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(40, 53, 121, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 53, 121, 0.15);
        }

        .card-header {
            background-color: var(--cor-primaria);
            color: var(--cor-branco);
            border: none;
            padding: 20px;
            font-weight: 600;
        }

        /* ALERTAS SIMPLIFICADOS */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--cor-secundaria);
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid var(--cor-primaria);
        }

        /* CORES UTILITÁRIAS */
        .text-primary {
            color: var(--cor-primaria) !important;
        }

        .text-secondary {
            color: var(--cor-secundaria) !important;
        }

        .bg-primary {
            background-color: var(--cor-primaria) !important;
        }

        .bg-secondary {
            background-color: var(--cor-secundaria) !important;
        }

        /* Footer */
        footer {
            background-color: var(--cor-primaria);
            color: var(--cor-branco);
            border-top: 3px solid var(--cor-secundaria);
            flex-shrink: 0;
            margin-top: auto; /* Empurra o footer para baixo quando necessário */
        }

        /* ANIMAÇÕES MANTIDAS */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: rotate(-10deg);
            }

            20%,
            40%,
            60%,
            80% {
                transform: rotate(10deg);
            }
        }

        .bell-shake {
            animation: shake 0.8s ease-in-out;
        }

        /* RESPONSIVIDADE */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0;
            }

            .navbar-nav {
                gap: 0;
                margin-top: 1rem;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .navbar-nav .nav-link.active::after {
                left: 1rem;
                width: calc(100% - 2rem);
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .user-area {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                justify-content: center;
            }
        }

        /* ACESSIBILIDADE */
        .btn:focus,
        .nav-link:focus,
        .dropdown-item:focus {
            outline: 2px solid var(--cor-secundaria);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <!-- Navbar Simplificada -->
    <?php if (Session::isLoggedIn()): ?>
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container">
                <!-- Logo Limpo - CORREÇÃO: Usar url() helper -->
                <a href="<?= url(Session::isLoggedIn() ? (Session::isCliente() ? '/cliente/dashboard' : '/prestador/dashboard') : '/') ?>"
                    class="navbar-brand d-flex align-items-center">
                    <span class="logo-circle"></span>
                    <span class="brand-text">CHAMA</span>
                    <span class="brand-accent">SERVIÇO</span>
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Navegação Principal - CORREÇÃO: URLs com BASE_URL -->
                    <ul class="navbar-nav me-auto">
                        <!-- Dashboard/Início -->
                        <li class="nav-item">
                            <?php if (Session::isPrestador() && !Session::isCliente()): ?>
                                <a class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/prestador/dashboard') !== false ? ' active' : '' ?>"
                                    href="<?= url('prestador/dashboard') ?>">
                                    Dashboard
                                </a>
                            <?php elseif (Session::isCliente()): ?>
                                <a class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/cliente/dashboard') !== false ? ' active' : '' ?>"
                                    href="<?= url('cliente/dashboard') ?>">
                                    Dashboard
                                </a>
                            <?php endif; ?>
                        </li>

                        <!-- Menu do Cliente -->
                        <?php if (Session::isCliente()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle<?= strpos($_SERVER['REQUEST_URI'], '/cliente/') !== false ? ' active' : '' ?>"
                                    href="#" role="button" data-bs-toggle="dropdown">
                                    Cliente
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header">Solicitações</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="<?= url('cliente/solicitacoes') ?>">
                                            <i class="bi bi-list"></i>Minhas Solicitações
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= url('cliente/solicitacoes/criar') ?>">
                                            <i class="bi bi-plus-circle"></i>Nova Solicitação
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header">Propostas</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('cliente/propostas/recebidas') ?>">
                                            <i class="bi bi-envelope"></i>Propostas Recebidas
                                            <?php
                                            try {
                                                require_once 'models/Proposta.php';
                                                $propostaModel = new Proposta();
                                                $propostas = $propostaModel->buscarPropostasRecebidas(Session::getUserId(), ['status' => 'pendente']);
                                                $pendentes = count($propostas);
                                                if ($pendentes > 0): ?>
                                                    <span class="notification-badge-menu"><?= $pendentes ?></span>
                                            <?php endif;
                                            } catch (Exception $e) {
                                            }
                                            ?>
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header">Serviços</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('cliente/servicos/concluidos') ?>">
                                            <i class="bi bi-check-circle"></i>Serviços Concluídos
                                            <?php
                                            try {
                                                require_once 'models/SolicitacaoServico.php';
                                                $solicitacaoModel = new SolicitacaoServico();
                                                $servicosConcluidos = $solicitacaoModel->contarSolicitacoesPorUsuarioEStatus(Session::getUserId(), 5);
                                                if ($servicosConcluidos > 0): ?>
                                                    <span class="notification-badge-menu"><?= $servicosConcluidos ?></span>
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
                                <a class="nav-link dropdown-toggle<?= strpos($_SERVER['REQUEST_URI'], '/prestador/') !== false && strpos($_SERVER['REQUEST_URI'], '/prestador/dashboard') === false ? ' active' : '' ?>"
                                    href="#" role="button" data-bs-toggle="dropdown">
                                    Prestador
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header">Trabalho</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="<?= url('prestador/solicitacoes') ?>">
                                            <i class="bi bi-search"></i>Buscar Serviços
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= url('prestador/propostas') ?>">
                                            <i class="bi bi-file-earmark-text"></i>Minhas Propostas
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= url('prestador/servicos/andamento') ?>">
                                            <i class="bi bi-tools"></i>Serviços em Andamento
                                        </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Menu Rápido -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Rápido
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (Session::isCliente()): ?>
                                    <li><a class="dropdown-item" href="<?= url('cliente/solicitacoes/criar') ?>">
                                            <i class="bi bi-plus-circle"></i>Nova Solicitação
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= url('cliente/propostas/recebidas') ?>">
                                            <i class="bi bi-inbox"></i>Ver Propostas
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <?php if (Session::isPrestador()): ?>
                                    <li><a class="dropdown-item" href="<?= url('prestador/solicitacoes') ?>">
                                            <i class="bi bi-search"></i>Buscar Trabalhos
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= url('prestador/propostas') ?>">
                                            <i class="bi bi-file-earmark-text"></i>Minhas Propostas
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= url('/perfil') ?>">
                                        <i class="bi bi-person-gear"></i>Editar Perfil
                                    </a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Área do Usuário Simplificada -->
                    <div class="user-area">
                        <!-- Notificações -->
                        <?php
                        $notificacoesNaoLidas = 0;
                        try {
                            if (file_exists('models/Notificacao.php')) {
                                require_once 'models/Notificacao.php';
                                $notificacaoModel = new Notificacao();
                                $userId = Session::getUserId();
                                if (is_array($userId)) $userId = $userId[0] ?? 0;
                                if ($userId) $notificacoesNaoLidas = $notificacaoModel->contarNaoLidas($userId);
                            }
                        } catch (Exception $e) {
                            $notificacoesNaoLidas = 0;
                        }

                        if ($notificacoesNaoLidas > 0): ?>
                            <div class="notification-area" onclick="window.location.href='<?= BASE_URL ?>/notificacoes'">
                                <i class="bi bi-bell-fill notification-bell"></i>
                                <span class="notification-badge"><?= $notificacoesNaoLidas ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Dropdown do Usuário -->
                        <div class="dropdown">
                            <a class="user-dropdown d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <?php
                                $fotoPerfil = Session::get('foto_perfil');
                                if ($fotoPerfil) {
                                    $fotoPerfil = basename($fotoPerfil);
                                    $caminhoCompleto = "uploads/perfil/" . $fotoPerfil;
                                    $arquivoExiste = file_exists($caminhoCompleto);
                                }
                                if ($fotoPerfil && $arquivoExiste): ?>
                                    <img src="<?= BASE_URL ?>/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>"
                                        class="user-avatar" alt="Foto do perfil">
                                <?php else: ?>
                                    <div class="user-avatar-placeholder">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>

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
                                                <img src="<?= BASE_URL ?>/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>"
                                                    class="rounded-circle me-2" width="40" height="40" alt="Foto do perfil">
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
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/notificacoes">
                                        <i class="bi bi-bell"></i>Notificações
                                        <?php if ($notificacoesNaoLidas > 0): ?>
                                            <span class="notification-badge-menu"><?= $notificacoesNaoLidas ?></span>
                                        <?php endif; ?>
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header">Minha Conta</h6>
                                </li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">
                                        <i class="bi bi-person"></i>Meu Perfil
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?><?= Session::isPrestador() && !Session::isCliente() ? '/prestador/perfil/enderecos' : '/cliente/perfil/enderecos' ?>">
                                        <i class="bi bi-geo-alt"></i>Meus Endereços
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                                        <i class="bi bi-box-arrow-right"></i>Sair da Conta
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>

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
        document.addEventListener('DOMContentLoaded', function() {
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

    <!-- Footer com nova paleta -->
    <footer class="border-top mt-auto py-4">
        <div class="container text-center">
            <span>&copy; 2025 ChamaServiço. Todos os direitos reservados.</span>
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
                const response = await fetch('cliente/perfil/enderecos?action=api_list');

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

                    const response = await fetch('cliente/perfil/enderecos', {
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

                    const response = await fetch('/cliente/perfil/enderecos', {
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
                    // CORREÇÃO: Usar BASE_URL
                    const response = await fetch('<?= BASE_URL ?>/notificacoes/contador');
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