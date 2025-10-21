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
        $filtros = $this->capturarFiltros(['status', 'urgencia', 'busca']);
        $solicitacoes = $this->model->buscarPorUsuario($userId, $filtros);
        include 'views/solicitacoes/listar.php';
    }

    public function criar()
    {
        $tiposServico = $this->model->getTiposServico();
        $enderecos = $this->model->getEnderecosPorUsuario(Session::getUserId());

        // AJAX: Retornar apenas o token CSRF
        if ($_GET['action'] ?? '' === 'csrf') {
            require_once 'config/session.php';
            echo json_encode(['csrf_token' => Session::generateCSRFToken()]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Processar criação da solicitação
            $dados = $this->capturarDadosSolicitacao();
            try {
                $this->validarDadosSolicitacao($dados);
                $solicitacaoId = $this->model->criar($dados);

                if ($solicitacaoId) {
                    $uploadSuccess = $this->processarUploadImagens($solicitacaoId);
                    $this->definirMensagemUpload($uploadSuccess);
                    header('Location: ' . url('solicitacoes'));
                    exit;
                } else {
                    Session::setFlash('error', 'Erro ao criar solicitação!', 'danger');
                }
            } catch (Exception $e) {
                Session::setFlash('error', $e->getMessage(), 'danger');
            }
        }

        include 'views/solicitacoes/form.php';
    }

    private function capturarFiltros(array $campos)
    {
        $filtros = [];
        foreach ($campos as $campo) {
            $filtros[$campo] = $_GET[$campo] ?? '';
        }
        return $filtros;
    }

    private function capturarDadosSolicitacao()
    {
        return [
            'cliente_id' => Session::getUserId(),
            'tipo_servico_id' => $_POST['tipo_servico_id'] ?? null,
            'endereco_id' => $_POST['endereco_id'] ?? null,
            'titulo' =>  ucfirst(trim($_POST['titulo'] ?? '')),
            'descricao' => ucfirst(trim($_POST['descricao'] ?? '')),
            'orcamento_estimado' => $_POST['orcamento_estimado'] ?? null,
            'data_atendimento' => $_POST['data_atendimento'] ?? null,
            'urgencia' => $_POST['urgencia'] ?? ''
        ];
    }

    private function validarDadosSolicitacao($dados)
    {
        $camposObrigatorios = ['tipo_servico_id', 'endereco_id', 'titulo', 'descricao'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($dados[$campo])) {
                throw new Exception("O campo {$campo} é obrigatório!");
            }
        }
    }

    private function definirMensagemUpload($uploadSuccess)
    {
        if ($uploadSuccess) {
            Session::setFlash('success', 'Solicitação criada com sucesso!', 'success');
        } else {
            Session::setFlash('success', 'Solicitação criada, mas houve problemas no upload de algumas imagens.', 'warning');
        }
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
            header('Location: ' . url('solicitacoes'));
            exit;
        }

        $tiposServico = $this->model->getTiposServico();
        $enderecos = $this->model->getEnderecosPorUsuario($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: ' . url('solicitacoes/editar?id=' . $id));
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
            'titulo' =>  ucfirst(trim($_POST['titulo'])),
            'descricao' => ucfirst(trim($_POST['descricao'])),
            'orcamento_estimado' => !empty($_POST['orcamento_estimado']) ? $_POST['orcamento_estimado'] : null,
            'data_atendimento' => !empty($_POST['data_atendimento']) ? $_POST['data_atendimento'] : null,
            'urgencia' => $_POST['urgencia']
        ];

        if ($this->model->atualizar($id, $dados, $userId)) {
            Session::setFlash('success', 'Solicitação atualizada com sucesso!', 'success');
            header('Location: ' . url('solicitacoes'));
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

        header('Location: ' . url('solicitacoes/editar?id=' . $solicitacaoId));
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

        header('Location: ' . url('solicitacoes/editar?id=' . $solicitacaoId));
        exit;
    }

    public function visualizar()
    {
        $id = $_GET['id'] ?? 0;
        $userId = Session::getUserId();

        $solicitacao = $this->model->buscarPorId($id, $userId);
        if (!$solicitacao) {
            Session::setFlash('error', 'Solicitação não encontrada!', 'danger');
            header('Location: ' . url('solicitacoes'));
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
                header('Location: ' . url('solicitacoes'));
                exit;
            }

            if ($this->model->deletar($id, $userId)) {
                Session::setFlash('success', 'Solicitação removida com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao remover solicitação!', 'danger');
            }
        }

        header('Location: ' . url('solicitacoes'));
        exit;
    }

    // CORREÇÃO: Redirecionar usando a função url()
    public function redirectToClient()
    {
        $currentPath = $_SERVER['REQUEST_URI'];
        
        // Remove o BASE_URL da URI atual se presente
        $cleanPath = str_replace(BASE_URL, '', $currentPath);
        
        // Remove '/solicitacoes' e substitui por '/cliente/solicitacoes' 
        $newPath = str_replace('/solicitacoes', '/cliente/solicitacoes', $cleanPath);
        
        // Usar a função url() para gerar a URL correta
        header('Location: ' . url(ltrim($newPath, '/')), true, 301);
        exit;
    }

    public function baixarImagens()
    {
        Session::requireClientLogin();
        $solicitacaoId = $_GET['id'] ?? 0;
        $clienteId = Session::getUserId();

        // Verifica se a solicitação pertence ao cliente logado
        require_once 'models/Proposta.php';
        $propostaModel = new Proposta();
        $solicitacao = $propostaModel->buscarSolicitacaoPorId($solicitacaoId, $clienteId);

        if (!$solicitacao) {
            http_response_code(404);
            echo "Solicitação não encontrada ou acesso negado.";
            exit;
        }

        // Buscar imagens anexadas
        require_once 'models/SolicitacaoServico.php';
        $solicitacaoModel = new SolicitacaoServico();
        $imagens = $solicitacaoModel->buscarImagensPorSolicitacao($solicitacaoId);

        if (empty($imagens)) {
            http_response_code(404);
            echo "Nenhuma imagem encontrada para esta solicitação.";
            exit;
        }

        // VERIFICAÇÃO: Extensão zip habilitada?
        if (!class_exists('ZipArchive')) {
            echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:8px;margin:20px;font-family:Arial,sans-serif;'>
                    <h3>❌ Erro: Extensão ZIP não habilitada</h3>
                    <p>Para baixar todas as imagens em um arquivo ZIP, habilite a extensão <strong>zip</strong> no seu PHP.</p>
                    <ul>
                        <li>Abra o arquivo <strong>php.ini</strong> do XAMPP</li>
                        <li>Procure por <code>;extension=zip</code> e remova o ponto e vírgula</li>
                        <li>Reinicie o Apache</li>
                    </ul>
                    <p>Após habilitar, tente novamente.</p>
                </div>";
            exit;
        }

        // Criar arquivo ZIP temporário
        $zip = new ZipArchive();
        $zipFile = sys_get_temp_dir() . "/solicitacao_{$solicitacaoId}_imagens.zip";
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            echo "Erro ao criar arquivo ZIP.";
            exit;
        }

        foreach ($imagens as $img) {
            $filePath = __DIR__ . '/../uploads/solicitacoes/' . basename($img['caminho_imagem']);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($img['caminho_imagem']));
            }
        }
        $zip->close();

        // Enviar arquivo ZIP para download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="solicitacao_' . $solicitacaoId . '_imagens.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        // Remover arquivo temporário
        unlink($zipFile);
        exit;
    }
}
?>
