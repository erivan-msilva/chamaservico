<?php
require_once 'models/OrdemServico.php';
require_once 'config/session.php';

class OrdemServicoController {
    private $model;
    
    public function __construct() {
        $this->model = new OrdemServico();
        Session::requireClientLogin();
    }
    
    public function visualizar() {
        $osId = $_GET['id'] ?? 0;
        $userId = Session::getUserId();
        
        $ordemServico = $this->model->buscarPorId($osId, $userId);
        
        if (!$ordemServico) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada!', 'danger');
            header('Location: /chamaservico/');
            exit;
        }
        
        include 'views/ordem_servico/visualizar.php';
    }
    
    public function download() {
        $osId = $_GET['id'] ?? 0;
        $userId = Session::getUserId();
        
        $ordemServico = $this->model->buscarPorId($osId, $userId);
        
        if (!$ordemServico) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada!', 'danger');
            header('Location: /chamaservico/');
            exit;
        }
        
        $caminhoArquivo = $this->model->gerarPDF($osId, true);
        
        if (file_exists($caminhoArquivo)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($caminhoArquivo) . '"');
            header('Content-Length: ' . filesize($caminhoArquivo));
            readfile($caminhoArquivo);
            exit;
        } else {
            Session::setFlash('error', 'Arquivo não encontrado!', 'danger');
            header('Location: /chamaservico/ordem-servico/visualizar?id=' . $osId);
            exit;
        }
    }
    
    public function enviarEmail() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/');
                exit;
            }
            
            $osId = $_POST['os_id'] ?? 0;
            $email = $_POST['email'] ?? '';
            $userId = Session::getUserId();
            
            $ordemServico = $this->model->buscarPorId($osId, $userId);
            
            if (!$ordemServico) {
                Session::setFlash('error', 'Ordem de Serviço não encontrada!', 'danger');
                header('Location: /chamaservico/');
                exit;
            }
            
            if ($this->model->enviarPorEmail($osId, $email)) {
                Session::setFlash('success', 'Ordem de Serviço enviada por email com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao enviar email!', 'danger');
            }
            
            header('Location: /chamaservico/ordem-servico/visualizar?id=' . $osId);
            exit;
        }
    }
    
    public function assinar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', 'Token de segurança inválido!', 'danger');
                header('Location: /chamaservico/');
                exit;
            }
            
            $osId = $_POST['os_id'] ?? 0;
            $assinatura = $_POST['assinatura'] ?? '';
            $userId = Session::getUserId();
            
            $ordemServico = $this->model->buscarPorId($osId, $userId);
            
            if (!$ordemServico) {
                Session::setFlash('error', 'Ordem de Serviço não encontrada!', 'danger');
                header('Location: /chamaservico/');
                exit;
            }
            
            // Determinar tipo de assinatura
            $tipoAssinatura = ($ordemServico['cliente_id'] == $userId) ? 'cliente' : 'prestador';
            
            $dadosAssinatura = [
                'assinatura' => $assinatura,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'timestamp' => time()
            ];
            
            if ($this->model->assinarOS($osId, $userId, $tipoAssinatura, $dadosAssinatura)) {
                Session::setFlash('success', 'Assinatura registrada com sucesso!', 'success');
            } else {
                Session::setFlash('error', 'Erro ao registrar assinatura!', 'danger');
            }
            
            header('Location: /chamaservico/ordem-servico/visualizar?id=' . $osId);
            exit;
        }
    }
    
    public function listar() {
        $userId = Session::getUserId();
        $ordensServico = $this->model->buscarPorUsuario($userId);
        
       
    }
}
?>
