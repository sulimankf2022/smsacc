<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-receipt me-2 text-warning"></i>Expenses</h1>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="bi bi-plus-lg me-1"></i>Add Expense
    </button>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">This Month (<?php echo date('M Y', strtotime($filterMonth . '-01')); ?>)</div>
                <div class="fw-bold fs-4 text-warning"><?php echo formatMoney($monthTotal); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">All Time Total</div>
                <div class="fw-bold fs-4"><?php echo formatMoney($grandTotal); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">This Month Count</div>
                <div class="fw-bold fs-4"><?php echo count($expenses); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Month Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-3">
            <input type="hidden" name="page" value="expenses">
            <label class="fw-semibold text-nowrap mb-0">Filter by Month:</label>
            <input type="month" name="month" class="form-control" style="max-width:200px" value="<?php echo htmlspecialchars($filterMonth); ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Expenses for <?php echo date('F Y', strtotime($filterMonth . '-01')); ?></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th><th>Title</th><th>Category</th>
                        <th>Currency</th><th class="text-end">Amount</th>
                        <th class="text-end">Rate</th><th class="text-end">USD</th>
                        <th>Notes</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No expenses for this month.</td></tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                    <tr>
                        <td class="text-muted small"><?php echo formatDate($e['expense_date']); ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($e['title']); ?></td>
                        <td>
                            <?php if ($e['category']): ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($e['category']); ?></span>
                            <?php else: ?>-
                            <?php endif; ?>
                        </td>
                        <td><?php echo $e['currency']; ?></td>
                        <td class="text-end"><?php echo number_format($e['original_amount'], 2); ?></td>
                        <td class="text-end text-muted small"><?php echo $e['exchange_rate']; ?></td>
                        <td class="text-end fw-semibold"><?php echo formatMoney($e['base_amount']); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($e['notes'] ?: '-'); ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this expense?')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="form_action" value="delete">
                                <input type="hidden" name="expense_id" value="<?php echo $e['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($expenses)): ?>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="6" class="text-end">Month Total:</td>
                        <td class="text-end"><?php echo formatMoney($monthTotal); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Currency rates data -->
<?php
$db = getDB();
$rates = getCurrencyRates($db, getTenantId());
?>
<script id="currency-rates-data" type="application/json"><?php echo json_encode($rates); ?></script>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Office Rent" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control" list="categoryList" placeholder="e.g. Rent, Software">
                            <datalist id="categoryList">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                                <option value="Rent"><option value="Software"><option value="Hardware">
                                <option value="Marketing"><option value="Travel"><option value="Utilities">
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Currency</label>
                            <select name="currency" class="form-select" data-currency-select>
                                <?php foreach ($currencies as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Exchange Rate</label>
                            <input type="number" name="exchange_rate" data-exchange-rate class="form-control" value="1.0" step="0.0001" min="0.0001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="original_amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
