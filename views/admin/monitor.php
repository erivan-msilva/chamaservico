<?php
session_start();
$title = 'Monitor do Sistema - Admin';

// Verificar se é admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}

// Se for uma requisição AJAX para dados do monitor
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    
    try {
        require_once 'core/Database.php';
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        // Monitorar usuários ativos
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM usuarios WHERE ativo = 1) as total_usuarios,
                (SELECT COUNT(*) FROM usuarios WHERE ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) as usuarios_ativos,
                (SELECT COUNT(*) FROM solicitacoes WHERE DATE(data_solicitacao) = CURDATE()) as solicitacoes_hoje
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $dados = [
            'total_sessoes' => $stats['usuarios_ativos'] ?? 0,
            'total_usuarios' => $stats['total_usuarios'] ?? 0,
            'solicitacoes_hoje' => $stats['solicitacoes_hoje'] ?? 0,
            'memoria_uso' => memory_get_usage(true),
            'tempo_resposta' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        echo json_encode($dados);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'total_sessoes' => 0,
            'total_usuarios' => 0,
            'solicitacoes_hoje' => 0,
            'memoria_uso' => memory_get_usage(true),
            'tempo_resposta' => 0,
            'erro' => $e->getMessage()
        ]);
        exit;
    }
}

ob_start();
?>

<div class="container-fluid">
    <h2>Monitor do Sistema</h2>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Usuários Online</h5>
                    <h3 id="usuarios-online">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Total Usuários</h5>
                    <h3 id="total-usuarios">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Solicitações Hoje</h5>
                    <h3 id="solicitacoes-hoje">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Uso de Memória</h5>
                    <h3 id="memoria-uso">0 MB</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarMonitor() {
    fetch('/chamaservico/admin/monitor?ajax=true')
        .then(response => response.json())
        .then(data => {
            document.getElementById('usuarios-online').textContent = data.total_sessoes || 0;
            document.getElementById('total-usuarios').textContent = data.total_usuarios || 0;
            document.getElementById('solicitacoes-hoje').textContent = data.solicitacoes_hoje || 0;
            document.getElementById('memoria-uso').textContent = Math.round((data.memoria_uso || 0) / 1024 / 1024) + ' MB';
        })
        .catch(error => console.error('Erro:', error));
}

// Atualizar a cada 5 segundos
setInterval(atualizarMonitor, 5000);
atualizarMonitor(); // Primeira execução
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
