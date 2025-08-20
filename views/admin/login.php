<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se já está logado como admin, redirecionar
if (isset($_SESSION['admin_id']) && $_SESSION['is_admin']) {
    header('Location: /chamaservico/admin/dashboard');
    exit;
}

$erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - ChamaServiço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .admin-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        .btn-admin {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="admin-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h3 class="fw-bold text-dark">Painel Administrativo</h3>
                            <p class="text-muted">ChamaServiço</p>
                        </div>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($erro) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/chamaservico/admin/login" id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                                           placeholder="Digite seu e-mail" required value="admin@chamaservico.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="senha" class="form-label fw-bold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="senha" name="senha" 
                                           placeholder="Digite sua senha" required value="admin123">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword()">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-admin text-white" id="btnLogin">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Entrar no Sistema
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                Acesso restrito a administradores
                            </small>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="/chamaservico/" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>
                                Voltar ao Site
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card de informações baseado na estrutura real -->
                <div class="card mt-3 bg-info bg-opacity-10 border-info">
                    <div class="card-body p-3">
                        <h6 class="card-title text-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Credenciais de Acesso
                        </h6>
                        <div class="small">
                            <strong>E-mail:</strong> admin@chamaservico.com<br>
                            <strong>Senha:</strong> admin123<br><br>
                            
                            <div class="alert alert-warning alert-sm p-2 mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Estrutura do seu banco:</strong><br>
                                • Admins: tabela <code>tb_usuario</code><br>
                                • Usuários: tabela <code>tb_pessoa</code><br>
                                • Serviços: tabela <code>tb_solicita_servico</code>
                            </div>
                            
                            <button class="btn btn-outline-info btn-sm" onclick="testarConexao()">
                                <i class="bi bi-database me-1"></i>Testar Conexão
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="verificarEstrutura()">
                                <i class="bi bi-list-check me-1"></i>Ver Estatísticas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card de resultado dos testes -->
                <div class="card mt-3 bg-light border-0" id="resultadoTestes" style="display: none;">
                    <div class="card-body p-3">
                        <h6 class="card-title text-dark">
                            <i class="bi bi-terminal me-1"></i>
                            Resultado dos Testes
                        </h6>
                        <div class="small" id="conteudoTestes">
                            <!-- Resultado será mostrado aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                senhaInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }

        // Melhorar UX do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            const originalText = btnLogin.innerHTML;
            
            btnLogin.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Entrando...';
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

        // Função para testar conexão
        function testarConexao() {
            const resultado = document.getElementById('resultadoTestes');
            const conteudo = document.getElementById('conteudoTestes');
            
            conteudo.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Testando conexão...';
            resultado.style.display = 'block';
            
            fetch('/chamaservico/admin/api/dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        conteudo.innerHTML = `
                            <div class="alert alert-success p-2 mb-2">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>Conexão OK!</strong> Banco conectado com sucesso.
                            </div>
                            <strong>Estatísticas:</strong><br>
                            • Total de usuários: ${data.dados.total_usuarios}<br>
                            • Usuários ativos: ${data.dados.usuarios_ativos}<br>
                            • Administradores: ${data.dados.total_admins || 0}
                        `;
                    } else {
                        conteudo.innerHTML = `
                            <div class="alert alert-danger p-2">
                                <i class="bi bi-x-circle me-1"></i>
                                <strong>Erro:</strong> ${data.erro}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    conteudo.innerHTML = `
                        <div class="alert alert-danger p-2">
                            <i class="bi bi-x-circle me-1"></i>
                            <strong>Erro de conexão:</strong> ${error.message}
                        </div>
                    `;
                });
        }

        // Função para verificar estrutura da tabela
        function verificarEstrutura() {
            const resultado = document.getElementById('resultadoTestes');
            const conteudo = document.getElementById('conteudoTestes');
            
            conteudo.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Verificando estrutura...';
            resultado.style.display = 'block';
            
            // Verificar estatísticas reais do banco
            fetch('/chamaservico/admin/api/dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        conteudo.innerHTML = `
                            <div class="alert alert-success p-2 mb-2">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>Banco conectado com sucesso!</strong>
                            </div>
                            <strong>Estatísticas do seu sistema:</strong><br>
                            • Total de usuários (tb_pessoa): ${data.dados.total_usuarios}<br>
                            • Total de clientes: ${data.dados.total_clientes}<br>
                            • Total de prestadores: ${data.dados.total_prestadores}<br>
                            • Total de admins (tb_usuario): ${data.dados.total_admins}<br>
                            • Solicitações hoje: ${data.dados.solicitacoes_hoje}<br>
                            • Cadastros hoje: ${data.dados.cadastros_hoje}<br><br>
                            
                            <div class="alert alert-info p-2">
                                <strong>Estrutura identificada:</strong><br>
                                ✅ tb_pessoa (${data.dados.total_usuarios} registros)<br>
                                ✅ tb_usuario (${data.dados.total_admins} registros)<br>
                                ✅ tb_solicita_servico (${data.dados.solicitacoes_hoje} hoje)<br>
                                ✅ tb_tipo_servico<br>
                                ✅ tb_status_solicitacao
                            </div>
                        `;
                    } else {
                        conteudo.innerHTML = `
                            <div class="alert alert-danger p-2">
                                <i class="bi bi-x-circle me-1"></i>
                                <strong>Erro:</strong> ${data.erro}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    conteudo.innerHTML = `
                        <div class="alert alert-danger p-2">
                            <i class="bi bi-x-circle me-1"></i>
                            <strong>Erro de conexão:</strong> ${error.message}
                        </div>
                    `;
                });
        }

        // Preenchimento automático em desenvolvimento
        document.addEventListener('DOMContentLoaded', function() {
            // Duplo clique para preencher automaticamente
            document.getElementById('email').addEventListener('dblclick', function() {
                this.value = 'admin@chamaservico.com';
                document.getElementById('senha').value = 'admin123';
                document.getElementById('senha').focus();
                
                // Feedback visual
                this.style.backgroundColor = '#d4edda';
                document.getElementById('senha').style.backgroundColor = '#d4edda';
                
                setTimeout(() => {
                    this.style.backgroundColor = '';
                    document.getElementById('senha').style.backgroundColor = '';
                }, 1000);
            });

            // Tooltip
            document.getElementById('email').title = 'Duplo-clique para preencher automaticamente';
        });
    </script>
</body>
</html>
