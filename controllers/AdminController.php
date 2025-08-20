<?php

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Usar o sistema de sessão existente
require_once 'config/session.php';
require_once 'core/Database.php';

class AdminController {
    
    public function redirectToDashboard() {
        // Verificar se é admin logado
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        header('Location: /chamaservico/admin/dashboard');
        exit;
    }
    
    public function login() {
        // Se já está logado como admin, redirecionar para dashboard
        if ($this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->authenticate();
            return;
        }
        
        // Mostrar formulário de login
        include 'views/admin/login.php';
    }
    
    public function authenticate() {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // Debug: Log da tentativa de login
        error_log("Admin login attempt - Email: " . $email);
        
        try {
            // Verificar credenciais do admin na tabela tb_usuario (admin) ou tb_pessoa (usuários normais)
            $admin = $this->verificarCredenciaisAdmin($email, $senha);
            
            if ($admin) {
                // Criar sessão de admin
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nome'] = $admin['nome'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['is_admin'] = true;
                
                // Atualizar último acesso
                $this->atualizarUltimoAcesso($admin['id'], $admin['tabela']);
                
                error_log("Admin login successful for user ID: " . $admin['id']);
                header('Location: /chamaservico/admin/dashboard');
                exit;
            } else {
                error_log("Admin login failed for email: " . $email);
                $_SESSION['erro_login'] = 'E-mail ou senha incorretos, ou usuário sem permissão de administrador';
                header('Location: /chamaservico/admin/login');
                exit;
            }
        } catch (Exception $e) {
            error_log("Erro na autenticação admin: " . $e->getMessage());
            $_SESSION['erro_login'] = 'Erro interno do sistema. Tente novamente.';
            header('Location: /chamaservico/admin/login');
            exit;
        }
    }
    
    public function logout() {
        // Limpar sessão do admin
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nome']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['is_admin']);
        
        header('Location: /chamaservico/admin/login');
        exit;
    }
    
    public function dashboard() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // Incluir o dashboard
        include 'views/admin/dashboard.php';
    }
    
    public function usuarios() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        include 'views/admin/usuarios.php';
    }
    
    public function tiposServico() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        include 'views/admin/tipos-servico.php';
    }
    
    public function statusSolicitacao() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        include 'views/admin/status-solicitacao.php';
    }
    
    public function relatorios() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        include 'views/admin/relatorios.php';
    }
    
    public function monitor() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
        
        // Incluir o monitor
        include 'views/admin/monitor.php';
    }
    
    // API Methods para AJAX
    public function apiDashboard() {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Contar usuários na tb_pessoa (estrutura real)
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1");
            $totalUsuarios = $stmt->fetchColumn();
            
            // Contar clientes
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('cliente', 'ambos') AND ativo = 1");
            $totalClientes = $stmt->fetchColumn();
            
            // Contar prestadores
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('prestador', 'ambos') AND ativo = 1");
            $totalPrestadores = $stmt->fetchColumn();
            
            // Contar administradores
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_usuario WHERE ativo = 1");
            $totalAdmins = $stmt->fetchColumn();
            
            // Contar cadastros hoje
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_pessoa WHERE DATE(data_cadastro) = CURDATE()");
            $cadastrosHoje = $stmt->fetchColumn();
            
            // Contar solicitações hoje
            $stmt = $pdo->query("SELECT COUNT(*) FROM tb_solicita_servico WHERE DATE(data_solicitacao) = CURDATE()");
            $solicitacoesHoje = $stmt->fetchColumn();
            
            $dados = [
                'total_usuarios' => $totalUsuarios,
                'total_clientes' => $totalClientes,
                'total_prestadores' => $totalPrestadores,
                'usuarios_ativos' => $totalUsuarios, // Assumindo que todos ativos
                'total_admins' => $totalAdmins,
                'cadastros_hoje' => $cadastrosHoje,
                'solicitacoes_hoje' => $solicitacoesHoje,
                'crescimento_mes' => 5.2
            ];
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $dados]);
            
        } catch (Exception $e) {
            error_log("Erro na API dashboard: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }
    
    // API para usuários usando a estrutura correta
    public function apiUsuarios() {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $filtros = [
                'nome' => $_GET['nome'] ?? '',
                'email' => $_GET['email'] ?? '',
                'tipo' => $_GET['tipo'] ?? '',
                'status' => $_GET['status'] ?? ''
            ];
            
            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 15);
            $offset = ($page - 1) * $perPage;
            
            // Construir query com filtros para tb_pessoa
            $where = [];
            $params = [];
            
            if (!empty($filtros['nome'])) {
                $where[] = "nome LIKE :nome";
                $params['nome'] = '%' . $filtros['nome'] . '%';
            }
            
            if (!empty($filtros['email'])) {
                $where[] = "email LIKE :email";
                $params['email'] = '%' . $filtros['email'] . '%';
            }
            
            if (!empty($filtros['tipo'])) {
                $where[] = "tipo = :tipo";
                $params['tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['status'])) {
                $where[] = "ativo = :ativo";
                $params['ativo'] = $filtros['status'] === 'ativo' ? 1 : 0;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Contar total
            $countSql = "SELECT COUNT(*) FROM tb_pessoa $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Buscar usuários
            $sql = "SELECT id, nome, email, tipo, ativo, data_cadastro, ultimo_acesso, telefone
                    FROM tb_pessoa $whereClause 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $dados = [
                'usuarios' => $usuarios,
                'paginacao' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $dados]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }
    
    // API para criar admin na estrutura correta
    public function apiCriarAdmin() {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        try {
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $nivel = $_POST['nivel_admin'] ?? 'admin';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            // Validações
            if (empty($nome) || empty($email) || empty($senha)) {
                throw new Exception('Nome, e-mail e senha são obrigatórios');
            }
            
            if (strlen($senha) < 6) {
                throw new Exception('A senha deve ter pelo menos 6 caracteres');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('E-mail inválido');
            }
            
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Verificar se o e-mail já existe em ambas as tabelas
            $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception('Este e-mail já está sendo usado por outro administrador');
            }
            
            $stmt = $pdo->prepare("SELECT id FROM tb_pessoa WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception('Este e-mail já está sendo usado por outro usuário');
            }
            
            // Hash da senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir novo admin na tb_usuario
            $stmt = $pdo->prepare("
                INSERT INTO tb_usuario (nome, email, senha, nivel, ativo) 
                VALUES (:nome, :email, :senha, :nivel, :ativo)
            ");
            
            $resultado = $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senhaHash,
                'nivel' => $nivel,
                'ativo' => $ativo
            ]);
            
            if ($resultado) {
                error_log("Admin criado - ID: " . $pdo->lastInsertId() . " | Por: " . $_SESSION['admin_nome']);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'sucesso' => true, 
                    'mensagem' => 'Usuário administrador criado com sucesso!',
                    'id' => $pdo->lastInsertId()
                ]);
            } else {
                throw new Exception('Erro ao criar usuário administrador');
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }
    
    public function toggleStatusUsuario() {
        if (!$this->isAdminLoggedIn()) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            
            if (!$id) {
                throw new Exception('ID do usuário não fornecido');
            }
            
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Buscar status atual na tb_pessoa
            $stmt = $pdo->prepare("SELECT ativo FROM tb_pessoa WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Inverter status
            $novoStatus = $usuario['ativo'] ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE tb_pessoa SET ativo = :ativo WHERE id = :id");
            $resultado = $stmt->execute(['ativo' => $novoStatus, 'id' => $id]);
            
            header('Content-Type: application/json');
            if ($resultado) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Status alterado com sucesso']);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao alterar status']);
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }
    
    // Métodos auxiliares
    private function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    private function verificarCredenciaisAdmin($email, $senha) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            error_log("Verificando credenciais para email: " . $email);
            
            // Primeiro, verificar na tabela tb_usuario (admins)
            $stmt = $pdo->prepare("
                SELECT id, nome, email, senha, nivel as tipo, ativo, 'tb_usuario' as tabela
                FROM tb_usuario 
                WHERE email = :email AND ativo = 1
            ");
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Se não encontrou, verificar na tabela tb_pessoa (usuários normais que podem ser admin)
            if (!$usuario) {
                $stmt = $pdo->prepare("
                    SELECT id, nome, email, senha, tipo, ativo, 'tb_pessoa' as tabela
                    FROM tb_pessoa 
                    WHERE email = :email AND ativo = 1
                ");
                $stmt->execute(['email' => $email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$usuario) {
                error_log("Usuário não encontrado para email: " . $email);
                
                // Se não há usuários na tb_usuario, criar um admin padrão
                $stmtCount = $pdo->query("SELECT COUNT(*) FROM tb_usuario");
                $totalAdmins = $stmtCount->fetchColumn();
                
                if ($totalAdmins == 0) {
                    error_log("Nenhum admin encontrado. Criando admin padrão...");
                    $this->criarAdminPadrao($pdo);
                    
                    // Tentar novamente
                    $stmt = $pdo->prepare("
                        SELECT id, nome, email, senha, nivel as tipo, ativo, 'tb_usuario' as tabela
                        FROM tb_usuario 
                        WHERE email = :email AND ativo = 1
                    ");
                    $stmt->execute(['email' => $email]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                if (!$usuario) {
                    return false;
                }
            }
            
            error_log("Usuário encontrado: " . print_r($usuario, true));
            
            // Verificar a senha
            $senhaValida = false;
            
            // Debug da senha
            error_log("Senha fornecida: " . $senha);
            error_log("Hash no banco (primeiros 30 chars): " . substr($usuario['senha'], 0, 30));
            
            // Tentar verificar como hash primeiro
            if (strlen($usuario['senha']) >= 60 && password_verify($senha, $usuario['senha'])) {
                $senhaValida = true;
                error_log("Senha verificada com password_verify");
            } 
            // Se não funcionou como hash, tentar como texto simples
            elseif ($usuario['senha'] === $senha) {
                $senhaValida = true;
                error_log("Senha verificada como texto simples");
            }
            // Tentar com hash MD5
            elseif (strlen($usuario['senha']) == 32 && md5($senha) === $usuario['senha']) {
                $senhaValida = true;
                error_log("Senha verificada com MD5");
            }
            
            if (!$senhaValida) {
                error_log("Senha inválida");
                return false;
            }
            
            // Verificar se tem permissão de admin
            if ($usuario['tabela'] === 'tb_usuario') {
                // Na tb_usuario, verificar se o nível é admin
                if (in_array($usuario['tipo'], ['admin', 'moderador'])) {
                    error_log("Usuário autenticado como admin (tb_usuario)");
                    return $usuario;
                }
            } else {
                // Na tb_pessoa, para desenvolvimento, permitir qualquer usuário ativo
                // Em produção, você pode restringir por tipo ou criar um campo específico
                error_log("Usuário autenticado como admin (tb_pessoa)");
                return $usuario;
            }
            
            error_log("Usuário não tem permissões de admin");
            return false;
            
        } catch (Exception $e) {
            error_log("Erro na verificação de credenciais admin: " . $e->getMessage());
            return false;
        }
    }
    
    private function criarAdminPadrao($pdo) {
        try {
            // Criar usuário admin padrão na tb_usuario
            $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO tb_usuario (nome, email, senha, nivel, ativo) 
                VALUES ('Administrador', 'admin@chamaservico.com', :senha, 'admin', 1)
            ");
            $stmt->execute(['senha' => $senhaHash]);
            
            error_log("Usuário admin padrão criado com sucesso");
            error_log("Email: admin@chamaservico.com | Senha: admin123");
            
        } catch (Exception $e) {
            error_log("Erro ao criar usuário admin padrão: " . $e->getMessage());
        }
    }
    
    private function atualizarUltimoAcesso($userId, $tabela) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            if ($tabela === 'tb_usuario') {
                $stmt = $pdo->prepare("UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = :id");
            } else {
                $stmt = $pdo->prepare("UPDATE tb_pessoa SET ultimo_acesso = NOW() WHERE id = :id");
            }
            
            $stmt->execute(['id' => $userId]);
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar último acesso: " . $e->getMessage());
        }
    }
}
?>