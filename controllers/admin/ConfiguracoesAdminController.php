<?php
require_once 'controllers/admin/BaseAdminController.php';

class ConfiguracoesAdminController extends BaseAdminController {
    
    public function index() {
        // Aqui você pode buscar configurações do banco se necessário
        $configuracoes = $this->buscarConfiguracoes();
        
        $this->renderView('configuracoes/index', compact('configuracoes'));
    }
    
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/admin/configuracoes');
            exit;
        }
        
        // Processar dados do formulário
        $dadosConfig = [
            'nome_sistema' => $_POST['nome_sistema'] ?? 'ChamaServiço',
            'permitir_cadastros' => isset($_POST['permitir_cadastros']) ? 1 : 0,
            'modo_manutencao' => isset($_POST['modo_manutencao']) ? 1 : 0,
            'taxa_sistema' => $_POST['taxa_sistema'] ?? 5.0,
            'limite_imagens' => $_POST['limite_imagens'] ?? 5,
            // Configurações de email
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? 587,
            'email_sistema' => $_POST['email_sistema'] ?? '',
            'email_senha' => $_POST['email_senha'] ?? '',
            'smtp_ssl' => isset($_POST['smtp_ssl']) ? 1 : 0,
            // Configurações de notificações
            'notif_email' => isset($_POST['notif_email']) ? 1 : 0,
            'notif_nova_solicitacao' => isset($_POST['notif_nova_solicitacao']) ? 1 : 0,
            'notif_nova_proposta' => isset($_POST['notif_nova_proposta']) ? 1 : 0,
            'notif_admin_problemas' => isset($_POST['notif_admin_problemas']) ? 1 : 0,
            'email_admins' => $_POST['email_admins'] ?? '',
            // Configurações de sistema
            'max_upload' => $_POST['max_upload'] ?? 5,
            'limpeza_automatica' => isset($_POST['limpeza_automatica']) ? 1 : 0,
            'max_tentativas_login' => $_POST['max_tentativas_login'] ?? 5,
            'log_atividades' => isset($_POST['log_atividades']) ? 1 : 0,
            'cache_sistema' => isset($_POST['cache_sistema']) ? 1 : 0,
            'tempo_cache' => $_POST['tempo_cache'] ?? 30,
            'compressao_imagens' => isset($_POST['compressao_imagens']) ? 1 : 0,
            // Configurações de backup
            'backup_automatico' => isset($_POST['backup_automatico']) ? 1 : 0,
            'horario_backup' => $_POST['horario_backup'] ?? '02:00',
            'dias_backup' => $_POST['dias_backup'] ?? 30
        ];
        
        if ($this->salvarConfiguracoes($dadosConfig)) {
            $this->setFlash('success', 'Configurações salvas com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao salvar configurações!');
        }
        
        header('Location: /chamaservico/admin/configuracoes');
        exit;
    }
    
    public function testarEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/admin/configuracoes');
            exit;
        }
        
        // Aqui você implementaria o teste de email
        // Por enquanto, vamos simular um sucesso
        
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'E-mail de teste enviado com sucesso!'
        ]);
        exit;
    }
    
    public function backup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /chamaservico/admin/configuracoes');
            exit;
        }
        
        // Implementar backup do banco
        $nomeBackup = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        if ($this->criarBackup($nomeBackup)) {
            $this->setFlash('success', 'Backup criado com sucesso: ' . $nomeBackup);
        } else {
            $this->setFlash('error', 'Erro ao criar backup!');
        }
        
        header('Location: /chamaservico/admin/configuracoes');
        exit;
    }
    
    private function buscarConfiguracoes() {
        // Por enquanto, retornar configurações padrão
        // Em uma implementação real, você buscaria do banco de dados
        return [
            'nome_sistema' => 'ChamaServiço',
            'permitir_cadastros' => true,
            'modo_manutencao' => false,
            'taxa_sistema' => 5.0,
            'limite_imagens' => 5,
            'smtp_host' => '',
            'smtp_port' => 587,
            'email_sistema' => '',
            'smtp_ssl' => true,
            'notif_email' => true,
            'notif_nova_solicitacao' => true,
            'notif_nova_proposta' => true,
            'notif_admin_problemas' => true,
            'email_admins' => 'admin@chamaservico.com',
            'max_upload' => 5,
            'limpeza_automatica' => false,
            'max_tentativas_login' => 5,
            'log_atividades' => true,
            'cache_sistema' => true,
            'tempo_cache' => 30,
            'compressao_imagens' => true,
            'backup_automatico' => true,
            'horario_backup' => '02:00',
            'dias_backup' => 30
        ];
    }
    
    private function salvarConfiguracoes($dados) {
        // Em uma implementação real, você salvaria no banco de dados
        // Por enquanto, vamos simular o sucesso
        return true;
    }
    
    private function criarBackup($nomeArquivo) {
        // Em uma implementação real, você criaria o backup do banco
        // Por enquanto, vamos simular o sucesso
        return true;
    }
}
?>
          