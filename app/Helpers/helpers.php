<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('getPageVisits')) {
    /**
     * Obtener el número de visitas de una página
     */
    function getPageVisits(string $path): int
    {
        return Cache::get("page_visits:{$path}", 0);
    }
}

if (!function_exists('getAllPageVisits')) {
    /**
     * Obtener todas las visitas de todas las páginas
     */
    function getAllPageVisits(): array
    {
        $keys = Cache::get('page_visits:*', []);
        $visits = [];
        
        foreach ($keys as $key) {
            $path = str_replace('page_visits:', '', $key);
            $visits[$path] = Cache::get($key, 0);
        }
        
        return $visits;
    }
}
