<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
    <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="text-muted small">Provider Outstanding</div>
                    <div class="fw-bold fs-5 <?php echo $stats['provider_balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?php echo formatMoney($stats['provider_balance']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="text-muted small">Client Outstanding</div>
                    <div class="fw-bold fs-5 <?php echo $stats['client_balance'] > 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo formatMoney($stats['client_balance']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Expenses</div>
                    <div class="fw-bold fs-5"><?php echo formatMoney($stats['total_expenses']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Salaries Paid</div>
                    <div class="fw-bold fs-5"><?php echo formatMoney($stats['total_salaries']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Recent Provider Transactions -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-building me-2 text-primary"></i>Recent Provider Transactions</span>
                <a href="<?php echo BASE_URL; ?>/index.php?page=providers" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Provider</th><th>Date</th><th>Type</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentProviderTx)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No transactions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentProviderTx as $tx): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tx['provider_name']); ?></td>
                                <td class="text-muted"><?php echo formatDate($tx['transaction_date']); ?></td>
                                <td><span class="badge badge-<?php echo $tx['type']; ?>"><?php echo ucfirst($tx['type']); ?></span></td>
                                <td class="text-end fw-semibold"><?php echo formatMoney($tx['base_amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Client Transactions -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2 text-success"></i>Recent Client Transactions</span>
                <a href="<?php echo BASE_URL; ?>/index.php?page=clients" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Client</th><th>Date</th><th>Type</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentClientTx)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No transactions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentClientTx as $tx): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tx['client_name']); ?></td>
                                <td class="text-muted"><?php echo formatDate($tx['transaction_date']); ?></td>
                                <td><span class="badge badge-<?php echo $tx['type']; ?>"><?php echo ucfirst($tx['type']); ?></span></td>
                                <td class="text-end fw-semibold"><?php echo formatMoney($tx['base_amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Items -->
<?php if (!empty($overdueItems)): ?>
<div class="card">
    <div class="card-header text-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>Overdue Items (<?php echo count($overdueItems); ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Party</th><th>Source</th><th>Description</th><th>Due Date</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                <?php foreach ($overdueItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['party_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo ucfirst($item['source']); ?></span></td>
                    <td class="text-muted"><?php echo htmlspecialchars($item['description'] ?? '-'); ?></td>
                    <td class="text-danger"><?php echo formatDate($item['due_date']); ?></td>
                    <td class="text-end fw-semibold text-danger"><?php echo formatMoney($item['base_amount']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
