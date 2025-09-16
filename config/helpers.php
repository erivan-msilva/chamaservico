<?php
if (!function_exists('url')) {
    function url($path = '') {
        // Remover barras extras e duplicações
        $path = trim($path, '/');
        
        // Se o path já contém admin/ no início, não duplicar
        if (strpos($path, 'admin/') === 0) {
            $cleanPath = $path;
        } else {
            $cleanPath = $path;
        }
        
        // Construir URL final
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'https://chamaservico.tds104-senac.online';
        $finalUrl = rtrim($baseUrl, '/') . '/' . $cleanPath;
        
        // Log para debug
        error_log("url() - Input: '$path' -> Output: '$finalUrl'");
        
        return $finalUrl;
    }
}

if (!function_exists('adminUrl')) {
    function adminUrl($path = '') {
        // Função específica para URLs admin que garante o prefixo correto
        $path = trim($path, '/');
        return url('admin/' . $path);
    }
}
?>
