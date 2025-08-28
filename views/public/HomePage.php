<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chama Serviço - Conectamos Profissionais e Clientes</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Encontre profissionais qualificados para qualquer serviço ou conecte-se com novos clientes. A maior plataforma de serviços do Brasil.">
    <meta name="keywords" content="serviços, profissionais, eletricista, encanador, diarista, técnico, reforma, reparos">
    <meta name="author" content="Chama Serviço">

    <!-- Open Graph -->
    <meta property="og:title" content="Chama Serviço - A Plataforma que Conecta">
    <meta property="og:description" content="Encontre o profissional ideal para qualquer serviço ou ofereça seus serviços para milhares de clientes.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://chamaservico.com">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-dark: #1e40af;
            --accent-orange: #f59e0b;
            --accent-light: #fbbf24;
            --success-green: #10b981;
            --neutral-gray: #6b7280;
            --light-gray: #f8fafc;
            --white: #ffffff;
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            --gradient-accent: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #1f2937;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        .navbar-custom.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-lg);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: #374151 !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-blue) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: var(--gradient-primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: var(--gradient-primary);
            opacity: 0.05;
            border-radius: 0 0 0 100px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--neutral-gray);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .btn-hero {
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            border: none;
        }

        .btn-hero-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
            color: white;
        }

        .btn-hero-secondary {
            background: white;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
        }

        .btn-hero-secondary:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
        }

        /* Features Grid */
        .features-section {
            padding: 6rem 0;
            background: white;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-blue);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .feature-description {
            color: var(--neutral-gray);
            line-height: 1.6;
        }

        /* Services Grid */
        .services-section {
            padding: 6rem 0;
            background: var(--light-gray);
        }

        .service-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-orange);
        }

        .service-icon {
            font-size: 3rem;
            color: var(--accent-orange);
            margin-bottom: 1rem;
        }

        .service-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .service-count {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: var(--gradient-primary);
            color: white;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Testimonials */
        .testimonials-section {
            padding: 6rem 0;
            background: white;
        }

        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
        }

        .testimonial-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.7;
            color: #374151;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .testimonial-info h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }

        .testimonial-info small {
            color: var(--neutral-gray);
        }

        .stars {
            color: #fbbf24;
            margin-bottom: 1rem;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: var(--gradient-primary);
            color: white;
        }

        .cta-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2.5rem;
        }

        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-section h5 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-link {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            margin-bottom: 0.5rem;
        }

        .footer-link:hover {
            color: white;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        /* Animations */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .pulse-slow {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .cta-title {
                font-size: 2rem;
            }

            .btn-hero {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }

            .feature-card,
            .testimonial-card {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="/chamaservico">
                <i class="bi bi-tools me-2"></i>Chama Serviço
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#como-funciona">Como Funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicos">Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#depoimentos">Depoimentos</a>
                    </li>
                </ul>

                <div class="d-flex gap-2">
                    <a href="/chamaservico/login" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                    </a>
                    <a href="/chamaservico/registro" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Criar Conta
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="inicio" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">
                        Conectamos você ao
                        <span style="background: var(--gradient-accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">profissional ideal</span>
                    </h1>
                    <p class="hero-subtitle">
                        A maior plataforma do Brasil para encontrar profissionais qualificados ou oferecer seus serviços.
                        Rápido, seguro e sem complicações.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="/chamaservico/registro" class="btn-hero btn-hero-primary">
                            <i class="bi bi-search"></i>
                            Encontrar Profissionais
                        </a>
                        <a href="/chamaservico/registro" class="btn-hero btn-hero-secondary">
                            <i class="bi bi-briefcase"></i>
                            Oferecer Serviços
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <img
                            src="assets/img/marketplace-hero.png"
                            alt="Ilustração profissional do marketplace Chama Serviço"
                            class="img-fluid floating shadow rounded-1 border border-3 border-light"
                            style="max-width: 90%; background: #fff; padding: 1px;"
                            loading="lazy">
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="como-funciona">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Como Funciona?</h2>
                <p class="lead text-muted">Em poucos passos você resolve o que precisa ou encontra novos clientes</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h3 class="feature-title">1. Publique</h3>
                        <p class="feature-description">
                            Descreva o serviço que precisa ou publique seus serviços disponíveis
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h3 class="feature-title">2. Conecte</h3>
                        <p class="feature-description">
                            Receba propostas de profissionais ou encontre clientes interessados
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-handshake"></i>
                        </div>
                        <h3 class="feature-title">3. Negocie</h3>
                        <p class="feature-description">
                            Compare propostas, negocie preços e escolha a melhor opção
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h3 class="feature-title">4. Avalie</h3>
                        <p class="feature-description">
                            Realize o serviço com qualidade e construa sua reputação
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                        <span class="stat-number">50K+</span>
                        <div class="stat-label">Profissionais Cadastrados</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                        <span class="stat-number">200K+</span>
                        <div class="stat-label">Serviços Realizados</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                        <span class="stat-number">98%</span>
                        <div class="stat-label">Satisfação dos Clientes</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                        <span class="stat-number">500+</span>
                        <div class="stat-label">Cidades Atendidas</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="servicos">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Serviços Mais Procurados</h2>
                <p class="lead text-muted">Encontre profissionais para qualquer tipo de serviço</p>
            </div>

            <div class="row g-4">
                <?php
                $servicos = [
                    ['icon' => 'tools', 'name' => 'Encanador', 'count' => '2.5k+ profissionais'],
                    ['icon' => 'lightning-charge', 'name' => 'Eletricista', 'count' => '3.1k+ profissionais'],
                    ['icon' => 'house-gear', 'name' => 'Diarista', 'count' => '4.2k+ profissionais'],
                    ['icon' => 'brush', 'name' => 'Pintor', 'count' => '1.8k+ profissionais'],
                    ['icon' => 'hammer', 'name' => 'Pedreiro', 'count' => '2.1k+ profissionais'],
                    ['icon' => 'tree', 'name' => 'Jardineiro', 'count' => '1.3k+ profissionais'],
                    ['icon' => 'laptop', 'name' => 'Técnico TI', 'count' => '900+ profissionais'],
                    ['icon' => 'wrench-adjustable', 'name' => 'Mecânico', 'count' => '1.1k+ profissionais']
                ];

                foreach ($servicos as $index => $servico):
                ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="bi bi-<?= $servico['icon'] ?>"></i>
                            </div>
                            <h4 class="service-name"><?= $servico['name'] ?></h4>
                            <p class="service-count"><?= $servico['count'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section" id="depoimentos">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">O Que Dizem Nossos Usuários</h2>
                <p class="lead text-muted">Milhares de pessoas já confiam na nossa plataforma</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Encontrei um eletricista excelente em menos de 2 horas! O profissional era pontual, educado e resolveu meu problema rapidamente."
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">MS</div>
                            <div class="testimonial-info">
                                <h5>Maria Silva</h5>
                                <small>Cliente desde 2023</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Como prestador, consegui triplicar minha clientela usando a plataforma. O sistema é fácil de usar e me conecta com clientes sérios."
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">JP</div>
                            <div class="testimonial-info">
                                <h5>João Pereira</h5>
                                <small>Prestador - Pintor</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Plataforma incrível! Consegui reformar minha casa inteira encontrando todos os profissionais em um só lugar. Recomendo!"
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">AC</div>
                            <div class="testimonial-info">
                                <h5>Ana Costa</h5>
                                <small>Cliente desde 2022</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <div data-aos="fade-up">
                <h2 class="cta-title">Pronto para Começar?</h2>
                <p class="cta-subtitle">
                    Junte-se a milhares de pessoas que já transformaram a forma de contratar e oferecer serviços
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="/chamaservico/registro" class="btn-hero btn-hero-secondary" style="background: white; color: var(--primary-blue);">
                        <i class="bi bi-person-plus"></i>
                        Cadastrar Gratuitamente
                    </a>
                    <a href="/chamaservico/login" class="btn-hero" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Já Tenho Conta
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>
                        <i class="bi bi-tools me-2"></i>Chama Serviço
                    </h5>
                    <p class="text-muted mb-4">
                        A maior plataforma do Brasil para conectar profissionais qualificados com pessoas que precisam de serviços.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Plataforma</h5>
                        <a href="#" class="footer-link">Como Funciona</a>
                        <a href="#" class="footer-link">Para Clientes</a>
                        <a href="#" class="footer-link">Para Prestadores</a>
                        <a href="#" class="footer-link">Segurança</a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Suporte</h5>
                        <a href="#" class="footer-link">Central de Ajuda</a>
                        <a href="#" class="footer-link">Contato</a>
                        <a href="#" class="footer-link">Status</a>
                        <a href="#" class="footer-link">Blog</a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Legal</h5>
                        <a href="#" class="footer-link">Termos de Uso</a>
                        <a href="#" class="footer-link">Privacidade</a>
                        <a href="#" class="footer-link">Cookies</a>
                        <a href="#" class="footer-link">Licenças</a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Empresa</h5>
                        <a href="#" class="footer-link">Sobre Nós</a>
                        <a href="#" class="footer-link">Carreiras</a>
                        <a href="#" class="footer-link">Imprensa</a>
                        <a href="#" class="footer-link">Investidores</a>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #374151;">

            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; 2024 Chama Serviço. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        Plataforma segura e confiável
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseFloat(counter.textContent.replace(/[^\d.]/g, ''));
                const suffix = counter.textContent.replace(/[\d.]/g, '');
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current) + suffix;
                }, 30);
            });
        }

        // Trigger counter animation when stats section is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>

</html>