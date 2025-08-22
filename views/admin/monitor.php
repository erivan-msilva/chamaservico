<?php
require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Monitorar usuários ativos
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1) as total_usuarios,
            (SELECT COUNT(*) FROM tb_pessoa WHERE ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) as usuarios_ativos,
            (SELECT COUNT(*) FROM tb_solicita_servico WHERE DATE(data_solicitacao) = CURDATE()) as solicitacoes_hoje
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $usuarios_ativos = [
        'total_sessoes' => $stats['usuarios_ativos'] ?? 0,
        'total_usuarios' => $stats['total_usuarios'] ?? 0,
        'solicitacoes_hoje' => $stats['solicitacoes_hoje'] ?? 0,
        'memoria_uso' => memory_get_usage(true),
        'tempo_resposta' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    ];

    header('Content-Type: application/json');
    echo json_encode($usuarios_ativos);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'total_sessoes' => 0,
        'total_usuarios' => 0,
        'solicitacoes_hoje' => 0,
        'memoria_uso' => memory_get_usage(true),
        'tempo_resposta' => 0,
        'erro' => $e->getMessage()
    ]);
}
?>
