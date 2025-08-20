<?php
$title = 'Meu Perfil Prestador - ChamaServiço';
ob_start();
?>



<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #f5a522;"><i class="bi bi-person-badge me-2"></i>Meu Perfil Prestador</h2>
            <div>
                <a href="/chamaservico/prestador/perfil/editar" class="btn btn-success">
                    <i class="bi bi-pencil me-1"></i>Editar Perfil
                </a>
                <a href="/chamaservico/prestador/dashboard" class="btn btn-outline-success">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informações do Perfil -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #f5a522;"><i class="bi bi-person-vcard me-2"></i>Informações Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                                <p><strong>Tipo de Conta:</strong>
                                    <span class="badge bg-success"><?= ucfirst($usuario['tipo']) ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($usuario['cpf']): ?>
                                    <p><strong>CPF:</strong> <?= preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario['cpf']) ?></p>
                                <?php endif; ?>
                                <?php if ($usuario['telefone']): ?>
                                    <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']) ?></p>
                                <?php endif; ?>
                                <?php if ($usuario['dt_nascimento']): ?>
                                    <p><strong>Data de Nascimento:</strong> <?= date('d/m/Y', strtotime($usuario['dt_nascimento'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr style="border-color: #4e5264;">

                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Prestador desde:</strong> <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($usuario['ultimo_acesso']): ?>
                                    <p><strong>Último acesso:</strong> <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Profissionais -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #f5a522;"><i class="bi bi-briefcase me-2"></i>Informações Profissionais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Especialidades:</strong></p>
                                <?php
                                $especialidades = isset($usuario['especialidades']) ? explode(',', $usuario['especialidades']) : [];
                                if (!empty($especialidades) && !empty($especialidades[0])):
                                ?>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($esp)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Nenhuma especialidade cadastrada</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Área de Atuação:</strong></p>
                                <span class="text-muted"><?= htmlspecialchars($usuario['area_atuacao'] ?? 'Não informada') ?></span>
                            </div>
                        </div>

                        <hr style="border-color: #4e5264;">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Dica:</strong> Complete seu perfil profissional para receber mais propostas!
                            Clientes confiam mais em prestadores com perfis completos.
                        </div>
                    </div>
                </div>

                <!-- Atividade Recente -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #f5a522;"><i class="bi bi-activity me-2"></i>Atividade Recente</h5>
                        <a href="/chamaservico/prestador/propostas" class="btn btn-sm btn-outline-success">
                            Ver Todas as Propostas
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-data" style="font-size: 3rem; color: #4e5264;"></i>
                            <h6 class="mt-3 text-muted">Suas últimas propostas aparecerão aqui</h6>
                            <p class="text-muted mb-4">Envie propostas para começar a construir seu histórico profissional</p>
                            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success">
                                <i class="bi bi-search me-1"></i>Buscar Serviços Disponíveis
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foto e Estatísticas do Prestador -->
            <div class="col-md-4">
                <!-- Foto do Perfil -->
                <div class="card text-center">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #f5a522;">Foto do Perfil</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php
                            // Verificar foto do perfil
                            $fotoPerfil = $usuario['foto_perfil'];
                            if ($fotoPerfil) {
                                // Remover qualquer prefixo de pasta
                                $fotoPerfil = basename($fotoPerfil);
                                // Verificar se o arquivo realmente existe
                                $caminhoArquivo = "uploads/perfil/" . $fotoPerfil;
                                $arquivoExiste = file_exists($caminhoArquivo);
                            }
                            ?>
                            <?php if ($fotoPerfil && $arquivoExiste): ?>
                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars($fotoPerfil) ?>"
                                    class="rounded-circle profile-img" alt="Foto do perfil"
                                    onerror="console.log('Erro ao carregar imagem: <?= $fotoPerfil ?>'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle profile-img bg-light d-flex align-items-center justify-content-center mx-auto"
                                    style="border-color: #4e5264; display: none;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: #4e5264;"></i>
                                </div>
                            <?php else: ?>
                                <div class="rounded-circle profile-img bg-light d-flex align-items-center justify-content-center mx-auto"
                                    style="border-color: #4e5264;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: #4e5264;"></i>
                                </div>
                                <?php if ($fotoPerfil): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        Arquivo de imagem não encontrado: <?= htmlspecialchars($fotoPerfil) ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="/chamaservico/prestador/perfil/editar" class="btn btn-outline-success">
                                <i class="bi bi-camera me-1"></i>
                                <?= ($usuario['foto_perfil']) ? 'Alterar Foto' : 'Adicionar Foto' ?>
                            </a>
                        </div>

                        <small class="text-muted mt-2 d-block">
                            <?= ($usuario['foto_perfil']) ? 'Uma boa foto aumenta sua credibilidade' : 'Adicione uma foto para ganhar mais confiança dos clientes' ?>
                        </small>
                    </div>
                </div>

                <!-- Estatísticas do Prestador -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #f5a522;"><i class="bi bi-graph-up me-2"></i>Suas Estatísticas</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="border-end" style="border-color: #4e5264 !important;">
                                        <h4 class="text-primary mb-0">0</h4>
                                        <small class="text-muted">Propostas Enviadas</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-0">0</h4>
                                    <small class="text-muted">Aceitas</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="border-end" style="border-color: #4e5264 !important;">
                                        <h4 class="text-info mb-0">0</h4>
                                        <small class="text-muted">Concluídos</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning mb-0">0.0</h4>
                                    <small class="text-muted">Avaliação</small>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color: #4e5264;">

                        <div class="d-grid gap-2">
                            <a href="/chamaservico/prestador/dashboard" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>Ver Dashboard Completo
                            </a>
                            <a href="/chamaservico/prestador/propostas" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-text me-1"></i>Minhas Propostas
                            </a>
                            <a href="/chamaservico/prestador/solicitacoes" class="btn btn-success btn-sm">
                                <i class="bi bi-search me-1"></i>Buscar Serviços
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status do Perfil -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #f5a522;"><i class="bi bi-shield-check me-2"></i>Status do Perfil</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $completude = 0;
                        $itens = [
                            'nome' => !empty($usuario['nome']),
                            'email' => !empty($usuario['email']),
                            'telefone' => !empty($usuario['telefone']),
                            'foto' => !empty($usuario['foto_perfil']),
                            'especialidades' => !empty($usuario['especialidades']),
                            'area_atuacao' => !empty($usuario['area_atuacao'])
                        ];

                        $completude = (array_sum($itens) / count($itens)) * 100;
                        $corBarra = $completude >= 80 ? '#f5a522' : ($completude >= 50 ? '#283579' : '#b02a37');
                        ?>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Completude do Perfil</span>
                                <span style="color: <?= $corBarra ?>; font-weight: bold;"><?= round($completude) ?>%</span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: #1a2240;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= $completude ?>%; background-color: <?= $corBarra ?>;">
                                </div>
                            </div>
                        </div>

                        <div class="checklist">
                            <?php foreach ($itens as $item => $completo): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-<?= $completo ? 'check-circle-fill text-success' : 'circle text-muted' ?> me-2"></i>
                                    <small class="<?= $completo ? 'text-muted' : 'text-warning' ?>">
                                        <?php
                                        $labels = [
                                            'nome' => 'Nome completo',
                                            'email' => 'Email válido',
                                            'telefone' => 'Telefone para contato',
                                            'foto' => 'Foto do perfil',
                                            'especialidades' => 'Especialidades definidas',
                                            'area_atuacao' => 'Área de atuação'
                                        ];
                                        echo $labels[$item];
                                        ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($completude < 100): ?>
                            <div class="mt-3">
                                <a href="/chamaservico/prestador/perfil/editar" class="btn btn-outline-success btn-sm w-100">
                                    <i class="bi bi-pencil me-1"></i>Completar Perfil
                                </a>
                                <a href="/chamaservico/prestador/perfil/enderecos" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="bi bi-geo-alt me-1"></i>Gerenciar Endereços
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';