<?php
session_start();
$title = 'Gerenciar Usuários - Admin';

// Verificar se é admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /chamaservico/admin/login');
    exit;
}

ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-people me-2"></i>Gerenciar Usuários</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/chamaservico/admin/dashboard">Admin</a></li>
                    <li class="breadcrumb-item active">Usuários</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                <i class="bi bi-plus-circle me-1"></i>Novo Usuário
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle mb-0">Total Usuários</h6>
                            <h3 class="card-title mb-0" id="total-usuarios">0</h3>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-people fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle mb-0">Usuários Ativos</h6>
                            <h3 class="card-title mb-0" id="usuarios-ativos">0</h3>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-person-check fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle mb-0">Clientes</h6>
                            <h3 class="card-title mb-0" id="total-clientes">0</h3>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-person fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle mb-0">Prestadores</h6>
                            <h3 class="card-title mb-0" id="total-prestadores">0</h3>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-tools fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form id="formFiltros" class="row g-3">
                <div class="col-md-3">
                    <label for="filtroNome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="filtroNome" name="nome" placeholder="Digite o nome...">
                </div>
                <div class="col-md-3">
                    <label for="filtroEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="filtroEmail" name="email" placeholder="Digite o e-mail...">
                </div>
                <div class="col-md-2">
                    <label for="filtroTipo" class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="cliente">Cliente</option>
                        <option value="prestador">Prestador</option>
                        <option value="ambos">Ambos</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroStatus" class="form-label">Status</label>
                    <select class="form-select" id="filtroStatus" name="status">
                        <option value="">Todos</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                        <i class="bi bi-x"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Usuários</h5>
            <div>
                <button class="btn btn-outline-success btn-sm me-2" onclick="exportarUsuarios()">
                    <i class="bi bi-file-earmark-excel"></i> Exportar
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="atualizarLista()">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="usuarios-lista">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando usuários...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Usuário -->
<div class="modal fade" id="modalNovoUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoUsuario">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Usuário</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="prestador">Prestador</option>
                                    <option value="ambos">Cliente e Prestador</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                    <label class="form-check-label" for="ativo">
                                        Usuário Ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin">
                                    <label class="form-check-label" for="is_admin">
                                        Permissões de Admin
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Carregar estatísticas e lista inicial
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarUsuarios();
    
    // Event listeners
    document.getElementById('formFiltros').addEventListener('submit', function(e) {
        e.preventDefault();
        carregarUsuarios();
    });
    
    document.getElementById('formNovoUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        criarUsuario();
    });
});

function carregarEstatisticas() {
    fetch('/chamaservico/admin/api/dashboard')
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                document.getElementById('total-usuarios').textContent = data.dados.total_usuarios || 0;
                document.getElementById('usuarios-ativos').textContent = data.dados.usuarios_ativos || 0;
                document.getElementById('total-clientes').textContent = data.dados.total_clientes || 0;
                document.getElementById('total-prestadores').textContent = data.dados.total_prestadores || 0;
            }
        })
        .catch(error => console.error('Erro:', error));
}

function carregarUsuarios(page = 1) {
    const filtros = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams(filtros);
    params.append('page', page);
    params.append('per_page', 15);
    
    fetch(`/chamaservico/admin/api/usuarios?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                renderizarUsuarios(data.dados.usuarios);
                renderizarPaginacao(data.dados.paginacao);
            } else {
                document.getElementById('usuarios-lista').innerHTML = 
                    '<div class="alert alert-danger">Erro ao carregar usuários: ' + (data.erro || 'Erro desconhecido') + '</div>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('usuarios-lista').innerHTML = 
                '<div class="alert alert-danger">Erro ao carregar usuários.</div>';
        });
}

function renderizarUsuarios(usuarios) {
    if (usuarios.length === 0) {
        document.getElementById('usuarios-lista').innerHTML = 
            '<div class="text-center py-4"><p class="text-muted">Nenhum usuário encontrado.</p></div>';
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data Cadastro</th>
                        <th>Último Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
    `;

    usuarios.forEach(usuario => {
        const statusBadge = usuario.ativo == 1 
            ? '<span class="badge bg-success">Ativo</span>' 
            : '<span class="badge bg-danger">Inativo</span>';
        
        const tipoBadge = {
            'cliente': '<span class="badge bg-primary">Cliente</span>',
            'prestador': '<span class="badge bg-warning">Prestador</span>',
            'ambos': '<span class="badge bg-info">Ambos</span>',
            'admin': '<span class="badge bg-dark">Admin</span>'
        }[usuario.tipo] || '<span class="badge bg-secondary">Indefinido</span>';

        const dataFormatada = usuario.data_cadastro 
            ? new Date(usuario.data_cadastro).toLocaleDateString('pt-BR')
            : '-';
        
        const ultimoAcesso = usuario.ultimo_acesso 
            ? new Date(usuario.ultimo_acesso).toLocaleDateString('pt-BR')
            : 'Nunca';

        html += `
            <tr>
                <td>${usuario.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar me-2">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                ${usuario.nome.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold">${usuario.nome}</div>
                            ${usuario.telefone ? '<small class="text-muted">' + usuario.telefone + '</small>' : ''}
                        </div>
                    </div>
                </td>
                <td>${usuario.email}</td>
                <td>${tipoBadge}</td>
                <td>${statusBadge}</td>
                <td>${dataFormatada}</td>
                <td>${ultimoAcesso}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editarUsuario(${usuario.id})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-${usuario.ativo == 1 ? 'warning' : 'success'}" 
                                onclick="toggleStatus(${usuario.id})" 
                                title="${usuario.ativo == 1 ? 'Desativar' : 'Ativar'}">
                            <i class="bi bi-${usuario.ativo == 1 ? 'pause' : 'play'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deletarUsuario(${usuario.id})" title="Deletar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    document.getElementById('usuarios-lista').innerHTML = html;
}

function renderizarPaginacao(paginacao) {
    // Implementar paginação se necessário
}

function toggleStatus(id) {
    if (confirm('Tem certeza que deseja alterar o status deste usuário?')) {
        fetch('/chamaservico/admin/toggle-status-usuario', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                carregarUsuarios();
                carregarEstatisticas();
            } else {
                alert('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao alterar status do usuário');
        });
    }
}

function limparFiltros() {
    document.getElementById('formFiltros').reset();
    carregarUsuarios();
}

function atualizarLista() {
    carregarUsuarios();
    carregarEstatisticas();
}

function exportarUsuarios() {
    // Implementar exportação
    alert('Funcionalidade de exportação em desenvolvimento');
}

function criarUsuario() {
    // Implementar criação de usuário
    alert('Funcionalidade de criação em desenvolvimento');
}

function editarUsuario(id) {
    // Implementar edição
    alert('Funcionalidade de edição em desenvolvimento');
}

function deletarUsuario(id) {
    // Implementar deleção
    if (confirm('Tem certeza que deseja deletar este usuário? Esta ação não pode ser desfeita.')) {
        alert('Funcionalidade de deleção em desenvolvimento');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'views/layouts/app.php';
?>
