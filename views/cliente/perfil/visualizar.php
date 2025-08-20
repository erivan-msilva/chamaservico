<?php
$title = 'Meu erivan Perfil Cliente - ChamaServiço';
ob_start();

// Debug para investigar o problema da foto
$fotoPath = '';
$fotoExists = false;

if (!empty($usuario['foto_perfil'])) {
    $fotoNome = basename($usuario['foto_perfil']);
    $possiveisCaminhos = [
        "uploads/perfil/" . $fotoNome,
        "uploads/" . $fotoNome,
        $usuario['foto_perfil']
    ];
    
    foreach ($possiveisCaminhos as $caminho) {
        if (file_exists($caminho)) {
            $fotoPath = $caminho;
            $fotoExists = true;
            break;
        }
    }
}
?>

<style>
.profile-header {
    background: linear-gradient(135deg, #283579 0%, #1a2359 100%);
    border-radius: 15px;
    color: white;
    position: relative;
    overflow: hidden;
}
.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="80" cy="20" r="20" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="80" r="15" fill="rgba(255,255,255,0.05)"/></svg>');
    background-size: 200px 200px;
}
.profile-avatar {
    width: 120px;
    height: 120px;
    border: 4px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}
.profile-avatar:hover {
    transform: scale(1.05);
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--accent-color, #007bff);
}
.completude-ring {
    position: relative;
    width: 80px;
    height: 80px;
}
.activity-timeline {
    position: relative;
    padding-left: 2rem;
}
.activity-timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #e9ecef);
}
.activity-item {
    position: relative;
    margin-bottom: 1.5rem;
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.activity-item::before {
    content: '';
    position: absolute;
    left: -1.8rem;
    top: 1rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--activity-color, #007bff);
    border: 3px solid white;
    box-shadow: 0 0 0 2px var(--activity-color, #007bff);
}
.security-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}
.security-item:last-child {
    border-bottom: none;
}
.verification-badge {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <!-- Header do Perfil -->
    <div class="profile-header p-4 mb-4 position-relative">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <div class="position-relative d-inline-block">
                    <?php if ($fotoExists && !empty($fotoPath)): ?>
                        <img src="/chamaservico/<?= htmlspecialchars($fotoPath) ?>"
                             class="rounded-circle profile-avatar" 
                             alt="Foto do perfil"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="rounded-circle profile-avatar bg-light d-flex align-items-center justify-content-center" style="display: none;">
                            <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php else: ?>
                        <div class="rounded-circle profile-avatar bg-light d-flex align-items-center justify-content-center mx-auto">
                            <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Status online -->
                    <div class="position-absolute bottom-0 end-0">
                        <span class="badge bg-success rounded-circle p-2">
                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <h2 class="mb-2"><?= htmlspecialchars($usuario['nome']) ?></h2>
                <p class="mb-1 opacity-75">
                    <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($usuario['email']) ?>
                </p>
                <p class="mb-1 opacity-75">
                    <i class="bi bi-calendar me-2"></i>Cliente desde <?= date('F Y', strtotime($usuario['data_cadastro'])) ?>
                </p>
                <div class="d-flex gap-2 mt-3">
                    <span class="verification-badge">
                        <i class="bi bi-shield-check me-1"></i>Verificado
                    </span>
                    <span class="badge bg-primary">
                        <?= ucfirst($usuario['tipo']) ?>
                    </span>
                </div>
            </div>
            
            <div class="col-md-3 text-end">
                <div class="d-grid gap-2">
                    <a href="/chamaservico/cliente/perfil/editar" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>Editar Perfil
                    </a>
                    <a href="/chamaservico/cliente/dashboard" class="btn btn-outline-light">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center" style="--accent-color: #007bff;">
                <div class="h3 text-primary mb-1"><?= $estatisticas['total_solicitacoes'] ?? 0 ?></div>
                <div class="text-muted">Solicitações Feitas</div>
                <small class="text-success">
                    <i class="bi bi-arrow-up"></i> +<?= $estatisticas['crescimento_mes'] ?? 0 ?>% este mês
                </small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card text-center" style="--accent-color: #28a745;">
                <div class="h3 text-success mb-1"><?= $estatisticas['servicos_concluidos'] ?? 0 ?></div>
                <div class="text-muted">Serviços Concluídos</div>
                <small class="text-info">
                    <i class="bi bi-star"></i> <?= number_format($estatisticas['satisfacao_media'] ?? 0, 1) ?>/5 satisfação
                </small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card text-center" style="--accent-color: #ffc107;">
                <div class="h3 text-warning mb-1">R$ <?= number_format($estatisticas['total_investido'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-muted">Total Investido</div>
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> Últimos 12 meses
                </small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card text-center" style="--accent-color: #17a2b8;">
                <div class="h3 text-info mb-1"><?= $estatisticas['prestadores_diferentes'] ?? 0 ?></div>
                <div class="text-muted">Prestadores Únicos</div>
                <small class="text-muted">
                    <i class="bi bi-people"></i> Rede de contatos
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informações Pessoais -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-vcard me-2 text-primary"></i>
                        Informações Pessoais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Nome Completo</label>
                                <div class="fw-bold"><?= htmlspecialchars($usuario['nome']) ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted small">E-mail</label>
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold me-2"><?= htmlspecialchars($usuario['email']) ?></span>
                                    <span class="badge bg-success">Verificado</span>
                                </div>
                            </div>
                            
                            <?php if ($usuario['telefone']): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Telefone</label>
                                <div class="fw-bold"><?= htmlspecialchars($usuario['telefone']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if ($usuario['cpf']): ?>
                            <div class="mb-3">
                                <label class="text-muted small">CPF</label>
                                <div class="fw-bold"><?= preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario['cpf']) ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($usuario['dt_nascimento']): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Data de Nascimento</label>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($usuario['dt_nascimento'])) ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="text-muted small">Membro desde</label>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereços -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        Meus Endereços
                    </h5>
                    <a href="/chamaservico/cliente/perfil/enderecos" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-circle me-1"></i>Gerenciar
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($enderecos)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-geo-alt text-muted" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-2">Nenhum endereço cadastrado</h6>
                            <p class="text-muted">Adicione um endereço para facilitar suas solicitações</p>
                            <a href="/chamaservico/cliente/perfil/enderecos" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Adicionar Endereço
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($enderecos as $endereco): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 <?= $endereco['principal'] ? 'border-primary bg-primary bg-opacity-10' : 'border-light' ?>">
                                        <?php if ($endereco['principal']): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-star me-1"></i>Principal
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <address class="mb-0">
                                            <strong><?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?></strong><br>
                                            <?= htmlspecialchars($endereco['bairro']) ?><br>
                                            <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?><br>
                                            <small class="text-muted">CEP: <?= htmlspecialchars($endereco['cep']) ?></small>
                                        </address>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Atividades Recentes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Atividades Recentes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <?php 
                        $atividades = $atividades_recentes ?? [
                            ['tipo' => 'solicitacao', 'titulo' => 'Nova solicitação criada', 'descricao' => 'Serviço de limpeza', 'data' => '2025-01-10', 'cor' => '#007bff'],
                            ['tipo' => 'proposta', 'titulo' => 'Proposta recebida', 'descricao' => 'João Silva enviou uma proposta', 'data' => '2025-01-09', 'cor' => '#28a745'],
                            ['tipo' => 'avaliacao', 'titulo' => 'Avaliação enviada', 'descricao' => 'Serviço de pintura - 5 estrelas', 'data' => '2025-01-08', 'cor' => '#ffc107']
                        ];
                        
                        foreach ($atividades as $ativ): ?>
                        <div class="activity-item" style="--activity-color: <?= $ativ['cor'] ?>;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($ativ['titulo']) ?></h6>
                                    <p class="text-muted mb-1 small"><?= htmlspecialchars($ativ['descricao']) ?></p>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($ativ['data'])) ?></small>
                                </div>
                                <i class="bi bi-<?= $ativ['tipo'] === 'solicitacao' ? 'plus-circle' : ($ativ['tipo'] === 'proposta' ? 'envelope' : 'star') ?> text-muted"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Completude do Perfil -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2 text-primary"></i>
                        Status do Perfil
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php
                    $itens = [
                        'nome' => !empty($usuario['nome']),
                        'email' => !empty($usuario['email']),
                        'telefone' => !empty($usuario['telefone']),
                        'foto' => !empty($usuario['foto_perfil']),
                        'cpf' => !empty($usuario['cpf']),
                        'dt_nascimento' => !empty($usuario['dt_nascimento']),
                        'endereco' => !empty($enderecos)
                    ];
                    $completude = (array_sum($itens) / count($itens)) * 100;
                    $corBarra = $completude >= 80 ? '#28a745' : ($completude >= 50 ? '#ffc107' : '#dc3545');
                    ?>
                    
                    <div class="completude-ring mx-auto mb-3">
                        <svg width="80" height="80" class="rotate-minus-90">
                            <circle cx="40" cy="40" r="35" fill="none" stroke="#e9ecef" stroke-width="6"/>
                            <circle cx="40" cy="40" r="35" fill="none" stroke="<?= $corBarra ?>" stroke-width="6"
                                    stroke-dasharray="<?= 220 * ($completude / 100) ?> 220" stroke-linecap="round"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <div class="h4 mb-0" style="color: <?= $corBarra ?>;"><?= round($completude) ?>%</div>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Perfil <?= $completude >= 80 ? 'Completo' : 'Incompleto' ?></h6>
                    
                    <div class="text-start">
                        <?php
                        $labels = [
                            'nome' => 'Nome completo',
                            'email' => 'Email verificado',
                            'telefone' => 'Telefone',
                            'foto' => 'Foto do perfil',
                            'cpf' => 'CPF',
                            'dt_nascimento' => 'Data de nascimento',
                            'endereco' => 'Endereço'
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
                        <div class="d-grid gap-2 mt-3">
                            <a href="/chamaservico/cliente/perfil/editar" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i>Completar Perfil
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Segurança -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2 text-primary"></i>
                        Segurança
                    </h5>
                </div>
                <div class="card-body">
                    <div class="security-item">
                        <div>
                            <div class="fw-bold">Senha</div>
                            <small class="text-muted">Última alteração: <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></small>
                        </div>
                        <a href="/chamaservico/cliente/perfil/senha" class="btn btn-sm btn-outline-primary">Alterar</a>
                    </div>
                    
                    <div class="security-item">
                        <div>
                            <div class="fw-bold">Autenticação em duas etapas</div>
                            <small class="text-muted">Adicione uma camada extra de segurança</small>
                        </div>
                        <div class="form-check form-switch">
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
