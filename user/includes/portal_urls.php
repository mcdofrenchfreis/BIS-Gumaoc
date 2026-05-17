<?php
/**
 * Absolute web paths for user portal links (works from any page in the project).
 */
if (!function_exists('user_portal_base_path')) {
    function user_portal_base_path(): string
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }

        $userDirFs = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
        $docRootFs = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        $userDir = str_replace('\\', '/', $userDirFs);
        $docRoot = str_replace('\\', '/', rtrim((string) $docRootFs, '/'));

        if ($docRoot !== '' && stripos($userDir, $docRoot) === 0) {
            $base = '/' . trim(substr($userDir, strlen($docRoot)), '/') . '/';
            return $base;
        }

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('#^(.*?)/user(?:/|$)#', $script, $m)) {
            $base = $m[1] . '/user/';
            return $base;
        }

        $base = rtrim(str_replace('\\', '/', dirname($script)), '/') . '/user/';
        return $base;
    }
}

if (!function_exists('user_portal_url')) {
    function user_portal_url(string $path = 'dashboard.php'): string
    {
        return user_portal_base_path() . ltrim($path, '/');
    }
}

if (!function_exists('user_portal_home_url')) {
    function user_portal_home_url(): string
    {
        return user_portal_url('dashboard.php');
    }
}
