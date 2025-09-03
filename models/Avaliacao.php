<?php
require_once 'core/Database.php';

class Avaliacao
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Criar nova avaliação
     */
    public function criar($dados)
    {
        try {
            $sql = "INSERT INTO tb_avaliacao 
                    (solicitacao_id, avaliador_id, avaliado_id, nota, comentario, recomendaria) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['solicitacao_id'],
                $dados['avaliador_id'],
                $dados['avaliado_id'],
                $dados['nota'],
                $dados['comentario'],
                $dados['recomendaria'] ?? 0
            ]);
        } catch (Exception $e) {
            error_log("Erro ao criar avaliação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se já avaliou um serviço
     */
    public function jaAvaliou($solicitacaoId, $avaliadorId)
    {
        $sql = "SELECT COUNT(*) FROM tb_avaliacao WHERE solicitacao_id = ? AND avaliador_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $avaliadorId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Buscar avaliações de um prestador
     */
    public function buscarPorPrestador($prestadorId, $limit = 10)
    {
        $sql = "SELECT a.*, s.titulo as servico_titulo, 
                       c.nome as avaliador_nome, c.foto_perfil as avaliador_foto
                FROM tb_avaliacao a
                JOIN tb_solicita_servico s ON a.solicitacao_id = s.id
                JOIN tb_pessoa c ON a.avaliador_id = c.id
                WHERE a.avaliado_id = ?
                ORDER BY a.data_avaliacao DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Calcular média de avaliações de um prestador
     */
    public function calcularMediaPrestador($prestadorId)
    {
        $sql = "SELECT AVG(nota) as media, COUNT(*) as total 
                FROM tb_avaliacao 
                WHERE avaliado_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        $resultado = $stmt->fetch();

        return [
            'media' => $resultado['media'] ? round($resultado['media'], 1) : 0,
            'total' => $resultado['total']
        ];
    }
}
?>
