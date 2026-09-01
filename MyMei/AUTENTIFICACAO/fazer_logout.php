<?php
// ============================================
// LOGOUT - DESTROI A SESSÃO
// ============================================
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destroi a sessão
session_destroy();

// Remove o cookie de lembrar-me (se existir)
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, '/');
}

// Redireciona para a página de login
header("Location: login_acao.php?msg=logout");
exit();
?>