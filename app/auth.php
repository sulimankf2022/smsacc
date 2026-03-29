<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function login(string $username, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function logout(): void {
    startSession();
    $_SESSION = [];
    session_destroy();
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'tenant_id' => $_SESSION['tenant_id'] ?? null,
    ];
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    $user = currentUser();
    if ($user['role'] !== $role && $user['role'] !== 'super_admin') {
        header('Location: ' . BASE_URL . '/index.php?page=dashboard');
        exit;
    }
}

function isSuperAdmin(): bool {
    $user = currentUser();
    return $user && $user['role'] === 'super_admin';
}
