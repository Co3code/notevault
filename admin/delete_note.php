<?php
session_start();
include '../config/db.php';

// Only admin can access
if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get note ID from query
if (! isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$note_id = intval($_GET['id']);

// Secure delete using prepared statement
$stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
$stmt->bind_param("i", $note_id);
$stmt->execute();
$stmt->close();

header("Location: admin_dashboard.php");
exit();
