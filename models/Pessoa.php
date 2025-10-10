<?php
require_once __DIR__ . '/../core/Database.php';

// Classe principal para gerenciar usuários/clientes
class Pessoa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Verificar se email existe
     */
    public function emailExiste($email) {
        try {
            $sql = "SELECT COUNT(*) FROM tb_pessoa WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar pessoa por email
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT id, nome, email, tipo, ativo FROM tb_pessoa WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar pessoa por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar token de redefinição de senha - usando campos da própria tabela
     */
    public function criarTokenRedefinicao($email, $token, $expiracao) {
        try {
            // Verificar se as colunas existem, senão criar
            $this->verificarColunaToken();
            
            // Limpar tokens antigos do mesmo email
            $sql = "UPDATE tb_pessoa SET token_redefinicao = NULL, token_expiracao = NULL WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            
            // Criar novo token
            $sql = "UPDATE tb_pessoa SET token_redefinicao = ?, token_expiracao = ? WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$token, $expiracao, $email]);
            
            error_log("Token criado para $email: " . ($result ? 'sucesso' : 'falha'));
            return $result;
            
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
            $sql = "SELECT id, nome, email, token_redefinicao, token_expiracao 
                    FROM tb_pessoa 
                    WHERE token_redefinicao = ? 
                    AND token_expiracao > NOW() 
                    AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Verificando token $token: " . ($result ? 'válido' : 'inválido'));
            return $result;
            
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
            // Verificar se o token é válido
            $usuario = $this->verificarTokenRedefinicao($token);
            if (!$usuario) {
                error_log("Token inválido ou expirado: $token");
                return false;
            }
            
            // Hash da nova senha
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            // Atualizar senha e limpar token
            $sql = "UPDATE tb_pessoa 
                    SET senha = ?, 
                        token_redefinicao = NULL, 
                        token_expiracao = NULL
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$senhaHash, $usuario['id']]);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("✅ Senha atualizada com sucesso para usuário ID: " . $usuario['id']);
                return true;
            } else {
                error_log("❌ Falha ao atualizar senha para usuário ID: " . $usuario['id']);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar e criar colunas de token se não existirem
     */
    private function verificarColunaToken() {
        try {
            // Verificar se as colunas já existem
            $sql = "SHOW COLUMNS FROM tb_pessoa LIKE 'token_redefinicao'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // Adicionar as colunas necessárias
                $alterSql = "ALTER TABLE tb_pessoa 
                            ADD COLUMN token_redefinicao VARCHAR(255) NULL,
                            ADD COLUMN token_expiracao DATETIME NULL";
                $stmt = $this->db->prepare($alterSql);
                $stmt->execute();
                error_log("✅ Colunas de token adicionadas à tabela tb_pessoa");
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar/criar colunas de token: " . $e->getMessage());
        }
    }

    /**
     * Verificar senha para login
     */
    public function verificarSenha($email, $senha) {
        try {
            $sql = "SELECT * FROM tb_pessoa WHERE email = ? AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($senha, $user['senha'])) {
                return $user;
            }
            return false;
        } catch (Exception $e) {
            error_log("Erro na verificação de senha: " . $e->getMessage());
            return false;
        }
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

