<?php
require_once 'core/Database.php';

class OrdemServico {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function criarOrdemServico($propostaId, $prestadorId) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Buscar dados completos do serviço
            $dadosServico = $this->buscarDadosServico($propostaId, $prestadorId);
            
            if (!$dadosServico) {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
            // Gerar número da OS
            $numeroOS = $this->gerarNumeroOS();
            
            // Criar registro da OS
            $sql = "INSERT INTO tb_ordem_servico 
                    (numero_os, proposta_id, prestador_id, cliente_id, solicitacao_id, 
                     valor_servico, data_inicio, data_conclusao, status, observacoes_prestador) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pendente_assinatura', ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $numeroOS,
                $propostaId,
                $prestadorId,
                $dadosServico['cliente_id'],
                $dadosServico['solicitacao_id'],
                $dadosServico['valor'],
                $dadosServico['data_aceite'],
                $dadosServico['observacoes_prestador'] ?? ''
            ]);
            
            if ($resultado) {
                $osId = $this->db->lastInsertId();
                $this->db->getConnection()->commit();
                
                // Gerar PDF da OS
                $this->gerarPDF($osId);
                
                // Enviar notificação para o cliente
                $this->enviarNotificacaoOS($osId);
                
                return $osId;
            } else {
                $this->db->getConnection()->rollBack();
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Erro ao criar OS: " . $e->getMessage());
            return false;
        }
    }
    
    private function buscarDadosServico($propostaId, $prestadorId) {
        $sql = "SELECT p.*, s.titulo, s.descricao, s.cliente_id, s.id as solicitacao_id,
                       s.data_atendimento, s.urgencia, ts.nome as tipo_servico_nome,
                       c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
                       pr.nome as prestador_nome, pr.email as prestador_email, pr.telefone as prestador_telefone,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                FROM tb_proposta p
                JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_pessoa c ON s.cliente_id = c.id
                JOIN tb_pessoa pr ON p.prestador_id = pr.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                WHERE p.id = ? AND p.prestador_id = ? AND p.status = 'aceita'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propostaId, $prestadorId]);
        return $stmt->fetch();
    }
    
    private function gerarNumeroOS() {
        $ano = date('Y');
        $sql = "SELECT COUNT(*) + 1 as proximo FROM tb_ordem_servico WHERE YEAR(data_conclusao) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ano]);
        $resultado = $stmt->fetch();
        
        $sequencial = str_pad($resultado['proximo'], 6, '0', STR_PAD_LEFT);
        return "OS{$ano}{$sequencial}";
    }
    
    public function buscarPorId($osId, $userId = null) {
        $sql = "SELECT os.*, s.titulo, s.descricao, s.urgencia, ts.nome as tipo_servico_nome,
                       c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
                       c.cpf as cliente_cpf,
                       pr.nome as prestador_nome, pr.email as prestador_email, pr.telefone as prestador_telefone,
                       pr.cpf as prestador_cpf,
                       e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep,
                       p.prazo_execucao, p.descricao as proposta_descricao
                FROM tb_ordem_servico os
                JOIN tb_solicita_servico s ON os.solicitacao_id = s.id
                JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                JOIN tb_pessoa c ON os.cliente_id = c.id
                JOIN tb_pessoa pr ON os.prestador_id = pr.id
                JOIN tb_endereco e ON s.endereco_id = e.id
                JOIN tb_proposta p ON os.proposta_id = p.id
                WHERE os.id = ?";
        
        $params = [$osId];
        
        if ($userId) {
            $sql .= " AND (os.cliente_id = ? OR os.prestador_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function assinarOS($osId, $userId, $tipoAssinatura, $dadosAssinatura) {
        try {
            $campo = $tipoAssinatura === 'cliente' ? 'assinatura_cliente' : 'assinatura_prestador';
            $campoData = $tipoAssinatura === 'cliente' ? 'data_assinatura_cliente' : 'data_assinatura_prestador';
            
            $sql = "UPDATE tb_ordem_servico 
                    SET {$campo} = ?, {$campoData} = NOW()
                    WHERE id = ? AND {$tipoAssinatura}_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                json_encode($dadosAssinatura),
                $osId,
                $userId
            ]);
            
            if ($resultado) {
                // Verificar se ambas as partes assinaram
                $this->verificarStatusAssinatura($osId);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao assinar OS: " . $e->getMessage());
            return false;
        }
    }
    
    private function verificarStatusAssinatura($osId) {
        $sql = "SELECT assinatura_cliente, assinatura_prestador FROM tb_ordem_servico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId]);
        $os = $stmt->fetch();
        
        if ($os['assinatura_cliente'] && $os['assinatura_prestador']) {
            // Ambas as partes assinaram - finalizar OS
            $sqlUpdate = "UPDATE tb_ordem_servico SET status = 'finalizada' WHERE id = ?";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([$osId]);
            
            // Gerar PDF final com assinaturas
            $this->gerarPDF($osId, true);
        }
    }
    
    public function gerarPDF($osId, $comAssinaturas = false) {
        require_once 'vendor/autoload.php'; // Para TCPDF ou FPDF
        
        $os = $this->buscarPorId($osId);
        if (!$os) return false;
        
        // Usar TCPDF para gerar PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configurações do documento
        $pdf->SetCreator('ChamaServiço');
        $pdf->SetAuthor('Sistema ChamaServiço');
        $pdf->SetTitle('Ordem de Serviço - ' . $os['numero_os']);
        $pdf->SetSubject('Ordem de Serviço');
        
        // Adicionar página
        $pdf->AddPage();
        
        // Conteúdo do PDF
        $html = $this->gerarHTMLOrdemServico($os, $comAssinaturas);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Salvar PDF
        $nomeArquivo = 'os_' . $os['numero_os'] . ($comAssinaturas ? '_assinada' : '') . '.pdf';
        $caminhoArquivo = 'uploads/ordens_servico/' . $nomeArquivo;
        
        // Criar diretório se não existir
        if (!is_dir('uploads/ordens_servico/')) {
            mkdir('uploads/ordens_servico/', 0755, true);
        }
        
        $pdf->Output($caminhoArquivo, 'F');
        
        // Atualizar caminho do arquivo na OS
        $sql = "UPDATE tb_ordem_servico SET arquivo_pdf = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nomeArquivo, $osId]);
        
        return $caminhoArquivo;
    }
    
    private function gerarHTMLOrdemServico($os, $comAssinaturas = false) {
        ob_start();
        ?>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 24px; font-weight: bold; color: #f5a522; }
            .os-number { font-size: 18px; font-weight: bold; margin: 10px 0; }
            .section { margin: 20px 0; }
            .section-title { font-size: 14px; font-weight: bold; background: #f5f5f5; padding: 5px; }
            .info-table { width: 100%; border-collapse: collapse; }
            .info-table td { padding: 8px; border: 1px solid #ddd; }
            .info-table .label { font-weight: bold; background: #f9f9f9; width: 30%; }
            .signature-area { border: 1px solid #000; height: 80px; margin: 10px 0; }
            .footer { text-align: center; font-size: 10px; color: #666; margin-top: 50px; }
        </style>
        
        <div class="header">
            <div class="logo">CHAMASERVIÇO</div>
            <div>Sistema de Solicitação de Serviços</div>
            <div class="os-number">ORDEM DE SERVIÇO Nº <?= htmlspecialchars($os['numero_os']) ?></div>
        </div>
        
        <div class="section">
            <div class="section-title">DADOS DO CLIENTE</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nome:</td>
                    <td><?= htmlspecialchars($os['cliente_nome']) ?></td>
                    <td class="label">CPF:</td>
                    <td><?= htmlspecialchars($os['cliente_cpf'] ?? 'Não informado') ?></td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td><?= htmlspecialchars($os['cliente_email']) ?></td>
                    <td class="label">Telefone:</td>
                    <td><?= htmlspecialchars($os['cliente_telefone'] ?? 'Não informado') ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">DADOS DO PRESTADOR</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nome:</td>
                    <td><?= htmlspecialchars($os['prestador_nome']) ?></td>
                    <td class="label">CPF:</td>
                    <td><?= htmlspecialchars($os['prestador_cpf'] ?? 'Não informado') ?></td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td><?= htmlspecialchars($os['prestador_email']) ?></td>
                    <td class="label">Telefone:</td>
                    <td><?= htmlspecialchars($os['prestador_telefone'] ?? 'Não informado') ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">DADOS DO SERVIÇO</div>
            <table class="info-table">
                <tr>
                    <td class="label">Tipo de Serviço:</td>
                    <td><?= htmlspecialchars($os['tipo_servico_nome']) ?></td>
                    <td class="label">Urgência:</td>
                    <td><?= ucfirst($os['urgencia']) ?></td>
                </tr>
                <tr>
                    <td class="label">Título:</td>
                    <td colspan="3"><?= htmlspecialchars($os['titulo']) ?></td>
                </tr>
                <tr>
                    <td class="label">Descrição:</td>
                    <td colspan="3"><?= nl2br(htmlspecialchars($os['descricao'])) ?></td>
                </tr>
                <tr>
                    <td class="label">Proposta do Prestador:</td>
                    <td colspan="3"><?= nl2br(htmlspecialchars($os['proposta_descricao'])) ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">LOCAL DO SERVIÇO</div>
            <table class="info-table">
                <tr>
                    <td class="label">Endereço:</td>
                    <td colspan="3">
                        <?= htmlspecialchars($os['logradouro']) ?>, <?= htmlspecialchars($os['numero']) ?>
                        <?php if ($os['complemento']): ?>
                            - <?= htmlspecialchars($os['complemento']) ?>
                        <?php endif; ?><br>
                        <?= htmlspecialchars($os['bairro']) ?> - <?= htmlspecialchars($os['cidade']) ?>/<?= htmlspecialchars($os['estado']) ?><br>
                        CEP: <?= htmlspecialchars($os['cep']) ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">INFORMAÇÕES FINANCEIRAS</div>
            <table class="info-table">
                <tr>
                    <td class="label">Valor do Serviço:</td>
                    <td><strong>R$ <?= number_format($os['valor_servico'], 2, ',', '.') ?></strong></td>
                    <td class="label">Prazo de Execução:</td>
                    <td><?= $os['prazo_execucao'] ?> dia(s)</td>
                </tr>
                <tr>
                    <td class="label">Data de Início:</td>
                    <td><?= date('d/m/Y H:i', strtotime($os['data_inicio'])) ?></td>
                    <td class="label">Data de Conclusão:</td>
                    <td><?= date('d/m/Y H:i', strtotime($os['data_conclusao'])) ?></td>
                </tr>
            </table>
        </div>
        
        <?php if ($os['observacoes_prestador']): ?>
        <div class="section">
            <div class="section-title">OBSERVAÇÕES DO PRESTADOR</div>
            <div style="padding: 10px; border: 1px solid #ddd;">
                <?= nl2br(htmlspecialchars($os['observacoes_prestador'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-title">ASSINATURAS</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; text-align: center;">
                        <div><strong>CLIENTE</strong></div>
                        <?php if ($comAssinaturas && $os['assinatura_cliente']): ?>
                            <div style="margin: 10px 0;">✓ Assinado digitalmente</div>
                            <div style="font-size: 10px;">
                                <?= date('d/m/Y H:i', strtotime($os['data_assinatura_cliente'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="signature-area"></div>
                        <?php endif; ?>
                        <div style="border-top: 1px solid #000; margin-top: 10px; padding-top: 5px;">
                            <?= htmlspecialchars($os['cliente_nome']) ?>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center;">
                        <div><strong>PRESTADOR</strong></div>
                        <?php if ($comAssinaturas && $os['assinatura_prestador']): ?>
                            <div style="margin: 10px 0;">✓ Assinado digitalmente</div>
                            <div style="font-size: 10px;">
                                <?= date('d/m/Y H:i', strtotime($os['data_assinatura_prestador'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="signature-area"></div>
                        <?php endif; ?>
                        <div style="border-top: 1px solid #000; margin-top: 10px; padding-top: 5px;">
                            <?= htmlspecialchars($os['prestador_nome']) ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <div>Este documento foi gerado automaticamente pelo sistema ChamaServiço</div>
            <div>Data de emissão: <?= date('d/m/Y H:i') ?></div>
            <?php if ($comAssinaturas): ?>
                <div><strong>DOCUMENTO ASSINADO DIGITALMENTE</strong></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function enviarPorEmail($osId, $emailDestinatario = null) {
        $os = $this->buscarPorId($osId);
        if (!$os) return false;
        
        $caminhoArquivo = $this->gerarPDF($osId, true);
        
        // Configurar email (usando PHPMailer ou função mail())
        $assunto = "Ordem de Serviço #{$os['numero_os']} - ChamaServiço";
        $mensagem = "
        <h2>Ordem de Serviço Gerada</h2>
        <p>Prezado(a) {$os['cliente_nome']},</p>
        <p>A Ordem de Serviço para o serviço <strong>{$os['titulo']}</strong> foi gerada com sucesso.</p>
        <p><strong>Detalhes:</strong></p>
        <ul>
            <li>Número da OS: <strong>{$os['numero_os']}</strong></li>
            <li>Prestador: {$os['prestador_nome']}</li>
            <li>Valor: R$ " . number_format($os['valor_servico'], 2, ',', '.') . "</li>
            <li>Data de Conclusão: " . date('d/m/Y H:i', strtotime($os['data_conclusao'])) . "</li>
        </ul>
        <p>Em anexo você encontra a Ordem de Serviço completa em PDF.</p>
        <p>Atenciosamente,<br>Equipe ChamaServiço</p>
        ";
        
        $email = $emailDestinatario ?: $os['cliente_email'];
        
        // Implementar envio de email aqui
        // return $this->enviarEmailComAnexo($email, $assunto, $mensagem, $caminhoArquivo);
        
        return true; // Simular sucesso por enquanto
    }
    
    private function enviarNotificacaoOS($osId) {
        $os = $this->buscarPorId($osId);
        
        if ($os) {
            require_once 'models/Notificacao.php';
            $notificacaoModel = new Notificacao();
            
            $titulo = "Ordem de Serviço Gerada";
            $mensagem = "A Ordem de Serviço #{$os['numero_os']} foi gerada para o serviço '{$os['titulo']}'. Clique para visualizar e assinar.";
            
            $notificacaoModel->criarNotificacao(
                $os['cliente_id'],
                $titulo,
                $mensagem,
                'ordem_servico',
                $osId
            );
        }
    }
    
    public function buscarPorUsuario($userId, $tipo = null) {
        $sql = "SELECT os.*, s.titulo, pr.nome as prestador_nome, c.nome as cliente_nome
                FROM tb_ordem_servico os
                JOIN tb_solicita_servico s ON os.solicitacao_id = s.id
                JOIN tb_pessoa pr ON os.prestador_id = pr.id
                JOIN tb_pessoa c ON os.cliente_id = c.id
                WHERE (os.cliente_id = ? OR os.prestador_id = ?)";
        
        $params = [$userId, $userId];
        
        if ($tipo) {
            $sql .= " AND os.status = ?";
            $params[] = $tipo;
        }
        
        $sql .= " ORDER BY os.data_conclusao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
