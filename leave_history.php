<?php
require_once 'db.php';
requireLogin();

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$user = currentUser();
$connection = getConnection();

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : $user['id'];
$showAll = ($user['role'] === 'admin');

if (!$showAll && $employeeId !== (int)$user['id']) {
    header('Location: dashboard.php');
    exit;
}

$employeeStmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$employeeStmt->bind_param('i', $employeeId);
$employeeStmt->execute();
$employee = $employeeStmt->get_result()->fetch_assoc();
$employeeStmt->close();

$historyStmt = $connection->prepare("SELECT lr.*, lrh.changed_at, lrh.new_status, lrh.old_status, lrh.note FROM leave_requests lr LEFT JOIN leave_history lrh ON lr.id = lrh.leave_id WHERE lr.user_id = ? ORDER BY lr.start_date DESC");
$historyStmt->bind_param('i', $employeeId);
$historyStmt->execute();
$historyRows = $historyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$historyStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="portal-shell">
        <div class="dashboard-layout">
            <aside class="sidebar">
                <div class="brand"><i class="fas fa-landmark"></i> HR Portal</div>
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <?php if ($user['role'] === 'admin'): ?><a href="employee_management.php"><i class="fas fa-user-tie"></i> Employees</a><?php endif; ?>
                <a href="leave_history.php" class="active"><i class="fas fa-history"></i> Leave History</a>
                <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
                <a href="change_password.php"><i class="fas fa-lock"></i> Password</a>
                <?php if ($user['role'] === 'admin'): ?><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a><?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Employee Records</p>
                        <h1>Leave History</h1>
                        <p><?= htmlspecialchars($employee['full_name'] ?? 'Employee') ?></p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>

                <section class="portal-card">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Dates</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyRows as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                        <td><?= htmlspecialchars($row['start_date']) ?> to <?= htmlspecialchars($row['end_date']) ?></td>
                                        <td><?= htmlspecialchars($row['reason']) ?></td>
                                        <td><span class="status-pill status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                        <td><?= htmlspecialchars($row['changed_at'] ?: '—') ?></td>
                                        <td><?= htmlspecialchars($row['note'] ?: '—') ?></td>
                                    </tr>
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
