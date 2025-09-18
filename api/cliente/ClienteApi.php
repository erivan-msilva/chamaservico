<?php
// Definir o cabeçalho como arquivo JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Não mostrar erros em produção
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Definir token estático para exemplo
define('API_TOKEN', '781e5e245d69b566979b86e28d23f2c7');

// Incluir dependências
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/Pessoa.php';
require_once '../models/SolicitacaoServico.php';
require_once '../models/Proposta.php';
require_once '../models/ClientePerfil.php';

/**
 * Função para verificar se o token enviado é válido
 */
function verificarToken($headers)
{
    if (!isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        return $token === API_TOKEN;
    }

    return false;
}

/**
 * Função para autenticar cliente
 */
function autenticarCliente($input)
{
    try {
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            return ['erro' => 'Email e senha são obrigatórios'];
        }

        $pessoa = new Pessoa();
        $usuario = $pessoa->verificarSenha($email, $senha);

        if ($usuario && $usuario['ativo']) {
            // Atualizar último acesso
            $pessoa->atualizarUltimoAcesso($usuario['id']);

            // Remover dados sensíveis
            unset($usuario['senha']);
            unset($usuario['token_redefinicao']);
            unset($usuario['token_expiracao']);

            return [
                'sucesso' => true,
                'usuario' => $usuario,
                'token' => API_TOKEN // Em produção, gerar token único
            ];
        } else {
            return ['erro' => 'Credenciais inválidas'];
        }
    } catch (Exception $e) {
        error_log("Erro na autenticação: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para registrar novo cliente
 */
function registrarCliente($input)
{
    try {
        $nome = $input['nome'] ?? '';
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        $telefone = $input['telefone'] ?? '';
        $tipo = 'cliente';

        if (empty($nome) || empty($email) || empty($senha)) {
            return ['erro' => 'Nome, email e senha são obrigatórios'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['erro' => 'Email inválido'];
        }

        if (strlen($senha) < 6) {
            return ['erro' => 'Senha deve ter pelo menos 6 caracteres'];
        }

        $pessoa = new Pessoa();

        // Verificar se email já existe
        if ($pessoa->emailExiste($email)) {
            return ['erro' => 'Este email já está cadastrado'];
        }

        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'tipo' => $tipo,
            'telefone' => $telefone
        ];

        $pessoaId = $pessoa->criar($dados);

        if ($pessoaId) {
            return [
                'sucesso' => true,
                'cliente_id' => $pessoaId,
                'mensagem' => 'Cliente cadastrado com sucesso'
            ];
        } else {
            return ['erro' => 'Erro ao cadastrar cliente'];
        }
    } catch (Exception $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para buscar perfil do cliente
 */
function buscarPerfil($clienteId)
{
    try {
        $perfil = new ClientePerfil();
        $pessoa = new Pessoa();

        $dadosPessoa = $pessoa->buscarPorId($clienteId);
        if (!$dadosPessoa) {
            return ['erro' => 'Cliente não encontrado'];
        }

        // Remover dados sensíveis
        unset($dadosPessoa['senha']);
        unset($dadosPessoa['token_redefinicao']);
        unset($dadosPessoa['token_expiracao']);

        // Buscar endereços
        $enderecos = $perfil->buscarEnderecos($clienteId);

        return [
            'sucesso' => true,
            'perfil' => $dadosPessoa,
            'enderecos' => $enderecos
        ];
    } catch (Exception $e) {
        error_log("Erro ao buscar perfil: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para listar solicitações do cliente
 */
function listarSolicitacoes($clienteId, $filtros = [])
{
    try {
        $solicitacao = new SolicitacaoServico();
        $solicitacoes = $solicitacao->buscarPorUsuario($clienteId, $filtros);

        // Adicionar contagem de propostas para cada solicitação
        $proposta = new Proposta();
        foreach ($solicitacoes as &$sol) {
            $sol['total_propostas'] = $proposta->contarPropostasPorSolicitacao($sol['id']);
        }

        return [
            'sucesso' => true,
            'solicitacoes' => $solicitacoes,
            'total' => count($solicitacoes)
        ];
    } catch (Exception $e) {
        error_log("Erro ao listar solicitações: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para criar nova solicitação
 */
function criarSolicitacao($input, $clienteId)
{
    try {
        $dados = [
            'cliente_id' => $clienteId,
            'titulo' => $input['titulo'] ?? '',
            'descricao' => $input['descricao'] ?? '',
            'tipo_servico_id' => $input['tipo_servico_id'] ?? 0,
            'endereco_id' => $input['endereco_id'] ?? 0,
            'urgencia' => $input['urgencia'] ?? 'media',
            'orcamento_estimado' => $input['orcamento_estimado'] ?? null,
            'data_preferencial' => $input['data_preferencial'] ?? null,
            'observacoes' => $input['observacoes'] ?? ''
        ];

        // Validações básicas
        if (empty($dados['titulo']) || empty($dados['descricao'])) {
            return ['erro' => 'Título e descrição são obrigatórios'];
        }

        if ($dados['tipo_servico_id'] <= 0) {
            return ['erro' => 'Tipo de serviço é obrigatório'];
        }

        if ($dados['endereco_id'] <= 0) {
            return ['erro' => 'Endereço é obrigatório'];
        }

        $solicitacao = new SolicitacaoServico();
        $solicitacaoId = $solicitacao->criar($dados);

        if ($solicitacaoId) {
            return [
                'sucesso' => true,
                'solicitacao_id' => $solicitacaoId,
                'mensagem' => 'Solicitação criada com sucesso'
            ];
        } else {
            return ['erro' => 'Erro ao criar solicitação'];
        }
    } catch (Exception $e) {
        error_log("Erro ao criar solicitação: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para listar propostas recebidas
 */
function listarPropostas($clienteId, $filtros = [])
{
    try {
        $proposta = new Proposta();
        $propostas = $proposta->buscarPropostasRecebidas($clienteId, $filtros);

        return [
            'sucesso' => true,
            'propostas' => $propostas,
            'total' => count($propostas)
        ];
    } catch (Exception $e) {
        error_log("Erro ao listar propostas: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para aceitar proposta
 */
function aceitarProposta($input, $clienteId)
{
    try {
        $propostaId = $input['proposta_id'] ?? 0;

        if ($propostaId <= 0) {
            return ['erro' => 'ID da proposta é obrigatório'];
        }

        $proposta = new Proposta();
        if ($proposta->aceitarProposta($propostaId, $clienteId)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Proposta aceita com sucesso'
            ];
        } else {
            return ['erro' => 'Erro ao aceitar proposta'];
        }
    } catch (Exception $e) {
        error_log("Erro ao aceitar proposta: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para buscar tipos de serviço
 */
function buscarTiposServico()
{
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, nome FROM tb_tipo_servico WHERE ativo = 1 ORDER BY nome");
        $stmt->execute();
        $tipos = $stmt->fetchAll();

        return [
            'sucesso' => true,
            'tipos_servico' => $tipos
        ];
    } catch (Exception $e) {
        error_log("Erro ao buscar tipos de serviço: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

/**
 * Função para adicionar endereço
 */
function adicionarEndereco($input, $clienteId)
{
    try {
        $dados = [
            'pessoa_id' => $clienteId,
            'cep' => $input['cep'] ?? '',
            'logradouro' => $input['logradouro'] ?? '',
            'numero' => $input['numero'] ?? '',
            'complemento' => $input['complemento'] ?? '',
            'bairro' => $input['bairro'] ?? '',
            'cidade' => $input['cidade'] ?? '',
            'estado' => $input['estado'] ?? '',
            'principal' => $input['principal'] ?? false
        ];

        // Validações básicas
        if (empty($dados['cep']) || empty($dados['logradouro']) || empty($dados['numero'])) {
            return ['erro' => 'CEP, logradouro e número são obrigatórios'];
        }

        $perfil = new ClientePerfil();
        $enderecoId = $perfil->criarEndereco($dados);

        if ($enderecoId) {
            return [
                'sucesso' => true,
                'endereco_id' => $enderecoId,
                'mensagem' => 'Endereço adicionado com sucesso'
            ];
        } else {
            return ['erro' => 'Erro ao adicionar endereço'];
        }
    } catch (Exception $e) {
        error_log("Erro ao adicionar endereço: " . $e->getMessage());
        return ['erro' => 'Erro interno do servidor'];
    }
}

// Capturar headers da requisição
$headers = getallheaders() ?: [];

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar token (exceto para login e registro)
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];

// Endpoints que não precisam de autenticação
$publicEndpoints = ['/login', '/registro', '/tipos-servico'];
$isPublicEndpoint = false;

foreach ($publicEndpoints as $endpoint) {
    if (strpos($uri, $endpoint) !== false) {
        $isPublicEndpoint = true;
        break;
    }
}

if (!$isPublicEndpoint && !verificarToken($headers)) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido ou ausente']);
    exit;
}

// Roteamento baseado na URI
try {
    // Extrair o endpoint da URI
    $pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH), '/'));
    $endpoint = end($pathParts);

    // Extrair cliente_id do token/headers (em produção, extrair do JWT)
    $clienteId = $input['cliente_id'] ?? ($_GET['cliente_id'] ?? null);

    switch ($method) {
        case 'GET':
            switch ($endpoint) {
                case 'tipos-servico':
                    $response = buscarTiposServico();
                    break;

                case 'perfil':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $response = buscarPerfil($clienteId);
                    break;

                case 'solicitacoes':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $filtros = [
                        'status' => $_GET['status'] ?? '',
                        'tipo_servico' => $_GET['tipo_servico'] ?? ''
                    ];
                    $response = listarSolicitacoes($clienteId, $filtros);
                    break;

                case 'propostas':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $filtros = [
                        'status' => $_GET['status'] ?? '',
                        'solicitacao_id' => $_GET['solicitacao_id'] ?? ''
                    ];
                    $response = listarPropostas($clienteId, $filtros);
                    break;

                default:
                    $response = ['erro' => 'Endpoint não encontrado'];
                    http_response_code(404);
                    break;
            }
            break;

        case 'POST':
            switch ($endpoint) {
                case 'login':
                    $response = autenticarCliente($input);
                    break;

                case 'registro':
                    $response = registrarCliente($input);
                    break;

                case 'solicitacoes':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $response = criarSolicitacao($input, $clienteId);
                    break;

                case 'enderecos':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $response = adicionarEndereco($input, $clienteId);
                    break;

                case 'aceitar-proposta':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $response = aceitarProposta($input, $clienteId);
                    break;

                default:
                    $response = ['erro' => 'Endpoint não encontrado'];
                    http_response_code(404);
                    break;
            }
            break;

        case 'PUT':
            switch ($endpoint) {
                case 'perfil':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    // TODO: Implementar atualização de perfil
                    $response = ['erro' => 'Funcionalidade em desenvolvimento'];
                    break;

                default:
                    $response = ['erro' => 'Endpoint não encontrado'];
                    http_response_code(404);
                    break;
            }
            break;

        case 'DELETE':
            switch ($endpoint) {
                case 'enderecos':
                    if (!$clienteId) {
                        $response = ['erro' => 'ID do cliente é obrigatório'];
                        break;
                    }
                    $enderecoId = $input['endereco_id'] ?? 0;
                    if ($enderecoId <= 0) {
                        $response = ['erro' => 'ID do endereço é obrigatório'];
                        break;
                    }

                    $perfil = new ClientePerfil();
                    if ($perfil->excluirEndereco($enderecoId, $clienteId)) {
                        $response = [
                            'sucesso' => true,
                            'mensagem' => 'Endereço excluído com sucesso'
                        ];
                    } else {
                        $response = ['erro' => 'Erro ao excluir endereço'];
                    }
                    break;

                default:
                    $response = ['erro' => 'Endpoint não encontrado'];
                    http_response_code(404);
                    break;
            }
            break;

        default:
            $response = ['erro' => 'Método não permitido'];
            http_response_code(405);
            break;
    }
} catch (Exception $e) {
    error_log("Erro na API: " . $e->getMessage());
    $response = ['erro' => 'Erro interno do servidor'];
    http_response_code(500);
}

// Retornar resposta
echo json_encode($response, JSON_UNESCAPED_UNICODE);
