<?php
require_once 'db.php';
requireLogin();

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$user = currentUser();
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getConnection()->prepare("UPDATE users SET full_name = ?, email = ?, department = ?, designation = ?, phone = ?, join_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param('sssssssi', $_POST['full_name'], $_POST['email'], $_POST['department'], $_POST['designation'], $_POST['phone'], $_POST['join_date'], $_POST['status'], $user['id']);
    $stmt->execute();
    $stmt->close();
    $message = 'Profile updated successfully.';
    $messageType = 'success';
    $user = currentUser();
}

$balanceStmt = getConnection()->prepare("SELECT * FROM leave_balances WHERE user_id = ?");
$balanceStmt->bind_param('i', $user['id']);
$balanceStmt->execute();
$balanceRow = $balanceStmt->get_result()->fetch_assoc();
$balanceStmt->close();

if (!$balanceRow) {
    $insertBalance = getConnection()->prepare("INSERT INTO leave_balances (user_id) VALUES (?)");
    $insertBalance->bind_param('i', $user['id']);
    $insertBalance->execute();
    $insertBalance->close();
    $balanceRow = ['annual_balance' => 20, 'casual_balance' => 8, 'medical_balance' => 10];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Staff Portal</title>
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
                <a href="leave_history.php"><i class="fas fa-history"></i> Leave History</a>
                <a href="profile.php" class="active"><i class="fas fa-id-card"></i> Profile</a>
                <a href="change_password.php"><i class="fas fa-lock"></i> Password</a>
                <?php if ($user['role'] === 'admin'): ?><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a><?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Personal Details</p>
                        <h1>Edit Profile</h1>
                        <p>Update your personal and professional information.</p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="portal-grid">
                    <section class="portal-card">
                        <div class="card-title-row">
                            <h2><i class="fas fa-id-card"></i> Profile Information</h2>
                        </div>
                        <form method="post" class="form-grid">
                            <div>
                                <label for="full_name">Full Name</label>
                                <input id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div>
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?: '') ?>">
                            </div>
                            <div>
                                <label for="department">Department</label>
                                <input id="department" name="department" value="<?= htmlspecialchars($user['department'] ?: '') ?>">
                            </div>
                            <div>
                                <label for="designation">Designation</label>
                                <input id="designation" name="designation" value="<?= htmlspecialchars($user['designation'] ?: '') ?>">
                            </div>
                            <div>
                                <label for="phone">Phone</label>
                                <input id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?: '') ?>">
                            </div>
                            <div>
                                <label for="join_date">Join Date</label>
                                <input id="join_date" type="date" name="join_date" value="<?= htmlspecialchars($user['join_date'] ?: '') ?>">
                            </div>
                            <div>
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="Active" <?= $user['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="On Leave" <?= $user['status'] === 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                                    <option value="Transferred" <?= $user['status'] === 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                                </select>
                            </div>
                            <div class="full-width">
                                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Profile</button>
                            </div>
                        </form>
                    </section>

                    <section class="portal-card">
                        <div class="card-title-row">
                            <h2><i class="fas fa-chart-pie"></i> Leave Balances</h2>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?= (int)($balanceRow['annual_balance'] ?? 0) ?></h3>
                                <p>Annual</p>
                            </div>
                            <div class="stat-card">
                                <h3><?= (int)($balanceRow['casual_balance'] ?? 0) ?></h3>
                                <p>Casual</p>
                            </div>
                            <div class="stat-card">
                                <h3><?= (int)($balanceRow['medical_balance'] ?? 0) ?></h3>
                                <p>Medical</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
