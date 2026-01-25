<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

$id = $_GET['id'] ?? 0;

// Check ownership or admin role
$stmt = $pdo->prepare("SELECT user_id FROM withdrawals WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if ($record) {
    if ($_SESSION['role'] == 'admin' || $record['user_id'] == $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM withdrawals WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: dashboard.php");
exit();
?>