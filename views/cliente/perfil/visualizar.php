<?php
$title = 'Meu Perfil Cliente - ChamaServiço';
ob_start();

// Buscar estatísticas reais do cliente
require_once 'models/SolicitacaoServico.php';
require_once 'models/Proposta.php';
$solicitacaoModel = new SolicitacaoServico();
$propostaModel = new Proposta();
$clienteId = Session::getUserId();

try {
    $estatisticas = [
        'total_solicitacoes' => $solicitacaoModel->contarSolicitacoesPorCliente($clienteId),
        'servicos_concluidos' => $solicitacaoModel->contarServicosConcluidos($clienteId),
        'propostas_recebidas' => $propostaModel->contarPropostasRecebidas($clienteId),
        'valor_total_investido' => $solicitacaoModel->calcularValorTotalInvestido($clienteId),
        'crescimento_mes' => 0 // Pode implementar depois se necessário
    ];
} catch (Exception $e) {
    // Fallback em caso de erro
    $estatisticas = [
        'total_solicitacoes' => 0,
        'servicos_concluidos' => 0,
        'propostas_recebidas' => 0,
        'valor_total_investido' => 0,
        'crescimento_mes' => 0
    ];
    error_log("Erro ao buscar estatísticas do cliente: " . $e->getMessage());
}

// CORREÇÃO: Definir variáveis da foto e completude antes de usar
$fotoPerfil = $usuario['foto_perfil'] ?? '';
$fotoNome = $fotoPerfil ? basename($fotoPerfil) : '';
$fotoExiste = $fotoNome && file_exists("uploads/perfil/" . $fotoNome);

// CORREÇÃO: Calcular completude do perfil
$itens = [
    'nome' => !empty($usuario['nome']),
    'email' => !empty($usuario['email']),
    'telefone' => !empty($usuario['telefone']),
    'foto' => !empty($usuario['foto_perfil']),
];
$completude = (array_sum($itens) / count($itens)) * 100;
$corBarra = $completude >= 80 ? '#b3d9ff' : ($completude >= 50 ? '#ffeb99' : '#ffb3b3');
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header com botão de editar perfil -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">
                    <i class="bi bi-person-badge me-2"></i>Meu Perfil Cliente
                </h2>
                <div class="d-flex gap-2">
                    <a href="/chamaservico/cliente/perfil/editar" class="btn btn-success">
                        <i class="bi bi-pencil me-1"></i>Editar Perfil
                    </a>
                    <a href="/chamaservico/cliente/dashboard" class="btn btn-outline-primary">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Informações principais -->
                <div class="col-lg-8">
                    <!-- Card do perfil detalhado -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-person-vcard me-2"></i>Informações do Perfil
                            </h5>
                            <a href="/chamaservico/cliente/perfil/editar" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <?php if ($fotoExiste): ?>
                                        <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($fotoNome) ?>"
                                             class="rounded-circle border border-3 border-primary" 
                                             style="width: 100px; height: 100px; object-fit: cover;"
                                             alt="Foto do cliente">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-3 border-primary mx-auto"
                                             style="width: 100px; height: 100px;">
                                            <i class="bi bi-person text-secondary" style="font-size: 2.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <h4 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h4>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($usuario['email']) ?>
                                    </p>
                                    <?php if ($usuario['telefone']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($usuario['telefone']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-calendar me-1"></i>Cliente desde <?= date('F/Y', strtotime($usuario['data_cadastro'])) ?>
                                    </p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-list-task me-1"></i><?= $estatisticas['total_solicitacoes'] ?> Solicitações
                                        </span>
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i><?= $estatisticas['servicos_concluidos'] ?> Concluídos
                                        </span>
                                        <span class="badge bg-info px-3 py-2">
                                            <i class="bi bi-envelope me-1"></i><?= $estatisticas['propostas_recebidas'] ?> Propostas
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas solicitações -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-clock-history me-2"></i>Atividade Recente
                            </h5>
                            <a href="/chamaservico/cliente/solicitacoes" class="btn btn-sm btn-outline-primary">
                                Ver Todas
                            </a>
                        </div>
                        <div class="card-body">
                            <?php
                            $ultimasSolicitacoes = $solicitacaoModel->buscarUltimasSolicitacoes($clienteId, 3);
                            if (empty($ultimasSolicitacoes)):
                            ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">Nenhuma solicitação criada ainda</h6>
                                    <p class="text-muted">Comece criando sua primeira solicitação de serviço</p>
                                    <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Criar Solicitação
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($ultimasSolicitacoes as $solicitacao): ?>
                                        <div class="timeline-item mb-3">
                                            <div class="d-flex">
                                                <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="bi bi-list-task"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <h6 class="mb-1"><?= htmlspecialchars($solicitacao['titulo']) ?></h6>
                                                    <p class="text-muted small mb-1">
                                                        Status: <span class="badge bg-<?= $solicitacao['status_id'] == 1 ? 'warning' : ($solicitacao['status_id'] == 5 ? 'success' : 'info') ?>"><?= htmlspecialchars($solicitacao['status_nome'] ?? 'Pendente') ?></span>
                                                        <?php if ($solicitacao['orcamento_estimado']): ?>
                                                            • Orçamento: R$ <?= number_format($solicitacao['orcamento_estimado'], 2, ',', '.') ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar com estatísticas e ações -->
                <div class="col-lg-4">
                    <!-- Estatísticas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-graph-up me-2"></i>Estatísticas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h4 text-primary mb-0"><?= $estatisticas['total_solicitacoes'] ?></div>
                                    <small class="text-muted">Solicitações</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-success mb-0"><?= $estatisticas['servicos_concluidos'] ?></div>
                                    <small class="text-muted">Concluídos</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-info mb-0"><?= $estatisticas['propostas_recebidas'] ?></div>
                                    <small class="text-muted">Propostas</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-warning mb-0">R$ <?= number_format($estatisticas['valor_total_investido'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Investido</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completude do perfil -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-shield-check me-2"></i>Status do Perfil
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="position-relative mb-3">
                                <svg width="80" height="80" class="mx-auto">
                                    <circle cx="40" cy="40" r="35" fill="none" stroke="#e9ecef" stroke-width="6"/>
                                    <circle cx="40" cy="40" r="35" fill="none" stroke="<?= $corBarra ?>" stroke-width="6"
                                            stroke-dasharray="<?= 220 * ($completude / 100) ?> 220" 
                                            stroke-linecap="round" transform="rotate(-90 40 40)"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <div class="h4 mb-0" style="color: <?= $corBarra ?>;"><?= round($completude) ?>%</div>
                                </div>
                            </div>
                            <h6 class="mb-3">Perfil <?= $completude >= 80 ? 'Completo' : 'Incompleto' ?></h6>
                            
                            <!-- CORREÇÃO: Adicionar checklist de completude -->
                            <div class="text-start">
                                <?php
                                $labels = [
                                    'nome' => 'Nome completo',
                                    'email' => 'Email verificado',
                                    'telefone' => 'Telefone',
                                    'foto' => 'Foto do perfil'
                                ];
                                
                                foreach ($itens as $item => $completo): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-<?= $completo ? 'check-circle-fill text-success' : 'circle text-muted' ?> me-2"></i>
                                        <small class="<?= $completo ? 'text-muted' : 'text-warning' ?>">
                                            <?= $labels[$item] ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if ($completude < 100): ?>
                                <a href="/chamaservico/cliente/perfil/editar" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i>Completar Perfil
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Ações rápidas -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-lightning me-2"></i>Ações Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/chamaservico/cliente/perfil/editar" class="btn btn-success">
                                    <i class="bi bi-pencil me-2"></i>Editar Perfil
                                </a>
                                <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Nova Solicitação
                                </a>
                                <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-success">
                                    <i class="bi bi-envelope me-2"></i>Ver Propostas
                                </a>
                                <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-info">
                                    <i class="bi bi-list-task me-2"></i>Minhas Solicitações
                                </a>
                                <a href="/chamaservico/cliente/perfil/enderecos" class="btn btn-outline-secondary">
                                    <i class="bi bi-geo-alt me-2"></i>Meus Endereços
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-marker {
    font-size: 0.875rem;
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
                            <input class="form-check-input" type="checkbox" id="2fa">
                        </div>
                    </div>
                    
                    <div class="security-item">
                        <div>
                            <div class="fw-bold">Sessões ativas</div>
                            <small class="text-muted">Gerencie seus dispositivos conectados</small>
                        </div>
                        <a href="/chamaservico/cliente/perfil/sessoes" class="btn btn-sm btn-outline-primary">Ver</a>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2 text-primary"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i>Nova Solicitação
                        </a>
                        <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-success">
                            <i class="bi bi-envelope me-2"></i>Ver Propostas
                        </a>
                        <a href="/chamaservico/cliente/avaliacoes" class="btn btn-outline-warning">
                            <i class="bi bi-star me-2"></i>Avaliar Serviços
                        </a>
                        <a href="/chamaservico/cliente/relatorios" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-2"></i>Relatórios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rotate-minus-90 {
    transform: rotate(-90deg);
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rotate-minus-90 {
    transform: rotate(-90deg);
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/chamaservico/cliente/solicitacoes/criar" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i>Nova Solicitação
                        </a>
                        <a href="/chamaservico/cliente/propostas/recebidas" class="btn btn-outline-success">
                            <i class="bi bi-envelope me-2"></i>Ver Propostas
                        </a>
                        <a href="/chamaservico/cliente/avaliacoes" class="btn btn-outline-warning">
                            <i class="bi bi-star me-2"></i>Avaliar Serviços
                        </a>
                        <a href="/chamaservico/cliente/relatorios" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-2"></i>Relatórios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rotate-minus-90 {
    transform: rotate(-90deg);
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rotate-minus-90 {
    transform: rotate(-90deg);
}
</style>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
