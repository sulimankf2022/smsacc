<?php require __DIR__ . '/../layout/header.php'; ?>

<!-- Client Header -->
<div class="profile-header mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="mb-1"><a href="<?php echo BASE_URL; ?>/index.php?page=clients" class="text-white-50 text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Back to Clients</a></div>
            <h4><?php echo htmlspecialchars($client['name']); ?></h4>
            <?php if ($client['contact_name']): ?><div class="text-white-50 small"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($client['contact_name']); ?></div><?php endif; ?>
            <?php if ($client['email']): ?><div class="text-white-50 small"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($client['email']); ?></div><?php endif; ?>
            <?php if ($client['phone']): ?><div class="text-white-50 small"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($client['phone']); ?></div><?php endif; ?>
        </div>
        <div class="text-end">
            <button class="btn btn-sm btn-light me-1" data-bs-toggle="modal" data-bs-target="#addTxModal" onclick="setTxType('invoice')">
                <i class="bi bi-file-text me-1"></i>Add Invoice
            </button>
            <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#addTxModal" onclick="setTxType('payment')">
                <i class="bi bi-check-circle me-1"></i>Record Payment
            </button>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#addTxModal" onclick="setTxType('adjustment_debit')">
                <i class="bi bi-sliders me-1"></i>Adjustment
            </button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total Invoiced</div>
                <div class="fw-bold fs-5 text-primary"><?php echo formatMoney($totalInvoiced); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total Collected</div>
                <div class="fw-bold fs-5 text-success"><?php echo formatMoney($totalPaid); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Net Outstanding</div>
                <div class="fw-bold fs-5 <?php echo $netBalance > 0 ? 'text-success' : 'text-danger'; ?>"><?php echo formatMoney($netBalance); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Overdue</div>
                <div class="fw-bold fs-5 <?php echo $overdueAmount > 0 ? 'text-danger' : 'text-muted'; ?>"><?php echo formatMoney($overdueAmount); ?></div>
            </div>
        </div>
    </div>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<!-- Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#transactions">Transactions</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#notes">Notes</a></li>
        </ul>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active p-0" id="transactions">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th><th>Type</th><th>Description</th><th>Reference</th>
                            <th>Currency</th><th class="text-end">Amount</th><th class="text-end">Rate</th>
                            <th class="text-end">USD</th><th>Due Date</th><th class="text-end">Running Balance</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="11" class="text-center text-muted py-4">No transactions yet.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_reverse($transactions) as $tx): ?>
                        <tr>
                            <td class="text-muted small"><?php echo formatDate($tx['transaction_date']); ?></td>
                            <td><span class="badge badge-<?php echo $tx['type']; ?>"><?php echo ucwords(str_replace('_', ' ', $tx['type'])); ?></span></td>
                            <td><?php echo htmlspecialchars($tx['description'] ?: '-'); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($tx['reference'] ?: '-'); ?></td>
                            <td><?php echo $tx['currency']; ?></td>
                            <td class="text-end"><?php echo number_format($tx['original_amount'], 2); ?></td>
                            <td class="text-end text-muted small"><?php echo $tx['exchange_rate']; ?></td>
                            <td class="text-end fw-semibold"><?php echo formatMoney($tx['base_amount']); ?></td>
                            <td class="<?php echo ($tx['due_date'] && $tx['due_date'] < date('Y-m-d')) ? 'text-danger' : 'text-muted'; ?> small">
                                <?php echo $tx['due_date'] ? formatDate($tx['due_date']) : '-'; ?>
                            </td>
                            <td class="text-end running-balance <?php echo $tx['running_balance'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo formatMoney($tx['running_balance']); ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this transaction?')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="form_action" value="delete_transaction">
                                    <input type="hidden" name="transaction_id" value="<?php echo $tx['id']; ?>">
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
        <div class="tab-pane fade p-4" id="notes">
            <?php if ($client['notes']): ?>
                <p><?php echo nl2br(htmlspecialchars($client['notes'])); ?></p>
            <?php else: ?>
                <p class="text-muted">No notes for this client.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Currency rates data for JS -->
<?php
$db = getDB();
$rates = getCurrencyRates($db, getTenantId());
?>
<script id="currency-rates-data" type="application/json"><?php echo json_encode($rates); ?></script>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add_transaction">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" id="txType" class="form-select" required>
                                <option value="invoice">Invoice</option>
                                <option value="payment">Payment</option>
                                <option value="adjustment_debit">Adjustment (Debit)</option>
                                <option value="adjustment_credit">Adjustment (Credit)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Monthly SMS traffic">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="INV-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select" data-currency-select required>
                                <?php foreach ($currencies as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Exchange Rate <span class="text-danger">*</span></label>
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
                    <button type="submit" class="btn btn-success">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setTxType(type) {
    document.getElementById('txType').value = type;
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
