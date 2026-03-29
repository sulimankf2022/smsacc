<?php
require_once __DIR__ . '/../../helpers.php';
require_once __DIR__ . '/../../auth.php';
$currentUser = currentUser();
$currentPage = $_GET['page'] ?? 'dashboard';
$businessName = '';
if ($currentUser && $currentUser['tenant_id']) {
    $db = getDB();
    $businessName = getSettingValue($db, $currentUser['tenant_id'], 'business_name', '');
}
$appTitle = $businessName ?: APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appTitle); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar d-flex flex-column flex-shrink-0 p-0">
        <a href="<?php echo BASE_URL; ?>/index.php?page=dashboard" class="sidebar-brand d-flex align-items-center p-3 text-decoration-none">
            <i class="bi bi-reception-4 me-2 fs-4"></i>
            <span class="fw-bold fs-5"><?php echo htmlspecialchars($appTitle); ?></span>
        </a>
        <hr class="sidebar-divider">
        <ul class="nav nav-pills flex-column mb-auto px-2">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=dashboard" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=providers" class="nav-link <?php echo $currentPage === 'providers' ? 'active' : ''; ?>">
                    <i class="bi bi-building me-2"></i> Providers
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=clients" class="nav-link <?php echo $currentPage === 'clients' ? 'active' : ''; ?>">
                    <i class="bi bi-people me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=expenses" class="nav-link <?php echo $currentPage === 'expenses' ? 'active' : ''; ?>">
                    <i class="bi bi-receipt me-2"></i> Expenses
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=employees" class="nav-link <?php echo $currentPage === 'employees' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge me-2"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=settings" class="nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </li>
            <?php if ($currentUser && $currentUser['role'] === 'super_admin'): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?page=admin" class="nav-link <?php echo $currentPage === 'admin' ? 'active' : ''; ?>">
                    <i class="bi bi-shield-lock me-2"></i> Admin
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <hr class="sidebar-divider">
        <div class="px-3 pb-3">
            <div class="text-muted small mb-1"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($currentUser['username'] ?? ''); ?></div>
            <div class="text-muted small mb-2"><span class="badge bg-secondary"><?php echo htmlspecialchars($currentUser['role'] ?? ''); ?></span></div>
            <a href="<?php echo BASE_URL; ?>/index.php?page=logout" class="btn btn-sm btn-outline-danger w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </nav>
    <!-- Page Content -->
    <div id="page-content-wrapper" class="flex-grow-1">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-2">
            <button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-brand mb-0 h6 text-muted"><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></span>
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo date('M j, Y'); ?></span>
            </div>
        </nav>
        <div class="container-fluid py-4 px-4">
