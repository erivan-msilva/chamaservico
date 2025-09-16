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
require_once 'controllers/admin/ConfiguracoesAdminController.php';
require_once 'config/session.php';

class AdminController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar se está logado - redireciona para AuthAdminController se não estiver
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . url('admin/login'));
            exit;
        }
    }
    
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
        $title = 'Admin Dashboard - ChamaServiço';
        
        // Estatísticas básicas
        $stats = $this->getStatistics();
        
        include 'views/admin/dashboard.php';
    }
    
    public function usuarios() {
        // CORREÇÃO: Verificar se a requisição veio de URL duplicada
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($currentUri, '/admin/admin/') !== false) {
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT p.*, 
                           COUNT(DISTINCT s.id) as total_solicitacoes,
                           COUNT(DISTINCT pr.id) as total_propostas
                    FROM tb_pessoa p
                    LEFT JOIN tb_solicita_servico s ON p.id = s.cliente_id
                    LEFT JOIN tb_proposta pr ON p.id = pr.prestador_id
                    GROUP BY p.id
                    ORDER BY p.data_cadastro DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            $usuarios = [];
        }
        
        $title = 'Gerenciar Usuários - Admin';
        include 'views/admin/usuarios/index.php';
    }
    
    public function usuariosVisualizar() {
        // Verificar se foi passado ID
        $usuarioId = $_GET['id'] ?? 0;
        
        if (!$usuarioId) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID do usuário não informado!'
            ];
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        // Incluir a view que já existe
        include 'views/admin/usuarios/visualizar.php';
    }
    
    public function usuariosAlterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (!$id || !$status) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Dados inválidos!'
            ];
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_pessoa SET ativo = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$status, $id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Status do usuário alterado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao alterar status do usuário!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao alterar status do usuário: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/usuarios/visualizar?id=' . $id));
        exit;
    }
    
    public function usuariosAtivar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID do usuário não informado!'
            ];
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_pessoa SET ativo = 1 WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Usuário ativado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao ativar usuário!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao ativar usuário: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/usuarios/visualizar?id=' . $id));
        exit;
    }
    
    public function usuariosDesativar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID do usuário não informado!'
            ];
            header('Location: ' . url('admin/usuarios'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_pessoa SET ativo = 0 WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Usuário desativado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao desativar usuário!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao desativar usuário: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/usuarios/visualizar?id=' . $id));
        exit;
    }
    
    public function solicitacoes() {
        $title = 'Gerenciar Solicitações - Admin';
        include 'views/admin/solicitacoes/index.php';
    }
    
    public function solicitacoesVisualizar() {
        // Verificar se foi passado ID
        $solicitacaoId = $_GET['id'] ?? 0;
        
        if (!$solicitacaoId) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID da solicitação não informado!'
            ];
            header('Location: ' . url('admin/solicitacoes'));
            exit;
        }
        
        include 'views/admin/solicitacoes/visualizar.php';
    }
    
    public function solicitacoesAlterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/solicitacoes'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (!$id || !$status) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Dados inválidos!'
            ];
            header('Location: ' . url('admin/solicitacoes'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_solicita_servico SET status_id = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$status, $id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Status da solicitação alterado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao alterar status da solicitação!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao alterar status da solicitação: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/solicitacoes/visualizar?id=' . $id));
        exit;
    }
    
    public function tiposServico() {
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            // Buscar estatísticas dos tipos de serviço
            $sqlStats = "
                SELECT 
                    COUNT(*) as total_tipos,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as tipos_ativos,
                    SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as tipos_inativos
                FROM tb_tipo_servico
            ";
            $stmtStats = $db->prepare($sqlStats);
            $stmtStats->execute();
            $stats = $stmtStats->fetch() ?: [
                'total_tipos' => 0,
                'tipos_ativos' => 0,
                'tipos_inativos' => 0
            ];
            
            // Buscar todos os tipos de serviço com estatísticas de uso
            $sql = "
                SELECT 
                    ts.*,
                    (SELECT COUNT(*) FROM tb_solicita_servico WHERE tipo_servico_id = ts.id) as total_solicitacoes,
                    (SELECT COUNT(*) FROM tb_solicita_servico s 
                     WHERE s.tipo_servico_id = ts.id 
                     AND s.data_solicitacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as solicitacoes_mes
                FROM tb_tipo_servico ts
                ORDER BY ts.nome ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $tiposServico = $stmt->fetchAll();
            
            // Configurar dados para a view
            $title = 'Tipos de Serviço - Admin';
            $currentPage = 'tipos-servico';
            
            include 'views/admin/tipos-servico/index.php';
            
        } catch (Exception $e) {
            error_log("Erro ao carregar tipos de serviço: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro ao carregar tipos de serviço!'
            ];
            $stats = ['total_tipos' => 0, 'tipos_ativos' => 0, 'tipos_inativos' => 0];
            $tiposServico = [];
            include 'views/admin/tipos-servico/index.php';
        }
    }
    
    public function tiposServicoSalvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $preco_medio = $_POST['preco_medio'] ?? 0;
        
        if (empty($nome)) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Nome do tipo de serviço é obrigatório!'
            ];
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            if ($id > 0) {
                // Atualizar
                $sql = "UPDATE tb_tipo_servico SET nome = ?, descricao = ?, categoria = ?, preco_medio = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$nome, $descricao, $categoria, $preco_medio, $id]);
                $mensagem = $result ? 'Tipo de serviço atualizado com sucesso!' : 'Erro ao atualizar tipo de serviço!';
            } else {
                // Criar novo
                $sql = "INSERT INTO tb_tipo_servico (nome, descricao, categoria, preco_medio, ativo) VALUES (?, ?, ?, ?, 1)";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$nome, $descricao, $categoria, $preco_medio]);
                $mensagem = $result ? 'Tipo de serviço criado com sucesso!' : 'Erro ao criar tipo de serviço!';
            }
            
            $_SESSION['admin_flash'] = [
                'type' => $result ? 'success' : 'error',
                'message' => $mensagem
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao salvar tipo de serviço: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/tipos-servico'));
        exit;
    }
    
    public function tiposServicoAtivar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID do tipo de serviço não informado!'
            ];
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_tipo_servico SET ativo = 1 WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Tipo de serviço ativado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao ativar tipo de serviço!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao ativar tipo de serviço: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/tipos-servico'));
        exit;
    }
    
    public function tiposServicoDesativar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'ID do tipo de serviço não informado!'
            ];
            header('Location: ' . url('admin/tipos-servico'));
            exit;
        }
        
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            $sql = "UPDATE tb_tipo_servico SET ativo = 0 WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$id])) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Tipo de serviço desativado com sucesso!'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Erro ao desativar tipo de serviço!'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao desativar tipo de serviço: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro interno!'
            ];
        }
        
        header('Location: ' . url('admin/tipos-servico'));
        exit;
    }
    
    private function getStatistics() {
        try {
            require_once 'core/Database.php';
            $db = Database::getInstance();
            
            // Total de usuários
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_pessoa WHERE ativo = 1");
            $stmt->execute();
            $totalUsuarios = $stmt->fetch()['total'];
            
            // Total de solicitações
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_solicita_servico");
            $stmt->execute();
            $totalSolicitacoes = $stmt->fetch()['total'];
            
            // Total de propostas
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_proposta");
            $stmt->execute();
            $totalPropostas = $stmt->fetch()['total'];
            
            // Serviços concluídos
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_solicita_servico WHERE status_id = 5");
            $stmt->execute();
            $servicosConcluidos = $stmt->fetch()['total'];
            
            return [
                'total_usuarios' => $totalUsuarios,
                'total_solicitacoes' => $totalSolicitacoes,
                'total_propostas' => $totalPropostas,
                'servicos_concluidos' => $servicosConcluidos
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'total_usuarios' => 0,
                'total_solicitacoes' => 0,
                'total_propostas' => 0,
                'servicos_concluidos' => 0
            ];
        }
    }
    
    // Métodos para compatibilidade com as rotas existentes
    public function configuracoes() {
        $configController = new ConfiguracoesAdminController();
        $configController->index();
    }
    
    public function salvarConfiguracoes() {
        $configController = new ConfiguracoesAdminController();
        $configController->salvar();
    }
    
    public function testarEmail() {
        $configController = new ConfiguracoesAdminController();
        $configController->testarEmail();
    }
    
    public function gerarBackup() {
        $configController = new ConfiguracoesAdminController();
        $configController->backup();
    }
    
    // Método placeholder para funcionalidades futuras
    public function relatorios() {
        // CORREÇÃO: Verificar se a requisição veio de URL duplicada
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($currentUri, '/admin/admin/') !== false) {
            header('Location: ' . url('admin/relatorios'));
            exit;
        }

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
            
            // Buscar evolução mensal de propostas
            $sqlEvolucaoMensal = "
                SELECT 
                    DATE_FORMAT(s.data_solicitacao, '%Y-%m') as mes,
                    COUNT(DISTINCT s.id) as total_solicitacoes,
                    COUNT(DISTINCT p.id) as total_propostas
                FROM tb_solicita_servico s
                LEFT JOIN tb_proposta p ON s.id = p.solicitacao_id
                WHERE s.data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(s.data_solicitacao, '%Y-%m')
                ORDER BY mes ASC
            ";
            
            $stmt = $db->prepare($sqlEvolucaoMensal);
            $stmt->execute();
            $evolucaoMensal = $stmt->fetchAll();
            
            // Log para debug
            error_log("Relatórios carregados - Total solicitações: " . $estatisticas['total_solicitacoes']);
            
            // Incluir a view de relatórios
            include 'views/admin/relatorios/index.php';
            
        } catch (Exception $e) {
            error_log("Erro ao carregar relatórios: " . $e->getMessage());
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' => 'Erro ao carregar relatórios: ' . $e->getMessage()
            ];
            
            // Dados padrão em caso de erro
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
            $evolucaoMensal = [];
            
            // Incluir a view mesmo com erro
            include 'views/admin/relatorios/index.php';
        }
    }
}
?>