<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $conn;
    private $table = 'tb_pessoa';

    public function __construct($db = null)
    {
        if ($db instanceof PDO) {
            $this->conn = $db;
        } else {
            // Tenta obter a instância singleton ou criar uma nova
            if (class_exists('Database') && method_exists('Database', 'getInstance')) {
                $this->conn = Database::getInstance()->getConnection();
            } else {
                $database = new Database();
                $this->conn = $database->getConnection();
            }
        }
    }

    // Método para cadastrar usuário com senha hash
    public function cadastrar($dados)
    {
        $sql = "INSERT INTO {$this->table} (nome, email, senha, cpf, telefone, dt_nascimento, tipo, ativo, data_cadastro)
                VALUES (:nome, :email, :senha, :cpf, :telefone, :dt_nascimento, :tipo, 1, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':senha', password_hash($dados['senha'], PASSWORD_DEFAULT));
        $stmt->bindValue(':cpf', $dados['cpf'] ?? null);
        $stmt->bindValue(':telefone', $dados['telefone'] ?? null);
        $stmt->bindValue(':dt_nascimento', $dados['dt_nascimento'] ?? null);
        $stmt->bindValue(':tipo', $dados['tipo'] ?? 'cliente');
        return $stmt->execute() ? $this->conn->lastInsertId() : false;
    }

    // Editar usuário
    public function editar($id, $dados)
    {
        $fields = "nome = :nome, email = :email, cpf = :cpf, telefone = :telefone, dt_nascimento = :dt_nascimento, tipo = :tipo";
        // atualizar senha somente se informado
        $sql = "UPDATE {$this->table} SET {$fields}" . (isset($dados['senha']) && $dados['senha'] !== '' ? ", senha = :senha" : "") . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':cpf', $dados['cpf'] ?? null);
        $stmt->bindValue(':telefone', $dados['telefone'] ?? null);
        $stmt->bindValue(':dt_nascimento', $dados['dt_nascimento'] ?? null);
        $stmt->bindValue(':tipo', $dados['tipo'] ?? 'cliente');
        if (isset($dados['senha']) && $dados['senha'] !== '') {
            $stmt->bindValue(':senha', password_hash($dados['senha'], PASSWORD_DEFAULT));
        }
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    // Excluir usuário
    public function excluir($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Listar usuários com paginação e filtros
    public function listar($page = 1, $per_page = 10, $filters = [])
    {
        $page = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset = ($page - 1) * $per_page;

        $where = [];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "nome LIKE :nome";
            $params[':nome'] = '%' . $filters['nome'] . '%';
        }
        if (!empty($filters['email'])) {
            $where[] = "email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['tipo'])) {
            $where[] = "tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $where[] = "ativo = :ativo";
            $params[':ativo'] = (int)$filters['ativo'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereSql}";
        $stmtCount = $this->conn->prepare($countSql);
        foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "SELECT * FROM {$this->table} {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_pages = (int)ceil($total / $per_page);

        return [
            'usuarios' => $rows,
            'paginacao' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages
            ]
        ];
    }

    // Buscar usuário por e-mail
    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
