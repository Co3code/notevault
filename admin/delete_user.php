<?php
session_start();
include '../config/db.php';

// Only admin can access
if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get user ID from query
if (! isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = intval($_GET['id']);

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    header("Location: admin_dashboard.php?message=You+cannot+delete+yourself");
    exit();
}

// Secure delete using prepared statement
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Redirect back to dashboard with alert
header("Location: admin_dashboard.php?message=User+deleted+successfully");
exit();
