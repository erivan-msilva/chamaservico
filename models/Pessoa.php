<?php
require_once 'core/Database.php';

class Pessoa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function salvarTokenRedefinicao($userId, $token, $expiracao = null)
    {
        try {
            if ($expiracao === null) {
                $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
            }
            
            $sql = "UPDATE tb_pessoa SET token_redefinicao = ?, token_expiracao = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$token, $expiracao, $userId]);
        } catch (Exception $e) {
            error_log("Erro ao salvar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se email existe
     */
    public function emailExiste($email) {
        $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Criar token de redefinição de senha
     */
    public function criarTokenRedefinicao($email, $token, $expiracao) {
        try {
            $sql = "UPDATE tb_pessoa SET token_redefinicao = ?, token_expiracao = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$token, $expiracao, $email]);
        } catch (Exception $e) {
            error_log("Erro ao criar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar token de redefinição
     */
    public function verificarTokenRedefinicao($token) {
        try {
            $sql = "SELECT * FROM tb_pessoa WHERE token_redefinicao = ? AND token_expiracao > NOW() AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao verificar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar senha com token
     */
    public function atualizarSenhaComToken($token, $novaSenha) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Verificar token novamente
            $usuario = $this->verificarTokenRedefinicao($token);
            if (!$usuario) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Atualizar senha e limpar token
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $sql = "UPDATE tb_pessoa SET senha = ?, token_redefinicao = NULL, token_expiracao = NULL WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$senhaHash, $usuario['id']]);
            
            if ($result) {
                $this->db->getConnection()->commit();
                return true;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM tb_pessoa WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar por email: " . $e->getMessage());
            return false;
        }
    }

    private function limparTokensExpirados()
    {
        try {
            $sql = "UPDATE tb_pessoa 
                    SET token_redefinicao = NULL, token_expiracao = NULL 
                    WHERE token_expiracao < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao limpar tokens: " . $e->getMessage());
        }
    }

    private function registrarLogAlteracaoSenha($userId)
    {
        try {
            $sql = "INSERT INTO tb_log_seguranca (usuario_id, acao, ip_address, data_acao) 
                    VALUES (?, 'redefinicao_senha', ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
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

    public function buscarPorTokenRedefinicao($token)
    {
        try {
            $sql = "SELECT * FROM tb_pessoa 
                    WHERE token_redefinicao = ? 
                    AND token_expiracao > NOW() 
                    AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar token: " . $e->getMessage());
            return false;
        }
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

    public function criar($dados) {
        $sql = "INSERT INTO tb_pessoa (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $dados['nome'],
            $dados['email'],
            password_hash($dados['senha'], PASSWORD_DEFAULT),
            $dados['tipo']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
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
