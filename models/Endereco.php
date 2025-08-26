<?php
require_once 'core/Database.php';

class Endereco {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Buscar endereços por pessoa
    public function buscarPorPessoa($pessoaId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return $stmt->fetchAll();
    }

    // Buscar endereço por ID
    public function buscarPorId($id) {
        $sql = "SELECT * FROM tb_endereco WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Buscar endereço principal
    public function buscarPrincipal($pessoaId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? AND principal = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return $stmt->fetch();
    }

    // Adicionar novo endereço
    public function adicionar($dados) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Se for principal, remover principal dos outros
            if (!empty($dados['principal'])) {
                $this->removerPrincipalOutros($dados['pessoa_id']);
            }
            
            $sql = "INSERT INTO tb_endereco (pessoa_id, cep, logradouro, numero, complemento, bairro, cidade, estado, principal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['pessoa_id'],
                $dados['cep'],
                $dados['logradouro'],
                $dados['numero'],
                $dados['complemento'] ?? null,
                $dados['bairro'],
                $dados['cidade'],
                $dados['estado'],
                !empty($dados['principal']) ? 1 : 0
            ]);
            
            if ($resultado) {
                $enderecoId = $this->db->lastInsertId();
                $this->db->getConnection()->commit();
                return $enderecoId;
            }
            
            $this->db->getConnection()->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao adicionar endereço: " . $e->getMessage());
            return false;
        }
    }

    // Editar endereço
    public function editar($id, $dados) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Se for principal, remover principal dos outros
            if (!empty($dados['principal'])) {
                $this->removerPrincipalOutros($dados['pessoa_id'], $id);
            }
            
            $sql = "UPDATE tb_endereco SET 
                    cep = ?, logradouro = ?, numero = ?, complemento = ?, 
                    bairro = ?, cidade = ?, estado = ?, principal = ?
                    WHERE id = ? AND pessoa_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['cep'],
                $dados['logradouro'],
                $dados['numero'],
                $dados['complemento'] ?? null,
                $dados['bairro'],
                $dados['cidade'],
                $dados['estado'],
                !empty($dados['principal']) ? 1 : 0,
                $id,
                $dados['pessoa_id']
            ]);
            
            if ($resultado) {
                $this->db->getConnection()->commit();
                return true;
            }
            
            $this->db->getConnection()->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao editar endereço: " . $e->getMessage());
            return false;
        }
    }

    // Definir como principal
    public function definirPrincipal($id, $pessoaId) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Remover principal de todos os outros
            $this->removerPrincipalOutros($pessoaId, $id);
            
            // Definir este como principal
            $sql = "UPDATE tb_endereco SET principal = 1 WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$id, $pessoaId]);
            
            if ($resultado) {
                $this->db->getConnection()->commit();
                return true;
            }
            
            $this->db->getConnection()->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao definir endereço principal: " . $e->getMessage());
            return false;
        }
    }

    // Excluir endereço
    public function excluir($id, $pessoaId) {
        try {
            // Verificar se não é o único endereço
            $total = $this->contarPorPessoa($pessoaId);
            if ($total <= 1) {
                return false; // Não pode excluir o último endereço
            }
            
            // Verificar se é principal
            $sql = "SELECT principal FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $pessoaId]);
            $endereco = $stmt->fetch();
            
            if (!$endereco) {
                return false; // Endereço não encontrado
            }
            
            $this->db->getConnection()->beginTransaction();
            
            // Excluir o endereço
            $sql = "DELETE FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$id, $pessoaId]);
            
            // Se era principal, definir outro como principal
            if ($endereco['principal']) {
                $sql = "UPDATE tb_endereco SET principal = 1 WHERE pessoa_id = ? LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$pessoaId]);
            }
            
            if ($resultado) {
                $this->db->getConnection()->commit();
                return true;
            }
            
            $this->db->getConnection()->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao excluir endereço: " . $e->getMessage());
            return false;
        }
    }

    // Contar endereços por pessoa
    public function contarPorPessoa($pessoaId) {
        $sql = "SELECT COUNT(*) FROM tb_endereco WHERE pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId]);
        return (int) $stmt->fetchColumn();
    }

    // Verificar se endereço pertence à pessoa
    public function pertenceAPessoa($enderecoId, $pessoaId) {
        $sql = "SELECT COUNT(*) FROM tb_endereco WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$enderecoId, $pessoaId]);
        return $stmt->fetchColumn() > 0;
    }

    // Validar CEP
    public function validarCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) === 8;
    }

    // Remover principal de outros endereços (método privado)
    private function removerPrincipalOutros($pessoaId, $exceto = null) {
        $sql = "UPDATE tb_endereco SET principal = 0 WHERE pessoa_id = ?";
        $params = [$pessoaId];
        
        if ($exceto) {
            $sql .= " AND id != ?";
            $params[] = $exceto;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Buscar endereços com informações extras
    public function buscarComDetalhes($pessoaId) {
        $enderecos = $this->buscarPorPessoa($pessoaId);
        
        foreach ($enderecos as &$endereco) {
            $endereco['endereco_completo'] = $this->formatarEnderecoCompleto($endereco);
        }
        
        return $enderecos;
    }

    // Formatar endereço completo
    public function formatarEnderecoCompleto($endereco) {
        $completo = $endereco['logradouro'] . ', ' . $endereco['numero'];
        
        if (!empty($endereco['complemento'])) {
            $completo .= ', ' . $endereco['complemento'];
        }
        
        $completo .= ' - ' . $endereco['bairro'];
        $completo .= ', ' . $endereco['cidade'] . ' - ' . $endereco['estado'];
        $completo .= ' CEP: ' . $endereco['cep'];
        
        return $completo;
    }

    // Buscar cidades únicas
    public function buscarCidades() {
        $sql = "SELECT DISTINCT cidade, estado FROM tb_endereco ORDER BY cidade";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
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
?>
