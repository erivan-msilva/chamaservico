<?php
//session_start();

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Definir página atual
$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Chama Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="/chamaservico/assets/admin/css/admin-styles.css" rel="stylesheet">
</head>

<body>
    <?php include 'layouts/sidebar.php'; ?>
    <?php // Incluir o modal, se ele estiver em um arquivo separado ou se ele precisar estar fora do main.
    // O modal para "Criar Admin" pode estar em um arquivo separado para ser incluído.
    // include 'partials/modal_criar_admin.php'; 
    ?>

    <main class="admin-main" id="adminMain">
        <header class="main-header">
            <div class="header-left">
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="h4 mb-0" id="pageTitle">Dashboard Administrativo</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/chamaservico/admin/dashboard">Admin</a></li>
                            <li class="breadcrumb-item active" id="breadcrumbCurrent">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="atualizarDados()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span class="d-none d-sm-inline ms-1">Atualizar</span>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="logout()">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline ms-1">Sair</span>
                </button>
            </div>
        </header>

        <div class="content-area">
            <div class="row g-4 mb-4" id="statsCards">
            </div>

            <div id="mainContent" class="fade-in">
            </div>
        </div>
    </main>

    <?php include 'layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="/chamaservico/assets/admin/js/admin-controller.js"></script>
    <script>
        // ... (seu código JavaScript aqui)
    </script>
</body>

</html>