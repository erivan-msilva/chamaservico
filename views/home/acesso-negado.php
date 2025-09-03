<?php
$title = 'Acesso Negado - ChamaServiço';
ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Acesso Negado
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-shield-x text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="card-title text-dark mb-3">Você não tem permissão para acessar esta área</h5>
                    
                    <p class="card-text text-muted mb-4">
                        Esta funcionalidade é restrita a 
                        <?php
                        $uri = $_SERVER['REQUEST_URI'] ?? '';
                        if (strpos($uri, '/prestador/') !== false) {
                            echo '<strong>Prestadores de Serviço</strong>';
                        } elseif (strpos($uri, '/cliente/') !== false) {
                            echo '<strong>Clientes</strong>';
                        } elseif (strpos($uri, '/admin/') !== false) {
                            echo '<strong>Administradores</strong>';
                        } else {
                            echo '<strong>usuários autorizados</strong>';
                        }
                        ?>.
                    </p>

                    <?php if (Session::isLoggedIn()): ?>
                        <div class="alert alert-info">
                            <strong>Usuário atual:</strong> <?= htmlspecialchars(Session::getUserName()) ?><br>
                            <strong>Tipo de conta:</strong> 
                            <?php
                            $tipo = Session::getUserType();
                            $tipoLabel = [
                                'cliente' => 'Cliente',
                                'prestador' => 'Prestador de Serviço',
                                'ambos' => 'Cliente e Prestador'
                            ];
                            echo $tipoLabel[$tipo] ?? ucfirst($tipo);
                            ?>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if (Session::isCliente()): ?>
                                <a href="/chamaservico/cliente/dashboard" class="btn btn-primary">
                                    <i class="bi bi-speedometer2 me-2"></i>
                                    Ir para Dashboard do Cliente
                                </a>
                            <?php endif; ?>
                            
                            <?php if (Session::isPrestador()): ?>
                                <a href="/chamaservico/prestador/dashboard" class="btn btn-success">
                                    <i class="bi bi-tools me-2"></i>
                                    Ir para Dashboard do Prestador
                                </a>
                            <?php endif; ?>
                            
                            <a href="/chamaservico/" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>
                                Voltar ao Início
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="/chamaservico/login" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Fazer Login
                            </a>
                            <a href="/chamaservico/registro" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>
                                Criar Conta
                            </a>
                            <a href="/chamaservico/" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>
                                Voltar ao Início
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer text-muted text-center">
                    <small>
                        Precisa de uma conta diferente? 
                        <a href="/chamaservico/registro" class="text-decoration-none">Registre-se aqui</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
