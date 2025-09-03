<?php
require_once 'core/Database.php';

class Avaliacao
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function verificarAvaliacaoExistente($solicitacaoId, $avaliadorId, $avaliadoId)
    {
        $sql = "SELECT COUNT(*) FROM tb_avaliacao 
                WHERE solicitacao_id = ? AND avaliador_id = ? AND avaliado_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$solicitacaoId, $avaliadorId, $avaliadoId]);
        return $stmt->fetchColumn() > 0;
    }

    public function criarAvaliacao($dados)
    {
        try {
            $sql = "INSERT INTO tb_avaliacao 
                    (solicitacao_id, avaliador_id, avaliado_id, nota, comentario) 
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['solicitacao_id'],
                $dados['avaliador_id'],
                $dados['avaliado_id'],
                $dados['nota'],
                $dados['comentario']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao criar avaliação: " . $e->getMessage());
            return false;
        }
    }

    public function buscarAvaliacoesPorPrestador($prestadorId)
    {
        $sql = "SELECT a.*, p.nome as avaliador_nome, s.titulo as servico_titulo
                FROM tb_avaliacao a
                JOIN tb_pessoa p ON a.avaliador_id = p.id
                JOIN tb_solicita_servico s ON a.solicitacao_id = s.id
                WHERE a.avaliado_id = ?
                ORDER BY a.data_avaliacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetchAll();
    }

    public function calcularMediaPrestador($prestadorId)
    {
        $sql = "SELECT AVG(nota) as media, COUNT(*) as total
                FROM tb_avaliacao 
                WHERE avaliado_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prestadorId]);
        return $stmt->fetch();
    }
}
?>

