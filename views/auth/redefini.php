<?php
$title = 'Redefinir Senha - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <!-- Logo e Título -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center mb-3">
                    <span style="width: 20px; height: 20px; background-color: #f5a522; border-radius: 50%; margin-right: 10px;"></span>
                    <span class="h3 fw-bold" style="color: #283579;">CHAMA</span>
                    <span class="h3" style="color: #f5a522; font-weight: 300;">SERVIÇO</span>
                </div>
                <h1 class="h4 text-dark mb-2">Redefinir Senha</h1>
                <p class="text-muted">Insira seu email para receber instruções de redefinição</p>
            </div>

            <!-- Card de Redefinição -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">
                                <i class="bi bi-envelope me-2 text-primary"></i>
                                Email cadastrado *
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   placeholder="Digite seu email"
                                   autocomplete="email">
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>
                                Enviar Instruções
                            </button>
                        </div>
                    </form>

                    <!-- Links de Navegação -->
                    <div class="text-center">
                        <hr class="my-3">
                        <p class="mb-0">
                            Lembrou da senha? 
                            <a href="login" class="text-decoration-none fw-bold">
                                Fazer Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app_public.php';
?>

