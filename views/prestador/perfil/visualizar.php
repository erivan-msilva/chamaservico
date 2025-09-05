<?php
$title = 'Meu Perfil Prestador - ChamaServiço';
ob_start();

// Buscar estatísticas reais do prestador
require_once 'models/Proposta.php';
$propostaModel = new Proposta();
$prestadorId = Session::getUserId();

$stats = [
    'propostas_enviadas' => $propostaModel->contarPropostasPorPrestador($prestadorId),
    'propostas_aceitas' => $propostaModel->contarPropostasAceitas($prestadorId),
    'servicos_concluidos' => $propostaModel->contarServicosConcluidos($prestadorId),
    'avaliacao_media' => $propostaModel->obterAvaliacaoMedia($prestadorId)
];
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header com ações rápidas -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">
                    <i class="bi bi-person-badge me-2"></i>Meu Perfil Prestador
                </h2>
                <div class="d-flex gap-2">
                    <a href="<?= url('prestador/perfil/editar') ?>" class="btn btn-success">
                        <i class="bi bi-pencil me-1"></i>Editar Perfil
                    </a>
                    <a href="<?= url('prestador/dashboard') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Informações principais -->
                <div class="col-lg-8">
                    <!-- Card do perfil -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <?php
                                    $fotoPerfil = $usuario['foto_perfil'] ?? '';
                                    $fotoNome = $fotoPerfil ? basename($fotoPerfil) : '';
                                    $fotoExiste = $fotoNome && file_exists("uploads/perfil/" . $fotoNome);
                                    ?>
                                    <?php if ($fotoExiste): ?>
                                        <img src="uploads/perfil/<?= htmlspecialchars($fotoNome) ?>"
                                             class="rounded-circle border border-3 border-primary" 
                                             style="width: 120px; height: 120px; object-fit: cover;"
                                             alt="Foto do prestador">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-3 border-primary mx-auto"
                                             style="width: 120px; height: 120px;">
                                            <i class="bi bi-person text-secondary" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <h3 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h3>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($usuario['email']) ?>
                                    </p>
                                    <?php if ($usuario['telefone']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($usuario['telefone']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-calendar me-1"></i>Prestador desde <?= date('F/Y', strtotime($usuario['data_cadastro'])) ?>
                                    </p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-star me-1"></i><?= $stats['avaliacao_media'] ?>/5
                                        </span>
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-file-earmark-text me-1"></i><?= $stats['propostas_enviadas'] ?> Propostas
                                        </span>
                                        <span class="badge bg-info px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i><?= $stats['servicos_concluidos'] ?> Concluídos
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações detalhadas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-person-vcard me-2"></i>Informações Pessoais
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Nome Completo</label>
                                        <div class="fw-semibold"><?= htmlspecialchars($usuario['nome']) ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Email</label>
                                        <div class="fw-semibold"><?= htmlspecialchars($usuario['email']) ?></div>
                                    </div>
                                    <?php if ($usuario['telefone']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted small">Telefone</label>
                                            <div class="fw-semibold"><?= htmlspecialchars($usuario['telefone']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($usuario['cpf']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted small">CPF</label>
                                            <div class="fw-semibold"><?= preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario['cpf']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($usuario['dt_nascimento']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted small">Data de Nascimento</label>
                                            <div class="fw-semibold"><?= date('d/m/Y', strtotime($usuario['dt_nascimento'])) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="text-muted small">Tipo de Conta</label>
                                        <div>
                                            <span class="badge bg-secondary px-2 py-1"><?= ucfirst($usuario['tipo'] ?? 'prestador') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas propostas -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-clock-history me-2"></i>Atividade Recente
                            </h5>
                            <a href="<?= url('prestador/propostas') ?>" class="btn btn-sm btn-outline-primary">
                                Ver Todas
                            </a>
                        </div>
                        <div class="card-body">
                            <?php
                            $ultimasPropostas = $propostaModel->buscarUltimasPropostas($prestadorId, 3);
                            if (empty($ultimasPropostas)):
                            ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">Nenhuma proposta enviada ainda</h6>
                                    <p class="text-muted">Comece a buscar serviços disponíveis para enviar propostas</p>
                                    <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-primary">
                                        <i class="bi bi-search me-1"></i>Buscar Serviços
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($ultimasPropostas as $proposta): ?>
                                        <div class="timeline-item mb-3">
                                            <div class="d-flex">
                                                <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div class="ms-3 flex-grow-1">
                                                    <h6 class="mb-1"><?= htmlspecialchars($proposta['titulo'] ?? $proposta['solicitacao_titulo'] ?? 'Serviço sem título') ?></h6>
                                                    <p class="text-muted small mb-1">
                                                        Valor: R$ <?= number_format($proposta['valor'] ?? 0, 2, ',', '.') ?> • 
                                                        Status: <span class="badge bg-<?= ($proposta['status'] ?? 'pendente') === 'aceita' ? 'success' : (($proposta['status'] ?? 'pendente') === 'pendente' ? 'warning' : 'secondary') ?>"><?= ucfirst($proposta['status'] ?? 'pendente') ?></span>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($proposta['data_proposta'] ?? 'now')) ?>
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
                                    <div class="h4 text-primary mb-0"><?= $stats['propostas_enviadas'] ?></div>
                                    <small class="text-muted">Propostas Enviadas</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-success mb-0"><?= $stats['propostas_aceitas'] ?></div>
                                    <small class="text-muted">Aceitas</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-info mb-0"><?= $stats['servicos_concluidos'] ?></div>
                                    <small class="text-muted">Concluídos</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-warning mb-0"><?= $stats['avaliacao_media'] ?></div>
                                    <small class="text-muted">Avaliação</small>
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
                            <?php
                            $itens = [
                                'nome' => !empty($usuario['nome']),
                                'email' => !empty($usuario['email']),
                                'telefone' => !empty($usuario['telefone']),
                                'foto' => !empty($usuario['foto_perfil']),
                            ];
                            $completude = (array_sum($itens) / count($itens)) * 100;
                            $corBarra = $completude >= 80 ? '#28a745' : ($completude >= 50 ? '#ffc107' : '#dc3545');
                            ?>
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
                            <?php if ($completude < 100): ?>
                                <a href="prestador/perfil/editar" class="btn btn-primary btn-sm">
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
                                <a href="<?= url('prestador/solicitacoes') ?>" class="btn btn-success">
                                    <i class="bi bi-search me-2"></i>Buscar Serviços
                                </a>
                                <a href="<?= url('prestador/propostas') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-2"></i>Minhas Propostas
                                </a>
                                <a href="<?= url('prestador/servicos/andamento') ?>" class="btn btn-outline-info">
                                    <i class="bi bi-tools me-2"></i>Serviços em Andamento
                                </a>
                                <a href="<?= url('prestador/perfil/enderecos') ?>" class="btn btn-outline-secondary">
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
