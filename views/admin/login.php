<?php
// Remover ou comentar esta linha:
// session_start();

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
        }
        
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
            overflow: hidden;
            min-height: 600px;
        }
        
        .login-image-section {
            background: #7a5deb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            position: relative;
        }
        
        .login-image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .login-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }
        
        .login-form-section {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header .admin-icon {
            width: 80px;
            height: 80px;
            background: #7a5deb;
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
        
        .form-floating > label {
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
            background: #7a5deb;
            border: none;
            border-radius: 12px;
            padding: 0.9rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background: #6b4fd9;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(122, 93, 235, 0.4);
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
        
        .credentials-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border-left: 4px solid #7a5deb;
        }
        
        .credentials-box h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .credential-item {
            background: white;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-family: 'Courier New', monospace;
            border: 1px solid #e9ecef;
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
            background: rgba(255, 255, 255, 0.08);
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
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .login-image-section {
                min-height: 300px;
                padding: 2rem 1.5rem;
            }
            
            .login-form-section {
                padding: 2rem 1.5rem;
            }
            
            .login-header h3 {
                font-size: 1.5rem;
            }
            
            .credentials-box {
                margin-top: 1rem;
                padding: 1rem;
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <div class="floating-shapes"></div>
    
    <div class="container-fluid login-container">
        <div class="card login-card">
            <div class="row g-0 h-100">
                <!-- Seção da Imagem -->
                <div class="col-lg-6">
                    <div class="login-image-section h-100">
                        <img src="/chamaservico/assets/img/admin-login.png">
                        
                        <!-- Fallback caso a imagem não carregue -->
                        <div style="display: none; flex-direction: column; align-items: center; text-align: center; color: white; position: relative; z-index: 2;">
                            <div style="width: 120px; height: 120px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem;">
                                <i class="bi bi-shield-lock-fill" style="font-size: 3rem;"></i>
                            </div>
                            <h4 style="font-weight: 600; margin-bottom: 1rem;">Área Administrativa</h4>
                            <p style="font-size: 1.1rem; opacity: 0.9; max-width: 300px;">
                                Acesso seguro ao painel de controle do sistema ChamaServiço
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Seção do Formulário -->
                <div class="col-lg-6">
                    <div class="login-form-section h-100">
                        <div class="login-header">
                            <div class="admin-icon">
                                <i class="bi bi-shield-lock-fill"></i>
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
                        <form method="POST" id="loginForm">
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
                            
                            <div class="d-grid">
                                <button class="btn btn-primary btn-login" type="submit" id="btnLogin">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Acessar Painel
                                </button>
                            </div>
                        </form>
                        
                        <!-- Informações de Acesso -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i> 
                                Acesso restrito a administradores autorizados
                            </small>
                        </div>
                        
                        <!-- Credenciais de Desenvolvimento -->
                        <div class="credentials-box">
                            <h6>
                                <i class="bi bi-code-slash me-2"></i>
                                Credenciais de Desenvolvimento
                            </h6>
                            <div class="credential-item">
                                <strong>Email:</strong> admin@chamaservico.com
                            </div>
                            <div class="credential-item">
                                <strong>Senha:</strong> 123456
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-shield-exclamation me-1"></i>
                                Altere essas credenciais em produção
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

        // Autocompletar credenciais de teste em ambiente de desenvolvimento
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se está em ambiente local
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                const emailField = document.getElementById('email');
                const senhaField = document.getElementById('senha');
                
                // Duplo clique no email para preencher automaticamente
                emailField.addEventListener('dblclick', function() {
                    emailField.value = 'admin@chamaservico.com';
                    senhaField.value = '123456';
                    senhaField.focus();
                });
                
                // Duplo clique na caixa de credenciais para preencher
                document.querySelector('.credentials-box').addEventListener('dblclick', function() {
                    emailField.value = 'admin@chamaservico.com';
                    senhaField.value = '123456';
                    senhaField.focus();
                });
            }
        });

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
