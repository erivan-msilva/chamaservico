<?php
require_once __DIR__ . '/../controllers/PHPMailer.class.php';
require_once __DIR__ . '/../controllers/SMTP.class.php';

class Email {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer();

        // Configurações do servidor SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = 'h63.servidorhh.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'chamaservico@tds104-senac.online';
        $this->mailer->Password = 'Chama@Servico123'; // Substitua pela senha correta
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Configurações do remetente
        $this->mailer->setFrom('chamaservico@tds104-senac.online', 'ChamaServiço');
        $this->mailer->CharSet = 'UTF-8'; // Definir codificação de caracteres
    }

    public function enviarEmail($destinatario, $assunto, $corpo) {
        try {
            $this->mailer->addAddress($destinatario);
            $this->mailer->isHTML(true); // Enviar como HTML
            $this->mailer->Subject = $assunto;
            $this->mailer->Body = $corpo;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
            return false;
        }
    }
}
?>

