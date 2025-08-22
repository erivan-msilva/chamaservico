<?php
class Logger {
    private static $logDir;
    
    public static function init() {
        self::$logDir = __DIR__ . '/../logs/';
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    public static function error($message, $context = []) {
        self::writeLog('error', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::writeLog('info', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            self::writeLog('debug', $message, $context);
        }
    }
    
    private static function writeLog($level, $message, $context) {
        if (!self::$logDir) self::init();
        
        $logFile = self::$logDir . $level . '_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
