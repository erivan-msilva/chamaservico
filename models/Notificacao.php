<?php
require_once 'core/Database.php';

class Notificacao
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function criarNotificacao($pessoaId, $titulo, $mensagem, $tipo = 'info', $referenciaId = null)
    {
        $sql = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$pessoaId, $titulo, $mensagem, $tipo, $referenciaId]);
    }

    public function buscarPorUsuario($pessoaId, $limit = 20)
    {
        $sql = "SELECT * FROM tb_notificacao 
                WHERE pessoa_id = ? 
                ORDER BY data_notificacao DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pessoaId, $limit]);
        return $stmt->fetchAll();
    }

    public function contarNaoLidas($pessoaId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM tb_notificacao WHERE pessoa_id = ? AND lida = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pessoaId]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erro ao contar notificações não lidas: " . $e->getMessage());
            return 0;
        }
    }

    public function marcarComoLida($notificacaoId, $pessoaId)
    {
        try {
            $sql = "UPDATE tb_notificacao SET lida = 1 WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$notificacaoId, $pessoaId]);
            error_log("Query executada: UPDATE tb_notificacao SET lida = 1 WHERE id = $notificacaoId AND pessoa_id = $pessoaId");
            error_log("Linhas afetadas: " . $stmt->rowCount());
            return $resultado && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLidas($pessoaId)
    {
        try {
            $sql = "UPDATE tb_notificacao SET lida = 1 WHERE pessoa_id = ? AND lida = 0";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$pessoaId]);
            error_log("Query executada: UPDATE tb_notificacao SET lida = 1 WHERE pessoa_id = $pessoaId AND lida = 0");
            error_log("Linhas afetadas: " . $stmt->rowCount());
            return $resultado;
        } catch (Exception $e) {
            error_log("Erro ao marcar todas as notificações como lidas: " . $e->getMessage());
            return false;
        }
    }

    public function deletar($notificacaoId, $pessoaId)
    {
        try {
            $sql = "DELETE FROM tb_notificacao WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificacaoId, $pessoaId]);
        } catch (Exception $e) {
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            return false;
        }
    }

    public function deletarNotificacao($notificacaoId, $pessoaId)
    {
        try {
            $sql = "DELETE FROM tb_notificacao WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificacaoId, $pessoaId]);
        } catch (Exception $e) {
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorTipo($pessoaId, $tipo, $limit = 5)
    {
        try {
            $sql = "SELECT * FROM tb_notificacao WHERE pessoa_id = ? AND tipo = ? ORDER BY data_notificacao DESC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pessoaId, $tipo, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar notificações por tipo: " . $e->getMessage());
            return [];
        }
    }

    public function buscarNotificacoesPorUsuario($userId, $filtros = [])
    {
        $sql = "SELECT * FROM tb_notificacao WHERE pessoa_id = ?";
        $params = [$userId];

        // Filtros opcionais
        if (!empty($filtros['lida'])) {
            $sql .= " AND lida = ?";
            $params[] = $filtros['lida'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = ?";
            $params[] = $filtros['tipo'];
        }

        $sql .= " ORDER BY data_notificacao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
