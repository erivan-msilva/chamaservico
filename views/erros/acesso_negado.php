<?php
$title = 'Acesso Negado - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center mb-5">
                <!-- Ícone de erro -->
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle mx-auto" 
                         style="width: 120px; height: 120px;">
                        <i class="bi bi-shield-exclamation" style="font-size: 4rem;"></i>
                    </div>
                </div>

                <!-- Título -->
                <h1 class="display-4 fw-bold text-danger mb-3">Acesso Negado</h1>
                <h2 class="h4 text-muted mb-4">Você não tem permissão para acessar esta página</h2>

                <!-- Mensagem explicativa -->
                <div class="alert alert-warning border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-warning me-3 fs-4"></i>
                        <div class="text-start">
                            <strong>Possíveis motivos:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Você não está logado no sistema</li>
                                <li>Sua sessão expirou</li>
                                <li>Você não tem o tipo de conta necessário para esta área</li>
                                <li>A página requer permissões específicas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Ações recomendadas -->
                <div class="row g-3 mt-4">
                    <?php if (!Session::isLoggedIn()): ?>
                        <!-- Usuário não logado -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-box-arrow-in-right text-primary fs-1 mb-3"></i>
                                    <h5 class="card-title">Fazer Login</h5>
                                    <p class="card-text text-muted">Entre com sua conta para acessar o sistema</p>
                                    <a href="/chamaservico/login" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Entrar
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-plus text-success fs-1 mb-3"></i>
                                    <h5 class="card-title">Criar Conta</h5>
                                    <p class="card-text text-muted">Não tem conta? Registre-se gratuitamente</p>
                                    <a href="/chamaservico/registro" class="btn btn-success">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Registrar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Usuário logado mas sem permissão -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-house text-primary fs-1 mb-3"></i>
                                    <h5 class="card-title">Ir para Dashboard</h5>
                                    <p class="card-text text-muted">Volte para sua área principal</p>
                                    <?php if (Session::isPrestador() && !Session::isCliente()): ?>
                                        <a href="/chamaservico/prestador/dashboard" class="btn btn-primary">
                                            <i class="bi bi-house me-2"></i>
                                            Dashboard Prestador
                                        </a>
                                    <?php elseif (Session::isCliente()): ?>
                                        <a href="/chamaservico/cliente/dashboard" class="btn btn-primary">
                                            <i class="bi bi-house me-2"></i>
                                            Dashboard Cliente
                                        </a>
                                    <?php else: ?>
                                        <a href="/chamaservico/" class="btn btn-primary">
                                            <i class="bi bi-house me-2"></i>
                                            Página Inicial
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-gear text-warning fs-1 mb-3"></i>
                                    <h5 class="card-title">Meu Perfil</h5>
                                    <p class="card-text text-muted">Verifique seu tipo de conta</p>
                                    <a href="/chamaservico/perfil" class="btn btn-warning">
                                        <i class="bi bi-person-gear me-2"></i>
                                        Ver Perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informações do usuário atual -->
                <?php if (Session::isLoggedIn()): ?>
                    <div class="mt-5">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Informações da sua conta
                                </h6>
                                <div class="row text-start">
                                    <div class="col-md-6">
                                        <strong>Nome:</strong> <?= htmlspecialchars(Session::getUserName()) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Tipo de conta:</strong> 
                                        <span class="badge bg-<?= Session::isCliente() ? 'primary' : 'success' ?>">
                                            <?php
                                            $tipo = Session::getUserType();
                                            $tipoLabel = [
                                                'cliente' => 'Cliente',
                                                'prestador' => 'Prestador',
                                                'ambos' => 'Cliente & Prestador'
                                            ];
                                            echo $tipoLabel[$tipo] ?? ucfirst($tipo);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Link para voltar -->
                <div class="mt-4">
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar à página anterior
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.alert {
    border-left: 4px solid #ffc107;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
