<?php
// ============================================
// PÁGINA DE LOGIN - INTERFACE VISUAL
// ============================================
session_start();

// Se já estiver logado, redireciona para o dashboard
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
    <title>Login - MyMei</title>
    <!-- Seu Bootstrap do Cerulean -->
    <link rel="stylesheet" href="../ASSETS/bootstrap.css">
    <style>
        body {
            background: linear-gradient(135deg, #2fa4e7 0%, #033c73 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: none;
        }
        .login-card .card-header {
            background: transparent;
            border-bottom: none;
            text-align: center;
            padding-top: 2rem;
        }
        .login-card .card-header h3 {
            color: #2fa4e7;
            font-weight: bold;
        }
        .login-card .card-body {
            padding: 2rem;
        }
        .btn-login {
            background: #2fa4e7;
            color: white;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #2683b9;
            transform: scale(1.02);
        }
        .logo-icon {
            font-size: 4rem;
            color: #2fa4e7;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <!-- CARD DE LOGIN -->
                <div class="card login-card">
                    <div class="card-header">
                        <div class="logo-icon">🏢</div>
                        <h3>MyMei</h3>
                        <p class="text-muted">Sistema de Gestão para MEI</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- MENSAGEM DE ERRO (se houver) -->
                        <?php if (isset($_GET['erro'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>❌ Erro!</strong> 
                                <?php 
                                    switch($_GET['erro']) {
                                        case 'credenciais':
                                            echo "E-mail ou senha incorretos!";
                                            break;
                                        case 'campos':
                                            echo "Preencha todos os campos!";
                                            break;
                                        case 'inativo':
                                            echo "Sua conta está inativa. Entre em contato com o suporte.";
                                            break;
                                        case 'sessao_expirada':
                                            echo "Sua sessão expirou. Faça login novamente.";
                                            break;
                                        default:
                                            echo "Erro ao fazer login. Tente novamente.";
                                    }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- MENSAGEM DE SUCESSO -->
                        <?php if (isset($_GET['msg'])): ?>
                            <?php if ($_GET['msg'] == 'logout'): ?>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <strong>👋 Até logo!</strong> Você saiu do sistema com sucesso.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['msg'] == 'senha_alterada'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>✅ Sucesso!</strong> Senha alterada com sucesso! Faça login.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- FORMULÁRIO DE LOGIN -->
                        <!-- O action envia para o login_acao.php (processamento) -->
                        <form action="login_acao.php" method="POST">
                            <!-- Campo E-mail -->
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text">📧</span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="seu@email.com" required autofocus
                                           value="<?php echo isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : ''; ?>">
                                </div>
                            </div>

                            <!-- Campo Senha -->
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text">🔒</span>
                                    <input type="password" class="form-control" id="senha" name="senha" 
                                           placeholder="Digite sua senha" required>
                                </div>
                            </div>

                            <!-- Lembrar-me e Esqueci a senha -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar"
                                           <?php echo isset($_COOKIE['user_email']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="lembrar">Lembrar-me</label>
                                </div>
                                <a href="recuperar_senha.php" class="text-decoration-none">
                                    Esqueceu a senha?
                                </a>
                            </div>

                            <!-- Botão Entrar -->
                            <button type="submit" class="btn btn-login w-100 btn-lg">
                                🔐 Entrar
                            </button>

                            <!-- Link para Cadastro -->
                            <div class="text-center mt-3">
                                <p class="text-muted">
                                    Não tem uma conta? 
                                    <a href="../CRUD_MYMEI/cadastrar_usuario.php" class="text-decoration-none fw-bold">
                                        Cadastre-se
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Rodapé do Login -->
                <div class="text-center mt-3 text-white">
                    <small>MyMei &copy; 2026 - Todos os direitos reservados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>