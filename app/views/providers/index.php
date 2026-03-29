<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i>Providers</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
        <i class="bi bi-plus-lg me-1"></i>Add Provider
    </button>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="card">
    <div class="card-header">All Providers (<?php echo count($providers); ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-end">Receivable</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Last Tx</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($providers)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No providers yet. Add your first provider!</td></tr>
                <?php else: ?>
                    <?php foreach ($providers as $p): ?>
                    <tr>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/index.php?page=providers&action=profile&id=<?php echo $p['id']; ?>" class="fw-semibold text-decoration-none">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </a>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($p['contact_name'] ?: '-'); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($p['email'] ?: '-'); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($p['phone'] ?: '-'); ?></td>
                        <td class="text-end"><?php echo formatMoney($p['total_receivable']); ?></td>
                        <td class="text-end text-success"><?php echo formatMoney($p['total_paid']); ?></td>
                        <td class="text-end fw-bold <?php echo $p['balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                            <?php echo formatMoney($p['balance']); ?>
                        </td>
                        <td class="text-muted small"><?php echo $p['last_transaction'] ? formatDate($p['last_transaction']) : '-'; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>/index.php?page=providers&action=profile&id=<?php echo $p['id']; ?>" class="btn btn-outline-primary" title="View Profile"><i class="bi bi-eye"></i></a>
                                <button class="btn btn-outline-secondary" title="Edit" onclick="editProvider(<?php echo htmlspecialchars(json_encode($p)); ?>)"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger" title="Deactivate" onclick="confirmDelete(<?php echo $p['id']; ?>, 'provider')"><i class="bi bi-trash"></i></button>
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

<!-- Add/Edit Provider Modal -->
<div class="modal fade" id="addProviderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?page=providers">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add" id="providerFormAction">
                <input type="hidden" name="provider_id" id="providerFormId">
                <div class="modal-header">
                    <h5 class="modal-title" id="providerModalTitle"><i class="bi bi-building me-2"></i>Add Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="providerName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_name" id="providerContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="providerEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="providerPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" id="providerAddress" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="providerNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form method="POST" id="deleteForm" action="<?php echo BASE_URL; ?>/index.php?page=providers">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="form_action" value="delete">
    <input type="hidden" name="provider_id" id="deleteId">
</form>

<script>
function editProvider(p) {
    document.getElementById('providerFormAction').value = 'edit';
    document.getElementById('providerFormId').value = p.id;
    document.getElementById('providerName').value = p.name;
    document.getElementById('providerContact').value = p.contact_name || '';
    document.getElementById('providerEmail').value = p.email || '';
    document.getElementById('providerPhone').value = p.phone || '';
    document.getElementById('providerAddress').value = p.address || '';
    document.getElementById('providerNotes').value = p.notes || '';
    document.getElementById('providerModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Provider';
    new bootstrap.Modal(document.getElementById('addProviderModal')).show();
}
function confirmDelete(id, type) {
    if (confirm('Deactivate this provider?')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
