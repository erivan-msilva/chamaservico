<?php
$title = 'Serviços Concluídos - ChamaServiço';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Serviços Concluídos
                    </h2>
                    <p class="text-muted">Histórico de serviços finalizados com sucesso</p>
                </div>
                <div>
                    <a href="<?= url('cliente/dashboard') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar ao Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($servicosConcluidos)): ?>
                <!-- Estado vazio -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Nenhum serviço concluído ainda</h4>
                        <p class="text-muted">Quando você finalizar um serviço, ele aparecerá aqui.</p>
                        <a href="<?= url('cliente/solicitacoes/criar') ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Criar Nova Solicitação
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Lista de serviços concluídos -->
                <div class="row">
                    <?php foreach ($servicosConcluidos as $servico): ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-success text-white border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            Concluído
                                        </h6>
                                        <small><?= date('d/m/Y', strtotime($servico['data_solicitacao'])) ?></small>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title text-truncate" title="<?= htmlspecialchars($servico['titulo']) ?>">
                                        <?= htmlspecialchars($servico['titulo']) ?>
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-tools me-1"></i>
                                            <?= htmlspecialchars($servico['tipo_servico_nome']) ?>
                                        </span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>
                                            <strong>Prestador:</strong> <?= htmlspecialchars($servico['prestador_nome']) ?>
                                        </small>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-success fw-bold">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            Valor: R$ <?= number_format($servico['valor_pago'], 2, ',', '.') ?>
                                        </small>
                                    </div>

                                    <!-- Status da avaliação -->
                                    <?php if ($servico['avaliacao_id']): ?>
                                        <div class="alert alert-info border-0 py-2 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-star-fill text-warning me-2"></i>
                                                <div>
                                                    <small class="fw-bold">Avaliado</small><br>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $servico['avaliacao_nota']): ?>
                                                                <i class="bi bi-star-fill"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning border-0 py-2 mb-3">
                                            <small>
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <strong>Aguardando avaliação</strong>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="d-grid gap-2">
                                        <?php if (!$servico['avaliacao_id']): ?>
                                            <a href="<?= url('cliente/servicos/avaliar?id=' . $servico['id']) ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-star me-1"></i>
                                                Avaliar Serviço
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalhes<?= $servico['id'] ?>">
                                            <i class="bi bi-eye me-1"></i>
                                            Ver Detalhes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de detalhes -->
                        <div class="modal fade" id="modalDetalhes<?= $servico['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">
                                            <i class="bi bi-check-circle-fill me-2"></i>
                                            Detalhes do Serviço Concluído
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-primary">Informações do Serviço</h6>
                                                <p><strong>Título:</strong> <?= htmlspecialchars($servico['titulo']) ?></p>
                                                <p><strong>Tipo:</strong> <?= htmlspecialchars($servico['tipo_servico_nome']) ?></p>
                                                <p><strong>Descrição:</strong><br><?= nl2br(htmlspecialchars($servico['descricao'])) ?></p>
                                                <p><strong>Urgência:</strong> 
                                                    <?php
                                                    $urgenciaClass = ['baixa' => 'success', 'media' => 'warning', 'alta' => 'danger'];
                                                    $urgenciaText = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'];
                                                    ?>
                                                    <span class="badge bg-<?= $urgenciaClass[$servico['urgencia']] ?>">
                                                        <?= $urgenciaText[$servico['urgencia']] ?>
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-primary">Prestador</h6>
                                                <p><strong>Nome:</strong> <?= htmlspecialchars($servico['prestador_nome']) ?></p>
                                                <?php if ($servico['prestador_telefone']): ?>
                                                    <p><strong>Telefone:</strong> <?= htmlspecialchars($servico['prestador_telefone']) ?></p>
                                                <?php endif; ?>
                                                
                                                <h6 class="text-primary mt-3">Localização</h6>
                                                <p>
                                                    <?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?><br>
                                                    <?= htmlspecialchars($servico['bairro']) ?><br>
                                                    <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?>
                                                </p>
                                                
                                                <h6 class="text-success mt-3">Financeiro</h6>
                                                <p><strong>Valor Pago:</strong> R$ <?= number_format($servico['valor_pago'], 2, ',', '.') ?></p>
                                            </div>
                                        </div>

                                        <?php if ($servico['avaliacao_id']): ?>
                                            <hr>
                                            <h6 class="text-warning">
                                                <i class="bi bi-star-fill me-1"></i>
                                                Sua Avaliação
                                            </h6>
                                            <div class="mb-2">
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $servico['avaliacao_nota']): ?>
                                                            <i class="bi bi-star-fill"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                    <span class="ms-2"><?= $servico['avaliacao_nota'] ?>/5</span>
                                                </div>
                                            </div>
                                            <?php if ($servico['avaliacao_comentario']): ?>
                                                <p class="mb-0"><strong>Comentário:</strong><br><?= nl2br(htmlspecialchars($servico['avaliacao_comentario'])) ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        <?php if (!$servico['avaliacao_id']): ?>
                                            <a href="<?= url('cliente/servicos/avaliar?id=' . $servico['id']) ?>" 
                                               class="btn btn-warning">
                                                <i class="bi bi-star me-1"></i>
                                                Avaliar Agora
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Estatísticas rápidas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?= count($servicosConcluidos) ?></h3>
                                <p class="mb-0">Serviços Concluídos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-info text-white">
                            <div class="card-body text-center">
                                <h3>R$ <?= number_format(array_sum(array_column($servicosConcluidos, 'valor_pago')), 2, ',', '.') ?></h3>
                                <p class="mb-0">Total Investido</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-warning text-white">
                            <div class="card-body text-center">
                                <h3><?= count(array_filter($servicosConcluidos, function($s) { return $s['avaliacao_id']; })) ?></h3>
                                <p class="mb-0">Serviços Avaliados</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>