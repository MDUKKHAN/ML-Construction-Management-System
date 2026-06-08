<?php
/**
 * Main Application Router
 */

require_once 'config/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$page = basename($page);
$action = basename($action);

$routes = [
    'login' => ['controller' => 'AuthController', 'action' => 'login', 'auth' => false],
    'register' => ['controller' => 'AuthController', 'action' => 'register', 'auth' => false],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout', 'auth' => true],
    'forgot-password' => ['controller' => 'AuthController', 'action' => 'forgotPassword', 'auth' => false],
    'reset-password' => ['controller' => 'AuthController', 'action' => 'resetPassword', 'auth' => false],
    'change-password' => ['controller' => 'AuthController', 'action' => 'changePassword', 'auth' => true],

    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'auth' => true],

    'employees' => ['controller' => 'EmployeeController', 'action' => 'index', 'auth' => true],
    'employee-add' => ['controller' => 'EmployeeController', 'action' => 'add', 'auth' => true],
    'employee-edit' => ['controller' => 'EmployeeController', 'action' => 'edit', 'auth' => true],
    'employee-view' => ['controller' => 'EmployeeController', 'action' => 'view', 'auth' => true],
    'employee-delete' => ['controller' => 'EmployeeController', 'action' => 'delete', 'auth' => true],

    'attendance' => ['controller' => 'AttendanceController', 'action' => 'index', 'auth' => true],
    'attendance-mark' => ['controller' => 'AttendanceController', 'action' => 'mark', 'auth' => true],
    'attendance-report' => ['controller' => 'AttendanceController', 'action' => 'report', 'auth' => true],
    'attendance-history' => ['controller' => 'AttendanceController', 'action' => 'history', 'auth' => true],

    'sites' => ['controller' => 'SiteController', 'action' => 'index', 'auth' => true],
    'site-add' => ['controller' => 'SiteController', 'action' => 'add', 'auth' => true],
    'site-edit' => ['controller' => 'SiteController', 'action' => 'edit', 'auth' => true],
    'site-view' => ['controller' => 'SiteController', 'action' => 'view', 'auth' => true],
    'site-delete' => ['controller' => 'SiteController', 'action' => 'delete', 'auth' => true],
    'site-gallery' => ['controller' => 'SiteController', 'action' => 'gallery', 'auth' => true],

    'payroll' => ['controller' => 'PayrollController', 'action' => 'index', 'auth' => true],
    'payroll-generate' => ['controller' => 'PayrollController', 'action' => 'generate', 'auth' => true],
    'payroll-slip' => ['controller' => 'PayrollController', 'action' => 'slip', 'auth' => true],
    'payroll-report' => ['controller' => 'PayrollController', 'action' => 'report', 'auth' => true],

    'inventory' => ['controller' => 'InventoryController', 'action' => 'index', 'auth' => true],
    'inventory-add' => ['controller' => 'InventoryController', 'action' => 'add', 'auth' => true],
    'inventory-edit' => ['controller' => 'InventoryController', 'action' => 'edit', 'auth' => true],
    'inventory-delete' => ['controller' => 'InventoryController', 'action' => 'delete', 'auth' => true],

    'files' => ['controller' => 'FileController', 'action' => 'index', 'auth' => true],
    'file-upload' => ['controller' => 'FileController', 'action' => 'upload', 'auth' => true],
    'file-download' => ['controller' => 'FileController', 'action' => 'download', 'auth' => true],
    'file-delete' => ['controller' => 'FileController', 'action' => 'delete', 'auth' => true],

    'account' => ['controller' => 'AccountController', 'action' => 'profile', 'auth' => true],
    'account-edit' => ['controller' => 'AccountController', 'action' => 'edit', 'auth' => true],
    'account-settings' => ['controller' => 'AccountController', 'action' => 'settings', 'auth' => true],

    'reports' => ['controller' => 'ReportController', 'action' => 'index', 'auth' => true],
    'report-employee' => ['controller' => 'ReportController', 'action' => 'employeeReport', 'auth' => true],
    'report-attendance' => ['controller' => 'ReportController', 'action' => 'attendanceReport', 'auth' => true],
    'report-payroll' => ['controller' => 'ReportController', 'action' => 'payrollReport', 'auth' => true],
    'report-site' => ['controller' => 'ReportController', 'action' => 'siteReport', 'auth' => true],
    'report-inventory' => ['controller' => 'ReportController', 'action' => 'inventoryReport', 'auth' => true],
];

if (!array_key_exists($page, $routes)) {
    $page = 'login';
}

$route = $routes[$page];

if ($route['auth'] && session_status() === PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }
}

$controllerFile = CONTROLLERS_PATH . '/' . $route['controller'] . '.php';

if (!file_exists($controllerFile)) {
    die('Controller not found: ' . $route['controller']);
}

require_once $controllerFile;

$controllerClass = str_replace('.php', '', $route['controller']);
$controller = new $controllerClass();

$method = $route['action'];

if (!method_exists($controller, $method)) {
    die('Method not found: ' . $method);
}

$controller->$method();

?>
