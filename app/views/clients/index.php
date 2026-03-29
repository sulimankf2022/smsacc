<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-people me-2 text-success"></i>Clients</h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addClientModal">
        <i class="bi bi-plus-lg me-1"></i>Add Client
    </button>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="card">
    <div class="card-header">All Clients (<?php echo count($clients); ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th><th>Contact</th><th>Email</th><th>Phone</th>
                        <th class="text-end">Invoiced</th><th class="text-end">Paid</th>
                        <th class="text-end">Balance</th><th>Last Tx</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No clients yet. Add your first client!</td></tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/index.php?page=clients&action=profile&id=<?php echo $c['id']; ?>" class="fw-semibold text-decoration-none">
                                <?php echo htmlspecialchars($c['name']); ?>
                            </a>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($c['contact_name'] ?: '-'); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($c['email'] ?: '-'); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($c['phone'] ?: '-'); ?></td>
                        <td class="text-end"><?php echo formatMoney($c['total_invoiced']); ?></td>
                        <td class="text-end text-success"><?php echo formatMoney($c['total_paid']); ?></td>
                        <td class="text-end fw-bold <?php echo $c['balance'] > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatMoney($c['balance']); ?>
                        </td>
                        <td class="text-muted small"><?php echo $c['last_transaction'] ? formatDate($c['last_transaction']) : '-'; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>/index.php?page=clients&action=profile&id=<?php echo $c['id']; ?>" class="btn btn-outline-success" title="View Profile"><i class="bi bi-eye"></i></a>
                                <button class="btn btn-outline-secondary" onclick="editClient(<?php echo htmlspecialchars(json_encode($c)); ?>)"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger" onclick="confirmDeleteClient(<?php echo $c['id']; ?>)"><i class="bi bi-trash"></i></button>
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

<!-- Add/Edit Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?page=clients">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add" id="clientFormAction">
                <input type="hidden" name="client_id" id="clientFormId">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalTitle"><i class="bi bi-people me-2"></i>Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="clientName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_name" id="clientContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="clientEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="clientPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" id="clientAddress" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="clientNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form method="POST" id="deleteClientForm" action="<?php echo BASE_URL; ?>/index.php?page=clients">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="form_action" value="delete">
    <input type="hidden" name="client_id" id="deleteClientId">
</form>

<script>
function editClient(c) {
    document.getElementById('clientFormAction').value = 'edit';
    document.getElementById('clientFormId').value = c.id;
    document.getElementById('clientName').value = c.name;
    document.getElementById('clientContact').value = c.contact_name || '';
    document.getElementById('clientEmail').value = c.email || '';
    document.getElementById('clientPhone').value = c.phone || '';
    document.getElementById('clientAddress').value = c.address || '';
    document.getElementById('clientNotes').value = c.notes || '';
    document.getElementById('clientModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Client';
    new bootstrap.Modal(document.getElementById('addClientModal')).show();
}
function confirmDeleteClient(id) {
    if (confirm('Deactivate this client?')) {
        document.getElementById('deleteClientId').value = id;
        document.getElementById('deleteClientForm').submit();
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
