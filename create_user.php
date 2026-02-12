<?php
/*include "db.php";

$username = "admin";
$password = password_hash("123456", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password)
        VALUES ('$username', '$password')";

if ($conn->query($sql)) {
    echo "User created!";
} */
include "db.php";

$username = "";
$password = password_hash("", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password)
        VALUES ('$username', '$password')";

if ($conn->query($sql)) {
    echo "User created successfully! Username: admin, Password: 123456";
} else {
    echo "Error creating user: " . $conn->error;
}
