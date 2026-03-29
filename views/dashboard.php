<?php
// dashboard.php - Financial Metrics Overview for Multi-Tenant System

// Fetch financial metrics
$tenants = getTenants();  // Function to get all tenants
$financialData = [];

foreach ($tenants as $tenant) {
    $financialData[$tenant['id']] = getFinancialMetrics($tenant['id']); // Function to get financial metrics per tenant
}

// Display overview
?>

<html>
<head>
    <title>Financial Metrics Dashboard</title>
</head>
<body>
    <h1>Financial Metrics Overview</h1>
    <table border='1'>
        <tr>
            <th>Tenant ID</th>
            <th>Revenue</th>
            <th>Expenses</th>
            <th>Profit</th>
        </tr>
        <?php
        foreach ($financialData as $id => $data) {
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . number_format($data['revenue'], 2) . '</td>';
            echo '<td>' . number_format($data['expenses'], 2) . '</td>';
            echo '<td>' . number_format($data['profit'], 2) . '</td>';
            echo '</tr>';
        }
        ?>
    </table>
</body>
</html>