<?php
require_once 'core/Database.php';

class Notificacao
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Criar nova notificação
     */
    public function criarNotificacao($pessoaId, $titulo, $mensagem, $tipo = null, $referenciaId = null)
    {
        try {
            $sql = "INSERT INTO tb_notificacao (pessoa_id, titulo, mensagem, tipo, referencia_id) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$pessoaId, $titulo, $mensagem, $tipo, $referenciaId]);
            
            if ($resultado) {
                error_log("Notificação criada: Usuário $pessoaId, Tipo: $tipo, Título: $titulo");
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar notificações por usuário
     */
    public function buscarPorUsuario($userId, $filtros = [])
    {
        $sql = "SELECT * FROM tb_notificacao WHERE pessoa_id = ?";
        $params = [$userId];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = ?";
            $params[] = $filtros['tipo'];
        }

        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'lidas') {
                $sql .= " AND lida = 1";
            } elseif ($filtros['status'] === 'nao_lidas') {
                $sql .= " AND lida = 0";
            }
        }

        $sql .= " ORDER BY data_notificacao DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Contar notificações não lidas
     */
    public function contarNaoLidas($userId)
    {
        $sql = "SELECT COUNT(*) FROM tb_notificacao WHERE pessoa_id = ? AND lida = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarComoLida($notificacaoId, $userId)
    {
        $sql = "UPDATE tb_notificacao SET lida = 1 WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificacaoId, $userId]);
        return $stmt->rowCount() > 0; // <-- só retorna true se realmente alterou algo
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasComoLidas($userId)
    {
        $sql = "UPDATE tb_notificacao SET lida = 1 WHERE pessoa_id = ? AND lida = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Deletar notificação
     */
    public function deletar($notificacaoId, $userId)
    {
        $sql = "DELETE FROM tb_notificacao WHERE id = ? AND pessoa_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificacaoId, $userId]);
    }

    /**
     * Obter estatísticas das notificações do usuário
     */
    public function getEstatisticasUsuario($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN lida = 0 THEN 1 ELSE 0 END) as nao_lidas,
                    SUM(CASE WHEN lida = 1 THEN 1 ELSE 0 END) as lidas
                FROM tb_notificacao 
                WHERE pessoa_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Criar notificação automática baseada em template (MÉTODO ÚNICO)
     */
    public static function criarNotificacaoAutomatica($tipo, $pessoaId, $referenciaId, $dados = [])
    {
        try {
            $notificacao = new self();
            
            $templates = [
                'proposta_aceita' => [
                    'titulo' => '🎉 Proposta Aceita!',
                    'mensagem' => 'Sua proposta para "{servico}" foi aceita pelo cliente! Entre em contato para combinar os detalhes.'
                ],
                'proposta_recusada' => [
                    'titulo' => '❌ Proposta Recusada',
                    'mensagem' => 'Sua proposta para "{servico}" foi recusada pelo cliente.'
                ],
                'servico_concluido' => [
                    'titulo' => '✅ Serviço Concluído',
                    'mensagem' => 'O prestador {prestador} marcou o serviço "{servico}" como concluído. Confirme a conclusão e avalie o trabalho realizado.'
                ],
                'nova_proposta' => [
                    'titulo' => '📋 Nova Proposta Recebida',
                    'mensagem' => 'Você recebeu uma nova proposta para "{servico}". Clique para visualizar e responder.'
                ],
                'revisao_solicitada' => [
                    'titulo' => '⚠️ Revisão Solicitada',
                    'mensagem' => 'O cliente solicitou revisão no serviço "{servico}".'
                ],
                'avaliacao_recebida' => [
                    'titulo' => '⭐ Avaliação Recebida',
                    'mensagem' => 'Você recebeu uma nova avaliação! Parabéns pelo trabalho realizado.'
                ],
                'status_servico' => [
                    'titulo' => '🔄 Status Atualizado',
                    'mensagem' => 'O status do seu serviço foi atualizado.'
                ],
                'ordem_servico_gerada' => [
                    'titulo' => '📄 Ordem de Serviço Gerada',
                    'mensagem' => 'Uma Ordem de Serviço foi gerada para o seu serviço concluído.'
                ]
            ];
            
            if (!isset($templates[$tipo])) {
                error_log("Tipo de notificação não encontrado: $tipo");
                return false;
            }
            
            $template = $templates[$tipo];
            $titulo = $template['titulo'];
            $mensagem = $template['mensagem'];
            
            // Substituir dados no template
            foreach ($dados as $chave => $valor) {
                $mensagem = str_replace("{{$chave}}", $valor, $mensagem);
            }
            
            return $notificacao->criarNotificacao($pessoaId, $titulo, $mensagem, $tipo, $referenciaId);
            
        } catch (Exception $e) {
            error_log("Erro ao criar notificação automática: " . $e->getMessage());
            return false;
        }
    }
}
?>
