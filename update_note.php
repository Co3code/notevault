<?php
include "db.php";

$id      = $_POST['id'];
$title   = mysqli_real_escape_string($conn, $_POST['title']);
$content = mysqli_real_escape_string($conn, $_POST['content']);

mysqli_query($conn, "UPDATE notes SET title='$title', content='$content' WHERE id=$id");

header("Location: index.php");
exit;
