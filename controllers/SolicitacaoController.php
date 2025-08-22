<?php
require_once 'models/SolicitacaoServico.php';
require_once 'config/session.php';

class SolicitacaoController
{
    private $model;

    public function __construct()
    {
        $this->model = new SolicitacaoServico();
        Session::requireClientLogin();
    }

    public function listar()
    {
        $userId = Session::getUserId();

        // Capturar filtros da URL
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'urgencia' => $_GET['urgencia'] ?? '',
            'busca' => $_GET['busca'] ?? ''
        ];

        $solicitacoes = $this->model->buscarPorUsuario($userId, $filtros);

        include 'views/solicitacoes/listar.php';
    }

    public function criar()
    {
        $tiposServico = $this->model->getTiposServico();
        $enderecos = $this->model->getEnderecosPorUsuario(Session::getUserId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/solicitacoes/criar');
                exit;
            }

            $dados = [
                'cliente_id' => Session::getUserId(),
                'tipo_servico_id' => $_POST['tipo_servico_id'],
                'endereco_id' => $_POST['endereco_id'],
                'titulo' => trim($_POST['titulo']),
                'descricao' => trim($_POST['descricao']),
                'orcamento_estimado' => !empty($_POST['orcamento_estimado']) ? $_POST['orcamento_estimado'] : null,
                'data_atendimento' => !empty($_POST['data_atendimento']) ? $_POST['data_atendimento'] : null,
                'urgencia' => $_POST['urgencia']
            ];

            $solicitacaoId = $this->model->criar($dados);

            if ($solicitacaoId) {
                // Processar upload de imagens
                $uploadSuccess = $this->processarUploadImagens($solicitacaoId);

                if ($uploadSuccess) {
                    Session::setFlash('success', 'Solicitação criada com sucesso!', 'success');
                } else {
                    Session::setFlash('success', 'Solicitação criada, mas houve problemas no upload de algumas imagens.', 'warning');
                }

                header('Location: /chamaservico/cliente/solicitacoes');
                exit;
            } else {
                Session::setFlash('error', 'Erro ao criar solicitação!', 'danger');
            }
        }

        include 'views/solicitacoes/form.php';
    }

    // Novo: Processar upload de múltiplas imagens
    private function processarUploadImagens($solicitacaoId)
    {
        if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'][0])) {
            return true; // Sem imagens para upload, não é erro
        }

        $uploadDir = 'uploads/solicitacoes/';

        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadSuccess = true;
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        for ($i = 0; $i < count($_FILES['imagens']['name']); $i++) {
            // Verificar se o arquivo foi enviado
            if ($_FILES['imagens']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['imagens']['name'][$i];
                $fileTmpName = $_FILES['imagens']['tmp_name'][$i];
                $fileSize = $_FILES['imagens']['size'][$i];
                $fileType = $_FILES['imagens']['type'][$i];

                // Validações
                if (!in_array($fileType, $allowedTypes)) {
                    $uploadSuccess = false;
                    continue;
                }

                if ($fileSize > $maxFileSize) {
                    $uploadSuccess = false;
                    continue;
                }

                // Gerar nome único para o arquivo
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = 'servico_' . $solicitacaoId . '_' . time() . '_' . $i . '.' . $fileExtension;
                $filePath = $uploadDir . $newFileName;

                // Mover arquivo para o diretório
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    // Salvar no banco de dados
                    if (!$this->model->salvarImagem($solicitacaoId, $newFileName)) {
                        $uploadSuccess = false;
                    }
                } else {
                    $uploadSuccess = false;
                }
            }
        }

        return $uploadSuccess;
    }

    public function editar()
    {
        $id = $_GET['id'] ?? 0;
        $userId = Session::getUserId();

        $solicitacao = $this->model->buscarPorId($id, $userId);
        if (!$solicitacao) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/solicitacoes');
            exit;
        }

        $tiposServico = $this->model->getTiposServico();
        $enderecos = $this->model->getEnderecosPorUsuario($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/solicitacoes/editar?id=' . $id);
                exit;
            }

            $acao = $_POST['acao'] ?? 'atualizar';

            switch ($acao) {
                case 'deletar_imagem':
                    $this->deletarImagem($id, $userId);
                    break;

                case 'adicionar_imagens':
                    $this->adicionarNovasImagens($id);
                    break;

                default:
                    $this->atualizarDadosSolicitacao($id, $userId);
                    break;
            }
        }

        include 'views/solicitacoes/form.php';
    }

    private function atualizarDadosSolicitacao($id, $userId)
    {
        $dados = [
            'tipo_servico_id' => $_POST['tipo_servico_id'],
            'titulo' => trim($_POST['titulo']),
            'descricao' => trim($_POST['descricao']),
            'orcamento_estimado' => !empty($_POST['orcamento_estimado']) ? $_POST['orcamento_estimado'] : null,
            'data_atendimento' => !empty($_POST['data_atendimento']) ? $_POST['data_atendimento'] : null,
            'urgencia' => $_POST['urgencia']
        ];

        if ($this->model->atualizar($id, $dados, $userId)) {
            Session::setFlash('success', 'Solicitação atualizada com sucesso!', 'success');
            header('Location: /chamaservico/cliente/solicitacoes');
            exit;
        } else {
            Session::setFlash('error', 'Erro ao atualizar solicitação!', 'danger');
        }
    }

    private function deletarImagem($solicitacaoId, $userId)
    {
        $imagemId = $_POST['imagem_id'] ?? 0;

        if ($this->model->deletarImagem($imagemId, $solicitacaoId, $userId)) {
            Session::setFlash('success', 'Imagem removida com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao remover imagem!', 'danger');
        }

        header('Location: /chamaservico/cliente/solicitacoes/editar?id=' . $solicitacaoId);
        exit;
    }

    private function adicionarNovasImagens($solicitacaoId)
    {
        $uploadSuccess = $this->processarUploadImagens($solicitacaoId);

        if ($uploadSuccess) {
            Session::setFlash('success', 'Imagens adicionadas com sucesso!', 'success');
        } else {
            Session::setFlash('error', 'Erro ao adicionar algumas imagens!', 'danger');
        }

        header('Location: /chamaservico/cliente/solicitacoes/editar?id=' . $solicitacaoId);
        exit;
    }

    public function visualizar()
    {
        $id = $_GET['id'] ?? 0;
        $userId = Session::getUserId();

        $solicitacao = $this->model->buscarPorId($id, $userId);
        if (!$solicitacao) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: /chamaservico/cliente/solicitacoes');
            exit;
        }

        include 'views/solicitacoes/visualizar.php';
    }

    public function deletar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $userId = Session::getUserId();

            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/cliente/solicitacoes');
                exit;
            }

            if ($this->model->deletar($id, $userId)) {
                Session::setFlash('success', 'Solicitação removida com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao remover solicitação!', 'danger');
            }
        }

        header('Location: /chamaservico/cliente/solicitacoes');
        exit;
    }

    // Novo: Redirecionamento para rotas específicas do cliente
    public function redirectToClient()
    {
        $currentPath = $_SERVER['REQUEST_URI'];
        $newPath = str_replace('/chamaservico/solicitacoes', '/chamaservico/cliente/solicitacoes', $currentPath);
        header("Location: $newPath", true, 301); // Redirect permanente
        exit;
    }
}
