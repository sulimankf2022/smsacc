<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_dashboard_index(): void {
    $db = getDB();
    $tenantId = getTenantId();

    // Summary stats
    $stats = [];

    // Provider receivables outstanding
    $stmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN type='receivable' THEN base_amount ELSE -base_amount END),0) as total FROM provider_transactions WHERE tenant_id=?");
    $stmt->execute([$tenantId]);
    $stats['provider_balance'] = (float)$stmt->fetchColumn();

    // Client receivables outstanding
    $stmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN type='invoice' THEN base_amount ELSE -base_amount END),0) as total FROM client_transactions WHERE tenant_id=?");
    $stmt->execute([$tenantId]);
    $stats['client_balance'] = (float)$stmt->fetchColumn();

    // Total expenses
    $stmt = $db->prepare("SELECT COALESCE(SUM(base_amount),0) as total FROM expenses WHERE tenant_id=?");
    $stmt->execute([$tenantId]);
    $stats['total_expenses'] = (float)$stmt->fetchColumn();

    // Total salaries paid
    $stmt = $db->prepare("SELECT COALESCE(SUM(base_amount),0) as total FROM payroll_records WHERE tenant_id=? AND type='salary'");
    $stmt->execute([$tenantId]);
    $stats['total_salaries'] = (float)$stmt->fetchColumn();

    // Recent provider transactions
    $stmt = $db->prepare("SELECT pt.*, p.name as provider_name FROM provider_transactions pt JOIN providers p ON p.id=pt.provider_id WHERE pt.tenant_id=? ORDER BY pt.transaction_date DESC LIMIT 5");
    $stmt->execute([$tenantId]);
    $recentProviderTx = $stmt->fetchAll();

    // Recent client transactions
    $stmt = $db->prepare("SELECT ct.*, c.name as client_name FROM client_transactions ct JOIN clients c ON c.id=ct.client_id WHERE ct.tenant_id=? ORDER BY ct.transaction_date DESC LIMIT 5");
    $stmt->execute([$tenantId]);
    $recentClientTx = $stmt->fetchAll();

    // Overdue items (due_date < today and not fully paid - simplified)
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT 'provider' as source, pt.*, p.name as party_name FROM provider_transactions pt JOIN providers p ON p.id=pt.provider_id WHERE pt.tenant_id=? AND pt.due_date < ? AND pt.due_date IS NOT NULL AND pt.type='receivable' ORDER BY pt.due_date LIMIT 5");
    $stmt->execute([$tenantId, $today]);
    $overdueItems = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT 'client' as source, ct.*, c.name as party_name FROM client_transactions ct JOIN clients c ON c.id=ct.client_id WHERE ct.tenant_id=? AND ct.due_date < ? AND ct.due_date IS NOT NULL AND ct.type='invoice' ORDER BY ct.due_date LIMIT 5");
    $stmt->execute([$tenantId, $today]);
    $overdueItems = array_merge($overdueItems, $stmt->fetchAll());

    require __DIR__ . '/../views/dashboard/index.php';
}
