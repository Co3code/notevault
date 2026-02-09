<?php
include "db.php";

$title   = mysqli_real_escape_string($conn, $_POST['title']);
$content = mysqli_real_escape_string($conn, $_POST['content']);

$query = "INSERT INTO notes (title, content) VALUES ('$title', '$content')";
mysqli_query($conn, $query);

header("Location: index.php");
exit;
