<?php
/**
 * Router for PHP built-in server (php -S).
 * Replicates the .htaccess RewriteRules so CI can serve the app without Apache/nginx.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    include __DIR__ . '/../home/index.php';
} elseif ($uri === '/about') {
    include __DIR__ . '/../about/about.html';
} elseif (preg_match('/^\/country\/([A-Za-z]{2})$/i', $uri, $m)) {
    $_GET['countryCode'] = strtoupper($m[1]);
    include __DIR__ . '/../country/country.php';
} elseif (preg_match('/^\/([A-Za-z]{2}[A-Za-z0-9]{3})$/', $uri, $m)) {
    $_GET['unlocode'] = strtoupper($m[1]);
    include __DIR__ . '/../details/unlocode.php';
} else {
    // Let PHP serve static files (CSS, JS, images, etc.) as-is
    return false;
}
