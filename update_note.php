<?php
session_start();
include "db.php";

// Ensure user is logged in
if (! isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check POST data
if (isset($_POST['id'], $_POST['title'], $_POST['content'])) {
    $id      = (int) $_POST['id'];
    $title   = $_POST['title'];   // keep HTML safe as-is
    $content = $_POST['content']; // Summernote HTML content

    // Update note only if it belongs to the logged-in user and is not deleted
    $stmt = $conn->prepare("
        UPDATE notes
        SET title = ?, content = ?
        WHERE id = ? AND user_id = ? AND deleted_at IS NULL
    ");
    $stmt->bind_param("ssii", $title, $content, $id, $user_id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        die("Error updating note: " . $stmt->error);
    }
} else {
    header("Location: index.php");
    exit();
}
