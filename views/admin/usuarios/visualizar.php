<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Configuração do layout
$title = 'Visualizar Usuário - Admin';
$currentPage = 'usuarios';

// Buscar dados reais do usuário
$usuarioId = $_GET['id'] ?? 0;
$usuario = null;

if ($usuarioId) {
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance();

        // Buscar dados do usuário
        $sql = "
            SELECT 
                p.*,
                (SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = p.id) as total_solicitacoes,
                (SELECT COUNT(*) FROM tb_proposta WHERE prestador_id = p.id) as total_propostas,
                (SELECT AVG(nota) FROM tb_avaliacao WHERE avaliado_id = p.id) as nota_media,
                (SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = p.id) as total_enderecos
            FROM tb_pessoa p 
            WHERE p.id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$usuarioId]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Usuário não encontrado!'
            ];
            header('Location: /chamaservico/admin/usuarios');
            exit;
        }

        // Buscar endereços do usuário
        $sqlEnderecos = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmtEnderecos = $db->prepare($sqlEnderecos);
        $stmtEnderecos->execute([$usuarioId]);
        $enderecos = $stmtEnderecos->fetchAll();

        // Buscar últimas solicitações
        $sqlSolicitacoes = "
            SELECT s.*, ts.nome as tipo_servico_nome, st.nome as status_nome, st.cor as status_cor
            FROM tb_solicita_servico s
            JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
            JOIN tb_status_solicitacao st ON s.status_id = st.id
            WHERE s.cliente_id = ?
            ORDER BY s.data_solicitacao DESC
            LIMIT 5
        ";
        $stmtSolicitacoes = $db->prepare($sqlSolicitacoes);
        $stmtSolicitacoes->execute([$usuarioId]);
        $solicitacoes = $stmtSolicitacoes->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao buscar usuário: " . $e->getMessage());
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'message' => 'Erro ao carregar dados do usuário!'
        ];
        header('Location: /chamaservico/admin/usuarios');
        exit;
    }
} else {
    header('Location: /chamaservico/admin/usuarios');
    exit;
}

// Definir variáveis padrão para estatísticas
$estatisticas = [
    'solicitacoes' => $usuario['total_solicitacoes'] ?? 0,
    'propostas' => $usuario['total_propostas'] ?? 0,
    'avaliacoes' => $usuario['total_enderecos'] ?? 0,
    'nota_media' => $usuario['nota_media'] ? number_format($usuario['nota_media'], 1) : '0.0'
];

ob_start();
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <h1 class="h2 text-dark">
        <i class="bi bi-person-circle me-2"></i>
        <?= htmlspecialchars($usuario['nome']) ?>
    </h1>

    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/chamaservico/admin/usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>

        <div class="btn-group">
            <button type="button" class="btn btn-<?= $usuario['ativo'] ? 'danger' : 'success' ?>"
                onclick="alterarStatus(<?= $usuario['ativo'] ? 0 : 1 ?>)">
                <i class="bi bi-person-<?= $usuario['ativo'] ? 'x' : 'check' ?> me-1"></i>
                <?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>
            </button>
            <button type="button" class="btn btn-info" onclick="enviarEmail()">
                <i class="bi bi-envelope me-1"></i>
                Enviar E-mail
            </button>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['admin_flash'])): ?>
    <?php $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']); ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Estatísticas do Usuário -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card" style="border-left-color: #007bff;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Solicitações
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                        <?= $estatisticas['solicitacoes'] ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-list-task fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Propostas
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                        <?= $estatisticas['propostas'] ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-envelope fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card" style="border-left-color: #dc3545;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Endereços
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                        <?= $estatisticas['avaliacoes'] ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-geo-alt fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card" style="border-left-color: #f6c23e;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Nota Média
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                        <?= $estatisticas['nota_media'] ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="bi bi-star fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes e Atividades -->
<div class="row">
    <!-- Informações Detalhadas -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações Detalhadas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <strong>Nome Completo:</strong><br>
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= $usuario['email'] ?>"><?= htmlspecialchars($usuario['email']) ?></a>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>CPF:</strong><br>
                        <?= $usuario['cpf'] ? htmlspecialchars($usuario['cpf']) : 'Não informado' ?>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Telefone:</strong><br>
                        <?= $usuario['telefone'] ? htmlspecialchars($usuario['telefone']) : 'Não informado' ?>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Tipo de Usuário:</strong><br>
                        <span class="badge bg-<?= $usuario['tipo'] === 'cliente' ? 'primary' : ($usuario['tipo'] === 'prestador' ? 'success' : 'info') ?>">
                            <?= ucfirst($usuario['tipo']) ?>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-<?= $usuario['ativo'] ? 'success' : 'danger' ?>">
                            <i class="bi bi-<?= $usuario['ativo'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
                            <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Data de Cadastro:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) ?>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <strong>Último Acesso:</strong><br>
                        <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca acessou' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Endereços -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    Endereços Cadastrados
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($enderecos)): ?>
                    <?php foreach ($enderecos as $endereco): ?>
                        <div class="mb-3 p-2 <?= $endereco['principal'] ? 'bg-light' : '' ?> rounded">
                            <?php if ($endereco['principal']): ?>
                                <span class="badge bg-primary mb-1">Principal</span>
                            <?php endif; ?>
                            <address class="mb-0 small">
                                <?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?><br>
                                <?= $endereco['complemento'] ? htmlspecialchars($endereco['complemento']) . '<br>' : '' ?>
                                <?= htmlspecialchars($endereco['bairro']) ?> - <?= htmlspecialchars($endereco['cidade']) ?>/<?= htmlspecialchars($endereco['estado']) ?><br>
                                CEP: <?= htmlspecialchars($endereco['cep']) ?>
                            </address>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">Nenhum endereço cadastrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Solicitações -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Últimas Solicitações
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($solicitacoes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($solicitacao['titulo']) ?></td>
                                        <td><?= htmlspecialchars($solicitacao['tipo_servico_nome']) ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $solicitacao['status_cor'] ?>;">
                                                <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                        <td>
                                            <?= $solicitacao['orcamento_estimado'] ? 'R$ ' . number_format($solicitacao['orcamento_estimado'], 2, ',', '.') : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Nenhuma solicitação encontrada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Estilos específicos da página
$styles = '
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border-left: 4px solid;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: none;
    transition: all 0.3s ease;
}
.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: bold;
    font-size: 1.1rem;
    color: #283579;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.text-xs {
    font-size: 0.75rem;
}
.font-weight-bold {
    font-weight: 700;
}
.text-gray-800 {
    color: #5a5c69;
}
.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: 12px;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f8f9fa;
}
.table-striped tbody tr:hover {
    background-color: #e9ecef;
}
';

// Scripts específicos da página
$scripts = '
<script>
function alterarStatus(novoStatus) {
    const acao = novoStatus ? "ativar" : "desativar";
    if (confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = novoStatus ? "/chamaservico/admin/usuarios/ativar" : "/chamaservico/admin/usuarios/desativar";
        
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "id";
        input.value = ' . $usuario['id'] . ';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function enviarEmail() {
    window.location.href = "mailto:' . htmlspecialchars($usuario['email']) . '";
}
</script>
';

include 'views/admin/layouts/app.php';
?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function alterarStatus(novoStatus) {
        const acao = novoStatus ? 'ativar' : 'desativar';
        if (confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = novoStatus ? '/chamaservico/admin/usuarios/ativar' : '/chamaservico/admin/usuarios/desativar';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = <?= $usuario['id'] ?>;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function enviarEmail() {
        window.location.href = 'mailto:<?= $usuario['email'] ?>';
    }
</script>
</body>

</html>