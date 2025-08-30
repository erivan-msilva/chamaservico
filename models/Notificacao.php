<?php
require_once 'core/Database.php';

class Notificacao
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Criar nova notificação
    public function criarNotificacao($pessoaId, $titulo, $mensagem, $tipo = null, $referenciaId = null)
    {
        try {
            // CORREÇÃO: Garantir que pessoaId seja um valor único
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            $sql = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$pessoaId, $titulo, $mensagem, $tipo, $referenciaId]);
        } catch (Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    // Contar notificações não lidas
    public function contarNaoLidas($pessoaId)
    {
        try {
            // CORREÇÃO: Garantir que $pessoaId seja um valor único
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            // CORREÇÃO LINHA 54: Adicionar validação e casting
            if (empty($pessoaId) || !is_numeric($pessoaId)) {
                error_log("ID de pessoa inválido para contagem de notificações: " . print_r($pessoaId, true));
                return 0;
            }

            $sql = "SELECT COUNT(*) FROM tb_notificacao WHERE pessoa_id = ? AND lida = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$pessoaId]);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erro ao contar notificações não lidas: " . $e->getMessage());
            return 0;
        }
    }

    // Buscar notificações de um usuário
    public function buscarPorUsuario($pessoaId, $limit = 10, $filtros = [])
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            $sql = "SELECT * FROM tb_notificacao WHERE pessoa_id = ?";
            $params = [(int)$pessoaId];

            // Aplicar filtros
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filtros['tipo'];
            }

            if (isset($filtros['lida']) && $filtros['lida'] !== '') {
                $sql .= " AND lida = ?";
                $params[] = (int)$filtros['lida'];
            }

            $sql .= " ORDER BY data_notificacao DESC LIMIT ?";
            $params[] = (int)$limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar notificações: " . $e->getMessage());
            return [];
        }
    }

    // Marcar notificação como lida
    public function marcarComoLida($notificacaoId, $pessoaId)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            if (is_array($notificacaoId)) {
                $notificacaoId = $notificacaoId[0] ?? 0;
            }

            $sql = "UPDATE tb_notificacao SET lida = 1 
                    WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$notificacaoId, (int)$pessoaId]);
        } catch (Exception $e) {
            error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
            return false;
        }
    }

    // Marcar todas as notificações como lidas
    public function marcarTodasComoLidas($pessoaId)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            $sql = "UPDATE tb_notificacao SET lida = 1 WHERE pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$pessoaId]);
        } catch (Exception $e) {
            error_log("Erro ao marcar todas as notificações como lidas: " . $e->getMessage());
            return false;
        }
    }

    // Deletar notificação
    public function deletar($notificacaoId, $pessoaId)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            if (is_array($notificacaoId)) {
                $notificacaoId = $notificacaoId[0] ?? 0;
            }

            $sql = "DELETE FROM tb_notificacao WHERE id = ? AND pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$notificacaoId, (int)$pessoaId]);
        } catch (Exception $e) {
            error_log("Erro ao deletar notificação: " . $e->getMessage());
            return false;
        }
    }

    // Buscar notificação por ID
    public function buscarPorId($notificacaoId, $pessoaId = null)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            if (is_array($notificacaoId)) {
                $notificacaoId = $notificacaoId[0] ?? 0;
            }

            $sql = "SELECT * FROM tb_notificacao WHERE id = ?";
            $params = [(int)$notificacaoId];

            if ($pessoaId) {
                $sql .= " AND pessoa_id = ?";
                $params[] = (int)$pessoaId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar notificação por ID: " . $e->getMessage());
            return false;
        }
    }

    // Limpar notificações antigas (mais de 30 dias)
    public function limparAntigas($dias = 30)
    {
        try {
            $sql = "DELETE FROM tb_notificacao 
                    WHERE data_notificacao < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$dias]);
        } catch (Exception $e) {
            error_log("Erro ao limpar notificações antigas: " . $e->getMessage());
            return false;
        }
    }

    // Estatísticas de notificações
    public function getEstatisticas($pessoaId)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN lida = 0 THEN 1 ELSE 0 END) as nao_lidas,
                        SUM(CASE WHEN lida = 1 THEN 1 ELSE 0 END) as lidas
                    FROM tb_notificacao 
                    WHERE pessoa_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$pessoaId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de notificações: " . $e->getMessage());
            return [
                'total' => 0,
                'nao_lidas' => 0,
                'lidas' => 0
            ];
        }
    }

    // Notificações por tipo
    public function buscarPorTipo($pessoaId, $tipo, $limit = 5)
    {
        try {
            if (is_array($pessoaId)) {
                $pessoaId = $pessoaId[0] ?? 0;
            }

            $sql = "SELECT * FROM tb_notificacao 
                    WHERE pessoa_id = ? AND tipo = ? 
                    ORDER BY data_notificacao DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$pessoaId, $tipo, (int)$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar notificações por tipo: " . $e->getMessage());
            return [];
        }
    }

    // Criar notificação em tempo real
    public function criarNotificacaoTempoReal($pessoaId, $titulo, $mensagem, $tipo = null, $referenciaId = null)
    {
        try {
            // Criar notificação normal
            $resultado = $this->criarNotificacao($pessoaId, $titulo, $mensagem, $tipo, $referenciaId);

            if ($resultado) {
                // Aqui você pode adicionar lógica para WebSockets ou Server-Sent Events
                // Por enquanto, usaremos polling com AJAX

                // Log para debug
                error_log("Nova notificação criada para usuário $pessoaId: $titulo");

                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar notificação em tempo real: " . $e->getMessage());
            return false;
        }
    }
}
