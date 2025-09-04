<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Limpar variáveis de sessão relacionadas ao admin
unset($_SESSION['admin_id'], $_SESSION['admin_nome'], $_SESSION['admin_nivel'], $_SESSION['is_admin']);

// Regenerar id por segurança e destruir sessão
session_regenerate_id(true);
session_destroy();

// Redirecionar para a página de login com indicação de logout
header('Location: admin/login?logout=1');
exit;

session_destroy();

// Redirecionar para a página de login com mensagem de logout
header('Location: login.php?logout=1');
exit;
?>
