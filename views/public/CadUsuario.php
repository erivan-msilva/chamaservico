<?php
// Exibe mensagens de erro/sucesso
$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

require_once __DIR__ . '/../../models/PessoaClass.php';

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário - Chama Serviço</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/homepage.css">
    <style>
        body {
            font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
            background: #f8fafc;
        }

        .cadastro-hero {
            background: linear-gradient(90deg, #1a2233 60%, #ffb347 100%);
            color: #fff;
            padding: 60px 0 40px 0;
            border-radius: 0 0 32px 32px;
            margin-bottom: 40px;
        }

        .cadastro-hero .bi {
            font-size: 3rem;
            color: #ffb347;
            background: #fff;
            border-radius: 50%;
            padding: 18px;
            margin-bottom: 16px;
        }

        .cadastro-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(30, 40, 60, 0.07);
            padding: 40px 32px;
            max-width: 480px;
            margin: -80px auto 32px auto;
        }

        .form-label {
            font-weight: 600;
            color: #1a2233;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            font-size: 1.05rem;
        }

        .btn-cadastro {
            background: #1a2233;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-cadastro:hover {
            background: #ffb347;
            color: #1a2233;
        }

        .btn-back {
            background: #fff;
            color: #1a2233;
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid #1a2233;
        }

        .btn-back:hover {
            background: #ffb347;
            color: #1a2233;
            border-color: #ffb347;
        }

        .form-icon {
            font-size: 1.3rem;
            color: #ffb347;
            margin-right: 8px;
        }

        @media (max-width: 575.98px) {
            .cadastro-section {
                padding: 24px 8px;
            }
        }
    </style>
</head>

<body>
    <!--Menu-->
    <?php require_once __DIR__ . '/../components/menu-publico.php'; ?>

    </header>
    <div class="cadastro-hero text-center">
        <div class="container">
            <i class="bi bi-person-plus" style="margin-top: 5px; display: inline-block;"></i>

            <h1 class="fw-bold mb-3">Crie sua conta</h1>
            <p class="lead mb-0" style="max-width: 500px; margin: 0 auto;">
                Cadastre-se gratuitamente e tenha acesso a todos os recursos da plataforma Chama Serviço.
            </p>
        </div>
    </div>
    <div class="cadastro-section">
        <?php if ($erro): ?>
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success text-center">
                <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="/servico/cadusuario/criar" autocomplete="off">
            <!-- Adicione este campo oculto para garantir que o POST sempre envie todos os campos -->
            <input type="hidden" name="enviar_todos_campos" value="1">
            <div class="mb-3">
                <label for="nome" class="form-label"><i class="bi bi-person form-icon"></i>Nome completo</label>
                <input type="text" class="form-control" id="nome" name="nome" required maxlength="80"
                    placeholder="Digite seu nome completo" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="bi bi-envelope form-icon"></i>E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required maxlength="80"
                    placeholder="Digite seu e-mail" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label"><i class="bi bi-lock form-icon"></i>Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required minlength="6"
                    maxlength="32" placeholder="Crie uma senha">
            </div>
            <div class="mb-3">
                <label for="cpf" class="form-label"><i class="bi bi-credit-card-2-front form-icon"></i>CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" required maxlength="14"
                    placeholder="Digite seu CPF" value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="data_nascimento" class="form-label"><i class="bi bi-calendar form-icon"></i>Data de
                    nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required
                    value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label"><i class="bi bi-person-badge form-icon"></i>Tipo de conta</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="cliente" <?php if (($_POST['tipo'] ?? '') == 'cliente') echo 'selected'; ?>>Cliente
                    </option>
                    <option value="prestador" <?php if (($_POST['tipo'] ?? '') == 'prestador') echo 'selected'; ?>>Prestador
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label"><i class="bi bi-telephone form-icon"></i>Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" maxlength="15"
                    placeholder="(99) 99999-9999" required value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-cadastro py-2"><i class="bi bi-check-circle me-1"></i>Cadastrar</button>
                <a href="HomePage.php" class="btn btn-back py-2"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
            </div>
        </form>
    </div>
    <footer class="text-center py-4 mt-5" style="background:#1a2233; color:#fff;">
        &copy; <script>
            document.write(new Date().getFullYear())
        </script> Chama Serviço. Todos os direitos reservados.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validarCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');
            if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
            let soma = 0,
                resto;
            for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            resto = (soma * 10) % 11;
            if ((resto == 10) || (resto == 11)) resto = 0;
            if (resto != parseInt(cpf.substring(9, 10))) return false;
            soma = 0;
            for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            resto = (soma * 10) % 11;
            if ((resto == 10) || (resto == 11)) resto = 0;
            if (resto != parseInt(cpf.substring(10, 11))) return false;
            return true;
        }

        function idadeMinima(data) {
            const nasc = new Date(data);
            const hoje = new Date();
            let idade = hoje.getFullYear() - nasc.getFullYear();
            const m = hoje.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
            return idade >= 18;
        }
       

            // Enviar via AJAX
            const formData = new FormData(this);

            fetch('/servico/cadusuario/criar', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert('Usuário cadastrado com sucesso!');
                        window.location.href = 'Login.php?cadastro=sucesso';
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar cadastro');
                });
      
    </script>
</body>

</html>