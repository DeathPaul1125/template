<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Si no existe .env, crearlo desde .env.example con una APP_KEY válida
// antes de que Laravel intente bootear (necesita la clave para el encrypter)
(static function (): void {
    $envFile     = dirname(__DIR__) . '/.env';
    $envExample  = dirname(__DIR__) . '/.env.example';

    if (!file_exists($envFile) && file_exists($envExample)) {
        $key     = 'base64:' . base64_encode(random_bytes(32));
        $content = str_replace('APP_KEY=', 'APP_KEY=' . $key, file_get_contents($envExample));
        file_put_contents($envFile, $content);
    }
})();

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
