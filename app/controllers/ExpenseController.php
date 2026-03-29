<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_expenses_index(): void {
    $db = getDB();
    $tenantId = getTenantId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=expenses');
        }

        $action = $_POST['form_action'] ?? '';

        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $currency = $_POST['currency'] ?? 'USD';
            $exchangeRate = (float)($_POST['exchange_rate'] ?? 1.0);
            $originalAmount = (float)($_POST['original_amount'] ?? 0);
            $baseAmount = convertToBase($originalAmount, $exchangeRate);
            $expenseDate = $_POST['expense_date'] ?: date('Y-m-d');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($title) || $originalAmount <= 0) {
                flash('error', 'Title and a valid amount are required.');
                redirect(BASE_URL . '/index.php?page=expenses');
            }

            $stmt = $db->prepare('INSERT INTO expenses (tenant_id, title, category, currency, exchange_rate, original_amount, base_amount, expense_date, notes) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$tenantId, $title, $category, $currency, $exchangeRate, $originalAmount, $baseAmount, $expenseDate, $notes]);
            flash('success', 'Expense added successfully.');
            redirect(BASE_URL . '/index.php?page=expenses');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['expense_id'] ?? 0);
            $stmt = $db->prepare('DELETE FROM expenses WHERE id=? AND tenant_id=?');
            $stmt->execute([$id, $tenantId]);
            flash('success', 'Expense deleted.');
            redirect(BASE_URL . '/index.php?page=expenses');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    // Filter by month/year
    $filterMonth = $_GET['month'] ?? date('Y-m');

    $stmt = $db->prepare("SELECT * FROM expenses WHERE tenant_id=? AND strftime('%Y-%m', expense_date)=? ORDER BY expense_date DESC");
    $stmt->execute([$tenantId, $filterMonth]);
    $expenses = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT COALESCE(SUM(base_amount),0) as total FROM expenses WHERE tenant_id=? AND strftime('%Y-%m', expense_date)=?");
    $stmt->execute([$tenantId, $filterMonth]);
    $monthTotal = (float)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(base_amount),0) as total FROM expenses WHERE tenant_id=?");
    $stmt->execute([$tenantId]);
    $grandTotal = (float)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT DISTINCT category FROM expenses WHERE tenant_id=? AND category != '' ORDER BY category");
    $stmt->execute([$tenantId]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $currencies = CURRENCIES;

    require __DIR__ . '/../views/expenses/index.php';
}
