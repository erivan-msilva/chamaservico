<?php
// recebe $erro do LoginController::index
$erro = $erro ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login - Chama Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1a2233 0%, #4f6fa5 60%, #ffb347 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-anhanguera-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(26, 34, 51, 0.18);
            max-width: 410px;
            width: 100%;
            padding: 2.5rem 2rem 2rem 2rem;
            margin: 32px 0;
            position: relative;
        }

        .login-anhanguera-card .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .login-anhanguera-card .logo img {
            max-width: 120px;
            height: auto;
        }

        .login-anhanguera-card h2 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a2233;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .login-anhanguera-card .subtitle {
            color: #4f6fa5;
            text-align: center;
            font-size: 1.08rem;
            margin-bottom: 2rem;
        }

        .login-anhanguera-card .form-label {
            font-weight: 600;
            color: #1a2233;
            margin-bottom: 0.3rem;
        }

        .login-anhanguera-card .form-control {
            border-radius: 12px;
            border: 1.5px solid #eaf3ff;
            font-size: 1.08rem;
            background: #f8fafc;
            color: #1a2233;
            padding: 12px 16px;
            margin-bottom: 1.2rem;
            transition: border 0.2s, box-shadow 0.2s;
        }

        .login-anhanguera-card .form-control:focus {
            border-color: #ffb347;
            box-shadow: 0 0 0 0.2rem rgba(255, 179, 71, .12);
            background: #fff;
        }

        .login-anhanguera-card .input-group-text {
            background: #eaf3ff;
            border: none;
            color: #4f6fa5;
            font-size: 1.15rem;
        }

        .login-anhanguera-card .btn-login {
            background: linear-gradient(90deg, #ffb347 0%, #4f6fa5 100%);
            border: none;
            border-radius: 22px;
            padding: 12px 22px;
            font-weight: 700;
            color: #1a2233;
            font-size: 1.08rem;
            letter-spacing: 0.7px;
            box-shadow: 0 4px 18px 0 rgba(26, 34, 51, 0.10), 0 1.5px 6px 0 rgba(79, 111, 165, 0.08);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.18s cubic-bezier(.4, 2, .6, 1);
            width: 100%;
            margin-bottom: 0.7rem;
        }

        .login-anhanguera-card .btn-login:hover,
        .login-anhanguera-card .btn-login:focus {
            background: linear-gradient(90deg, #4f6fa5 0%, #ffb347 100%);
            color: #1a2233;
            transform: translateY(-2px) scale(1.045);
        }

        .login-anhanguera-card .btn-outline-secondary {
            background: #fff;
            color: #4f6fa5;
            border: 2px solid #ffb347;
            border-radius: 22px;
            padding: 12px 22px;
            font-weight: 700;
            width: 100%;
            margin-bottom: 0.7rem;
            transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s, transform 0.18s cubic-bezier(.4, 2, .6, 1);
        }

        .login-anhanguera-card .btn-outline-secondary:hover,
        .login-anhanguera-card .btn-outline-secondary:focus {
            background: #ffb347;
            color: #1a2233;
            border-color: #4f6fa5;
            transform: translateY(-2px) scale(1.045);
        }

        .login-anhanguera-card .forgot-link {
            display: block;
            text-align: right;
            font-size: 0.98rem;
            color: #4f6fa5;
            text-decoration: none;
            margin-bottom: 1.2rem;
            font-weight: 500;
        }

        .login-anhanguera-card .forgot-link:hover {
            text-decoration: underline;
            color: #ffb347;
        }

        .login-anhanguera-card .alert-danger {
            background: #fff3f3;
            color: #e74c3c;
            border: 1.5px solid #f8d7da;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 1rem;
            text-align: center;
        }

        .login-anhanguera-card .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1.5px solid #c3e6cb;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 1rem;
            text-align: center;
        }

        .login-anhanguera-card .divider {
            text-align: center;
            margin: 1.5rem 0 1.2rem 0;
            color: #b0b0b0;
            font-size: 0.98rem;
            position: relative;
        }

        .login-anhanguera-card .divider:before,
        .login-anhanguera-card .divider:after {
            content: '';
            display: inline-block;
            width: 40%;
            height: 1px;
            background: #eaf3ff;
            vertical-align: middle;
            margin: 0 8px;
        }

        .login-anhanguera-card .register-link {
            display: block;
            text-align: center;
            font-size: 1.01rem;
            color: #4f6fa5;
            text-decoration: none;
            font-weight: 600;
            margin-top: 0.7rem;
        }

        .login-anhanguera-card .register-link:hover {
            color: #ffb347;
            text-decoration: underline;
        }

        @media (max-width: 575.98px) {
            .login-anhanguera-card {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem;
                max-width: 98vw;
            }
        }
    </style>
</head>

<body>
    <div class="login-anhanguera-card">
        <div class="logo">
            <img src="assets/img/logochamaser.png" alt="Chama Serviço" />
        </div>
        <h2>Bem-vindo!</h2>
        <div class="subtitle">Acesse sua conta para usar o sistema</div>

        <form action="/servico/index.php?url=login/autenticar" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">
                    <span class="input-group-text" style="background:transparent;border:none;padding-left:0;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    E-mail
                </label>
                <input type="email" class="form-control" id="email" name="email" value=""
                    placeholder="Digite seu e-mail" required autofocus>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">
                    <span class="input-group-text" style="background:transparent;border:none;padding-left:0;">
                        <i class="bi bi-lock"></i>
                    </span>
                    Senha
                </label>
                <div class="input-group" style="position:relative;">
                    <input type="password" class="form-control" id="senha" name="senha"
                        placeholder="Digite sua senha" required
                        style="border-radius: 12px; padding-right: 38px;">
                    <button class="btn btn-link p-0" type="button" id="toggleSenha" tabindex="-1"
                        style="position:absolute; right:16px; top:0; bottom:0; height:100%; display:flex; align-items:center; z-index:2; color:#4f6fa5; font-size:1.1rem; background:transparent; border:none;">
                        <i class="bi bi-eye" id="iconSenha"></i>
                    </button>
                </div>
            </div>
            <a href="/servico/index.php?url=recuperar-senha" class="forgot-link">
                <i class="bi bi-key me-1"></i> Esqueceu a senha?
            </a>
            <button type="submit" class="btn btn-login">Entrar</button>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            <div class="divider">ou</div>
            <a href="/servico/index.php?url=cadusuario" class="btn btn-outline-secondary">
                <i class="bi bi-person-plus"></i> Criar uma conta
            </a>
        </form>
        <a href="/servico/index.php?url=home" class="register-link">
            <i class="bi bi-arrow-left-circle me-1"></i> Voltar para a Home
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Visualizador de senha
        document.addEventListener('DOMContentLoaded', function() {
            const senhaInput = document.getElementById('senha');
            const toggleSenha = document.getElementById('toggleSenha');
            const iconSenha = document.getElementById('iconSenha');
            if (toggleSenha) {
                toggleSenha.addEventListener('click', function() {
                    if (senhaInput.type === 'password') {
                        senhaInput.type = 'text';
                        iconSenha.classList.remove('bi-eye');
                        iconSenha.classList.add('bi-eye-slash');
                    } else {
                        senhaInput.type = 'password';
                        iconSenha.classList.remove('bi-eye-slash');
                        iconSenha.classList.add('bi-eye');
                    }
                });
            }
        });
    </script>
</body>

</html>