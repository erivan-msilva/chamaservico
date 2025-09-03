<?php
/**
 * AdminController - Classe de roteamento para área administrativa
 * 
 * Esta classe serve como roteador central para delegar as requisições
 * para os controllers específicos do módulo administrativo.
 * 
 * Estrutura modular:
 * - AuthAdminController: Autenticação e login
 * - DashboardAdminController: Dashboard e estatísticas
 * - UsuariosAdminController: Gestão de usuários
 * - RelatoriosAdminController: Relatórios e análises
 * - ConfiguracoesAdminController: Configurações do sistema
 */

require_once 'controllers/admin/AuthAdminController.php';
require_once 'controllers/admin/DashboardAdminController.php';
require_once 'controllers/admin/UsuariosAdminController.php';
require_once 'controllers/admin/SolicitacoesAdminController.php';
require_once 'controllers/admin/ConfiguracoesAdminController.php';
require_once 'controllers/admin/TiposServicoAdminController.php';

class AdminController {
    
    public function index() {
        $authController = new AuthAdminController();
        $authController->index();
    }
    
    public function login() {
        $authController = new AuthAdminController();
        $authController->login();
    }
    
    public function authenticate() {
        $authController = new AuthAdminController();
        $authController->authenticate();
    }
    
    public function logout() {
        $authController = new AuthAdminController();
        $authController->logout();
    }
    
    public function dashboard() {
        $dashboardController = new DashboardAdminController();
        $dashboardController->index();
    }
    
    public function usuarios() {
        $usuariosController = new UsuariosAdminController();
        $usuariosController->index();
    }
    
    public function usuarioVisualizar() {
        $usuariosController = new UsuariosAdminController();
        $usuariosController->visualizar();
    }
    
    public function usuarioAtivar() {
        $usuariosController = new UsuariosAdminController();
        $usuariosController->ativar();
    }
    
    public function usuarioDesativar() {
        $usuariosController = new UsuariosAdminController();
        $usuariosController->desativar();
    }
    
    public function solicitacoes() {
        $solicitacoesController = new SolicitacoesAdminController();
        $solicitacoesController->index();
    }
    
    public function solicitacaoVisualizar() {
        $solicitacoesController = new SolicitacoesAdminController();
        $solicitacoesController->visualizar();
    }
    
    public function solicitacaoAlterarStatus() {
        $solicitacoesController = new SolicitacoesAdminController();
        $solicitacoesController->alterarStatus();
    }
    
    public function solicitacoesEstatisticas() {
        $solicitacoesController = new SolicitacoesAdminController();
        $solicitacoesController->estatisticas();
    }
    
    // Métodos para futuras funcionalidades
    public function relatorios() {
        Session::requireAdminLogin();
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            // Buscar estatísticas gerais
            $sqlEstatisticas = "
                SELECT 
                    (SELECT COUNT(*) FROM tb_solicita_servico) as total_solicitacoes,
                    (SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('cliente', 'ambos')) as total_clientes,
                    (SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('prestador', 'ambos')) as total_prestadores,
                    (SELECT COUNT(*) FROM tb_proposta) as total_propostas,
                    (SELECT COUNT(*) FROM tb_proposta WHERE status = 'aceita') as propostas_aceitas,
                    (SELECT SUM(valor) FROM tb_proposta WHERE status = 'aceita') as valor_total_aceito,
                    (SELECT COUNT(*) FROM tb_solicita_servico WHERE status_id = 5) as servicos_concluidos,
                    (SELECT COUNT(*) FROM tb_avaliacao) as total_avaliacoes,
                    (SELECT AVG(nota) FROM tb_avaliacao) as nota_media_geral
            ";
            
            $stmt = $db->prepare($sqlEstatisticas);
            $stmt->execute();
            $estatisticas = $stmt->fetch() ?: [
                'total_solicitacoes' => 0,
                'total_clientes' => 0,
                'total_prestadores' => 0,
                'total_propostas' => 0,
                'propostas_aceitas' => 0,
                'valor_total_aceito' => 0,
                'servicos_concluidos' => 0,
                'total_avaliacoes' => 0,
                'nota_media_geral' => 0
            ];
            
            // Buscar dados para gráficos dos últimos 12 meses
            $sqlSolicitacoesMes = "
                SELECT 
                    DATE_FORMAT(data_solicitacao, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM tb_solicita_servico 
                WHERE data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_solicitacao, '%Y-%m')
                ORDER BY mes ASC
            ";
            
            $stmt = $db->prepare($sqlSolicitacoesMes);
            $stmt->execute();
            $solicitacoesPorMes = $stmt->fetchAll();
            
            // Buscar tipos de serviços mais solicitados
            $sqlTiposPopulares = "
                SELECT 
                    ts.nome,
                    COUNT(s.id) as total,
                    AVG(s.orcamento_estimado) as orcamento_medio
                FROM tb_tipo_servico ts
                LEFT JOIN tb_solicita_servico s ON ts.id = s.tipo_servico_id
                GROUP BY ts.id, ts.nome
                ORDER BY total DESC
                LIMIT 10
            ";
            
            $stmt = $db->prepare($sqlTiposPopulares);
            $stmt->execute();
            $tiposPopulares = $stmt->fetchAll();
            
            // Buscar distribuição por status
            $sqlStatusDistribuicao = "
                SELECT 
                    st.nome,
                    st.cor,
                    COUNT(s.id) as total
                FROM tb_status_solicitacao st
                LEFT JOIN tb_solicita_servico s ON st.id = s.status_id
                GROUP BY st.id, st.nome, st.cor
                ORDER BY total DESC
            ";
            
            $stmt = $db->prepare($sqlStatusDistribuicao);
            $stmt->execute();
            $statusDistribuicao = $stmt->fetchAll();
            
            // Buscar cidades com mais atividade
            $sqlCidadesAtivas = "
                SELECT 
                    e.cidade,
                    e.estado,
                    COUNT(s.id) as total_solicitacoes
                FROM tb_endereco e
                JOIN tb_solicita_servico s ON e.id = s.endereco_id
                GROUP BY e.cidade, e.estado
                ORDER BY total_solicitacoes DESC
                LIMIT 10
            ";
            
            $stmt = $db->prepare($sqlCidadesAtivas);
            $stmt->execute();
            $cidadesAtivas = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar dados de relatórios: " . $e->getMessage());
            $estatisticas = [
                'total_solicitacoes' => 0,
                'total_clientes' => 0,
                'total_prestadores' => 0,
                'total_propostas' => 0,
                'propostas_aceitas' => 0,
                'valor_total_aceito' => 0,
                'servicos_concluidos' => 0,
                'total_avaliacoes' => 0,
                'nota_media_geral' => 0
            ];
            $solicitacoesPorMes = [];
            $tiposPopulares = [];
            $statusDistribuicao = [];
            $cidadesAtivas = [];
        }
        
        include 'views/admin/relatorios/index.php';
    }
    
    public function configuracoes() {
        $configController = new ConfiguracoesAdminController();
        $configController->index();
    }
    
    public function configuracoesSalvar() {
        $configController = new ConfiguracoesAdminController();
        $configController->salvar();
    }
    
    public function configuracaoTestarEmail() {
        $configController = new ConfiguracoesAdminController();
        $configController->testarEmail();
    }
    
    public function configuracaoBackup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/admin/configuracoes');
            exit;
        }
        
        // Verificar se está logado como admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // NOVO: Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Token de segurança inválido!'
            ];
            header('Location: /chamaservico/admin/configuracoes');
            exit;
        }
        
        try {
            // MELHORIA: Log de auditoria
            error_log("Backup iniciado pelo admin: " . ($_SESSION['admin_nome'] ?? 'Desconhecido') . " em " . date('Y-m-d H:i:s'));
            
            // Método 1: Tentar usar mysqldump
            $backupMysqldump = $this->criarBackupMysqldump();
            
            if ($backupMysqldump['sucesso']) {
                // MELHORIA: Log de sucesso
                error_log("Backup mysqldump criado com sucesso: " . $backupMysqldump['nome']);
                $this->enviarArquivoBackup($backupMysqldump['arquivo'], $backupMysqldump['nome']);
                return;
            }
            
            // Método 2: Backup via PHP (fallback)
            $backupPHP = $this->criarBackupPHP();
            
            if ($backupPHP['sucesso']) {
                // MELHORIA: Log de fallback
                error_log("Backup PHP criado com sucesso: " . $backupPHP['nome']);
                $this->enviarArquivoBackup($backupPHP['arquivo'], $backupPHP['nome']);
                return;
            }
            
            // Se chegou aqui, ambos os métodos falharam
            throw new Exception('Não foi possível criar o backup pelos métodos disponíveis');
            
        } catch (Exception $e) {
            error_log("ERRO CRÍTICO no backup: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro ao criar backup: ' . $e->getMessage()
            ];
        }
        
        header('Location: /chamaservico/admin/configuracoes');
        exit;
    }
    
    private function criarBackupMysqldump() {
        try {
            // MELHORIA: Nome mais descritivo
            $timestamp = date('Y-m-d_H-i-s');
            $nomeBackup = "backup_chamaservico_{$timestamp}.sql";
            $caminhoBackup = sys_get_temp_dir() . '/' . $nomeBackup;
            
            // MELHORIA: Configurações do banco via constantes ou arquivo de config
            $config = $this->getDbConfig();
            
            // Verificar se mysqldump está disponível
            $mysqldumpPath = $this->encontrarMysqldump();
            
            if (!$mysqldumpPath) {
                return ['sucesso' => false, 'erro' => 'mysqldump não encontrado no sistema'];
            }
            
            // MELHORIA: Comando com mais opções de segurança
            $comando = sprintf(
                '"%s" --host=%s --user=%s %s --single-transaction --routines --triggers --add-drop-table --complete-insert --extended-insert --quick --lock-tables=false --set-gtid-purged=OFF %s',
                $mysqldumpPath,
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                empty($config['password']) ? '' : '--password=' . escapeshellarg($config['password']),
                escapeshellarg($config['database'])
            );
            
            // MELHORIA: Timeout para o processo
            $descriptors = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            
            $process = proc_open($comando, $descriptors, $pipes);
            
            if (is_resource($process)) {
                fclose($pipes[0]);
                
                // MELHORIA: Ler dados com timeout
                stream_set_timeout($pipes[1], 300); // 5 minutos
                stream_set_timeout($pipes[2], 300);
                
                $backupData = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                
                $returnVar = proc_close($process);
                
                if ($returnVar === 0 && !empty($backupData) && strlen($backupData) > 500) {
                    // MELHORIA: Adicionar cabeçalho personalizado
                    $headerBackup = $this->gerarCabecalhoBackup('mysqldump', strlen($backupData));
                    $backupCompleto = $headerBackup . $backupData;
                    
                    if (file_put_contents($caminhoBackup, $backupCompleto) !== false) {
                        return [
                            'sucesso' => true,
                            'arquivo' => $caminhoBackup,
                            'nome' => $nomeBackup,
                            'tamanho' => filesize($caminhoBackup),
                            'metodo' => 'mysqldump'
                        ];
                    }
                } else {
                    return [
                        'sucesso' => false,
                        'erro' => "mysqldump falhou. Código: {$returnVar}" . ($errors ? ", Erros: {$errors}" : "") . ", Tamanho: " . strlen($backupData) . " bytes"
                    ];
                }
            }
            
            return ['sucesso' => false, 'erro' => 'Não foi possível executar mysqldump'];
            
        } catch (Exception $e) {
            return ['sucesso' => false, 'erro' => 'Exceção no mysqldump: ' . $e->getMessage()];
        }
    }
    
    private function criarBackupPHP() {
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $timestamp = date('Y-m-d_H-i-s');
            $nomeBackup = "backup_php_{$timestamp}.sql";
            $caminhoBackup = sys_get_temp_dir() . '/' . $nomeBackup;
            
            $arquivo = fopen($caminhoBackup, 'w');
            if (!$arquivo) {
                return ['sucesso' => false, 'erro' => 'Não foi possível criar arquivo de backup'];
            }
            
            // MELHORIA: Cabeçalho mais completo
            $cabecalho = $this->gerarCabecalhoBackup('PHP Export');
            fwrite($arquivo, $cabecalho);
            
            // MELHORIA: Configurações SQL mais robustas
            fwrite($arquivo, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($arquivo, "START TRANSACTION;\n");
            fwrite($arquivo, "SET time_zone = \"+00:00\";\n");
            fwrite($arquivo, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($arquivo, "SET AUTOCOMMIT=0;\n\n");
            
            // Buscar todas as tabelas
            $stmt = $db->getConnection()->query("SHOW TABLES");
            $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $estatisticas = [
                'tabelas_sucesso' => 0,
                'tabelas_erro' => 0,
                'total_registros' => 0
            ];
            
            foreach ($tabelas as $tabela) {
                fwrite($arquivo, "-- ========================================\n");
                fwrite($arquivo, "-- Tabela: {$tabela}\n");
                fwrite($arquivo, "-- ========================================\n\n");
                
                $resultado = $this->backupTabela($db, $arquivo, $tabela);
                if ($resultado['sucesso']) {
                    $estatisticas['tabelas_sucesso']++;
                    $estatisticas['total_registros'] += $resultado['registros'];
                    fwrite($arquivo, "-- ✓ Backup da tabela {$tabela} concluído: {$resultado['registros']} registros\n\n");
                } else {
                    $estatisticas['tabelas_erro']++;
                    fwrite($arquivo, "-- ✗ ERRO no backup da tabela {$tabela}: {$resultado['erro']}\n\n");
                }
            }
            
            // MELHORIA: Rodapé com estatísticas
            fwrite($arquivo, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($arquivo, "SET AUTOCOMMIT=1;\n");
            fwrite($arquivo, "COMMIT;\n\n");
            fwrite($arquivo, "-- ========================================\n");
            fwrite($arquivo, "-- ESTATÍSTICAS DO BACKUP\n");
            fwrite($arquivo, "-- ========================================\n");
            fwrite($arquivo, "-- Tabelas processadas com sucesso: {$estatisticas['tabelas_sucesso']}\n");
            fwrite($arquivo, "-- Tabelas com erro: {$estatisticas['tabelas_erro']}\n");
            fwrite($arquivo, "-- Total de registros: {$estatisticas['total_registros']}\n");
            fwrite($arquivo, "-- Data de conclusão: " . date('Y-m-d H:i:s') . "\n");
            fwrite($arquivo, "-- Gerado por: ChamaServiço Admin Panel\n");
            
            fclose($arquivo);
            
            if (file_exists($caminhoBackup) && filesize($caminhoBackup) > 500) {
                return [
                    'sucesso' => true,
                    'arquivo' => $caminhoBackup,
                    'nome' => $nomeBackup,
                    'tamanho' => filesize($caminhoBackup),
                    'metodo' => 'PHP',
                    'estatisticas' => $estatisticas
                ];
            }
            
            return ['sucesso' => false, 'erro' => 'Arquivo de backup vazio ou corrompido'];
            
        } catch (Exception $e) {
            if (isset($arquivo) && $arquivo) {
                fclose($arquivo);
            }
            return ['sucesso' => false, 'erro' => 'Exceção no backup PHP: ' . $e->getMessage()];
        }
    }
    
    private function backupTabela($db, $arquivo, $nomeTabela) {
        try {
            // Verificar se a tabela existe
            $stmt = $db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$nomeTabela]);
            if (!$stmt->fetch()) {
                return ['sucesso' => false, 'erro' => 'Tabela não existe'];
            }
            
            // Obter estrutura da tabela
            $stmt = $db->getConnection()->query("SHOW CREATE TABLE `{$nomeTabela}`");
            $estrutura = $stmt->fetch();
            
            if ($estrutura) {
                fwrite($arquivo, "-- Estrutura da tabela {$nomeTabela}\n");
                fwrite($arquivo, "DROP TABLE IF EXISTS `{$nomeTabela}`;\n");
                fwrite($arquivo, $estrutura['Create Table'] . ";\n\n");
            }
            
            // MELHORIA: Contar registros primeiro
            $stmt = $db->getConnection()->query("SELECT COUNT(*) FROM `{$nomeTabela}`");
            $totalRegistros = $stmt->fetchColumn();
            
            if ($totalRegistros > 0) {
                fwrite($arquivo, "-- Dados da tabela {$nomeTabela} ({$totalRegistros} registros)\n");
                
                // MELHORIA: Processar em lotes para tabelas grandes
                $batchSize = 1000;
                $totalBatches = ceil($totalRegistros / $batchSize);
                
                for ($batch = 0; $batch < $totalBatches; $batch++) {
                    $offset = $batch * $batchSize;
                    $stmt = $db->getConnection()->query("SELECT * FROM `{$nomeTabela}` LIMIT {$batchSize} OFFSET {$offset}");
                    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($dados)) {
                        $this->escreverDadosTabela($arquivo, $nomeTabela, $dados);
                    }
                }
                
                fwrite($arquivo, "\n");
            }
            
            return ['sucesso' => true, 'registros' => $totalRegistros];
            
        } catch (Exception $e) {
            $erro = "Erro na tabela {$nomeTabela}: " . $e->getMessage();
            fwrite($arquivo, "-- {$erro}\n");
            error_log($erro);
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    private function escreverDadosTabela($arquivo, $nomeTabela, $dados) {
        foreach ($dados as $linha) {
            $colunas = array_keys($linha);
            $valores = array_values($linha);
            
            // MELHORIA: Escape mais seguro usando PDO::quote se disponível
            $valoresEscapados = array_map(function($valor) {
                if ($valor === null) {
                    return 'NULL';
                }
                
                // Escape completo para SQL
                $valor = str_replace(['\\', "\0", "\n", "\r", "'", '"', "\x1a"], 
                                   ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $valor);
                return "'" . $valor . "'";
            }, $valores);
            
            $sql = "INSERT INTO `{$nomeTabela}` (`" . implode('`, `', $colunas) . "`) VALUES (" . implode(', ', $valoresEscapados) . ");\n";
            fwrite($arquivo, $sql);
        }
    }
    
    private function gerarCabecalhoBackup($metodo, $tamanho = null) {
        $cabecalho = "-- ========================================\n";
        $cabecalho .= "-- BACKUP DO CHAMASERVIÇO\n";
        $cabecalho .= "-- ========================================\n";
        $cabecalho .= "-- Data/Hora: " . date('Y-m-d H:i:s') . "\n";
        $cabecalho .= "-- Método: {$metodo}\n";
        $cabecalho .= "-- Banco: bd_servicos\n";
        $cabecalho .= "-- Servidor: " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\n";
        $cabecalho .= "-- Usuário Admin: " . ($_SESSION['admin_nome'] ?? 'Sistema') . "\n";
        $cabecalho .= "-- Versão PHP: " . phpversion() . "\n";
        if ($tamanho) {
            $cabecalho .= "-- Tamanho: " . number_format($tamanho / 1024, 2) . " KB\n";
        }
        $cabecalho .= "-- ========================================\n\n";
        
        // Headers SQL padrão
        $cabecalho .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $cabecalho .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $cabecalho .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $cabecalho .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        
        return $cabecalho;
    }
    
    private function getDbConfig() {
        // MELHORIA: Centralizar configurações do banco
        return [
            'host' => 'localhost',
            'database' => 'bd_servicos',
            'username' => 'root',
            'password' => ''
        ];
    }
    
    private function encontrarMysqldump() {
        // MELHORIA: Cache do caminho encontrado
        static $caminhoEncontrado = null;
        
        if ($caminhoEncontrado !== null) {
            return $caminhoEncontrado;
        }
        
        $possiveis = [
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/lampp/bin/mysqldump'
        ];
        
        foreach ($possiveis as $caminho) {
            if ($this->comandoExiste($caminho)) {
                $caminhoEncontrado = $caminho;
                return $caminho;
            }
        }
        
        return false;
    }
    
    private function comandoExiste($comando) {
        if (PHP_OS_FAMILY === 'Windows') {
            $teste = "where " . escapeshellarg($comando) . " >nul 2>&1";
        } else {
            $teste = "which " . escapeshellarg($comando) . " >/dev/null 2>&1";
        }
        
        exec($teste, $output, $returnVar);
        return $returnVar === 0;
    }
    
    private function enviarArquivoBackup($caminhoArquivo, $nomeArquivo) {
        if (!file_exists($caminhoArquivo)) {
            throw new Exception('Arquivo de backup não encontrado: ' . $caminhoArquivo);
        }
        
        $tamanhoArquivo = filesize($caminhoArquivo);
        
        // MELHORIA: Verificar tamanho mínimo
        if ($tamanhoArquivo < 500) {
            throw new Exception('Arquivo de backup muito pequeno, pode estar corrompido');
        }
        
        // MELHORIA: Headers mais completos
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($nomeArquivo) . '"');
        header('Content-Length: ' . $tamanhoArquivo);
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Limpar buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // MELHORIA: Envio em chunks com verificação de conexão
        $handle = fopen($caminhoArquivo, 'rb');
        if ($handle) {
            while (!feof($handle) && connection_status() === CONNECTION_NORMAL) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }
        
        // Cleanup
        unlink($caminhoArquivo);
        
        // MELHORIA: Log de download
        error_log("Backup baixado com sucesso: {$nomeArquivo} ({$tamanhoArquivo} bytes)");
        
        exit;
    }
    
    public function showNotImplemented($feature) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Em Desenvolvimento - Admin</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-body text-center'>
                                <i class='bi bi-tools' style='font-size: 4rem; color: #ffc107;'></i>
                                <h2 class='mt-3'>$feature</h2>
                                <p class='text-muted'>Esta funcionalidade está em desenvolvimento.</p>
                                <p><small>Em breve estará disponível com uma interface completa.</small></p>
                                <div class='d-flex gap-2 justify-content-center'>
                                    <a href='/chamaservico/admin/dashboard' class='btn btn-primary'>
                                        <i class='bi bi-speedometer2 me-1'></i>Dashboard
                                    </a>
                                    <a href='/chamaservico/admin/usuarios' class='btn btn-outline-secondary'>
                                        <i class='bi bi-people me-1'></i>Usuários
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    // NOVO: Métodos para Tipos de Serviços
    public function tiposServico() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->index();
        } else {
            $this->mostrarPaginaEmDesenvolvimento('Gestão de Tipos de Serviços');
        }
    }
    
    public function tiposServicoCriar() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->criar();
        } else {
            header('Location: /chamaservico/admin/tipos-servico');
            exit;
        }
    }
    
    public function tiposServicoEditar() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->editar();
        } else {
            header('Location: /chamaservico/admin/tipos-servico');
            exit;
        }
    }
    
    public function tiposServicoAlterarStatus() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->alterarStatus();
        } else {
            header('Location: /chamaservico/admin/tipos-servico');
            exit;
        }
    }
    
    public function tiposServicoExcluir() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->excluir();
        } else {
            header('Location: /chamaservico/admin/tipos-servico');
            exit;
        }
    }
    
    public function tiposServicoOrdenar() {
        $this->verificarAutenticacao();
        
        if (class_exists('TiposServicoAdminController')) {
            $controller = new TiposServicoAdminController();
            $controller->ordenar();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Funcionalidade não disponível']);
            exit;
        }
    }
    
    private function verificarAutenticacao() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // Verificar timeout da sessão (4 horas)
        if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > 14400) {
            $this->logout();
        }
    }
    
    private function mostrarPaginaEmDesenvolvimento($titulo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        echo "<!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$titulo} - Admin</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
            <style>
                .sidebar {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .nav-link {
                    color: rgba(255,255,255,0.8) !important;
                    transition: all 0.3s ease;
                }
                .nav-link:hover,
                .nav-link.active {
                    color: #fff !important;
                    background: rgba(255,255,255,0.1);
                    border-radius: 8px;
                }
                .main-content {
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    min-height: 100vh;
                }
                .dev-icon {
                    font-size: 5rem;
                    color: #667eea;
                    margin-bottom: 2rem;
                }
            </style>
        </head>
        <body>
            <div class='container-fluid'>
                <div class='row'>
                    <!-- Sidebar -->
                    <nav class='col-md-3 col-lg-2 d-md-block sidebar collapse'>
                        <div class='position-sticky pt-3'>
                            <div class='text-center mb-4'>
                                <h4 class='text-white'>
                                    <i class='bi bi-shield-check me-2'></i>
                                    Admin Panel
                                </h4>
                                <p class='text-white-50 small'>ChamaServiço</p>
                            </div>
                            
                            <ul class='nav flex-column'>
                                <li class='nav-item'>
                                    <a class='nav-link' href='/chamaservico/admin/dashboard'>
                                        <i class='bi bi-speedometer2 me-2'></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class='nav-item'>
                                    <a class='nav-link' href='/chamaservico/admin/usuarios'>
                                        <i class='bi bi-people me-2'></i>
                                        Usuários
                                    </a>
                                </li>
                                <li class='nav-item'>
                                    <a class='nav-link' href='/chamaservico/admin/solicitacoes'>
                                        <i class='bi bi-list-task me-2'></i>
                                        Solicitações
                                    </a>
                                </li>
                                <li class='nav-item'>
                                    <a class='nav-link active' href='/chamaservico/admin/tipos-servico'>
                                        <i class='bi bi-tools me-2'></i>
                                        Tipos de Serviços
                                    </a>
                                </li>
                                <li class='nav-item'>
                                    <a class='nav-link' href='/chamaservico/admin/relatorios'>
                                        <i class='bi bi-graph-up me-2'></i>
                                        Relatórios
                                    </a>
                                </li>
                                <li class='nav-item'>
                                    <a class='nav-link' href='/chamaservico/admin/configuracoes'>
                                        <i class='bi bi-gear me-2'></i>
                                        Configurações
                                    </a>
                                </li>
                            </ul>
                            
                            <div class='mt-auto pt-4'>
                                <div class='text-center'>
                                    <div class='text-white-50 small'>
                                        Logado como:
                                    </div>
                                    <div class='text-white fw-bold small'>
                                        " . htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin Sistema') . "
                                    </div>
                                    <a href='/chamaservico/admin/logout' class='btn btn-outline-light btn-sm mt-2'>
                                        <i class='bi bi-box-arrow-right me-1'></i>
                                        Sair
                                    </a>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Main content -->
                    <main class='col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content'>
                        <div class='d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4'>
                            <h1 class='h2 text-dark'>
                                {$titulo}
                            </h1>
                        </div>

                        <div class='row justify-content-center'>
                            <div class='col-md-8'>
                                <div class='card border-0 shadow-sm'>
                                    <div class='card-body text-center py-5'>
                                        <i class='bi bi-tools dev-icon'></i>
                                        <h3 class='text-muted mb-3'>Em Desenvolvimento</h3>
                                        <p class='lead text-muted mb-4'>
                                            A funcionalidade <strong>{$titulo}</strong> 
                                            está sendo desenvolvida e estará disponível em breve.
                                        </p>
                                        
                                        <div class='alert alert-info text-start'>
                                            <h6><i class='bi bi-info-circle me-2'></i>Status do Desenvolvimento:</h6>
                                            <ul class='mb-0'>
                                                <li>✅ Estrutura base criada</li>
                                                <li>🔄 Interface em desenvolvimento</li>
                                                <li>⏳ Funcionalidades sendo implementadas</li>
                                                <li>🧪 Testes em andamento</li>
                                            </ul>
                                        </div>
                                        
                                        <div class='d-flex gap-2 justify-content-center'>
                                            <a href='/chamaservico/admin/dashboard' class='btn btn-primary'>
                                                <i class='bi bi-speedometer2 me-1'></i>
                                                Voltar ao Dashboard
                                            </a>
                                            <a href='/chamaservico/admin/solicitacoes' class='btn btn-success'>
                                                <i class='bi bi-list-task me-1'></i>
                                                Ver Solicitações
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>

            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
    }
}
?>