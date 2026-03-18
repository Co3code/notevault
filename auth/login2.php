<?php
    session_start();
    include '../config/db.php';
    $pageTitle = "Login Page";
    require '../includes/header.php';

    if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Secure your query! Consider using prepared statements later.
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role']; // store role session
            if ($user['role'] == 'admin') {
                header("location: ../admin/admin_dashboard.php");
            } else {
                header("Location:../index.php");
            }
            exit();
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "User not found!";
    }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
     <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
    
</body>
</html>