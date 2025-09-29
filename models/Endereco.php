<?php
require_once 'core/Database.php';

class Endereco
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Criar novo endereço
     * @param array $dados Dados do endereço
     * @return int|false ID do endereço criado ou false em caso de erro
     */
    public function criar($dados)
    {
        try {
            $this->db->getConnection()->beginTransaction();

            // Se for principal, desmarcar outros endereços da pessoa
            if ($dados['principal']) {
                $this->desmarcarPrincipal($dados['pessoa_id']);
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
                return $enderecoId;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao criar endereço: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar endereços por pessoa
     * @param int $pessoaId ID da pessoa
     * @return array Lista de endereços
     */
    public function buscarPorPessoa($pessoaId)
    {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar endereço por ID
     * @param int $enderecoId ID do endereço
     * @return array|false Dados do endereço ou false
     */
    public function buscarPorId($enderecoId)
    {
        $sql = "SELECT * FROM tb_endereco WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId]);
        return $stmt->fetch();
    }

    /**
     * Atualizar endereço
     * @param int $enderecoId ID do endereço
     * @param array $dados Novos dados
     * @return bool Sucesso da operação
     */
    public function atualizar($enderecoId, $dados)
    {
        try {
            // Buscar dados atuais do endereço
            $enderecoAtual = $this->buscarPorId($enderecoId);
            if (!$enderecoAtual) {
                return false;
            }

            $this->db->getConnection()->beginTransaction();

            // Se for principal, desmarcar outros endereços da pessoa
            if ($dados['principal']) {
                $this->desmarcarPrincipal($enderecoAtual['pessoa_id'], $enderecoId);
            }

            $sql = "UPDATE tb_endereco 
                    SET cep = ?, logradouro = ?, numero = ?, complemento = ?, 
                        bairro = ?, cidade = ?, estado = ?, principal = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['cep'],
                $dados['logradouro'],
                $dados['numero'],
                $dados['complemento'],
                $dados['bairro'],
                $dados['cidade'],
                $dados['estado'],
                $dados['principal'] ? 1 : 0,
                $enderecoId
            ]);

            if ($resultado) {
                $this->db->getConnection()->commit();
                return true;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao atualizar endereço: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Definir endereço como principal
     * @param int $enderecoId ID do endereço
     * @param int $pessoaId ID da pessoa (para validação)
     * @return bool Sucesso da operação
     */
    public function definirComoPrincipal($enderecoId, $pessoaId)
    {
        try {
            $this->db->getConnection()->beginTransaction();

            // Verificar se o endereço pertence à pessoa
            $endereco = $this->buscarPorId($enderecoId);
            if (!$endereco || $endereco['pessoa_id'] != $pessoaId) {
                $this->db->getConnection()->rollBack();
                return false;
            }

            // Desmarcar todos os endereços da pessoa como principal
            $this->desmarcarPrincipal($pessoaId);

            // Marcar o endereço especificado como principal
            $sql = "UPDATE tb_endereco SET principal = 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$enderecoId]);

            if ($resultado) {
                $this->db->getConnection()->commit();
                return true;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Erro ao definir endereço como principal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar endereço
     * @param int $enderecoId ID do endereço
     * @return bool Sucesso da operação
     */
    public function deletar($enderecoId)
    {
        try {
            $sql = "DELETE FROM tb_endereco WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$enderecoId]);
        } catch (Exception $e) {
            error_log("Erro ao deletar endereço: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desmarcar endereço principal de uma pessoa
     * @param int $pessoaId ID da pessoa
     * @param int $excluirId ID do endereço para excluir da desmarcação (opcional)
     * @return bool Sucesso da operação
     */
    private function desmarcarPrincipal($pessoaId, $excluirId = null)
    {
        try {
            $sql = "UPDATE tb_endereco SET principal = 0 WHERE pessoa_id = ?";
            $params = [$pessoaId];

            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Erro ao desmarcar principal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar endereço principal de uma pessoa
     * @param int $pessoaId ID da pessoa
     * @return array|false Endereço principal ou false
     */
    public function buscarPrincipalPorPessoa($pessoaId)
    {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? AND principal = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return $stmt->fetch();
    }

    /**
     * Contar endereços de uma pessoa
     * @param int $pessoaId ID da pessoa
     * @return int Número de endereços
     */
    public function contarPorPessoa($pessoaId)
    {
        $sql = "SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verificar se endereço está sendo usado em solicitações
     * @param int $enderecoId ID do endereço
     * @return bool Se está sendo usado
     */
    public function verificarEmUso($enderecoId)
    {
        $sql = "SELECT COUNT(*) FROM tb_solicita_servico WHERE endereco_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId]);
        return $stmt->fetchColumn() > 0;
    }

    // Estatísticas de endereços
    public function obterEstatisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_enderecos,
                    COUNT(DISTINCT pessoa_id) as usuarios_com_endereco,
                    COUNT(DISTINCT CONCAT(cidade, estado)) as cidades_atendidas
                FROM tb_endereco";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Buscar endereço por CEP
    public function buscarPorCEP($cep) {
        $sql = "SELECT cep, logradouro, bairro, cidade, estado, complemento FROM tb_endereco WHERE REPLACE(cep, '-', '') = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cep]);
        return $stmt->fetch();
    }
}
// Nenhuma alteração necessária. Esta é a classe recomendada para operações de endereço.
?>
