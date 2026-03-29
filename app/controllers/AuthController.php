<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

function controller_login_index(): void {
    if (isLoggedIn()) {
        redirect(BASE_URL . '/index.php?page=dashboard');
    }

    $error = getFlash('error');
    $success = getFlash('success');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Username and password are required.';
            } elseif (login($username, $password)) {
                redirect(BASE_URL . '/index.php?page=dashboard');
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }

    require __DIR__ . '/../views/auth/login.php';
}

function controller_logout_index(): void {
    logout();
    flash('success', 'You have been logged out.');
    redirect(BASE_URL . '/index.php?page=login');
}
