<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/database.php';
require_once __DIR__ . '/app/helpers.php';

echo "=== SMS Finance Setup ===\n\n";

// Initialize DB
$db = getDB();
echo "✓ Database initialized\n";

// Check if already seeded
$stmt = $db->query("SELECT COUNT(*) FROM users");
if ((int)$stmt->fetchColumn() > 0) {
    echo "⚠ Database already has data. Delete database/smsacc.db to re-seed.\n";
    exit(0);
}

// Create super admin
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role) VALUES (NULL, 'admin', 'admin@smsfinance.local', ?, 'super_admin')")->execute([$adminHash]);
echo "✓ Super admin created (admin / admin123)\n";

// Create demo tenant
$db->prepare("INSERT INTO tenants (name, slug) VALUES ('Demo SMS Company', 'demo')")->execute();
$tenantId = (int)$db->lastInsertId();
echo "✓ Tenant created: Demo SMS Company (id=$tenantId)\n";

// Create demo owner
$ownerHash = password_hash('demo123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users (tenant_id, username, email, password_hash, role) VALUES (?, 'demo_owner', 'owner@demo.local', ?, 'owner')")->execute([$tenantId, $ownerHash]);
echo "✓ Demo owner created (demo_owner / demo123)\n";

// Currency rates
$db->prepare("INSERT INTO currency_rates (tenant_id, currency, rate_to_usd) VALUES (?, 'EUR', 1.08)")->execute([$tenantId]);
$db->prepare("INSERT INTO currency_rates (tenant_id, currency, rate_to_usd) VALUES (?, 'ILS', 0.28)")->execute([$tenantId]);
echo "✓ Currency rates set: EUR=1.08, ILS=0.28\n";

// Business settings
$db->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value) VALUES (?, 'business_name', 'Demo SMS Company')")->execute([$tenantId]);

// Providers
$db->prepare("INSERT INTO providers (tenant_id, name, contact_name, email, phone, notes) VALUES (?, 'Telecom Globals', 'John Smith', 'john@telecomglobals.com', '+1-555-0101', 'Main SMS route provider for Europe')")->execute([$tenantId]);
$provider1 = (int)$db->lastInsertId();

$db->prepare("INSERT INTO providers (tenant_id, name, contact_name, email, phone, notes) VALUES (?, 'SMS Gateway Ltd', 'Maria Chen', 'maria@smsgateway.io', '+44-20-7946-0958', 'Backup provider, competitive rates')")->execute([$tenantId]);
$provider2 = (int)$db->lastInsertId();
echo "✓ Providers created\n";

// Provider transactions
$provTxs = [
    [$tenantId, $provider1, 'receivable', 'Route charges Jan', 'INV-001', 'USD', 1.0, 5000.00, 5000.00, '2024-01-31', '2024-01-05', null],
    [$tenantId, $provider1, 'payment', 'Payment for Jan invoice', 'PAY-001', 'USD', 1.0, 3000.00, 3000.00, null, '2024-02-10', null],
    [$tenantId, $provider1, 'receivable', 'Route charges Feb', 'INV-002', 'EUR', 1.08, 4000.00, 4320.00, '2024-02-29', '2024-02-05', null],
    [$tenantId, $provider1, 'payment', 'Partial payment Feb', 'PAY-002', 'USD', 1.0, 2000.00, 2000.00, null, '2024-02-20', null],
    [$tenantId, $provider2, 'receivable', 'SMS traffic charges', 'INV-P2-001', 'ILS', 0.28, 10000.00, 2800.00, '2024-02-15', '2024-01-15', null],
    [$tenantId, $provider2, 'payment', 'Payment ILS', 'PAY-P2-001', 'ILS', 0.28, 5000.00, 1400.00, null, '2024-02-01', null],
    [$tenantId, $provider2, 'receivable', 'Feb SMS charges', 'INV-P2-002', 'USD', 1.0, 3500.00, 3500.00, '2024-03-15', '2024-02-28', null],
];

$ptStmt = $db->prepare('INSERT INTO provider_transactions (tenant_id, provider_id, type, description, reference, currency, exchange_rate, original_amount, base_amount, due_date, transaction_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
foreach ($provTxs as $tx) {
    $ptStmt->execute($tx);
}
echo "✓ Provider transactions created\n";

// Clients
$db->prepare("INSERT INTO clients (tenant_id, name, contact_name, email, phone, notes) VALUES (?, 'TechCorp Inc', 'Alice Johnson', 'alice@techcorp.com', '+1-555-0201', 'Major enterprise client, monthly invoicing')")->execute([$tenantId]);
$client1 = (int)$db->lastInsertId();

$db->prepare("INSERT INTO clients (tenant_id, name, contact_name, email, phone, notes) VALUES (?, 'Media Group', 'Bob Williams', 'bob@mediagroup.net', '+1-555-0202', 'Marketing agency, high volume SMS campaigns')")->execute([$tenantId]);
$client2 = (int)$db->lastInsertId();
echo "✓ Clients created\n";

// Client transactions
$clientTxs = [
    [$tenantId, $client1, 'invoice', 'SMS service Jan', 'CINV-001', 'USD', 1.0, 8000.00, 8000.00, '2024-01-31', '2024-01-05', null],
    [$tenantId, $client1, 'payment', 'Payment for Jan', 'CPAY-001', 'USD', 1.0, 8000.00, 8000.00, null, '2024-01-28', null],
    [$tenantId, $client1, 'invoice', 'SMS service Feb', 'CINV-002', 'USD', 1.0, 9500.00, 9500.00, '2024-02-29', '2024-02-05', null],
    [$tenantId, $client1, 'payment', 'Partial payment Feb', 'CPAY-002', 'USD', 1.0, 5000.00, 5000.00, null, '2024-02-20', null],
    [$tenantId, $client2, 'invoice', 'Campaign SMS Jan', 'CINV-M-001', 'EUR', 1.08, 6000.00, 6480.00, '2024-02-15', '2024-01-20', null],
    [$tenantId, $client2, 'payment', 'EUR payment', 'CPAY-M-001', 'EUR', 1.08, 6000.00, 6480.00, null, '2024-02-14', null],
    [$tenantId, $client2, 'invoice', 'Campaign SMS Feb', 'CINV-M-002', 'EUR', 1.08, 7000.00, 7560.00, '2024-03-20', '2024-02-25', null],
];

$ctStmt = $db->prepare('INSERT INTO client_transactions (tenant_id, client_id, type, description, reference, currency, exchange_rate, original_amount, base_amount, due_date, transaction_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
foreach ($clientTxs as $tx) {
    $ctStmt->execute($tx);
}
echo "✓ Client transactions created\n";

// Expenses
$expenses = [
    [$tenantId, 'Office Rent January', 'Rent', 'USD', 1.0, 2000.00, 2000.00, '2024-01-01', null],
    [$tenantId, 'Office Rent February', 'Rent', 'USD', 1.0, 2000.00, 2000.00, '2024-02-01', null],
    [$tenantId, 'Cloud Infrastructure', 'Software', 'USD', 1.0, 450.00, 450.00, '2024-01-15', 'AWS monthly bill'],
    [$tenantId, 'SMS Monitoring Tool', 'Software', 'EUR', 1.08, 99.00, 106.92, '2024-01-20', 'Monthly subscription'],
    [$tenantId, 'Business Travel', 'Travel', 'ILS', 0.28, 5000.00, 1400.00, '2024-02-10', 'Conference in Tel Aviv'],
];

$expStmt = $db->prepare('INSERT INTO expenses (tenant_id, title, category, currency, exchange_rate, original_amount, base_amount, expense_date, notes) VALUES (?,?,?,?,?,?,?,?,?)');
foreach ($expenses as $exp) {
    $expStmt->execute($exp);
}
echo "✓ Expenses created\n";

// Employees
$db->prepare("INSERT INTO employees (tenant_id, name, role, salary_amount, salary_currency, salary_exchange_rate, salary_base_amount, notes) VALUES (?, 'David Cohen', 'Senior Developer', 15000, 'ILS', 0.28, 4200.00, 'Full stack developer')")->execute([$tenantId]);
$emp1 = (int)$db->lastInsertId();

$db->prepare("INSERT INTO employees (tenant_id, name, role, salary_amount, salary_currency, salary_exchange_rate, salary_base_amount, notes) VALUES (?, 'Sarah Miller', 'Account Manager', 3500, 'USD', 1.0, 3500.00, 'Manages client relationships')")->execute([$tenantId]);
$emp2 = (int)$db->lastInsertId();
echo "✓ Employees created\n";

// Payroll records
$payroll = [
    [$tenantId, $emp1, 'salary', 'January salary', 'ILS', 0.28, 15000.00, 4200.00, '2024-01-31', null],
    [$tenantId, $emp1, 'salary', 'February salary', 'ILS', 0.28, 15000.00, 4200.00, '2024-02-29', null],
    [$tenantId, $emp1, 'advance', 'Advance request', 'ILS', 0.28, 3000.00, 840.00, '2024-02-15', 'Emergency advance'],
    [$tenantId, $emp2, 'salary', 'January salary', 'USD', 1.0, 3500.00, 3500.00, '2024-01-31', null],
    [$tenantId, $emp2, 'salary', 'February salary', 'USD', 1.0, 3500.00, 3500.00, '2024-02-29', null],
    [$tenantId, $emp2, 'bonus', 'Q1 performance bonus', 'USD', 1.0, 500.00, 500.00, '2024-02-28', 'Exceeded targets'],
];

$prStmt = $db->prepare('INSERT INTO payroll_records (tenant_id, employee_id, type, description, currency, exchange_rate, original_amount, base_amount, payment_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
foreach ($payroll as $pr) {
    $prStmt->execute($pr);
}
echo "✓ Payroll records created\n";

echo "\n=== Setup Complete! ===\n";
echo "Login credentials:\n";
echo "  Super Admin: admin / admin123\n";
echo "  Demo Owner:  demo_owner / demo123\n";
echo "\nPoint your web server to: public/index.php\n";
echo "Delete this setup.php file after setup is complete.\n";
