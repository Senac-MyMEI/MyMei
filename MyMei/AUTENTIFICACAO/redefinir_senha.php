<?php
// ============================================
// REDEFINIR SENHA - INTERFACE
// ============================================
session_start();

// Verifica se o token foi passado na URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Se não tiver token, redireciona
if (empty($token)) {
    header("Location: recuperar_senha.php?erro=token_invalido");
    exit();
}

// Verifica se o token é válido no banco
require_once("../config/database.php");

try {
    $sql = "SELECT id_usuario, email_usuario FROM usuarios 
            WHERE token_recuperacao = :token 
            AND token_expiracao > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header("Location: recuperar_senha.php?erro=token_invalido");
        exit();
    }
} catch (PDOException $e) {
    header("Location: recuperar_senha.php?erro=banco");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - MyMei</title>
    <link rel="stylesheet" href="../ASSETS/bootstrap.css">
    <style>
        body {
            background: linear-gradient(135deg, #2fa4e7 0%, #033c73 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: none;
        }
        .btn-reset {
            background: #73a839;
            color: white;
            font-weight: bold;
        }
        .btn-reset:hover {
            background: #5c862e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card reset-card">
                    <div class="card-header text-center bg-transparent border-0 pt-4">
                        <h3>🔄 Redefinir Senha</h3>
                        <p class="text-muted">Crie uma nova senha para sua conta</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Mensagem de erro -->
                        <?php if (isset($_GET['erro'])): ?>
                            <div class="alert alert-danger">
                                ❌ As senhas não coincidem ou são muito curtas.
                            </div>
                        <?php endif; ?>

                        <form action="salvar_nova_senha.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="user_id" value="<?php echo $usuario['id_usuario']; ?>">
                            
                            <div class="mb-3">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                       placeholder="Mínimo 6 caracteres" required minlength="6">
                            </div>

                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                       placeholder="Digite a senha novamente" required>
                            </div>

                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <small>🔒 A senha deve ter no mínimo 6 caracteres.</small>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-reset w-100 btn-lg">
                                💾 Salvar Nova Senha
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="login_acao.php" class="text-decoration-none">
                                ← Voltar para o login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>