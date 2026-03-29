<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-shield-lock me-2 text-danger"></i>Admin Panel</h1>
    <span class="badge bg-danger fs-6">Super Admin</span>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="row g-4">
    <!-- Tenants -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-buildings me-2"></i>Tenants (<?php echo count($tenants); ?>)</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Tenant
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Slug</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                        <tr>
                            <td class="text-muted"><?php echo $tenant['id']; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($tenant['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($tenant['slug']); ?></code></td>
                            <td>
                                <span class="badge <?php echo $tenant['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $tenant['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo formatDate($tenant['created_at']); ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="form_action" value="toggle_tenant">
                                    <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $tenant['is_active'] ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                        <?php echo $tenant['is_active'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Users -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Users (<?php echo count($users); ?>)</span>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Tenant</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="text-muted"><?php echo $user['id']; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($user['tenant_name'] ?? 'None'); ?></td>
                            <td>
                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="form_action" value="toggle_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                        <?php echo $user['is_active'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add_tenant">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-building me-2"></i>Add Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Acme Corp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control" required placeholder="acme-corp" pattern="[a-z0-9_-]+">
                        <div class="form-text">Lowercase letters, numbers, hyphens only. Must be unique.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="form_action" value="add_user">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select">
                                <option value="staff">Staff</option>
                                <option value="manager">Manager</option>
                                <option value="owner">Owner</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assign to Tenant</label>
                            <select name="tenant_id" class="form-select">
                                <option value="">— No Tenant (Super Admin) —</option>
                                <?php foreach ($tenants as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['slug']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
