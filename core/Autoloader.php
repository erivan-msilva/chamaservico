<?php
class Autoloader
{
    private static $registered = false;
    private static $dependencies = [
        'core/Database.php',
        'config/session.php'
    ];

    /**
     * Registrar o autoloader
     */
    public static function register()
    {
        if (self::$registered) {
            return;
        }

        spl_autoload_register([__CLASS__, 'autoload']);
        self::$registered = true;
    }

    /**
     * Carregar classe automaticamente
     */
    public static function autoload($className)
    {
        $paths = [
            'controllers/' . $className . '.php',
            'models/' . $className . '.php',
            'core/' . $className . '.php',
            'config/' . $className . '.php'
        ];

        foreach ($paths as $path) {
            $fullPath = __DIR__ . '/../' . $path;
            if (file_exists($fullPath)) {
                require_once $fullPath;
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
        foreach (self::$dependencies as $dependency) {
            $fullPath = __DIR__ . '/../' . $dependency;
            if (file_exists($fullPath)) {
                require_once $fullPath;
            }
        }
    }

    public static function isRegistered()
    {
        return self::$registered;
    }
}
?>
        