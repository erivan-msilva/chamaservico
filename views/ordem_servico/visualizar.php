<?php
$title = 'Ordem de Serviço - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text me-2"></i>Ordem de Serviço #<?= htmlspecialchars($ordemServico['numero_os']) ?></h2>
            <div>
                <button type="button" class="btn btn-primary" onclick="downloadPDF()">
                    <i class="bi bi-download me-1"></i>Download PDF
                </button>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalEmail">
                    <i class="bi bi-envelope me-1"></i>Enviar por Email
                </button>
            </div>
        </div>

        <!-- Status da OS -->
        <div class="alert alert-<?= $ordemServico['status'] === 'finalizada' ? 'success' : 'warning' ?>">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Status:</strong> 
            <?php
            $statusLabels = [
                'pendente_assinatura' => 'Pendente de Assinatura',
                'finalizada' => 'Finalizada'
            ];
            echo $statusLabels[$ordemServico['status']] ?? ucfirst($ordemServico['status']);
            ?>
        </div>

        <!-- Dados da OS -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informações do Serviço</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número da OS:</strong> <?= htmlspecialchars($ordemServico['numero_os']) ?></p>
                        <p><strong>Título:</strong> <?= htmlspecialchars($ordemServico['titulo']) ?></p>
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($ordemServico['tipo_servico_nome']) ?></p>
                        <p><strong>Valor:</strong> R$ <?= number_format($ordemServico['valor_servico'], 2, ',', '.') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Data de Início:</strong> <?= date('d/m/Y H:i', strtotime($ordemServico['data_inicio'])) ?></p>
                        <p><strong>Data de Conclusão:</strong> <?= date('d/m/Y H:i', strtotime($ordemServico['data_conclusao'])) ?></p>
                        <p><strong>Prazo:</strong> <?= $ordemServico['prazo_execucao'] ?> dia(s)</p>
                        <p><strong>Urgência:</strong> <?= ucfirst($ordemServico['urgencia']) ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Cliente</h6>
                        <p><strong><?= htmlspecialchars($ordemServico['cliente_nome']) ?></strong></p>
                        <p><?= htmlspecialchars($ordemServico['cliente_email']) ?></p>
                        <?php if ($ordemServico['cliente_telefone']): ?>
                            <p><?= htmlspecialchars($ordemServico['cliente_telefone']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6>Prestador</h6>
                        <p><strong><?= htmlspecialchars($ordemServico['prestador_nome']) ?></strong></p>
                        <p><?= htmlspecialchars($ordemServico['prestador_email']) ?></p>
                        <?php if ($ordemServico['prestador_telefone']): ?>
                            <p><?= htmlspecialchars($ordemServico['prestador_telefone']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endereço do Serviço -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Local do Serviço</h5>
            </div>
            <div class="card-body">
                <address>
                    <?= htmlspecialchars($ordemServico['logradouro']) ?>, <?= htmlspecialchars($ordemServico['numero']) ?><br>
                    <?php if ($ordemServico['complemento']): ?>
                        <?= htmlspecialchars($ordemServico['complemento']) ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($ordemServico['bairro']) ?> - <?= htmlspecialchars($ordemServico['cidade']) ?>/<?= htmlspecialchars($ordemServico['estado']) ?><br>
                    CEP: <?= htmlspecialchars($ordemServico['cep']) ?>
                </address>
            </div>
        </div>

        <!-- Assinaturas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Assinaturas Digitais</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Cliente</h6>
                        <?php if ($ordemServico['assinatura_cliente']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Assinado em:</strong> <?= date('d/m/Y H:i', strtotime($ordemServico['data_assinatura_cliente'])) ?>
                            </div>
                        <?php else: ?>
                            <?php if ($ordemServico['cliente_id'] == Session::getUserId()): ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAssinatura">
                                    <i class="bi bi-pen me-1"></i>Assinar Digitalmente
                                </button>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-clock me-2"></i>Aguardando assinatura do cliente
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Prestador</h6>
                        <?php if ($ordemServico['assinatura_prestador']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Assinado em:</strong> <?= date('d/m/Y H:i', strtotime($ordemServico['data_assinatura_prestador'])) ?>
                            </div>
                        <?php else: ?>
                            <?php if ($ordemServico['prestador_id'] == Session::getUserId()): ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAssinatura">
                                    <i class="bi bi-pen me-1"></i>Assinar Digitalmente
                                </button>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-clock me-2"></i>Aguardando assinatura do prestador
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Enviar por Email -->
<div class="modal fade" id="modalEmail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar OS por Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ordem-servico/enviar-email">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="os_id" value="<?= $ordemServico['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email de Destino</label>
                        <input type="email" class="form-control" name="email" id="email" 
                               value="<?= htmlspecialchars($ordemServico['cliente_email']) ?>" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        A Ordem de Serviço será enviada em formato PDF anexado ao email.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Enviar Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Assinatura Digital -->
<div class="modal fade" id="modalAssinatura" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assinatura Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ordem-servico/assinar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="os_id" value="<?= $ordemServico['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="assinatura" class="form-label">Digite seu nome completo para assinar</label>
                        <input type="text" class="form-control" name="assinatura" id="assinatura" 
                               placeholder="Seu nome completo" required>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Ao assinar digitalmente, você confirma que concorda com todos os termos 
                        e condições desta Ordem de Serviço. Esta assinatura tem validade legal.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-pen me-1"></i>Assinar Digitalmente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
function downloadPDF() {
    window.open("ordem-servico/download?id=' . $ordemServico['id'] . '", "_blank");
}
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>
