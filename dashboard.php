<?php
require_once 'db.php';
require_once 'mail.php';
requireLogin();

try {
    $connection = ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$user = currentUser();
if (!$user) {
    header('Location: logout.php');
    exit;
}

$isAdmin = ($user['role'] === 'admin');

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_staff') {
        if (!$isAdmin) {
            $message = 'Only administrators can add staff members.';
            $messageType = 'error';
        } else {
            $stmt = $connection->prepare("INSERT INTO users (username, password_hash, role, full_name, email, department, designation, phone, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $passwordHash = password_hash('welcome123', PASSWORD_DEFAULT);
            $role = 'staff';
            $status = 'Active';
            $stmt->bind_param('ssssssssss', $_POST['username'], $passwordHash, $role, $_POST['full_name'], $_POST['email'], $_POST['department'], $_POST['designation'], $_POST['phone'], $_POST['join_date'], $status);
            $stmt->execute();
            $stmt->close();
            $message = 'Staff account created successfully.';
            $messageType = 'success';
        }
    } elseif ($action === 'add_leave') {
        $stmt = $connection->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param('issss', $user['id'], $_POST['leave_type'], $_POST['start_date'], $_POST['end_date'], $_POST['reason']);
        $stmt->execute();
        $stmt->close();
        $message = 'Leave request submitted successfully.';
        $messageType = 'success';
    } elseif ($action === 'update_leave') {
        if (!$isAdmin) {
            $message = 'Only administrators can change leave status.';
            $messageType = 'error';
        } else {
            $leaveId = (int)($_POST['leave_id'] ?? 0);
            $newStatus = $_POST['status'] ?? 'Pending';
            $currentStatusStmt = $connection->prepare("SELECT status FROM leave_requests WHERE id = ?");
            $currentStatusStmt->bind_param('i', $leaveId);
            $currentStatusStmt->execute();
            $currentStatusResult = $currentStatusStmt->get_result();
            $currentRow = $currentStatusResult->fetch_assoc();
            $currentStatusStmt->close();
            $oldStatus = $currentRow['status'] ?? 'Pending';

            $stmt = $connection->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $newStatus, $leaveId);
            $stmt->execute();
            $stmt->close();

            if ($newStatus === 'Approved') {
                $leaveRequestStmt = $connection->prepare("SELECT user_id, leave_type, start_date, end_date FROM leave_requests WHERE id = ?");
                $leaveRequestStmt->bind_param('i', $leaveId);
                $leaveRequestStmt->execute();
                $leaveRequestRow = $leaveRequestStmt->get_result()->fetch_assoc();
                $leaveRequestStmt->close();

                if ($leaveRequestRow) {
                    $days = (int) ((strtotime($leaveRequestRow['end_date']) - strtotime($leaveRequestRow['start_date'])) / 86400) + 1;
                    $balanceStmt = $connection->prepare("SELECT * FROM leave_balances WHERE user_id = ?");
                    $balanceStmt->bind_param('i', $leaveRequestRow['user_id']);
                    $balanceStmt->execute();
                    $balanceRow = $balanceStmt->get_result()->fetch_assoc();
                    $balanceStmt->close();

                    if (!$balanceRow) {
                        $createBalanceStmt = $connection->prepare("INSERT INTO leave_balances (user_id) VALUES (?)");
                        $createBalanceStmt->bind_param('i', $leaveRequestRow['user_id']);
                        $createBalanceStmt->execute();
                        $createBalanceStmt->close();
                        $balanceRow = ['annual_balance' => 20, 'casual_balance' => 8, 'medical_balance' => 10];
                    }

                    $column = null;
                    if ($leaveRequestRow['leave_type'] === 'Annual') {
                        $column = 'annual_balance';
                    } elseif ($leaveRequestRow['leave_type'] === 'Casual') {
                        $column = 'casual_balance';
                    } elseif ($leaveRequestRow['leave_type'] === 'Medical') {
                        $column = 'medical_balance';
                    }

                    if ($column) {
                        $updateBalanceStmt = $connection->prepare("UPDATE leave_balances SET $column = GREATEST(0, $column - ?) WHERE user_id = ?");
                        $updateBalanceStmt->bind_param('ii', $days, $leaveRequestRow['user_id']);
                        $updateBalanceStmt->execute();
                        $updateBalanceStmt->close();
                    }
                }
            }

            $note = 'Status changed by admin';
            $historyStmt = $connection->prepare("INSERT INTO leave_history (leave_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)");
            $historyStmt->bind_param('iisss', $leaveId, $user['id'], $oldStatus, $newStatus, $note);
            $historyStmt->execute();
            $historyStmt->close();

            if ($newStatus === 'Approved' || $newStatus === 'Rejected') {
                $leaveUserStmt = $connection->prepare("SELECT full_name, email FROM users WHERE id = (SELECT user_id FROM leave_requests WHERE id = ?)");
                $leaveUserStmt->bind_param('i', $leaveId);
                $leaveUserStmt->execute();
                $leaveUserRow = $leaveUserStmt->get_result()->fetch_assoc();
                $leaveUserStmt->close();
                if ($leaveUserRow && !empty($leaveUserRow['email'])) {
                    sendApprovalEmail($leaveUserRow['email'], $leaveUserRow['full_name'], 'leave', $newStatus, $note);
                }
            }

            $message = 'Leave status updated.';
            $messageType = 'success';
        }
    }
}

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';
$staffSearch = trim($_GET['staff_search'] ?? '');
$staffStatusFilter = $_GET['staff_status'] ?? 'all';

$staffQuery = "SELECT * FROM users WHERE 1=1";
$paramsStaff = [];
if ($staffSearch !== '') {
    $staffQuery .= " AND (full_name LIKE ? OR username LIKE ? OR department LIKE ?)";
    $like = '%' . $staffSearch . '%';
    $paramsStaff = [$like, $like, $like];
}
if ($staffStatusFilter !== 'all') {
    $staffQuery .= " AND status = ?";
    $paramsStaff[] = $staffStatusFilter;
}
$staffQuery .= " ORDER BY full_name";

$staffStmt = $connection->prepare($staffQuery);
if (!empty($paramsStaff)) {
    $staffTypes = str_repeat('s', count($paramsStaff));
    $staffStmt->bind_param($staffTypes, ...$paramsStaff);
}
$staffStmt->execute();
$staffMembers = $staffStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$staffStmt->close();

$leaveQuery = "SELECT lr.*, u.full_name, u.email FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE 1=1";
$params = [];
if ($search !== '') {
    $leaveQuery .= " AND (u.full_name LIKE ? OR lr.leave_type LIKE ? OR lr.reason LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}
if ($statusFilter !== 'all') {
    $leaveQuery .= " AND lr.status = ?";
    $params[] = $statusFilter;
}
$leaveQuery .= " ORDER BY lr.created_at DESC";

$leaveStmt = $connection->prepare($leaveQuery);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $leaveStmt->bind_param($types, ...$params);
}
$leaveStmt->execute();
$leaveRequests = $leaveStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$leaveStmt->close();

$historyResult = $connection->query("SELECT lh.*, u.full_name AS changed_by_name FROM leave_history lh LEFT JOIN users u ON lh.changed_by = u.id ORDER BY lh.changed_at DESC");
$leaveHistory = $historyResult ? $historyResult->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Staff Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="portal-shell">
        <div class="dashboard-layout">
            <aside class="sidebar">
                <div class="brand"><i class="fas fa-landmark"></i> HR Portal</div>
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <?php if ($isAdmin): ?><a href="employee_management.php"><i class="fas fa-user-tie"></i> Employees</a><?php endif; ?>
                <a href="leave_history.php"><i class="fas fa-history"></i> Leave History</a>
                <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
                <a href="change_password.php"><i class="fas fa-lock"></i> Password</a>
                <?php if ($isAdmin): ?><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a><?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Protected Dashboard</p>
                        <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>
                        <p>Role: <?= htmlspecialchars(ucfirst($user['role'])) ?> • Department: <?= htmlspecialchars($user['department'] ?: '—') ?></p>
                    </div>
                    <div class="header-actions">
                        <a href="logout.php" class="btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="stats-grid">
            <div class="stat-card">
                <h3><?= count($staffMembers) ?></h3>
                <p>Total Accounts</p>
            </div>
            <div class="stat-card">
                <h3><?= count($leaveRequests) ?></h3>
                <p>Leave Requests</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($leaveRequests, fn($item) => $item['status'] === 'Approved')) ?></h3>
                <p>Approved Leaves</p>
            </div>
        </div>

        <div class="portal-grid">
            <?php if ($isAdmin): ?>
                <section class="portal-card">
                    <div class="card-title-row">
                        <h2><i class="fas fa-user-plus"></i> Add Staff Account</h2>
                    </div>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="add_staff">
                        <div>
                            <label for="username">Username</label>
                            <input id="username" name="username" required>
                        </div>
                        <div>
                            <label for="full_name">Full Name</label>
                            <input id="full_name" name="full_name" required>
                        </div>
                        <div>
                            <label for="designation">Designation</label>
                            <input id="designation" name="designation" required>
                        </div>
                        <div>
                            <label for="department">Department</label>
                            <input id="department" name="department" required>
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email">
                        </div>
                        <div>
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone">
                        </div>
                        <div>
                            <label for="join_date">Join Date</label>
                            <input id="join_date" type="date" name="join_date">
                        </div>
                        <div class="full-width">
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Account</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <section class="portal-card">
                <div class="card-title-row">
                    <h2><i class="fas fa-calendar-plus"></i> Submit Leave Request</h2>
                </div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="add_leave">
                    <div>
                        <label for="leave_type">Leave Type</label>
                        <select id="leave_type" name="leave_type" required>
                            <option value="Annual">Annual Leave</option>
                            <option value="Medical">Medical Leave</option>
                            <option value="Casual">Casual Leave</option>
                            <option value="Special">Special Leave</option>
                        </select>
                    </div>
                    <div>
                        <label for="start_date">Start Date</label>
                        <input id="start_date" type="date" name="start_date" required>
                    </div>
                    <div>
                        <label for="end_date">End Date</label>
                        <input id="end_date" type="date" name="end_date" required>
                    </div>
                    <div class="full-width">
                        <label for="reason">Reason</label>
                        <textarea id="reason" name="reason" rows="4" required></textarea>
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Submit Leave</button>
                    </div>
                </form>
            </section>
        </div>

        <section id="staff" class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-users"></i> Staff Accounts</h2>
            </div>
            <form method="get" class="filter-form">
                <input type="text" name="staff_search" value="<?= htmlspecialchars($staffSearch) ?>" placeholder="Search staff by name or department">
                <select name="staff_status">
                    <option value="all" <?= $staffStatusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="Active" <?= $staffStatusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="On Leave" <?= $staffStatusFilter === 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                    <option value="Transferred" <?= $staffStatusFilter === 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                </select>
                <button type="submit" class="btn-secondary small">Filter</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffMembers as $staff): ?>
                            <tr>
                                <td><?= htmlspecialchars($staff['full_name']) ?></td>
                                <td><?= htmlspecialchars($staff['username']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($staff['role'])) ?></td>
                                <td><?= htmlspecialchars($staff['department'] ?: '—') ?></td>
                                <td><span class="status-pill"><?= htmlspecialchars($staff['status']) ?></span></td>
                                <td><a href="leave_history.php?employee_id=<?= (int)$staff['id'] ?>" class="btn-secondary small">History</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="leave" class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-clipboard-list"></i> Leave Requests</h2>
            </div>
            <div class="export-links">
                <a href="export.php?type=leave&format=csv" class="btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</a>
                <a href="export.php?type=leave&format=pdf" class="btn-secondary"><i class="fas fa-file-pdf"></i> Export PDF</a>
            </div>
            <form method="get" class="filter-form">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search employee or reason">
                <select name="status">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                <button type="submit" class="btn-secondary small">Filter</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaveRequests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['full_name']) ?></td>
                                <td><?= htmlspecialchars($request['leave_type']) ?></td>
                                <td><?= htmlspecialchars($request['start_date']) ?> to <?= htmlspecialchars($request['end_date']) ?></td>
                                <td><?= htmlspecialchars($request['reason']) ?></td>
                                <td><span class="status-pill status-<?= strtolower($request['status']) ?>"><?= htmlspecialchars($request['status']) ?></span></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="action" value="update_leave">
                                            <input type="hidden" name="leave_id" value="<?= htmlspecialchars($request['id']) ?>">
                                            <select name="status">
                                                <option value="Pending" <?= $request['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Approved" <?= $request['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                <option value="Rejected" <?= $request['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                            </select>
                                            <button type="submit" class="btn-secondary small">Update</button>
                                        </form>
                                    <?php else: ?>
                                        <span>Awaiting review</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-chart-bar"></i> Leave Trend Snapshot</h2>
            </div>
            <?php
            $trendQuery = $connection->query("SELECT DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as total FROM leave_requests GROUP BY month ORDER BY month");
            $trendRows = $trendQuery ? $trendQuery->fetch_all(MYSQLI_ASSOC) : [];
            $maxTrend = !empty($trendRows) ? max(array_column($trendRows, 'total')) : 0;
            ?>
            <?php if (!empty($trendRows)): ?>
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
                        <tr><th>Month</th><th>Leave Requests</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trendRows as $trend): ?>
                            <tr><td><?= htmlspecialchars($trend['month']) ?></td><td><?= htmlspecialchars($trend['total']) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-history"></i> Leave Approval History</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Leave ID</th>
                            <th>Changed By</th>
                            <th>Old Status</th>
                            <th>New Status</th>
                            <th>Note</th>
                            <th>Changed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaveHistory as $history): ?>
                            <tr>
                                <td><?= htmlspecialchars($history['leave_id']) ?></td>
                                <td><?= htmlspecialchars($history['changed_by_name'] ?? 'System') ?></td>
                                <td><?= htmlspecialchars($history['old_status']) ?></td>
                                <td><?= htmlspecialchars($history['new_status']) ?></td>
                                <td><?= htmlspecialchars($history['note'] ?: '—') ?></td>
                                <td><?= htmlspecialchars($history['changed_at']) ?></td>
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
