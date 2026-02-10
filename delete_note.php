<?php
 include "db.php";

// $id = $_GET['id'];
//  mysqli_query($conn, "DELETE FROM notes WHERE id=$id");

// soft delete
// mysqli_query($conn, "UPDATE notes SET deleted_at = NOW() WHERE id = $id");

// header("Location: index.php");
// exit;
//  /Implement soft delete for notes

//  replacing the old hard delete and simple soft delete code with a safer prepared statement
// Old code (commented out) used mysqli_query directly:
// - Hard delete: removed the note permanently
// - Soft delete: updated deleted_at without prepared statement (vulnerable to SQL injection)

$id = $_GET['id'];

// soft delete with prepared statement
$stmt = mysqli_prepare($conn, "UPDATE notes SET deleted_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;

// New code uses a prepared statement to safely mark the note as deleted (soft delete):
// - The note stays in the database but is marked as deleted by setting deleted_at
// - Protects against SQL injection by binding parameters but this is only my Notes where i can put my notes so need to overthink

