<?php
$title = 'Acesso Negado - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="text-center">
            <i class="bi bi-shield-exclamation text-warning" style="font-size: 5rem;"></i>
            <h1 class="h2 text-danger mb-3">Acesso Negado</h1>
            <p class="text-muted mb-4">
                Você não tem permissão para acessar esta área do sistema.
            </p>
            
            <div class="d-flex gap-2 justify-content-center">
                <a href="/chamaservico/solicitacoes" class="btn btn-primary">Voltar ao Início</a>
                <a href="/chamaservico/logout" class="btn btn-outline-secondary">Sair</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
