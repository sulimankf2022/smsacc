<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database.php';

function controller_employees_index(): void {
    $db = getDB();
    $tenantId = getTenantId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=employees');
        }

        $action = $_POST['form_action'] ?? '';

        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $salaryCurrency = $_POST['salary_currency'] ?? 'USD';
            $salaryExchangeRate = (float)($_POST['salary_exchange_rate'] ?? 1.0);
            $salaryAmount = (float)($_POST['salary_amount'] ?? 0);
            $salaryBaseAmount = convertToBase($salaryAmount, $salaryExchangeRate);
            $notes = trim($_POST['notes'] ?? '');

            if (empty($name)) {
                flash('error', 'Employee name is required.');
                redirect(BASE_URL . '/index.php?page=employees');
            }

            if ($action === 'add') {
                $stmt = $db->prepare('INSERT INTO employees (tenant_id, name, role, salary_amount, salary_currency, salary_exchange_rate, salary_base_amount, notes) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([$tenantId, $name, $role, $salaryAmount, $salaryCurrency, $salaryExchangeRate, $salaryBaseAmount, $notes]);
                flash('success', 'Employee added successfully.');
            } else {
                $id = (int)($_POST['employee_id'] ?? 0);
                $stmt = $db->prepare('UPDATE employees SET name=?, role=?, salary_amount=?, salary_currency=?, salary_exchange_rate=?, salary_base_amount=?, notes=? WHERE id=? AND tenant_id=?');
                $stmt->execute([$name, $role, $salaryAmount, $salaryCurrency, $salaryExchangeRate, $salaryBaseAmount, $notes, $id, $tenantId]);
                flash('success', 'Employee updated successfully.');
            }
            redirect(BASE_URL . '/index.php?page=employees');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['employee_id'] ?? 0);
            $stmt = $db->prepare('UPDATE employees SET is_active=0 WHERE id=? AND tenant_id=?');
            $stmt->execute([$id, $tenantId]);
            flash('success', 'Employee deactivated.');
            redirect(BASE_URL . '/index.php?page=employees');
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->prepare("
        SELECT e.*,
            COALESCE(SUM(CASE WHEN pr.type='salary' THEN pr.base_amount ELSE 0 END),0) as total_salary_paid,
            COALESCE(SUM(CASE WHEN pr.type='advance' THEN pr.base_amount ELSE 0 END),0) as total_advances,
            COALESCE(SUM(pr.base_amount),0) as total_paid
        FROM employees e
        LEFT JOIN payroll_records pr ON pr.employee_id=e.id AND pr.tenant_id=e.tenant_id
        WHERE e.tenant_id=? AND e.is_active=1
        GROUP BY e.id
        ORDER BY e.name
    ");
    $stmt->execute([$tenantId]);
    $employees = $stmt->fetchAll();

    $currencies = CURRENCIES;

    require __DIR__ . '/../views/employees/index.php';
}

function controller_employees_profile(): void {
    $db = getDB();
    $tenantId = getTenantId();
    $employeeId = (int)($_GET['id'] ?? 0);

    if (!$employeeId) redirect(BASE_URL . '/index.php?page=employees');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            flash('error', 'Invalid security token.');
            redirect(BASE_URL . '/index.php?page=employees&action=profile&id=' . $employeeId);
        }

        $formAction = $_POST['form_action'] ?? '';

        if ($formAction === 'add_payment') {
            $type = $_POST['type'] ?? 'salary';
            $validTypes = ['salary', 'advance', 'bonus', 'deduction'];
            if (!in_array($type, $validTypes)) {
                flash('error', 'Invalid payment type.');
                redirect(BASE_URL . '/index.php?page=employees&action=profile&id=' . $employeeId);
            }

            $description = trim($_POST['description'] ?? '');
            $currency = $_POST['currency'] ?? 'USD';
            $exchangeRate = (float)($_POST['exchange_rate'] ?? 1.0);
            $originalAmount = (float)($_POST['original_amount'] ?? 0);
            $baseAmount = convertToBase($originalAmount, $exchangeRate);
            $paymentDate = $_POST['payment_date'] ?: date('Y-m-d');
            $notes = trim($_POST['notes'] ?? '');

            if ($originalAmount <= 0) {
                flash('error', 'Amount must be greater than 0.');
                redirect(BASE_URL . '/index.php?page=employees&action=profile&id=' . $employeeId);
            }

            $stmt = $db->prepare('INSERT INTO payroll_records (tenant_id, employee_id, type, description, currency, exchange_rate, original_amount, base_amount, payment_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$tenantId, $employeeId, $type, $description, $currency, $exchangeRate, $originalAmount, $baseAmount, $paymentDate, $notes]);
            flash('success', 'Payment recorded.');
            redirect(BASE_URL . '/index.php?page=employees&action=profile&id=' . $employeeId);
        }

        if ($formAction === 'delete_payment') {
            $prId = (int)($_POST['payroll_id'] ?? 0);
            $stmt = $db->prepare('DELETE FROM payroll_records WHERE id=? AND tenant_id=?');
            $stmt->execute([$prId, $tenantId]);
            flash('success', 'Payment record deleted.');
            redirect(BASE_URL . '/index.php?page=employees&action=profile&id=' . $employeeId);
        }
    }

    $success = getFlash('success');
    $error = getFlash('error');

    $stmt = $db->prepare('SELECT * FROM employees WHERE id=? AND tenant_id=?');
    $stmt->execute([$employeeId, $tenantId]);
    $employee = $stmt->fetch();
    if (!$employee) redirect(BASE_URL . '/index.php?page=employees');

    $stmt = $db->prepare('SELECT * FROM payroll_records WHERE employee_id=? AND tenant_id=? ORDER BY payment_date DESC, id DESC');
    $stmt->execute([$employeeId, $tenantId]);
    $payrollRecords = $stmt->fetchAll();

    $totalSalaryPaid = 0;
    $totalAdvances = 0;
    $totalBonuses = 0;
    foreach ($payrollRecords as $pr) {
        if ($pr['type'] === 'salary') $totalSalaryPaid += $pr['base_amount'];
        elseif ($pr['type'] === 'advance') $totalAdvances += $pr['base_amount'];
        elseif ($pr['type'] === 'bonus') $totalBonuses += $pr['base_amount'];
    }

    $currencies = CURRENCIES;

    require __DIR__ . '/../views/employees/profile.php';
}
