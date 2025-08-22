<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha | Chama Serviço</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #27ae60;
        }
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-recuperar {
            max-width: 400px;
            margin: 40px auto;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.12);
            border: none;
        }
        .card-recuperar .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            border-radius: 18px 18px 0 0;
            text-align: center;
        }
        .btn-recuperar {
            background: var(--secondary-color);
            color: #fff;
            font-weight: 600;
            border-radius: 25px;
            transition: background 0.2s;
        }
        .btn-recuperar:hover, .btn-recuperar:focus {
            background: var(--primary-color);
            color: #fff;
        }
        .logo-login {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }
        .text-muted {
            font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card card-recuperar shadow">
            <div class="card-header">
                <img src="assets/img/logochamaser.png" alt="Logo" class="logo-login">
                <h4 class="mb-1"><i class="fas fa-key me-2"></i>Recuperar Senha</h4>
                <p class="mb-0 text-white-50">Informe seu e-mail cadastrado para receber o código de recuperação.</p>
            </div>
            <div class="card-body">
                <form id="formRecuperar" method="post" autocomplete="off">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Seu e-mail" required>
                    </div>
                    <div class="mb-3">
                        <label for="cmail" class="form-label">Confirmar E-mail</label>
                        <input type="email" name="cmail" id="cmail" class="form-control" placeholder="Confirme seu e-mail" required>
                    </div>
                    <div id="recuperarStatus" class="mb-2"></div>
                    <button type="submit" class="btn btn-recuperar w-100" id="btnRecuperar">
                        <i class="fas fa-paper-plane me-1"></i>Enviar Código
                    </button>
                </form>
                <div class="mt-3 text-center">
                    <a href="Login.php" class="text-secondary text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i>Voltar ao Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('formRecuperar').addEventListener('submit', function(e) {
        e.preventDefault();
        var email = document.getElementById('email').value.trim();
        var cmail = document.getElementById('cmail').value.trim();
        var status = document.getElementById('recuperarStatus');
        status.innerHTML = '';
        if (email === '' || cmail === '') {
            status.innerHTML = '<div class="alert alert-danger py-2">Preencha os dois campos de e-mail.</div>';
            return;
        }
        if (email !== cmail) {
            status.innerHTML = '<div class="alert alert-danger py-2">Os e-mails não conferem.</div>';
            return;
        }
        document.getElementById('btnRecuperar').disabled = true;
        document.getElementById('btnRecuperar').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
        fetch('recuperar_senha.php', {
            method: 'POST',
            body: new URLSearchParams({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                status.innerHTML = '<div class="alert alert-success py-2">Código enviado para seu e-mail!</div>';
            } else {
                status.innerHTML = '<div class="alert alert-danger py-2">' + (data.msg || 'E-mail não encontrado.') + '</div>';
            }
            document.getElementById('btnRecuperar').disabled = false;
            document.getElementById('btnRecuperar').innerHTML = '<i class="fas fa-paper-plane me-1"></i>Enviar Código';
        })
        .catch(() => {
            status.innerHTML = '<div class="alert alert-danger py-2">Erro ao enviar. Tente novamente.</div>';
            document.getElementById('btnRecuperar').disabled = false;
            document.getElementById('btnRecuperar').innerHTML = '<i class="fas fa-paper-plane me-1"></i>Enviar Código';
        });
    });
    </script>
</body>
</html>