<?php
// ============================================
// PROCESSAMENTO DO LOGIN
// ============================================
session_start();
require_once("../config/database.php");

// ============================================
// VERIFICA SE OS CAMPOS FORAM ENVIADOS
// ============================================
if (empty($_POST['email']) || empty($_POST['senha'])) {
    header("Location: fazer_login.php?erro=campos");
    exit();
}

// ============================================
// LIMPA OS DADOS RECEBIDOS
// ============================================
$email = trim($_POST['email']);
$senha = $_POST['senha'];

try {
    // ============================================
    // BUSCA O USUÁRIO PELO E-MAIL
    // ============================================
    $sql = "SELECT id_usuario, nome_usuario, email_usuario, senha_hash, status_conta 
            FROM usuarios 
            WHERE email_usuario = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // ============================================
    // VERIFICA SE O USUÁRIO EXISTE
    // ============================================
    if (!$usuario) {
        header("Location: fazer_login.php?erro=credenciais");
        exit();
    }

    // ============================================
    // VERIFICA SE A CONTA ESTÁ ATIVA
    // ============================================
    if ($usuario['status_conta'] !== 'ativa') {
        header("Location: fazer_login.php?erro=inativo");
        exit();
    }

    // ============================================
    // VERIFICA A SENHA
    // ============================================
    if (password_verify($senha, $usuario['senha_hash'])) {
        // ✅ SUCESSO! CRIA A SESSÃO
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['user_name'] = $usuario['nome_usuario'];
        $_SESSION['user_email'] = $usuario['email_usuario'];
        
        // ============================================
        // SE "LEMBRAR-ME" ESTIVER MARCADO, CRIA COOKIE
        // ============================================
        if (isset($_POST['lembrar'])) {
            setcookie('user_email', $email, time() + (86400 * 30), "/"); // 30 dias
        } else {
            // Se não marcou, remove o cookie se existir
            if (isset($_COOKIE['user_email'])) {
                setcookie('user_email', '', time() - 3600, '/');
            }
        }

        // ============================================
        // REDIRECIONA PARA O DASHBOARD
        // ============================================
        header("Location: ../dashboard.php");
        exit();
    } else {
        // ❌ SENHA INCORRETA
        header("Location: fazer_login.php?erro=credenciais");
        exit();
    }

} catch (PDOException $e) {
    // ============================================
    // ERRO NO BANCO DE DADOS
    // ============================================
    error_log("Erro no login: " . $e->getMessage());
    header("Location: fazer_login.php?erro=banco");
    exit();
}
?>