<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

function authMiddleware(): void {
    requireLogin();
}

function tenantMiddleware(): void {
    requireLogin();
    $user = currentUser();
    if (!isSuperAdmin() && empty($user['tenant_id'])) {
        flash('error', 'No tenant assigned to your account.');
        redirect(BASE_URL . '/index.php?page=login');
    }
}
