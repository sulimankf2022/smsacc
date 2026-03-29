<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_clients_index(): void {
    $db = getDB();
    $tenantId = getTenantId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=clients');
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
                flash('error', 'Client name is required.');
                redirect(BASE_URL . '/index.php?page=clients');
            }

            if ($action === 'add') {
                $stmt = $db->prepare('INSERT INTO clients (tenant_id, name, contact_name, email, phone, address, notes) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$tenantId, $name, $contactName, $email, $phone, $address, $notes]);
                flash('success', 'Client added successfully.');
            } else {
                $id = (int)($_POST['client_id'] ?? 0);
                $stmt = $db->prepare('UPDATE clients SET name=?, contact_name=?, email=?, phone=?, address=?, notes=? WHERE id=? AND tenant_id=?');
                $stmt->execute([$name, $contactName, $email, $phone, $address, $notes, $id, $tenantId]);
                flash('success', 'Client updated successfully.');
            }
            redirect(BASE_URL . '/index.php?page=clients');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['client_id'] ?? 0);
            $stmt = $db->prepare('UPDATE clients SET is_active=0 WHERE id=? AND tenant_id=?');
            $stmt->execute([$id, $tenantId]);
            flash('success', 'Client deactivated.');
            redirect(BASE_URL . '/index.php?page=clients');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->prepare("
        SELECT c.*,
            COALESCE(SUM(CASE WHEN ct.type='invoice' THEN ct.base_amount ELSE 0 END),0) as total_invoiced,
            COALESCE(SUM(CASE WHEN ct.type='payment' THEN ct.base_amount ELSE 0 END),0) as total_paid,
            COALESCE(SUM(CASE WHEN ct.type IN ('invoice','adjustment_debit') THEN ct.base_amount WHEN ct.type IN ('payment','adjustment_credit') THEN -ct.base_amount ELSE 0 END),0) as balance,
            MAX(ct.transaction_date) as last_transaction
        FROM clients c
        LEFT JOIN client_transactions ct ON ct.client_id=c.id AND ct.tenant_id=c.tenant_id
        WHERE c.tenant_id=? AND c.is_active=1
        GROUP BY c.id
        ORDER BY c.name
    ");
    $stmt->execute([$tenantId]);
    $clients = $stmt->fetchAll();

    require __DIR__ . '/../views/clients/index.php';
}

function controller_clients_profile(): void {
    $db = getDB();
    $tenantId = getTenantId();
    $clientId = (int)($_GET['id'] ?? 0);

    if (!$clientId) redirect(BASE_URL . '/index.php?page=clients');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=clients&action=profile&id=' . $clientId);
        }

        $formAction = $_POST['form_action'] ?? '';

        if ($formAction === 'add_transaction') {
            $type = $_POST['type'] ?? '';
            $validTypes = ['invoice', 'payment', 'adjustment_debit', 'adjustment_credit'];
            if (!in_array($type, $validTypes)) {
                flash('error', 'Invalid transaction type.');
                redirect(BASE_URL . '/index.php?page=clients&action=profile&id=' . $clientId);
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
                redirect(BASE_URL . '/index.php?page=clients&action=profile&id=' . $clientId);
            }

            $stmt = $db->prepare('INSERT INTO client_transactions (tenant_id, client_id, type, description, reference, currency, exchange_rate, original_amount, base_amount, due_date, transaction_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$tenantId, $clientId, $type, $description, $reference, $currency, $exchangeRate, $originalAmount, $baseAmount, $dueDate, $transactionDate, $notes]);
            flash('success', 'Transaction added successfully.');
            redirect(BASE_URL . '/index.php?page=clients&action=profile&id=' . $clientId);
        }

        if ($formAction === 'delete_transaction') {
            $txId = (int)($_POST['transaction_id'] ?? 0);
            $stmt = $db->prepare('DELETE FROM client_transactions WHERE id=? AND tenant_id=?');
            $stmt->execute([$txId, $tenantId]);
            flash('success', 'Transaction deleted.');
            redirect(BASE_URL . '/index.php?page=clients&action=profile&id=' . $clientId);
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->prepare('SELECT * FROM clients WHERE id=? AND tenant_id=?');
    $stmt->execute([$clientId, $tenantId]);
    $client = $stmt->fetch();
    if (!$client) redirect(BASE_URL . '/index.php?page=clients');

    $stmt = $db->prepare('SELECT * FROM client_transactions WHERE client_id=? AND tenant_id=? ORDER BY transaction_date ASC, id ASC');
    $stmt->execute([$clientId, $tenantId]);
    $transactions = $stmt->fetchAll();

    $runningBalance = 0;
    $totalInvoiced = 0;
    $totalPaid = 0;
    foreach ($transactions as &$tx) {
        if ($tx['type'] === 'invoice' || $tx['type'] === 'adjustment_debit') {
            $runningBalance += $tx['base_amount'];
            if ($tx['type'] === 'invoice') $totalInvoiced += $tx['base_amount'];
        } else {
            $runningBalance -= $tx['base_amount'];
            if ($tx['type'] === 'payment') $totalPaid += $tx['base_amount'];
        }
        $tx['running_balance'] = $runningBalance;
    }
    unset($tx);

    $netBalance = $totalInvoiced - $totalPaid;
    $today = date('Y-m-d');
    $overdueAmount = 0;
    foreach ($transactions as $tx) {
        if ($tx['type'] === 'invoice' && $tx['due_date'] && $tx['due_date'] < $today) {
            $overdueAmount += $tx['base_amount'];
        }
    }

    $currencies = CURRENCIES;

    require __DIR__ . '/../views/clients/profile.php';
}
