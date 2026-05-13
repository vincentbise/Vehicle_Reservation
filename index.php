<?php
// ════════════════════════════════════════════════════════════════
//  USeP Vehicle Reservation System — Front Controller
//  Entry point for all HTTP requests.
// ════════════════════════════════════════════════════════════════

// Bootstrap
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';

// Autoload models & controllers
spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/'      . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Start session
session_name(SESSION_NAME);
session_start();

// Dispatch request
$router = new Router();
$router->dispatch();
