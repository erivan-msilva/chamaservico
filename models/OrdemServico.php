<?php
require_once 'core/Database.php';

class OrdemServico
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function criarOrdemServico($propostaId)
    {
        try {
            // Buscar dados da proposta aceita
            $sql = "SELECT p.*, s.titulo, s.descricao, s.cliente_id, s.endereco_id,
                           pr.nome as prestador_nome, pr.email as prestador_email, pr.cpf as prestador_cpf, pr.telefone as prestador_telefone,
                           c.nome as cliente_nome, c.email as cliente_email, c.cpf as cliente_cpf, c.telefone as cliente_telefone,
                           e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep,
                           ts.nome as tipo_servico_nome, s.urgencia
                    FROM tb_proposta p
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa pr ON p.prestador_id = pr.id
                    JOIN tb_pessoa c ON s.cliente_id = c.id
                    JOIN tb_endereco e ON s.endereco_id = e.id
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    WHERE p.id = ? AND p.status = 'aceita'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propostaId]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dados) {
                return false;
            }

            // Criar ordem de serviço
            $numeroOS = 'OS' . date('Y') . str_pad($propostaId, 6, '0', STR_PAD_LEFT);

            $sqlOS = "INSERT INTO tb_ordem_servico 
                      (numero_os, proposta_id, cliente_id, prestador_id, 
                       titulo, descricao, valor, status, data_criacao) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'em_andamento', NOW())";

            $stmtOS = $this->db->prepare($sqlOS);
            $success = $stmtOS->execute([
                $numeroOS,
                $propostaId,
                $dados['cliente_id'],
                $dados['prestador_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['valor']
            ]);

            if ($success) {
                $this->enviarNotificacaoOS($this->db->lastInsertId());
            }

            return $success;
        } catch (PDOException $e) {
            // Logar erro em um sistema de log
            error_log("Erro ao criar ordem de serviço: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorUsuario($userId, $tipo = 'cliente')
    {
        try {
            $campoUsuario = $tipo === 'cliente' ? 'cliente_id' : 'prestador_id';

            $sql = "SELECT os.*, 
                           c.nome as cliente_nome, c.cpf as cliente_cpf, c.email as cliente_email, c.telefone as cliente_telefone,
                           pr.nome as prestador_nome, pr.cpf as prestador_cpf, pr.email as prestador_email, pr.telefone as prestador_telefone,
                           s.titulo as servico_titulo, s.descricao as servico_descricao, s.urgencia,
                           ts.nome as tipo_servico_nome
                    FROM tb_ordem_servico os
                    JOIN tb_proposta p ON os.proposta_id = p.id
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa c ON os.cliente_id = c.id
                    JOIN tb_pessoa pr ON os.prestador_id = pr.id
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    WHERE os.$campoUsuario = ?
                    ORDER BY os.data_criacao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar ordens de serviço: " . $e->getMessage());
            return [];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $sql = "SELECT os.*, 
                           c.nome as cliente_nome, c.cpf as cliente_cpf, c.email as cliente_email, c.telefone as cliente_telefone,
                           pr.nome as prestador_nome, pr.cpf as prestador_cpf, pr.email as prestador_email, pr.telefone as prestador_telefone,
                           s.titulo as servico_titulo, s.descricao as servico_descricao, s.urgencia,
                           ts.nome as tipo_servico_nome,
                           e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
                    FROM tb_ordem_servico os
                    JOIN tb_proposta p ON os.proposta_id = p.id
                    JOIN tb_solicita_servico s ON p.solicitacao_id = s.id
                    JOIN tb_pessoa c ON os.cliente_id = c.id
                    JOIN tb_pessoa pr ON os.prestador_id = pr.id
                    JOIN tb_endereco e ON s.endereco_id = e.id
                    JOIN tb_tipo_servico ts ON s.tipo_servico_id = ts.id
                    WHERE os.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar ordem de serviço por ID: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarStatus($id, $status)
    {
        try {
            $sql = "UPDATE tb_ordem_servico SET status = ?, data_atualizacao = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status da ordem de serviço: " . $e->getMessage());
            return false;
        }
    }

    public function finalizar($id, $observacoes = null)
    {
        try {
            $sql = "UPDATE tb_ordem_servico 
                    SET status = 'finalizado', 
                        data_finalizacao = NOW(),
                        observacoes_finalizacao = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$observacoes, $id]);
        } catch (PDOException $e) {
            error_log("Erro ao finalizar ordem de serviço: " . $e->getMessage());
            return false;
        }
    }

    public function gerarPDF($osId, $comAssinaturas = false)
    {
        $os = $this->buscarPorId($osId);
        if (!$os) {
            return false;
        }

        ob_start();
?>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
            }

            .logo {
                font-size: 24px;
                font-weight: bold;
                color: #f5a522;
            }

            .os-number {
                font-size: 18px;
                font-weight: bold;
                margin: 10px 0;
            }

            .section {
                margin: 20px 0;
            }

            .section-title {
                font-size: 14px;
                font-weight: bold;
                background: #f5f5f5;
                padding: 5px;
            }

            .info-table {
                width: 100%;
                border-collapse: collapse;
            }

            .info-table td {
                padding: 8px;
                border: 1px solid #ddd;
            }

            .info-table .label {
                font-weight: bold;
                background: #f9f9f9;
                width: 30%;
            }

            .signature-area {
                border: 1px solid #000;
                height: 80px;
                margin: 10px 0;
            }

            .footer {
                text-align: center;
                font-size: 10px;
                color: #666;
                margin-top: 50px;
            }
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
                    <td><?= ucfirst(htmlspecialchars($os['urgencia'] ?? 'Não informado')) ?></td>
                </tr>
                <tr>
                    <td class="label">Título:</td>
                    <td colspan="3"><?= htmlspecialchars($os['servico_titulo']) ?></td>
                </tr>
                <tr>
                    <td class="label">Descrição:</td>
                    <td colspan="3"><?= nl2br(htmlspecialchars($os['servico_descricao'])) ?></td>
                </tr>
                <tr>
                    <td class="label">Proposta do Prestador:</td>
                    <td colspan="3"><?= nl2br(htmlspecialchars($os['proposta_descricao'] ?? 'Não informado')) ?></td>
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
                        <?php if (!empty($os['complemento'])): ?>
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
                    <td><strong>R$ <?= number_format($os['valor'], 2, ',', '.') ?></strong></td>
                    <td class="label">Prazo de Execução:</td>
                    <td><?= htmlspecialchars($os['prazo_execucao'] ?? 'Não informado') ?> dia(s)</td>
                </tr>
                <tr>
                    <td class="label">Data de Início:</td>
                    <td><?= !empty($os['data_inicio']) ? date('d/m/Y H:i', strtotime($os['data_inicio'])) : 'Não informado' ?></td>
                    <td class="label">Data de Conclusão:</td>
                    <td><?= !empty($os['data_conclusao']) ? date('d/m/Y H:i', strtotime($os['data_conclusao'])) : 'Não informado' ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($os['observacoes_prestador'])): ?>
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
                        <?php if ($comAssinaturas && !empty($os['assinatura_cliente'])): ?>
                            <div style="margin: 10px 0;">✓ Assinado digitalmente</div>
                            <div style="font-size: 10px;">
                                <?= !empty($os['data_assinatura_cliente']) ? date('d/m/Y H:i', strtotime($os['data_assinatura_cliente'])) : 'Não informado' ?>
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
                        <?php if ($comAssinaturas && !empty($os['assinatura_prestador'])): ?>
                            <div style="margin: 10px 0;">✓ Assinado digitalmente</div>
                            <div style="font-size: 10px;">
                                <?= !empty($os['data_assinatura_prestador']) ? date('d/m/Y H:i', strtotime($os['data_assinatura_prestador'])) : 'Não informado' ?>
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

        $html = ob_get_clean();

        // Aqui você deve implementar a geração do PDF usando uma biblioteca como DomPDF
        // Exemplo com DomPDF (descomente e configure conforme necessário):
        /*
        require_once 'vendor/autoload.php';
        use Dompdf\Dompdf;
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $caminhoArquivo = "os_{$osId}.pdf";
        file_put_contents($caminhoArquivo, $output);
        return $caminhoArquivo;
        */

        return $html; // Retorna HTML por enquanto, substitua pelo caminho do PDF quando implementar
    }

    public function enviarPorEmail($osId, $emailDestinatario = null)
    {
        try {
            $os = $this->buscarPorId($osId);
            if (!$os) {
                return false;
            }

            $caminhoArquivo = $this->gerarPDF($osId, true);

            $assunto = "Ordem de Serviço #{$os['numero_os']} - ChamaServiço";
            $mensagem = "
                <h2>Ordem de Serviço Gerada</h2>
                <p>Prezado(a) {$os['cliente_nome']},</p>
                <p>A Ordem de Serviço para o serviço <strong>{$os['servico_titulo']}</strong> foi gerada com sucesso.</p>
                <p><strong>Detalhes:</strong></p>
                <ul>
                    <li>Número da OS: <strong>{$os['numero_os']}</strong></li>
                    <li>Prestador: {$os['prestador_nome']}</li>
                    <li>Valor: R$ " . number_format($os['valor'], 2, ',', '.') . "</li>
                    <li>Data de Conclusão: " . (!empty($os['data_conclusao']) ? date('d/m/Y H:i', strtotime($os['data_conclusao'])) : 'Não informado') . "</li>
                </ul>
                <p>Em anexo você encontra a Ordem de Serviço completa em PDF.</p>
                <p>Atenciosamente,<br>Equipe ChamaServiço</p>
            ";

            $email = $emailDestinatario ?: $os['cliente_email'];

            // Implementar envio de email com PHPMailer
            /*
            require_once 'vendor/autoload.php';
            use PHPMailer\PHPMailer\PHPMailer;
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_username';
            $mail->Password = 'your_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('from@example.com', 'ChamaServiço');
            $mail->addAddress($email);
            $mail->addAttachment($caminhoArquivo);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            
            return $mail->send();
            */

            return true; // Simular sucesso por enquanto
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }

    private function enviarNotificacaoOS($osId)
    {
        try {
            $os = $this->buscarPorId($osId);

            if ($os) {
                require_once 'models/Notificacao.php';
                $notificacaoModel = new Notificacao();

                $titulo = "Ordem de Serviço Gerada";
                $mensagem = "A Ordem de Serviço #{$os['numero_os']} foi gerada para o serviço '{$os['servico_titulo']}'. Clique para visualizar e assinar.";

                $notificacaoModel->criarNotificacao(
                    $os['cliente_id'],
                    $titulo,
                    $mensagem,
                    'ordem_servico',
                    $osId
                );
            }
        } catch (Exception $e) {
            error_log("Erro ao enviar notificação: " . $e->getMessage());
        }
    }
}
?>