<?php
require_once 'db.php';

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
        $messageType = 'error';
    } else {
        $connection = getConnection();
        $stmt = $connection->prepare("SELECT id, username, password_hash, role, full_name FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit;
        }

        $message = 'Invalid username or password.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Staff Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-hero">
            <div class="brand-pill"><i class="fas fa-landmark"></i> ACLG HR Portal</div>
            <h1>Modern staff operations in one place</h1>
            <p>Track leave requests, update employee records, and review approvals with a cleaner and faster experience.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Secure staff sign-in</li>
                <li><i class="fas fa-check-circle"></i> Centralized leave workflow</li>
                <li><i class="fas fa-check-circle"></i> Role-based admin controls</li>
            </ul>
        </div>
        <div class="auth-card">
            <div class="auth-card-inner">
                <div class="auth-card-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to access the staff portal.</p>
                </div>
                <?php if (!empty($dbError)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($dbError) ?></div>
                <?php endif; ?>
                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <form method="post" class="auth-form">
                    <label for="username">Username</label>
                    <input id="username" name="username" required>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                    <button type="submit" class="btn-primary">Login</button>
                </form>
                <p class="auth-note">Default admin login: username <strong>admin</strong> password <strong>admin123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
