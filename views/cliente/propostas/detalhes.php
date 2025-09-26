<?php
$title = 'Detalhes da Proposta - ChamaServiço';
ob_start();
?>

<!-- CSS Customizado Melhorado -->
<style>
    /* Estilos Existentes */
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
        background: linear-gradient(to bottom, #007bff, #28a745);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        padding-left: 1rem;
        transition: all 0.3s ease;
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
        transition: all 0.3s ease;
        box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.1);
    }

    .timeline-item.active::before {
        background: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        transform: scale(1.2);
    }

    .timeline-item:hover {
        transform: translateX(5px);
    }

    /* Status do Prestador - NOVO */
    .prestador-avatar {
        position: relative;
        display: inline-block;
    }

    .status-indicator {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }

    .status-online {
        background-color: #28a745;
        animation: pulse-online 2s infinite;
    }

    .status-offline {
        background-color: #6c757d;
    }

    @keyframes pulse-online {
        0% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 0 0 0 rgba(40, 167, 69, 0.7);
        }

        50% {
            transform: scale(1.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 0 0 8px rgba(40, 167, 69, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Avaliações Interativas - NOVO */
    .rating-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .rating-tooltip {
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .rating-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
    }

    .rating-container:hover .rating-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-5px);
    }

    /* Estatísticas com Ícones - NOVO */
    .stat-item {
        position: relative;
        padding: 1rem;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #e9ecef;
    }

    .stat-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        border-color: #007bff;
    }

    .stat-icon {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        opacity: 0.4;
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }

    .stat-item:hover .stat-icon {
        opacity: 0.8;
        transform: scale(1.2);
    }

    /* Abas Melhoradas - NOVO */
    .nav-tabs {
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
        padding: 0.5rem 0.5rem 0;
        border: none;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        border: none;
        border-radius: 6px 6px 0 0;
        margin-right: 0.25rem;
        transition: all 0.3s ease;
        position: relative;
        background: transparent;
    }

    .nav-tabs .nav-link:hover {
        color: #495057;
        background-color: white;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: white;
        border-bottom: 3px solid #007bff;
        font-weight: 600;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Animação de Fade nas Abas - NOVO */
    .tab-pane {
        opacity: 0;
        transform: translateY(15px);
        transition: all 0.4s ease;
    }

    .tab-pane.show.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* Botões de Ação Melhorados - NOVO */
    .action-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .action-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transition: all 0.6s ease;
        transform: translate(-50%, -50%);
    }

    .action-button:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .action-button:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-success.action-button:hover {
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    /* Card Responsivo */
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

    .prestador-card-highlight {
        border: 2px solid #007bff;
        box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
        transition: all 0.3s ease;
    }

    .prestador-card-highlight:hover {
        transform: translateY(-3px);
        box-shadow: 0 1rem 2rem rgba(0, 123, 255, 0.2);
    }

    /* Modal Melhorado */
    .modal-content {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .confirmation-summary {
        background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
        border: 2px solid #28a745;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- Cabeçalho da Página -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                    Detalhes da Proposta
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= url('cliente/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('cliente/propostas/recebidas') ?>">Propostas</a></li>
                        <li class="breadcrumb-item active">Detalhes</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?= url('cliente/propostas/recebidas') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Voltar
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-md-8">
                <!-- Card Principal - Valores e Título da Proposta -->
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
                        <!-- Informações Financeiras em Destaque -->
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded border">
                                    <h6 class="text-muted mb-2">💰 Valor da Proposta</h6>
                                    <h2 class="text-success mb-0 fw-bold">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></h2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded border">
                                    <h6 class="text-muted mb-2">⏱️ Prazo de Execução</h6>
                                    <h3 class="text-primary mb-0 fw-bold">
                                        <?= ($proposta['prazo_execucao'] ?? 0) ?> dia<?= ($proposta['prazo_execucao'] ?? 0) != 1 ? 's' : '' ?>
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- Status da Proposta -->
                        <div class="text-center mt-3">
                            <span class="badge fs-6 px-3 py-2 bg-<?=
                                                                    ($proposta['status'] ?? 'pendente') === 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') === 'recusada' ? 'danger' : 'warning')
                                                                    ?>">
                                <?php
                                $statusLabels = [
                                    'pendente' => '⏳ Aguardando sua Resposta',
                                    'aceita' => '✅ Proposta Aceita',
                                    'recusada' => '❌ Proposta Recusada',
                                    'cancelada' => '🚫 Proposta Cancelada'
                                ];
                                echo $statusLabels[$proposta['status'] ?? 'pendente'] ?? 'Status Indefinido';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card do Prestador MELHORADO -->
                <div class="card shadow-sm mb-4 prestador-card-highlight">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Informações do Prestador
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <!-- Foto com Status Indicator -->
                                <div class="prestador-avatar mb-2">
                                    <?php
                                    $fotoPrestador = $proposta['prestador_foto'] ?? null;
                                    if ($fotoPrestador && file_exists("propostas/uploads/perfil/" . basename($fotoPrestador))):
                                    ?>
                                        <img src="uploads/perfil/<?= htmlspecialchars(basename($fotoPrestador)) ?>"
                                            class="rounded-circle" width="100" height="100"
                                            style="object-fit: cover; border: 3px solid #007bff;" alt="Foto do prestador">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                            style="width: 100px; height: 100px; border: 3px solid #6c757d;">
                                            <i class="bi bi-person" style="font-size: 3rem; color: #6c757d;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Status Indicator -->
                                    <div class="status-indicator status-online" title="Prestador Online"></div>
                                </div>

                                <!-- Avaliação Interativa -->
                                <div class="rating-container mb-1">
                                    <div class="text-warning">
                                        <?php
                                        $avaliacao = floatval($proposta['prestador_avaliacao'] ?? 0);
                                        for ($i = 1; $i <= 5; $i++):
                                        ?>
                                            <i class="bi bi-star<?= $i <= $avaliacao ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="rating-tooltip">
                                        Nota Média: <?= number_format($avaliacao, 1) ?> de 5<br>
                                        Baseado em <?= $proposta['prestador_total_avaliacoes'] ?? 0 ?> avaliações
                                    </div>
                                </div>
                                <small class="text-muted">(<?= $proposta['prestador_total_avaliacoes'] ?? 0 ?> avaliações)</small>
                            </div>

                            <div class="col-md-6">
                                <h4 class="mb-2 text-primary"><?= htmlspecialchars($proposta['prestador_nome'] ?? 'Prestador') ?></h4>

                                <div class="mb-2">
                                    <i class="bi bi-telephone text-success me-2"></i>
                                    <span><?= htmlspecialchars($proposta['prestador_telefone'] ?? 'Não informado') ?></span>
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-envelope text-info me-2"></i>
                                    <span><?= htmlspecialchars($proposta['prestador_email'] ?? 'Não informado') ?></span>
                                </div>

                                <!-- Estatísticas Melhoradas com Ícones -->
                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <i class="bi bi-check-all stat-icon text-primary"></i>
                                            <strong class="d-block text-primary fs-5"><?= $proposta['prestador_servicos_concluidos'] ?? 0 ?></strong>
                                            <small class="text-muted">Serviços</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <i class="bi bi-star stat-icon text-warning"></i>
                                            <strong class="d-block text-success fs-5"><?= number_format($proposta['prestador_avaliacao'] ?? 0, 1) ?></strong>
                                            <small class="text-muted">Nota</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <i class="bi bi-calendar-check stat-icon text-info"></i>
                                            <strong class="d-block text-warning fs-5"><?= $proposta['prestador_anos_experiencia'] ?? 0 ?>+</strong>
                                            <small class="text-muted">Anos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <!-- Botões de Contato -->
                                <div class="d-grid gap-2">
                                    <?php if (!empty($proposta['prestador_telefone']) && $proposta['prestador_telefone'] !== 'Não informado'): ?>
                                        <a href="tel:<?= preg_replace('/\D/', '', $proposta['prestador_telefone']) ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-telephone me-1"></i>Ligar
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($proposta['prestador_email']) && $proposta['prestador_email'] !== 'Não informado'): ?>
                                        <a href="mailto:<?= htmlspecialchars($proposta['prestador_email']) ?>"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-envelope me-1"></i>Email
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($proposta['prestador_whatsapp'])): ?>
                                        <a href="https://wa.me/55<?= preg_replace('/\D/', '', $proposta['prestador_whatsapp']) ?>?text=Olá! Vi sua proposta no ChamaServiço..."
                                            class="btn btn-success btn-sm" target="_blank">
                                            <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sistema de Abas MELHORADO -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <!-- Navegação das Abas Melhorada -->
                        <ul class="nav nav-tabs" id="propostaTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button" role="tab">
                                    <i class="bi bi-file-earmark-text me-2"></i>Detalhes da Proposta
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="solicitacao-tab" data-bs-toggle="tab" data-bs-target="#solicitacao" type="button" role="tab">
                                    <i class="bi bi-clipboard-check me-2"></i>Sobre a Solicitação
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="localizacao-tab" data-bs-toggle="tab" data-bs-target="#localizacao" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-2"></i>Local do Serviço
                                </button>
                            </li>
                        </ul>

                        <!-- Conteúdo das Abas -->
                        <div class="tab-content p-4" id="propostaTabContent">
                            <!-- Aba 1: Detalhes da Proposta -->
                            <div class="tab-pane fade show active" id="detalhes" role="tabpanel">
                                <!-- Descrição da Proposta -->
                                <div class="mb-4">
                                    <h6><i class="bi bi-chat-left-text me-2"></i>Descrição da Proposta</h6>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($proposta['descricao'] ?? 'Sem descrição')) ?></p>
                                    </div>
                                </div>

                                <!-- Timeline do Status -->
                                <div class="mb-4">
                                    <h6><i class="bi bi-clock-history me-2"></i>Histórico da Proposta</h6>
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

                                <!-- Ações da Proposta MELHORADAS -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6><i class="bi bi-gear me-2"></i>Ações</h6>
                                        <?php if (($proposta['status'] ?? 'pendente') === 'pendente'): ?>
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                                <button type="button" class="btn btn-success btn-lg px-4 action-button" data-bs-toggle="modal" data-bs-target="#modalAceitar">
                                                    <i class="bi bi-check-circle me-2"></i>Aceitar Proposta
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-lg px-4 action-button" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                                                    <i class="bi bi-x-circle me-2"></i>Recusar Proposta
                                                </button>
                                            </div>
                                            <hr>
                                            <div class="text-center">
                                                <a href="cliente/propostas/comparar?solicitacao_id=<?= $proposta['solicitacao_id'] ?? 0 ?>"
                                                    class="btn btn-outline-primary me-2">
                                                    <i class="bi bi-bar-chart me-1"></i>Comparar Propostas
                                                </a>
                                                <a href="cliente/propostas/recebidas?solicitacao_id=<?= $proposta['solicitacao_id'] ?? 0 ?>"
                                                    class="btn btn-outline-secondary">
                                                    <i class="bi bi-list me-1"></i>Ver Todas as Propostas
                                                </a>
                                            </div>
                                        <?php elseif (($proposta['status'] ?? 'pendente') === 'aceita'): ?>
                                            <div class="alert alert-success text-center">
                                                <i class="bi bi-check-circle me-2"></i>
                                                <strong>Proposta Aceita!</strong><br>
                                                <small>Entre em contato com o prestador para combinar os detalhes.</small>
                                            </div>
                                        <?php elseif (($proposta['status'] ?? 'pendente') === 'recusada'): ?>
                                            <div class="alert alert-danger text-center">
                                                <i class="bi bi-x-circle me-2"></i>
                                                <strong>Proposta Recusada</strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Aba 2: Sobre a Solicitação -->
                            <div class="tab-pane fade" id="solicitacao" role="tabpanel">
                                <!-- Informações da Solicitação -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Serviço:</strong><br>
                                            <span class="text-primary fs-5"><?= htmlspecialchars($proposta['tipo_servico_nome'] ?? 'Não informado') ?></span>
                                        </p>
                                        <p><strong>Urgência:</strong><br>
                                            <span class="badge fs-6 bg-<?=
                                                                        ($proposta['urgencia'] ?? 'media') === 'alta' ? 'danger' : (($proposta['urgencia'] ?? 'media') === 'media' ? 'warning' : 'info')
                                                                        ?>">
                                                <?= ucfirst($proposta['urgencia'] ?? 'média') ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!empty($proposta['orcamento_estimado'])): ?>
                                            <p><strong>Orçamento Estimado:</strong><br>
                                                <span class="text-success fs-5">R$ <?= number_format($proposta['orcamento_estimado'], 2, ',', '.') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <hr>

                                <!-- Descrição da Solicitação -->
                                <div class="mb-4">
                                    <h6><strong>Descrição da Solicitação:</strong></h6>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($proposta['solicitacao_descricao'] ?? 'Sem descrição')) ?></p>
                                    </div>
                                </div>

                                <!-- Imagens da Solicitação -->
                                <?php if (!empty($proposta['imagens_solicitacao'])): ?>
                                    <div class="mb-4">
                                        <h6><i class="bi bi-images me-2"></i>Imagens da Solicitação</h6>
                                        <div class="image-gallery">
                                            <?php foreach ($proposta['imagens_solicitacao'] as $imagem): ?>
                                                <img src="uploads/solicitacoes/<?= htmlspecialchars($imagem) ?>"
                                                    alt="Imagem da solicitação"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalImagem"
                                                    onclick="mostrarImagem('<?= htmlspecialchars($imagem) ?>')">
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Aba 3: Local do Serviço -->
                            <div class="tab-pane fade" id="localizacao" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6><i class="bi bi-geo-alt me-2"></i>Endereço Completo</h6>
                                        <address class="mb-4">
                                            <strong><?= htmlspecialchars(trim(($proposta['logradouro'] ?? 'Não informado') . ', ' . ($proposta['numero'] ?? ''), ', ')) ?></strong><br>
                                            <?php if (!empty($proposta['complemento'])): ?>
                                                <?= htmlspecialchars($proposta['complemento']) ?><br>
                                            <?php endif; ?>
                                            <?= htmlspecialchars(trim(($proposta['bairro'] ?? '') . ' - ' . ($proposta['cidade'] ?? '') . '/' . ($proposta['estado'] ?? ''), ' - /')) ?><br>
                                            <small class="text-muted">CEP: <?= htmlspecialchars($proposta['cep'] ?? 'Não informado') ?></small>
                                        </address>

                                        <button class="btn btn-primary" onclick="abrirMapa()">
                                            <i class="bi bi-geo-alt me-2"></i>Ver no Google Maps
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Mapa Preview -->
                                        <div id="mapa" style="height: 250px; background: #f8f9fa; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed #dee2e6;" onclick="abrirMapa()">
                                            <div class="text-center text-muted">
                                                <i class="bi bi-map" style="font-size: 3rem;"></i>
                                                <p class="mb-0 mt-2">Mapa Interativo</p>
                                                <small>Clique para abrir</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral Reduzida -->
            <div class="col-md-4">
                <!-- Resumo Rápido MELHORADO -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Resumo Rápido</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="fs-4 fw-bold text-success">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></div>
                            <small class="text-muted">em <?= ($proposta['prazo_execucao'] ?? 0) ?> dia(s)</small>
                        </div>
                        <div class="d-grid">
                            <?php if (($proposta['status'] ?? 'pendente') === 'pendente'): ?>
                                <button type="button" class="btn btn-success mb-2 action-button" data-bs-toggle="modal" data-bs-target="#modalAceitar">
                                    <i class="bi bi-check-circle me-1"></i>Aceitar
                                </button>
                                <button type="button" class="btn btn-outline-danger action-button" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                                    <i class="bi bi-x-circle me-1"></i>Recusar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dica para o Cliente -->
                <div class="alert alert-info border-0">
                    <h6><i class="bi bi-lightbulb me-2"></i>Dica</h6>
                    <small>Compare diferentes propostas antes de tomar sua decisão. Considere não apenas o preço, mas também a experiência e avaliações do prestador.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aceitar Proposta MELHORADO -->
<div class="modal fade" id="modalAceitar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Confirmar Aceitação da Proposta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
          
                <form method="POST" action="<?= url('cliente/propostas/aceitar') ?>">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                        <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">

                        <!-- Resumo da Decisão MELHORADO -->
                        <div class="confirmation-summary">
                            <div class="text-center">
                                <h5 class="text-success mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Você está prestes a aceitar esta proposta
                                </h5>
                                <div class="bg-white rounded p-3 shadow-sm">
                                    <h6 class="mb-2">Resumo da Contratação:</h6>
                                    <p class="mb-1">
                                        <strong>Serviço:</strong> <?= htmlspecialchars($proposta['solicitacao_titulo'] ?? 'Serviço') ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Prestador:</strong> <?= htmlspecialchars($proposta['prestador_nome'] ?? 'Prestador') ?>
                                    </p>
                                    <div class="row text-center mt-3">
                                        <div class="col-6">
                                            <h4 class="text-success mb-0">R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?></h4>
                                            <small class="text-muted">Valor Total</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-primary mb-0"><?= ($proposta['prazo_execucao'] ?? 0) ?> dia(s)</h4>
                                            <small class="text-muted">Prazo</small>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-0 text-dark">
                                    <strong>Confirma a contratação de <?= htmlspecialchars($proposta['prestador_nome'] ?? 'Prestador') ?>?</strong>
                                </p>
                            </div>
                        </div>

                        <!-- ...existing code... -->

                        <div class="alert alert-warning">
                            <h6><i class="bi bi-info-circle me-2"></i>Atenção - Esta ação não pode ser desfeita!</h6>
                            <ul class="mb-0 small">
                                <li>✅ O prestador será <strong>notificado imediatamente</strong></li>
                                <li>✅ Outras propostas serão automaticamente recusadas</li>
                                <li>✅ O status mudará para "Serviço Contratado"</li>
                                <li>✅ Você receberá os dados de contato completos</li>
                            </ul>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="confirmarTermos" required>
                            <label class="form-check-label fw-bold" for="confirmarTermos">
                                Confirmo que li e concordo com os termos desta contratação
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success btn-lg action-button">
                            <i class="bi bi-check-circle me-1"></i>Sim, Contratar Serviço
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
            <form method="POST" action="<?= url('cliente/propostas/recusar') ?>">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="<?= url('cliente/propostas/recusar') ?>" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                        <input type="hidden" name="proposta_id" value="<?= $proposta['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>
                            Confirmar Recusa
                        </button>
                    </form>
                </div>
            </form>
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

<?php
// 1. PRIMEIRO: Capturar o conteúdo principal
$content = ob_get_clean();

// 2. SEGUNDO: Definir os scripts
$scripts = '
<script>
// CORRIGIDO: Usar json_encode() para transferir dados de forma segura
const enderecoCompleto = ' . json_encode([
    'logradouro' => $proposta['logradouro'] ?? '',
    'numero' => $proposta['numero'] ?? '',
    'complemento' => $proposta['complemento'] ?? '',
    'bairro' => $proposta['bairro'] ?? '',
    'cidade' => $proposta['cidade'] ?? '',
    'estado' => $proposta['estado'] ?? '',
    'cep' => $proposta['cep'] ?? ''
], JSON_UNESCAPED_UNICODE) . ';

// Função para mostrar imagem no modal
function mostrarImagem(nomeImagem) {
    document.getElementById("imagemModal").src = "uploads/solicitacoes/" + nomeImagem;
}

// FUNÇÃO CORRIGIDA: abrirMapa() com URL e lógica melhoradas
function abrirMapa() {
    console.log("=== INICIANDO FUNÇÃO ABRIR MAPA ===");
    console.log("Dados do endereço:", enderecoCompleto);
    
    // Verificar se temos dados básicos
    if (!enderecoCompleto.cidade && !enderecoCompleto.logradouro) {
        console.error("Erro: Nenhum dado de endereço disponível");
        alert("Erro: Dados de endereço não disponíveis para abrir o mapa.");
        return;
    }
    
    // MELHORADO: Construir endereço removendo partes vazias automaticamente
    let partes = [];
    
    // Adicionar logradouro e número
    if (enderecoCompleto.logradouro && enderecoCompleto.logradouro.trim()) {
        let enderecoPrimario = enderecoCompleto.logradouro.trim();
        if (enderecoCompleto.numero && enderecoCompleto.numero.trim()) {
            enderecoPrimario += ", " + enderecoCompleto.numero.trim();
        }
        partes.push(enderecoPrimario);
    }
    
    // Adicionar complemento se existir
    if (enderecoCompleto.complemento && enderecoCompleto.complemento.trim()) {
        partes.push(enderecoCompleto.complemento.trim());
    }
    
    // Adicionar bairro
    if (enderecoCompleto.bairro && enderecoCompleto.bairro.trim()) {
        partes.push(enderecoCompleto.bairro.trim());
    }
    
    // Adicionar cidade (obrigatório)
    if (enderecoCompleto.cidade && enderecoCompleto.cidade.trim()) {
        partes.push(enderecoCompleto.cidade.trim());
    }
    
    // Adicionar estado
    if (enderecoCompleto.estado && enderecoCompleto.estado.trim()) {
        partes.push(enderecoCompleto.estado.trim());
    }
    
    // Adicionar CEP se disponível
    if (enderecoCompleto.cep && enderecoCompleto.cep.trim()) {
        partes.push("CEP " + enderecoCompleto.cep.trim());
    }
    
    // Montar endereço final
    const endereco = partes.join(", ");
    
    console.log("Partes do endereço:", partes);
    console.log("Endereço final:", endereco);
    
    if (!endereco || endereco.trim() === "") {
        console.error("Erro: Não foi possível construir o endereço");
        alert("Erro: Não foi possível construir o endereço para o mapa.");
        return;
    }
    
    try {
        // CORRIGIDO: URL correta do Google Maps
        const url = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(endereco);
        console.log("URL gerada:", url);
        
        // Abrir em nova aba
        const novaAba = window.open(url, "_blank");
        
        if (!novaAba) {
            console.error("Erro: Popup bloqueado pelo navegador");
            // Fallback: tentar abrir na mesma aba
            if (confirm("Não foi possível abrir em nova aba. Deseja abrir na aba atual?")) {
                window.location.href = url;
            }
        } else {
            console.log("Mapa aberto com sucesso!");
        }
        
    } catch (error) {
        console.error("Erro ao abrir mapa:", error);
        alert("Erro ao abrir o mapa. Verifique se o bloqueador de pop-ups está desabilitado.");
    }
}

// Função para favoritar prestador
function toggleFavorito(prestadorId) {
    fetch("api/favoritar-prestador", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({prestador_id: prestadorId})
    })
    .then(response => response.json())
    .then data => {
        const icon = document.getElementById("favoritoIcon");
        const text = document.getElementById("favoritoText");
        
        if(data.favoritado) {
            icon.className = "bi bi-star-fill";
            text.textContent = "Remover dos Favoritos";
        } else {
            icon.className = "bi bi-star";
            text.textContent = "Favoritar Prestador";
        }
    })
    .catch(error => {
        console.log("API de favoritos não disponível:", error);
    });
}

// Inicialização quando a página carregar
document.addEventListener("DOMContentLoaded", function() {
    // Debug: Mostrar dados do endereço no console
    console.log("=== DADOS DO ENDEREÇO CARREGADOS ===");
    console.log("Endereço completo:", enderecoCompleto);
    
    // Verificar se temos dados mínimos para o mapa
    if (!enderecoCompleto.cidade && !enderecoCompleto.logradouro) {
        console.warn("ATENÇÃO: Dados de endereço incompletos!");
        
        // Ocultar botão do mapa se não tiver dados suficientes
        const botaoMapa = document.querySelector("button[onclick=\"abrirMapa()\"]");
        if (botaoMapa) {
            botaoMapa.style.display = "none";
            botaoMapa.insertAdjacentHTML("afterend", 
                "<p class=\"text-muted\"><i class=\"bi bi-exclamation-triangle\"></i> Endereço não disponível para visualização no mapa</p>"
            );
        }
    }
    
    // Ativar tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Função de teste para debug (pode ser removida em produção)
function testarEndereco() {
    console.log("=== TESTE DE ENDEREÇO ===");
    console.log("Dados disponíveis:", enderecoCompleto);
    
    Object.keys(enderecoCompleto).forEach(key => {
        const valor = enderecoCompleto[key];
        console.log(`${key}: "${valor}" (${valor ? "OK" : "VAZIO"})`);
    });
    
    // Testar a construção do endereço
    const partes = [];
    if (enderecoCompleto.logradouro) partes.push(enderecoCompleto.logradouro);
    if (enderecoCompleto.numero) partes.push(enderecoCompleto.numero);
    if (enderecoCompleto.bairro) partes.push(enderecoCompleto.bairro);
    if (enderecoCompleto.cidade) partes.push(enderecoCompleto.cidade);
    if (enderecoCompleto.estado) partes.push(enderecoCompleto.estado);
    
    const enderecoTeste = partes.join(", ");
    console.log("Endereço construído:", enderecoTeste);
    
    if (enderecoTeste) {
        const urlTeste = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(enderecoTeste);
        console.log("URL de teste:", urlTeste);
    }
}

// Chamar teste automaticamente em modo debug
if (window.location.search.includes("debug=1")) {
    setTimeout(testarEndereco, 1000);
}
</script>
';

// 3. POR ÚLTIMO: Incluir o layout com todas as variáveis definidas
include 'views/layouts/app.php';
?>