<?php
session_start();
include "db.php";

$success = null;

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $success = "Registration successful! Redirecting to login...";
            // Redirect after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $error = "Error creating user: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - NoteVault</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/3dd.jpg');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
}

.register-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.37);
    padding: 3rem 2rem;
    width: 100%;
    max-width: 400px;
    transition: transform 0.3s ease;
}

.register-card:hover { transform: translateY(-5px); }

h2 {
    color: #ffffff;
    text-align: center;
    margin-bottom: 2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.input-group {
    background: rgba(255,255,255,0.9);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1.5rem !important;
}

.input-group-text {
    background: transparent;
    border: none;
    color: #667eea;
    padding-left: 1.2rem;
}

.form-control {
    border: none;
    padding: 0.8rem;
    background: transparent;
}

.form-control:focus {
    box-shadow: none;
    background: #fff;
}

.btn-primary {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
    border-radius: 10px;
    padding: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    width: 100%;
    margin-top: 1rem;
}

.btn-primary:hover {
    filter: brightness(1.1);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.alert {
    border-radius: 10px;
    color: white;
    text-align: center;
    font-weight: 600;
}

.alert-success { background: rgba(46, 204, 113, 0.9); }
.alert-danger { background: rgba(220, 53, 69, 0.9); }
</style>
</head>
<body>

<div class="register-card">
    <h2><i class="fas fa-user-plus me-2"></i>Register</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger mb-3"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success mb-3"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if(!isset($success)): ?>
    <form method="POST">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
        <a href="login.php" class="btn btn-link text-white mt-2">Back to login</a>
    </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>