<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/middleware.php';

function route(string $page, string $action = ''): void {
    $page = preg_replace('/[^a-z0-9_]/', '', strtolower($page));
    $action = preg_replace('/[^a-z0-9_]/', '', strtolower($action));

    $publicPages = ['login'];

    if (!in_array($page, $publicPages)) {
        authMiddleware();
        if ($page !== 'admin') {
            tenantMiddleware();
        }
    }

    $controllerMap = [
        'login'     => 'AuthController',
        'logout'    => 'AuthController',
        'dashboard' => 'DashboardController',
        'providers' => 'ProviderController',
        'clients'   => 'ClientController',
        'expenses'  => 'ExpenseController',
        'employees' => 'EmployeeController',
        'settings'  => 'SettingsController',
        'admin'     => 'AdminController',
    ];

    $controller = $controllerMap[$page] ?? 'DashboardController';
    $controllerFile = __DIR__ . '/controllers/' . $controller . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $action = $action ?: 'index';
        if (function_exists('controller_' . $page . '_' . $action)) {
            call_user_func('controller_' . $page . '_' . $action);
        } else {
            call_user_func('controller_' . $page . '_index');
        }
    } else {
        redirect(BASE_URL . '/index.php?page=dashboard');
    }
}
