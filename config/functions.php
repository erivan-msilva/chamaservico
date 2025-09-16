<?php

if (!function_exists('url')) {
    function url($path = '') {
        // Base URL para produção
        $baseUrl = 'https://chamaservico.tds104-senac.online';
        
        // Remove slash inicial se existir
        $path = ltrim($path, '/');
        
        // Garante que sempre termine com /
        $baseUrl = rtrim($baseUrl, '/');
        
        return $baseUrl . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset($path = '') {
        return url('assets/' . ltrim($path, '/'));
    }
}
