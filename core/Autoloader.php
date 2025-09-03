<?php
class Autoloader
{
    private static $namespaces = [];
    private static $classMap = [];
    
    /**
     * Registrar o autoloader
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'autoload']);
        
        // Configurar namespaces e diretórios
        self::addNamespace('App\\Controllers\\', __DIR__ . '/../controllers/');
        self::addNamespace('App\\Models\\', __DIR__ . '/../models/');
        self::addNamespace('App\\Core\\', __DIR__ . '/../core/');
        self::addNamespace('App\\Config\\', __DIR__ . '/../config/');
        
        // Mapeamento direto para classes sem namespace (compatibilidade)
        self::mapLegacyClasses();
    }
    
    /**
     * Adicionar namespace
     */
    public static function addNamespace($namespace, $directory)
    {
        $namespace = trim($namespace, '\\') . '\\';
        $directory = rtrim($directory, '/') . '/';
        
        if (!isset(self::$namespaces[$namespace])) {
            self::$namespaces[$namespace] = [];
        }
        
        self::$namespaces[$namespace][] = $directory;
    }
    
    /**
     * Mapear classes legadas (sem namespace)
     */
    private static function mapLegacyClasses()
    {
        $legacyMappings = [
            // Controllers
            'HomeController' => __DIR__ . '/../controllers/HomeController.php',
            'AuthController' => __DIR__ . '/../controllers/AuthController.php',
            'SolicitacaoController' => __DIR__ . '/../controllers/SolicitacaoController.php',
            'ClientePerfilController' => __DIR__ . '/../controllers/ClientePerfilController.php',
            'PrestadorController' => __DIR__ . '/../controllers/PrestadorController.php',
            'PropostaController' => __DIR__ . '/../controllers/PropostaController.php',
            'ClienteDashboardController' => __DIR__ . '/../controllers/ClienteDashboardController.php',
            'ClientePropostaController' => __DIR__ . '/../controllers/ClientePropostaController.php',
            'ClienteServicoController' => __DIR__ . '/../controllers/ClienteServicoController.php',
            'PrestadorPerfilController' => __DIR__ . '/../controllers/PrestadorPerfilController.php',
            'PerfilController' => __DIR__ . '/../controllers/PerfilController.php',
            
            // Models
            'Database' => __DIR__ . '/../core/Database.php',
            'Session' => __DIR__ . '/../config/session.php',
            'Pessoa' => __DIR__ . '/../models/Pessoa.php',
            'SolicitacaoServico' => __DIR__ . '/../models/SolicitacaoServico.php',
            'Proposta' => __DIR__ . '/../models/Proposta.php',
            'Avaliacao' => __DIR__ . '/../models/Avaliacao.php',
            'Auth' => __DIR__ . '/../models/Auth.php',
            'TipoServico' => __DIR__ . '/../models/TipoServico.php',
            
            // Controllers opcionais
            'NotificacaoController' => __DIR__ . '/../controllers/NotificacaoController.php',
            'NegociacaoController' => __DIR__ . '/../controllers/NegociacaoController.php',
            'OrdemServicoController' => __DIR__ . '/../controllers/OrdemServicoController.php',
            'AdminController' => __DIR__ . '/../controllers/AdminController.php',
        ];
        
        foreach ($legacyMappings as $class => $file) {
            if (file_exists($file)) {
                self::$classMap[$class] = $file;
            }
        }
    }
    
    /**
     * Carregar classe automaticamente
     */
    public static function autoload($className)
    {
        // 1. Verificar mapeamento direto (classes legadas)
        if (isset(self::$classMap[$className])) {
            require_once self::$classMap[$className];
            return true;
        }
        
        // 2. Tentar carregar por namespace
        foreach (self::$namespaces as $namespace => $directories) {
            if (strpos($className, $namespace) === 0) {
                $relativeClass = substr($className, strlen($namespace));
                $relativeClass = str_replace('\\', '/', $relativeClass);
                
                foreach ($directories as $directory) {
                    $file = $directory . $relativeClass . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
        }
        
        // 3. Busca global em diretórios comuns (fallback)
        $searchDirs = [
            __DIR__ . '/../controllers/',
            __DIR__ . '/../models/',
            __DIR__ . '/../core/',
            __DIR__ . '/../config/',
        ];
        
        foreach ($searchDirs as $dir) {
            $file = $dir . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Carregar dependências específicas
     */
    public static function loadDependencies()
    {
        // Carregar dependências principais
        $dependencies = [
            __DIR__ . '/../config/config.php',
            __DIR__ . '/../config/session.php',
            __DIR__ . '/../core/Database.php',
        ];
        
        foreach ($dependencies as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
