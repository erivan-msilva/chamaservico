<?php
$title = 'Título da Página - ChamaServiço';
ob_start();
?>

<div class="container-fluid py-4">
    <!-- Header da Página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-dark fw-bold">
                        <i class="bi bi-icon me-2 text-primary"></i>Título Principal
                    </h1>
                    <p class="text-muted mb-0">Subtítulo ou descrição da página</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Voltar
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Ação Principal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row">
        <div class="col-12">
            <!-- Cards ou conteúdo aqui -->
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
