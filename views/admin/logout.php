<?php
// Destruir sessão admin
session_start();
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nome']);
unset($_SESSION['admin_nivel']);
unset($_SESSION['is_admin']);

// Regenerar ID da sessão por segurança
session_regenerate_id(true);

// Redirecionar para login admin
header('Location: /chamaservico/admin/login');
exit;
?>
// Destruir a sessão
session_destroy();

// Redirecionar para a página de login com mensagem de logout
header('Location: login.php?logout=1');
exit;
?>
