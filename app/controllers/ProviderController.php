<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_providers_index(): void {
    $db = getDB();
    $tenantId = getTenantId();

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=providers');
        }

        $action = $_POST['form_action'] ?? '';

        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $contactName = trim($_POST['contact_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($name)) {
                flash('error', 'Provider name is required.');
                redirect(BASE_URL . '/index.php?page=providers');
            }

            if ($action === 'add') {
                $stmt = $db->prepare('INSERT INTO providers (tenant_id, name, contact_name, email, phone, address, notes) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$tenantId, $name, $contactName, $email, $phone, $address, $notes]);
                flash('success', 'Provider added successfully.');
            } else {
                $id = (int)($_POST['provider_id'] ?? 0);
                $stmt = $db->prepare('UPDATE providers SET name=?, contact_name=?, email=?, phone=?, address=?, notes=? WHERE id=? AND tenant_id=?');
                $stmt->execute([$name, $contactName, $email, $phone, $address, $notes, $id, $tenantId]);
                flash('success', 'Provider updated successfully.');
            }
            redirect(BASE_URL . '/index.php?page=providers');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['provider_id'] ?? 0);
            $stmt = $db->prepare('UPDATE providers SET is_active=0 WHERE id=? AND tenant_id=?');
            $stmt->execute([$id, $tenantId]);
            flash('success', 'Provider deactivated.');
            redirect(BASE_URL . '/index.php?page=providers');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->prepare("
        SELECT p.*,
            COALESCE(SUM(CASE WHEN pt.type='receivable' THEN pt.base_amount ELSE 0 END),0) as total_receivable,
            COALESCE(SUM(CASE WHEN pt.type='payment' THEN pt.base_amount ELSE 0 END),0) as total_paid,
            COALESCE(SUM(CASE WHEN pt.type IN ('receivable','adjustment_debit') THEN pt.base_amount WHEN pt.type IN ('payment','adjustment_credit') THEN -pt.base_amount ELSE 0 END),0) as balance,
            MAX(pt.transaction_date) as last_transaction
        FROM providers p
        LEFT JOIN provider_transactions pt ON pt.provider_id=p.id AND pt.tenant_id=p.tenant_id
        WHERE p.tenant_id=? AND p.is_active=1
        GROUP BY p.id
        ORDER BY p.name
    ");
    $stmt->execute([$tenantId]);
    $providers = $stmt->fetchAll();

    require __DIR__ . '/../views/providers/index.php';
}

function controller_providers_profile(): void {
    $db = getDB();
    $tenantId = getTenantId();
    $providerId = (int)($_GET['id'] ?? 0);

    if (!$providerId) redirect(BASE_URL . '/index.php?page=providers');

    // Handle POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=providers&action=profile&id=' . $providerId);
        }

        $formAction = $_POST['form_action'] ?? '';

        if ($formAction === 'add_transaction') {
            $type = $_POST['type'] ?? '';
            $validTypes = ['receivable', 'payment', 'adjustment_debit', 'adjustment_credit'];
            if (!in_array($type, $validTypes)) {
                flash('error', 'Invalid transaction type.');
                redirect(BASE_URL . '/index.php?page=providers&action=profile&id=' . $providerId);
            }

            $description = trim($_POST['description'] ?? '');
            $reference = trim($_POST['reference'] ?? '');
            $currency = $_POST['currency'] ?? 'USD';
            $exchangeRate = (float)($_POST['exchange_rate'] ?? 1.0);
            $originalAmount = (float)($_POST['original_amount'] ?? 0);
            $baseAmount = convertToBase($originalAmount, $exchangeRate);
            $dueDate = $_POST['due_date'] ?: null;
            $transactionDate = $_POST['transaction_date'] ?: date('Y-m-d');
            $notes = trim($_POST['notes'] ?? '');

            if ($originalAmount <= 0) {
                flash('error', 'Amount must be greater than 0.');
                redirect(BASE_URL . '/index.php?page=providers&action=profile&id=' . $providerId);
            }

            $stmt = $db->prepare('INSERT INTO provider_transactions (tenant_id, provider_id, type, description, reference, currency, exchange_rate, original_amount, base_amount, due_date, transaction_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$tenantId, $providerId, $type, $description, $reference, $currency, $exchangeRate, $originalAmount, $baseAmount, $dueDate, $transactionDate, $notes]);
            flash('success', 'Transaction added successfully.');
            redirect(BASE_URL . '/index.php?page=providers&action=profile&id=' . $providerId);
        }

        if ($formAction === 'delete_transaction') {
            $txId = (int)($_POST['transaction_id'] ?? 0);
            $stmt = $db->prepare('DELETE FROM provider_transactions WHERE id=? AND tenant_id=?');
            $stmt->execute([$txId, $tenantId]);
            flash('success', 'Transaction deleted.');
            redirect(BASE_URL . '/index.php?page=providers&action=profile&id=' . $providerId);
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    // Get provider
    $stmt = $db->prepare('SELECT * FROM providers WHERE id=? AND tenant_id=?');
    $stmt->execute([$providerId, $tenantId]);
    $provider = $stmt->fetch();
    if (!$provider) redirect(BASE_URL . '/index.php?page=providers');

    // Get transactions ordered
    $stmt = $db->prepare('SELECT * FROM provider_transactions WHERE provider_id=? AND tenant_id=? ORDER BY transaction_date ASC, id ASC');
    $stmt->execute([$providerId, $tenantId]);
    $transactions = $stmt->fetchAll();

    // Calculate running balance
    $runningBalance = 0;
    $totalReceivable = 0;
    $totalPaid = 0;
    foreach ($transactions as &$tx) {
        if ($tx['type'] === 'receivable' || $tx['type'] === 'adjustment_debit') {
            $runningBalance += $tx['base_amount'];
            if ($tx['type'] === 'receivable') $totalReceivable += $tx['base_amount'];
        } else {
            $runningBalance -= $tx['base_amount'];
            if ($tx['type'] === 'payment') $totalPaid += $tx['base_amount'];
        }
        $tx['running_balance'] = $runningBalance;
    }
    unset($tx);

    $netBalance = $totalReceivable - $totalPaid;

    // Overdue
    $today = date('Y-m-d');
    $overdueAmount = 0;
    foreach ($transactions as $tx) {
        if ($tx['type'] === 'receivable' && $tx['due_date'] && $tx['due_date'] < $today) {
            $overdueAmount += $tx['base_amount'];
        }
    }

    $currencies = CURRENCIES;

    require __DIR__ . '/../views/providers/profile.php';
}
