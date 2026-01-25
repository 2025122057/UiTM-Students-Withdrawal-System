<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

// Restricted to admin only
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

// Validate status
$allowed_statuses = ['Pending', 'Approved', 'Rejected'];
if (in_array($status, $allowed_statuses) && $id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Redirect back with success message (simplified)
        header("Location: admin_dashboard.php?msg=Status updated to $status");
        exit();
    } catch (PDOException $e) {
        die("Error updating status: " . $e->getMessage());
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>