<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redirecionar
if (isset($_SESSION['admin_id'])) {
    header('Location: /chamaservico/admin/dashboard');
    exit;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .login-container {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            min-height: 700px;
            border: none;
        }

        .login-image-section {
            background: #7a5deb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            min-height: 700px;
        }

        .login-image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        }

        .admin-illustration {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            width: 100%;
            max-width: 400px;
        }

        .admin-illustration .icon-container {
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .admin-illustration .icon-container i {
            font-size: 4rem;
            color: white;
        }

        .admin-illustration h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .admin-illustration p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .features-list li i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }

        .login-form-section {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            min-height: 700px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header .admin-icon {
            width: 80px;
            height: 80px;
            background: slateblue;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 20px rgba(122, 93, 235, 0.3);
        }

        .login-header h3 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .login-header p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating>label {
            color: #6c757d;
            font-weight: 500;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            height: auto;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #7a5deb;
            box-shadow: 0 0 0 0.25rem rgba(122, 93, 235, 0.25);
            transform: translateY(-2px);
        }

        .btn-login {
            background: #785eef;
            border: none;
            border-radius: 12px;
            padding: 0.9rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-login:hover {
            background: #785eef;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px #785eef;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }

        .alert-warning {
            background: linear-gradient(135deg, #ffd93d 0%, #ffcc02 100%);
            color: #333;
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
            background: rgba(255, 255, 255, 0.05);
            animation: float 8s ease-in-out infinite;
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
            bottom: 15%;
            right: 15%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        /* Responsividade */
        @media (max-width: 991px) {
            .login-card {
                min-height: auto;
            }

            .login-image-section {
                min-height: 300px;
                padding: 2rem 1.5rem;
            }

            .admin-illustration h3 {
                font-size: 1.8rem;
            }

            .admin-illustration p {
                font-size: 1rem;
            }

            .admin-illustration .icon-container {
                width: 100px;
                height: 100px;
            }

            .admin-illustration .icon-container i {
                font-size: 3rem;
            }

            .features-list {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .login-form-section {
                padding: 2rem 1.5rem;
                min-height: auto;
            }

            .login-header h3 {
                font-size: 1.5rem;
            }

            .login-container {
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .login-image-section {
                min-height: 250px;
                padding: 1.5rem 1rem;
            }

            .admin-illustration .icon-container {
                width: 80px;
                height: 80px;
            }

            .admin-illustration .icon-container i {
                font-size: 2.5rem;
            }

            .admin-illustration h3 {
                font-size: 1.5rem;
            }
        }

        .login-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            box-shadow: 0 5px 15px #7a5deb;
            position: relative;
            z-index: 2;
            opacity: 0.9;
            border: 3px solid #7a5deb;
        }
    </style>
</head>

<body>
    <div class="floating-shapes"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="row g-0 h-100">
                <!-- Seção da Imagem -->
                <div class="col-lg-6">
                    <div class="login-image-section">
                        <div class="admin-illustration">
                            <img src="/chamaservico/assets/img/admin-login.png"
                                alt="Admin Login"
                                class="login-image"
                                style="max-width: 100%; height: auto; max-height: 400px; border-radius: 15px; box-shadow: 0 0 30px rgba(122, 93, 235, 0.4); position: relative; z-index: 2; opacity: 0.85; border: none; filter: drop-shadow(0 0 20px rgba(122, 93, 235, 0.6)) saturate(1.1); mix-blend-mode: soft-light;">

                            <!-- Fallback caso a imagem não carregue -->
                            <div style="display: none;" id="fallbackContent">
                                <div class="icon-container">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </div>
                                <h3>Área Administrativa</h3>
                                <p>Acesso seguro ao painel de controle do sistema ChamaServiço</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção do Formulário -->
                <div class="col-lg-6">
                    <div class="login-form-section">
                        <div class="login-header">
                            <div class="admin-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <h3>Painel Admin</h3>
                            <p>ChamaServiço</p>
                        </div>

                        <!-- Mensagens de Feedback -->
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($erro); ?>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($mensagem_logout): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo htmlspecialchars($mensagem_logout); ?>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($mensagem_sessao): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-clock me-2"></i>
                                <?php echo htmlspecialchars($mensagem_sessao); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário de Login -->
                        <form method="POST" action="/chamaservico/admin/login" id="loginForm">
                            <div class="form-floating">
                                <input type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="name@example.com"
                                    required
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <label for="email">
                                    <i class="bi bi-envelope me-2"></i>Email do Administrador
                                </label>
                            </div>

                            <div class="form-floating">
                                <input type="password"
                                    class="form-control"
                                    id="senha"
                                    name="senha"
                                    placeholder="Password"
                                    required>
                                <label for="senha">
                                    <i class="bi bi-lock me-2"></i>Senha de Acesso
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button class="btn btn-primary btn-login" type="submit" id="btnLogin">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Acessar Painel
                                </button>
                            </div>
                        </form>

                        <!-- Informações de Acesso -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Acesso restrito a administradores autorizados
                            </small>
                        </div>

                        <div class="text-center mt-3">
                            <a href="/chamaservico/" class="text-decoration-none" style="color: #2c3e50;">
                                <i class="bi bi-arrow-left me-1"></i>Voltar ao Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Verificar se a imagem carregou corretamente
        document.querySelector('.login-image').addEventListener('error', function() {
            this.style.display = 'none';
            document.getElementById('fallbackContent').style.display = 'block';
        });

        // Melhorar UX do formulário de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            const originalText = btnLogin.innerHTML;

            btnLogin.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>Entrando...';
            btnLogin.disabled = true;

            // Se houver erro, reativar o botão após 3 segundos
            setTimeout(() => {
                if (btnLogin.disabled) {
                    btnLogin.innerHTML = originalText;
                    btnLogin.disabled = false;
                }
            }, 3000);
        });

        // Auto-focus no campo email
        document.getElementById('email').focus();

        // Auto-remover alertas após 6 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 6000);

        // Animação suave nos campos de input
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>

</html>