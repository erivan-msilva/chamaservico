<?php
class EmailService
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'smtp_host' => 'h63.servidorhh.com',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_username' => 'chamaservico@tds104-senac.online',
            'smtp_password' => 'Chama@Servico123',
            'from_email' => 'chamaservico@tds104-senac.online',
            'from_name' => 'ChamaServiço'
        ];
    }

    public function enviarEmailRedefinicao($email, $nome, $token)
    {
        $assunto = '🔒 Redefinição de Senha - ChamaServiço';
        $linkRedefinicao = "http://localhost:8083/chamaservico/redefinir-senha-nova?token=" . $token;
        
        $corpo = $this->gerarTemplateRedefinicao($nome, $linkRedefinicao);

        return $this->enviarComPHPMailer($email, $assunto, $corpo);
    }

    public function enviarEmailConfirmacaoRedefinicao($email, $nome)
    {
        $assunto = '✅ Senha Alterada com Sucesso - ChamaServiço';
        $corpo = $this->gerarTemplateConfirmacao($nome);

        return $this->enviarComPHPMailer($email, $assunto, $corpo);
    }

    private function enviarComPHPMailer($email, $assunto, $corpo)
    {
        try {
            // Incluir as classes PHPMailer que você já tem
            require_once __DIR__ . '/../controllers/PHPMailer.class.php';
            require_once __DIR__ . '/../controllers/SMTP.class.php';

            $mail = new PHPMailer(true);

            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Debug para desenvolvimento (remova em produção)
            $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages
            $mail->Debugoutput = 'error_log';

            // Destinatários
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($email);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpo;

            $result = $mail->send();
            
            if ($result) {
                error_log("✅ Email enviado com sucesso para: $email");
                return true;
            } else {
                error_log("❌ Falha no envio para: $email");
                return false;
            }

        } catch (Exception $e) {
            error_log("❌ Erro PHPMailer: " . $e->getMessage());
            
            // Em desenvolvimento, vamos simular o envio
            if (defined('AMBIENTE') && AMBIENTE === 'desenvolvimento') {
                return $this->simularEnvio($email, $assunto, "Link: http://localhost:8083/chamaservico/redefinir-senha-nova?token=" . substr($corpo, -32));
            }
            
            return false;
        }
    }

    private function simularEnvio($email, $assunto, $conteudo)
    {
        // Para desenvolvimento: simular envio e salvar em arquivo
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'emails_simulados.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logContent = "================================\n";
        $logContent .= "📧 EMAIL SIMULADO\n";
        $logContent .= "DATA: $timestamp\n";
        $logContent .= "PARA: $email\n";
        $logContent .= "ASSUNTO: $assunto\n";
        $logContent .= "CONTEÚDO: $conteudo\n";
        $logContent .= "================================\n\n";

        file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
        
        error_log("📧 Email simulado salvo para: $email");
        return true;
    }

    private function gerarTemplateRedefinicao($nome, $link)
    {
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>Redefinição de Senha</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 2px solid #283579; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { color: #283579; font-size: 24px; font-weight: bold; }
                .logo-accent { color: #f5a522; }
                .button { display: inline-block; background: #283579; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>CHAMA<span class='logo-accent'>SERVIÇO</span></div>
                    <h2 style='color: #283579;'>🔒 Redefinição de Senha</h2>
                </div>
                
                <p>Olá <strong>" . htmlspecialchars($nome) . "</strong>,</p>
                
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no ChamaServiço.</p>
                
                <div style='text-align: center;'>
                    <a href='" . $link . "' class='button'>🔑 REDEFINIR MINHA SENHA</a>
                </div>
                
                <div class='alert'>
                    <strong>⚠️ Importante:</strong>
                    <ul>
                        <li>Este link expira em <strong>1 hora</strong></li>
                        <li>Se você não solicitou esta redefinição, ignore este e-mail</li>
                    </ul>
                </div>
                
                <p><strong>Link alternativo:</strong><br>
                <span style='color: #283579; word-break: break-all;'>" . $link . "</span></p>
                
                <div class='footer'>
                    <p>© 2024 ChamaServiço - Sistema de email em desenvolvimento</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function gerarTemplateConfirmacao($nome)
    {
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>Senha Alterada</title>
            <style>
                body { font-family: Arial, sans-serif; color: #333; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
                .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2 style='color: #28a745;'>✅ Senha Alterada com Sucesso</h2>
                <p>Olá <strong>" . htmlspecialchars($nome) . "</strong>,</p>
                
                <div class='success'>
                    <h3>🎉 Sua senha foi alterada com sucesso!</h3>
                    <p>Agora você já pode fazer login com sua nova senha.</p>
                </div>
                
                <p>Data: <strong>" . date('d/m/Y H:i:s') . "</strong></p>
            </div>
        </body>
        </html>";
    }

    public function testarConfiguracao()
    {
        return $this->enviarEmailRedefinicao(
            'teste@exemplo.com',
            'Usuário Teste',
            'token_teste_' . time()
        );
    }

    public function getStatusSistema()
    {
        $phpmailerDisponivel = false;
        
        try {
            require_once __DIR__ . '/../controllers/PHPMailer.class.php';
            require_once __DIR__ . '/../controllers/SMTP.class.php';
            $phpmailerDisponivel = class_exists('PHPMailer');
        } catch (Exception $e) {
            // Classes não encontradas
        }

        $status = [
            'phpmailer_disponivel' => $phpmailerDisponivel,
            'openssl_habilitado' => extension_loaded('openssl'),
            'curl_habilitado' => extension_loaded('curl'),
            'modo_operacao' => $phpmailerDisponivel ? 'PHPMailer Direto' : 'Simulação',
            'smtp_configurado' => !empty($this->config['smtp_username'])
        ];

        return $status;
    }
}
?>
        }
    }

    public function getStatusSistema()
    {
        $status = [
            'phpmailer_disponivel' => $this->usePHPMailer,
            'openssl_habilitado' => extension_loaded('openssl'),
            'curl_habilitado' => extension_loaded('curl'),
            'modo_operacao' => $this->usePHPMailer ? 'PHPMailer' : 'Simulação'
        ];

        return $status;
    }
}
?>
}
?>
