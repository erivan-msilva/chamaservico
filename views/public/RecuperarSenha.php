<?php
header('Content-Type: application/json');
require_once __DIR__ . '/controller/RecuperarController.php';

$email = $_POST['email'] ?? '';
if (!$email) {
    echo json_encode(['sucesso' => false, 'msg' => 'E-mail não informado']);
    exit;
}

$result = RecuperarController::enviarCodigo($email);
echo json_encode($result);
exit; // Finaliza após enviar resposta
?>
    echo json_encode(['sucesso' => false, 'msg' => 'E-mail não encontrado']);
    exit;
}

// Gera código de recuperação
$codigo = rand(100000, 999999);

// Salva o código em sessão ou banco (exemplo: sessão)
session_start();
$_SESSION['recuperar_email'] = $email;
$_SESSION['recuperar_codigo'] = $codigo;

// Envia e-mail (simples)
$assunto = "Recuperação de senha - Chama Serviço";
$mensagem = "Seu código de recuperação é: $codigo";
$headers = "From: suporte@chamaservico.com\r\n";
$enviado = mail($email, $assunto, $mensagem, $headers);

if ($enviado) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'msg' => 'Falha ao enviar e-mail']);
}
