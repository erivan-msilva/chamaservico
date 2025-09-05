<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ChamaServiço' ?></title>
    <link rel="icon" type="image/x-icon" href="/chamaservico/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: #283579;
            --cor-secundaria: #f5a522;
            --cor-branco: #ffffff;
            --cor-cinza-claro: #f8f9fa;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--cor-primaria) !important;
            font-size: 1.4rem;
            text-decoration: none !important;
        }

        .btn-primary {
            background-color: var(--cor-primaria);
            border-color: var(--cor-primaria);
        }

        .btn-primary:hover {
            background-color: #1e2a5f;
            border-color: #1e2a5f;
        }

        .btn-outline-primary {
            color: var(--cor-primaria);
            border-color: var(--cor-primaria);
        }

        .btn-outline-primary:hover {
            background-color: var(--cor-primaria);
            border-color: var(--cor-primaria);
        }

        .text-primary {
            color: var(--cor-primaria) !important;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(40, 53, 121, 0.1);
        }

        .form-control:focus {
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 0.2rem rgba(40, 53, 121, 0.25);
        }

        .alert {
            border: none;
            border-radius: 12px;
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
    </style>
</head>

<body>
    <!-- Navbar simples para páginas públicas -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/chamaservico/">
                <span style="width: 20px; height: 20px; background-color: var(--cor-secundaria); border-radius: 50%; margin-right: 10px;"></span>
                <span class="fw-bold" style="color: var(--cor-primaria);">CHAMA</span>
                <span style="color: var(--cor-secundaria); font-weight: 300; margin-left: 2px;">SERVIÇO</span>
            </a>

            <div class="d-flex gap-2">
                <a href="/chamaservico/login" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                </a>
                <a href="/chamaservico/registro" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Criar Conta
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
        <?php $flash = Session::getFlash('success'); ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        <?php $flash = Session::getFlash('error'); ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('warning')): ?>
        <?php $flash = Session::getFlash('warning'); ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('info')): ?>
        <?php $flash = Session::getFlash('info'); ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Conteúdo Principal -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer simples -->
    <footer class="mt-auto py-3 text-center text-muted">
        <div class="container">
            <small>&copy; 2024 ChamaServiço. Todos os direitos reservados.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-hide alerts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-danger)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    <?= $scripts ?? '' ?>
</body>
</html>
