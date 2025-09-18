<?php
require_once __DIR__ . '/config.php';

// Ensure Composer autoloader is included for PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class EmailService
{
    private $config;
    private $usePHPMailer;

    public function __construct()
    {
        // Aguardar que o config seja carregado primeiro
        if (!defined('EMAIL_SMTP_HOST')) {
            require_once __DIR__ . '/../config/config.php';
        }

        $this->config = [
            'smtp_host' => EMAIL_SMTP_HOST,
            'smtp_port' => EMAIL_SMTP_PORT,
            'smtp_username' => EMAIL_SMTP_USERNAME,
            'smtp_password' => EMAIL_SMTP_PASSWORD,
            'from_email' => EMAIL_FROM_EMAIL,
            'from_name' => EMAIL_FROM_NAME
        ];

        // Log das configurações para debug
        error_log("📧 EmailService configurado - Host: {$this->config['smtp_host']} - User: {$this->config['smtp_username']} - Ambiente: " . AMBIENTE);

        $this->usePHPMailer = $this->verificarPHPMailer();
        
        // Log do status do PHPMailer
        error_log("📧 PHPMailer disponível: " . ($this->usePHPMailer ? 'SIM' : 'NÃO'));
    }

    private function verificarPHPMailer()
    {
        try {
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
                return class_exists('PHPMailer\PHPMailer\PHPMailer');
            }

            if (file_exists(url('controllers/PHPMailer.class.php')) && 
                file_exists(url('controllers/SMTP.class.php'))) {
                
                require_once url('controllers/PHPMailer.class.php');
                require_once url('controllers/SMTP.class.php');
                return class_exists('PHPMailer');
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar PHPMailer: " . $e->getMessage());
        }

        return false;
    }

    public function enviarEmailRedefinicao($email, $nome, $token)
    {
        $assunto = '🔒 Redefinição de Senha - ChamaServiço';
        $linkRedefinicao = BASE_URL . "/redefinir-senha-nova?token=" . $token;
        $corpo = $this->gerarTemplateRedefinicao($nome, $linkRedefinicao);

        if ($this->usePHPMailer && AMBIENTE === 'producao') {
            return $this->enviarComPHPMailer($email, $assunto, $corpo);
        } else {
            return $this->simularEnvio($email, $assunto, $linkRedefinicao);
        }
    }

    public function enviarEmailConfirmacaoRedefinicao($email, $nome)
    {
        $assunto = '✅ Senha Alterada com Sucesso - ChamaServiço';
        $corpo = $this->gerarTemplateConfirmacao($nome);

        if ($this->usePHPMailer && AMBIENTE === 'producao') {
            return $this->enviarComPHPMailer($email, $assunto, $corpo);
        } else {
            return $this->simularEnvio($email, $assunto, "Confirmação para: $nome");
        }
    }

    private function enviarComPHPMailer($email, $assunto, $corpo)
    {
        try {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            } else {
                $mail = new PHPMailer(true);
            }

            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Em produção, não mostrar debug
            if (AMBIENTE === 'desenvolvimento') {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'error_log';
            } else {
                $mail->SMTPDebug = 0; // Sem debug em produção
            }

            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpo;

            $result = $mail->send();
            
            if ($result) {
                error_log("✅ Email enviado com sucesso para: $email");
            }
            
            return $result;

        } catch (Exception $e) {
            // Log mais detalhado para produção
            error_log("❌ Erro PHPMailer para $email: " . $e->getMessage());
            
            // Em produção, não simular envio em caso de erro
            if (AMBIENTE === 'desenvolvimento') {
                return $this->simularEnvio($email, $assunto, "Erro: " . $e->getMessage());
            }
            
            return false;
        }
    }

    private function simularEnvio($email, $assunto, $conteudo)
    {
        try {
            $logDir = url('logs');
            if (!is_dir($logDir)) {
                if (!mkdir($logDir, 0755, true)) {
                    error_log("Erro ao criar diretório de logs: $logDir");
                    return false;
                }
            }

            $logFile = $logDir . DIRECTORY_SEPARATOR . 'emails_simulados.log';
            $timestamp = date('Y-m-d H:i:s');
            $ambiente = AMBIENTE;
            
            $logEntry = "[$timestamp] [$ambiente] EMAIL SIMULADO\n";
            $logEntry .= "Para: $email\n";
            $logEntry .= "Assunto: $assunto\n";
            $logEntry .= "Conteúdo: $conteudo\n";
            $logEntry .= "Base URL: " . BASE_URL . "\n";
            $logEntry .= str_repeat("-", 80) . "\n\n";

            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
            error_log("📧 Email simulado para: $email (arquivo: $logFile)");
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao simular envio de email: " . $e->getMessage());
            return false;
        }
    }

    private function gerarTemplateRedefinicao($nome, $link)
    {
        $siteUrl = BASE_URL;
        
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
                    <p>© 2024 ChamaServiço - <a href='" . $siteUrl . "'>Visite nosso site</a></p>
                    <p>Este email foi enviado automaticamente, não responda.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function gerarTemplateConfirmacao($nome)
    {
        $siteUrl = BASE_URL;
        
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>Senha Alterada</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { color: #283579; font-size: 24px; font-weight: bold; }
                .logo-accent { color: #f5a522; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>CHAMA<span class='logo-accent'>SERVIÇO</span></div>
                    <h2 style='color: #28a745;'>✅ Senha Alterada com Sucesso</h2>
                </div>
                
                <p>Olá <strong>" . htmlspecialchars($nome) . "</strong>,</p>
                
                <div class='success'>
                    <strong>✅ Sua senha foi alterada com sucesso!</strong><br>
                    A alteração foi realizada em: " . date('d/m/Y \à\s H:i') . "
                </div>
                
                <p>Se você não fez esta alteração, entre em contato conosco imediatamente.</p>
                
                <div class='footer'>
                    <p>© 2024 ChamaServiço - <a href='" . $siteUrl . "'>Visite nosso site</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function getStatusSistema()
    {
        return [
            'phpmailer_disponivel' => $this->usePHPMailer,
            'openssl_habilitado' => extension_loaded('openssl'),
            'curl_habilitado' => extension_loaded('curl'),
            'modo_operacao' => $this->usePHPMailer ? 'PHPMailer' : 'Simulação',
            'smtp_configurado' => !empty($this->config['smtp_username']),
            'ambiente' => AMBIENTE,
            'base_url' => BASE_URL
        ];
    }
}
?>
