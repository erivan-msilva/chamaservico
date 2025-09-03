<?php
require_once 'core/Database.php';

class Perfil {
    public $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function buscarPorId($id) {
        try {
            // Buscar dados básicos da pessoa
            $sql = "SELECT p.*
                    FROM tb_pessoa p
                    WHERE p.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Tentar buscar dados profissionais se a tabela existir
                try {
                    $sqlProfissional = "SELECT especialidades, area_atuacao, descricao as descricao_profissional
                                       FROM tb_perfil_profissional 
                                       WHERE pessoa_id = ?";
                    $stmtProfissional = $this->db->prepare($sqlProfissional);
                    $stmtProfissional->execute([$id]);
                    $dadosProfissionais = $stmtProfissional->fetch();
                    
                    if ($dadosProfissionais) {
                        $usuario = array_merge($usuario, $dadosProfissionais);
                    }
                } catch (PDOException $e) {
                    // Ignorar erro se a tabela não existir
                    error_log("Tabela tb_perfil_profissional pode não existir: " . $e->getMessage());
                }
            }
            
            return $usuario;
        } catch (Exception $e) {
            error_log("Erro ao buscar perfil: " . $e->getMessage());
            return false;
        }
    }
    
    public function buscarEnderecosPorUsuario($userId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
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
    
    /**
     * Atualizar senha do usuário
     * @param int $userId ID do usuário
     * @param string $novaSenhaHash Nova senha já criptografada
     * @return bool Sucesso da operação
     */
    public function atualizarSenha($userId, $novaSenhaHash) {
        try {
            $sql = "UPDATE tb_pessoa SET senha = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$novaSenhaHash, $userId]);
            
            if ($resultado && $stmt->rowCount() > 0) {
                error_log("Senha atualizada com sucesso para usuário ID: $userId");
                return true;
            } else {
                error_log("Nenhuma linha afetada ao atualizar senha para usuário ID: $userId");
                return false;
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }
    
    public function verificarSenha($id, $senha) {
        $sql = "SELECT senha FROM tb_pessoa WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            return password_verify($senha, $user['senha']);
        }
        return false;
    }
    
    public function atualizarFotoPerfil($id, $nomeArquivo) {
        $sql = "UPDATE tb_pessoa SET foto_perfil = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nomeArquivo, $id]);
    }
    
    public function verificarEnderecoExistente($pessoaId, $dados) {
        $sql = "SELECT COUNT(*) FROM tb_endereco 
                WHERE pessoa_id = ? AND cep = ? AND logradouro = ? AND numero = ? AND bairro = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $pessoaId,
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['bairro']
        ]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function adicionarEndereco($dados) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Se for principal, desmarcar outros
            if ($dados['principal']) {
                $this->desmarcarEnderecoPrincipal($dados['pessoa_id']);
            }
            
            $sql = "INSERT INTO tb_endereco (pessoa_id, cep, logradouro, numero, complemento, bairro, cidade, estado, principal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['pessoa_id'],
                $dados['cep'],
                $dados['logradouro'],
                $dados['numero'],
                $dados['complemento'],
                $dados['bairro'],
                $dados['cidade'],
                $dados['estado'],
                $dados['principal'] ? 1 : 0
            ]);
            
            if ($resultado) {
                $enderecoId = $this->db->lastInsertId();
                $this->db->getConnection()->commit();
                return $enderecoId; // Retornar o ID do endereço criado
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao adicionar endereço: " . $e->getMessage());
            return false;
        }
    }
    
    // Novo método para buscar endereço por ID
    public function buscarEnderecoPorId($enderecoId) {
        $sql = "SELECT * FROM tb_endereco WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId]);
        return $stmt->fetch();
    }
    
    public function editarEndereco($enderecoId, $pessoaId, $dados) {
        // Se for principal, desmarcar outros
        if ($dados['principal']) {
            $this->desmarcarEnderecoPrincipal($pessoaId);
        }
        
        $sql = "UPDATE tb_endereco SET cep = ?, logradouro = ?, numero = ?, complemento = ?, 
                bairro = ?, cidade = ?, estado = ?, principal = ? 
                WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['principal'] ? 1 : 0,
            $enderecoId,
            $pessoaId
        ]);
    }
    
    private function desmarcarEnderecoPrincipal($pessoaId) {
        $sql = "UPDATE tb_endereco SET principal = 0 WHERE pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$pessoaId]);
    }
    
    public function definirEnderecoPrincipal($enderecoId, $pessoaId) {
        // Primeiro desmarcar todos
        $this->desmarcarEnderecoPrincipal($pessoaId);
        
        // Marcar o novo principal
        $sql = "UPDATE tb_endereco SET principal = 1 WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$enderecoId, $pessoaId]);
    }
    
    public function excluirEndereco($enderecoId, $pessoaId) {
        try {
            // Verificar se o endereço pertence ao usuário antes de excluir
            $sql = "SELECT COUNT(*) FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$enderecoId, $pessoaId]);
            
            if ($stmt->fetchColumn() == 0) {
                error_log("Tentativa de excluir endereço que não pertence ao usuário. Endereço ID: $enderecoId, Usuário ID: $pessoaId");
                return false;
            }
            
            // Excluir o endereço
            $sqlDelete = "DELETE FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $resultado = $stmtDelete->execute([$enderecoId, $pessoaId]);
            
            error_log("Resultado da exclusão: " . ($resultado ? 'Sucesso' : 'Falha'));
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir endereço: " . $e->getMessage());
            return false;
        }
    }
    
    // Novo: Buscar endereço específico por ID e usuário
    public function buscarEnderecoPorIdEUsuario($enderecoId, $pessoaId) {
        $sql = "SELECT * FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId, $pessoaId]);
        return $stmt->fetch();
    }
    
    // Novo: Contar endereços do usuário
    public function contarEnderecosPorUsuario($pessoaId) {
        $sql = "SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return $stmt->fetchColumn();
    }
    
    // Novo: Verificar se endereço está sendo usado em solicitações
    public function verificarEnderecoEmUso($enderecoId) {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE endereco_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Novo: Definir primeiro endereço como principal se não houver nenhum
    public function definirPrimeiroComoEnderecoPrincipal($pessoaId) {
        // Verificar se há algum endereço principal
        $sql = "SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = ? AND principal = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        
        if ($stmt->fetchColumn() == 0) {
            // Não há endereço principal, definir o primeiro como principal
            $sqlUpdate = "UPDATE tb_endereco SET principal = 1 WHERE pessoa_id = ? ORDER BY id ASC LIMIT 1";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            return $stmtUpdate->execute([$pessoaId]);
        }
        
        return true;
    }
    
    /**
     * Atualizar dados profissionais do prestador
     * @param int $userId ID do usuário
     * @param array $dados Dados profissionais
     * @return bool Sucesso da operação
     */
    public function atualizarDadosProfissionais($userId, $dados) {
        try {
            // Primeiro verificar se existe tabela tb_perfil_profissional
            if (!$this->verificarTabelaPerfilProfissional()) {
                $this->criarTabelaPerfilProfissional();
            }
            
            // Verificar se já existe registro para este usuário
            $sqlVerificar = "SELECT id FROM tb_perfil_profissional WHERE pessoa_id = ?";
            $stmtVerificar = $this->db->prepare($sqlVerificar);
            $stmtVerificar->execute([$userId]);
            $registro = $stmtVerificar->fetch();
            
            if ($registro) {
                // Atualizar registro existente
                $sql = "UPDATE tb_perfil_profissional 
                       SET especialidades = ?, area_atuacao = ?, descricao = ?
                       WHERE pessoa_id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $dados['especialidades'],
                    $dados['area_atuacao'],
                    $dados['descricao_profissional'],
                    $userId
                ]);
            } else {
                // Inserir novo registro
                $sql = "INSERT INTO tb_perfil_profissional 
                       (pessoa_id, especialidades, area_atuacao, descricao) 
                       VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $userId,
                    $dados['especialidades'],
                    $dados['area_atuacao'],
                    $dados['descricao_profissional']
                ]);
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar dados profissionais: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se tabela de perfil profissional existe
     * @return bool
     */
    private function verificarTabelaPerfilProfissional() {
        try {
            $sql = "SHOW TABLES LIKE 'tb_perfil_profissional'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar tabela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar tabela de perfil profissional se não existir
     * @return bool
     */
    private function criarTabelaPerfilProfissional() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `tb_perfil_profissional` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `pessoa_id` int(11) NOT NULL,
              `especialidades` varchar(255) DEFAULT NULL COMMENT 'Lista de especialidades separadas por vírgula',
              `area_atuacao` varchar(255) DEFAULT NULL COMMENT 'Área geográfica de atendimento',
              `descricao` text DEFAULT NULL COMMENT 'Descrição profissional',
              `disponibilidade` varchar(100) DEFAULT NULL COMMENT 'Dias/horários disponíveis',
              `experiencia_anos` int(11) DEFAULT NULL COMMENT 'Anos de experiência',
              `ultima_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `pessoa_id` (`pessoa_id`),
              CONSTRAINT `fk_perfil_profissional_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `tb_pessoa` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute();
            
            if ($resultado) {
                error_log("Tabela tb_perfil_profissional criada com sucesso");
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela tb_perfil_profissional: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar dados profissionais do prestador
     * @param int $userId ID do usuário
     * @return array|null Dados profissionais ou null se não encontrado
     */
    public function buscarDadosProfissionais($userId) {
        try {
            // Verificar se tabela existe
            if (!$this->verificarTabelaPerfilProfissional()) {
                return null;
            }
            
            $sql = "SELECT * FROM tb_perfil_profissional WHERE pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar dados profissionais: " . $e->getMessage());
            return null;
        }
    }
}
?>

