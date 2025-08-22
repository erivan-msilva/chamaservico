<?php
session_start();

// Se já estiver logado, redirecionar
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/database.php';
    
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT id, nome, email, senha FROM tb_usuario WHERE email = :email AND ativo = 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['admin_id'] = $usuario['id'];
            $_SESSION['admin_nome'] = $usuario['nome'];
            $_SESSION['admin_email'] = $usuario['email'];
            $_SESSION['admin_login_time'] = time();
            
            // Atualizar último acesso
            $updateStmt = $conn->prepare("UPDATE tb_usuario SET ultimo_acesso = NOW() WHERE id = :id");
            $updateStmt->bindParam(':id', $usuario['id']);
            $updateStmt->execute();
            
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = "Email ou senha inválidos";
        }
    } catch (Exception $e) {
        $erro = "Erro no sistema. Tente novamente.";
    }
}

// Verificar parâmetros de URL para mensagens
$mensagem_logout = isset($_GET['logout']) ? 'Logout realizado com sucesso!' : '';
$mensagem_sessao = isset($_GET['sessao_expirada']) ? 'Sua sessão expirou. Faça login novamente.' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
        }
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        .floating-shapes::before,
        .floating-shapes::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        .floating-shapes::before {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        .floating-shapes::after {
            width: 150px;
            height: 150px;
            bottom: 10%;
            right: 10%;
            animation-delay: 3s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
    </style>
</head>
<body>
    <div class="floating-shapes"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                        <h3 class="mt-2">Painel Admin</h3>
                        <p class="mb-0">Chama Serviço</p>
                    </div>
                    
                    <div class="p-4">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $erro; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mensagem_logout): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?php echo $mensagem_logout; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mensagem_sessao): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-clock"></i> <?php echo $mensagem_sessao; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <label for="email"><i class="bi bi-envelope"></i> Email</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Password" required>
                                <label for="senha"><i class="bi bi-lock"></i> Senha</label>
                            </div>
                            
                            <div class="d-grid">
                                <button class="btn btn-primary btn-login" type="submit" id="btnLogin">
                                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Acesso restrito a administradores
                            </small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <strong>Credenciais de teste:</strong><br>
                                <code>admin@chamaservico.com</code><br>
                                <code>123456</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Melhorar UX do formulário de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            btnLogin.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Entrando...';
            btnLogin.disabled = true;
            
            // Se houver erro, reativar o botão após 2 segundos
            setTimeout(() => {
                if (btnLogin.disabled) {
                    btnLogin.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Entrar';
                    btnLogin.disabled = false;
                }
            }, 2000);
        });

        // Autocompletar credenciais de teste em ambiente de desenvolvimento
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se está em ambiente local
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                const emailField = document.getElementById('email');
                const senhaField = document.getElementById('senha');
                
                // Adicionar evento duplo clique para preencher automaticamente
                emailField.addEventListener('dblclick', function() {
                    emailField.value = 'admin@chamaservico.com';
                    senhaField.value = '123456';
                    senhaField.focus();
                });
            }
        });

        // Auto-remover alertas após 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
    
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>
</body>
</html>
