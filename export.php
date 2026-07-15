<?php
require_once 'db.php';
requireLogin();

try {
    ensureDatabaseSchema();
} catch (Throwable $e) {
    die('Database setup failed: ' . htmlspecialchars($e->getMessage()));
}

$connection = getConnection();
$format = $_GET['format'] ?? 'csv';
$type = $_GET['type'] ?? 'leave';

if ($type === 'leave') {
    $query = "SELECT lr.id, u.full_name, lr.leave_type, lr.start_date, lr.end_date, lr.reason, lr.status FROM leave_requests lr JOIN users u ON lr.user_id = u.id ORDER BY lr.created_at DESC";
    $result = $connection->query($query);
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    if ($format === 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="leave_requests.pdf"');
        echo "PDF export is ready for integration with a PDF library.\n";
        exit;
    }

    $filename = 'leave_requests.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Employee', 'Leave Type', 'Start Date', 'End Date', 'Reason', 'Status']);
    foreach ($rows as $row) {
        fputcsv($output, [$row['id'], $row['full_name'], $row['leave_type'], $row['start_date'], $row['end_date'], $row['reason'], $row['status']]);
    }
    fclose($output);
    exit;
}

header('Location: dashboard.php');
exit;
