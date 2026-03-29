<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_admin_index(): void {
    requireRole('super_admin');
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=admin');
        }

        $action = $_POST['form_action'] ?? '';

        if ($action === 'add_tenant') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($slug));

            if (empty($name) || empty($slug)) {
                flash('error', 'Tenant name and slug are required.');
                redirect(BASE_URL . '/index.php?page=admin');
            }

            try {
                $stmt = $db->prepare('INSERT INTO tenants (name, slug) VALUES (?,?)');
                $stmt->execute([$name, $slug]);
                // Add default currency rates
                $tenantId = (int)$db->lastInsertId();
                $stmt = $db->prepare('INSERT INTO currency_rates (tenant_id, currency, rate_to_usd) VALUES (?,?,?)');
                $stmt->execute([$tenantId, 'EUR', 1.08]);
                $stmt->execute([$tenantId, 'ILS', 0.28]);
                flash('success', 'Tenant created successfully.');
            } catch (Exception $e) {
                flash('error', 'Slug already exists or other error: ' . $e->getMessage());
            }
            redirect(BASE_URL . '/index.php?page=admin');
        }

        if ($action === 'add_user') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'staff';
            $tenantId = (int)($_POST['tenant_id'] ?? 0) ?: null;

            $validRoles = ['super_admin', 'owner', 'manager', 'staff'];
            if (!in_array($role, $validRoles)) $role = 'staff';

            if (empty($username) || empty($email) || strlen($password) < 6) {
                flash('error', 'Username, email and password (min 6 chars) are required.');
                redirect(BASE_URL . '/index.php?page=admin');
            }

            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (tenant_id, username, email, password_hash, role) VALUES (?,?,?,?,?)');
                $stmt->execute([$tenantId, $username, $email, $hash, $role]);
                flash('success', 'User created successfully.');
            } catch (Exception $e) {
                flash('error', 'Username or email already exists.');
            }
            redirect(BASE_URL . '/index.php?page=admin');
        }

        if ($action === 'toggle_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $stmt = $db->prepare('UPDATE users SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?');
            $stmt->execute([$userId]);
            flash('success', 'User status updated.');
            redirect(BASE_URL . '/index.php?page=admin');
        }

        if ($action === 'toggle_tenant') {
            $tenantId = (int)($_POST['tenant_id'] ?? 0);
            $stmt = $db->prepare('UPDATE tenants SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?');
            $stmt->execute([$tenantId]);
            flash('success', 'Tenant status updated.');
            redirect(BASE_URL . '/index.php?page=admin');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->query('SELECT * FROM tenants ORDER BY created_at DESC');
    $tenants = $stmt->fetchAll();

    $stmt = $db->query('SELECT u.*, t.name as tenant_name FROM users u LEFT JOIN tenants t ON t.id=u.tenant_id ORDER BY u.created_at DESC');
    $users = $stmt->fetchAll();

    require __DIR__ . '/../views/admin/index.php';
}
