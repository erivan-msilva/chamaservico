<?php
$title = 'Avaliar Serviço - Cliente';
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="h3 mb-1">
                    <i class="bi bi-star text-warning me-2"></i>
                    Avaliar Serviço
                </h2>
                <p class="text-muted">Compartilhe sua experiência e ajude outros clientes</p>
            </div>

            <!-- Card de Avaliação -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-tools me-2"></i>
                        <?= htmlspecialchars($servico['titulo']) ?>
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <!-- Informações do Serviço -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-person-check text-primary me-2"></i>
                                    Prestador
                                </h6>
                                <p class="mb-1"><?= htmlspecialchars($servico['prestador_nome']) ?></p>
                                <small class="text-muted">Valor: R$ <?= number_format($servico['valor_aceito'], 2, ',', '.') ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-geo-alt text-success me-2"></i>
                                    Local do Serviço
                                </h6>
                                <p class="mb-0 small">
                                    <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?><br>
                                    <?= htmlspecialchars($servico['bairro']) ?> - <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Avaliação -->
                    <form method="POST" id="formAvaliacao">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">

                        <!-- Nota -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-star me-2"></i>
                                Qual sua nota para este serviço? *
                            </label>
                            <div class="star-rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?= $i ?>" name="nota" value="<?= $i ?>" required>
                                    <label for="star<?= $i ?>" class="star-label">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-text text-muted small"></div>
                        </div>

                        <!-- Comentário -->
                        <div class="mb-4">
                            <label for="comentario" class="form-label fw-bold">
                                <i class="bi bi-chat-text me-2"></i>
                                Conte-nos sobre sua experiência *
                            </label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                      required maxlength="500" 
                                      placeholder="Descreva como foi o atendimento, qualidade do trabalho, pontualidade..."></textarea>
                            <div class="form-text">
                                <span id="char-count">0</span>/500 caracteres
                            </div>
                        </div>

                        <!-- Recomendação -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="recomendaria" name="recomendaria" value="1">
                                <label class="form-check-label fw-bold" for="recomendaria">
                                    <i class="bi bi-heart text-danger me-2"></i>
                                    Eu recomendaria este prestador para outras pessoas
                                </label>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-warning btn-lg flex-fill">
                                <i class="bi bi-send me-2"></i>
                                Enviar Avaliação
                            </button>
                            <a href="/chamaservico/cliente/servicos/concluidos" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dicas -->
            <div class="card border-0 bg-light mt-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Dicas para uma boa avaliação
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Seja específico sobre o que gostou
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Mencione a pontualidade e profissionalismo
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Comente sobre a qualidade do trabalho
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Seja honesto e construtivo
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 5px;
    margin: 10px 0;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating .star-label {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: all 0.3s ease;
}

.star-rating .star-label:hover,
.star-rating .star-label:hover ~ .star-label,
.star-rating input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
    transform: scale(1.1);
}

.star-rating .star-label:hover {
    color: #ffb800;
}

@media (max-width: 768px) {
    .star-rating .star-label {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const comentario = document.getElementById('comentario');
    const charCount = document.getElementById('char-count');
    const ratingInputs = document.querySelectorAll('input[name="nota"]');
    const ratingText = document.querySelector('.rating-text');
    
    const ratingTexts = {
        1: '⭐ Muito ruim - Serviço não atendeu expectativas',
        2: '⭐⭐ Ruim - Precisa melhorar muito',
        3: '⭐⭐⭐ Regular - Atendeu parcialmente',
        4: '⭐⭐⭐⭐ Bom - Recomendo!',
        5: '⭐⭐⭐⭐⭐ Excelente - Superou expectativas!'
    };
    
    // Contador de caracteres
    comentario.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        if (this.value.length > 450) {
            charCount.parentElement.classList.add('text-warning');
        } else {
            charCount.parentElement.classList.remove('text-warning');
        }
    });
    
    // Texto da avaliação
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            ratingText.textContent = ratingTexts[this.value];
            ratingText.className = 'rating-text small fw-bold';
            
            if (this.value <= 2) {
                ratingText.classList.add('text-danger');
            } else if (this.value == 3) {
                ratingText.classList.add('text-warning');
            } else {
                ratingText.classList.add('text-success');
            }
        });
    });
    
    // Validação do formulário
    document.getElementById('formAvaliacao').addEventListener('submit', function(e) {
        const nota = document.querySelector('input[name="nota"]:checked');
        const comentarioValue = comentario.value.trim();
        
        if (!nota) {
            e.preventDefault();
            alert('Por favor, selecione uma nota de 1 a 5 estrelas.');
            return;
        }
        
        if (comentarioValue.length < 10) {
            e.preventDefault();
            alert('Por favor, escreva um comentário com pelo menos 10 caracteres.');
            comentario.focus();
            return;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
    
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
