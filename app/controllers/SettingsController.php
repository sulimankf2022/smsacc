<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_settings_index(): void {
    requireLogin();
    $db = getDB();
    $tenantId = getTenantId();
    $user = currentUser();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=settings');
        }

        $action = $_POST['form_action'] ?? '';

        if ($action === 'currency_rates' && $tenantId) {
            foreach (['EUR', 'ILS'] as $currency) {
                $rate = (float)($_POST['rate_' . $currency] ?? 0);
                if ($rate > 0) {
                    $stmt = $db->prepare('INSERT INTO currency_rates (tenant_id, currency, rate_to_usd, updated_at) VALUES (?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(tenant_id, currency) DO UPDATE SET rate_to_usd=excluded.rate_to_usd, updated_at=CURRENT_TIMESTAMP');
                    $stmt->execute([$tenantId, $currency, $rate]);
                }
            }
            flash('success', 'Currency rates updated.');
            redirect(BASE_URL . '/index.php?page=settings');
        }

        if ($action === 'business_settings' && $tenantId) {
            $businessName = trim($_POST['business_name'] ?? '');
            if (!empty($businessName)) {
                $stmt = $db->prepare('INSERT INTO settings (tenant_id, setting_key, setting_value) VALUES (?,?,?) ON CONFLICT(tenant_id, setting_key) DO UPDATE SET setting_value=excluded.setting_value');
                $stmt->execute([$tenantId, 'business_name', $businessName]);
            }
            flash('success', 'Settings saved.');
            redirect(BASE_URL . '/index.php?page=settings');
        }

        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $stmt = $db->prepare('SELECT password_hash FROM users WHERE id=?');
            $stmt->execute([$user['id']]);
            $userRow = $stmt->fetch();

            if (!password_verify($currentPassword, $userRow['password_hash'])) {
                flash('error', 'Current password is incorrect.');
            } elseif (strlen($newPassword) < 6) {
                flash('error', 'New password must be at least 6 characters.');
            } elseif ($newPassword !== $confirmPassword) {
                flash('error', 'Passwords do not match.');
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare('UPDATE users SET password_hash=? WHERE id=?');
                $stmt->execute([$hash, $user['id']]);
                flash('success', 'Password changed successfully.');
            }
            redirect(BASE_URL . '/index.php?page=settings');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $currencyRates = [];
    if ($tenantId) {
        $stmt = $db->prepare('SELECT currency, rate_to_usd FROM currency_rates WHERE tenant_id=?');
        $stmt->execute([$tenantId]);
        foreach ($stmt->fetchAll() as $row) {
            $currencyRates[$row['currency']] = $row['rate_to_usd'];
        }
        $businessName = getSettingValue($db, $tenantId, 'business_name', '');
    } else {
        $businessName = '';
    }

    require __DIR__ . '/../views/settings/index.php';
}
