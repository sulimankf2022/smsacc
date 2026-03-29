<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-person-badge me-2 text-info"></i>Employees</h1>
    <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        <i class="bi bi-plus-lg me-1"></i>Add Employee
    </button>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="card">
    <div class="card-header">All Employees (<?php echo count($employees); ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th><th>Role</th>
                        <th class="text-end">Monthly Salary</th><th>Currency</th>
                        <th class="text-end">Salary (USD)</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Advances</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($employees)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No employees yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/index.php?page=employees&action=profile&id=<?php echo $emp['id']; ?>" class="fw-semibold text-decoration-none">
                                <?php echo htmlspecialchars($emp['name']); ?>
                            </a>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($emp['role'] ?: '-'); ?></td>
                        <td class="text-end"><?php echo number_format($emp['salary_amount'], 2); ?></td>
                        <td><?php echo $emp['salary_currency']; ?></td>
                        <td class="text-end fw-semibold"><?php echo formatMoney($emp['salary_base_amount']); ?></td>
                        <td class="text-end text-success"><?php echo formatMoney($emp['total_salary_paid']); ?></td>
                        <td class="text-end text-warning"><?php echo formatMoney($emp['total_advances']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>/index.php?page=employees&action=profile&id=<?php echo $emp['id']; ?>" class="btn btn-outline-info" title="View Profile"><i class="bi bi-eye"></i></a>
                                <button class="btn btn-outline-secondary" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($emp)); ?>)"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger" onclick="confirmDeleteEmployee(<?php echo $emp['id']; ?>)"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?page=employees">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add" id="empFormAction">
                <input type="hidden" name="employee_id" id="empFormId">
                <div class="modal-header">
                    <h5 class="modal-title" id="empModalTitle"><i class="bi bi-person-badge me-2"></i>Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="empName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role / Position</label>
                            <input type="text" name="role" id="empRole" class="form-control" placeholder="e.g. Developer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Currency</label>
                            <select name="salary_currency" id="empSalaryCurrency" class="form-select" data-currency-select>
                                <?php foreach ($currencies as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Exchange Rate</label>
                            <input type="number" name="salary_exchange_rate" id="empSalaryRate" data-exchange-rate class="form-control" value="1.0" step="0.0001" min="0.0001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Monthly Salary</label>
                            <input type="number" name="salary_amount" id="empSalaryAmount" class="form-control" step="0.01" min="0" value="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="empNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST" id="deleteEmpForm" action="<?php echo BASE_URL; ?>/index.php?page=employees">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="form_action" value="delete">
    <input type="hidden" name="employee_id" id="deleteEmpId">
</form>

<?php
$db = getDB();
$rates = getCurrencyRates($db, getTenantId());
?>
<script id="currency-rates-data" type="application/json"><?php echo json_encode($rates); ?></script>

<script>
function editEmployee(emp) {
    document.getElementById('empFormAction').value = 'edit';
    document.getElementById('empFormId').value = emp.id;
    document.getElementById('empName').value = emp.name;
    document.getElementById('empRole').value = emp.role || '';
    document.getElementById('empSalaryCurrency').value = emp.salary_currency;
    document.getElementById('empSalaryRate').value = emp.salary_exchange_rate;
    document.getElementById('empSalaryAmount').value = emp.salary_amount;
    document.getElementById('empNotes').value = emp.notes || '';
    document.getElementById('empModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Employee';
    new bootstrap.Modal(document.getElementById('addEmployeeModal')).show();
}
function confirmDeleteEmployee(id) {
    if (confirm('Deactivate this employee?')) {
        document.getElementById('deleteEmpId').value = id;
        document.getElementById('deleteEmpForm').submit();
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
