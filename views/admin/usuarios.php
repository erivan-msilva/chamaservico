<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - Chama Serviço</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Gerenciar Usuários</h1>
        
        <!-- Monitor de Sistema -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Sessões Ativas</h6>
                        <h3 id="total-sessoes">3</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Uso de Memória</h6>
                        <h3 id="memoria-uso">2MB</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Tempo de Resposta</h6>
                        <h3 id="tempo-resposta">0.10s</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Total Usuários</h6>
                        <h3 id="total-usuarios">0</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filtrosForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="filtroNome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="filtroNome" name="nome">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroEmail" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="filtroEmail" name="email">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroTipo" class="form-label">Tipo</label>
                        <select class="form-select" id="filtroTipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="cliente">Cliente</option>
                            <option value="prestador">Prestador</option>
                            <option value="ambos">Ambos</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
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

        <!-- Tabela de usuários -->
        <div class="card">
            <div class="card-body">
                <div id="usuariosLista">
                    <!-- Conteúdo carregado via JavaScript -->
                </div>
                
                <!-- Paginação -->
                <nav id="paginacao" class="mt-3">
                    <!-- Paginação carregada via JavaScript -->
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal para editar usuário -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editarUsuarioForm">
                    <div class="modal-body">
                        <input type="hidden" id="editarId" name="id">
                        <div class="mb-3">
                            <label for="editarNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editarNome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarEmail" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="editarEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarTelefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="editarTelefone" name="telefone">
                        </div>
                        <div class="mb-3">
                            <label for="editarTipo" class="form-label">Tipo</label>
                            <select class="form-select" id="editarTipo" name="tipo" required>
                                <option value="cliente">Cliente</option>
                                <option value="prestador">Prestador</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editarSenha" class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                            <input type="password" class="form-control" id="editarSenha" name="senha">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let paginaAtual = 1;
        const porPagina = 10;

        // Carregar usuários
        function carregarUsuarios(page = 1, filtros = {}) {
            paginaAtual = page;
            const params = new URLSearchParams({
                page: page,
                per_page: porPagina,
                ...filtros
            });

            fetch(`../controllers/UsuarioController.class.php?acao=listar&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        renderizarUsuarios(data.dados.usuarios);
                        renderizarPaginacao(data.dados.paginacao);
                    } else {
                        alert('Erro ao carregar usuários: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar usuários');
                });
        }

        // Renderizar lista de usuários
        function renderizarUsuarios(usuarios) {
            const lista = document.getElementById('usuariosLista');
            
            if (usuarios.length === 0) {
                lista.innerHTML = '<p class="text-center">Nenhum usuário encontrado.</p>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Tipo</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            usuarios.forEach(usuario => {
                const statusBadge = usuario.ativo == 1 ? 
                    '<span class="badge bg-success">Ativo</span>' : 
                    '<span class="badge bg-danger">Inativo</span>';

                html += `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.nome}</td>
                        <td>${usuario.email}</td>
                        <td><span class="badge bg-primary">${usuario.tipo}</span></td>
                        <td>${usuario.telefone || '-'}</td>
                        <td>${statusBadge}</td>
                        <td>${new Date(usuario.data_cadastro).toLocaleDateString('pt-BR')}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(${usuario.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="toggleStatus(${usuario.id})">
                                <i class="bi bi-toggle-${usuario.ativo == 1 ? 'on' : 'off'}"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletarUsuario(${usuario.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            lista.innerHTML = html;
        }

        // Renderizar paginação
        function renderizarPaginacao(paginacao) {
            const nav = document.getElementById('paginacao');
            
            if (paginacao.total_pages <= 1) {
                nav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination justify-content-center">';

            // Anterior
            if (paginacao.page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="carregarUsuarios(${paginacao.page - 1}, getFiltros())">Anterior</a></li>`;
            }

            // Páginas
            for (let i = 1; i <= paginacao.total_pages; i++) {
                const active = i == paginacao.page ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="carregarUsuarios(${i}, getFiltros())">${i}</a></li>`;
            }

            // Próximo
            if (paginacao.page < paginacao.total_pages) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="carregarUsuarios(${paginacao.page + 1}, getFiltros())">Próximo</a></li>`;
            }

            html += '</ul>';
            nav.innerHTML = html;
        }

        // Obter filtros do formulário
        function getFiltros() {
            return {
                nome: document.getElementById('filtroNome').value,
                email: document.getElementById('filtroEmail').value,
                tipo: document.getElementById('filtroTipo').value
            };
        }

        // Limpar filtros
        function limparFiltros() {
            document.getElementById('filtrosForm').reset();
            carregarUsuarios(1);
        }

        // Editar usuário
        function editarUsuario(id) {
            fetch(`../controllers/UsuarioController.class.php?acao=buscar&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const usuario = data.dados;
                        document.getElementById('editarId').value = usuario.id;
                        document.getElementById('editarNome').value = usuario.nome;
                        document.getElementById('editarEmail').value = usuario.email;
                        document.getElementById('editarTelefone').value = usuario.telefone || '';
                        document.getElementById('editarTipo').value = usuario.tipo;
                        
                        new bootstrap.Modal(document.getElementById('editarUsuarioModal')).show();
                    } else {
                        alert('Erro ao carregar usuário: ' + data.mensagem);
                    }
                });
        }

        // Alternar status
        function toggleStatus(id) {
            if (confirm('Deseja alterar o status deste usuário?')) {
                fetch(`../controllers/UsuarioController.class.php?acao=toggle_status&id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert(data.mensagem);
                        carregarUsuarios(paginaAtual, getFiltros());
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                });
            }
        }

        // Deletar usuário
        function deletarUsuario(id) {
            if (confirm('Deseja realmente deletar este usuário? Esta ação não pode ser desfeita.')) {
                fetch(`../controllers/UsuarioController.class.php?acao=deletar&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert(data.mensagem);
                        carregarUsuarios(paginaAtual, getFiltros());
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                });
            }
        }

        // Atualizar monitor do sistema
        function atualizarMonitor() {
            fetch('monitor.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-sessoes').textContent = data.total_sessoes || 0;
                    document.getElementById('memoria-uso').textContent = formatarMemoria(data.memoria_uso || 0);
                    document.getElementById('tempo-resposta').textContent = (data.tempo_resposta || 0).toFixed(3) + 's';
                })
                .catch(error => console.error('Erro ao atualizar monitor:', error));
        }

        function formatarMemoria(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Event listeners
        document.getElementById('filtrosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            carregarUsuarios(1, getFiltros());
        });

        document.getElementById('editarUsuarioForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = formData.get('id');

            fetch(`../controllers/UsuarioController.class.php?acao=atualizar&id=${id}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert(data.mensagem);
                    bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal')).hide();
                    carregarUsuarios(paginaAtual, getFiltros());
                } else {
                    alert('Erro: ' + data.mensagem);
                }
            });
        });

        // Carregar usuários na inicialização
        document.addEventListener('DOMContentLoaded', function() {
            carregarUsuarios();
            atualizarMonitor();
            // Atualizar monitor a cada 30 segundos
            setInterval(atualizarMonitor, 30000);
        });
    </script>
</body>
</html>
