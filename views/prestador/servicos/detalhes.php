<?php
$title = 'Detalhes do Serviço - Prestador';
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="h3 mb-1">
                <i class="bi bi-clipboard-check text-primary me-2"></i>
                Detalhes do Serviço
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/chamaservico/prestador/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/chamaservico/prestador/servicos/andamento">Serviços em Andamento</a></li>
                    <li class="breadcrumb-item active">Detalhes</li>
                </ol>
            </nav>
        </div>
        <a href="/chamaservico/prestador/servicos/andamento" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar aos Serviços
        </a>
    </div>

    <?php if (empty($servico)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Serviço não encontrado ou você não tem permissão para visualizá-lo.
        </div>
    <?php else: ?>
        
        <!-- Card Principal do Serviço -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Informações do Serviço -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-tools me-2"></i>
                                <?= htmlspecialchars($servico['titulo']) ?>
                            </h5>
                            <span class="badge" style="background-color: <?= $servico['status_cor'] ?? '#6c757d' ?>;">
                                <?= htmlspecialchars($servico['status_nome'] ?? 'Status não definido') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary">Tipo de Serviço</h6>
                                <p class="mb-0"><?= htmlspecialchars($servico['tipo_servico_nome']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-success">Valor Acordado</h6>
                                <p class="mb-0 fs-5 fw-bold text-success">R$ <?= number_format($servico['valor'], 2, ',', '.') ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Prazo</h6>
                                <p class="mb-0"><?= $servico['prazo_execucao'] ? $servico['prazo_execucao'] . ' dia(s)' : 'Não informado' ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Data Preferencial</h6>
                                <p class="mb-0">
                                    <?php if ($servico['data_atendimento']): ?>
                                        <?= date('d/m/Y H:i', strtotime($servico['data_atendimento'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Não informada</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">Descrição do Serviço</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($servico['descricao'])) ?></p>
                        </div>

                        <!-- Urgência -->
                        <div class="mb-3">
                            <h6 class="fw-bold">Urgência</h6>
                            <?php
                            $urgenciaBadge = [
                                'baixa' => 'success',
                                'media' => 'warning', 
                                'alta' => 'danger'
                            ];
                            $urgenciaTexto = [
                                'baixa' => 'Baixa',
                                'media' => 'Média',
                                'alta' => 'Alta'
                            ];
                            ?>
                            <span class="badge bg-<?= $urgenciaBadge[$servico['urgencia']] ?? 'secondary' ?>">
                                <?= $urgenciaTexto[$servico['urgencia']] ?? 'Não informada' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Imagens do Serviço -->
                <?php if (!empty($servico['imagens'])): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-images me-2"></i>
                                Fotos do Serviço (<?= count($servico['imagens']) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <?php foreach ($servico['imagens'] as $imagem): ?>
                                    <div class="col-md-4">
                                        <div class="position-relative">
                                            <img src="/chamaservico/uploads/solicitacoes/<?= htmlspecialchars($imagem['caminho_imagem']) ?>" 
                                                 class="img-fluid rounded" 
                                                 style="height: 150px; width: 100%; object-fit: cover;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#modalImagem"
                                                 data-src="/chamaservico/uploads/solicitacoes/<?= htmlspecialchars($imagem['caminho_imagem']) ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Cliente -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person me-2"></i>
                            Informações do Cliente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h5 class="fw-bold"><?= htmlspecialchars($servico['cliente_nome']) ?></h5>
                        </div>
                        
                        <?php if (!empty($servico['cliente_telefone'])): ?>
                            <div class="d-grid mb-2">
                                <a href="tel:<?= htmlspecialchars($servico['cliente_telefone']) ?>" 
                                   class="btn btn-success btn-sm">
                                    <i class="bi bi-telephone me-2"></i>
                                    <?= htmlspecialchars($servico['cliente_telefone']) ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($servico['cliente_email'])): ?>
                            <div class="d-grid mb-2">
                                <a href="mailto:<?= htmlspecialchars($servico['cliente_email']) ?>" 
                                   class="btn btn-info btn-sm">
                                    <i class="bi bi-envelope me-2"></i>
                                    Enviar Email
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Local do Serviço
                        </h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-3">
                            <strong><?= htmlspecialchars($servico['logradouro']) ?>, <?= htmlspecialchars($servico['numero']) ?></strong><br>
                            <?php if ($servico['complemento']): ?>
                                <?= htmlspecialchars($servico['complemento']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($servico['bairro']) ?><br>
                            <?= htmlspecialchars($servico['cidade']) ?>/<?= htmlspecialchars($servico['estado']) ?><br>
                            CEP: <?= htmlspecialchars($servico['cep']) ?>
                        </address>
                        
                        <div class="d-grid">
                            <a href="https://www.google.com/maps/search/<?= urlencode($servico['logradouro'] . ', ' . $servico['numero'] . ', ' . $servico['bairro'] . ', ' . $servico['cidade'] . ', ' . $servico['estado']) ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-map me-2"></i>
                                Ver no Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Atualizar Status -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Atualizar Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/chamaservico/prestador/servicos/atualizar-status" id="formStatus">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                            <input type="hidden" name="proposta_id" value="<?= $servico['id'] ?>">

                            <div class="mb-3">
                                <label for="novo_status" class="form-label fw-bold">Novo Status</label>
                                <select class="form-select" id="novo_status" name="novo_status" required>
                                    <option value="">Selecione...</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="aguardando_materiais">Aguardando Materiais</option>
                                    <option value="suspenso">Suspenso</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações (opcional)</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                          placeholder="Adicione observações sobre o andamento do serviço..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Atualizar Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Visualizar Imagens -->
<div class="modal fade" id="modalImagem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imagem do Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagemModal" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal de imagens
    const modalImagem = document.getElementById('modalImagem');
    if (modalImagem) {
        const imagemModal = document.getElementById('imagemModal');
        
        modalImagem.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const src = trigger.getAttribute('data-src');
            imagemModal.src = src;
        });
    }

    // Validação do formulário de status
    const formStatus = document.getElementById('formStatus');
    if (formStatus) {
        formStatus.addEventListener('submit', function(e) {
            const novoStatus = document.getElementById('novo_status').value;
            
            if (!novoStatus) {
                e.preventDefault();
                alert('Por favor, selecione um novo status.');
                return;
            }

            // Confirmação para status "concluído"
            if (novoStatus === 'concluido') {
                if (!confirm('Tem certeza que deseja marcar este serviço como concluído? O cliente será notificado.')) {
                    e.preventDefault();
                    return;
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
