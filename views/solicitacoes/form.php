<?php
$title = 'Nova Solicitação - ChamaServiço';
ob_start();
?>

<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Nova Solicitação</h2>
    <form id="formSolicitacao" method="POST" action="/chamaservico/cliente/solicitacoes/criar" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <!-- Tipo de Serviço -->
        <div class="mb-3">
            <label for="tipo_servico" class="form-label">Tipo de Serviço *</label>
            <select class="form-select" name="tipo_servico" id="tipo_servico" required>
                <option value="">Selecione o tipo de serviço</option>
                <?php foreach ($tiposServico as $tipo): ?>
                    <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Escolha o serviço que melhor representa sua necessidade.</small>
        </div>

        <!-- Endereço -->
        <div class="mb-3">
            <label for="endereco_id" class="form-label">Endereço *</label>
            <select class="form-select" name="endereco_id" id="endereco_id" required>
                <option value="">Selecione o endereço</option>
                <?php foreach ($enderecos as $endereco): ?>
                    <option value="<?= $endereco['id'] ?>">
                        <?= htmlspecialchars($endereco['logradouro'] . ', ' . $endereco['numero'] . ' - ' . $endereco['bairro'] . ', ' . $endereco['cidade'] . ' - ' . $endereco['estado']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($enderecos)): ?>
                <div class="alert alert-warning mt-2">
                    Cadastre um endereço antes de criar uma solicitação.
                    <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Endereço
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Título -->
        <div class="mb-3">
            <label for="titulo" class="form-label">Título *</label>
            <input type="text" class="form-control" name="titulo" id="titulo" required placeholder="Ex: Instalar chuveiro, consertar torneira...">
            <small class="text-muted">Seja breve e objetivo. Exemplo: "Instalar chuveiro elétrico".</small>
        </div>

        <!-- Descrição -->
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição *</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="4" required placeholder="Descreva detalhadamente o que precisa ser feito"></textarea>
            <small class="text-muted">Informe detalhes importantes: local, problema, expectativas, horários, etc.</small>
        </div>

        <!-- Fotos -->
        <div class="mb-3">
            <label for="imagens" class="form-label">Fotos do Local/Problema</label>
            <input type="file" class="form-control" name="imagens[]" id="imagens" multiple accept="image/*">
            <small class="text-muted">Adicione fotos para ajudar os prestadores a entenderem melhor o serviço. Máximo 5MB por imagem. Formatos: JPG, PNG, GIF.</small>
        </div>

        <!-- Orçamento -->
        <div class="mb-3">
            <label for="orcamento_estimado" class="form-label">Orçamento Estimado</label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="number" class="form-control" name="orcamento_estimado" id="orcamento_estimado" step="0.01" min="0" placeholder="0,00">
            </div>
            <small class="text-muted">Informe um valor se desejar receber propostas dentro de um orçamento.</small>
        </div>

        <!-- Data Preferencial -->
        <div class="mb-3">
            <label for="data_atendimento" class="form-label">Data Preferencial</label>
            <input type="datetime-local" class="form-control" name="data_atendimento" id="data_atendimento">
            <small class="text-muted">Selecione a data e horário desejados para o serviço.</small>
        </div>

        <!-- Urgência -->
        <div class="mb-3">
            <label for="urgencia" class="form-label">Urgência *</label>
            <select class="form-select" name="urgencia" id="urgencia" required>
                <option value="baixa">Baixa</option>
                <option value="media" selected>Média</option>
                <option value="alta">Alta</option>
            </select>
            <small class="text-muted">Serviços urgentes são priorizados pelos prestadores.</small>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Criar Solicitação</button>
            <a href="/chamaservico/cliente/solicitacoes" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </form>
</div>

<!-- Modal de Cadastro de Endereço com busca por CEP -->
<div class="modal fade" id="modalEndereco" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="/chamaservico/cliente/perfil/enderecos">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>Cadastrar Endereço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCSRFToken() ?>">
                <input type="hidden" name="acao" value="criar">
                <div class="mb-3">
                    <label for="cep" class="form-label">CEP *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="cep" id="cepModal" required maxlength="9" pattern="\d{5}-?\d{3}">
                        <button type="button" class="btn btn-outline-info" id="btnBuscarCepModal" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div id="cepStatusModal" class="small mt-1"></div>
                </div>
                <div class="mb-3">
                    <label for="logradouro" class="form-label">Logradouro *</label>
                    <input type="text" class="form-control" name="logradouro" id="logradouroModal" required>
                </div>
                <div class="mb-3">
                    <label for="numero" class="form-label">Número *</label>
                    <input type="text" class="form-control" name="numero" id="numeroModal" required>
                </div>
                <div class="mb-3">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" name="complemento" id="complementoModal">
                </div>
                <div class="mb-3">
                    <label for="bairro" class="form-label">Bairro *</label>
                    <input type="text" class="form-control" name="bairro" id="bairroModal" required>
                </div>
                <div class="mb-3">
                    <label for="cidade" class="form-label">Cidade *</label>
                    <input type="text" class="form-control" name="cidade" id="cidadeModal" required>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado *</label>
                    <input type="text" class="form-control" name="estado" id="estadoModal" required maxlength="2">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="principal" id="principalModal" value="1" checked>
                    <label class="form-check-label" for="principalModal">Definir como endereço principal</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Endereço</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (empty($enderecos)): ?>
        // Impede envio do formulário de solicitação se não houver endereço
        const formSolicitacao = document.querySelector('form#formSolicitacao');
        if (formSolicitacao) {
            formSolicitacao.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Cadastre um endereço antes de criar a solicitação.');
            });
        }
    <?php endif; ?>
});

document.getElementById('btnBuscarCepModal').addEventListener('click', function() {
    const cepInput = document.getElementById('cepModal');
    const cep = cepInput.value.replace(/\D/g, '');
    const status = document.getElementById('cepStatusModal');
    if (cep.length !== 8) {
        status.textContent = 'CEP inválido';
        status.className = 'text-danger small';
        return;
    }
    status.textContent = 'Buscando endereço...';
    status.className = 'text-muted small';

    fetch('/chamaservico/cliente/perfil/api/buscar-cep?cep=' + cep)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.endereco) {
                document.getElementById('logradouroModal').value = data.endereco.logradouro || '';
                document.getElementById('bairroModal').value = data.endereco.bairro || '';
                document.getElementById('cidadeModal').value = data.endereco.cidade || '';
                document.getElementById('estadoModal').value = data.endereco.estado || '';
                status.textContent = 'Endereço preenchido automaticamente!';
                status.className = 'text-success small';
            } else {
                status.textContent = data.message || 'Endereço não encontrado';
                status.className = 'text-warning small';
            }
        })
        .catch(() => {
            status.textContent = 'Erro ao buscar endereço';
            status.className = 'text-danger small';
        });
});
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
