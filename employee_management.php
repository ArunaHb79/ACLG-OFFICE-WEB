<?php
require_once 'db.php';
requireLogin();

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$user = currentUser();
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$connection = getConnection();
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_employee') {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $joinDate = trim($_POST['join_date'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');
        if ($username !== '' && $fullName !== '') {
            $passwordHash = password_hash('welcome123', PASSWORD_DEFAULT);
            $stmt = $connection->prepare("INSERT INTO users (username, password_hash, role, full_name, email, department, designation, phone, join_date, status) VALUES (?, ?, 'staff', ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $username, $passwordHash, $fullName, $email, $department, $designation, $phone, $joinDate, $status);
            $stmt->execute();
            $stmt->close();
            $message = 'Employee created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Please provide a username and full name.';
            $messageType = 'error';
        }
    }
}

$employeeSearch = trim($_GET['employee_search'] ?? '');
$employeeStmt = $connection->prepare("SELECT * FROM users WHERE role = 'staff' AND (full_name LIKE ? OR username LIKE ? OR department LIKE ?) ORDER BY full_name");
$like = '%' . $employeeSearch . '%';
$employeeStmt->bind_param('sss', $like, $like, $like);
$employeeStmt->execute();
$employees = $employeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$employeeStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-shell">
        <div class="dashboard-layout">
            <aside class="sidebar">
                <div class="brand"><i class="fas fa-users-cog"></i> HR Portal</div>
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="employee_management.php" class="active"><i class="fas fa-user-tie"></i> Employees</a>
                <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="change_password.php"><i class="fas fa-lock"></i> Password</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Employee Management</p>
                        <h1>Manage Staff Records</h1>
                        <p>Add and review employee details from one place.</p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <section class="portal-card">
                    <div class="card-title-row">
                        <h2><i class="fas fa-user-plus"></i> Create Employee</h2>
                    </div>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="create_employee">
                        <div><label for="username">Username</label><input id="username" name="username" required></div>
                        <div><label for="full_name">Full Name</label><input id="full_name" name="full_name" required></div>
                        <div><label for="email">Email</label><input id="email" type="email" name="email"></div>
                        <div><label for="department">Department</label><input id="department" name="department"></div>
                        <div><label for="designation">Designation</label><input id="designation" name="designation"></div>
                        <div><label for="phone">Phone</label><input id="phone" name="phone"></div>
                        <div><label for="join_date">Join Date</label><input id="join_date" type="date" name="join_date"></div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Transferred">Transferred</option>
                            </select>
                        </div>
                        <div class="full-width"><button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Employee</button></div>
                    </form>
                </section>

                <section class="portal-card">
                    <div class="card-title-row">
                        <h2><i class="fas fa-list"></i> Employee Directory</h2>
                    </div>
                    <form method="get" class="filter-form">
                        <input type="text" name="employee_search" value="<?= htmlspecialchars($employeeSearch) ?>" placeholder="Search by name, username or department">
                        <button type="submit" class="btn-secondary small">Search</button>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($employee['full_name']) ?></td>
                                        <td><?= htmlspecialchars($employee['username']) ?></td>
                                        <td><?= htmlspecialchars($employee['department'] ?: '—') ?></td>
                                        <td><?= htmlspecialchars($employee['designation'] ?: '—') ?></td>
                                        <td><span class="status-pill"><?= htmlspecialchars($employee['status']) ?></span></td>
                                        <td><a href="leave_history.php?employee_id=<?= (int)$employee['id'] ?>" class="btn-secondary small">History</a></td>
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
