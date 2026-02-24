<?php
session_start();
require '../config/db.php';

// Ensure user is logged in
if (! isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$note_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($note_id > 0) {
    // Soft delete note only if it belongs to the user
    $stmt = $conn->prepare("UPDATE notes SET deleted_at = NOW() WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
}

header("Location: ../index.php");
exit();
