<?php
//  Session security settings (before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
// ini_set('session.cookie_secure', 1); // enable if using HTTPS

session_start();

include '../config/db.php';
$pageTitle = "Login Page";
require '../includes/header.php';

//  Initialize login attempts
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

//  Reset attempts after 60 seconds
if (time() - $_SESSION['last_attempt'] > 60) {
    $_SESSION['attempts'] = 0;
}

if (isset($_POST['login'])) {

    // Block if too many attempts
    if ($_SESSION['attempts'] >= 5) {
        $error = "Too many login attempts. Please try again later.";
    } else {

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Invalid username or password!";
        } else {

            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            $fakeHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $hash = $user['password'];
            } else {
                $user = null;
                $hash = $fakeHash;
            }

            if (password_verify($password, $hash) && $user !== null) {

                session_regenerate_id(true);

                //  Reset attempts on success
                $_SESSION['attempts'] = 0;

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                if ($user['role'] == 'admin') {
                    header("location: ../admin/admin_dashboard.php");
                } else {
                    header("Location:../index.php");
                }
                exit();

            } else {
                //  Failed login
                $_SESSION['attempts']++;
                $_SESSION['last_attempt'] = time();

                $error = "Invalid username or password!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

</body>
</html>