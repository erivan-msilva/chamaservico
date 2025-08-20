<?php
require_once '../core/Database.php';
require_once '../models/Usuario.php';

// Inicializar
$database = new Database();
$db = $database->getConnection();
$usuarioModel = new Usuario($db);

// Processar busca
$resultado = [];
$nome_busca = '';

if (isset($_POST['consultar_pessoa_admin'])) {
    $nome_busca = $_POST['nome'] ?? '';
    
    try {
        $dados = $usuarioModel->listar(1, 100, ['nome' => $nome_busca]);
        $resultado = $dados['usuarios'];
    } catch (Exception $e) {
        $erro = "Erro ao buscar usuários: " . $e->getMessage();
    }
} else {
    // Carregar todos os usuários por padrão
    try {
        $dados = $usuarioModel->listar(1, 100);
        $resultado = $dados['usuarios'];
    } catch (Exception $e) {
        $erro = "Erro ao carregar usuários: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Consulta</title>
</head>

<body>
    <div class="container-fluid">
        <br>
        <h2>Gerenciar Pessoas</h2>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Pessoa</label>
                        <input type="text" name="nome" class="form-control" id="nome_pessoa"
                            placeholder="Digite o nome da pessoa..." value="<?php echo htmlspecialchars($nome_busca); ?>">
                    </div>
                </div>
                <div class="col-6 d-flex align-items-end">
                    <button type="submit" name="consultar_pessoa_admin" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Consultar
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
        <br>
    </div>

    <div class="container-fluid">
        <?php if (empty($resultado)): ?>
            <div class="alert alert-info">Nenhum usuário encontrado.</div>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data Cadastro</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultado as $pessoa): ?>
                    <tr>
                        <th scope="row"><?php echo $pessoa['id']; ?></th>
                        <td><?php echo htmlspecialchars($pessoa['nome']); ?></td>
                        <td><?php echo htmlspecialchars($pessoa['cpf'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($pessoa['telefone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($pessoa['email']); ?></td>
                        <td>
                            <span class="badge bg-primary"><?php echo ucfirst($pessoa['tipo']); ?></span>
                        </td>
                        <td>
                            <?php if ($pessoa['ativo'] == 1): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($pessoa['data_cadastro'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick="editarPessoa(<?php echo $pessoa['id']; ?>)">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                            <button type="button" class="btn btn-<?php echo $pessoa['ativo'] ? 'secondary' : 'success'; ?> btn-sm"
                                    onclick="toggleStatus(<?php echo $pessoa['id']; ?>)">
                                <i class="bi bi-toggle-<?php echo $pessoa['ativo'] ? 'on' : 'off'; ?>"></i> 
                                <?php echo $pessoa['ativo'] ? 'Desativar' : 'Ativar'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modal para editar pessoa -->
    <div class="modal fade" id="editarPessoaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Pessoa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editarPessoaForm">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script>
        function editarPessoa(id) {
            fetch(`../controllers/UsuarioController.class.php?acao=buscar&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        const pessoa = data.dados;
                        document.getElementById('editarId').value = pessoa.id;
                        document.getElementById('editarNome').value = pessoa.nome;
                        document.getElementById('editarEmail').value = pessoa.email;
                        document.getElementById('editarTelefone').value = pessoa.telefone || '';
                        document.getElementById('editarTipo').value = pessoa.tipo;
                        
                        new bootstrap.Modal(document.getElementById('editarPessoaModal')).show();
                    } else {
                        alert('Erro ao carregar dados: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados da pessoa');
                });
        }

        function toggleStatus(id) {
            if (confirm('Deseja alterar o status desta pessoa?')) {
                fetch(`../controllers/UsuarioController.class.php?acao=toggle_status&id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert(data.mensagem);
                        location.reload();
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao alterar status');
                });
            }
        }

        // Submit do formulário de edição
        document.getElementById('editarPessoaForm').addEventListener('submit', function(e) {
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
                    bootstrap.Modal.getInstance(document.getElementById('editarPessoaModal')).hide();
                    location.reload();
                } else {
                    alert('Erro: ' + data.mensagem);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao salvar alterações');
            });
        });
    </script>
</body>
</html>