<?php
declare(strict_types=1);

// Bootstrap the app
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/database.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/middleware.php';
require_once __DIR__ . '/../app/router.php';

// Start session
startSession();

// Get route
$page = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['page'] ?? 'dashboard'));
$action = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['action'] ?? ''));

// Handle logout directly
if ($page === 'logout') {
    logout();
    flash('success', 'You have been logged out.');
    redirect(BASE_URL . '/index.php?page=login');
}

// Route to controller
route($page, $action);
