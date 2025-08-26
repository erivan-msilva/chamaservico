<?php
require_once 'core/Database.php';

class Pessoa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM tb_pessoa WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function verificarSenha($email, $senha) {
        $sql = "SELECT * FROM tb_pessoa WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha'])) {
            return $user;
        }
        return false;
    }

    public function salvarTokenRedefinicao($id, $token) {
        $sql = "UPDATE tb_pessoa SET token_redefinicao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token, $id]);
    }

    public function buscarPorTokenRedefinicao($token) {
        $sql = "SELECT * FROM tb_pessoa WHERE token_redefinicao = ? AND token_expiracao > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function alterarSenha($id, $novaSenha) {
        $sql = "UPDATE tb_pessoa SET senha = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $id]);
    }

    public function removerTokenRedefinicao($id) {
        $sql = "UPDATE tb_pessoa SET token_redefinicao = NULL, token_expiracao = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function emailExiste($email) {
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function criar($dados) {
        $sql = "INSERT INTO tb_pessoa (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            password_hash($dados['senha'], PASSWORD_DEFAULT),
            $dados['tipo']
        ]);
    }

    public function atualizarUltimoAcesso($id) {
        $sql = "UPDATE tb_pessoa SET ultimo_acesso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM tb_pessoa WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE tb_pessoa SET nome = ?, email = ?, telefone = ?, cpf = ?, dt_nascimento = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['cpf'],
            $dados['dt_nascimento'],
            $id
        ]);
    }

    public function atualizarFotoPerfil($id, $nomeArquivo) {
        $sql = "UPDATE tb_pessoa SET foto_perfil = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nomeArquivo, $id]);
    }
}
?>
