<?php
$title = 'Erro interno - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center">
                <!-- Ícone de erro -->
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle mx-auto" 
                         style="width: 120px; height: 120px;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 4rem;"></i>
                    </div>
                </div>

                <!-- Título -->
                <h1 class="display-4 fw-bold text-danger mb-3">Erro Interno</h1>
                <h2 class="h4 text-muted mb-4">Ops! Algo deu errado no servidor</h2>

                <!-- Mensagem explicativa -->
                <div class="alert alert-danger border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-3 fs-4"></i>
                        <div class="text-start">
                            <strong>O que aconteceu:</strong>
                            <p class="mb-0 mt-2">
                                Ocorreu um erro interno no servidor. Nossa equipe foi notificada e está trabalhando para resolver o problema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ações recomendadas -->
                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-arrow-clockwise text-primary fs-1 mb-3"></i>
                                <h5 class="card-title">Tentar Novamente</h5>
                                <p class="card-text text-muted">Recarregue a página</p>
                                <button onclick="window.location.reload()" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Recarregar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-house text-success fs-1 mb-3"></i>
                                <h5 class="card-title">Página Inicial</h5>
                                <p class="card-text text-muted">Volte para o início</p>
                                <a href="/chamaservico/" class="btn btn-success">
                                    <i class="bi bi-house me-2"></i>
                                    Ir para Início
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações técnicas (apenas em modo debug) -->
                <?php if (defined('DEBUG_MODE') && DEBUG_MODE && isset($errorMessage)): ?>
                    <div class="mt-5">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-bug me-2"></i>
                                    Informações Técnicas (Debug)
                                </h6>
                            </div>
                            <div class="card-body text-start">
                                <code><?= htmlspecialchars($errorMessage ?? 'Erro não especificado') ?></code>
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
    border-left: 4px solid #dc3545;
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
