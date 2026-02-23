<?php
session_start();
include "db.php";

// Ensure user is logged in
if (! isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['title'], $_POST['content'])) {
    $title   = $_POST['title']; // Summernote HTML allowed
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    // Insert note using prepared statement
    $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        die("Error saving note: " . $stmt->error);
    }
} else {
    header("Location: index.php");
    exit();
}
