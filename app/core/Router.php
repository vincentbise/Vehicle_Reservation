<?php
// ─── Router ──────────────────────────────────────────────────────────────────
class Router {
    // Route map: 'controller/action' => [ControllerClass, method]
    private array $routes = [
        // Auth
        ''                         => ['AuthController',        'login'],
        'login'                    => ['AuthController',        'login'],
        'auth/login'               => ['AuthController',        'login'],
        'auth/logout'              => ['AuthController',        'logout'],
        'auth/do_login'            => ['AuthController',        'doLogin'],

        // Admin
        'admin/dashboard'          => ['DashboardController',   'adminDashboard'],
        'admin/accounts'           => ['UserController',        'index'],
        'admin/accounts/create'    => ['UserController',        'create'],
        'admin/accounts/store'     => ['UserController',        'store'],
        'admin/accounts/edit'      => ['UserController',        'edit'],
        'admin/accounts/update'    => ['UserController',        'update'],
        'admin/accounts/toggle'    => ['UserController',        'toggle'],
        'admin/vehicles'           => ['VehicleController',     'index'],
        'admin/vehicles/create'    => ['VehicleController',     'create'],
        'admin/vehicles/store'     => ['VehicleController',     'store'],
        'admin/vehicles/edit'      => ['VehicleController',     'edit'],
        'admin/vehicles/update'    => ['VehicleController',     'update'],
        'admin/reservations'       => ['ReservationController', 'adminIndex'],
        'admin/reservations/view'  => ['ReservationController', 'adminView'],
        'admin/reservations/assign'=> ['ReservationController', 'assign'],
        'admin/reports'            => ['ReportController',      'index'],

        // Requester
        'requester/dashboard'      => ['DashboardController',   'requesterDashboard'],
        'requester/new'            => ['ReservationController', 'newForm'],
        'requester/store'          => ['ReservationController', 'store'],
        'requester/my_requests'    => ['ReservationController', 'myRequests'],
        'requester/cancel'         => ['ReservationController', 'cancel'],

        // Unit Head / ASD Coordinator approval
        'approvals'                => ['ReservationController', 'pendingApprovals'],
        'approvals/decide'         => ['ReservationController', 'decide'],

        // Driver
        'driver/dashboard'         => ['DashboardController',   'driverDashboard'],
        'driver/trips'             => ['DriverController',      'myTrips'],
        'driver/dispatch'          => ['DriverController',      'dispatch'],
        'driver/complete'          => ['DriverController',      'complete'],

        // ── AJAX API Endpoints ──────────────────────────────────────────
        'api/auth/login'           => ['AuthController',        'doLogin'],
        'api/accounts/store'       => ['UserController',        'store'],
        'api/accounts/update'      => ['UserController',        'update'],
        'api/accounts/toggle'      => ['UserController',        'toggle'],
        'api/vehicles/store'       => ['VehicleController',     'store'],
        'api/vehicles/update'      => ['VehicleController',     'update'],
        'api/reservations/store'   => ['ReservationController', 'store'],
        'api/reservations/cancel'  => ['ReservationController', 'cancel'],
        'api/reservations/assign'  => ['ReservationController', 'assign'],
        'api/approvals/decide'     => ['ReservationController', 'decide'],
        'api/driver/dispatch'      => ['DriverController',      'dispatch'],
        'api/driver/complete'      => ['DriverController',      'complete'],
    ];

    public function dispatch(): void {
        // Detect URL using REQUEST_URI to be more robust than $_GET['url']
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir    = str_replace(basename($scriptName), '', $scriptName);
        
        // Remove baseDir from requestUri and strip query string
        $url = str_replace($baseDir, '', $requestUri);
        $url = parse_url($url, PHP_URL_PATH);
        $url = trim($url ?? '', '/');

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        // Allow POST override via _method field
        if ($method === 'post' && isset($_POST['_method'])) {
            $method = strtolower($_POST['_method']);
        }

        if (!array_key_exists($url, $this->routes)) {
            http_response_code(404);
            // For API routes, return JSON 404
            if (str_starts_with($url, 'api/')) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Endpoint not found.']);
                return;
            }
            include VIEW_PATH . '/errors/404.php';
            return;
        }

        [$controllerName, $action] = $this->routes[$url];

        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            http_response_code(500);
            die("Controller not found: {$controllerName}");
        }

        require_once $controllerFile;

        // Load model files for every request
        foreach (glob(APP_PATH . '/models/*.php') as $model) {
            require_once $model;
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            http_response_code(500);
            die("Action not found: {$controllerName}::{$action}");
        }

        $controller->$action();
    }
}
