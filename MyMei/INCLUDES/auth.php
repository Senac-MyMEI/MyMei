<?php
// INCLUDES/auth.php
require_once 'conexao.php';

session_start();

/**
 * Verifica se o usuário está autenticado e tem permissões
 */
function verificarAcesso() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login.php');
        exit;
    }
    
    global $pdo;
    
    try {
        // Busca status da conta e MEI
        $sql = "SELECT 
                    u.status_conta, 
                    m.status_mei,
                    m.id_mei
                FROM usuarios u
                INNER JOIN mei m ON u.id_usuario = m.id_usuario
                WHERE u.id_usuario = :usuario_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
        $dados = $stmt->fetch();
        
        if (!$dados) {
            header('Location: /logout.php');
            exit;
        }
        
        // Verifica se a conta está ativa
        if ($dados['status_conta'] != 'ativa' || $dados['status_mei'] != 'ativo') {
            $_SESSION['erro_acesso'] = "Sua conta não está ativa. Status: " . $dados['status_conta'];
            header('Location: /acesso_restrito.php');
            exit;
        }
        
        // Armazena o id_mei na sessão para uso posterior
        $_SESSION['id_mei'] = $dados['id_mei'];
        
        return true;
        
    } catch (PDOException $e) {
        die("Erro ao verificar acesso: " . $e->getMessage());
    }
}
?>