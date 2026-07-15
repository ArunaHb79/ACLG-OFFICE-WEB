<?php
$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$staffFile = $dataDir . '/staff.json';
$leaveFile = $dataDir . '/leaves.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

function loadJsonFile($path, $default = []) {
    if (!file_exists($path)) {
        return $default;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return $default;
    }

    $decoded = json_decode($contents, true);
    return is_array($decoded) ? $decoded : $default;
}

function saveJsonFile($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$staffMembers = loadJsonFile($staffFile, []);
$leaveRequests = loadJsonFile($leaveFile, []);

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_staff') {
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $joinDate = trim($_POST['join_date'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');

        if ($name === '' || $designation === '' || $department === '') {
            $message = 'Please fill in the required staff details.';
            $messageType = 'error';
        } else {
            $staffMembers[] = [
                'id' => 'ST' . str_pad((count($staffMembers) + 1), 3, '0', STR_PAD_LEFT),
                'name' => $name,
                'designation' => $designation,
                'department' => $department,
                'email' => $email,
                'phone' => $phone,
                'join_date' => $joinDate,
                'status' => $status
            ];

            saveJsonFile($staffFile, $staffMembers);
            $message = 'Staff member added successfully.';
            $messageType = 'success';
        }
    } elseif ($action === 'add_leave') {
        $staffId = trim($_POST['staff_id'] ?? '');
        $leaveType = trim($_POST['leave_type'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($staffId === '' || $leaveType === '' || $startDate === '' || $endDate === '' || $reason === '') {
            $message = 'Please complete all leave request fields.';
            $messageType = 'error';
        } else {
            $leaveRequests[] = [
                'id' => 'LV' . str_pad((count($leaveRequests) + 1), 3, '0', STR_PAD_LEFT),
                'staff_id' => $staffId,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reason,
                'status' => 'Pending'
            ];

            saveJsonFile($leaveFile, $leaveRequests);
            $message = 'Leave request submitted successfully.';
            $messageType = 'success';
        }
    } elseif ($action === 'update_leave') {
        $leaveId = trim($_POST['leave_id'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');

        foreach ($leaveRequests as &$request) {
            if (($request['id'] ?? '') === $leaveId) {
                $request['status'] = $status;
                break;
            }
        }

        saveJsonFile($leaveFile, $leaveRequests);
        $message = 'Leave request updated.';
        $messageType = 'success';
    }
}

$staffMembers = array_values($staffMembers);
$leaveRequests = array_values($leaveRequests);

$staffById = [];
foreach ($staffMembers as $staff) {
    $staffById[$staff['id']] = $staff;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff & Leave Management Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="portal-shell">
        <div class="portal-header">
            <div>
                <p class="portal-eyebrow">Internal Office Portal</p>
                <h1>Staff Management & Leave Management</h1>
                <p>Manage personnel records, maintain leave requests, and review employee status from one place.</p>
            </div>
            <a href="establishment.html" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Establishment</a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="portal-grid">
            <section class="portal-card">
                <div class="card-title-row">
                    <h2><i class="fas fa-user-plus"></i> Add Staff Member</h2>
                </div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="add_staff">
                    <div>
                        <label for="name">Full Name</label>
                        <input id="name" name="name" required>
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
                        <label for="status">Employment Status</label>
                        <select id="status" name="status">
                            <option value="Active">Active</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Transferred">Transferred</option>
                        </select>
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
                        <label for="join_date">Joined Date</label>
                        <input id="join_date" type="date" name="join_date">
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Staff</button>
                    </div>
                </form>
            </section>

            <section class="portal-card">
                <div class="card-title-row">
                    <h2><i class="fas fa-calendar-plus"></i> Submit Leave Request</h2>
                </div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="add_leave">
                    <div>
                        <label for="staff_id">Select Staff</label>
                        <select id="staff_id" name="staff_id" required>
                            <option value="">Choose employee</option>
                            <?php foreach ($staffMembers as $staff): ?>
                                <option value="<?= htmlspecialchars($staff['id']) ?>"><?= htmlspecialchars($staff['name']) ?> (<?= htmlspecialchars($staff['id']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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

        <section class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-users"></i> Staff Directory</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staffMembers)): ?>
                            <tr><td colspan="6">No staff members yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staffMembers as $staff): ?>
                                <tr>
                                    <td><?= htmlspecialchars($staff['id']) ?></td>
                                    <td><?= htmlspecialchars($staff['name']) ?></td>
                                    <td><?= htmlspecialchars($staff['designation']) ?></td>
                                    <td><?= htmlspecialchars($staff['department']) ?></td>
                                    <td><span class="status-pill"><?= htmlspecialchars($staff['status']) ?></span></td>
                                    <td><?= htmlspecialchars($staff['join_date'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="portal-card">
            <div class="card-title-row">
                <h2><i class="fas fa-clipboard-list"></i> Leave Requests</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaveRequests)): ?>
                            <tr><td colspan="6">No leave requests yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaveRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['id']) ?></td>
                                    <td><?= htmlspecialchars($staffById[$request['staff_id']]['name'] ?? $request['staff_id']) ?></td>
                                    <td><?= htmlspecialchars($request['leave_type']) ?></td>
                                    <td><?= htmlspecialchars($request['start_date']) ?> to <?= htmlspecialchars($request['end_date']) ?></td>
                                    <td><span class="status-pill status-<?= strtolower($request['status']) ?>"><?= htmlspecialchars($request['status']) ?></span></td>
                                    <td>
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
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
