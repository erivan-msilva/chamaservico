<?php
/**
 * Sistema de Sessões Otimizado para Múltiplos Usuários Simultâneos
 * Proteção contra múltiplas inicializações
 */

// Proteção contra múltiplas inclusões
if (defined('SESSION_CLASS_LOADED')) {
    return;
}
define('SESSION_CLASS_LOADED', true);

class Session {
    private static $instance = null;
    private static $isStarted = false;
    private static $sessionName = 'CHAMASERVICO_SESSION';
    private static $sessionTimeout = 7200; // 2 horas
    private static $adminTimeout = 3600; // 1 hora para admin
    
    /**
     * MÉTODO CORRIGIDO: login - Fazer login do usuário
     */
    public static function login($userId, $userName, $userEmail, $userType, $userData = []) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_email'] = $userEmail;
        $_SESSION['user_type'] = $userType;
        $_SESSION['last_activity'] = time();
        
        // Dados adicionais opcionais
        if (isset($userData['foto_perfil'])) {
            $_SESSION['foto_perfil'] = $userData['foto_perfil'];
        }
        
        // Log da sessão criada
        error_log("Login realizado - ID: $userId, Nome: $userName, Tipo: $userType");
        
        // Regenerar ID da sessão por segurança
        session_regenerate_id(true);
        
        return true;
    }

    /**
     * MÉTODO CORRIGIDO: logout - Fazer logout do usuário
     */
    public static function logout() {
        // Log do logout
        $userId = self::getUserId();
        $userName = self::getUserName();
        error_log("Logout realizado - ID: $userId, Nome: $userName");
        
        // Remover dados do usuário da sessão
        $keysToRemove = ['user_id', 'user_name', 'user_email', 'user_type', 'foto_perfil', 'last_activity'];
        foreach ($keysToRemove as $key) {
            unset($_SESSION[$key]);
        }
        
        // Destruir sessão completamente
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        self::$isStarted = false;
        
        return true;
    }

    /**
     * Configuração otimizada para múltiplas sessões simultâneas
     */
    public static function start() {
        // Verificar se já foi iniciada
        if (self::$isStarted) {
            return;
        }
        
        // Verificar se sessão já existe
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$isStarted = true;
            return;
        }
        
        // Configurações otimizadas para produção
        ini_set('session.name', self::$sessionName);
        ini_set('session.cookie_lifetime', 0); // Sessão expira quando browser fecha
        ini_set('session.cookie_httponly', 1); // Proteção XSS
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0); // HTTPS se disponível
        ini_set('session.cookie_samesite', 'Lax'); // Proteção CSRF
        ini_set('session.use_strict_mode', 1); // Strict mode
        ini_set('session.use_only_cookies', 1); // Apenas cookies
        
        // Configurações para múltiplas sessões simultâneas
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1000); // 0.1% chance de garbage collection
        ini_set('session.gc_maxlifetime', self::$sessionTimeout);
        
        // Usar files para sessões (mais estável que database em alta concorrência)
        ini_set('session.save_handler', 'files');
        
        // Diretório customizado para sessões (se disponível)
        $sessionDir = sys_get_temp_dir() . '/chamaservico_sessions';
        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0755, true);
        }
        if (is_writable($sessionDir)) {
            ini_set('session.save_path', $sessionDir);
        }
        
        // Iniciar sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Regenerar ID periodicamente para segurança
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
                session_regenerate_id(true);
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutos
                $_SESSION['last_regeneration'] = time();
                session_regenerate_id(true);
            }
        }
        
        self::$isStarted = true;
        
        // Verificar timeout automaticamente
        self::checkTimeout();
    }

    /**
     * Verificar se sessão foi iniciada
     */
    public static function isStarted() {
        return self::$isStarted;
    }

    /**
     * Verificar se usuário está logado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * MÉTODO CORRIGIDO: requireLogin - Exigir login básico
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            // Salvar URL para redirecionamento
            if (!empty($_SERVER['REQUEST_URI'])) {
                self::set('redirect_after_login', $_SERVER['REQUEST_URI']);
            }
            header('Location: login');
            exit;
        }
        
        if (!self::checkTimeout()) {
            header('Location: login?timeout=1');
            exit;
        }
    }

    /**
     * Exigir login de cliente
     */
    public static function requireClientLogin() {
        self::requireLogin();
        if (!self::isCliente()) {
            header('Location: acesso-negado');
            exit;
        }
    }

    /**
     * Exigir login de prestador
     */
    public static function requirePrestadorLogin() {
        self::requireLogin();
        if (!self::isPrestador()) {
            header('Location: acesso-negado');
            exit;
        }
    }

    /**
     * MÉTODO CORRIGIDO: requirePrestadorAccess
     * Alias para requirePrestadorLogin para compatibilidade
     */
    public static function requirePrestadorAccess() {
        self::requirePrestadorLogin();
    }

    /**
     * Método genérico de controle de acesso por tipo
     * @param string $tipoRequerido - 'cliente', 'prestador', 'ambos'
     */
    public static function requireUserAccess($tipoRequerido) {
        self::requireLogin();
        
        $userType = self::getUserType();
        
        switch ($tipoRequerido) {
            case 'cliente':
                if (!self::isCliente()) {
                    self::redirectAccessDenied();
                }
                break;
                
            case 'prestador':
                if (!self::isPrestador()) {
                    self::redirectAccessDenied();
                }
                break;
                
            case 'ambos':
                if ($userType !== 'ambos') {
                    self::redirectAccessDenied();
                }
                break;
                
            default:
                self::redirectAccessDenied();
        }
    }

    /**
     * Exigir login de administrador
     */
    public static function requireAdminLogin() {
        if (!self::isAdminLoggedIn()) {
            header('Location: admin/login');
            exit;
        }
        
        $lastActivity = $_SESSION['admin_last_activity'] ?? 0;
        if (time() - $lastActivity > self::$adminTimeout) {
            self::destroyAdminSession();
            header('Location: admin/login?timeout=1');
            exit;
        }
        
        $_SESSION['admin_last_activity'] = time();
    }

    /**
     * Verificar se é cliente
     */
    public static function isCliente() {
        $tipo = self::getUserType();
        return $tipo === 'cliente' || $tipo === 'ambos';
    }

    /**
     * Verificar se é prestador
     */
    public static function isPrestador() {
        $tipo = self::getUserType();
        return $tipo === 'prestador' || $tipo === 'ambos';
    }

    /**
     * Verificar se é admin
     */
    public static function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Obter tipo de usuário
     */
    public static function getUserType() {
        return $_SESSION['user_type'] ?? '';
    }

    /**
     * Obter ID do usuário
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obter nome do usuário
     */
    public static function getUserName() {
        return $_SESSION['user_name'] ?? '';
    }

    /**
     * Obter email do usuário
     */
    public static function getUserEmail() {
        return $_SESSION['user_email'] ?? '';
    }

    /**
     * Definir dados do usuário na sessão (método alternativo ao login)
     */
    public static function setUser($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['nome'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_type'] = $userData['tipo'];
        $_SESSION['foto_perfil'] = $userData['foto_perfil'] ?? null;
        $_SESSION['last_activity'] = time();
        
        // Log da sessão criada
        error_log("Sessão criada para usuário: {$userData['id']} - {$userData['nome']} ({$userData['tipo']})");
    }

    /**
     * Definir dados do administrador na sessão
     */
    public static function setAdmin($adminData) {
        $_SESSION['admin_id'] = $adminData['id'];
        $_SESSION['admin_name'] = $adminData['nome'];
        $_SESSION['admin_email'] = $adminData['email'];
        $_SESSION['admin_level'] = $adminData['nivel'];
        $_SESSION['admin_last_activity'] = time();
    }

    /**
     * Método para login de administrador
     */
    public static function loginAdmin($id, $nome, $email, $nivel) {
        if (!self::isStarted()) {
            self::start();
        }
        
        $_SESSION['admin_id'] = $id;
        $_SESSION['admin_name'] = $nome;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_level'] = $nivel;
        $_SESSION['is_admin'] = true;
        $_SESSION['login_time'] = time();
        
        session_regenerate_id(true);
    }

    /**
     * Verificar se é admin
     */
    public static function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Obter ID do admin
     */
    public static function getAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }

    /**
     * Obter nome do admin
     */
    public static function getAdminName() {
        return $_SESSION['admin_name'] ?? null;
    }

    /**
     * Obter nível do admin
     */
    public static function getAdminLevel() {
        return $_SESSION['admin_level'] ?? null;
    }

    /**
     * Obter valor da sessão
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Definir valor na sessão
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Remover valor da sessão
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Verificar timeout da sessão
     */
    public static function checkTimeout() {
        if (!self::isLoggedIn()) {
            return true;
        }
        
        $lastActivity = self::get('last_activity');
        if (!$lastActivity) {
            self::updateLastActivity();
            return true;
        }
        
        $timeout = self::isAdminLoggedIn() ? self::$adminTimeout : self::$sessionTimeout;
        
        if (time() - $lastActivity > $timeout) {
            self::destroy();
            return false;
        }
        
        // Atualizar última atividade a cada 5 minutos para evitar writes excessivos
        if (time() - $lastActivity > 300) {
            self::updateLastActivity();
        }
        
        return true;
    }

    /**
     * Atualizar última atividade
     */
    public static function updateLastActivity() {
        $_SESSION['last_activity'] = time();
    }

    /**
     * Destruir sessão do usuário
     */
    public static function destroy() {
        // Remover dados do usuário mas manter outros dados da sessão
        $keysToRemove = ['user_id', 'user_name', 'user_email', 'user_type', 'foto_perfil', 'last_activity'];
        foreach ($keysToRemove as $key) {
            unset($_SESSION[$key]);
        }
        
        error_log("Sessão de usuário destruída");
    }

    /**
     * Destruir sessão do administrador
     */
    public static function destroyAdminSession() {
        $adminKeys = ['admin_id', 'admin_name', 'admin_email', 'admin_level', 'admin_last_activity'];
        foreach ($adminKeys as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destruir sessão completamente
     */
    public static function destroyAll() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$isStarted = false;
    }

    /**
     * Redirecionar para página de acesso negado
     */
    private static function redirectAccessDenied() {
        // Log da tentativa de acesso negado
        $userId = self::getUserId();
        $userType = self::getUserType();
        $requestedUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        error_log("Acesso negado - User: $userId, Type: $userType, URI: $requestedUri");
        
        header('Location: acesso-negado');
        exit;
    }

    /**
     * Sistema de Flash Messages
     */
    public static function setFlash($key, $message, $type = 'info') {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public static function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }

    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Gerar token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Limpar tokens CSRF expirados
     */
    public static function cleanupCSRF() {
        // Regenerar token CSRF a cada hora
        if (!isset($_SESSION['csrf_generated']) || (time() - $_SESSION['csrf_generated']) > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_generated'] = time();
        }
    }
}

// Auto-inicialização se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE && !defined('SESSION_MANUAL_START')) {
    Session::start();
}
?>
