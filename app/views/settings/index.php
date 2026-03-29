<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-gear me-2"></i>Settings</h1>
</div>

<?php echo alertHtml($success ?? null, $error ?? null); ?>

<div class="row g-4">
    <!-- Business Settings -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-building me-2"></i>Business Settings</div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_action" value="business_settings">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Business Name</label>
                        <input type="text" name="business_name" class="form-control" value="<?php echo htmlspecialchars($businessName); ?>" placeholder="Your company name">
                        <div class="form-text">Displayed in the sidebar and navbar.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Currency Rates -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-currency-exchange me-2"></i>Currency Exchange Rates (to USD)</div>
            <div class="card-body">
                <?php if (!getTenantId()): ?>
                    <div class="alert alert-info">Super Admin accounts do not have currency settings.</div>
                <?php else: ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_action" value="currency_rates">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">EUR → USD</label>
                        <div class="input-group">
                            <span class="input-group-text">€1 =</span>
                            <input type="number" name="rate_EUR" class="form-control" step="0.0001" min="0.0001"
                                value="<?php echo htmlspecialchars($currencyRates['EUR'] ?? '1.08'); ?>" required>
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ILS → USD</label>
                        <div class="input-group">
                            <span class="input-group-text">₪1 =</span>
                            <input type="number" name="rate_ILS" class="form-control" step="0.0001" min="0.0001"
                                value="<?php echo htmlspecialchars($currencyRates['ILS'] ?? '0.28'); ?>" required>
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Rates</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-key me-2"></i>Change Password</div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>System Info</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Application</th><td><?php echo APP_NAME; ?></td></tr>
                    <tr><th>Version</th><td><?php echo APP_VERSION; ?></td></tr>
                    <tr><th>PHP Version</th><td><?php echo PHP_VERSION; ?></td></tr>
                    <tr><th>Base Currency</th><td><?php echo BASE_CURRENCY; ?></td></tr>
                    <tr><th>Supported Currencies</th><td><?php echo implode(', ', CURRENCIES); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
