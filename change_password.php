<?php
require_once 'db.php';
requireLogin();

$user = currentUser();
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword === '' || $confirmPassword === '') {
        $message = 'Please provide a new password.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match.';
        $messageType = 'error';
    } elseif (!password_verify($currentPassword, $user['password_hash'])) {
        $message = 'Current password is incorrect.';
        $messageType = 'error';
    } else {
        $connection = getConnection();
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param('si', $newHash, $user['id']);
        $stmt->execute();
        $stmt->close();
        $message = 'Password updated successfully.';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
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
                <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
                <a href="change_password.php" class="active"><i class="fas fa-lock"></i> Password</a>
                <?php if ($user['role'] === 'admin'): ?><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a><?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </aside>

            <div class="main-panel">
                <div class="portal-header">
                    <div>
                        <p class="portal-eyebrow">Account Settings</p>
                        <h1>Change Password</h1>
                        <p>Keep your account secure by updating your password regularly.</p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <section class="portal-card">
                    <form method="post" class="form-grid">
                        <div class="full-width">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" type="password" name="current_password" required>
                        </div>
                        <div>
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="new_password" required>
                        </div>
                        <div>
                            <label for="confirm_password">Confirm New Password</label>
                            <input id="confirm_password" type="password" name="confirm_password" required>
                        </div>
                        <div class="full-width">
                            <button type="submit" class="btn-primary"><i class="fas fa-lock"></i> Update Password</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
