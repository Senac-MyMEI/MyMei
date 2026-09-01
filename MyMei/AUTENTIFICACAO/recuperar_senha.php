<?php
// ============================================
// RECUPERAR SENHA - INTERFACE
// ============================================
session_start();
// Se já estiver logado, redireciona
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - MyMei</title>
    <link rel="stylesheet" href="../ASSETS/bootstrap.css">
    <style>
        body {
            background: linear-gradient(135deg, #2fa4e7 0%, #033c73 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .recover-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: none;
        }
        .btn-recover {
            background: #2fa4e7;
            color: white;
            font-weight: bold;
        }
        .btn-recover:hover {
            background: #2683b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card recover-card">
                    <div class="card-header text-center bg-transparent border-0 pt-4">
                        <h3>🔑 Recuperar Senha</h3>
                        <p class="text-muted">Digite seu e-mail para receber o link de redefinição</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Mensagem de sucesso -->
                        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'enviado'): ?>
                            <div class="alert alert-success">
                                ✅ Um link de recuperação foi enviado para seu e-mail!
                            </div>
                        <?php endif; ?>

                        <!-- Mensagem de erro -->
                        <?php if (isset($_GET['erro'])): ?>
                            <div class="alert alert-danger">
                                ❌ E-mail não encontrado. Verifique e tente novamente.
                            </div>
                        <?php endif; ?>

                        <form action="enviar_link_recuperacao.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail cadastrado</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="seu@email.com" required>
                            </div>
                            
                            <button type="submit" class="btn btn-recover w-100 btn-lg">
                                📩 Enviar Link
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