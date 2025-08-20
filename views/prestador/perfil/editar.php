<?php
$title = 'Editar Perfil - ChamaServiço';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #f5a522;"><i class="bi bi-person-gear me-2"></i>Editar Perfil Prestador</h2>
            <a href="/chamaservico/prestador/perfil" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4"></div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if ($usuario['foto_perfil'] && file_exists("uploads/perfil/" . basename($usuario['foto_perfil']))): ?>
                                <img src="/chamaservico/uploads/perfil/<?= htmlspecialchars(basename($usuario['foto_perfil'])) ?>" 
                                    class="rounded-circle profile-img" alt="Foto do perfil">
                            <?php else: ?>
                                <div class="rounded-circle profile-img bg-light d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($usuario['email']) ?></p>
                        
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modalFoto">
                            <i class="bi bi-camera me-1"></i>
                            <?= ($usuario['foto_perfil']) ? 'Alterar Foto' : 'Adicionar Foto' ?>
                        </button>
                    </div>
                </div>
                
                <div class="list-group mt-4 shadow-sm">
                    <a href="#dadosPessoais" class="list-group-item list-group-item-action d-flex align-items-center active" 
                       data-bs-toggle="list">
                        <i class="bi bi-person me-2"></i>Dados Pessoais
                    </a>
                    <a href="#dadosProfissionais" class="list-group-item list-group-item-action d-flex align-items-center" 
                       data-bs-toggle="list">
                        <i class="bi bi-briefcase me-2"></i>Informações Profissionais
                    </a>
                    <a href="#seguranca" class="list-group-item list-group-item-action d-flex align-items-center" 
                       data-bs-toggle="list">
                        <i class="bi bi-shield-lock me-2"></i>Segurança
                    </a>
                    <a href="/chamaservico/prestador/perfil/enderecos" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-geo-alt me-2"></i>Meus Endereços
                    </a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Dados Pessoais -->
                            <div class="tab-pane fade show active" id="dadosPessoais">
                                <h4 class="mb-4"><i class="bi bi-person me-2"></i>Dados Pessoais</h4>
                                
                                <form method="POST" action="/chamaservico/prestador/perfil/editar">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="dados_pessoais">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="nome" name="nome" 
                                                required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="telefone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="telefone" name="telefone" 
                                                value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="cpf" name="cpf" 
                                                value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>" 
                                                placeholder="000.000.000-00"
                                                <?= (!empty($usuario['cpf'])) ? 'readonly' : '' ?>>
                                            <?php if (!empty($usuario['cpf'])): ?>
                                                <div class="form-text text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    CPF já cadastrado não pode ser alterado
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="dt_nascimento" class="form-label">Data de Nascimento</label>
                                            <input type="date" class="form-control" id="dt_nascimento" name="dt_nascimento" 
                                                value="<?= $usuario['dt_nascimento'] ?? '' ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de Conta</label>
                                            <input type="text" class="form-control" 
                                                value="<?= ucfirst($usuario['tipo'] ?? 'prestador') ?>" readonly>
                                            <div class="form-text text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                O tipo de conta não pode ser alterado
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Informações Profissionais -->
                            <div class="tab-pane fade" id="dadosProfissionais">
                                <h4 class="mb-4"><i class="bi bi-briefcase me-2"></i>Informações Profissionais</h4>
                                
                                <form method="POST" action="/chamaservico/prestador/perfil/editar">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="dados_profissionais">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Especialidades/Serviços</label>
                                        
                                        <div class="row g-2">
                                            <?php 
                                            $especialidadesUsuario = isset($usuario['especialidades']) ? 
                                                explode(',', $usuario['especialidades']) : [];
                                            
                                            foreach ($tiposServico as $tipo): 
                                            ?>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="especialidades[]" 
                                                           id="tipo_<?= $tipo['id'] ?>" 
                                                           value="<?= htmlspecialchars($tipo['nome']) ?>"
                                                           <?= in_array($tipo['nome'], $especialidadesUsuario) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="tipo_<?= $tipo['id'] ?>">
                                                        <?= htmlspecialchars($tipo['nome']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            
                                            <!-- Opção "Outro" -->
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="especialidade_outro" 
                                                           id="tipo_outro" 
                                                           value="1" 
                                                           onclick="toggleOutroInput()">
                                                    <label class="form-check-label" for="tipo_outro">
                                                        Outro (especificar)
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12" id="outroInputContainer" style="display: none;">
                                                <input type="text" class="form-control mt-2" 
                                                       name="especialidade_outro_texto" 
                                                       id="especialidade_outro_texto"
                                                       placeholder="Digite outra especialidade">
                                            </div>
                                        </div>
                                        
                                        <small class="text-muted">Selecione os tipos de serviço que você oferece. Isso ajudará os clientes a encontrarem você!</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="area_atuacao" class="form-label">Área de Atuação</label>
                                        <input type="text" class="form-control" id="area_atuacao" name="area_atuacao" 
                                               value="<?= htmlspecialchars($usuario['area_atuacao'] ?? '') ?>" 
                                               placeholder="Ex: Toda Grande São Paulo">
                                        <small class="text-muted">Informe as regiões onde você atende</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="descricao_profissional" class="form-label">Descrição Profissional</label>
                                        <textarea class="form-control" id="descricao_profissional" name="descricao_profissional" 
                                                  rows="4" placeholder="Conte um pouco sobre sua experiência, trabalhos anteriores e diferenciais..."><?= htmlspecialchars($usuario['descricao_profissional'] ?? '') ?></textarea>
                                        <small class="text-muted">Uma boa descrição aumenta significativamente suas chances de conseguir trabalhos!</small>
                                    </div>
                                    
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>Salvar Informações Profissionais
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Segurança -->
                            <div class="tab-pane fade" id="seguranca">
                                <h4 class="mb-4"><i class="bi bi-shield-lock me-2"></i>Segurança</h4>
                                
                                <form method="POST" action="/chamaservico/prestador/perfil/editar">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                                    <input type="hidden" name="acao" value="alterar_senha">
                                    
                                    <div class="mb-3">
                                        <label for="senha_atual" class="form-label">Senha Atual *</label>
                                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nova_senha" class="form-label">Nova Senha *</label>
                                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                            required minlength="6">
                                        <div class="form-text">A senha deve ter no mínimo 6 caracteres</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                    </div>
                                    
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>Alterar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>
                    <?= ($usuario['foto_perfil']) ? 'Alterar' : 'Adicionar' ?> Foto de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/chamaservico/prestador/perfil/editar" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                    <input type="hidden" name="acao" value="upload_foto">
                    
                    <div class="mb-3">
                        <label for="foto_perfil" class="form-label">Selecione uma imagem</label>
                        <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" 
                               accept="image/jpeg,image/jpg,image/png" required>
                        <div class="form-text">
                            Formatos aceitos: JPG, PNG. Tamanho máximo: 2MB
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div id="imagemPreview" class="text-center d-none">
                            <p>Preview da imagem:</p>
                            <img id="previewImg" src="" class="img-fluid rounded-circle" style="max-height: 200px; max-width: 200px;">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>
                            <?= ($usuario['foto_perfil']) ? 'Alterar' : 'Adicionar' ?> Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '
<script>
// Máscara para telefone
function mascaraTelefone(input) {
    let value = input.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
    } else if (value.length > 6) {
        value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
    } else if (value.length > 2) {
        value = value.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
    } else if (value.length > 0) {
        value = value.replace(/^(\d*)/, "($1");
    }
    input.value = value;
}

// Máscara para CPF
function mascaraCPF(input) {
    let value = input.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 9) {
        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, "$1.$2.$3-$4");
    } else if (value.length > 6) {
        value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, "$1.$2.$3");
    } else if (value.length > 3) {
        value = value.replace(/^(\d{3})(\d{0,3}).*/, "$1.$2");
    }
    input.value = value;
}

// Preview da imagem de upload
document.getElementById("foto_perfil").addEventListener("change", function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("previewImg").src = e.target.result;
            document.getElementById("imagemPreview").classList.remove("d-none");
        }
        reader.readAsDataURL(file);
    }
});

// Aplicar máscaras
document.getElementById("telefone").addEventListener("input", function() {
    mascaraTelefone(this);
});

document.getElementById("cpf").addEventListener("input", function() {
    mascaraCPF(this);
});

// Função para mostrar/esconder campo de outra especialidade
function toggleOutroInput() {
    const checkbox = document.getElementById("tipo_outro");
    const container = document.getElementById("outroInputContainer");
    const input = document.getElementById("especialidade_outro_texto");
    
    if (checkbox.checked) {
        container.style.display = "block";
    } else {
        container.style.display = "none";
        input.value = "";
    }
}

// Manter aba ativa após refresh
document.addEventListener("DOMContentLoaded", function() {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`a[href="${hash}"]`);
        if (tab) {
            tab.click();
        }
    }
});
</script>
';

$content = ob_get_clean();
include 'views/layouts/app.php';
?>