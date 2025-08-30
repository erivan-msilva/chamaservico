<?php
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Novo: Login do usuário
    public static function login($pessoa_id, $nome, $email, $tipo) {
        self::start();
        $_SESSION['pessoa_id'] = $pessoa_id;
        $_SESSION['cliente_id'] = $pessoa_id; // Compatibilidade
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['tipo'] = $tipo;
        $_SESSION['login_time'] = time();
    }

    // Novo: Logout do usuário
    public static function logout() {
        self::start();
        session_unset();
        session_destroy();
    }

    // Novo: Obter ID do usuário logado
    public static function getUserId() {
        self::start();
        return $_SESSION['pessoa_id'] ?? $_SESSION['cliente_id'] ?? null;
    }

    // Novo: Obter nome do usuário logado
    public static function getUserName() {
        self::start();
        return $_SESSION['nome'] ?? null;
    }

    // Novo: Obter email do usuário logado
    public static function getUserEmail() {
        self::start();
        return $_SESSION['email'] ?? null;
    }

    // Novo: Obter tipo do usuário (cliente, prestador, ambos)
    public static function getUserType() {
        self::start();
        return $_SESSION['tipo'] ?? null;
    }

    // Novo: Verificar se é prestador
    public static function isPrestador() {
        $tipo = self::getUserType();
        return $tipo === 'prestador' || $tipo === 'ambos';
    }

    // Novo: Verificar se é cliente
    public static function isCliente() {
        $tipo = self::getUserType();
        return $tipo === 'cliente' || $tipo === 'ambos';
    }

    // Atualizar método existente para usar pessoa_id
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['pessoa_id']);
    }

    // Atualizar para usar o caminho correto
    public static function requireClientLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /chamaservico/login');
            exit();
        }
    }

    // CORRIGIDO: Novo método para verificar se pode acessar área do prestador
    public static function requirePrestadorLogin() {
        self::requireClientLogin();
        if (!self::isPrestador()) {
            header('Location: /chamaservico/acesso-negado');
            exit();
        }
    }

    // Novo: Verificar se pode acessar área do prestador (método alternativo)
    public static function requirePrestadorAccess() {
        self::requireClientLogin();
        if (!self::isPrestador()) {
            header('Location: /chamaservico/acesso-negado');
            exit();
        }
    }

    // MELHORIA: Método para regenerar ID da sessão
    public static function regenerateId() {
        self::start();
        session_regenerate_id(true);
    }

    // MELHORIA: Limpeza de sessões antigas
    public static function cleanup() {
        self::start();
        // Remover flash messages antigas
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $key => $flash) {
                if (isset($flash['timestamp']) && time() - $flash['timestamp'] > 300) {
                    unset($_SESSION['flash'][$key]);
                }
            }
        }
    }

    // MELHORIA: Definir mensagem flash com timestamp
    public static function setFlash($key, $message, $type = 'info') {
        self::start();
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type,
            'timestamp' => time()
        ];
    }

    // Novo: Obter e remover mensagem flash
    public static function getFlash($key) {
        self::start();
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }

    // Novo: Verificar se há mensagens flash
    public static function hasFlash($key = null) {
        self::start();
        if ($key) {
            return isset($_SESSION['flash'][$key]);
        }
        return !empty($_SESSION['flash']);
    }

    // Novo: Definir dados temporários na sessão
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    // Novo: Obter dados da sessão
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    // Novo: Verificar timeout da sessão (30 minutos)
    public static function checkTimeout($timeout = 1800) {
        self::start();
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $timeout) {
                self::logout();
                return false;
            }
            $_SESSION['login_time'] = time(); // Renovar tempo
        }
        return true;
    }

    // Novo: Token CSRF para formulários
    public static function generateCSRFToken() {
        self::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Novo: Verificar token CSRF
    public static function verifyCSRFToken($token) {
        self::start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Novo: Limpar dados da sessão
    public static function clear() {
        self::start();
        $_SESSION = [];
    }

    // Novo: Mensagem de sucesso ao fazer login
    public static function setLoginSuccessMessage() {
        self::setFlash('success', 'Login realizado com sucesso!', 'success');
    }

    // Novo: Mensagem de sucesso ao fazer logout
    public static function setLogoutSuccessMessage() {
        self::setFlash('success', 'Você saiu do sistema com sucesso!', 'success');
    }

    public static function requireAdminLogin() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /chamaservico/admin/login');
            exit;
        }
    }
}
?>
