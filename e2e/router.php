<?php
/**
 * Router for PHP built-in server (php -S).
 * Replicates the .htaccess RewriteRules so CI can serve the app without Apache/nginx.
 *
 * IMPORTANT: includes must happen at global scope (not inside a function), so that
 * variables set in secrets.php (e.g. $db_host) land in global scope where
 * setupDb() can find them via "global $db_host".
 *
 * We chdir() into each script's own directory before including it, so that
 * relative paths like include '../database.php' resolve correctly.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = realpath(__DIR__ . '/..');

if ($uri === '/' || $uri === '') {
    chdir("$root/home");
    include "$root/home/index.php";
} elseif ($uri === '/about') {
    chdir("$root/about");
    include "$root/about/about.html";
} elseif (preg_match('/^\/country\/([A-Za-z]{2})$/i', $uri, $m)) {
    $_GET['countryCode'] = strtoupper($m[1]);
    chdir("$root/country");
    include "$root/country/country.php";
} elseif (preg_match('/^\/([A-Za-z]{2}[A-Za-z0-9]{3})$/', $uri, $m)) {
    $_GET['unlocode'] = strtoupper($m[1]);
    chdir("$root/details");
    include "$root/details/unlocode.php";
} else {
    // Let PHP serve static files (CSS, images, etc.) as-is
    return false;
}
