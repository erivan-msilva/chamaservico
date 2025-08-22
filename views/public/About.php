<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Sobre nós - Chama Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/homepage.css">
    <style>
        body {
            font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
            background: #f8fafc;
        }

        .about-hero {
            background: linear-gradient(90deg, #1a2233 60%, #ffb347 100%);
            color: #fff;
            padding: 60px 0 40px 0;
            border-radius: 0 0 32px 32px;
            margin-bottom: 40px;
        }

        .about-hero .bi {
            font-size: 3rem;
            color: #ffb347;
            background: #fff;
            border-radius: 50%;
            padding: 18px;
            margin-bottom: 16px;
        }

        .about-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(30, 40, 60, 0.07);
            padding: 40px 32px;
            margin-bottom: 32px;
        }

        .about-values {
            background: #f3f6fa;
            border-radius: 12px;
            padding: 24px;
        }

        .about-values h5 {
            color: #1a2233;
            font-weight: 700;
        }

        .about-values li {
            margin-bottom: 8px;
            font-size: 1.05rem;
        }

        .btn-back {
            background: #1a2233;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-back:hover {
            background: #ffb347;
            color: #1a2233;
        }
    </style>
</head>

<body>
    <!--MENU-->
    <?php require_once __DIR__ . '/../components/menu-publico.php'; ?>
    <div class="about-hero text-center">
        <div class="container">
            <i class="bi bi-people" style="margin-top: 5px; display: inline-block;"></i>
            <h1 class="fw-bold mb-3">Sobre o Chama Serviço</h1>
            <p class="lead mb-0" style="max-width: 600px; margin: 0 auto;">
                Conectando pessoas e profissionais para transformar serviços em experiências positivas.
            </p>
        </div>
    </div>
    <div class="container">
        <div class="about-section mb-4">
            <h3 class="fw-bold mb-3 text-primary">Nossa Missão</h3>
            <p>
                O <strong>Chama Serviço</strong> nasceu para facilitar a vida de quem precisa contratar serviços e valorizar o trabalho de profissionais autônomos e empresas. Nossa missão é promover conexões seguras, rápidas e transparentes entre clientes e prestadores, tornando o processo de contratação mais simples e confiável.
            </p>
        </div>
        <div class="about-section mb-4">
            <h3 class="fw-bold mb-3 text-primary">O que oferecemos?</h3>
            <ul class="about-values list-unstyled">
                <li><i class="bi bi-check-circle-fill text-success"></i> Diversidade de serviços em várias áreas</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> Avaliações reais e portfólio dos profissionais</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> Negociação direta, sem taxas abusivas</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> Segurança e privacidade para todos os usuários</li>
                <li><i class="bi bi-check-circle-fill text-success"></i> Suporte dedicado para clientes e prestadores</li>
            </ul>
        </div>
        <div class="about-section mb-4">
            <h3 class="fw-bold mb-3 text-primary">Nossos Valores</h3>
            <ul class="about-values list-unstyled">
                <li><i class="bi bi-star-fill text-warning"></i> Transparência e ética em todas as relações</li>
                <li><i class="bi bi-star-fill text-warning"></i> Valorização do trabalho profissional</li>
                <li><i class="bi bi-star-fill text-warning"></i> Inovação e melhoria contínua</li>
                <li><i class="bi bi-star-fill text-warning"></i> Respeito à diversidade e inclusão</li>
            </ul>
        </div>
        <div class="text-center mb-5">
            <a href="HomePage.php" class="btn btn-back px-4 py-2"><i class="bi bi-arrow-left me-1"></i>Voltar para Home</a>
        </div>
    </div>
    <footer class="text-center py-4 mt-5" style="background:#1a2233; color:#fff;">
        &copy; <script>
            document.write(new Date().getFullYear())
        </script> Chama Serviço. Todos os direitos reservados.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>