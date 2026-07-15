<?php
require_once 'db.php';
requireLogin();

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$connection = getConnection();
$user = currentUser();

if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$reportStmt = $connection->query("SELECT leave_type, status, COUNT(*) as total FROM leave_requests GROUP BY leave_type, status ORDER BY leave_type, status");
$reportRows = $reportStmt ? $reportStmt->fetch_all(MYSQLI_ASSOC) : [];

$trendStmt = $connection->query("SELECT DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as total FROM leave_requests GROUP BY month ORDER BY month");
$trendRows = $trendStmt ? $trendStmt->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Reports</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="portal-shell">
        <div class="dashboard-layout">
            <aside class="sidebar">
                <div class="brand"><i class="fas fa-landmark"></i> HR Portal</div>
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="employee_management.php"><i class="fas fa-user-tie"></i> Employees</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> Leave History</a>
                <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
                <a href="change_password.php"><i class="fas fa-lock"></i> Password</a>
                <a href="reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Analytics</p>
                        <h1>Leave Reports</h1>
                        <p>Printable overview of leave activity and trends.</p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>

                <section class="portal-card">
                    <div class="export-links">
                        <a href="reports.php" class="btn-secondary"><i class="fas fa-print"></i> Print</a>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?= count($reportRows) ?></h3>
                            <p>Leave Entries</p>
                        </div>
                        <div class="stat-card">
                            <h3><?= count($trendRows) ?></h3>
                            <p>Monthly Periods</p>
                        </div>
                    </div>

                    <h3>Leave Summary</h3>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Leave Type</th><th>Status</th><th>Count</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportRows as $row): ?>
                                    <tr><td><?= htmlspecialchars($row['leave_type']) ?></td><td><?= htmlspecialchars($row['status']) ?></td><td><?= htmlspecialchars($row['total']) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h3 style="margin-top: 18px;">Leave Trend</h3>
                    <?php if (!empty($trendRows)): ?>
                        <?php $maxTrend = max(array_column($trendRows, 'total')); ?>
                        <svg class="chart-svg" viewBox="0 0 420 220" role="img" aria-label="Leave trend chart">
                            <line x1="30" y1="190" x2="390" y2="190" class="chart-axis"></line>
                            <line x1="30" y1="20" x2="30" y2="190" class="chart-axis"></line>
                            <?php foreach ($trendRows as $index => $trend): ?>
                                <?php $barHeight = $maxTrend > 0 ? (int)(($trend['total'] / $maxTrend) * 130) : 0; ?>
                                <?php $x = 50 + ($index * 60); ?>
                                <rect x="<?= $x ?>" y="<?= 190 - $barHeight ?>" width="30" height="<?= $barHeight ?>" class="chart-bar"></rect>
                                <text x="<?= $x + 15 ?>" y="205" text-anchor="middle" class="chart-label"><?= htmlspecialchars(substr($trend['month'], 5)) ?></text>
                                <text x="<?= $x + 15 ?>" y="<?= 182 - $barHeight ?>" text-anchor="middle" class="chart-value"><?= htmlspecialchars($trend['total']) ?></text>
                            <?php endforeach; ?>
                        </svg>
                    <?php else: ?>
                        <p>No trend data yet.</p>
                    <?php endif; ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Month</th><th>Total Leave Requests</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trendRows as $row): ?>
                                    <tr><td><?= htmlspecialchars($row['month']) ?></td><td><?= htmlspecialchars($row['total']) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
