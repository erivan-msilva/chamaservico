<?php
$title = 'Avaliar Serviço - Cliente';
ob_start();
?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/chamaservico/cliente/dashboard" class="text-decoration-none">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="/chamaservico/cliente/servicos/concluidos" class="text-decoration-none">Serviços Concluídos</a>
            </li>
            <li class="breadcrumb-item active">Avaliar Serviço</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="text-center mb-5">
        <div class="d-inline-flex align-items-center bg-warning text-dark px-4 py-2 rounded-pill mb-3">
            <i class="bi bi-star-fill me-2 fs-5"></i>
            <span class="fw-bold">Avaliar Serviço</span>
        </div>
        <h2 class="display-6 fw-bold text-dark mb-2">Como foi o trabalho realizado?</h2>
        <p class="text-muted fs-5">Sua avaliação ajuda outros clientes e motiva nossos prestadores</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Informações do Serviço -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informações do Serviço
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <h6 class="fw-bold text-primary"><?= htmlspecialchars($servico['titulo']) ?></h6>
                            <p class="text-muted mb-2"><?= htmlspecialchars($servico['tipo_servico_nome']) ?></p>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?> - 
                                <?= htmlspecialchars($servico['bairro']) ?>, <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="mb-2">
                                <small class="text-muted d-block">Valor Pago</small>
                                <strong class="text-success fs-5">R$ <?= number_format($servico['valor_aceito'], 2, ',', '.') ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Data do Serviço</small>
                                <strong><?= date('d/m/Y', strtotime($servico['data_aceite'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Prestador -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check me-2"></i>
                        Prestador de Serviços
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if ($servico['prestador_foto']): ?>
                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($servico['prestador_foto']) ?>" 
                                     class="rounded-circle" width="60" height="60" alt="Foto do prestador">
                            <?php else: ?>
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-person text-white fs-3"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($servico['prestador_nome']) ?></h6>
                            <p class="text-muted mb-0">Prestador de Serviços</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Avaliação -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-star-fill me-2"></i>
                        Sua Avaliação
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="formAvaliacao">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

                        <!-- Nota com Estrelas -->
                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5 mb-3">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                Como você avalia este serviço? *
                            </label>
                            <div class="star-rating text-center mb-3">
                                <input type="hidden" name="nota" id="nota" required>
                                <div class="stars" id="stars">
                                    <i class="bi bi-star star" data-rating="1"></i>
                                    <i class="bi bi-star star" data-rating="2"></i>
                                    <i class="bi bi-star star" data-rating="3"></i>
                                    <i class="bi bi-star star" data-rating="4"></i>
                                    <i class="bi bi-star star" data-rating="5"></i>
                                </div>
                                <div class="rating-text mt-2">
                                    <span id="rating-text" class="text-muted">Clique nas estrelas para avaliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Comentário -->
                        <div class="mb-4">
                            <label for="comentario" class="form-label fw-bold">
                                <i class="bi bi-chat-text me-2"></i>
                                Conte como foi sua experiência *
                            </label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="4" required
                                      placeholder="Descreva como foi o serviço, a pontualidade, qualidade do trabalho, atendimento..."></textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Seja honesto e construtivo. Sua avaliação ajuda outros clientes e o prestador a melhorar.
                            </div>
                        </div>

                        <!-- Recomendação -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="recomendaria" name="recomendaria" value="1">
                                <label class="form-check-label fw-bold" for="recomendaria">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>
                                    Eu recomendaria este prestador para outros clientes
                                </label>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/chamaservico/cliente/servicos/concluidos" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg" id="btnEnviarAvaliacao">
                                <i class="bi bi-send me-2"></i>
                                Enviar Avaliação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating .stars {
    font-size: 3rem;
    cursor: pointer;
}

.star-rating .star {
    color: #ddd;
    transition: color 0.2s;
    margin: 0 5px;
}

.star-rating .star:hover,
.star-rating .star.active {
    color: #ffc107;
}

.star-rating .star.active {
    color: #ff8c00;
}

.rating-text {
    font-size: 1.1rem;
    font-weight: 500;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const notaInput = document.getElementById('nota');
    const ratingText = document.getElementById('rating-text');
    const btnEnviar = document.getElementById('btnEnviarAvaliacao');
    
    const ratingTexts = {
        1: '⭐ Muito Ruim - Serviço não atendeu as expectativas',
        2: '⭐⭐ Ruim - Precisa melhorar muito',
        3: '⭐⭐⭐ Regular - Atendeu parcialmente',
        4: '⭐⭐⭐⭐ Bom - Serviço de qualidade',
        5: '⭐⭐⭐⭐⭐ Excelente - Superou as expectativas!'
    };
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            notaInput.value = rating;
            
            // Atualizar visual das estrelas
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('bi-star');
                    s.classList.add('bi-star-fill', 'active');
                } else {
                    s.classList.remove('bi-star-fill', 'active');
                    s.classList.add('bi-star');
                }
            });
            
            // Atualizar texto
            ratingText.textContent = ratingTexts[rating];
            ratingText.className = rating >= 4 ? 'text-success' : rating >= 3 ? 'text-warning' : 'text-danger';
            
            // Habilitar botão
            btnEnviar.disabled = false;
        });
        
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = this.dataset.rating;
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    // Reset hover
    document.getElementById('stars').addEventListener('mouseleave', function() {
        const currentRating = notaInput.value;
        stars.forEach((s, i) => {
            if (currentRating && i < currentRating) {
                s.style.color = '#ff8c00';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
    
    // Validação do formulário
    document.getElementById('formAvaliacao').addEventListener('submit', function(e) {
        const nota = notaInput.value;
        const comentario = document.getElementById('comentario').value.trim();
        
        if (!nota || nota < 1 || nota > 5) {
            e.preventDefault();
            alert('Por favor, selecione uma nota de 1 a 5 estrelas!');
            return;
        }
        
        if (comentario.length < 10) {
            e.preventDefault();
            alert('Por favor, escreva um comentário com pelo menos 10 caracteres!');
            return;
        }
        
        // Confirmar envio
        if (!confirm('Tem certeza que deseja enviar esta avaliação? Ela não poderá ser alterada depois.')) {
            e.preventDefault();
        }
    });
    
    // Inicialmente desabilitar botão
    btnEnviar.disabled = true;
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
