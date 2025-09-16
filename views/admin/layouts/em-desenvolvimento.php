<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/login'));
    exit;
}

$currentPage = 'em-desenvolvimento';
$title = ($titulo ?? 'Funcionalidade') . ' - Em Desenvolvimento';

ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <h1 class="h2 text-dark">
        <?= htmlspecialchars($titulo ?? 'Funcionalidade em Desenvolvimento') ?>
    </h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-tools" style="font-size: 5rem; color: #667eea; margin-bottom: 2rem;"></i>
                <h3 class="text-muted mb-3">Em Desenvolvimento</h3>
                <p class="lead text-muted mb-4">
                    A funcionalidade <strong><?= htmlspecialchars($titulo ?? 'Esta funcionalidade') ?></strong> 
                    está sendo desenvolvida e estará disponível em breve.
                </p>
                
                <div class="alert alert-info text-start">
                    <h6><i class="bi bi-info-circle me-2"></i>Status do Desenvolvimento:</h6>
                    <ul class="mb-0">
                        <li>✅ Estrutura base criada</li>
                        <li>🔄 Interface em desenvolvimento</li>
                        <li>⏳ Funcionalidades sendo implementadas</li>
                        <li>🧪 Testes em andamento</li>
                    </ul>
                </div>
                
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?= url('admin/dashboard') ?>" class="btn btn-primary">
                        <i class="bi bi-speedometer2 me-1"></i>
                        Voltar ao Dashboard
                    </a>
                    <a href="<?= url('admin/usuarios') ?>" class="btn btn-success">
                        <i class="bi bi-people me-1"></i>
                        Gerenciar Usuários
                    </a>
                    <a href="<?= url('admin/solicitacoes') ?>" class="btn btn-info">
                        <i class="bi bi-list-task me-1"></i>
                        Ver Solicitações
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/admin/layouts/app.php';
?>
