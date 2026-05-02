<?php
    session_start();
    include '../config/db.php';

    if (isset($_POST['login'])) {

    // Input handling
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    //  Empty input check
    if (empty($username) || empty($password)) {
        $error = "Invalid username or password!";
    } else {

        // 🔍 Fetch user safely
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        //  Fake hash for timing protection
        $fakeHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hash = $user['password'];
        } else {
            $user = null;
            $hash = $fakeHash;
        }

        //  Secure password check
        if (password_verify($password, $hash) && $user !== null) {

            //  Prevent session fixation
            session_regenerate_id(true);

            //  Set session data
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            //  Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();

        } else {
            //  Generic error (no user enumeration)
            $error = "Invalid username or password!";
        }
    }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NoteVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            /* REPLACE 'background-image.jpg' WITH YOUR FILE PATH */
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../images/notes3.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;

            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            /* Glassmorphism Effect */
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);

            padding: 3rem 2rem;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        h2 {
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.9);
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
            margin-top: 1rem;
        }

        .btn-primary:hover {
            filter: brightness(1.1);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2><i class=""></i>NoteVault</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input
                type="text"
                 name="username"
                  class="form-control"
                  placeholder="Username" required
                    value="<?php echo htmlspecialchars($username ?? '') ?>">  <!-- ??  kong naa gamiton ang usernmae na gi declared kong wala gamiton ang empty string('')  Null Coalescing Operator -->
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">
                Sign In
            </button>
        </form>
        <p class="mt-3 text-center text-white">
             Don't have an account? <a href="register.php" class="text-decoration-none" style="color: #ffd700;">Register here</a>
        </p>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>