<?php
require_once __DIR__ . '/auth.php';

function csrf_token(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool {
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function formatMoney(float $amount, string $currency = 'USD'): string {
    $symbols = ['USD' => '$', 'EUR' => '€', 'ILS' => '₪'];
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount, 2);
}

function formatDate(?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    return $ts ? date('M j, Y', $ts) : $date;
}

function flash(string $key, string $message): void {
    startSession();
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string {
    startSession();
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function getTenantId(): ?int {
    startSession();
    return isset($_SESSION['tenant_id']) ? (int)$_SESSION['tenant_id'] : null;
}

function getCurrencyRates(PDO $db, int $tenantId): array {
    $stmt = $db->prepare('SELECT currency, rate_to_usd FROM currency_rates WHERE tenant_id = ?');
    $stmt->execute([$tenantId]);
    $rates = ['USD' => 1.0];
    foreach ($stmt->fetchAll() as $row) {
        $rates[$row['currency']] = (float)$row['rate_to_usd'];
    }
    return $rates;
}

function convertToBase(float $amount, float $rate): float {
    return round($amount * $rate, 4);
}

function getSettingValue(PDO $db, int $tenantId, string $key, string $default = ''): string {
    $stmt = $db->prepare('SELECT setting_value FROM settings WHERE tenant_id = ? AND setting_key = ?');
    $stmt->execute([$tenantId, $key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function alertHtml(?string $success, ?string $error): string {
    $html = '';
    if ($success) $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($success) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    if ($error) $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($error) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    return $html;
}
