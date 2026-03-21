<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id         = $_SESSION['user_id'];
$new_username    = trim($_POST['username']);
$new_password    = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

// Validate username
if (empty($new_username)) {
    header("Location: ../index.php?profile_error=Username cannot be empty");
    exit();
}

// Check if username is taken by another user
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$stmt->bind_param("si", $new_username, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: ../index.php?profile_error=Username already taken");
    exit();
}

// Update username
$stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
$stmt->bind_param("si", $new_username, $user_id);
$stmt->execute();
$_SESSION['username'] = $new_username;

// Update password if provided
if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        header("Location: ../index.php?profile_error=Passwords do not match");
        exit();
    }
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $user_id);
    $stmt->execute();
}

header("Location: ../index.php?profile_success=1");
exit();
