<?php
include "db.php";

$id = $_GET['id'];
// mysqli_query($conn, "DELETE FROM notes WHERE id=$id");

//soft delete
mysqli_query($conn, "UPDATE notes SET deleted_at = NOW() WHERE id = $id");

header("Location: index.php");
exit;
// /Implement soft delete for notes
