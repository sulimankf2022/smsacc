<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="profile-header mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="mb-1"><a href="<?php echo BASE_URL; ?>/index.php?page=employees" class="text-white-50 text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Back to Employees</a></div>
            <h4><?php echo htmlspecialchars($employee['name']); ?></h4>
            <?php if ($employee['role']): ?><div class="text-white-50 small"><i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($employee['role']); ?></div><?php endif; ?>
            <div class="text-white-50 small mt-1">Monthly Salary: <strong class="text-white"><?php echo number_format($employee['salary_amount'], 2) . ' ' . $employee['salary_currency']; ?></strong> = <strong class="text-white"><?php echo formatMoney($employee['salary_base_amount']); ?></strong></div>
        </div>
        <div class="text-end">
            <button class="btn btn-sm btn-light me-1" data-bs-toggle="modal" data-bs-target="#addPaymentModal" onclick="setPayType('salary')">
                <i class="bi bi-cash me-1"></i>Record Salary
            </button>
            <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#addPaymentModal" onclick="setPayType('advance')">
                <i class="bi bi-wallet me-1"></i>Advance
            </button>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal" onclick="setPayType('bonus')">
                <i class="bi bi-star me-1"></i>Bonus
            </button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Monthly Salary (USD)</div>
                <div class="fw-bold fs-5 text-primary"><?php echo formatMoney($employee['salary_base_amount']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total Salary Paid</div>
                <div class="fw-bold fs-5 text-success"><?php echo formatMoney($totalSalaryPaid); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total Advances</div>
                <div class="fw-bold fs-5 text-warning"><?php echo formatMoney($totalAdvances); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Bonuses</div>
                <div class="fw-bold fs-5 text-info"><?php echo formatMoney($totalBonuses); ?></div>
            </div>
        </div>
    </div>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="card">
    <div class="card-header">Payroll History</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th><th>Type</th><th>Description</th>
                        <th>Currency</th><th class="text-end">Amount</th>
                        <th class="text-end">Rate</th><th class="text-end">USD</th>
                        <th>Notes</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($payrollRecords)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No payroll records yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payrollRecords as $pr): ?>
                    <tr>
                        <td class="text-muted small"><?php echo formatDate($pr['payment_date']); ?></td>
                        <td><span class="badge badge-<?php echo $pr['type']; ?>"><?php echo ucfirst($pr['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($pr['description'] ?: '-'); ?></td>
                        <td><?php echo $pr['currency']; ?></td>
                        <td class="text-end"><?php echo number_format($pr['original_amount'], 2); ?></td>
                        <td class="text-end text-muted small"><?php echo $pr['exchange_rate']; ?></td>
                        <td class="text-end fw-semibold"><?php echo formatMoney($pr['base_amount']); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($pr['notes'] ?: '-'); ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="form_action" value="delete_payment">
                                <input type="hidden" name="payroll_id" value="<?php echo $pr['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$db = getDB();
$rates = getCurrencyRates($db, getTenantId());
?>
<script id="currency-rates-data" type="application/json"><?php echo json_encode($rates); ?></script>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add_payment">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" id="payType" class="form-select" required>
                                <option value="salary">Salary</option>
                                <option value="advance">Advance</option>
                                <option value="bonus">Bonus</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. March 2024 salary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Currency</label>
                            <select name="currency" class="form-select" data-currency-select>
                                <?php foreach ($currencies as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $c === $employee['salary_currency'] ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Exchange Rate</label>
                            <input type="number" name="exchange_rate" data-exchange-rate class="form-control" value="<?php echo $employee['salary_exchange_rate']; ?>" step="0.0001" min="0.0001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="original_amount" class="form-control" step="0.01" min="0.01" value="<?php echo $employee['salary_amount']; ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setPayType(type) {
    document.getElementById('payType').value = type;
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
