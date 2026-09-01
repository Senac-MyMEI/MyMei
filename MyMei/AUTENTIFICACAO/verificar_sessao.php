<?php
// ============================================
// VERIFICAÇÃO DE SESSÃO (INCLUDE EM PÁGINAS PROTEGIDAS)
// ============================================
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redireciona para o login
    header("Location: ../AUTENTIFICACAO/login_acao.php?erro=sessao_expirada");
    exit();
}

// Opcional: Verificar se a conta ainda está ativa no banco
require_once("../config/database.php");

try {
    $sql = "SELECT status_conta FROM usuarios WHERE id_usuario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario || $usuario['status_conta'] !== 'ativa') {
        session_destroy();
        header("Location: ../AUTENTIFICACAO/login_acao.php?erro=inativo");
        exit();
    }
} catch (PDOException $e) {
    // Se der erro, redireciona para login
    session_destroy();
    header("Location: ../AUTENTIFICACAO/login_acao.php?erro=banco");
    exit();
}

// Se passou por todas as verificações, o usuário está autenticado
// Pode usar em qualquer página protegida assim:
// require_once("AUTENTIFICACAO/verificar_sessao.php");
?>