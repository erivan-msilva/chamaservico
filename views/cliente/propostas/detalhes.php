<?php
$title = 'Detalhes da Proposta - ChamaServiço';
ob_start();
?>

<!-- Adicionar CSS customizado -->
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -0.6rem;
    top: 0.2rem;
    width: 0.8rem;
    height: 0.8rem;
    border-radius: 50%;
    background: #6c757d;
}
.timeline-item.active::before {
    background: #198754;
}
.chat-container {
    max-height: 300px;
    overflow-y: auto;
}
.message {
    margin-bottom: 1rem;
}
.message.own {
    text-align: right;
}
.message-bubble {
    display: inline-block;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    max-width: 70%;
}
.message.own .message-bubble {
    background: #007bff;
    color: white;
}
.message:not(.own) .message-bubble {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}
.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 0.5rem;
}
.image-gallery img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: transform 0.2s;
}
.image-gallery img:hover {
    transform: scale(1.05);
}
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text me-2"></i>Detalhes da Proposta</h2>
            <div>
                <!-- Adicionar botão de favoritar prestador -->
                <button class="btn btn-outline-warning me-2" onclick="toggleFavorito(<?= $proposta['prestador_id'] ?? 0 ?>)">
                    <i class="bi bi-star" id="favoritoIcon"></i>
                    <span id="favoritoText">Favoritar Prestador</span>
                </button>
                <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Voltar às Propostas
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Coluna Principal - Detalhes da Proposta -->
            <div class="col-md-8">
                <!-- Imagens da Solicitação -->
                <?php if (!empty($proposta['imagens_solicitacao'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-images me-2"></i>Imagens da Solicitação</h6>
                    </div>
                    <div class="card-body">
                        <div class="image-gallery">
                            <?php foreach($proposta['imagens_solicitacao'] as $imagem): ?>
                                <img src="/chamaservico/uploads/solicitacoes/<?= htmlspecialchars($imagem) ?>" 
                                     alt="Imagem da solicitação" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalImagem"
                                     onclick="mostrarImagem('<?= htmlspecialchars($imagem) ?>')">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-check me-2"></i>
                            Proposta para: <?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Proposta') ?>
                        </h5>
                        <small class="opacity-75">
                            Enviada em <?= date('d/m/Y às H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <!-- Informações Financeiras -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="text-muted mb-2">Valor da Proposta</h6>
                                    <h3 class="text-success mb-0">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="text-muted mb-2">Prazo de Execução</h6>
                                    <h4 class="text-primary mb-0">
                                        <?= ($proposta['prazo_execucao'] ?? 0) ?> dia<?= ($proposta['prazo_execucao'] ?? 0) != 1 ? 's' : '' ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição da Proposta -->
                        <div class="mb-4">
                            <h6><i class="bi bi-chat-left-text me-2"></i>Descrição da Proposta</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($proposta['descricao'] ?? 'Sem descrição')) ?></p>
                            </div>
                        </div>

                        <!-- Status da Proposta -->
                        <div class="mb-4">
                            <h6><i class="bi bi-info-circle me-2"></i>Status</h6>
                            <span class="badge fs-6 bg-<?= 
                                ($proposta['status'] ?? 'pendente') === 'aceita' ? 'success' : 
                                (($proposta['status'] ?? 'pendente') === 'recusada' ? 'danger' : 'warning') 
                            ?>">
                                <?php
                                $statusLabels = [
                                    'pendente' => 'Aguardando Resposta',
                                    'aceita' => 'Proposta Aceita',
                                    'recusada' => 'Proposta Recusada',
                                    'cancelada' => 'Proposta Cancelada'
                                ];
                                echo $statusLabels[$proposta['status'] ?? 'pendente'] ?? 'Status Indefinido';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Timeline do Status -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Histórico da Proposta</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item active">
                                <strong>Proposta Enviada</strong>
                                <br><small class="text-muted"><?= date('d/m/Y às H:i', strtotime($proposta['data_proposta'])) ?></small>
                            </div>
                            <?php if ($proposta['status'] === 'aceita'): ?>
                            <div class="timeline-item active">
                                <strong>Proposta Aceita</strong>
                                <br><small class="text-muted"><?= date('d/m/Y às H:i', strtotime($proposta['data_aceite'] ?? 'now')) ?></small>
                            </div>
                            <?php elseif ($proposta['status'] === 'recusada'): ?>
                            <div class="timeline-item">
                                <strong>Proposta Recusada</strong>
                                <br><small class="text-muted"><?= date('d/m/Y às H:i', strtotime($proposta['data_recusa'] ?? 'now')) ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Chat/Mensagens -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Conversa com o Prestador</h6>
                        <small class="text-muted">Mensagens em tempo real</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="chat-container p-3" id="chatContainer">
                            <!-- Mensagens serão carregadas via JavaScript -->
                            <div class="text-center text-muted">
                                <i class="bi bi-chat-square-dots"></i>
                                <p>Inicie uma conversa com o prestador</p>
                            </div>
                        </div>
                        <div class="border-top p-3">
                            <form id="formMensagem" class="d-flex gap-2">
                                <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">
                                <input type="text" class="form-control" name="mensagem" placeholder="Digite sua mensagem..." required>
                                <button type="button" class="btn btn-outline-secondary" onclick="anexarArquivo()">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Informações da Solicitação -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Sobre a Solicitação</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tipo de Serviço:</strong><br>
                                   <span class="text-primary"><?= htmlspecialchars($proposta['tipo_servico_nome'] ?? 'Não informado') ?></span>
                                </p>
                                <p><strong>Urgência:</strong><br>
                                    <span class="badge bg-<?= 
                                        ($proposta['urgencia'] ?? 'media') === 'alta' ? 'danger' : 
                                        (($proposta['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info') 
                                    ?>">
                                        <?= ucfirst($proposta['urgencia'] ?? 'média') ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($proposta['orcamento_estimado'])): ?>
                                    <p><strong>Orçamento Estimado:</strong><br>
                                       <span class="text-success">R$ <?= number_format($proposta['orcamento_estimado'], 2, ',', '.') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr>
                        <p><strong>Descrição da Solicitação:</strong></p>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($proposta['solicitacao_descricao'] ?? 'Sem descrição')) ?></p>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral - Prestador e Ações -->
            <div class="col-md-4">
                <!-- Informações do Prestador -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Prestador</h6>
                        <div class="d-flex align-items-center">
                            <!-- Avaliação do prestador -->
                            <div class="text-warning me-2">
                                <?php 
                                $avaliacao = $proposta['prestador_avaliacao'] ?? 0;
                                for($i = 1; $i <= 5; $i++): 
                                ?>
                                    <i class="bi bi-star<?= $i <= $avaliacao ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">(<?= $proposta['prestador_total_avaliacoes'] ?? 0 ?>)</small>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $fotoPrestador = $proposta['prestador_foto'] ?? null;
                        if ($fotoPrestador && file_exists("uploads/perfil/" . basename($fotoPrestador))):
                        ?>
                            <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($fotoPrestador)) ?>"
                                class="rounded-circle mb-3" width="80" height="80" 
                                style="object-fit: cover;" alt="Foto do prestador">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-person" style="font-size: 2rem; color: #6c757d;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h6 class="mb-1"><?= htmlspecialchars($proposta['prestador_nome'] ?? 'Prestador') ?></h6>
                        
                        <?php if (!empty($proposta['prestador_telefone'])): ?>
                            <p class="text-muted mb-2">
                                <i class="bi bi-telephone me-1"></i>
                                <?= htmlspecialchars($proposta['prestador_telefone']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($proposta['prestador_email'])): ?>
                            <p class="text-muted mb-3">
                                <i class="bi bi-envelope me-1"></i>
                                <small><?= htmlspecialchars($proposta['prestador_email']) ?></small>
                            </p>
                        <?php endif; ?>

                        <!-- Estatísticas do prestador -->
                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <div class="border-end">
                                    <strong class="d-block text-primary"><?= $proposta['prestador_servicos_concluidos'] ?? 0 ?></strong>
                                    <small class="text-muted">Serviços</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <strong class="d-block text-success"><?= number_format($proposta['prestador_avaliacao'] ?? 0, 1) ?></strong>
                                    <small class="text-muted">Nota</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <strong class="d-block text-warning"><?= $proposta['prestador_anos_experiencia'] ?? 0 ?>+</strong>
                                <small class="text-muted">Anos</small>
                            </div>
                        </div>

                        <!-- Botões de Contato -->
                        <div class="d-grid gap-2 mt-3">
                            <?php if (!empty($proposta['prestador_telefone'])): ?>
                                <a href="tel:<?= preg_replace('/\D/', '', $proposta['prestador_telefone']) ?>" 
                                   class="btn btn-success btn-sm">
                                    <i class="bi bi-telephone me-1"></i>Ligar
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($proposta['prestador_email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($proposta['prestador_email']) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-envelope me-1"></i>Enviar Email
                                </a>
                            <?php endif; ?>
                            
                            <!-- Novo botão para WhatsApp -->
                            <?php if (!empty($proposta['prestador_whatsapp'])): ?>
                                <a href="https://wa.me/55<?= preg_replace('/\D/', '', $proposta['prestador_whatsapp']) ?>?text=Olá! Vi sua proposta no ChamaServiço..." 
                                   class="btn btn-success btn-sm" target="_blank">
                                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Localização com Mapa -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Local do Serviço</h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            <strong><?= htmlspecialchars(($proposta['logradouro'] ?? '') . ', ' . ($proposta['numero'] ?? '')) ?></strong><br>
                            <?php if (!empty($proposta['complemento'])): ?>
                                <?= htmlspecialchars($proposta['complemento']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars(($proposta['bairro'] ?? '') . ' - ' . ($proposta['cidade'] ?? '') . ', ' . ($proposta['estado'] ?? '')) ?><br>
                            <small class="text-muted">CEP: <?= htmlspecialchars($proposta['cep'] ?? '') ?></small>
                        </address>
                        
                        <!-- Mapa interativo -->
                        <div class="mt-3">
                            <div id="mapa" style="height: 200px; background: #f8f9fa; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-map" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Mapa Interativo</p>
                                    <small>Clique para ver no Google Maps</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100 mt-2" onclick="abrirMapa()">
                                <i class="bi bi-geo-alt me-1"></i>Ver no Google Maps
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ações da Proposta -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Ações</h6>
                    </div>
                    <div class="card-body">
                        <?php if (($proposta['status'] ?? 'pendente') === 'pendente'): ?>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAceitar">
                                    <i class="bi bi-check-circle me-1"></i>Aceitar Proposta
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                                    <i class="bi bi-x-circle me-1"></i>Recusar Proposta
                                </button>
                                <hr>
                                <a href="/chamaservico/cliente/propostas/comparar?solicitacao_id=<?= $proposta['solicitacao_id'] ?? 0 ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-bar-chart me-1"></i>Comparar Propostas
                                </a>
                                <a href="/chamaservico/cliente/propostas/recebidas?solicitacao_id=<?= $proposta['solicitacao_id'] ?? 0 ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-list me-1"></i>Ver Todas as Propostas
                                </a>
                            </div>
                        <?php elseif (($proposta['status'] ?? 'pendente') === 'aceita'): ?>
                            <div class="alert alert-success text-center mb-3">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Proposta Aceita!</strong><br>
                                <small>Entre em contato com o prestador para combinar os detalhes.</small>
                            </div>
                            <div class="d-grid gap-2">
                                <?php if (!empty($proposta['prestador_telefone'])): ?>
                                    <a href="tel:<?= preg_replace('/\D/', '', $proposta['prestador_telefone']) ?>" 
                                       class="btn btn-success">
                                        <i class="bi bi-telephone me-1"></i>Ligar para o Prestador
                                    </a>
                                <?php endif; ?>
                                <a href="/chamaservico/cliente/solicitacoes/visualizar?id=<?= $proposta['solicitacao_id'] ?? 0 ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Ver Solicitação
                                </a>
                            </div>
                        <?php elseif (($proposta['status'] ?? 'pendente') === 'recusada'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-x-circle me-2"></i>
                                <strong>Proposta Recusada</strong><br>
                                <small>Esta proposta foi recusada.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Outras propostas do mesmo prestador -->
                <?php if (!empty($proposta['outras_propostas_prestador'])): ?>
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-collection me-2"></i>Outros Serviços do Prestador</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach($proposta['outras_propostas_prestador'] as $outra): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="fw-bold"><?= htmlspecialchars($outra['titulo']) ?></small>
                                <br><small class="text-success">R$ <?= number_format($outra['valor'], 2, ',', '.') ?></small>
                            </div>
                            <span class="badge bg-<?= $outra['status'] === 'concluido' ? 'success' : 'warning' ?>">
                                <?= ucfirst($outra['status']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar imagens -->
<div class="modal fade" id="modalImagem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imagem da Solicitação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagemModal" src="" class="img-fluid" alt="Imagem da solicitação">
            </div>
        </div>
    </div>
</div>

<!-- Modal para anexar arquivos -->
<div class="modal fade" id="modalAnexo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anexar Arquivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAnexo" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="arquivo" class="form-label">Selecione o arquivo:</label>
                        <input type="file" class="form-control" id="arquivo" name="arquivo" accept="image/*,.pdf,.doc,.docx">
                        <div class="form-text">Formatos aceitos: Imagens, PDF, DOC, DOCX (máx. 5MB)</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarAnexo()">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aceitar Proposta -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2 text-success"></i>Aceitar Proposta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/aceitar">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">
                    
                    <div class="alert alert-success">
                        <h6><i class="bi bi-info-circle me-2"></i>Confirmar Aceitação</h6>
                        <p>Você está prestes a aceitar a proposta de <strong><?= htmlspecialchars($proposta['prestador_nome']) ?></strong> no valor de <strong class="text-success">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></strong>.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">
                            <i class="bi bi-chat-text me-1"></i>Observações para o Prestador (Opcional)
                        </label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="4" 
                                 placeholder="Deixe uma mensagem para o prestador sobre detalhes específicos, horários preferenciais, etc."></textarea>
                        <div class="form-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Aproveite para informar horários preferenciais, detalhes específicos do serviço ou outras observações importantes.
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-bell me-2"></i>O que acontece após aceitar?</h6>
                        <ul class="mb-0">
                            <li>O prestador será <strong>notificado imediatamente</strong></li>
                            <li>Outras propostas serão automaticamente recusadas</li>
                            <li>O status do serviço mudará para "Em Andamento"</li>
                            <li>Você poderá acompanhar o progresso do serviço</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Confirmar e Aceitar Proposta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recusar Proposta -->
<div class="modal fade" id="modalRecusar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recusar Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/chamaservico/cliente/propostas/recusar">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">Tem certeza que deseja recusar esta proposta?</p>
                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label">Motivo da recusa (opcional):</label>
                        <textarea class="form-control" id="motivo_recusa" name="motivo_recusa" rows="3"
                                  placeholder="Explique o motivo da recusa para ajudar o prestador a melhorar suas propostas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?? 0 ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Confirmar Recusa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>

<?php
$scripts = '
<script>
// Função para mostrar imagem no modal
function mostrarImagem(nomeImagem) {
    document.getElementById("imagemModal").src = "/chamaservico/uploads/solicitacoes/" + nomeImagem;
}

// Função para anexar arquivo
function anexarArquivo() {
    new bootstrap.Modal(document.getElementById("modalAnexo")).show();
}

// Função para enviar anexo
function enviarAnexo() {
    const formData = new FormData(document.getElementById("formAnexo"));
    formData.append("proposta_id", ' . ($proposta['id'] ?? 0) . ');
    
    fetch("/chamaservico/api/anexar-arquivo", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalAnexo")).hide();
            carregarMensagens();
        }
    });
}

// Função para abrir mapa
function abrirMapa() {
    const endereco = "' . htmlspecialchars(($proposta['logradouro'] ?? '') . ', ' . ($proposta['numero'] ?? '') . ', ' . ($proposta['cidade'] ?? '') . ', ' . ($proposta['estado'] ?? '')) . '";
    const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(endereco)}`;
    window.open(url, "_blank");
}

// Função para favoritar prestador
function toggleFavorito(prestadorId) {
    fetch("/chamaservico/api/favoritar-prestador", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({prestador_id: prestadorId})
    })
    .then(response => response.json())
    .then(data => {
        const icon = document.getElementById("favoritoIcon");
        const text = document.getElementById("favoritoText");
        
        if(data.favoritado) {
            icon.className = "bi bi-star-fill";
            text.textContent = "Remover dos Favoritos";
        } else {
            icon.className = "bi bi-star";
            text.textContent = "Favoritar Prestador";
        }
    });
}

// Sistema de chat em tempo real
function carregarMensagens() {
    fetch("/chamaservico/api/mensagens?proposta_id=' . ($proposta['id'] ?? 0) . '")
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById("chatContainer");
        container.innerHTML = "";
        
        data.mensagens.forEach(msg => {
            const div = document.createElement("div");
            div.className = `message ${msg.eh_cliente ? "own" : ""}`;
            div.innerHTML = `
                <div class="message-bubble">
                    <div>${msg.mensagem}</div>
                    <small class="text-muted d-block mt-1">${msg.data_formatada}</small>
                </div>
            `;
            container.appendChild(div);
        });
        
        container.scrollTop = container.scrollHeight;
    });
}

// Enviar mensagem
document.getElementById("formMensagem").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("/chamaservico/api/enviar-mensagem", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            this.reset();
            carregarMensagens();
        }
    });
});

// Carregar mensagens ao abrir a página
document.addEventListener("DOMContentLoaded", function() {
    carregarMensagens();
    
    // Atualizar mensagens a cada 5 segundos
    setInterval(carregarMensagens, 5000);
});

// Notificações em tempo real
if ("Notification" in window && Notification.permission === "granted") {
    // Implementar WebSocket para notificações em tempo real
}

// ...existing code...
</script>
';
?>
