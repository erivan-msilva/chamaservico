<?php
require_once 'core/Database.php';
require_once 'config/session.php';

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if (empty($email) || empty($senha)) {
                Session::setFlash('error', 'E-mail e senha são obrigatórios!', 'danger');
                include 'views/admin/login.php';
                return;
            }

            // Verificar usuário admin
            $sql = "SELECT * FROM tb_usuario WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($senha, $admin['senha'])) {
                // Atualizar último acesso
                $sqlUpdate = "UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute([$admin['id']]);

                // Criar sessão admin
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nome'] = $admin['nome'];
                $_SESSION['admin_nivel'] = $admin['nivel'];
                $_SESSION['is_admin'] = true;

                header('Location: /chamaservico/admin/dashboard');
                exit;
            } else {
                Session::setFlash('error', 'E-mail ou senha inválidos!', 'danger');
            }
        }

        include 'views/admin/login.php';
    }

    public function dashboard()
    {
        $this->requireAdmin();
        include 'views/admin/dashboard.php';
    }

    public function redirectToDashboard()
    {
        header('Location: /chamaservico/admin/dashboard');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /chamaservico/admin/login');
        exit;
    }

    public function usuarios()
    {
        $this->requireAdmin();
        include 'views/admin/usuarios.php';
    }

    // Nova API para buscar usuários
    public function apiUsuarios()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 15;
            $filtros = [
                'nome' => $_GET['nome'] ?? '',
                'email' => $_GET['email'] ?? '',
                'tipo' => $_GET['tipo'] ?? '',
                'status' => $_GET['status'] ?? '',
                'periodo' => $_GET['periodo'] ?? ''
            ];

            $usuarios = $this->getUsuarios($page, $perPage, $filtros);
            echo json_encode(['sucesso' => true, 'dados' => $usuarios]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    // Nova API para buscar usuário específico
    public function apiUsuario()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $userId = $_GET['id'] ?? 0;

        try {
            $sql = "SELECT * FROM tb_pessoa WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                echo json_encode(['sucesso' => true, 'dados' => $usuario]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    // Nova API para criar usuário
    public function apiCriarUsuario()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'senha' => $_POST['senha'] ?? '',
                'tipo' => $_POST['tipo'] ?? '',
                'telefone' => trim($_POST['telefone'] ?? '')
            ];

            try {
                // Validações
                if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['tipo'])) {
                    throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
                }

                if (strlen($dados['senha']) < 6) {
                    throw new Exception('A senha deve ter pelo menos 6 caracteres');
                }

                if (!in_array($dados['tipo'], ['cliente', 'prestador', 'ambos'])) {
                    throw new Exception('Tipo de usuário inválido');
                }

                // Verificar se email já existe
                $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE email = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$dados['email']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Este e-mail já está cadastrado');
                }

                // Criar usuário
                $sql = "INSERT INTO tb_pessoa (nome, email, senha, tipo, telefone) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $resultado = $stmt->execute([
                    $dados['nome'],
                    $dados['email'],
                    password_hash($dados['senha'], PASSWORD_DEFAULT),
                    $dados['tipo'],
                    $dados['telefone']
                ]);

                if ($resultado) {
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário criado com sucesso']);
                } else {
                    throw new Exception('Erro ao criar usuário');
                }
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
    }

    // Nova API para editar usuário
    public function apiEditarUsuario()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'id' => $_POST['id'] ?? 0,
                'nome' => trim($_POST['nome'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'tipo' => $_POST['tipo'] ?? '',
                'telefone' => trim($_POST['telefone'] ?? ''),
                'nova_senha' => $_POST['nova_senha'] ?? ''
            ];

            try {
                // Validações
                if (empty($dados['nome']) || empty($dados['email']) || empty($dados['tipo'])) {
                    throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
                }

                if (!in_array($dados['tipo'], ['cliente', 'prestador', 'ambos'])) {
                    throw new Exception('Tipo de usuário inválido');
                }

                // Verificar se email já existe para outro usuário
                $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE email = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$dados['email'], $dados['id']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Este e-mail já está sendo usado por outro usuário');
                }

                // Atualizar usuário
                if (!empty($dados['nova_senha'])) {
                    if (strlen($dados['nova_senha']) < 6) {
                        throw new Exception('A nova senha deve ter pelo menos 6 caracteres');
                    }

                    $sql = "UPDATE tb_pessoa SET nome = ?, email = ?, tipo = ?, telefone = ?, senha = ? WHERE id = ?";
                    $params = [
                        $dados['nome'],
                        $dados['email'],
                        $dados['tipo'],
                        $dados['telefone'],
                        password_hash($dados['nova_senha'], PASSWORD_DEFAULT),
                        $dados['id']
                    ];
                } else {
                    $sql = "UPDATE tb_pessoa SET nome = ?, email = ?, tipo = ?, telefone = ? WHERE id = ?";
                    $params = [
                        $dados['nome'],
                        $dados['email'],
                        $dados['tipo'],
                        $dados['telefone'],
                        $dados['id']
                    ];
                }

                $stmt = $this->db->prepare($sql);
                $resultado = $stmt->execute($params);

                if ($resultado) {
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso']);
                } else {
                    throw new Exception('Erro ao atualizar usuário');
                }
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
    }

    // Nova API para deletar usuário
    public function apiDeletarUsuario()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['id'] ?? 0;

            try {
                // Verificar se usuário existe
                $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Usuário não encontrado');
                }

                // Verificar se usuário tem solicitações ativas
                $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE cliente_id = ? AND status_id IN (1,2,3,4)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Não é possível excluir usuário com solicitações ativas');
                }

                // Deletar usuário
                $sql = "DELETE FROM tb_pessoa WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $resultado = $stmt->execute([$userId]);

                if ($resultado) {
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário excluído com sucesso']);
                } else {
                    throw new Exception('Erro ao excluir usuário');
                }
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
    }

    // Nova API para exportar usuários
    public function apiExportarUsuarios()
    {
        $this->requireAdmin();

        try {
            $sql = "SELECT id, nome, email, tipo, telefone, data_cadastro, ultimo_acesso, 
                           CASE WHEN ativo = 1 THEN 'Ativo' ELSE 'Inativo' END as status
                    FROM tb_pessoa ORDER BY data_cadastro DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll();

            // Definir headers para download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d_H-i-s') . '.csv"');

            // Criar arquivo CSV
            $output = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalhos
            fputcsv($output, ['ID', 'Nome', 'Email', 'Tipo', 'Telefone', 'Data Cadastro', 'Último Acesso', 'Status'], ';');

            // Dados
            foreach ($usuarios as $usuario) {
                fputcsv($output, [
                    $usuario['id'],
                    $usuario['nome'],
                    $usuario['email'],
                    ucfirst($usuario['tipo']),
                    $usuario['telefone'] ?? '',
                    $usuario['data_cadastro'] ? date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) : '',
                    $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca',
                    $usuario['status']
                ], ';');
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    private function getDashboardStats()
    {
        $stats = [];

        // Total de usuários
        $sql = "SELECT COUNT(*) FROM tb_pessoa";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_usuarios'] = $stmt->fetchColumn();

        // Usuários ativos
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['usuarios_ativos'] = $stmt->fetchColumn();

        // Total de clientes
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('cliente', 'ambos')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_clientes'] = $stmt->fetchColumn();

        // Total de prestadores
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE tipo IN ('prestador', 'ambos')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_prestadores'] = $stmt->fetchColumn();

        // Solicitações hoje
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE DATE(data_solicitacao) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['solicitacoes_hoje'] = $stmt->fetchColumn();

        // Propostas pendentes
        $sql = "SELECT COUNT(*) FROM tb_proposta WHERE status = 'pendente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['propostas_pendentes'] = $stmt->fetchColumn();

        // Valor total das propostas aceitas este mês
        $sql = "SELECT COALESCE(SUM(valor), 0) FROM tb_proposta WHERE status = 'aceita' AND MONTH(data_aceite) = MONTH(CURDATE()) AND YEAR(data_aceite) = YEAR(CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['valor_total_mes'] = $stmt->fetchColumn();

        // Crescimento mensal de usuários
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE MONTH(data_cadastro) = MONTH(CURDATE()) AND YEAR(data_cadastro) = YEAR(CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['novos_usuarios_mes'] = $stmt->fetchColumn();

        return $stats;
    }

    private function getUsuarios($page, $perPage, $filtros)
    {
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!empty($filtros['nome'])) {
            $where[] = "(nome LIKE ? OR email LIKE ?)";
            $params[] = '%' . $filtros['nome'] . '%';
            $params[] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['tipo'])) {
            $where[] = "tipo = ?";
            $params[] = $filtros['tipo'];
        }

        if (!empty($filtros['status'])) {
            $where[] = "ativo = ?";
            $params[] = $filtros['status'];
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoje':
                    $where[] = "DATE(data_cadastro) = CURDATE()";
                    break;
                case 'semana':
                    $where[] = "data_cadastro >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'mes':
                    $where[] = "data_cadastro >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total
        $sqlCount = "SELECT COUNT(*) FROM tb_pessoa $whereClause";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetchColumn();

        // Get users
        $sql = "SELECT * FROM tb_pessoa $whereClause ORDER BY data_cadastro DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();

        return [
            'usuarios' => $usuarios,
            'paginacao' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
    }

    // Métodos para tipos de serviço
    private function criarTipoServico()
    {
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precoMedio = $_POST['preco_medio'] ?? null;

        if (empty($nome)) {
            Session::setFlash('error', 'Nome é obrigatório!', 'danger');
            return;
        }

        $sql = "INSERT INTO tb_tipo_servico (nome, descricao, categoria, preco_medio) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$nome, $descricao, $categoria, $precoMedio])) {
            Session::setFlash('success', 'Tipo de serviço criado com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao criar tipo de serviço!', 'danger');
        }
    }

    private function editarTipoServico()
    {
        $id = $_POST['id'] ?? 0;
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precoMedio = $_POST['preco_medio'] ?? null;

        $sql = "UPDATE tb_tipo_servico SET nome = ?, descricao = ?, categoria = ?, preco_medio = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$nome, $descricao, $categoria, $precoMedio, $id])) {
            Session::setFlash('success', 'Tipo de serviço atualizado com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao atualizar tipo de serviço!', 'danger');
        }
    }

    private function deletarTipoServico()
    {
        $id = $_POST['id'] ?? 0;

        $sql = "DELETE FROM tb_tipo_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$id])) {
            Session::setFlash('success', 'Tipo de serviço removido com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao remover tipo de serviço!', 'danger');
        }
    }

    // Métodos para status de solicitação
    private function criarStatusSolicitacao()
    {
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $cor = trim($_POST['cor'] ?? '#007bff');

        if (empty($nome)) {
            Session::setFlash('error', 'Nome é obrigatório!', 'danger');
            return;
        }

        $sql = "INSERT INTO tb_status_solicitacao (nome, descricao, cor) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$nome, $descricao, $cor])) {
            Session::setFlash('success', 'Status criado com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao criar status!', 'danger');
        }
    }

    private function editarStatusSolicitacao()
    {
        $id = $_POST['id'] ?? 0;
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $cor = trim($_POST['cor'] ?? '#007bff');

        $sql = "UPDATE tb_status_solicitacao SET nome = ?, descricao = ?, cor = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$nome, $descricao, $cor, $id])) {
            Session::setFlash('success', 'Status atualizado com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao atualizar status!', 'danger');
        }
    }

    private function deletarStatusSolicitacao()
    {
        $id = $_POST['id'] ?? 0;

        $sql = "DELETE FROM tb_status_solicitacao WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$id])) {
            Session::setFlash('success', 'Status removido com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao remover status!', 'danger');
        }
    }
}
