<?php
class SecurityHelper
{
    /**
     * Aplicar headers de segurança
     */
    public static function applySecurityHeaders()
    {
        if (AMBIENTE === 'producao') {
            // Headers de segurança para produção
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // Forçar HTTPS
            if (defined('FORCE_HTTPS') && constant('FORCE_HTTPS') && !isset($_SERVER['HTTPS'])) {
                $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header("Location: $redirectURL", true, 301);
                exit;
            }
        }
    }
    
    /**
     * Configurar cookies seguros
     */
    public static function configureSecureCookies()
    {
        if (defined('SECURE_COOKIES') && constant('SECURE_COOKIES')) {
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
        }
    }
    
    /**
     * Validar e sanitizar entrada
     */
    public static function sanitizeInput($input, $type = 'string')
    {
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Verificar rate limiting básico
     */
    public static function checkRateLimit($action, $limit = 5, $window = 300)
    {
        if (AMBIENTE === 'desenvolvimento') {
            return true; // Sem limite em desenvolvimento
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$action}_{$ip}";
        
        // Implementação simples com sessão (melhorar com Redis/Memcached)
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'start' => time()];
            return true;
        }
        
        $data = $_SESSION[$key];
        
        // Resetar janela se passou o tempo
        if ((time() - $data['start']) > $window) {
            $_SESSION[$key] = ['count' => 1, 'start' => time()];
            return true;
        }
        
        // Verificar limite
        if ($data['count'] >= $limit) {
            return false;
        }
        
        // Incrementar contador
        $_SESSION[$key]['count']++;
        return true;
    }
}

// Aplicar configurações de segurança automaticamente
SecurityHelper::applySecurityHeaders();
SecurityHelper::configureSecureCookies();
?>
