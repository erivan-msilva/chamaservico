<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Plataforma Chama Serviço - Encontre os melhores prestadores de serviços ou divulgue seu trabalho. Facilidade, segurança e qualidade em um só lugar.">
  <meta name="keywords" content="serviços, prestadores, autônomos, reformas, reparos, encanador, eletricista, diarista">
  <title>Chama Serviço | Plataforma de Serviços - Encontre Profissionais Qualificados</title>

  <!-- Preload de recursos importantes -->
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Poppins:400,600,700&display=swap" as="style">
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style">

  <!-- Favicon -->
  <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- AOS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/homepage.css">
  <style>
    .contact-section {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(30, 40, 60, 0.07);
      padding: 40px 32px;
      max-width: 500px;
      margin: 40px auto 32px auto;
    }

    .form-label {
      font-weight: 600;
      color: #1a2233;
    }

    .form-control {
      border-radius: 8px;
      font-size: 1.05rem;
    }

    .btn-contact {
      background: #1a2233;
      color: #fff;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.2s;
    }

    .btn-contact:hover {
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
      .contact-section {
        padding: 24px 8px;
      }
    }
  </style>
</head>

<body>
  <!--MENU-->
  <?php require_once __DIR__ . '/../components/menu-publico.php'; ?>
  
  <div class="container" style="margin-top: 110px;">
    <div class="contact-section">
      <h2 class="mb-4 text-center"><i class="bi bi-chat-dots form-icon"></i>Fale conosco</h2>
      <form id="contatoForm" method="post" action="enviaContato.php" autocomplete="off">
        <div class="mb-3">
          <label for="nome" class="form-label"><i class="bi bi-person form-icon"></i>Seu nome</label>
          <input type="text" class="form-control" id="nome" name="nome" required maxlength="80" placeholder="Digite seu nome completo">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label"><i class="bi bi-envelope form-icon"></i>Seu e-mail</label>
          <input type="email" class="form-control" id="email" name="email" required maxlength="80" placeholder="Digite seu e-mail">
        </div>
        <div class="mb-3">
          <label for="mensagem" class="form-label"><i class="bi bi-chat-left-text form-icon"></i>Mensagem</label>
          <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required maxlength="500" placeholder="Digite sua mensagem"></textarea>
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-contact py-2"><i class="bi bi-send me-1"></i>Enviar</button>
          <a href="HomePage.php" class="btn btn-back py-2"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
        </div>
      </form>
      <div class="mt-4 text-center">
        <strong>Email:</strong> contato@chamaservico.com<br>
        <strong>Telefone:</strong> (11) 1234-5678
      </div>
    </div>
  </div>

  <!-- Modal de sucesso -->
  <div class="modal fade" id="modalSucesso" tabindex="-1" aria-labelledby="modalSucessoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-header border-0">
          <h5 class="modal-title w-100" id="modalSucessoLabel"><i class="bi bi-check-circle-fill text-success me-2"></i>Mensagem enviada!</h5>
        </div>
        <div class="modal-body">
          <p class="mb-0">Seu contato foi enviado com sucesso.<br>Em breve nossa equipe retornará.</p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-contact" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('contatoForm').addEventListener('submit', function(e) {
      e.preventDefault();
      // Simula envio AJAX (substitua por AJAX real se desejar)
      setTimeout(function() {
        var modal = new bootstrap.Modal(document.getElementById('modalSucesso'));
        modal.show();
        document.getElementById('contatoForm').reset();
      }, 500);
    });
  </script>
</body>

</html>